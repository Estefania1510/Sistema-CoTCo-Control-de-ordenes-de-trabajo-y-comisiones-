$(document).ready(function () {
  // Agregar nueva licencia 
  $("#addLicencia").on("click", function () {
    const $first = $(".licencia-item:first");
    const $clone = $first.clone();

    // Limpiar campos
    $clone.find("input").val("");
    $clone.find("select").val("Instalada");

    // Actualizar número de licencia
    const total = $(".licencia-item").length + 1;
    $clone.find("h6").html(`<i class="fas fa-key me-2"></i> Licencia #${total}`);

    // Agregar al contenedor
    $("#licenciasContainer").append($clone);
    actualizarOpcionesSoftware();

  });

  // Eliminar tarjeta
  $("#licenciasContainer").on("click", "[data-del='licencia']", function () {
    const total = $(".licencia-item").length;
    if (total === 1) {
      Swal.fire("Atención", "Debe haber al menos una licencia.", "warning");
      return;
    }

    $(this).closest(".licencia-item").remove();

    // Reenumerar licencias
    $(".licencia-item").each(function (index) {
      $(this)
        .find("h6")
        .html(`<i class="fas fa-key me-2"></i> Licencia #${index + 1}`);
    });
  });

  // Enviar formulario 
  $("#formLicencias").on("submit", async function (e) {
    e.preventDefault();

    // Validar que haya al menos una licencia
    if ($(".licencia-item").length === 0) {
      Swal.fire("Atención", "Debes agregar al menos una licencia.", "warning");
      return;
    }

    // Validar campos vacíos
    let incompleto = false;
    $("input[required], select[required]").each(function () {
      if (!$(this).val().trim()) {
        incompleto = true;
        $(this).addClass("is-invalid");
      } else {
        $(this).removeClass("is-invalid");
      }
    });

    if (incompleto) {
      Swal.fire("Campos incompletos", "Por favor llena todos los campos antes de guardar.", "error");
      return;
    }

    // Enviar al servidor
    const formData = new FormData(this);
    const btnGuardar = $(this).find("button[type='submit']");
    btnGuardar.prop("disabled", true).text("Guardando...");

    try {
      const res = await fetch("../controllers/procesarLicenciaOrden.php", {
        method: "POST",
        body: formData,
      });
      const data = await res.json();

      if (data.status === "success") {
        Swal.fire({
          icon: "success",
          title: "Licencias registradas",
          text: data.message,
          confirmButtonColor: "#3085d6",
        }).then(() => {
          window.location.href = "ordenestrabajo.php";
        });
      } else {
        Swal.fire("Error", data.message, "error");
      }
    } catch (err) {
      Swal.fire("Error", err.message, "error");
    } finally {
      btnGuardar.prop("disabled", false).text("Guardar Licencias");
    }
  });

//  cargar licencias libres 
$(document).on("change", ".softwareNota", function () {
  const $tarjeta = $(this).closest(".licencia-item");
  const software = $(this).val();
  const $selectLicencias = $tarjeta.find(".licenciaLibre");

  $selectLicencias.empty().append('<option value="">Cargando...</option>');

  if (!software) {
    $selectLicencias.html('<option value="">Seleccionar licencia...</option>');
    return;
  }

  fetch(`../controllers/LicenciasLibres.php?software=${encodeURIComponent(software)}`)
    .then(res => res.json())
    .then(data => {
      $selectLicencias.empty().append('<option value="">Seleccionar licencia...</option>');
      if (data.length === 0) {
        $selectLicencias.append('<option disabled>No hay licencias libres disponibles</option>');
      } else {
        data.forEach(item => {
          $selectLicencias.append(`<option value="${item.idLS}">${item.Licencia}</option>`);
        });
      }
    })
    .catch(err => {
      console.error("Error al cargar licencias:", err);
      $selectLicencias.html('<option disabled>Error al cargar</option>');
    });
});


//  Evitar repetir el mismo software en múltiples licencias
function actualizarOpcionesSoftware() {

  const seleccionados = $(".softwareNota").map(function () {
    return $(this).val();
  }).get().filter(v => v !== "");

  $(".softwareNota").each(function () {
    const $select = $(this);
    const valorActual = $select.val();

    $select.find("option").show();

    seleccionados.forEach(sw => {
      if (sw !== valorActual) {
        $select.find(`option[value='${sw.replace(/'/g, "\\'")}']`).hide();
      }
    });
  });
}


$(document).on("change", ".softwareNota", function () {
  actualizarOpcionesSoftware();
});


});
