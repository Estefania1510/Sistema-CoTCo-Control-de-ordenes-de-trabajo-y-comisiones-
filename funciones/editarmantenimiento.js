document.addEventListener("DOMContentLoaded", () => {

  const tabla = $('#tablaServicios').DataTable({
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

  // Cargar Tipos de Servicio 
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

  // Cargar Servicios según el Tipo 
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

  // Agregar nueva fila de servicio 
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
        <td><input type="text" name="tipo[]" class="form-control" value="${tipoTexto}" readonly></td>
        <td><input type="text" name="servicio[]" class="form-control" value="${servicioTexto}" readonly></td>
        <td><input type="number" name="precio[]" step="0.01" class="form-control text-end" placeholder="0.00"></td>
        <td><button type="button" class="btn btn-danger btn-sm fa-solid fa-trash-can" data-del="row"> </button></td>
      </tr>
    `;

    tabla.row.add($(fila)).draw(false);
    calcularTotales();
  });

  // Eliminar fila de servicio 
$('#tablaServicios tbody').on('click', '[data-del="row"]', function () {
  if (!rolUsuario.includes('administrador') && !rolUsuario.includes('encargado')) {
    Swal.fire({
      icon: "warning",
      title: "Acción no permitida",
      text: "Solo el administrador o encargado pueden eliminar Servicios.",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  const table = $('#tablaServicios').DataTable();
  let tr = $(this).closest('tr');
  let row = table.row(tr);

  if (tr.hasClass('child')) {
    tr = tr.prev();
    row = table.row(tr);
  }

  if (table.rows().count() > 1) {
    row.remove().draw(false);
  } else {
    tr.find('input').val('');
  }

  table.columns.adjust().responsive.recalc();
  calcularCostos();
});

  // Calcular costo
  function calcularTotales() {
    let total = 0;
    const precios = document.querySelectorAll('input[name="precio[]"]');
    precios.forEach(input => {
      total += parseFloat(input.value) || 0;
    });

    const inputTotal = document.querySelector('input[name="total"]');
    const inputAnticipo = document.querySelector('input[name="anticipo"]');
    const inputResto = document.querySelector('input[name="resto"]');
    const errorDiv = document.getElementById("error-anticipo");

    const anticipo = parseFloat(inputAnticipo.value) || 0;
    const resto = total - anticipo;

    inputTotal.value = total.toFixed(2);
    inputResto.value = resto.toFixed(2);

    if (anticipo > total) {
      if (!errorDiv) return;
      errorDiv.textContent = "El anticipo excede el total. Se debe regresar el sobrante al cliente.";
      errorDiv.style.display = "block";
    } else {
      if (!errorDiv) return;
      errorDiv.style.display = "none";
    }
  }

  // Validaciones numéricas
  $(document).on('input', 'input[name="precio[]"], input[name="anticipo"]', function () {
    let valor = this.value.replace(/[^0-9.]/g, '');
    const partes = valor.split('.');
    if (partes.length > 2) valor = partes[0] + '.' + partes.slice(1).join('');
    this.value = valor;
    calcularTotales();
  });

  // Quitar 0.00 automático 
  $(document).on('focus', 'input[name="anticipo"]', function () {
    if (this.value === '0.00') this.value = '';
  });
  $(document).on('blur', 'input[name="anticipo"]', function () {
    if (this.value.trim() === '' || isNaN(parseFloat(this.value))) this.value = '0.00';
    calcularTotales();
  });

  // Control de Cotización Pendiente
  $('#cotizacionPendiente').on('change', function () {
    const disable = $(this).is(':checked');
    $('input[name="total"], input[name="resto"]').val(disable ? '' : '0.00').prop('readonly', disable);
    $('input[name="precio[]"]').prop('readonly', disable);
    $('#msgPendiente').toggle(disable);
  });

  // Actualizar automáticamente totales
  $(document).on('input', 'input[name="precio[]"], input[name="anticipo"]', calcularTotales);

    cargarTipos();


  // Guardar 
  document.getElementById("formEditarMantenimiento").addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    try {
      const res = await fetch("../controllers/actualizarmantenimiento.php", {
        method: "POST",
        body: formData
      });
      const data = await res.json();

      if (data.status === "success") {
        Swal.fire({
          icon: "success",
          title: "Orden actualizada",
          text: data.message,
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Aceptar"
        }).then(() => {
          window.location.href = "ordenestrabajo.php";
        });
      } else {
        Swal.fire("Error", data.message, "error");
      }
    } catch (err) {
      Swal.fire("Error", err.message, "error");
    }
  });

  //  Evitar signos negativos 
  $(document).on('keydown', 'input[name="anticipo"], input[name="precio[]"]', function (e) {
    if (e.key === '-' || e.keyCode === 189 || e.keyCode === 109) e.preventDefault();
  });

    // Mostrar mensaje si ya hay un anticipo mayor guardado 
  const totalVal = parseFloat(document.querySelector('input[name="total"]').value) || 0;
  const anticipoVal = parseFloat(document.querySelector('input[name="anticipo"]').value) || 0;
  const restoVal = parseFloat(document.querySelector('input[name="resto"]').value) || 0;
  const errorDiv = document.getElementById("error-anticipo");

  if (anticipoVal > totalVal) {
    if (errorDiv) {
      errorDiv.textContent = "El anticipo excede el total. Se debe regresar el sobrante al cliente.";
      errorDiv.style.display = "block";
    }

    if (restoVal === 0) {
      const newResto = totalVal - anticipoVal;
      document.querySelector('input[name="resto"]').value = newResto.toFixed(2);
    }
  }


});
