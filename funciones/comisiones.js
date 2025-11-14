document.addEventListener("DOMContentLoaded", () => {
  const isAdminView = !!document.getElementById("tablaComisiones");
  const isDetalleView = !!document.getElementById("tablaDetalle") || !!document.getElementById("tablaComisionesUser");
  const btn = document.getElementById("btnActualizarPorcentaje");
  if (isAdminView) {
    listarComisiones();
  }
  if (isDetalleView) {
    document.getElementById("btnBuscar")?.addEventListener("click", cargarDetalle);
    document.getElementById("filtroEstado")?.addEventListener("change", cargarDetalle);
    cargarDetalle();
  }

  if (btn) {
    btn.addEventListener("click", () => {
      const nuevo = document.getElementById("porcentaje").value;

      if (!nuevo || nuevo < 1 || nuevo > 100) {
        Swal.fire("Error", "Ingresa un porcentaje válido (1–100)", "error");
        return;
      }

      Swal.fire({
        title: "¿Actualizar porcentaje?",
        text: "Este será el nuevo porcentaje para todas las comisiones futuras.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Actualizar",
        cancelButtonText: "Cancelar",
      }).then((r) => {
        if (!r.isConfirmed) return;

        fetch("../controllers/comisionesController.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            accion: "actualizarPorcentaje",
            porcentaje: nuevo
          })
        })
          .then(res => res.json())
          .then(data => {
            if (data.status === "ok") {
              Swal.fire("Guardado", "Porcentaje actualizado correctamente", "success");
            } else {
              Swal.fire("Error", data.message, "error");
            }
          })
          .catch(() => Swal.fire("Error", "No se pudo actualizar", "error"));
      });

    });
  }
});


function val(id, def = "") {
  const el = document.getElementById(id);
  return el ? el.value : def;
}

function listarComisiones() {
  console.log("Ejecutando listarComisiones() ...");
  console.log("Enviando petición a:", "../controllers/comisionesController.php");

  fetch("../controllers/comisionesController.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ accion: "listar" }),
  })
    .then((res) => {
      console.log("Respuesta sin procesar:", res);
      return res.json();
    })
    .then((data) => {
      console.log("Respuesta convertida:", data);

      const tbody = document.querySelector("#tablaComisiones tbody");
      tbody.innerHTML = "";

      if (data.status !== "ok" || !data.data.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-muted text-center">No hay usuarios registrados.</td></tr>`;
        return;
      }

      data.data.forEach((row) => {
        tbody.innerHTML += `
          <tr>
            <td></td>
            <td>${row.NombreUsuario}</td>
            <td>${row.rol}</td>
            <td>${row.trabajos}</td>
            <td>
              <a class="btn btn-outline-primary btn-sm"
                 href="comisionesdetalle.php?id=${row.idUsuario}&nombre=${encodeURIComponent(row.NombreUsuario)}"
                 title="Ver detalle">
                <i class="fas fa-eye"></i>
              </a>
            </td>
          </tr>`;
      });

      if ($.fn.DataTable.isDataTable('#tablaComisiones')) {
        $('#tablaComisiones').DataTable().destroy();
      }

      $('#tablaComisiones').DataTable({
        responsive: {
          details: { type: 'column', target: 0 },
        },
        columnDefs: [{ className: 'dtr-control', orderable: false, targets: 0 }],
        order: [1, 'asc'],
        paging: false,
        searching: true,
        info: false,
        autoWidth: false,
        language: {
          url: "../funciones/datatable-es.js",
          emptyTable: "No hay datos para mostrar",
          infoEmpty: "Sin registros disponibles",
        },
      });
    })
    .catch((err) => {
      console.error("Error en fetch:", err);
      Swal.fire("Error", "No se pudieron cargar los usuarios", "error");
    });
}

//DETALLE DE COMISIONES
function cargarDetalle() {
  const idUsuario = window.__idUsuarioActivo;
  const fechaInicio = val("fechaInicio") || null;
  const fechaFin = val("fechaFin") || null;
  const filtroEstado = val("filtroEstado", "todas");

    if ($.fn.DataTable.isDataTable('#tablaComisionesUser')) {
    $('#tablaComisionesUser').DataTable().destroy();
  }

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
    .then((r) => r.json())
    .then((data) => {
      const tbody =
        document.querySelector("#tablaDetalle tbody") ||
        document.querySelector("#tablaComisionesUser tbody");
      tbody.innerHTML = "";

      // Solo actualizar totales si existen 
      const totalEntregadas = document.getElementById("totalEntregadas");
      const totalPendientes = document.getElementById("totalPendientes");
      const totalPagadas = document.getElementById("totalPagadas");

      if (totalEntregadas && totalPendientes && totalPagadas) {
        const tot = data.totales || { entregadas: 0, pendientes: 0, pagadas: 0 };
        totalEntregadas.textContent = `$${Number(tot.entregadas).toFixed(2)}`;
        totalPendientes.textContent = `$${Number(tot.pendientes).toFixed(2)}`;
        totalPagadas.textContent = `$${Number(tot.pagadas).toFixed(2)}`;
      }

      // Mostrar tabla
      if (data.status !== "ok" || !data.data.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-muted">Sin comisiones para mostrar.</td></tr>`;
        return;
      }

      data.data.forEach((c) => {
        let color = "secondary";
        if (c.estado === "Pagado") color = "success";
        else if (c.estado === "Orden Entregada") color = "primary";

        let acciones = "";
        if (window.__ROL_POWER__) {
          if (c.estado === "Orden Entregada")
            acciones = `<button class="btn btn-success btn-sm" onclick="pagar(${c.idComisiones})">Pagar</button>`;
          else if (c.estado === "Orden no Entregada")
            acciones = `<button class="btn btn-warning btn-sm" onclick="adelantar(${c.idComisiones})">Adelantar</button>`;
        }

        tbody.innerHTML += `
          <tr>
          <td></td>
            <td>${c.folio}</td>
            <td>${c.NombreCliente}</td>
            <td>${c.tipo}</td>
            <td>${c.FechaRecepcion}</td>
            <td>${c.FechaEntrega ?? '-'}</td>
            <td>$${Number(c.monto).toFixed(2)}</td>
            <td>${c.fechapago ? c.fechapago : 'Sin pagar'}</td>
            <td><span class="badge bg-${color}">${c.estado}</span></td>
            ${window.__ROL_POWER__ ? `<td>${acciones}</td>` : ""}
          </tr>`;
      });

       if ($.fn.DataTable.isDataTable('#tablaComisionesUser')) {
        $('#tablaComisionesUser').DataTable().destroy();
      }

      $('#tablaComisionesUser').DataTable({
        responsive: {
          details: { type: 'column', target: 0 },
        },
        columnDefs: [{ className: 'dtr-control', orderable: true, targets: 0 }],
        order: [1, 'asc'],
        paging: true,
        searching: true,
        info: false,
        autoWidth: false,
        language: {
          url: "../funciones/datatable-es.js",
          emptyTable: "No hay datos para mostrar",
          infoEmpty: "Sin registros disponibles",
        },
      });
    })
    .catch((err) => {
      console.error("Error en cargarDetalle:", err);
      Swal.fire("Error", "No se pudo cargar el detalle", "error");
    });
}



// FUNCIONES DE PAGO
function pagar(idComision) {
  Swal.fire({
    title: "¿Confirmas que se pagó esta comisión?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, pagar",
    cancelButtonText: "Cancelar",
  }).then((res) => {
    if (!res.isConfirmed) return;

    fetch("../controllers/comisionesController.php", {
      method: "POST",
      body: JSON.stringify({ accion: "marcarPagada", idComision }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === "ok") {
          Swal.fire({ icon: "success", title: "Pagada correctamente", timer: 1200, showConfirmButton: false });
          cargarDetalle();
        }
      })
      .catch(() => Swal.fire("Error", "Error de conexión", "error"));
  });
}

function adelantar(idComision) {
  Swal.fire({
    title: "¿Adelantar comisión?",
    text: "Se pagará aunque la orden no esté entregada.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, adelantar",
    cancelButtonText: "Cancelar",
  }).then((res) => {
    if (!res.isConfirmed) return;

    fetch("../controllers/comisionesController.php", {
      method: "POST",
      body: JSON.stringify({ accion: "adelantarComision", idComision }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === "ok") {
          Swal.fire({ icon: "success", title: "Comisión adelantada", timer: 1200, showConfirmButton: false });
          cargarDetalle();
        }
      })
      .catch(() => Swal.fire("Error", "Error de conexión", "error"));
  });
}
