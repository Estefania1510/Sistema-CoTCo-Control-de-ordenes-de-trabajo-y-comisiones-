$(document).ready(function () {
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
        details: {
          type: 'column',
          target: 0
        }
      },
      columnDefs: [
        { className: 'dtr-control', orderable: false, targets: 0 }, 
        { orderable: false, targets: [1, 2, 3, 4] }
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

        let total = 0;
        const table = $('#tablaMnt').DataTable();
        const numFilas = table.rows().count();

        if (isPendiente) {
            restoField.val("0.00");
            errorDiv.hide();
            return;
        }

        if (numFilas > 0) {
            total = 0;

            table.rows({ page: 'all' }).every(function () {
                const node = this.node();
                const precio = parseFloat($(node).find("input[name='precio[]']").val()) || 0;
                total += precio;
            });
            $("#total").prop("readonly", true);
            $("#total").val(total.toFixed(2));

        } else {
            $("#total").prop("readonly", false);
        }

        const anticipo = parseFloat(anticipoField.val()) || 0;
        const totalActual = parseFloat($("#total").val()) || 0;
        const resto = totalActual - anticipo;

        restoField.val(resto.toFixed(2));

        if (anticipo > totalActual) {
            errorDiv.text("El anticipo excede el total. Se debe regresar el sobrante al cliente.").show();
        } else {
            errorDiv.hide();
        }
    }

  $(document).on('input', "input[name='precio[]'], #anticipo", function () {
    let valor = parseFloat(this.value);
    if (!isNaN(valor) && valor < 0) this.value = '0.00';
    calcularCostos();
  });

  // mostrar o ocultar bloque de catalogo
    $('#agregarProblema').on('change', function () {
        if (this.checked) {
            $('#bloqueCatalogo').slideDown();
            cargarTipos();
            $("#total").prop("readonly", true);
        } else {
            $('#bloqueCatalogo').slideUp();
            const table = $('#tablaMnt').DataTable();
            table.clear().draw();
            $("#total").val("0.00");
            $("#total").prop("readonly", false);
        }
    });

  // cargar tipos
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

  // cargar servicios
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

  // agregar fila
  $('#btnAgregarServicio').on('click', function () {
    const tipoTexto = $('#tipoServicio option:selected').text();
    const servicioTexto = $('#servicioCatalogo option:selected').text();
    const tipoVal = $('#tipoServicio').val();
    const servicioVal = $('#servicioCatalogo').val();

    if (!tipoVal || !servicioVal) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos incompletos',
        text: 'Selecciona un tipo y un servicio antes de agregar.',
      });
      return;
    }

    const fila = `
      <tr>
        <td></td>
        <td>
          <span class="d-block fw-semibold text-truncate" title="${tipoTexto}">${tipoTexto}</span>
          <input type="hidden" name="tipo[]" value="${tipoTexto}">
        </td>
        <td>
          <span class="d-block text-truncate" title="${servicioTexto}">${servicioTexto}</span>
          <input type="hidden" name="servicio[]" value="${servicioTexto}">
        </td>
        <td><input type="number" name="precio[]" class="form-control text-end" step="0.01" placeholder="0.00"></td>
        <td><button type="button" class="btn btn-danger btn-sm" data-del="row">
          <i class="fa-solid fa-trash-can"></i></button></td>
      </tr>
    `;
    
    tablaMnt.row.add($(fila)).draw(false);
  });

  //Eliminar fila
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
        }
         if (typeof calcularCostos === 'function') {
            calcularCostos();
          }
      });
  });
  

  //validacion precio
  $(document).on('input', 'input[name="precio[]"]', function () {
    let valor = this.value.replace(/[^0-9.]/g, '');
    const partes = valor.split('.');
    if (partes.length > 2) valor = partes[0] + '.' + partes.slice(1).join('');
    this.value = valor;
  });

  // autocompletar cliente
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

  // validacion de telefonos
  function validarTelefono(input) {
    input.value = input.value.replace(/[^0-9+]/g, '').replace(/(?!^)\+/g, '');
  }

  $('#telefono, #telefono2').on('input', function () {
    validarTelefono(this);
  });


  // validacion anticipo
  $(document).on('input', 'input[name="anticipo"], input[name="total"]', function () {
    let valor = this.value.replace(/[^0-9.]/g, '');
    const partes = valor.split('.');
    if (partes.length > 2) valor = partes[0] + '.' + partes.slice(1).join('');
    this.value = valor;
  });

  // quitar el 0.00 automáticamente
  $(document).on('focus', 'input[name="anticipo"], input[name="total"]', function () {
    if (this.value === '0.00') this.value = '';
  });

  // si queda vacío poner 0.00
  $(document).on('blur', 'input[name="anticipo"], input[name="total"]', function () {
    if (this.value.trim() === '' || isNaN(parseFloat(this.value))) this.value = '0.00';
    calcularCostos();
  });

  // Evitar signo negativo
  $(document).on('keydown', 'input[name="anticipo"], input[name="total"]', function (e) {
    if (e.key === '-' || e.keyCode === 189 || e.keyCode === 109) e.preventDefault();
  });

  // recalcular dimensiones
  $(window).on('resize', function () {
    tablaMnt.responsive.recalc();
  });

  function CotizacionPendiente() {
    const isPendiente = $("#cotizacionPendiente").is(":checked");
    const total = $("#total");
    const anticipo = $("#anticipo");
    const resto = $("#resto");
    const precios = $("input[name='precio[]']");
    const msgPendiente = $("#msgPendiente");

    if (isPendiente) {
      total.val("").prop("readonly", true);
      resto.val("").prop("readonly", true);
      anticipo.prop("readonly", false); 

      precios.each(function () {
        $(this).val("").prop("readonly", true).addClass("bg-light");
      });

      msgPendiente.show();
    } else {
      total.prop("readonly", true);
      resto.prop("readonly", true);
      anticipo.prop("readonly", false);

      precios.prop("readonly", false).removeClass("bg-light");

      msgPendiente.hide();

      calcularCostos();
    }
  }

  $("#cotizacionPendiente").on("change", CotizacionPendiente);

  $(window).on("load", CotizacionPendiente);
});
