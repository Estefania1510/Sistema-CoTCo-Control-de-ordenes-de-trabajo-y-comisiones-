  <?php
  session_start();
  if(isset($_SESSION['idUsuario'])){
      header("Location: principal.php");
      exit;
  }
  ?>
  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>Login - ICT</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-gray-100 flex items-center justify-center h-screen">
   <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md sm:max-w-lg md:max-w-xl">


      <div class="flex justify-center mb-4">
        <img src="../Image/ICT_AZUL.png" alt="ICT" class="h-28 w-auto">
      </div>

      <h2 class="text-2xl font-bold text-center mb-6">Iniciar Sesión</h2>

      <form method="POST" action="../controllers/procesarLogin.php" class="space-y-4">

        <div>
          <label class="block text-gray-800 text-lg font-medium">Usuario</label>
          <input type="text" name="usuario" required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
        </div>
        
        <div>
          <label class="block text-gray-800 text-lg font-medium">Contraseña</label>
          <input type="password" name="password" required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
        </div>
        <button type="submit"
          class="w-full bg-blue-600 text-white py-3 text-lg font-semibold rounded-lg hover:bg-blue-700 transition">
          Ingresar
        </button>

      </form>
    </div>
  </body>
  </html>
