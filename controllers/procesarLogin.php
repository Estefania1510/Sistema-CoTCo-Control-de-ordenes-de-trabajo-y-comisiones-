<?php
        session_start();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        require_once __DIR__ . "/../config/Conexion.php";
        require_once __DIR__ . "/../config/ConnectData.php";

        $conexion = new Conexion($conData);
        $conn = $conexion->getConnection();

        $usuario  = $_POST['usuario'];
        $password = $_POST['password'];

        $sql = "SELECT u.idUsuario, u.NombreUsuario, u.Usuario, u.Contraseña
                FROM usuario u
                WHERE u.Usuario = :usuario AND u.Estatus = 'Activo'";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['Contraseña'])) {
                $_SESSION['idUsuario'] = $user['idUsuario'];
                $_SESSION['nombre']    = $user['NombreUsuario'];

         
                $roles = [];
                $sqlRoles = "SELECT r.rol 
                             FROM usuarioroles ur
                             INNER JOIN rol r ON ur.idRol = r.idRol
                             WHERE ur.idUsuario = :idUsuario";

                $stmtRoles = $conn->prepare($sqlRoles);
                $stmtRoles->bindParam(':idUsuario', $user['idUsuario']);
                $stmtRoles->execute();

                while ($row = $stmtRoles->fetch(PDO::FETCH_ASSOC)) {
                    $roles[] = $row['rol'];
                }
                $_SESSION['roles'] = $roles;

                header("Location: ../views/principal.php");
                exit;

            } else {
                mostrarAlertaContraseña();
            }
        } else {
            mostrarAlertaUsuario();
        }

        function mostrarAlertaContraseña() {
            echo '
            <!DOCTYPE html>
            <html>
            <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                Swal.fire({
                  icon: "error",
                  title: "Contraseña Incorrecta",
                  showConfirmButton: true
                }).then(() => {
                  window.location.href = "../views/login.php";
                });
                </script>
            </body>
            </html>';
            exit;
        }


        function mostrarAlertaUsuario() {
            echo '
            <!DOCTYPE html>
            <html>
            <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                Swal.fire({
                  icon: "error",
                  title: "Usuario Incorrecto",
                  showConfirmButton: true
                }).then(() => {
                  window.location.href = "../views/login.php";
                });
                </script>
            </body>
            </html>';
            exit;
        }
