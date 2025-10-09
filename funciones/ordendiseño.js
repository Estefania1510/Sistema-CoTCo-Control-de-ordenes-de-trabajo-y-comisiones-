

// FUNCION BOTON X//
const tbody = document.querySelector('#tablaMateriales tbody');

document.getElementById('addRow').addEventListener('click', () => {
  const table = $('#tablaMateriales').DataTable();


  const firstRow = table.row(0).node();
  const newRow = $(firstRow).clone();
  newRow.find('input').val('');


  table.row.add(newRow).draw(false);


  table.responsive.rebuild();
  table.responsive.recalc();

  calcularCostos();
});


//Eliminar filas
$('#tablaMateriales tbody').on('click', '[data-del="row"]', function () {
  const table = $('#tablaMateriales').DataTable();
  let tr = $(this).closest('tr');
  let row = table.row(tr);

  // Si está en modo responsive y esta fila es un child, sube al padre
  if (tr.hasClass('child')) {
    tr = tr.prev();
    row = table.row(tr);
  }

  // Si hay más de una fila, elimina la seleccionada
  if (table.rows().count() > 1) {
    row.remove().draw(false);
  } else {
    // Si solo queda una, limpia sus campos en lugar de borrarla
    tr.find('input').val('');
  }

  table.columns.adjust().responsive.recalc();


  calcularCostos(); // recalcula tus totales
});






// FUNCION CALCULOS//
function calcularCostos() {
  let filas = document.querySelectorAll("#tablaMateriales tbody tr");
  let subtotal = 0;

  filas.forEach(fila => {
    let cant = parseFloat(fila.querySelector("input[name='cantidad[]']").value) || 0;
    let precio = parseFloat(fila.querySelector("input[name='precio[]']").value) || 0;
    subtotal += cant * precio;
  });

  let diseño = parseFloat(document.querySelector("input[name='diseño']").value) || 0;
  let total = subtotal + diseño;

  document.querySelector("input[name='subtotal']").value = subtotal.toFixed(2);
  document.querySelector("input[name='total']").value = total.toFixed(2);

  let anticipoField = document.querySelector("input[name='anticipo']");
  let restoField = document.querySelector("input[name='resto']");
  let errorDiv = document.getElementById("error-anticipo");

  if (anticipoField.value.trim() === "") {
    restoField.value = "";
    errorDiv.style.display = "none";
  } else {
    let anticipo = parseFloat(anticipoField.value) || 0;
    let resto = total - anticipo;
    restoField.value = resto.toFixed(2);

    if (anticipo > total) {
      errorDiv.textContent = " El anticipo excede el total. Se debe regresar el sobrante al cliente.";
      errorDiv.style.display = "block";
    } else {
      errorDiv.style.display = "none";
    }
  }
}

document.addEventListener("input", function(e) {
  if (e.target.matches("input[name='cantidad[]'], input[name='precio[]'], input[name='diseño'], input[name='anticipo']")) {
    calcularCostos();
  }
});
window.addEventListener("load", calcularCostos);


// FUNCION COTIZACION PENDIENTE //
function CotizacionPendiente() {
  let isPendiente = document.getElementById("cotPendiente").checked;
  
  let subtotal = document.querySelector("input[name='subtotal']");
  let total = document.querySelector("input[name='total']");
  let anticipo = document.querySelector("input[name='anticipo']");
  let resto = document.querySelector("input[name='resto']");
  let diseño = document.querySelector("input[name='diseño']");
  let msgPendiente = document.getElementById("msgPendiente");

  if (isPendiente) {
    subtotal.value = "";
    total.value = "";
    anticipo.value = "";
    resto.value = "";
    diseño.value = "";

    subtotal.readOnly  = true;
    total.readOnly  = true;
    anticipo.readOnly  = true;
    resto.readOnly  = true;
    diseño.readOnly  = true;

    msgPendiente.style.display = "block";
  } else {
    subtotal.readOnly  = true; 
    total.readOnly  = true;
    resto.readOnly  = true;
    anticipo.readOnly  = false;
    diseño.readOnly  = false;

    msgPendiente.style.display = "none";
  }
}

document.getElementById("cotPendiente").addEventListener("change", CotizacionPendiente);
window.addEventListener("load", CotizacionPendiente);


// FUNCION COMPLETAR DATOS DEL CLIENTE//
$(function() {
  $("#nombreCliente").autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "../controllers/BuscarCliente.php",
        dataType: "json",
        data: { term: request.term },
        success: function(data) {
          response($.map(data, function(item) {
            return {
              label: item.NombreCliente,
              value: item.NombreCliente,
              telefono: item.Telefono,
              telefono2: item.Telefono2,
              direccion: item.Direccion
            };
          }));
        }
      });
    },
    minLength: 1,
    select: function(event, ui) {
      $("#telefono").val(ui.item.telefono);
      $("#telefono2").val(ui.item.telefono2);
      $("#direccion").val(ui.item.direccion);
    }
  });
});

// FUNCION VALIDAR TELEFONOS //
function validarTelefono(input) {
  input.value = input.value.replace(/[^0-9+]/g, '');
}

// Ejecutar validación en tiempo real
document.addEventListener("DOMContentLoaded", () => {
  const tel1 = document.getElementById("telefono");
  const tel2 = document.getElementById("telefono2");

  if (tel1) tel1.addEventListener("input", () => validarTelefono(tel1));
  if (tel2) tel2.addEventListener("input", () => validarTelefono(tel2));
});

