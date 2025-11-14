$(document).ready(function () {
fetch("../controllers/SoftwareCatalogo.php")

  .then(res => res.json())
  .then(data => {
    const select = document.getElementById("software");
    data.forEach(item => {
      const option = document.createElement("option");
      option.value = item.Servicio;
      option.textContent = item.Servicio;

      select.appendChild(option);
    });
  })
  .catch(err => console.error("Error cargando catálogo de software:", err));

  let editId = null;

  const table = $('#tablaLicencias').DataTable({
    ajax: {
      url: "../controllers/licenciasoftwareController.php",
      type: "POST",
      data: { accion: "listar" },
      dataSrc: ""
    },
    responsive: true,
    columns: [
      { data: "idLS" },
      { data: "Licencia" },
      { data: "Software" },
      {
        data: 'Estatus',
        render: function (data) {
          const colores = {
            'Libre': 'badge bg-primary',
            'Instalada': 'badge bg-success',
            'Baja': 'badge bg-danger',
          };
          return `<span class="${colores[data] || 'badge bg-light'}">${data}</span>`;
        }
      },


      {
        data: "idNota",
        render: data => data ? data : "—"
      },
      {
        data: null,
        render: row => {
          let btns = `
            <button class="btn btn-sm btn-outline-success me-1" data-edit="${row.idLS}" title="Editar">
              <i class="fas fa-pen"></i>
            </button>
          `;

          if (row.Estatus === "Baja") {
            btns += `
              <button class="btn btn-sm btn-outline-secondary" data-reactivar="${row.idLS}" title="Reactivar">
                <i class="fas fa-undo"></i>
              </button>
            `;
          } else {
            btns += `
              <button class="btn btn-sm btn-outline-danger" data-baja="${row.idLS}" title="Dar de baja">
                <i class="fas fa-trash"></i>
              </button>
            `;
          }

          return btns;
        }
      }
    ],
    language: { url: "../funciones/datatable-es.js" }
  });

  // Agregar o actualizar licencia 
  $('#btnAgregar').on('click', function () {
    const licencia = $('#licencia').val().trim();
    const software = $('#software').val().trim();

    if (!licencia || !software) {
      Swal.fire("Campos vacíos", "Debes ingresar la licencia y el software.", "warning");
      return;
    }

    const accion = editId ? "editar" : "agregar";
    const datos = { accion, idLS: editId, licencia, software };

    fetch("../controllers/licenciasoftwareController.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datos)
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          Swal.fire("Éxito", data.message, "success");
          table.ajax.reload();
          $('#licencia').val('');
          $('#software').val('');
          $('#estatus').val('Libre');
          editId = null;
          $('#btnAgregar').html('<i class="fas fa-plus"></i> Agregar').removeClass('btn-success').addClass('btn-primary');
        } else {
          Swal.fire("Error", data.message, "error");
        }
      });
  });

  // Editar
  $(document).on("click", "[data-edit]", function () {
    const id = $(this).data("edit");
    fetch("../controllers/licenciasoftwareController.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ accion: "obtener", idLS: id })
    })
      .then(res => res.json())
      .then(data => {
        $('#licencia').val(data.Licencia);
        $('#software').val(data.Software);
        $('#estatus').val(data.Estatus);
        editId = data.idLS;
        $('#btnAgregar').html('<i class="fas fa-save"></i> Actualizar').removeClass('btn-primary').addClass('btn-success');
      });
  });

  // Dar de baja 
  $(document).on("click", "[data-baja]", function () {
    const id = $(this).data("baja");
    Swal.fire({
      title: "¿Dar de baja?",
      text: "La licencia se marcará como inactiva.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, continuar"
    }).then((r) => {
      if (r.isConfirmed) {
        fetch("../controllers/licenciasoftwareController.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ accion: "baja", idLS: id })
        }).then(() => table.ajax.reload());
      }
    });
  });

  // Reactivar 
  $(document).on("click", "[data-reactivar]", function () {
    const id = $(this).data("reactivar");
    fetch("../controllers/licenciasoftwareController.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ accion: "reactivar", idLS: id })
    }).then(() => table.ajax.reload());
  });
});
