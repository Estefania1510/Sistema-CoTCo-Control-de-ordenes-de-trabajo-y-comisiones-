$(document).ready(function () {


const urlParams = new URLSearchParams(window.location.search);
const estadoURL = urlParams.get('estado');

if (estadoURL) {
  $('#filtroEstado').val(estadoURL); 
}


  // Rol actual del usuario
const rolesUsuario = window.rolesUsuario || "";

  const table = $('#tablaOrdenes').DataTable({
    ajax: {
      url: '../controllers/obtenerordenes.php',
      type: 'GET',
      data: function (d) {
        d.nombre = $('#filtroNombre').val();
        d.estado = $('#filtroEstado').val();
        d.tipo = $('#filtroTipo').val();
        d.fecha = $('#filtroFecha').val();
        d.misOrdenes = $('#misOrdenes').is(':checked') ? 1 : 0;
        d.OrdenesTrabajadas = $('#OrdenesTrabajadas').is(':checked') ? 1 : 0; 
      },
      dataSrc: ''
    },
    responsive: {
      details: { type: 'column', target: 0 }
    },
    columnDefs: [{
      className: 'dtr-control',
      orderable: false,
      targets: 0
    }],

      searching: false,
    language: { url: "../funciones/datatable-es.js" },
    columns: [
      { data: null, defaultContent: '' },
      { data: 'folio' },
      { data: 'cliente' },
      { data: 'tipo' },
      { data: 'fechaRecepcion' },
      { data: 'fechaEntrega' },
      {
        data: 'estado',
        render: function (data) {
          const colores = {
            'Proceso': 'badge bg-warning',
            'Espera': 'badge bg-secondary',
            'EnviadoTequila': 'badge bg-primary',
            'Avisado': 'badge bg-light text-dark',
            'Entregado': 'badge bg-success',
            'Cancelado': 'badge bg-danger',
            'Retrasado': 'badge bg-dark'
          };
          return `<span class="${colores[data] || 'badge bg-light'}">${data}</span>`;
        }
      },
      { data: 'usuario' },
      {
        data: null,


        //BOTONES
        render: function (row) {
          let botones = `
            <button class="btn btn-outline-primary btn-sm" data-ver="${row.folio}" title="Ver">
              <i class="fas fa-eye"></i>
            </button>
          `;

          // Botón de edición
          if (row.puedeEditar) {
            botones += `
              <button class="btn btn-outline-success btn-sm" data-edit="${row.folio}" data-tipo="${row.tipo}" title="Editar">
                <i class="fas fa-pen"></i>
              </button>
            `;
          }

          // Botón de ticket PDF
          botones += `
            <button class="btn btn-outline-danger btn-sm" data-ticket="${row.folio}" title="Descargar Ticket">
              <i class="fas fa-file-pdf"></i>
            </button>
          `;

          // Agregar Licencia Software 
          const esAdmin = rolesUsuario.includes('administrador');
          if (esAdmin && row.tipo === "Mantenimiento" && row.tieneSoftware) {
            botones += `
              <button class="btn btn-outline-warning btn-sm" data-licencia="${row.folio}" title="Agregar Licencia Software">
              <i class="fas fa-key"></i>
              </button>
            `;
            if (row.tieneLicencia) {
              botones += `
                <i class="fas fa-thumbs-up text-secondary ms-1 " title="Licencias registradas"></i>
              `;
            }
          }

          return botones;
        }

      }
    ],
    order: [1, 'desc']
  });

  if (estadoURL) {
  setTimeout(() => {
    table.ajax.reload();
  }, 300);
}


  $('#filtroNombre, #filtroEstado, #filtroTipo, #filtroFecha, #misOrdenes, #OrdenesTrabajadas').on('input change', function () {
    table.ajax.reload();
  });

  // LIMPIARLA URL AL QUITAR EL FILTRO
$('#filtroEstado').on('change', function () {
  const estado = $(this).val();

  if (!estado || estado === '') {
    const url = new URL(window.location.href);
    url.searchParams.delete('estado'); 
    window.history.replaceState({}, '', url); 
  }
});

  $(document).on("click", "[data-edit]", function () {
    const id = $(this).data("edit");
    const tipo = $(this).data("tipo");

    if (tipo.toLowerCase() === "diseño") {
      window.location.href = `editardiseño.php?id=${id}`;
    } else {
      window.location.href = `editarMantenimiento.php?id=${id}`;
    }
  });

  // VER ORDEN
  $(document).on("click", "[data-ver]", function () {
    const id = $(this).data("ver");
    const tipo = $(this).closest("tr").find("td:nth-child(4)").text().trim().toLowerCase();

    if (tipo === "diseño") {
      window.location.href = `verdiseño.php?id=${id}`;
    } else {
      window.location.href = `vermantenimiento.php?id=${id}`;
    }
  });

  // TICKEY
$(document).on("click", "[data-ticket]", function () {
  const id = $(this).data("ticket");
  const tipo = $(this).closest("tr").find("td:nth-child(4)").text().trim().toLowerCase();

  if (tipo === "diseño") {

    window.open(`../controllers/ticketDiseno.php?idNota=${id}`, '_blank');
  } else {

    window.open(`../controllers/ticketMantenimiento.php?idNota=${id}`, '_blank');
  }
});

  $(document).on("click", "[data-licencia]", function () {
    const id = $(this).data("licencia");
    window.location.href = `agregarLicenciaOrden.php?idNota=${id}`;
  });




});
