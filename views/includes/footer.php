    
    </main>
  </div>
</div>

<!-- jQuery principal -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<link href="css/tablas.css" rel="stylesheet" />

<!-- jQuery UI para Autocomplete -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- scripts -->
<script src="../funciones/ColapsoTablas.js"></script>

<script src="js/scripts.js"></script>


<!-- PARA CUMPLEAÑOS -->
<?php
$nombreCumple = $_SESSION['nombre'] ?? '';
$fechaNac = $_SESSION['FechaNacimiento'] ?? '';

$esCumpleHoy = false;
if (!empty($nombreCumple) && !empty($fechaNac)) {
  $esCumpleHoy = (date('m-d') === date('m-d', strtotime($fechaNac)));
}

?>

<script>
  // Bandera para que principal.php decida mostrar el botón
  window.esCumpleHoy = <?= $esCumpleHoy ? 'true' : 'false' ?>;

  // Función reusable: confeti + felicitación
  window.runBirthdayCelebration = function() {
    const nombre = <?= json_encode($nombreCumple) ?>;

    // Cargar confetti si no está cargado
function start() {
  // 2 cañones laterales, en ráfagas cortas (bajo consumo)
  const defaults = {
    spread: 55,
    ticks: 140,
    gravity: 0.9,
    decay: 0.92,
    startVelocity: 38,
    scalar: 0.9
  };

  function shootFromLeft() {
    confetti(Object.assign({}, defaults, {
      particleCount: 70,
      angle: 60,
      origin: { x: 0.02, y: 0.75 }
    }));
  }

  function shootFromRight() {
    confetti(Object.assign({}, defaults, {
      particleCount: 70,
      angle: 120,
      origin: { x: 0.98, y: 0.75 }
    }));
  }

  // Disparo “doble” (dos veces) para efecto WOW sin lag
  shootFromLeft(); shootFromRight();
  setTimeout(() => { shootFromLeft(); shootFromRight(); }, 220);

  // Mensaje
  Swal.fire({
    icon: 'success',
    title: '🎉 ¡Feliz cumpleaños!',
    html: '<b>' + nombre + '</b> 🎂<br>¡Que tengas un día increíble!',
    confirmButtonText: '¡Gracias!'
  });
}


    if (typeof confetti === 'function') {
      start();
    } else {
      const s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js';
      s.onload = start;
      document.head.appendChild(s);
    }
  };
</script>

<?php if ($esCumpleHoy): ?>
<script>
  // Auto: SOLO 1 vez por día en este navegador (aunque cierres sesión)
  (function(){
    const hoy = '<?= date('Y-m-d') ?>';
    const key = 'cumple_mostrado_' + hoy;

    if (!localStorage.getItem(key)) {
      localStorage.setItem(key, '1');
      if (typeof window.runBirthdayCelebration === 'function') {
        window.runBirthdayCelebration();
      }
    }
  })();
</script>
<?php endif; ?>




</body>
</html>



