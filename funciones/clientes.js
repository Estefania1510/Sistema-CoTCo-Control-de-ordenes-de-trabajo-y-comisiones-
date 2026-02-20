document.addEventListener("DOMContentLoaded", () => {
  const tablaClientes  = document.getElementById("tablaClientes");
  const tablaHistorial = document.getElementById("tablaHistorial");

  if (tablaClientes) initClientes();
  if (tablaHistorial) initClienteHistorial();

  // Botón nuevo cliente
  const btnNuevo = document.getElementById("btnNuevoCliente");
  if (btnNuevo) btnNuevo.addEventListener("click", abrirModalNuevo);

  // Guardar (crear/editar)
  const btnGuardar = document.getElementById("btnGuardarCliente");
  if (btnGuardar) btnGuardar.addEventListener("click", guardarCliente);

  // Delegación de eventos en tabla (editar/eliminar)
  if (tablaClientes) {
    tablaClientes.addEventListener("click", (e) => {
      const btnEdit = e.target.closest(".btnEditarCliente");
      const btnDel  = e.target.closest(".btnEliminarCliente");

      if (btnEdit) {
        const id = btnEdit.dataset.id;
        abrirModalEditar(id);
      }
      if (btnDel) {
        const id = btnDel.dataset.id;
        eliminarCliente(id);
      }
    });
  }
});

let dtClientes = null;

function initClientes() {
  if (dtClientes) {
    dtClientes.ajax.reload(null, false); // recarga sin reiniciar la paginación
    return;
  }

  dtClientes = $("#tablaClientes").DataTable({
    responsive: { details: { type: "column", target: 0 } },
    columnDefs: [{ className: "dtr-control", orderable: false, targets: 0 }],
    order: [[1, "asc"]],
    paging: true,
    searching: true,
    info: true,
    autoWidth: false,
    language: { url: "../funciones/datatable-es.js" },

    ajax: {
      url: "../controllers/clientesController.php",
      type: "POST",
      contentType: "application/json",
      data: function () {
        return JSON.stringify({ accion: "listarClientes" });
      },
      dataSrc: function (json) {
        if (!json || json.status !== "ok") return [];
        return json.data || [];
      },
    },

    columns: [
      { data: null, defaultContent: "" }, // columna control responsive
      { data: "idCliente" },
      { data: "NombreCliente", defaultContent: "" },
      { data: "Telefono", defaultContent: "" },
      { data: "Telefono2", defaultContent: "" },
      { data: "Direccion", defaultContent: "" },
      { data: "totalNotas", defaultContent: 0 },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return `
            <a class="btn btn-outline-primary btn-sm"
               href="clientehistorial.php?idCliente=${row.idCliente}"
               title="Ver historial">
              <i class="fas fa-eye"></i>
            </a>

            <button class="btn btn-outline-success btn-sm ms-1 btnEditarCliente"
                    data-id="${row.idCliente}" title="Editar">
              <i class="fas fa-pen"></i>
            </button>

            <button class="btn btn-outline-danger btn-sm ms-1 btnEliminarCliente"
                    data-id="${row.idCliente}" title="Eliminar">
              <i class="fas fa-trash"></i>
            </button>
          `;
        },
      },
    ],
  });
}

/* =========================
   MODAL NUEVO / EDITAR
========================= */

function abrirModalNuevo() {
  document.getElementById("modalClienteTitle").textContent = "Nuevo Cliente";
  document.getElementById("idCliente").value = "";
  document.getElementById("NombreCliente").value = "";
  document.getElementById("Telefono").value = "";
  document.getElementById("Telefono2").value = "";
  document.getElementById("Direccion").value = "";

  const modal = new bootstrap.Modal(document.getElementById("modalCliente"));
  modal.show();
}

function abrirModalEditar(idCliente) {
  fetch("../controllers/clientesController.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ accion: "obtenerCliente", idCliente }),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.status !== "ok") {
        Swal.fire("Error", res.message || "No se pudo obtener el cliente", "error");
        return;
      }

      const c = res.data;
      document.getElementById("modalClienteTitle").textContent = "Editar Cliente";
      document.getElementById("idCliente").value = c.idCliente;
      document.getElementById("NombreCliente").value = c.NombreCliente ?? "";
      document.getElementById("Telefono").value = c.Telefono ?? "";
      document.getElementById("Telefono2").value = c.Telefono2 ?? "";
      document.getElementById("Direccion").value = c.Direccion ?? "";

      const modal = new bootstrap.Modal(document.getElementById("modalCliente"));
      modal.show();
    })
    .catch((err) => {
      console.error(err);
      Swal.fire("Error", "No se pudo obtener el cliente", "error");
    });
}

function guardarCliente() {
  const idCliente     = document.getElementById("idCliente").value || null;
  const NombreCliente = document.getElementById("NombreCliente").value.trim();
  const Telefono      = document.getElementById("Telefono").value.trim();
  const Telefono2     = document.getElementById("Telefono2").value.trim();
  const Direccion     = document.getElementById("Direccion").value.trim();

  if (!NombreCliente) {
    Swal.fire("Falta información", "El nombre del cliente es obligatorio.", "warning");
    return;
  }

  const accion = idCliente ? "actualizarCliente" : "crearCliente";

  fetch("../controllers/clientesController.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      accion,
      idCliente,
      NombreCliente,
      Direccion,
      Telefono,
      Telefono2,
    }),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.status !== "ok") {
        Swal.fire("Error", res.message || "No se pudo guardar", "error");
        return;
      }

      Swal.fire("Listo", res.message || "Guardado correctamente", "success").then(() => {
        const modalEl = document.getElementById("modalCliente");
        bootstrap.Modal.getInstance(modalEl)?.hide();
        dtClientes?.ajax.reload(null, false);

      });
    })
    .catch((err) => {
      console.error(err);
      Swal.fire("Error", "No se pudo guardar el cliente", "error");
    });
}

/* =========================
   ELIMINAR
========================= */

function eliminarCliente(idCliente) {
  Swal.fire({
    title: "¿Eliminar cliente?",
    text: "Esto solo se permitirá si no tiene órdenes registradas.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((r) => {
    if (!r.isConfirmed) return;

    fetch("../controllers/clientesController.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ accion: "eliminarCliente", idCliente }),
    })
      .then((x) => x.json())
      .then((res) => {
        if (res.status !== "ok") {
          Swal.fire("No se pudo", res.message || "Error al eliminar", "error");
          return;
        }
        Swal.fire("Eliminado", res.message || "Cliente eliminado", "success");
        dtClientes?.ajax.reload(null, false);

      })
      .catch((err) => {
        console.error(err);
        Swal.fire("Error", "No se pudo eliminar", "error");
      });
  });
}

// ===============================
//  HISTORIAL DEL CLIENTE
// ===============================
function initClienteHistorial() {
  const idCliente = window.__idCliente;

  const folioInput = document.getElementById("filtroFolio");
  const estadoSel  = document.getElementById("filtroEstado");
  const tipoSel    = document.getElementById("filtroTipo");
  const fechaIni   = document.getElementById("fechaInicio");
  const fechaFin   = document.getElementById("fechaFin");

  const recargar = () => cargarHistorialCliente(idCliente);

  if (folioInput) folioInput.addEventListener("keyup", recargar);
  if (estadoSel)  estadoSel.addEventListener("change", recargar);
  if (tipoSel)    tipoSel.addEventListener("change", recargar);
  if (fechaIni)   fechaIni.addEventListener("change", recargar);
  if (fechaFin)   fechaFin.addEventListener("change", recargar);

  cargarHistorialCliente(idCliente);
}

function cargarHistorialCliente(idCliente) {
  const folio = document.getElementById("filtroFolio")?.value || "";
  const estado = document.getElementById("filtroEstado")?.value || "todos";
  const tipo = document.getElementById("filtroTipo")?.value || "todos";
  const fechaInicio = document.getElementById("fechaInicio")?.value || null;
  const fechaFin = document.getElementById("fechaFin")?.value || null;

  const $tabla = $("#tablaHistorial");

  if ($.fn.DataTable.isDataTable("#tablaHistorial")) {
    $tabla.DataTable().clear().destroy();
  }

  const tbody = document.querySelector("#tablaHistorial tbody");
  tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">Cargando...</td></tr>`;

  fetch("../controllers/clientesController.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      accion: "historialCliente",
      idCliente,
      folio,
      estado,
      tipo,
      fechaInicio,
      fechaFin,
    }),
  })
    .then((r) => r.json())
    .then((data) => {
      tbody.innerHTML = "";

      if (data.status !== "ok" || !data.data || data.data.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" class="text-center text-muted py-3">
              Sin órdenes para mostrar.
            </td>
          </tr>
        `;
        return;
      }

      data.data.forEach((h) => {
        const fechaRecep = h.FechaRecepcion ?? "";
        const fechaEnt   = h.FechaEntrega ?? "Pendiente";

        let urlNota = "#";
        if (h.tipo === "Diseño") urlNota = `verdiseño.php?id=${h.folio}`;
        if (h.tipo === "Mantenimiento") urlNota = `vermantenimiento.php?id=${h.folio}`;

        tbody.innerHTML += `
          <tr>
            <td></td>
            <td>${h.folio}</td>
            <td>${h.tipo}</td>
            <td>${h.UsuarioAsignado}</td>
            <td>${fechaRecep}</td>
            <td>${fechaEnt}</td>
            <td>${h.estado}</td>
            <td>
              <a class="btn btn-outline-primary btn-sm" href="${urlNota}" title="Ver nota">
                <i class="fas fa-eye"></i>
              </a>

              ${
                h.tipo === "Mantenimiento" && Number(h.licencias) > 0
                  ? `<a class="btn btn-outline-warning btn-sm ms-1"
                        href="agregarLicenciaOrden.php?idNota=${h.folio}"
                        title="Ver Licencias Software">
                        <i class="fas fa-key"></i>
                     </a>`
                  : ""
              }
            </td>
          </tr>
        `;
      });

      $tabla.DataTable({
        responsive: { details: { type: "column", target: 0 } },
        columnDefs: [{ className: "dtr-control", orderable: true, targets: 0 }],
        order: [[1, "desc"]],
        paging: true,
        searching: false,
        info: true,
        autoWidth: true,
        language: { url: "../funciones/datatable-es.js" },
      });
    })
    .catch((err) => {
      console.error("Error al cargar historial:", err);
      Swal.fire("Error", "No se pudo cargar el historial del cliente", "error");
    });
}

