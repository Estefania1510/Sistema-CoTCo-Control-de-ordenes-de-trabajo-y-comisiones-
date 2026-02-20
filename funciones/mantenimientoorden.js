$(document).ready(function () {

  // Folio
  $.ajax({
    url: "../controllers/obtenerFolio.php",
    method: "GET",
    dataType: "json",
    success: function (data) {
      $("#folio").text(data.folio);
    }
  });

  const tablaMnt = $('#tablaMnt').DataTable({
    responsive: {
      details: { type: 'column', target: 0 }
    },
    columnDefs: [
      { className: 'dtr-control', orderable: false, targets: 0 },
      { orderable: false, targets: [1,2,3,4,5] }
    ],
    paging: false,
    searching: false,
    info: false,
    ordering: false,
    autoWidth: false,
    language: { url: "../funciones/datatable-es.js" }
  });

  function calcularCostos() {
    const isPendiente = $("#cotizacionPendiente").is(":checked");
    const anticipoField = $("#anticipo");
    const restoField = $("#resto");
    const errorDiv = $("#error-anticipo");

    if (isPendiente) {
      restoField.val("0.00");
      errorDiv.hide();
      return;
    }

    let total = 0;
    tablaMnt.rows({ page: 'all' }).every(function () {
      const node = this.node();
      const precio = parseFloat($(node).find("input[name='precio[]']").val()) || 0;
      const cantidad = parseFloat($(node).find("input[name='cantidad[]']").val()) || 0;
      total += (cantidad * precio);
    });

    $("#total").prop("readonly", true).val(total.toFixed(2));

    const anticipo = parseFloat(anticipoField.val()) || 0;
    const resto = total - anticipo;
    restoField.val(resto.toFixed(2));

    if (anticipo > total) {
      errorDiv.text("El anticipo excede el total. Se debe regresar el sobrante al cliente.").show();
    } else {
      errorDiv.hide();
    }
  }

  // Recalcular cuando cambien precios/cantidades/anticipo
  $(document).on('input', "input[name='precio[]'], input[name='cantidad[]'], #anticipo", function () {
    let v = parseFloat(this.value);
    if (!isNaN(v) && v < 0) this.value = '0.00';
    calcularCostos();
  });

  // Checkbox solo muestra/oculta los selects de catálogo (NO borra tabla)
  $('#agregarProblema').on('change', function () {
    if (this.checked) {
      $('#bloqueCatalogo').slideDown();
      cargarTipos();
    } else {
      $('#bloqueCatalogo').slideUp();
      $('#tipoServicio').val('');
      $('#servicioCatalogo').val('');
    }
  });

  function cargarTipos() {
    $.ajax({
      url: '../controllers/CargarServicios.php',
      dataType: 'json',
      success: function (data) {
        const tipoSelect = $('#tipoServicio');
        tipoSelect.empty().append('<option value="">Selecciona tipo</option>');
        data.forEach(tipo => {
          tipoSelect.append(`<option value="${tipo.idTipoMnt}">${tipo.NombreTipo}</option>`);
        });
      },
      error: function () {
        Swal.fire('Error', 'No se pudieron cargar los tipos.', 'error');
      }
    });
  }

  // cargar servicios por tipo
  $('#tipoServicio').on('change', function () {
    const idTipo = $(this).val();
    const servicioSelect = $('#servicioCatalogo');

    if (!idTipo) {
      servicioSelect.html('<option value="">Selecciona un tipo primero</option>');
      return;
    }

    $.ajax({
      url: '../controllers/CargarServicios.php',
      data: { tipo: idTipo },
      dataType: 'json',
      success: function (data) {
        servicioSelect.empty().append('<option value="">Selecciona un servicio</option>');
        data.forEach(s => {
          servicioSelect.append(`<option value="${s.Servicio}">${s.Servicio}</option>`);
        });
      },
      error: function () {
        Swal.fire('Error', 'No se pudieron cargar los servicios.', 'error');
      }
    });
  });

  // === AGREGAR DESDE CATÁLOGO ===
  $('#btnAgregarServicio').on('click', function () {
    const tipoTexto = $('#tipoServicio option:selected').text();
    const servicioTexto = $('#servicioCatalogo option:selected').text();
    const tipoVal = $('#tipoServicio').val();
    const servicioVal = $('#servicioCatalogo').val();

    if (!tipoVal || !servicioVal) {
      Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Selecciona un tipo y un servicio antes de agregar.' });
      return;
    }

    const fila = `
      <tr>
        <td></td>
        <td>
          <span class="d-block fw-semibold text-truncate" title="${tipoTexto}">${tipoTexto}</span>
          <input type="hidden" name="tipo[]" value="${tipoTexto}">
          <input type="hidden" name="origen[]" value="CATALOGO">
        </td>
        <td>
          <span class="d-block text-truncate" title="${servicioTexto}">${servicioTexto}</span>
          <input type="hidden" name="servicio[]" value="${servicioTexto}">
        </td>
        <td>
          <input type="number" name="cantidad[]" class="form-control text-end" step="1.00" value="1">
        </td>
        <td>
          <input type="text" name="precio[]" class="form-control text-end" step="0.01" placeholder="0.00">
        </td>
        <td>
          <button type="button" class="btn btn-danger btn-sm" data-del="row">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
      </tr>
    `;

    tablaMnt.row.add($(fila)).draw(false);
    calcularCostos();
  });


  // === AGREGAR MANUAL ===
  $('#btnAgregarManual').on('click', function () {
    const desc = ($('#servicioManual').val() || '').trim();

    if (!desc) {
      Swal.fire({ icon: 'warning', title: 'Falta concepto', text: 'Escribe el concepto manual.' });
      return;
    }

    const fila = `
      <tr>
        <td></td>
        <td>
          <span class="d-block fw-semibold">Manual</span>
          <input type="hidden" name="tipo[]" value="">
          <input type="hidden" name="origen[]" value="MANUAL">
        </td>
        <td>
          <span class="d-block text-truncate" title="${desc}">${desc}</span>
          <input type="hidden" name="servicio[]" value="${$('<div>').text(desc).html()}">
        </td>
        <td>
          <input type="number" name="cantidad[]" class="form-control text-end" step="1" value="1">
        </td>
        <td>
          <input type="text" name="precio[]" class="form-control text-end" inputmode="decimal" placeholder="0.00">
        </td>
        <td>
          <button type="button" class="btn btn-danger btn-sm" data-del="row">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
      </tr>
    `;

    tablaMnt.row.add($(fila)).draw(false);

    // limpiar concepto
    $('#servicioManual').val('');

    calcularCostos();
  });


  // Eliminar fila
  $('#tablaMnt tbody').on('click', '[data-del="row"]', function () {
    const table = $('#tablaMnt').DataTable();
    let tr = $(this).closest('tr');
    let row = table.row(tr);

    if (tr.hasClass('child')) {
      tr = tr.prev();
      row = table.row(tr);
    }

    Swal.fire({
      title: '¿Eliminar fila?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        row.remove().draw(false);
        calcularCostos();
      }
    });
  });

  // Cotización pendiente
  function CotizacionPendiente() {
    const isPendiente = $("#cotizacionPendiente").is(":checked");
    const total = $("#total");
    const resto = $("#resto");
    const msgPendiente = $("#msgPendiente");

    if (isPendiente) {
      total.val("").prop("readonly", true);
      resto.val("").prop("readonly", true);
      $("input[name='precio[]'], input[name='cantidad[]']").val("").prop("readonly", true).addClass("bg-light");
      msgPendiente.show();
    } else {
      total.prop("readonly", true);
      resto.prop("readonly", true);
      $("input[name='precio[]'], input[name='cantidad[]']").prop("readonly", false).removeClass("bg-light");
      msgPendiente.hide();
      calcularCostos();
    }
  }
  $("#cotizacionPendiente").on("change", CotizacionPendiente);
  $(window).on("load", CotizacionPendiente);

  $("#nombreCliente").on("input", function () {
  $("#idCliente").val("");
});

  // autocompletar cliente (tu código igual)
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
    delay: 250,
    select: function (event, ui) {
      $("#telefono").val(ui.item.telefono || "");
      $("#telefono2").val(ui.item.telefono2 || "");
      $("#direccion").val(ui.item.direccion || "");
      $("#idCliente").val(ui.item.idCliente);
    },
    change: function (event, ui) {
      if (!ui.item) $("#idCliente").val("");
    }
  });

 // ======================
// VALIDACIONES (MANUAL + TABLA)
// ======================

// Utilidad: permitir solo teclas de control/navegación
function esTeclaControl(e) {
  const k = e.key;
  return (
    k === "Backspace" || k === "Delete" || k === "Tab" ||
    k === "ArrowLeft" || k === "ArrowRight" || k === "Home" || k === "End" ||
    e.ctrlKey || e.metaKey // copiar/pegar/seleccionar todo
  );
}

});
