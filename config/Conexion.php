<?php
//CONEXION A LA BASE DE DATOS
    class Conexion{
        private $host;
        private $user;
        private $pass;
        private $db;
        
        public function __construct($datos){
            $this->host = $datos['host'];
            $this->user = $datos['user'];
            $this->pass = $datos['pass'];
            $this->db = $datos['db'];
        }

        function getConnection(){
            $conn = "";
            try{
                $cadena = "mysql:host=".$this->host.";dbname=".$this->db;
                $conn = new PDO($cadena, $this->user, $this->pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }catch(PDOException $e){
                echo $e->getMessage();
            }      
            return $conn;      
        }
    }
?>