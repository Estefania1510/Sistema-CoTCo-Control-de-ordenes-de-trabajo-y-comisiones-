function alertaGuardadoExito() {
  Swal.fire({
    position: "center",
    icon: "success",
    title: "Guardado con éxito",
    showConfirmButton: false,
    timer: 1500
  });
}

function alertaError(msg) {
  Swal.fire({
    position: "center",
    icon: "error",
    title: "Error al guardar",
    text: msg || "Intenta nuevamente",
  });
}
