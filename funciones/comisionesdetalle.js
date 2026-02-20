document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("btnBuscar")?.addEventListener("click", cargarDetalle);
  document.getElementById("filtroEstado")?.addEventListener("change", cargarDetalle);
  document.getElementById("btnPagarTodo")?.addEventListener("click", pagarTodo);

  cargarDetalle();
});

function val(id, def = "") {
  const el = document.getElementById(id);
  return el ? el.value : def;
}

function cargarDetalle() {
  const idUsuario = window.__idUsuarioActivo;
  const fechaInicio = val("fechaInicio") || null;
  const fechaFin = val("fechaFin") || null;
  const filtroEstado = val("filtroEstado", "todas");

  // destruir datatable si existe
  if ($.fn.DataTable.isDataTable("#tablaDetalle")) {
    $("#tablaDetalle").DataTable().destroy();
  }

  const tbody = document.querySelector("#tablaDetalle tbody");
  tbody.innerHTML = "";

  fetch("../controllers/comisionesController.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      accion: "detalleUsuario",
      idUsuario,
      fechaInicio,
      fechaFin,
      filtroEstado,
    }),
  })
    .then(async (r) => {
      // evita el clásico "unexpected character" si PHP manda warnings/html
      const text = await r.text();
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error("Respuesta NO JSON:", text);
        throw e;
      }
    })
    .then((data) => {
      // Tarjeta total por pagar
      const totalPorPagar = document.getElementById("totalPorPagar");
      if (totalPorPagar) {
        const pendiente = Number(data.pendiente || 0);
        totalPorPagar.textContent = `$${pendiente.toFixed(2)}`;

        const btnPagarTodo = document.getElementById("btnPagarTodo");
        if (btnPagarTodo) btnPagarTodo.disabled = pendiente <= 0;
      }

      // si no hay datos
      if (data.status !== "ok" || !data.data || !data.data.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-muted text-center">Sin comisiones para mostrar.</td></tr>`;
        initDT();
        return;
      }

      data.data.forEach((c) => {
        let color = "secondary";
        if (c.estado === "Orden Cancelada") color = "danger";
        else if (c.estado === "Pagado") color = "success";
        else if (c.estado === "Orden Entregada") color = "primary";

        // botones de acción
        let acciones = "";

        if (window.__ROL_POWER__) {
          // admin/encargado
          if (c.estado === "Orden Entregada") {
            acciones += `<button class="btn btn-success btn-sm" onclick="pagar(${c.idComisiones})">Pagar</button>`;
          } else if (c.estado === "Orden no Entregada") {
            acciones += `<button class="btn btn-warning btn-sm" onclick="adelantar(${c.idComisiones})">Adelantar</button>`;
          }
        }

        // ver nota
        let rutaNota = "";
        if (c.tipo === "Diseño") rutaNota = `verdiseño.php?id=${c.folio}`;
        if (c.tipo === "Mantenimiento") rutaNota = `vermantenimiento.php?id=${c.folio}`;

        const btnVerNota = `
          <a class="btn btn-outline-primary btn-sm" href="${rutaNota}" title="Ver Nota">
            <i class="fas fa-eye"></i>
          </a>
        `;

        const botonesAccion = `<div class="d-flex gap-2">${acciones}${btnVerNota}</div>`;

        tbody.innerHTML += `
          <tr>
            <td></td>
            <td>${c.folio}</td>
            <td>${c.NombreCliente}</td>
            <td>${c.tipo}</td>
            <td>${c.FechaRecepcion}</td>
            <td>${c.FechaEntrega ?? "-"}</td>
            <td>$${Number(c.monto).toFixed(2)}</td>
            <td>${c.fechapago ? c.fechapago : "-"}</td>
            <td><span class="badge bg-${color}">${c.estado}</span></td>
            <td>${botonesAccion}</td>
          </tr>`;
      });

      initDT();
    })
    .catch((err) => {
      console.error("Error en cargarDetalle:", err);
      Swal.fire("Error", "No se pudo cargar las comisiones", "error");
    });
}

function initDT() {
  $("#tablaDetalle").DataTable({
    responsive: { details: { type: "column", target: 0 } },
    columnDefs: [{ className: "dtr-control", orderable: true, targets: 0 }],
    order: [1, "asc"],
    paging: true,
    searching: true,
    info: true,
    autoWidth: false,
    language: { url: "../funciones/datatable-es.js" },
  });
}

// Pagar 1
function pagar(idComision) {
  Swal.fire({
    title: "¿Confirmas el pago?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, pagar",
    cancelButtonText: "Cancelar",
  }).then((res) => {
    if (!res.isConfirmed) return;

    fetch("../controllers/comisionesController.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ accion: "marcarPagada", idComision }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === "ok") {
          Swal.fire({ icon: "success", title: "Pagada", timer: 1200, showConfirmButton: false });
          cargarDetalle(); // actualiza tarjeta + tabla
        } else {
          Swal.fire("Error", d.message || "No se pudo pagar", "error");
        }
      })
      .catch(() => Swal.fire("Error", "Error de conexión", "error"));
  });
}

// Adelantar
function adelantar(idComision) {
  Swal.fire({
    title: "¿Adelantar comisión?",
    text: "¿Deseas adelantar el pago aunque la orden no esté entregada?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, adelantar",
    cancelButtonText: "Cancelar",
  }).then((res) => {
    if (!res.isConfirmed) return;

    fetch("../controllers/comisionesController.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ accion: "adelantarComision", idComision }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === "ok") {
          Swal.fire({ icon: "success", title: "Comisión adelantada", timer: 1200, showConfirmButton: false });
          cargarDetalle();
        } else {
          Swal.fire("Error", d.message || "No se pudo adelantar", "error");
        }
      })
      .catch(() => Swal.fire("Error", "Error de conexión", "error"));
  });
}

// Pagar todo
function pagarTodo() {
  const idUsuario = window.__idUsuarioActivo;

  Swal.fire({
    title: "¿Pagar todas las comisiones pendientes?",
    text: "Se marcarán como pagadas todas las comisiones NO pagadas.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, pagar todo",
    cancelButtonText: "Cancelar",
  }).then((res) => {
    if (!res.isConfirmed) return;

    fetch("../controllers/comisionesController.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ accion: "pagarTodoUsuario", idUsuario }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === "ok") {
          Swal.fire({ icon: "success", title: "Listo", text: "Todas fueron marcadas como pagadas", timer: 1400, showConfirmButton: false });
          cargarDetalle(); // tarjeta a 0 y tabla actualizada
        } else {
          Swal.fire("Error", d.message || "No se pudo pagar todo", "error");
        }
      })
      .catch(() => Swal.fire("Error", "Error de conexión", "error"));
  });
}
