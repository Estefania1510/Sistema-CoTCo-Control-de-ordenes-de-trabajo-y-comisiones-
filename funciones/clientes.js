document.addEventListener("DOMContentLoaded", () => {
  const tablaClientes   = document.getElementById("tablaClientes");
  const tablaHistorial  = document.getElementById("tablaHistorial");

  if (tablaClientes) {
    initClientes();
  }

  if (tablaHistorial) {
    initClienteHistorial();
  }
});

//Listar clientes
function initClientes() {
  fetch("../controllers/clientesController.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ accion: "listarClientesConNotas" }),
  })
    .then((r) => r.json())
    .then((data) => {
      const tbody = document.querySelector("#tablaClientes tbody");
      tbody.innerHTML = "";

      if (data.status !== "ok" || !data.data.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">No hay clientes con historial.</td></tr>`;
        return;
      }

      data.data.forEach((c) => {
        tbody.innerHTML += `
          <tr>
            <td></td>
            <td>${c.idCliente}</td>
            <td>${c.NombreCliente}</td>
            <td>${c.Telefono ?? ''}</td>
            <td>${c.Telefono2 ?? ''}</td>
            <td>${c.Direccion ?? ''}</td>
            <td>${c.totalNotas}</td>
            <td>
              <a class="btn btn-outline-primary btn-sm"
                 href="clientehistorial.php?idCliente=${c.idCliente}"
                 title="Ver historial del cliente">
                <i class="fas fa-eye"></i>
              </a>
            </td>
          </tr>
        `;
      });

      // DataTable responsiva
      if ($.fn.DataTable.isDataTable('#tablaClientes')) {
        $('#tablaClientes').DataTable().destroy();
      }

      const dt = $('#tablaClientes').DataTable({
        responsive: {
          details: { type: 'column', target: 0 },
        },
        columnDefs: [{ className: 'dtr-control', orderable: true, targets: 0 }],
        order: [[2, 'asc']], 
        paging: true,
        searching: true,      
        info: true,
        autoWidth: true,
        language: { url: "../funciones/datatable-es.js" },
      });

    })
    .catch((err) => {
      console.error("Error al cargar clientes:", err);
      Swal.fire("Error", "No se pudieron cargar los clientes", "error");
    });
}


//  HISTORIAL DEL CLIENTE
function initClienteHistorial() {
  const idCliente = window.__idCliente;
  const folioInput = document.getElementById("filtroFolio");
  const estadoSel = document.getElementById("filtroEstado");
  const tipoSel = document.getElementById("filtroTipo");
  const fechaIni = document.getElementById("fechaInicio");
  const fechaFin = document.getElementById("fechaFin");

  const recargar = () => cargarHistorialCliente(idCliente);

  if (folioInput) folioInput.addEventListener("keyup", recargar);
  if (estadoSel) estadoSel.addEventListener("change", recargar);
  if (tipoSel) tipoSel.addEventListener("change", recargar);
  if (fechaIni) fechaIni.addEventListener("change", recargar);
  if (fechaFin) fechaFin.addEventListener("change", recargar);

  cargarHistorialCliente(idCliente);
}

function cargarHistorialCliente(idCliente) {
  const folio = document.getElementById("filtroFolio")?.value || "";
  const estado = document.getElementById("filtroEstado")?.value || "todos";
  const tipo = document.getElementById("filtroTipo")?.value || "todos";
  const fechaInicio = document.getElementById("fechaInicio")?.value || null;
  const fechaFin = document.getElementById("fechaFin")?.value || null;

  const $tabla = $('#tablaHistorial');

  if ($.fn.DataTable.isDataTable('#tablaHistorial')) {
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

      if (!data.data || data.data.length === 0) {
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
        const fechaRecep = h.FechaRecepcion ?? '';
        const fechaEnt   = h.FechaEntrega ?? 'Pendiente';

        let urlNota = "#";
        if (h.tipo === "Diseño") {
          urlNota = `verdiseño.php?id=${h.folio}`;
        } else if (h.tipo === "Mantenimiento") {
          urlNota = `vermantenimiento.php?id=${h.folio}`;
        }

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
			  <!-- Botón Ver -->
			  <a class="btn btn-outline-primary btn-sm" href="${urlNota}" title="Ver nota">
			    <i class="fas fa-eye"></i>
			  </a>
          
			  ${
			    h.tipo === "Mantenimiento" && h.licencias > 0
			      ? `
			        <a class="btn btn-outline-warning btn-sm ms-1"
			           href="agregarLicenciaOrden.php?idNota=${h.folio}"
			           title="Ver Licencias Software">
			          <i class="fas fa-key"></i>
			        </a>
			      `
			      : ''
			  }
			</td>

          </tr>
        `;
      });

      $tabla.DataTable({
        responsive: { details: { type: 'column', target: 0 }},
        columnDefs: [{ className: 'dtr-control', orderable: true, targets: 0 }],
        order: [[1, 'desc']],
        paging: true,
        searching: false,
        info: true,
        autoWidth: true,
        language: { url: "../funciones/datatable-es.js" }
      });
    })
    .catch((err) => {
      console.error("Error al cargar historial:", err);
      Swal.fire("Error", "No se pudo cargar el historial del cliente", "error");
    });
}
