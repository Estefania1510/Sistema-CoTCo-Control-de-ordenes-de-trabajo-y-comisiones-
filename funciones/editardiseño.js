document.addEventListener("DOMContentLoaded", () => {

  const tbody = document.querySelector('#tablaMateriales tbody');

// AGREGAR FILA
document.getElementById('addRow').addEventListener('click', () => {
  const table = $('#tablaMateriales').DataTable();

  // Si no hay filas, crea una desde cero
  if (table.rows().count() === 0) {
    const rowNode = $(`
      <tr>
        <td></td>
        <td><input type="text" name="material[]" class="form-control" value=""></td>
        <td><input type="text" name="cantidad[]" class="form-control" value=""></td>
        <td><input type="text" name="precio[]" class="form-control" value=""></td>
        <td><button type="button" class="btn btn-danger btn-sm fa-solid fa-trash-can" data-del="row"></button></td>
      </tr>
    `);

    table.row.add(rowNode).draw(false);
  } else {
    // Si sí hay, clona la primera
    const firstRow = table.row(0).node();
    const newRow = $(firstRow).clone();
    newRow.find('input').val('');
    newRow.removeAttr('data-original');

    newRow.find('input[name="material[]"]').prop('readonly', false);
    newRow.find('input[name="cantidad[]"]').prop('readonly', false);
    newRow.find('input[name="precio[]"]').prop('readonly', false);

    table.row.add(newRow).draw(false);
  }

  table.responsive.rebuild();
  table.responsive.recalc();
  calcularCostos();
});



// ELIMINAR FILA (TODOS)
$('#tablaMateriales tbody').on('click', '[data-del="row"]', function () {
  const table = $('#tablaMateriales').DataTable();
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



  // VALIDACIÓN DE CAMPOS NUMÉRICOS
  $(document).on('input', 'input[name="cantidad[]"], input[name="precio[]"]', function () {
    let valor = this.value.replace(/[^0-9.]/g, '');
    const partes = valor.split('.');
    if (partes.length > 2) {
      valor = partes[0] + '.' + partes.slice(1).join('').replace(/\./g, '');
    }
    this.value = valor;
  });

      // FUNCIÓN CALCULAR COSTOS
     function calcularCostos() { 
        let subtotal = 0;
        const table = $.fn.dataTable.isDataTable('#tablaMateriales')
          ? $('#tablaMateriales').DataTable()
          : null;

        if (table) {
          table.rows().every(function () {
            const node = this.node();
            const cantidad = parseFloat($(node).find('input[name="cantidad[]"]').val()) || 0;
            const precio = parseFloat($(node).find('input[name="precio[]"]').val()) || 0;
            subtotal += cantidad * precio;
          });
        } else {
          document.querySelectorAll('#tablaMateriales tbody tr').forEach((fila) => {
            const cantidad = parseFloat(fila.querySelector('input[name="cantidad[]"]').value) || 0;
            const precio = parseFloat(fila.querySelector('input[name="precio[]"]').value) || 0;
            subtotal += cantidad * precio;
          });
        }

        const inputSubtotal = document.querySelector('input[name="subtotal"]');
        const inputDiseno = document.querySelector('input[name="diseño"]');
        const inputTotal = document.querySelector('input[name="total"]');
        const inputAnticipo = document.querySelector('input[name="anticipo"]');
        const inputResto = document.querySelector('input[name="resto"]');
        const errorDiv = document.getElementById("error-anticipo");

        const subtotalVal = subtotal;
        const disenoVal = parseFloat(inputDiseno.value) || 0;
        const anticipoVal = parseFloat(inputAnticipo.value) || 0;
        const total = subtotalVal + disenoVal;
        const resto = total - anticipoVal;

        inputSubtotal.value = subtotalVal.toFixed(2);
        inputTotal.value = total.toFixed(2);
        inputResto.value = resto.toFixed(2);

        // Mostrar mensaje si el anticipo excede el total
        if (anticipoVal > total) {
          if (errorDiv) {
            errorDiv.textContent = "El anticipo excede el total. Se debe regresar el sobrante al cliente.";
            errorDiv.style.display = "block";
          }
        } else {
          if (errorDiv) errorDiv.style.display = "none";
        }
      }

  calcularCostos();

  // ACTUALIZAR COSTOS AUTOMÁTICAMENTE
  document.addEventListener("input", (e) => {
    if (
      e.target.name === "cantidad[]" ||
      e.target.name === "precio[]" ||
      e.target.name === "diseño" ||
      e.target.name === "anticipo"
    ) {
      calcularCostos();
    }
  });

  // COTIZACIÓN PENDIENTE
  const cotPendiente = document.getElementById("cotPendiente");
  const msgPendiente = document.getElementById("msgPendiente");
  if (cotPendiente) {
    cotPendiente.addEventListener("change", () => {
      const disable = cotPendiente.checked;
      document.querySelectorAll('input[name="subtotal"], input[name="diseño"], input[name="total"], input[name="anticipo"], input[name="resto"]').forEach((el) => {
        el.value = "";
        el.readOnly = disable;
      });
      msgPendiente.style.display = disable ? "block" : "none";
    });
  }

  // FECHA ENTREGA
      const estatusSelect = document.getElementById("estatus");
      const fechaEntregaInput = document.getElementById("FechaEntrega");
      if (estatusSelect && fechaEntregaInput) {
      estatusSelect.addEventListener("change", () => {
        if (estatusSelect.value === "Entregado") {
          const hoy = new Date().toISOString().split("T")[0];
          fechaEntregaInput.value = hoy;
        }
      });
  }

  // GUARDAR
  document.getElementById("formEditarDiseno").addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);

    try {
      const res = await fetch("../controllers/actualizardiseño.php", {
        method: "POST",
        body: formData,
      });
      const data = await res.json();

      if (data.status === "success") {
        Swal.fire({
          icon: "success",
          title: "Orden actualizada",
          text: data.message,
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Aceptar",
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

