$(document).ready(function () {
    $.ajax({
      url: "../controllers/obtenerFolio.php",
      method: "GET",
      dataType: "json",
      success: function (data) {
        $("#folio").text(data.folio);
      }
    });

// agregar fila
document.getElementById('addRow').addEventListener('click', () => {
  const table = $('#tablaMateriales').DataTable();
  const firstRow = table.row(0).node();
  const newRow = $(firstRow).clone();

  newRow.find('input').val('');
  table.row.add(newRow).draw(false);

  table.responsive.rebuild();
  table.responsive.recalc();

  calcularCostos();
});

    // Eliminar fila
    $('#tablaMateriales tbody').on('click', '[data-del="row"]', function () {
      const table = $('#tablaMateriales').DataTable();
      let tr = $(this).closest('tr');
      let row = table.row(tr);

      if (tr.hasClass('child')) {
        tr = tr.prev();
        row = table.row(tr);
      }

      const totalFilas = table.rows().count();

      Swal.fire({
        title: '¿Eliminar fila?',
        text: totalFilas > 1
          ? 'Esta acción eliminará la fila seleccionada.'
          : 'Se limpiarán los campos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          if (totalFilas > 1) {
            row.remove().draw(false);
          } else {

            tr.find('input').val('');
          }

          if (typeof calcularCostos === 'function') {
            calcularCostos();
          }
        }
      });
    });



$(document).on('input', 'input[name="cantidad[]"], input[name="precio[]"]', function () {
  let valor = this.value.replace(/[^0-9.]/g, '');
  const partes = valor.split('.');
  if (partes.length > 2) {
    valor = partes[0] + '.' + partes.slice(1).join('').replace(/\./g, '');
  }
  this.value = valor;
});


// CÁLCULO DE COSTOS
function calcularCostos() {
  let subtotal = 0;
  const table = $.fn.dataTable.isDataTable('#tablaMateriales')
    ? $('#tablaMateriales').DataTable()
    : null;

  if (table) {
    table.rows({ page: 'all' }).every(function () {
      const node = this.node();
      const cantidad = parseFloat($(node).find("input[name='cantidad[]']").val()) || 0;
      const precio = parseFloat($(node).find("input[name='precio[]']").val()) || 0;
      subtotal += cantidad * precio;
    });
  } else {
    $("#tablaMateriales tbody tr").each(function () {
      const cantidad = parseFloat($(this).find("input[name='cantidad[]']").val()) || 0;
      const precio = parseFloat($(this).find("input[name='precio[]']").val()) || 0;
      subtotal += cantidad * precio;
    });
  }

  const diseno = parseFloat($("input[name='diseño']").val()) || 0;
  const total = subtotal + diseno;
  const anticipoField = $("input[name='anticipo']");
  const restoField = $("input[name='resto']");
  const errorDiv = $("#error-anticipo");
  const isPendiente = $("#cotPendiente").is(":checked");

  $("input[name='subtotal']").val(subtotal.toFixed(2));
  $("input[name='total']").val(total.toFixed(2));
  
  if (isPendiente) {
    restoField.val("0.00"); 
    errorDiv.hide();
    return; 
  }

  if (!anticipoField.val().trim()) {
    restoField.val("");
    errorDiv.hide();
  } else {
    const anticipo = parseFloat(anticipoField.val()) || 0;
    const resto = total - anticipo;
    restoField.val(resto.toFixed(2));

    if (anticipo > total) {
      errorDiv.text("El anticipo excede el total. Se debe regresar el sobrante al cliente.").show();
    } else {
      errorDiv.hide();
    }
  }
}


$(document).on('input', "input[name='cantidad[]'], input[name='precio[]'], input[name='diseño'], input[name='anticipo']", function () {
  if (this.name === 'diseño' || this.name === 'anticipo') {
    let valor = parseFloat(this.value);
    if (!isNaN(valor) && valor < 0) {
      this.value = '0.00';
    }
  }


  calcularCostos();
});


if ($.fn.dataTable.isDataTable('#tablaMateriales')) {
  const table = $('#tablaMateriales').DataTable();
  table.on('responsive-display responsive-resize draw column-visibility', calcularCostos);
}

$(document).on('input', '.child input[name="cantidad[]"], .child input[name="precio[]"]', function () {
  const parentRow = $(this).closest('tr').prev();
  const originalRow = $('#tablaMateriales').DataTable().row(parentRow);
  const originalNode = $(originalRow.node());

  const name = $(this).attr('name');
  const value = $(this).val();
  originalNode.find(`input[name="${name}"]`).val(value);

  calcularCostos();
});


//  COTIZACIÓN PENDIENTE
function CotizacionPendiente() {
  const isPendiente = $("#cotPendiente").is(":checked");
  const subtotal = $("input[name='subtotal']");
  const total = $("input[name='total']");
  const anticipo = $("input[name='anticipo']");
  const resto = $("input[name='resto']");
  const diseño = $("input[name='diseño']");
  const msgPendiente = $("#msgPendiente");
  const precios = $("input[name='precio[]']");

  if (isPendiente) {
    subtotal.val("").prop("readonly", true);
    total.val("").prop("readonly", true);
    diseño.val("").prop("readonly", true);
    resto.val("").prop("readonly", true);
    anticipo.prop("readonly", false);

    precios.each(function () {
      $(this).val("").prop("readonly", true).addClass("bg-light");
    });

    msgPendiente.show();
  } else {
    subtotal.prop("readonly", true);
    total.prop("readonly", true);
    resto.prop("readonly", true);
    anticipo.prop("readonly", false);
    diseño.prop("readonly", false);
    precios.prop("readonly", false).removeClass("bg-light");

    msgPendiente.hide();
    calcularCostos();
  }
}

$("#cotPendiente").on("change", CotizacionPendiente);
$(window).on("load", CotizacionPendiente);


// AUTOCOMPLETAR CLIENTE
$(function () {
  $("#nombreCliente").autocomplete({
    source: function (request, response) {
      $.ajax({
        url: "../controllers/BuscarCliente.php",
        dataType: "json",
        data: { term: request.term },
        success: function (data) {
          response($.map(data, function (item) {
            return {
              label: item.NombreCliente,
              value: item.NombreCliente,
              idCliente: item.idCliente, 
              telefono: item.Telefono,
              telefono2: item.Telefono2,
              direccion: item.Direccion
            };
          }));
        }
      });
    },
    minLength: 1,
    select: function (event, ui) {
      $("#telefono").val(ui.item.telefono);
      $("#telefono2").val(ui.item.telefono2);
      $("#direccion").val(ui.item.direccion);
      $("#idCliente").val(ui.item.idCliente);
    }
  });
});


//VALIDAR TELÉFONOS
function validarTelefono(input) {
  input.value = input.value
    .replace(/[^0-9+]/g, '')   
    .replace(/(?!^)\+/g, '');  
}

$('#telefono, #telefono2').on('input', function () {
  validarTelefono(this);
});
  
  $(document).on('input', 'input[name="anticipo"], input[name="diseño"]', function () {
    let valor = this.value.replace(/[^0-9.]/g, '');
    
    // Evita múltiples puntos
    const partes = valor.split('.');
    if (partes.length > 2) {
      valor = partes[0] + '.' + partes.slice(1).join('');
    }
    this.value = valor;
  });

  // Evitar puntos en cantidad 
  $(document).on('input', 'input[name="cantidad[]"]', function () {
    this.value = this.value.replace(/[^0-9]/g, '');
  });

  // quitar el 0.00 automáticamente
  $(document).on('focus', 'input[name="anticipo"], input[name="diseño"]', function () {
    if (this.value === '0.00') {
      this.value = '';
    }
  });

  // si queda vacío  poner 0.00
  $(document).on('blur', 'input[name="anticipo"], input[name="diseño"]', function () {
    if (this.value.trim() === '' || isNaN(parseFloat(this.value))) {
      this.value = '0.00';
    }
    calcularCostos(); 
  });

  // Evitar escribir el signo menos
  $(document).on('keydown', 'input[name="anticipo"], input[name="diseño"]', function (e) {
    if (e.key === '-' || e.keyCode === 189 || e.keyCode === 109) {
      e.preventDefault();
    }
  });

 });