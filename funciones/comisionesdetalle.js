document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("btnBuscar").addEventListener("click", cargarDetalle);
  document.getElementById("filtroEstado").addEventListener("change", cargarDetalle);
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

  if ($.fn.DataTable.isDataTable('#tablaDetalle')) {
    $('#tablaDetalle').DataTable().destroy();
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
    .then((r) => r.json())
    .then((data) => {
      // Totales
      const tot = data.totales || { entregadas: 0, pendientes: 0, pagadas: 0 };
      document.getElementById("totalEntregadas").textContent = `$${Number(tot.entregadas).toFixed(2)}`;
      document.getElementById("totalPendientes").textContent = `$${Number(tot.pendientes).toFixed(2)}`;
      document.getElementById("totalPagadas").textContent = `$${Number(tot.pagadas).toFixed(2)}`;

      if (data.status !== "ok" || !data.data || !data.data.length) {
        tbody.innerHTML = ""; 
        return; 
      }

      data.data.forEach((c) => {
        let color = "secondary";
        if (c.estado === "Orden Cancelada") {
            color = "danger";
            c.monto = 0; 
        } 
        else if (c.estado === "Pagado") {
            color = "success";
        }
        else if (c.estado === "Orden Entregada") {
            color = "primary";
        }


          let botones = "";
          if (window.__ROL_POWER__) {
            if (c.estado === "Orden Cancelada") {
                botones = "";
            }
            else if (c.estado === "Orden Entregada") {
                botones = `<button class="btn btn-success btn-sm" onclick="pagar(${c.idComisiones})">Pagar</button>`;
            }
            else if (c.estado === "Orden no Entregada") {
                botones = `<button class="btn btn-warning btn-sm" onclick="adelantar(${c.idComisiones})">Adelantar</button>`;
            }
          }
          
        let rutaNota = "";
        if (c.tipo === "Diseño") {
            rutaNota = `verdiseño.php?id=${c.folio}`;
        } else if (c.tipo === "Mantenimiento") {
            rutaNota = `vermantenimiento.php?id=${c.folio}`;
        }

        // Botón ver nota
        let btnVerNota = `
          <a class="btn btn-outline-primary btn-sm" href="${rutaNota}" title="Ver Nota">
            <i class="fas fa-eye"></i>
          </a>
        `;

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
            <td>
            ${botones}
            ${btnVerNota}
            </td>
          </tr>`;
      });

      if ($.fn.DataTable.isDataTable('#tablaDetalle')) {
        $('#tablaDetalle').DataTable().destroy();
      }

      $('#tablaDetalle').DataTable({
        responsive: {
          details: { type: 'column', target: 0 },
        },
        columnDefs: [{ className: 'dtr-control', orderable: true, targets: 0 }],
        order: [1, 'asc'],
        paging: true,
        searching: true,
        info: false,
        autoWidth: true,
        language: {
          url: "../funciones/datatable-es.js",
          emptyTable: "No hay datos para mostrar",
          infoEmpty: "Sin registros disponibles",
        },
      });
    })
    .catch(() => Swal.fire("Error", "No se pudo cargar las comisiones", "error"));
}

//Funciones de pago
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
      body: JSON.stringify({ accion: "marcarPagada", idComision }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === "ok") {
          Swal.fire({ icon: "success", title: "Pagada", timer: 1200, showConfirmButton: false });
          cargarDetalle();
        }
      });
  });
}

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
      body: JSON.stringify({ accion: "adelantarComision", idComision }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === "ok") {
          Swal.fire({ icon: "success", title: "Comisión adelantada", timer: 1200, showConfirmButton: false });
          cargarDetalle();
        }
      });
  });
}
