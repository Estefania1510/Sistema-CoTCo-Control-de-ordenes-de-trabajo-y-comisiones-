$(document).ready(function () {

  // Inicializar DataTable
const tabla = $('#tablaUsuarios').DataTable({
  responsive: {
    details: { type: 'column', target: 0 }
  },
  columnDefs: [{ className: 'dtr-control', orderable: false, targets: 0 }],
  paging: true,
  info: true,
  searching: false,
  language: {
    url: "../funciones/datatable-es.js"  
  }
});

  //  Filtrar por nombre
  $('#buscarNombre').on('keyup', function () {
    tabla.column(1).search(this.value).draw();
  });

  // Filtrar por rol
  $('#filtroRol').on('change', function () {
    tabla.column(3).search(this.value).draw();
  });

  // Agregar nuevo usuario
  $('#btnAgregar').click(function () {
    $('#tituloModal').text('Agregar Usuario');
    $('#formUsuario')[0].reset();
    $('#idUsuario').val('');
    $('#Rol').val([]).trigger('change');
    $('#modalUsuario').modal('show');
  });

  // Editar usuario
$(document).on('click', '.editarUsuario', function () {
  const id = $(this).data('id');

  $.ajax({
    url: '../controllers/obtenerUsuario.php',
    type: 'GET',
    data: { id },
    dataType: 'json',
    success: function (data) {
      if (data.status === 'error') {
        Swal.fire('Error', data.message, 'error');
        return;
      }

      $('#tituloModal').text('Editar Usuario');
      $('#idUsuario').val(data.idUsuario);
      $('#NombreUsuario').val(data.NombreUsuario);
      $('#Usuario').val(data.Usuario);
      $('#Contraseña').val('');

      $('input[name="Rol[]"]').prop('checked', false);

      if (data.Roles) {
        const roles = data.Roles.split(',');
        roles.forEach(r => {
          $(`#rol_${r.trim()}`).prop('checked', true);
        });
      }

      $('#modalUsuario').modal('show');
    },
    error: function () {
      Swal.fire('Error', 'No se pudo obtener la información del usuario.', 'error');
    }
  });
});

  //  Guardar o editar usuario
  $('#formUsuario').submit(function (e) {
    e.preventDefault();

    const formData = $(this).serialize() + '&accion=guardar';

    $.ajax({
      url: '../controllers/UsuarioController.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function (data) {
        if (data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
          });

          $('#modalUsuario').modal('hide');

          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      },
      error: function () {
        Swal.fire('Error', 'Ocurrió un problema al guardar el usuario.', 'error');
      }
    });
  });

      // Inactivar o reactivar usuario
    $(document).on('click', '.cambiarEstadoUsuario', function () {
      const id = $(this).data('id');
      const accion = $(this).data('accion');
      const esBaja = accion === 'baja';

      Swal.fire({
        title: esBaja ? '¿Dar de baja al usuario?' : '¿Reactivar usuario?',
        text: esBaja
          ? 'El usuario no podrá acceder al sistema hasta que se reactive.'
          : 'El usuario podrá iniciar sesión nuevamente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          $.ajax({
            url: '../controllers/UsuarioController.php',
            type: 'POST',
            data: { accion: accion === 'baja' ? 'eliminar' : 'eliminar', id },
            dataType: 'json',
            success: function (data) {
              if (data.status === 'success') {
                Swal.fire({
                  icon: 'success',
                  title: 'Hecho',
                  text: data.message,
                  timer: 1200,
                  showConfirmButton: false
                });

                setTimeout(() => location.reload(), 1200);
              } else {
                Swal.fire('Error', data.message, 'error');
              }
            },
            error: function () {
              Swal.fire('Error', 'No se pudo actualizar el estado del usuario.', 'error');
            }
          });
        }
      });
    });
});
