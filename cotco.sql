CREATE DATABASE  IF NOT EXISTS `cotco` /*!40100 DEFAULT CHARACTER SET utf8mb3 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cotco`;
-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: localhost    Database: cotco
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auxservicios`
--

DROP TABLE IF EXISTS `auxservicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auxservicios` (
  `idAuxServicios` int NOT NULL AUTO_INCREMENT,
  `idMantenimiento` int NOT NULL,
  `idCatalogoMnt` int NOT NULL,
  `Precio` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`idAuxServicios`),
  KEY `fk_AuxServicios_NotaMantenimiento1_idx` (`idMantenimiento`),
  KEY `fk_AuxServicios_CatalogoMnt1_idx` (`idCatalogoMnt`),
  CONSTRAINT `fk_AuxServicios_CatalogoMnt1` FOREIGN KEY (`idCatalogoMnt`) REFERENCES `catalogomnt` (`idCatalogoMnt`),
  CONSTRAINT `fk_AuxServicios_NotaMantenimiento1` FOREIGN KEY (`idMantenimiento`) REFERENCES `notamantenimiento` (`idMantenimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auxservicios`
--

LOCK TABLES `auxservicios` WRITE;
/*!40000 ALTER TABLE `auxservicios` DISABLE KEYS */;
INSERT INTO `auxservicios` VALUES (1,8,7,0.00),(2,9,19,0.00),(3,10,7,0.00),(4,10,18,0.00),(5,10,19,0.00),(6,11,3,0.00),(7,12,6,0.00),(8,13,24,0.00),(9,13,25,0.00),(10,13,26,0.00),(11,14,18,0.00),(12,14,2,0.00),(13,15,25,0.00),(14,16,16,0.00),(15,18,6,0.00),(16,19,24,0.00),(17,19,15,0.00),(18,19,26,0.00),(19,20,15,0.00),(20,21,24,0.00),(21,22,24,0.00),(41,26,19,0.00),(42,26,21,0.00),(69,24,15,100.00),(70,24,24,100.00),(79,27,24,0.00),(80,27,18,0.00),(81,27,6,500.00),(82,27,24,1.00),(83,27,19,2.00),(84,25,18,400.00),(85,25,20,0.00),(86,25,19,100.00),(90,29,11,0.00),(91,28,24,0.00),(92,31,24,100.00),(93,31,18,100.00),(94,30,6,0.00),(97,32,25,100.00),(99,33,6,500.00),(100,34,6,150.00),(101,35,9,200.00),(105,36,19,200.00),(106,38,12,100.00),(107,37,15,100.00),(134,43,7,200.00),(143,44,6,200.00),(144,40,7,200.00),(146,45,6,200.00),(147,46,7,100.00),(148,47,11,100.00),(149,48,10,200.00),(151,50,18,0.00),(160,51,7,100.00),(163,56,20,400.00),(164,41,25,150.00),(165,55,15,200.00),(166,55,18,100.00),(167,54,18,400.00),(168,49,15,200.00),(187,59,8,400.00),(188,59,18,500.00),(189,57,18,150.00),(195,60,7,150.00),(196,60,20,400.00);
/*!40000 ALTER TABLE `auxservicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogomnt`
--

DROP TABLE IF EXISTS `catalogomnt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogomnt` (
  `idCatalogoMnt` int NOT NULL AUTO_INCREMENT,
  `idTipoMnt` int NOT NULL,
  `Servicio` varchar(100) NOT NULL,
  PRIMARY KEY (`idCatalogoMnt`),
  KEY `idTipoMnt` (`idTipoMnt`),
  CONSTRAINT `catalogomnt_ibfk_1` FOREIGN KEY (`idTipoMnt`) REFERENCES `tipomantenimiento` (`idTipoMnt`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogomnt`
--

LOCK TABLES `catalogomnt` WRITE;
/*!40000 ALTER TABLE `catalogomnt` DISABLE KEYS */;
INSERT INTO `catalogomnt` VALUES (1,1,'Limpieza interna y externa del hardware '),(2,1,'Actualización de software y sistemas operativos'),(3,1,'Revisión de cables, conexiones y periféricos'),(4,1,'Escaneo antivirus y eliminación de malware'),(5,1,'Comprobación del estado del disco duro y respaldos de información'),(6,2,'Reparación o reemplazo de componentes dañados '),(7,2,'Reinstalación del sistema operativo'),(8,2,'Eliminación de virus o recuperación del sistema'),(9,2,'Solución de errores de software o controladores'),(10,3,'Revisión de temperaturas, consumo de energía y rendimiento del CPU'),(11,3,'Análisis del estado del disco duro'),(12,3,'Supervisión del desempeño de la red y el sistema operativo'),(13,3,'Reportes de desgaste o fallas inminentes'),(14,4,'Ampliación de memoria RAM o almacenamiento'),(15,4,'Sustitución de componentes por versiones más nuevas'),(16,4,'Instalación de nuevas versiones de software o sistemas operativos'),(17,4,'Optimización de configuraciones para mejor desempeño'),(18,5,'Microsoft Office'),(19,5,'Antivirus'),(20,5,'Microsoft Windows'),(21,5,'Suite Adobe'),(22,5,'Autodesk'),(23,5,'Utilerías'),(24,6,'Cambio de almohadillas'),(25,6,'Limpieza de cabezales'),(26,6,'Configuración e instalación en PC');
/*!40000 ALTER TABLE `catalogomnt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente` (
  `idCliente` int NOT NULL AUTO_INCREMENT,
  `NombreCliente` varchar(50) NOT NULL,
  `Direccion` varchar(50) NOT NULL,
  `Telefono` varchar(12) NOT NULL,
  `Telefono2` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`idCliente`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (1,'Fernanda Campos','Laurel 53','33215484',NULL),(2,'Sebastian Castro','Carranza 25','3325488545','1111111111'),(3,'Fernando Blanco','Topos 20','35214587',''),(5,'Miguel Hidalgo','Roble 9','1234567891','222222222'),(6,'Fabiola Dueñas','Lucio Blanco 85','5263845236',''),(7,'Pedro Moreno','Emiliano Zapata 8','1122558877',''),(8,'Michelle Rodriguez','Laurel 53','3321477424',''),(9,'jaquie','Ferrocarril 25','3322551565',''),(10,'Kassandra Elizabeth','Lopez Mateos 52','3321477452',''),(11,'','','',''),(12,'Whisky','Matamoros','3356847525',''),(13,'Melina Barrios','Emiliano Zapata #15, San simon','3319537015',''),(14,'Fernando Blanco','Topos 20','3521458785',''),(15,'Fernando Andrade','Hidalgo poniente 129','3861052396',''),(16,'Miguel Hidalgo Occidente','roble 55','111111111111','222222222222'),(17,'Fabiola Gonzalez','Lucio Blanco 40','3321477424','123456789'),(18,'Fabiola camacho Rodriguez','Lucio Blanco 40','222222222222','111111111111'),(19,'ESTEFANIA CAMACHO','Laurel 53','3321477424',''),(20,'AXEL','SN','3861052165',''),(21,'estefania camacho','Laurel 53','3321477424',''),(22,'efrain camacho rodriguez','no se','332145242',''),(23,'Maria de Lourdes Valenzuela ','Ejido 55','3321458785','3215489563'),(24,'Maria Guadalupe Carranza','Platon 30','3254895235','4567891235'),(25,'Jose Maria Garnica','Cedro 89','3256987452',NULL);
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comisiones`
--

DROP TABLE IF EXISTS `comisiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comisiones` (
  `idComisiones` int NOT NULL AUTO_INCREMENT,
  `tipo` enum('Diseño','Mantenimiento') NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  `fechapago` date DEFAULT NULL,
  `monto` decimal(5,2) NOT NULL,
  `estado` enum('Pagado','Orden Entregada','Orden no Entregada','Orden Cancelada') NOT NULL,
  `idUsuario` int NOT NULL,
  `idnota` int NOT NULL,
  PRIMARY KEY (`idComisiones`),
  KEY `fk_Comisiones_Usuario1_idx` (`idUsuario`),
  KEY `fk_Nota_idnota_idx` (`idnota`),
  CONSTRAINT `fk_comisiones_Usuario1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  CONSTRAINT `fk_Nota_idnota` FOREIGN KEY (`idnota`) REFERENCES `nota` (`idNota`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comisiones`
--

LOCK TABLES `comisiones` WRITE;
/*!40000 ALTER TABLE `comisiones` DISABLE KEYS */;
INSERT INTO `comisiones` VALUES (1,'Mantenimiento',30.00,'2025-11-06',50.00,'Pagado',3,84),(2,'Diseño',40.00,'2025-11-06',60.00,'Pagado',2,85),(3,'Diseño',40.00,'2025-11-06',80.00,'Pagado',2,86),(4,'Mantenimiento',30.00,'2025-11-06',150.00,'Pagado',3,87),(5,'Mantenimiento',30.00,'2025-11-07',45.00,'Pagado',3,88),(6,'Mantenimiento',30.00,NULL,60.00,'Orden no Entregada',7,89),(7,'Mantenimiento',30.00,'2025-11-07',60.00,'Pagado',3,90),(8,'Diseño',30.00,'2025-11-07',45.00,'Pagado',5,91),(9,'Diseño',30.00,'2025-11-07',45.00,'Pagado',5,92),(10,'Diseño',30.00,'2025-11-07',45.00,'Pagado',3,93),(11,'Mantenimiento',30.00,'2025-11-07',30.00,'Pagado',3,94),(12,'Mantenimiento',30.00,'2025-11-07',30.00,'Pagado',3,95),(13,'Diseño',30.00,'2025-11-13',45.00,'Pagado',5,96),(14,'Mantenimiento',50.00,'2025-11-13',100.00,'Pagado',3,98),(15,'Diseño',30.00,NULL,75.00,'Orden no Entregada',4,99),(16,'Mantenimiento',30.00,NULL,45.00,'Orden Cancelada',3,100),(17,'Mantenimiento',30.00,NULL,60.00,'Orden no Entregada',1,103),(18,'Mantenimiento',30.00,NULL,60.00,'Orden no Entregada',3,104),(19,'Diseño',30.00,'2025-11-29',90.00,'Pagado',5,105),(20,'Diseño',30.00,NULL,75.00,'Orden no Entregada',6,107),(21,'Mantenimiento',30.00,'2025-11-26',30.00,'Pagado',3,108),(22,'Mantenimiento',30.00,NULL,60.00,'Orden no Entregada',3,109),(23,'Mantenimiento',30.00,NULL,60.00,'Orden no Entregada',1,110),(24,'Diseño',30.00,NULL,75.00,'Orden no Entregada',5,112),(25,'Diseño',30.00,NULL,75.00,'Orden no Entregada',5,113),(26,'Diseño',30.00,NULL,75.00,'Orden no Entregada',6,114),(27,'Diseño',30.00,'2025-11-20',60.00,'Pagado',2,115),(28,'Diseño',30.00,'2025-11-21',105.00,'Pagado',5,116),(29,'Diseño',30.00,NULL,75.00,'Orden Cancelada',5,118),(30,'Mantenimiento',30.00,NULL,30.00,'Orden no Entregada',1,119),(31,'Mantenimiento',30.00,NULL,120.00,'Orden Cancelada',3,123),(32,'Diseño',30.00,NULL,75.00,'Orden no Entregada',5,125),(33,'Diseño',30.00,NULL,90.00,'Orden no Entregada',5,126),(34,'Diseño',30.00,'2025-11-21',45.00,'Pagado',2,127),(35,'Diseño',30.00,'2025-11-22',45.00,'Pagado',2,128),(36,'Diseño',30.00,NULL,75.00,'Orden Cancelada',5,129),(37,'Diseño',30.00,NULL,120.00,'Orden Cancelada',5,131),(38,'Diseño',30.00,'2025-11-22',45.00,'Pagado',2,133),(39,'Mantenimiento',30.00,NULL,45.00,'Orden no Entregada',3,132),(40,'Mantenimiento',30.00,NULL,90.00,'Orden Entregada',3,134),(41,'Diseño',30.00,NULL,45.00,'Orden no Entregada',5,136),(42,'Mantenimiento',30.00,NULL,270.00,'Orden Entregada',8,135),(43,'Diseño',30.00,NULL,0.00,'Orden no Entregada',5,137),(44,'Diseño',30.00,NULL,0.00,'Orden no Entregada',8,138),(45,'Diseño',30.00,NULL,45.00,'Orden Entregada',5,139),(46,'Mantenimiento',30.00,NULL,165.00,'Orden no Entregada',3,140);
/*!40000 ALTER TABLE `comisiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configcomision`
--

DROP TABLE IF EXISTS `configcomision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configcomision` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombreajuste` varchar(50) NOT NULL,
  `valor` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombreajuste` (`nombreajuste`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configcomision`
--

LOCK TABLES `configcomision` WRITE;
/*!40000 ALTER TABLE `configcomision` DISABLE KEYS */;
INSERT INTO `configcomision` VALUES (1,'porcentaje','30');
/*!40000 ALTER TABLE `configcomision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `licenciasoftware`
--

DROP TABLE IF EXISTS `licenciasoftware`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `licenciasoftware` (
  `idLS` int NOT NULL AUTO_INCREMENT,
  `Licencia` varchar(100) DEFAULT NULL,
  `Software` varchar(30) DEFAULT NULL,
  `Estatus` enum('Instalada','Libre','Baja') DEFAULT 'Libre',
  `Password` varchar(30) DEFAULT NULL,
  `Equipo` varchar(100) DEFAULT NULL,
  `Procesador` varchar(100) DEFAULT NULL,
  `IdDispositivo` varchar(100) DEFAULT NULL,
  `IdProducto` varchar(100) DEFAULT NULL,
  `Fecha` date DEFAULT NULL,
  `idCliente` int DEFAULT NULL,
  `idNota` int DEFAULT NULL,
  PRIMARY KEY (`idLS`),
  KEY `fk_licencia_cliente` (`idCliente`),
  KEY `fk_licencia_nota_idx` (`idNota`),
  CONSTRAINT `fk_licencia_cliente` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_licencia_nota` FOREIGN KEY (`idNota`) REFERENCES `nota` (`idNota`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licenciasoftware`
--

LOCK TABLES `licenciasoftware` WRITE;
/*!40000 ALTER TABLE `licenciasoftware` DISABLE KEYS */;
/*!40000 ALTER TABLE `licenciasoftware` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logerror`
--

DROP TABLE IF EXISTS `logerror`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logerror` (
  `idLog` int NOT NULL AUTO_INCREMENT,
  `metodo` varchar(100) NOT NULL,
  `excepcion` varchar(100) NOT NULL,
  PRIMARY KEY (`idLog`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logerror`
--

LOCK TABLES `logerror` WRITE;
/*!40000 ALTER TABLE `logerror` DISABLE KEYS */;
INSERT INTO `logerror` VALUES (3,'procesarOrdenDiseno','SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'Anticipo\' at row 1'),(4,'procesarOrdenDiseno','SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens'),(5,'procesarOrdenDiseno','SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'Precio\' at row 1'),(6,'procesarOrdenDiseno','SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'Precio\' at row 1'),(7,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(8,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(9,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(10,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(11,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(12,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(13,'procesarOrdenMantenimiento','SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens'),(14,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'fdssdfsdf\''),(15,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'hhghgffggfh\''),(16,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'fdssdfsdf\''),(17,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'fdssdfsdf\''),(18,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'fdssdfsdf\''),(19,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'c.fechaentrega\' in \'where clause\''),(20,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'c.fechaentrega\' in \'where clause\''),(21,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'c.fechaentrega\' in \'where clause\''),(22,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(23,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(24,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(25,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(26,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(27,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(28,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(29,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(30,'procesarOrdenDiseno','SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column \'Telefono\' at row 1'),(31,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'cli.NombreCliente\' in \'field list\''),(32,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'cli.NombreCliente\' in \'field list\''),(33,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'cli.NombreCliente\' in \'field list\''),(34,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'cli.NombreCliente\' in \'field list\'');
/*!40000 ALTER TABLE `logerror` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material`
--

DROP TABLE IF EXISTS `material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material` (
  `idMaterial` int NOT NULL AUTO_INCREMENT,
  `Material` varchar(100) NOT NULL,
  `Cantidad` int NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  `idDiseño` int NOT NULL,
  PRIMARY KEY (`idMaterial`),
  KEY `fk_Material_NotaDiseño1_idx` (`idDiseño`),
  CONSTRAINT `fk_Material_NotaDiseño1` FOREIGN KEY (`idDiseño`) REFERENCES `notadiseño` (`idDiseño`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material`
--

LOCK TABLES `material` WRITE;
/*!40000 ALTER TABLE `material` DISABLE KEYS */;
INSERT INTO `material` VALUES (2,'Couche Tabloide',5,30.00,150.00,3),(9,'Couche Tabloide',30,30.00,900.00,7),(10,'Lona 1x1',2,200.00,400.00,7),(11,'Couche Tabloide',30,30.00,900.00,8),(12,'Lona 1x1',2,200.00,400.00,8),(13,'Couche Tabloide',30,30.00,900.00,9),(14,'Lona 1x1',2,200.00,400.00,9),(15,'LONA 2X5',1,500.00,500.00,10),(16,'couche tabloide',2,30.00,60.00,10),(17,'LONA 2X5',1,500.00,500.00,11),(18,'couche tabloide',2,30.00,60.00,11),(19,'LONA 2X5',1,500.00,500.00,12),(20,'couche tabloide',2,30.00,60.00,12),(21,'LONA 2X5',1,500.00,500.00,13),(22,'couche tabloide',2,30.00,60.00,13),(23,'couche tabloide',2,30.00,60.00,14),(25,'couche tabloide',0,0.00,0.00,16),(28,'LONA 2X5',1,500.00,500.00,19),(30,'cortplast 80x5',1,5.00,5.00,21),(31,'couche tabloide',2,0.00,0.00,22),(80,'Lona 5x3',2,1.00,2.00,25),(81,'Vinil dorado 15x20cm',1,2.00,2.00,25),(83,'Couche Tabloide',5,30.00,150.00,5),(84,'Lona 1x1',2,300.00,600.00,5),(86,'carte 5x1',1,200.00,200.00,27),(87,'MILLAR ',2,300.00,600.00,27),(90,'LONA 2X5',1,500.00,500.00,26),(91,'LONA 2X5',1,1.00,1.00,28),(109,'vinil dorado 30x20',1,100.00,100.00,29),(114,'lona',1,200.00,200.00,32),(115,'prueba',1,100.00,100.00,30),(116,'tarjeta de presenctacion',1,800.00,800.00,33),(117,'LONA 2X5',1,200.00,200.00,33),(119,'PRUEBA',1,100.00,100.00,34),(130,'PRUEBA',1,0.00,0.00,37),(147,'prueba',1,0.00,0.00,38),(149,'prueba',1,0.00,0.00,39),(150,'lona',1,200.00,200.00,23),(151,'tarjeta de presenctacion',1,700.00,700.00,23),(153,'vinil dorado',1,0.00,0.00,20),(154,'couche tabloide',1,1000.00,1000.00,42),(155,'Invitacion digital',1,300.00,300.00,15),(156,'Couche',1,10.00,10.00,15),(157,'couche tabloide',5,50.00,250.00,18),(158,'lona',1,20.00,20.00,18),(159,'cortplast 80x5',1,5.00,5.00,17),(160,'Couche Tabloide',5,30.00,150.00,6),(161,'Lona 1x1',2,200.00,400.00,6),(162,'Couche Tabloide',5,30.00,150.00,4),(163,'Lona 1x1',2,200.00,400.00,4),(166,'PRUEBA',1,100.00,100.00,31),(170,'',0,0.00,0.00,44),(171,'ddd',0,0.00,0.00,45),(172,'prueba',1,0.00,0.00,46),(174,'LONA',1,1.00,1.00,47),(180,'Prueba',1,1.00,1.00,48),(181,'Lona',2,200.00,400.00,48),(187,'Couche Tabloide',5,30.00,150.00,1),(188,'PRUEBA',1,1.00,1.00,43),(189,'FF',1,1.00,1.00,43),(190,'df',1,3.00,3.00,43),(191,'PRUEBA',1,0.00,0.00,35),(192,'lona 1x1',1,200.00,200.00,49),(195,'lona',1,100.00,100.00,50),(196,'lona',1,100.00,100.00,51),(200,'LONA',1,100.00,100.00,52),(203,'PRUEBA',1,1000.00,1000.00,54),(204,'MILLAR',1,1000.00,1000.00,53),(215,'lona',1,100.00,100.00,55),(219,'prueba',1,100.00,100.00,56),(221,'PRUEBA',1,100.00,100.00,36),(223,'lona',1,100.00,100.00,58),(226,'couche tabloide',1,100.00,100.00,61),(228,'Lona',1,200.00,200.00,63),(229,'ma',1,1.00,1.00,64),(231,'f',0,0.00,0.00,66),(232,'LONA',1,100.00,100.00,67),(233,'lona',1,100.00,100.00,68),(235,'couche tabloide',15,30.00,450.00,69),(236,'lona',1,100.00,100.00,62),(242,'prueba',1,100.00,100.00,57),(245,'lona',1,100.00,100.00,59),(246,'lona',1,100.00,100.00,60),(247,'ads',1,100.00,100.00,65),(248,'lona',1,100.00,100.00,71),(253,'PACO',1,200.00,200.00,72),(255,'LONA',1,100.00,100.00,73),(258,'lona',1,100.00,100.00,70),(259,'COUCHE',5,30.00,150.00,74),(260,'digital',1,150.00,150.00,75),(263,'Couche Tabloide',1,30.00,30.00,76),(267,'lona 1x1',1,200.00,200.00,77),(268,'lona 2x2',1,300.00,300.00,77),(269,'couche tabloide',1,30.00,30.00,24),(270,'Invitacion digital',1,100.00,100.00,24);
/*!40000 ALTER TABLE `material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nota`
--

DROP TABLE IF EXISTS `nota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nota` (
  `idNota` int NOT NULL AUTO_INCREMENT,
  `FechaRecepcion` date NOT NULL,
  `FechaEntrega` date DEFAULT NULL,
  `Total` decimal(10,2) NOT NULL,
  `Anticipo` decimal(10,2) DEFAULT NULL,
  `Resto` decimal(10,2) NOT NULL,
  `Descripcion` varchar(250) DEFAULT NULL,
  `Comentario` varchar(250) DEFAULT NULL,
  `idUsuario` int NOT NULL,
  `idCliente` int NOT NULL,
  PRIMARY KEY (`idNota`),
  KEY `fk_Nota_Usuario1_idx` (`idUsuario`),
  KEY `fk_Nota_Cliente1_idx` (`idCliente`),
  CONSTRAINT `fk_Nota_Cliente1` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`),
  CONSTRAINT `fk_Nota_Usuario1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nota`
--

LOCK TABLES `nota` WRITE;
/*!40000 ALTER TABLE `nota` DISABLE KEYS */;
INSERT INTO `nota` VALUES (3,'2025-10-07',NULL,162.00,150.00,12.00,'Invitacion cumpleaños','Transfirio',1,2),(4,'2025-10-07','2025-10-07',0.00,0.00,0.00,'',NULL,1,5),(5,'2025-10-08','2025-10-08',0.00,150.00,0.00,'Diseño en whatsap','Transfirio',1,2),(6,'2025-10-08',NULL,600.00,350.00,250.00,'Invitacion Cumpleaños','Transfirio anticipo',1,3),(7,'2025-10-08','2025-10-10',0.00,350.00,0.00,'Invitacion Cumpleaños hola','Transfirio anticipo',1,3),(8,'2025-10-08','2025-10-08',600.00,700.00,0.00,'ssdsffds','',1,3),(9,'2025-10-08','2025-10-08',0.00,500.00,0.00,'IMAGEN WHATSAP','',1,2),(10,'2025-10-08','2025-10-08',0.00,500.00,0.00,'IMAGEN WHATSAP','',1,2),(11,'2025-10-08','2025-10-08',0.00,500.00,0.00,'IMAGEN WHATSAP','',1,2),(12,'2025-10-08','2025-10-08',0.00,700.00,0.00,'Whatsaap','',1,3),(13,'2025-10-08','2025-10-08',0.00,700.00,0.00,'Whatsaap','',1,3),(14,'2025-10-08','2025-10-08',0.00,700.00,0.00,'Whatsaap','',1,3),(15,'2025-10-08','2025-10-08',0.00,700.00,0.00,'Whatsaap','',1,3),(16,'2025-10-08','2025-10-08',210.00,100.00,110.00,'Invitacion paw patrol','',1,2),(17,'2025-10-08','2025-10-08',410.00,150.00,260.00,'Video Invitacion','Transfirio',2,6),(18,'2025-10-08','2025-10-08',650.00,300.00,350.00,'invitacion','transfirio',2,5),(19,'2025-10-09',NULL,105.00,50.00,55.00,'diseño de imagen de stitch','transfirio',1,7),(20,'2025-10-09',NULL,470.00,400.00,70.00,'invitacion de agraristas, lo quiere del tamaño 8x5','pago total',1,5),(21,'2025-10-09','2025-10-09',650.00,0.00,650.00,'Hola','',1,2),(22,'2025-10-09',NULL,0.00,0.00,0.00,'letras en vinil FELIZ CUMPLEAÑOS color dorado 4 x 30','',1,5),(23,'2025-10-09','2025-10-09',155.00,200.00,-45.00,'fdssdf','Transfirio',1,6),(24,'2025-10-09','2025-10-09',0.00,0.00,0.00,'fds','',1,5),(25,'2025-10-09',NULL,1800.00,100.00,1700.00,'invitacion','56',3,7),(26,'2025-10-09',NULL,230.00,50.00,180.00,'prueba edicion ffd','transfirio',3,6),(27,'2025-10-10','2025-10-10',900.00,300.00,450.00,'Vinil dorado \"Feliz Cumpleaños\" 15x20cm dffffhola','Trasferencia',2,8),(28,'2025-10-10','2025-10-10',400.00,200.00,200.00,'Instalar Office',NULL,3,8),(29,'2025-10-10','2025-10-10',650.00,500.00,150.00,'Invitacion diseño en whats','Transfirio',2,9),(30,'2025-10-10','2025-10-10',950.00,500.00,450.00,'diseño en whats','',3,3),(31,'2025-10-14','2025-10-14',1.00,5.00,-4.00,'PRUEBAA','',1,7),(32,'2025-10-14','2025-10-14',250.00,100.00,150.00,'Invitacion agrgaristas','',1,8),(33,'2025-10-14','2025-10-14',250.00,100.00,150.00,'prueba2','',1,1),(34,'2025-10-14','2025-10-16',250.00,100.00,150.00,'prueba','TRANSFIRIO ANTICIPO',1,5),(35,'2025-10-14','2025-10-14',350.00,100.00,250.00,'invitacion','',1,9),(36,'2025-10-14','2025-10-15',1200.00,600.00,600.00,'PRUEBA','',1,10),(37,'2025-10-15','2025-10-15',200.00,50.00,150.00,'PRUEBA','',1,10),(38,'2025-10-15','2025-10-29',0.00,0.00,0.00,'PRUEBA2','',1,2),(39,'2025-10-15','2025-10-09',100.00,0.00,100.00,'prueba','',1,6),(40,'2025-10-15','2025-10-15',0.00,0.00,0.00,'DSF','',1,1),(41,'2025-10-15',NULL,0.00,0.00,0.00,'prueba','',1,5),(42,'2025-10-15',NULL,0.00,0.00,0.00,'pendiente','',1,10),(45,'2025-10-15',NULL,1150.00,500.00,650.00,'prueba','',1,7),(48,'2025-10-16',NULL,10.00,2.00,8.00,'PRUEBAfdsds','',3,8),(50,'2025-10-21',NULL,0.00,0.00,0.00,'DDDDD','',1,1),(51,'2025-10-21',NULL,0.00,0.00,0.00,'ddd','',1,12),(55,'2025-10-23',NULL,0.00,0.00,0.00,'prueba','',3,1),(56,'2025-10-23',NULL,200.00,100.00,100.00,'Esta lenta, no funciona el teclado','',3,5),(57,'2025-10-23',NULL,100.00,50.00,50.00,'NO ENCIENDE','',3,2),(58,'2025-10-23',NULL,780.00,500.00,280.00,'Esta muy lenta necesita formateo, office y antivirus','',3,10),(59,'2025-10-23',NULL,0.00,0.00,0.00,'','',3,12),(60,'2025-10-23',NULL,150.00,100.00,50.00,'','',2,5),(61,'2025-10-23',NULL,0.00,0.00,0.00,'','',2,5),(62,'2025-10-23',NULL,600.00,0.00,600.00,'esta lenta, office','',2,12),(63,'2025-10-23',NULL,100.00,200.00,-100.00,'','',2,10),(64,'2025-10-23',NULL,500.00,0.00,500.00,'otra prueba','',2,3),(65,'2025-10-23',NULL,1.00,0.00,1.00,'sdsffsd','',2,1),(66,'2025-10-24',NULL,402.00,500.00,-98.00,'Prueba','',4,8),(68,'2025-10-24',NULL,0.00,0.00,0.00,'PRUEBA67','SUGERENCIA',1,9),(69,'2025-10-24',NULL,1.00,0.00,1.00,'','',1,8),(70,'2025-10-24',NULL,300.00,150.00,150.00,'no enciende esta lenta','',1,2),(71,'2025-10-24',NULL,0.00,0.00,0.00,'d','',1,7),(72,'2025-10-24',NULL,250.00,100.00,150.00,'otra prueba','otra prueba',1,2),(73,'2025-10-24',NULL,100.00,50.00,50.00,'7','8',1,6),(74,'2025-10-24',NULL,0.00,0.00,0.00,'7','8',1,10),(75,'2025-10-24',NULL,200.00,600.00,-400.00,'no enciende','',2,7),(76,'2025-10-25',NULL,500.00,0.00,500.00,'','',1,8),(77,'2025-10-25',NULL,0.00,0.00,0.00,'SE LES OLVIDO LA CONTRASEÑA, FORMATEAR SIN RESPALDO\r\nsoftware','',1,13),(78,'2025-10-25','2025-10-29',503.00,0.00,503.00,'no enciendeczx','',1,13),(79,'2025-10-25',NULL,350.00,100.00,250.00,'esta en whats','Transfirio',1,5),(80,'2025-10-29','2025-11-06',0.00,0.00,0.00,'dsf','sdf',3,3),(81,'2025-10-31',NULL,0.00,0.00,0.00,'','',1,3),(82,'2025-10-31','2025-11-06',0.00,0.00,0.00,'','',1,5),(83,'2025-10-31','2025-11-06',200.00,50.00,150.00,'','',1,3),(84,'2025-11-06','2025-11-06',100.00,50.00,50.00,'','',1,5),(85,'2025-11-06','2025-11-06',250.00,30.00,220.00,'JKDFAKLJDAS','',1,9),(86,'2025-11-06','2025-11-06',300.00,0.00,300.00,'hola','',1,9),(87,'2025-11-06','2025-11-06',500.00,0.00,500.00,'','',1,7),(88,'2025-11-06',NULL,150.00,100.00,50.00,'prueba','',1,14),(89,'2025-11-06',NULL,200.00,100.00,100.00,'','',1,14),(90,'2025-11-06','2025-11-07',200.00,0.00,200.00,'','',1,8),(91,'2025-11-06','2025-11-07',250.00,0.00,250.00,'PRUEBA','',1,12),(92,'2025-11-06','2025-11-12',1150.00,0.00,1150.00,'PRUEBA','',1,7),(93,'2025-11-06',NULL,1150.00,0.00,1150.00,'PRUEBA','',1,10),(94,'2025-11-07','2025-11-07',100.00,0.00,100.00,'','',1,3),(95,'2025-11-07','2025-11-07',100.00,0.00,100.00,'','',1,5),(96,'2025-11-07','2025-11-07',250.00,0.00,250.00,'prueba','',1,8),(97,'2025-11-12',NULL,0.00,0.00,0.00,'','',1,5),(98,'2025-11-12',NULL,200.00,100.00,100.00,'','',1,8),(99,'2025-11-12',NULL,250.00,100.00,150.00,'prueba','',1,5),(100,'2025-11-12',NULL,150.00,50.00,100.00,'','',1,12),(101,'2025-11-12',NULL,0.00,100.00,-100.00,'ddd','',1,8),(102,'2025-11-12',NULL,200.00,100.00,100.00,'','',1,8),(103,'2025-11-12',NULL,200.00,50.00,150.00,'','',1,9),(104,'2025-11-13',NULL,200.00,50.00,150.00,'','',1,5),(105,'2025-11-13','2025-11-21',300.00,0.00,300.00,'prueba','',1,9),(106,'2025-11-13',NULL,100.00,0.00,100.00,'','',1,7),(107,'2025-11-13',NULL,250.00,0.00,250.00,'prueba','',1,3),(108,'2025-11-13',NULL,100.00,0.00,100.00,'','',1,6),(109,'2025-11-13',NULL,200.00,0.00,200.00,'prueba','',1,2),(110,'2025-11-13',NULL,200.00,0.00,200.00,'','',1,7),(111,'2025-11-14',NULL,0.00,0.00,0.00,'','',1,15),(112,'2025-11-14',NULL,250.00,0.00,250.00,'dasssss','',1,16),(113,'2025-11-14',NULL,250.00,0.00,250.00,'cvxcv','',1,16),(114,'2025-11-14',NULL,250.00,100.00,150.00,'dsdsfffd','',1,17),(115,'2025-11-14','2025-11-19',300.00,150.00,150.00,'cvxxc','',1,18),(116,'2025-11-14',NULL,350.00,200.00,150.00,'ddsa','',1,18),(117,'2025-11-14',NULL,151.00,1.00,150.00,'sda','',1,17),(118,'2025-11-14',NULL,250.00,100.00,150.00,'das','',1,16),(119,'2025-11-14',NULL,100.00,0.00,100.00,'','',1,18),(120,'2025-11-14',NULL,0.00,0.00,0.00,'ddssd','',1,6),(121,'2025-11-14',NULL,0.00,0.00,0.00,'','',1,9),(122,'2025-11-14',NULL,0.00,0.00,0.00,'','',1,12),(123,'2025-11-14',NULL,400.00,50.00,350.00,'jjj','',1,17),(124,'2025-11-14',NULL,300.00,1.00,299.00,'dssd','',1,12),(125,'2025-11-19',NULL,250.00,0.00,250.00,'COMSION','',1,3),(126,'2025-11-19',NULL,300.00,100.00,200.00,'gfdf','',1,10),(127,'2025-11-20',NULL,600.00,300.00,300.00,'INVITACIÓN DE LAS GUERRERAS K-POP\r\nGAEL CAMACHO\r\n8 AÑOS\r\nDOMICILIO LAUREL #53 A LAS 5:00PM\r\nLLEVEN BEBIDA\r\nNOTA: LLEVAR PIÑATAS DE LOS PERSONAJES\r\n100 INVITACIONES DE 10X15CM','TRANFERENCIA DE ANTICIPO',2,19),(128,'2025-11-20','2025-11-22',250.00,0.00,250.00,'sdfsfd','',1,19),(129,'2025-11-20',NULL,250.00,0.00,250.00,'sdds','',1,6),(130,'2025-11-21',NULL,400.00,200.00,200.00,'','',1,10),(131,'2025-11-21',NULL,400.00,0.00,400.00,'HOLA BEBE','',1,19),(132,'2025-11-21',NULL,150.00,0.00,150.00,'','',1,3),(133,'2025-11-21',NULL,250.00,10.00,240.00,'SFDFDS','',1,19),(134,'2025-11-21','2025-11-26',300.00,0.00,300.00,'ee','',1,9),(135,'2025-11-22','2025-11-22',900.00,0.00,900.00,'NO INGRESA A WIN\r\nd\r\nd\r\nd\r\nd\r\nd\r\ndd','d\r\nd\r\nd\r\nd\r\nd\r\nd',1,20),(136,'2025-11-22',NULL,300.00,150.00,150.00,'PAW PATROL\r\nIVAN 5 AÑOS\r\ndd\r\ndd\r\nd\r\nd\r\ndd','TRANSFIRIO ANTICIPO',1,19),(137,'2025-11-25',NULL,150.00,100.00,50.00,'dsdsa','',5,19),(138,'2025-11-25',NULL,30.00,0.00,30.00,'sdsd','',1,21),(139,'2025-11-29','2025-12-02',650.00,200.00,450.00,'se vende\r\nterreno\r\n332124554','',1,22),(140,'2025-12-04',NULL,550.00,250.00,300.00,'La laptop esta lenta y necesita activacion de office','',1,24);
/*!40000 ALTER TABLE `nota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notadiseño`
--

DROP TABLE IF EXISTS `notadiseño`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notadiseño` (
  `idDiseño` int NOT NULL AUTO_INCREMENT,
  `estatus` enum('Proceso','EnviadoTequila','Avisado','Entregado','Cancelado','Retrasado') NOT NULL,
  `CostoDiseño` decimal(10,2) DEFAULT NULL,
  `idNota` int NOT NULL,
  `idDiseñador` int DEFAULT NULL,
  PRIMARY KEY (`idDiseño`),
  KEY `fk_NotaDiseño_Nota1_idx` (`idNota`),
  KEY `fk_NotaDiseño_UsuarioTrabaja` (`idDiseñador`),
  CONSTRAINT `fk_NotaDiseño_Nota1` FOREIGN KEY (`idNota`) REFERENCES `nota` (`idNota`),
  CONSTRAINT `fk_NotaDiseño_UsuarioTrabaja` FOREIGN KEY (`idDiseñador`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notadiseño`
--

LOCK TABLES `notadiseño` WRITE;
/*!40000 ALTER TABLE `notadiseño` DISABLE KEYS */;
INSERT INTO `notadiseño` VALUES (1,'Retrasado',12.00,3,3),(2,'Retrasado',NULL,4,NULL),(3,'Retrasado',NULL,5,2),(4,'EnviadoTequila',50.00,6,NULL),(5,'Entregado',100.00,7,NULL),(6,'Retrasado',50.00,8,NULL),(7,'Retrasado',NULL,9,2),(8,'Retrasado',NULL,10,2),(9,'Retrasado',NULL,11,2),(10,'Retrasado',NULL,12,2),(11,'EnviadoTequila',NULL,13,2),(12,'EnviadoTequila',NULL,14,2),(13,'EnviadoTequila',NULL,15,NULL),(14,'Retrasado',NULL,16,2),(15,'EnviadoTequila',100.00,17,NULL),(16,'EnviadoTequila',NULL,18,2),(17,'EnviadoTequila',100.00,19,NULL),(18,'Entregado',200.00,20,NULL),(19,'Entregado',NULL,21,NULL),(20,'EnviadoTequila',0.00,22,2),(21,'Entregado',NULL,23,NULL),(22,'Entregado',NULL,24,NULL),(23,'Avisado',900.00,25,2),(24,'EnviadoTequila',100.00,26,NULL),(25,'Entregado',300.00,27,2),(26,'Cancelado',150.00,29,NULL),(27,'Entregado',150.00,30,NULL),(28,'Retrasado',0.00,31,NULL),(29,'Entregado',150.00,32,2),(30,'Entregado',150.00,33,NULL),(31,'Entregado',150.00,34,NULL),(32,'Retrasado',150.00,35,2),(33,'Entregado',200.00,36,2),(34,'Entregado',100.00,37,NULL),(35,'Entregado',0.00,38,NULL),(36,'Entregado',0.00,39,6),(37,'Entregado',0.00,40,NULL),(38,'Avisado',0.00,41,2),(39,'EnviadoTequila',0.00,42,NULL),(42,'Retrasado',150.00,45,NULL),(43,'Entregado',5.00,48,3),(44,'Avisado',0.00,50,NULL),(45,'Avisado',0.00,51,NULL),(46,'Avisado',0.00,55,1),(47,'Retrasado',0.00,65,NULL),(48,'Avisado',1.00,66,4),(49,'Cancelado',150.00,79,2),(50,'Entregado',150.00,85,2),(51,'Entregado',200.00,86,2),(52,'Entregado',150.00,91,5),(53,'Entregado',150.00,92,5),(54,'Retrasado',150.00,93,5),(55,'Entregado',150.00,96,5),(56,'Retrasado',150.00,99,4),(57,'Entregado',200.00,105,5),(58,'Retrasado',150.00,107,6),(59,'Cancelado',150.00,112,5),(60,'Cancelado',150.00,113,5),(61,'Retrasado',150.00,114,6),(62,'Entregado',200.00,115,2),(63,'Retrasado',150.00,116,5),(64,'Retrasado',150.00,117,1),(65,'Cancelado',150.00,118,5),(66,'Retrasado',0.00,120,NULL),(67,'Retrasado',150.00,125,5),(68,'Retrasado',200.00,126,5),(69,'EnviadoTequila',150.00,127,2),(70,'Entregado',150.00,128,2),(71,'Cancelado',150.00,129,5),(72,'Cancelado',200.00,131,5),(73,'Avisado',150.00,133,2),(74,'EnviadoTequila',150.00,136,5),(75,'Retrasado',0.00,137,5),(76,'Retrasado',0.00,138,8),(77,'Entregado',150.00,139,5);
/*!40000 ALTER TABLE `notadiseño` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notamantenimiento`
--

DROP TABLE IF EXISTS `notamantenimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notamantenimiento` (
  `idMantenimiento` int NOT NULL AUTO_INCREMENT,
  `Equipo` varchar(50) NOT NULL,
  `Marca` varchar(50) NOT NULL,
  `Model` varchar(50) DEFAULT NULL,
  `Contraseña` varchar(50) DEFAULT NULL,
  `Accesorios` varchar(100) DEFAULT NULL,
  `SugerenciaTecn` varchar(250) DEFAULT NULL,
  `Estatus` enum('Proceso','Espera','Avisado','Entregado','Cancelado','Retrasado') NOT NULL,
  `DescripcionEquipo` varchar(250) NOT NULL,
  `idNota` int NOT NULL,
  `idTecnico` int DEFAULT NULL,
  PRIMARY KEY (`idMantenimiento`),
  KEY `fk_NotaMantenimiento_Nota1_idx` (`idNota`),
  KEY `fk_NotaMantenimiento_UsuarioTrabaja` (`idTecnico`),
  CONSTRAINT `fk_NotaMantenimiento_Nota1` FOREIGN KEY (`idNota`) REFERENCES `nota` (`idNota`),
  CONSTRAINT `fk_NotaMantenimiento_UsuarioTrabaja` FOREIGN KEY (`idTecnico`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notamantenimiento`
--

LOCK TABLES `notamantenimiento` WRITE;
/*!40000 ALTER TABLE `notamantenimiento` DISABLE KEYS */;
INSERT INTO `notamantenimiento` VALUES (1,'Laptop','Lenovo','idea pad 3','2001','cargador y maletin','Actualizar windows','Retrasado','',28,1),(8,'Laptop','Acer','H1','2001','','','Retrasado','',56,1),(9,'CELULAR','APPLE','IPHONE 13','54874','','','Retrasado','',57,1),(10,'Laptop','Lenovo','Ideapad 3','2001','Cargador y maletin','','Retrasado','',58,1),(11,'Laptop','prueba','prueba','','','','Retrasado','',59,NULL),(12,'prueba','prueba','prueba','','','','Retrasado','',60,NULL),(13,'impresora','epson','E5050','','','','Retrasado','',61,NULL),(14,'Laptop','ACER','klao','1551','cargador','','Retrasado','',62,1),(15,'prueba','prueba','','','','','Retrasado','',63,1),(16,'otra prueba','otra prueba','otra prueba','','','','Retrasado','',64,NULL),(17,'prueba','prueba','prueba','12345','Cargador y maletin','SUGERENCIA','Retrasado','PRUEBA67',68,3),(18,'pruba','prueba','prueba','','','','Retrasado','prueba',69,3),(19,'prueba','prueba','prueba','12345','prueba','','Retrasado','color negro',70,3),(20,'p','f','d','','','','Retrasado','d',71,NULL),(21,'otra prueba','otra prueba','otra prueba','otra prueba','otra prueba','otra prueba','Espera','otra prueba',72,1),(22,'1','2','3','4','5','8','Espera','6',73,NULL),(23,'1','2','3','4','5','8','Espera','6',74,3),(24,'laptop','lenova','leadpad','12345','Cargador','','Cancelado','Color negro con un rayon en la parte superior',75,3),(25,'CPU','LENOVO','IDEAPAD3','2001','CARGADOR, MALETIN','','Espera','COLOR NERGO',76,1),(26,'LAPTOP','LENOVO','','','CARGADOR','','Espera','COLOR NEGRO',77,1),(27,'laptop','asus','jdsjsd','ph','cargador','','Entregado','color negro, golpeada, quebrada',78,3),(28,'sfd','sfd','sfd','fsd','fsd','sdf','Entregado','fds',80,NULL),(29,'laptop','lenovo','Ideapad 3','2001','cargador, maletin','','Avisado','color caqui',81,3),(30,'prueba','prueba','prueba','','','','Entregado','ptrueba',82,3),(31,'Laptop','prueba','prueba','','','','Entregado','prueba',83,3),(32,'prueba','prueba','prueba','12345','prueba','','Entregado','prueba',84,3),(33,'prueba','prueba','prueba','12345','cargador','','Entregado','prueva',87,3),(34,'prueba','prueba','prueba','prueba','1234','','Retrasado','preuba',88,3),(35,'prueba2','prueba2','prueba2','12345','cargador','','Retrasado','prueba',89,7),(36,'prueba','Lenovo','prueba','4','prueba','','Entregado','fddf',90,3),(37,'laptop','lenovo','lenovo','12345','cargador','','Entregado','dasdas',94,3),(38,'prueba','prueba','prueba','12345','cargador','','Entregado','dsa',95,3),(39,'prueba','prueba','prueba','prueba','prueba','','Retrasado','prueba',97,NULL),(40,'prueba','prueba','','','','','Retrasado','prueba',98,3),(41,'prueba','prueba','','','','','Cancelado','prueba',100,3),(42,'prueba','prueba','','','','','Avisado','prueba',101,NULL),(43,'prueba','prueba','prueba','','','','Avisado','prueba',102,3),(44,'prueba','prueba','prueba','','','','Avisado','prueva',103,3),(45,'prueba','prueba','prueba','','','','Avisado','prueba',104,3),(46,'prueba','prueba','prueba','prueba','prueba','','Avisado','prueba',106,NULL),(47,'prueba','prueba','prueba','prueba','prueba','','Avisado','prueba',108,3),(48,'prueba','prueba','prueba','','','','Retrasado','prueba',109,3),(49,'prueba','prueba','prueba','prueba','','','Retrasado','pp',110,1),(50,'laptop ','Dell','','','Cargador','','Retrasado','Laptop color negra',111,NULL),(51,'jj','j','j','','','','Retrasado','j',119,1),(52,'h','h','','','','','Retrasado','h',121,NULL),(53,'g','g','','','','','Retrasado','g',122,NULL),(54,'1','1','','','','','Cancelado','1',123,3),(55,'Lenvoo','Lenovoo','Lenovp','123456','car','','Cancelado','uu',124,3),(56,'lenovo','leno','le','12345','ds','','Retrasado','sdsd',130,3),(57,'ee','ee','ee','ee','eee','','Retrasado','dsd',132,3),(58,'ee','ee','ee','ee','ee','','Entregado','ee',134,3),(59,'laptop','HP','PAVILION','124563','cargador','d\r\nd\r\nd\r\nd\r\nd\r\nd','Entregado','d}\r\nd\r\nd\r\nd\r\nd\r\ndd',135,8),(60,'Laptop','Marca de Laptop X','Ideapad 3','2001','Maletin y cargador','','Proceso','color negro, con una ralladura en la parte superior izquierda de la pantalla.',140,3);
/*!40000 ALTER TABLE `notamantenimiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `idRol` int NOT NULL AUTO_INCREMENT,
  `rol` enum('administrador','encargado','diseñador','tecnico') NOT NULL,
  `estatus` enum('Activo','Inactivo') NOT NULL,
  PRIMARY KEY (`idRol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'administrador','Activo'),(2,'encargado','Activo'),(3,'diseñador','Activo'),(4,'tecnico','Activo');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipomantenimiento`
--

DROP TABLE IF EXISTS `tipomantenimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipomantenimiento` (
  `idTipoMnt` int NOT NULL AUTO_INCREMENT,
  `NombreTipo` varchar(100) NOT NULL,
  PRIMARY KEY (`idTipoMnt`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipomantenimiento`
--

LOCK TABLES `tipomantenimiento` WRITE;
/*!40000 ALTER TABLE `tipomantenimiento` DISABLE KEYS */;
INSERT INTO `tipomantenimiento` VALUES (1,'Mantenimiento Preventivo'),(2,'Mantenimiento Correctivo'),(3,'Mantenimiento Predictivo'),(4,'Mantenimiento Evolutivo o de Actualización'),(5,'Software'),(6,'Impresoras');
/*!40000 ALTER TABLE `tipomantenimiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `idUsuario` int NOT NULL AUTO_INCREMENT,
  `NombreUsuario` varchar(50) NOT NULL,
  `Usuario` varchar(30) NOT NULL,
  `Contraseña` varchar(250) NOT NULL,
  `Estatus` enum('Activo','Inactivo') NOT NULL,
  PRIMARY KEY (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Claudia Guadalupe Moya','Claudia','$2y$10$NSyaVolN2SyYF9Pg2SYO9OQfM4QTeoZvUJmOcqxfaIjLa5C2vVLgS','Activo'),(2,'Alexia Yanett Avila Ornelas','Alexia','$2y$10$4kVFbdjTnHuuYGhrMutFxuwZGj8wfNfDiDzA9zS4XeOGl8wuXQGES','Activo'),(3,'Jose Maria','chema','$2y$10$01tNlb8zsiplgywPXpV3dewKw0zjldA8k/CqjC1KZq2nQBrbDQ9NO','Activo'),(4,'Deisy Manuela Avila Ornelas','Deisy','$2y$10$01tNlb8zsiplgywPXpV3dewKw0zjldA8k/CqjC1KZq2nQBrbDQ9NO','Activo'),(5,'Estefania Camacho','Estefania','$2y$10$CxBb66SNFXuPP2YJLdS7UufbQl3spXdANT.kV62mFMJ3Js83BaZ0m','Activo'),(6,'Norma','Norma','$2y$10$UgKGzUFGVZY81LEsU8QzouRqXOF8dTOw1XxA2Qa08VgNL9QyRrpSS','Activo'),(7,'prueba','prueba','$2y$10$D4lZx.bxn0CsTNbZQWtH/OeSp2TUt5Cef2X.TrmIHpyt8WHVGnvri','Inactivo'),(8,'pedro','pedro','$2y$10$z0tsjmv8uUq4so/dnIAwlunLoRgjUGYQMMmiG41C5uHcSJgRdSPnW','Activo');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarioroles`
--

DROP TABLE IF EXISTS `usuarioroles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarioroles` (
  `idUsuarioRol` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int NOT NULL,
  `idRol` int NOT NULL,
  PRIMARY KEY (`idUsuarioRol`),
  UNIQUE KEY `idUsuario` (`idUsuario`,`idRol`),
  KEY `idRol` (`idRol`),
  CONSTRAINT `usuarioroles_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  CONSTRAINT `usuarioroles_ibfk_2` FOREIGN KEY (`idRol`) REFERENCES `rol` (`idRol`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarioroles`
--

LOCK TABLES `usuarioroles` WRITE;
/*!40000 ALTER TABLE `usuarioroles` DISABLE KEYS */;
INSERT INTO `usuarioroles` VALUES (3,1,1),(29,2,2),(30,2,3),(6,3,3),(7,3,4),(28,4,3),(10,5,3),(20,6,2),(21,6,3),(24,7,2),(25,7,4),(31,8,1),(32,8,2),(33,8,3),(34,8,4);
/*!40000 ALTER TABLE `usuarioroles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-04 15:13:27
