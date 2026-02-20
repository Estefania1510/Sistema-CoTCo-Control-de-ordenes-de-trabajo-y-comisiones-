// ../funciones/whatsappFlow.js
// Requiere: SweetAlert2 (Swal) y (opcional) FontAwesome para íconos

function normalizarTelefonoWhatsApp(raw) {
  const digits = (raw || "").toString().replace(/\D+/g, "");
  if (digits.length < 10) return null;

  // Si son 10 dígitos, asumimos México y agregamos 52
  return digits.length === 10 ? `52${digits}` : digits;
}

async function construirMensajeWhatsApp(endpointBaseUrl, idNota) {
  const url = new URL(endpointBaseUrl, window.location.href);
  url.searchParams.set("idNota", idNota);

  const r = await fetch(url.toString());
  const j = await r.json();

  if (!j || j.status !== "success") {
    throw new Error(j?.message || "No se pudo construir el mensaje para WhatsApp");
  }
  return j.mensaje;
}


/**
 * Modal para elegir teléfono y poder intentar con ambos SIN que se cierre.
 * - No podemos saber si un número "tiene WhatsApp" sin una API.
 * - Entonces dejamos el modal abierto para que si WhatsApp marca error, el usuario pruebe el otro.
 */
async function abrirSelectorTelefonoWhatsApp({ tel1Raw, tel2Raw, mensaje }) {
  const tel1 = normalizarTelefonoWhatsApp(tel1Raw);
  const tel2 = normalizarTelefonoWhatsApp(tel2Raw);

  const opciones = [];
  if (tel1) opciones.push({ id: tel1, label: `Teléfono 1: ${tel1Raw}` });
  if (tel2) opciones.push({ id: tel2, label: `Teléfono 2: ${tel2Raw}` });

  if (opciones.length === 0) {
    await Swal.fire({
      icon: "warning",
      title: "Teléfono inválido",
      text: "No hay un teléfono válido (teléfono 1 o 2) para enviar WhatsApp.",
    });
    return;
  }

  const htmlRadios = opciones.map((o, idx) => `
    <div style="text-align:left; margin:8px 0;">
      <label style="cursor:pointer;">
        <input type="radio" name="telPick" value="${o.id}" ${idx === 0 ? "checked" : ""} />
        <span style="margin-left:8px;">${o.label}</span>
      </label>
    </div>
  `).join("");

  await Swal.fire({
    icon: "question",
    title: "¿A cuál teléfono quieres enviar?",
    html: `
      ${htmlRadios}
      <div style="margin-top:10px; font-size:0.9em; color:#666; text-align:left;">
        *Nota:* Si WhatsApp dice que el número no existe, vuelve aquí y selecciona el otro.
      </div>
    `,
    showConfirmButton: false,
    showCancelButton: true,
    cancelButtonText: "Cerrar",
    allowOutsideClick: false,
    didOpen: () => {
      const container = Swal.getHtmlContainer();
      if (!container) return;

      // Botón "Continuar" (Abrir WhatsApp) hecho a mano para NO cerrar modal
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "swal2-confirm swal2-styled";
      btn.style.background = "#198754"; // verde bootstrap
      btn.style.marginTop = "12px";
      btn.innerHTML = `<i class="fa-brands fa-whatsapp"></i> Continuar`;

      btn.addEventListener("click", () => {
        const checked = container.querySelector('input[name="telPick"]:checked');
        const tel = checked ? checked.value : null;

        if (!tel) {
          Swal.showValidationMessage("Selecciona un teléfono");
          return;
        }

        const url = `https://wa.me/${tel}?text=${encodeURIComponent(mensaje)}`;
        window.open(url, "_blank");
        // NO cerramos el modal; así puede elegir el otro si WhatsApp marca error.
      });

      Swal.getActions().prepend(btn);
    }
  });
}

/**
 * Flujo reutilizable:
 * - Muestra modal de "Guardado con éxito"
 * - Botón imprimir (rojo + icono)
 * - Botón WhatsApp (verde + icono)
 * - Si WhatsApp: abre selector de teléfono (NO se cierra)
 */
async function flujoGuardadoConAcciones({
  idNota,
  tel1Raw,
  tel2Raw,
  ticketUrl,
  whatsEndpointUrl
}) {
  let mensaje = `Orden guardada.\nFolio: ${idNota}`;

  try {
    mensaje = await construirMensajeWhatsApp(whatsEndpointUrl, idNota);
  } catch (e) {
    // si falla, dejamos un mensaje mínimo
  }

  const r = await Swal.fire({
    icon: "success",
    title: "Guardado con éxito",
    text: "¿Qué deseas hacer?",
    showCancelButton: true,
    showDenyButton: true,

    // Botones con color + iconos (usa HTML)
    confirmButtonText: `<i class="fa-solid fa-print"></i> Imprimir ticket`,
    confirmButtonColor: "#dc3545", // rojo

    denyButtonText: `<i class="fa-brands fa-whatsapp"></i> Enviar por WhatsApp`,
    denyButtonColor: "#198754", // verde

    cancelButtonText: "Cerrar",
  });

  if (r.isConfirmed) {
    window.open(ticketUrl, "_blank");
    return;
  }

  if (r.isDenied) {
    await abrirSelectorTelefonoWhatsApp({
      tel1Raw,
      tel2Raw,
      mensaje
    });
  }
}

// Export “global” (por si no usas módulos)
window.flujoGuardadoConAcciones = flujoGuardadoConAcciones;
