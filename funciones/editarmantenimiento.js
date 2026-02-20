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
      <td>
        <input type="text" class="form-control" value="${tipoTexto}" readonly>
        <input type="hidden" name="tipo[]" value="${tipoTexto}">
        <input type="hidden" name="origen[]" value="CATALOGO">
      </td>
      <td>
        <input type="text" name="servicio[]" class="form-control" value="${servicioTexto}" readonly>
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


    tabla.row.add($(fila)).draw(false);
    calcularTotales();
  });

  // Eliminar fila de servicio 
$('#tablaServicios tbody').on('click', '[data-del="row"]', function () {

  const table = $('#tablaServicios').DataTable();
  let tr = $(this).closest('tr');

  // si dio click en la fila "child" (responsive)
  if (tr.hasClass('child')) {
    tr = tr.prev(); // la fila real es la anterior
  }

  const row = table.row(tr);

  if (table.rows().count() > 1) {
    row.remove().draw(false);
  } else {
    // si solo queda 1 fila, solo limpia (no borrar)
    tr.find('input').val('');
    table.draw(false);
  }

  table.columns.adjust().responsive.recalc();
  calcularTotales(); // ✅ ESTA ES LA FUNCIÓN CORRECTA
});

//AGREGAR MANUAL

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
        <input type="text" class="form-control" value="Manual" readonly>
        <input type="hidden" name="tipo[]" value="">
        <input type="hidden" name="origen[]" value="MANUAL">
      </td>
      <td>
        <input type="text" name="servicio[]" class="form-control" value="${$('<div>').text(desc).html()}">
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

  tabla.row.add($(fila)).draw(false);
  $('#servicioManual').val('');
  calcularTotales();
});


  // Calcular costo
  function calcularTotales() {
    let total = 0;

    $('#tablaServicios tbody tr').each(function () {
      const cant = parseFloat($(this).find('input[name="cantidad[]"]').val()) || 0;
      let p = ($(this).find('input[name="precio[]"]').val() || '').toString().replace(/,/g,'.');
      const precio = parseFloat(p) || 0;
      total += (cant * precio);
    });

    const inputTotal = document.querySelector('input[name="total"]');
    const inputAnticipo = document.querySelector('input[name="anticipo"]');
    const inputResto = document.querySelector('input[name="resto"]');
    const errorDiv = document.getElementById("error-anticipo");

    const anticipo = parseFloat((inputAnticipo.value || '0').replace(/,/g,'.')) || 0;
    const resto = total - anticipo;

    inputTotal.value = total.toFixed(2);
    inputResto.value = resto.toFixed(2);

    if (anticipo > total) {
      if (errorDiv) {
        errorDiv.textContent = "El anticipo excede el total. Se debe regresar el sobrante al cliente.";
        errorDiv.style.display = "block";
      }
    } else {
      if (errorDiv) errorDiv.style.display = "none";
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
$(document).on('input', 'input[name="precio[]"], input[name="cantidad[]"], input[name="anticipo"]', calcularTotales);


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

// ==========================
// WhatsApp (Avisar / Abrir chat) con selección de teléfono
// ==========================
function normalizarTelefono(raw) {
  if (!raw) return "";
  let tel = String(raw).replace(/\D+/g, ""); // solo dígitos

  // Si viene con 10 dígitos, asumimos México y agregamos 52
  if (tel.length === 10) tel = "52" + tel;

  return tel;
}

function obtenerTelefonosValidos() {
  const raw1 = document.getElementById("wa_tel1")?.value || "";
  const raw2 = document.getElementById("wa_tel2")?.value || "";

  const tel1 = normalizarTelefono(raw1);
  const tel2 = normalizarTelefono(raw2);

  const lista = [];
  if (tel1) lista.push({ key: "tel1", tel: tel1, label: `Teléfono 1: ${raw1}` });
  if (tel2) lista.push({ key: "tel2", tel: tel2, label: `Teléfono 2: ${raw2}` });

  return lista;
}

async function elegirTelefonoSiEsNecesario() {
  const telefonos = obtenerTelefonosValidos();

  if (telefonos.length === 0) {
    await Swal.fire("Sin teléfono", "Este cliente no tiene teléfono registrado.", "warning");
    return null;
  }

  // Si solo hay uno → no preguntar
  if (telefonos.length === 1) return telefonos[0].tel;

  // Si hay dos → mostrar radio buttons
  const inputOptions = {};
  telefonos.forEach(t => {
    inputOptions[t.tel] = t.label;
  });

  const { value: telSeleccionado } = await Swal.fire({
    icon: "question",
    title: "¿A cuál teléfono quieres enviar?",
    input: "radio",
    inputOptions: inputOptions,
    inputValidator: (value) => {
      if (!value) {
        return "Selecciona un teléfono";
      }
    },
    confirmButtonText: '<i class="fa-brands fa-whatsapp me-1"></i> Continuar',
    cancelButtonText: "Cerrar",
    showCancelButton: true,
    confirmButtonColor: "#198754"
  });

  return telSeleccionado || null;
}


async function abrirWhatsConMensaje() {
  const tel = await elegirTelefonoSiEsNecesario();
  if (!tel) return;

  const cliente = document.getElementById("wa_cliente")?.value?.trim() || "cliente";
  const trabajo = document.querySelector('input[name="trabajo"]')?.value?.trim() || "tu trabajo";

  // (Opcional) cambiar estatus en pantalla
  // const estatusSelect = document.getElementById("estatus");
  // if (estatusSelect) estatusSelect.value = "Cliente Avisado";

  const texto = `¡Hola ${cliente}! Te informamos de ICT que tu trabajo ${trabajo} ya está listo para ser recogido. ¡Te esperamos!`;
  const url = `https://wa.me/${tel}?text=${encodeURIComponent(texto)}`;
  window.open(url, "_blank", "noopener");
}

async function abrirWhatsChat() {
  const tel = await elegirTelefonoSiEsNecesario();
  if (!tel) return;

  const url = `https://wa.me/${tel}`;
  window.open(url, "_blank", "noopener");
}

document.getElementById("btnWhatsAvisar")?.addEventListener("click", abrirWhatsConMensaje);
document.getElementById("btnWhatsChat")?.addEventListener("click", abrirWhatsChat);



});
