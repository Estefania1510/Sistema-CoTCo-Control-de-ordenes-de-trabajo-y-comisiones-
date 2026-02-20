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
  `idCatalogoMnt` int DEFAULT NULL,
  `Descripcion` varchar(150) DEFAULT NULL,
  `Cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `Precio` decimal(10,2) DEFAULT '0.00',
  `Subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Origen` enum('CATALOGO','MANUAL') NOT NULL DEFAULT 'CATALOGO',
  PRIMARY KEY (`idAuxServicios`),
  KEY `fk_AuxServicios_NotaMantenimiento1_idx` (`idMantenimiento`),
  KEY `fk_AuxServicios_CatalogoMnt1_idx` (`idCatalogoMnt`),
  CONSTRAINT `fk_AuxServicios_CatalogoMnt1` FOREIGN KEY (`idCatalogoMnt`) REFERENCES `catalogomnt` (`idCatalogoMnt`),
  CONSTRAINT `fk_AuxServicios_NotaMantenimiento1` FOREIGN KEY (`idMantenimiento`) REFERENCES `notamantenimiento` (`idMantenimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auxservicios`
--

LOCK TABLES `auxservicios` WRITE;
/*!40000 ALTER TABLE `auxservicios` DISABLE KEYS */;
INSERT INTO `auxservicios` VALUES (1,1,18,NULL,1.00,400.00,400.00,'CATALOGO'),(2,1,20,NULL,1.00,100.00,100.00,'CATALOGO'),(3,2,24,NULL,1.00,0.00,0.00,'CATALOGO'),(4,2,24,NULL,1.00,300.00,300.00,'CATALOGO'),(5,3,1,NULL,1.00,200.00,200.00,'CATALOGO'),(6,5,22,NULL,1.00,450.00,450.00,'CATALOGO'),(7,4,9,NULL,1.00,200.00,200.00,'CATALOGO'),(8,6,22,NULL,1.00,600.00,600.00,'CATALOGO'),(9,7,18,NULL,1.00,400.00,400.00,'CATALOGO'),(10,10,18,NULL,1.00,400.00,400.00,'CATALOGO'),(11,8,18,NULL,1.00,450.00,450.00,'CATALOGO'),(12,14,1,NULL,1.00,400.00,400.00,'CATALOGO'),(13,14,15,NULL,1.00,500.00,500.00,'CATALOGO'),(14,18,24,NULL,1.00,0.00,0.00,'CATALOGO'),(15,15,15,NULL,1.00,500.00,500.00,'CATALOGO'),(16,15,23,NULL,1.00,200.00,200.00,'CATALOGO'),(17,23,NULL,'prueba',1.00,100.00,100.00,'MANUAL');
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
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (1,'Diana Ocegueda','Sin dirección','3741032210',''),(2,'Francisco Vega','Sin dirección','3861083662',''),(3,'Centro Rehabilitacion','Sin direccion','3741380003',''),(4,'Fernando Andrade','Sin dirección','3861052396',''),(5,'Yuri Caro','Sin dirección','3314209371',''),(6,'Mestro','Sin dirección','3333632690',''),(7,'YESICA RUIZ','Sin dirección','3861054625',''),(8,'MARIELA LOPEZ','Sin dirección','3861080344',''),(9,'Angelo','MATAMOROS #53','3861005039',''),(10,'TAKESHI SUSHI MARISCOS','S/D','3861036936',''),(11,'LUPITA','S/D','3324222200',''),(12,'S/N','S/D','3325554002',''),(13,'PERLA SANTOS','S/N','3861116933',''),(14,'BELEN CASTAÑEDA','Sin dirección','3314215741',''),(15,'YOANA REYNOSO','Sin dirección','3861186236',''),(16,'FATIMA PADILLA','Sin dirección','3326172433',''),(17,'LILI BENAVIDES','S/D','3861122655',''),(18,'Raquel Berenice Calvillo Rosales','Vicente Guerrero, Hostotipaquillo','3861116464',''),(19,'LUPITA BERNAL','Sin dirección','386106778',''),(20,'CARLOS ALBERTO NOLASCO','Sin dirección','3861091636',''),(21,'MARICURZ CARRILLO','Sin dirección','3325322458',''),(22,'Malena','S/D','3319184825',''),(23,'ANGELICA SOLORIO','S/D','3861055398',''),(24,'FABIOLA HUERTA','Sin dirección','3324050011',''),(25,'ANA','Sin dirección','+18637848786',''),(26,'LIZ AYON','Sin dirección','3327648805',''),(27,'Astrid barco','Sin dirección','3312384070',''),(28,'SUSANA SANTIAGO','Sin dirección','3861090087',''),(29,'JAQUELINE MALDONADO','Sin dirección','3325930080',''),(30,'RODOLFO ESCOBEDO LOPEZ','Sin dirección','3861467736',''),(31,'MAYTE NUTRIOLOGA','Sin dirección','3311623713',''),(32,'Ruth Jimenez','S/N','3331157283',''),(39,'ALMA DELIA FRANCO VALDEZ','S/N','3742195482',''),(40,'AZUCENA DELGADILLO','S/D','3861096142',''),(41,'MIRC','Sin dirección','3861054109',''),(42,'YESENIA CARRILLO','SD','3741100010',''),(43,'ROCIO VALLEJO','S/N','3861009727',''),(44,'CBTis 244','SD','3861053935',''),(45,'Lucero Bañuelos','S/D','3324573677',''),(47,'ANGELA RANGEL FLORES','SN','3861046547',''),(48,'esperanza garcia','dn','3322730963',''),(49,'CARLOS RIVERA','S/N','3861133999',''),(50,'VIANEY AVILA MERCADO','S/N','3741010914',''),(51,'NOTAS VALENZUELA','SIN','3867441378',''),(52,'LEONEL RODRIGUEZ','Sin dirección','3861084849',''),(53,'Daniel Rubio','Sin dirección','3314817649',''),(54,'LUSMILA PEREZ MURILLO','Sin dirección','3311212242','3328447115'),(55,'MARIA DE LOURDES MONTES GALINDO','Sin dirección','3861136438',''),(56,'LEONARDO','SN','3861098982',''),(57,'Rysmac','M. Avila Camacho #245','3311357144',''),(58,'JUAN ROSALES','S/D','3866906425',''),(59,'ALEJANDRO TOCUMBO','Sin dirección','3861188366',''),(60,'ROSAURA GARCIA','S/D','3333607219',''),(61,'Ejido oblatos','S/N','3312176400',''),(62,'ESMERALDA ARELLANO','SIN','3329530070',''),(63,'JUAN VILLALOBOS','SN','3412543243',''),(64,'azucena','386 109 6142','3861096142',''),(65,'DANIELA HUERTA','S/D','3741030592',''),(66,'LAURA GARCIA','SN','3112296963',''),(67,'ALEJANDRA MACHUCA','S/D','3861124800',''),(68,'JOSE DIONISIO DOMINGUEZ RODRIGUEZ','CONOCIDO','3321172062',''),(69,'MARIANA RUBIO','S/D','3319974465',''),(70,'TERESA DEL REAL','S/D','3861009674',''),(71,'Everardo Quiñones','Sin dirección','3342800015',''),(73,'EZEQUIEL SERNA','SN','3861261323',''),(74,'FILIBERTO PULIDO PEREZ','SN','3331050490',''),(75,'Ruth Elizabeth RaV','SD','3741007852',''),(76,'Liz Barba','Sin dirección','3861009895',''),(77,'VICTOR LOPEZ','S/D','4522023241',''),(78,'DULCE MARIA FLORERIA TACOTAL','Sin direccion','3861399958',''),(79,'JUAN JOSE CARRILLO','S/D','3313030772',''),(80,'ALMA DELIA LOPEZ MEDINA','SN','3319604093','6693294586'),(81,'BRENDA CORTEZ','S/D','3325413428',''),(82,'Rigoberto Pulido','S/N','3861001064',''),(83,'SUPERVISION ZONA 11','S/D','3861048253',''),(85,'Bertha Miramontes','S/D','3321202828',''),(86,'MARISOL CAMPO','SD','3861125623',''),(87,'centro de rehabilitacion','SN','3741380003',''),(88,'RODRIGO RODRIGUEZ','SN','3861465586','');
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comisiones`
--

LOCK TABLES `comisiones` WRITE;
/*!40000 ALTER TABLE `comisiones` DISABLE KEYS */;
INSERT INTO `comisiones` VALUES (1,'Diseño',30.00,'2026-01-16',0.00,'Pagado',4,3),(2,'Mantenimiento',30.00,NULL,150.00,'Orden Entregada',1,4),(3,'Mantenimiento',30.00,NULL,90.00,'Orden Entregada',3,5),(4,'Diseño',30.00,'2025-12-10',60.00,'Pagado',5,6),(5,'Diseño',30.00,'2026-01-16',24.00,'Pagado',4,8),(6,'Diseño',30.00,'2025-12-24',45.00,'Pagado',2,9),(7,'Diseño',30.00,'2025-12-24',60.00,'Pagado',2,10),(8,'Diseño',30.00,'2026-01-16',30.00,'Pagado',4,11),(9,'Diseño',30.00,'2026-01-16',45.00,'Pagado',4,12),(10,'Mantenimiento',30.00,NULL,60.00,'Orden Entregada',3,13),(11,'Diseño',30.00,'2025-12-24',45.00,'Pagado',5,14),(12,'Diseño',30.00,'2025-12-24',30.00,'Pagado',5,15),(13,'Diseño',30.00,'2025-12-24',30.00,'Pagado',5,16),(14,'Diseño',30.00,'2026-01-16',45.00,'Pagado',4,17),(15,'Diseño',30.00,'2026-01-16',30.00,'Pagado',4,19),(16,'Mantenimiento',30.00,NULL,135.00,'Orden Entregada',3,20),(17,'Diseño',30.00,'2026-01-16',30.00,'Pagado',4,22),(18,'Diseño',30.00,'2026-01-16',36.00,'Pagado',4,23),(19,'Diseño',30.00,'2025-12-24',45.00,'Pagado',2,24),(20,'Diseño',30.00,'2025-12-24',30.00,'Pagado',5,25),(21,'Diseño',30.00,'2025-12-24',45.00,'Pagado',5,27),(22,'Mantenimiento',30.00,NULL,180.00,'Orden no Entregada',1,30),(23,'Diseño',30.00,'2025-12-24',84.00,'Pagado',5,31),(24,'Diseño',30.00,'2026-01-13',120.00,'Pagado',5,33),(25,'Diseño',30.00,'2026-01-16',45.00,'Pagado',4,34),(26,'Diseño',30.00,NULL,0.00,'Orden Entregada',3,35),(27,'Diseño',30.00,'2026-01-16',30.00,'Pagado',5,43),(28,'Diseño',30.00,'2026-01-23',0.00,'Pagado',5,54),(29,'Diseño',30.00,'2026-01-23',60.00,'Pagado',4,60),(30,'Diseño',30.00,NULL,0.00,'Orden no Entregada',4,61),(31,'Diseño',30.00,NULL,45.00,'Orden Entregada',5,62),(32,'Diseño',30.00,NULL,30.00,'Orden Entregada',2,63),(33,'Diseño',30.00,NULL,45.00,'Orden Entregada',2,64),(34,'Diseño',30.00,NULL,45.00,'Orden Entregada',2,66),(35,'Diseño',30.00,NULL,7.50,'Orden Entregada',2,67),(36,'Mantenimiento',30.00,NULL,210.00,'Orden Entregada',3,73),(37,'Diseño',30.00,NULL,60.00,'Orden Entregada',4,75),(38,'Diseño',30.00,NULL,45.00,'Orden no Entregada',2,84);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licenciasoftware`
--

LOCK TABLES `licenciasoftware` WRITE;
/*!40000 ALTER TABLE `licenciasoftware` DISABLE KEYS */;
INSERT INTO `licenciasoftware` VALUES (1,'W269N-WFGWX-YVC9B-4J6C9-T83GX','Microsoft Windows','Libre',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'3NGBP-X7MTQ-DPWY2-FTY32-GRB2W','Microsoft Office','Libre',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'ZKLD-FJ4A-R7QP-MNB5-VX9C-W3E8','Suite Adobe','Libre',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,'AX45-BGT7-CV89-DWQ1-ERF2-GHY6','Antivirus','Libre',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,'PROD-KEY-BX5Z-A9QW-E1CD-F7RG','Utilerías','Libre',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,'a25667@msdn365.vip','Microsoft Office','Instalada','Ictmag26..','LAPTOP-BN3HOH7J','Procesador Intel(R) Celeron(R) N4120 CPU @ 1.10GHz 1.10 GHz','67346AB0-0DC1-46C0-ADD7-9C917C253D13','00356-07402-87374-AAOEM','2026-01-12',50,44);
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
  `excepcion` text NOT NULL,
  PRIMARY KEY (`idLog`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logerror`
--

LOCK TABLES `logerror` WRITE;
/*!40000 ALTER TABLE `logerror` DISABLE KEYS */;
INSERT INTO `logerror` VALUES (3,'procesarOrdenDiseno','SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'Anticipo\' at row 1'),(4,'procesarOrdenDiseno','SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens'),(5,'procesarOrdenDiseno','SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'Precio\' at row 1'),(6,'procesarOrdenDiseno','SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'Precio\' at row 1'),(7,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(8,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(9,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(10,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(11,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(12,'procesarOrdenMantenimiento','SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'idUsuario\' cannot be null'),(13,'procesarOrdenMantenimiento','SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens'),(14,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'fdssdfsdf\''),(15,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'hhghgffggfh\''),(16,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'fdssdfsdf\''),(17,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'fdssdfsdf\''),(18,'procesarLicenciaOrden','SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: \'fdssdfsdf\''),(19,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'c.fechaentrega\' in \'where clause\''),(20,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'c.fechaentrega\' in \'where clause\''),(21,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'c.fechaentrega\' in \'where clause\''),(22,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(23,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(24,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(25,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(26,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(27,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(28,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(29,'clientesController','SQLSTATE[42S02]: Base table or view not found: 1146 Table \'cotco.notas_software\' doesn\'t exist'),(30,'procesarOrdenDiseno','SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column \'Telefono\' at row 1'),(31,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'cli.NombreCliente\' in \'field list\''),(32,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'cli.NombreCliente\' in \'field list\''),(33,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'cli.NombreCliente\' in \'field list\''),(34,'comisionesController','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'cli.NombreCliente\' in \'field list\''),(35,'procesarOrdenDiseno','SQLSTATE[HY093]: Invalid parameter number: parameter was not defined'),(36,'procesarOrdenDiseno','SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`cotco`.`nota`, CONSTRAINT `fk_Nota_Usuario1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`))'),(37,'procesarOrdenDiseno','idUsuario en sesión (18) NO existe en tabla usuario. Cierra sesión e inicia sesión otra vez.');
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
) ENGINE=InnoDB AUTO_INCREMENT=235 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material`
--

LOCK TABLES `material` WRITE;
/*!40000 ALTER TABLE `material` DISABLE KEYS */;
INSERT INTO `material` VALUES (2,'Impresion de vinil en corplast con base',1,250.00,250.00,75),(23,'Impresion de vinil en corplast con base',1,250.00,250.00,1),(24,'Lona 1.20 x .50',1,200.00,200.00,3),(27,'DIGITAL',1,0.00,0.00,4),(33,'CALCOMANIA TABLOIDE C/S',1,50.00,50.00,6),(34,'COUCHE CARTA',1,20.00,20.00,6),(35,'LONA .50X1',2,100.00,200.00,5),(42,'milla recetas',1,1140.00,1140.00,2),(43,'LONA 1.5X1',1,300.00,300.00,10),(45,'Tabloide calca',3,50.00,150.00,11),(58,'CALCA TABLOIDE',2,50.00,100.00,12),(61,'Tabloide couche',1,50.00,50.00,19),(65,'MENUS F/V ENMICADOS',20,45.00,900.00,8),(66,'MILLAR DE NOTAS 1/4 CON COPIA',1,1650.00,1650.00,7),(68,'CALCOMANIA TABLOIDE C/S',5,50.00,250.00,16),(75,'CALCOMANIA  CON SUAJE',2,50.00,100.00,22),(78,'CALCA CARTA',1,30.00,30.00,18),(79,'CALCA',3,50.00,150.00,15),(82,'COUCHE FRENTE Y VUELTA',2,80.00,160.00,23),(84,'VINIL  1 MT',1,350.00,350.00,21),(85,'CALCA TABLOIDE',1,50.00,50.00,17),(86,'vinil',1,185.00,185.00,24),(89,'INVITACION DIGITAL',1,0.00,0.00,25),(91,'CALCA TABLOIDE',2,50.00,100.00,9),(92,'LONA .80 X 2',1,400.00,400.00,20),(106,'INVITACION DIGITAL',1,0.00,0.00,27),(108,'Calcamonia con couche',6,450.00,2700.00,29),(115,'VINIL NEGRO',1,105.00,105.00,31),(118,'COUCHE TABLOIDE',5,50.00,250.00,28),(122,'GRABADO DE TERMO',2,200.00,400.00,34),(127,'CALCOMANIA',2,100.00,200.00,37),(129,'impresion invitacion 15 año',5,50.00,250.00,36),(133,'LONA 1.80X90',1,360.00,360.00,33),(134,'DOBLADO PARA TUBO',1,50.00,50.00,33),(135,'MILLAR DE NOTAS 1/2 CARTA',1,1700.00,1700.00,41),(139,'GRABADO LASER TERMO',2,100.00,200.00,35),(149,'VINIL TROVICEL 1MT DE ALTO POR 3MT DE ANCHO',1,1650.00,1650.00,43),(151,'MILLAR DE NOTAS 1/2 CARTA',1,1850.00,1850.00,26),(160,'BORDADO',1,750.00,750.00,40),(166,'clacomania tabloide',1,50.00,50.00,47),(167,'calcomania carta',1,30.00,30.00,47),(168,'COUCHE TABLOIDE',1,50.00,50.00,47),(170,'tabloide imp. frente y vuelta con corte y dobles',7,60.00,420.00,44),(171,'FOIL EN COUCHE 20X20',1,70.00,70.00,42),(183,'MILLAR DE NOTAS BASCULA',1,1600.00,1600.00,38),(184,'SELLO CHICO',1,450.00,450.00,48),(190,'50 OPALINA DELGADA OFICIO MEMBRETADA',50,22.00,1100.00,46),(193,'1 MILLAR DE RECETAS ',0,0.00,0.00,57),(194,'LONA 1.5X1',1,300.00,300.00,49),(195,'TABLOIDE ADHERIBLE',10,35.00,350.00,50),(196,'HOJA BOND',1,5.00,5.00,54),(199,'Tabloide couche',2,30.00,60.00,53),(201,'IMPRESION DE CORPLAST 80 CM OSO',1,300.00,300.00,55),(208,'LONA 1X1',1,200.00,200.00,56),(209,'VINIL TROVICEL 30 X 20',13,50.00,650.00,52),(212,'500 BOLETOS DE RIFA',1,940.00,940.00,51),(213,'4 METROS VINIL CON SUAJE',4,400.00,1600.00,45),(218,'VINIL 1 MT',1,400.00,400.00,60),(221,'PONCHADO',1,250.00,250.00,30),(222,'BORDADO',2,65.00,130.00,30),(223,'TARJETA PVC',1,50.00,50.00,58),(224,'VINIL 20X23 CM',1,100.00,100.00,39),(225,'OPALINA CARTA',2,20.00,40.00,13),(226,'COUCHE',5,50.00,250.00,14),(227,'LONA 1X1',1,200.00,200.00,14),(228,'SELLO CHICO',2,550.00,1100.00,32),(229,'Editar diseño actual',1,0.00,0.00,61),(230,'VINIL SUAJE IMPRESO',3,400.00,1200.00,59),(231,'RECORTE DE VINIL',2,380.00,760.00,59),(233,'LONA',1,1400.00,1400.00,62),(234,'SELLO MEDIANO',1,550.00,550.00,63);
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
  `Trabajo` varchar(1000) NOT NULL,
  `Descripcion` varchar(2000) DEFAULT NULL,
  `Comentario` varchar(250) DEFAULT NULL,
  `idUsuario` int NOT NULL,
  `idCliente` int NOT NULL,
  PRIMARY KEY (`idNota`),
  KEY `fk_Nota_Usuario1_idx` (`idUsuario`),
  KEY `fk_Nota_Cliente1_idx` (`idCliente`),
  CONSTRAINT `fk_Nota_Cliente1` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`),
  CONSTRAINT `fk_Nota_Usuario1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nota`
--

LOCK TABLES `nota` WRITE;
/*!40000 ALTER TABLE `nota` DISABLE KEYS */;
INSERT INTO `nota` VALUES (1,'2025-11-26','2025-12-01',250.00,100.00,150.00,'Figura coroplast conejo','Figura Cortplast conejo de 80 cm de alto','',2,1),(2,'2025-11-26','2025-12-11',1140.00,200.00,940.00,'Millar recetas azul','Millar de recetas 1 tinta azul','',6,2),(3,'2025-11-26','2025-12-01',200.00,200.00,0.00,'Croquis PDF WhatsApp','Croquis mando a whats es un pdf','',6,3),(4,'2025-11-27','2025-11-27',500.00,0.00,500.00,'Word no funciona','No deja trabajar en word','',5,4),(5,'2025-11-30','2025-12-05',300.00,300.00,0.00,'Orden sin detalle','','',3,5),(6,'2025-11-30','2025-12-02',200.00,0.00,200.00,'Diseño digital','DISEÑO EN DIGITAL\r\nDATOS EN WHATSAAP','',5,6),(7,'2025-12-04','2025-12-05',200.00,0.00,200.00,'2 lonas 0.50x1','2 LONAS DE .50X1','',4,7),(8,'2025-12-05','2025-12-09',150.00,0.00,150.00,'Etiquetas 1ra comunión','*30 CIRCULOS DE 5CM C/U CON SUAJES CALCOMANIA\r\nPRIMERA COMUNION AARON GRACIAS POR ACOMPAÑARNOS 06/DIC/2025\r\n*10 CUADADOS DE 6CM OPALINA\r\nAARON ERNESTO 06/DIC/25','',4,8),(9,'2025-12-10','2025-12-19',1800.00,1000.00,800.00,'Millar notas c/copia','Un millar de notas con copia 1/4 de carta','',2,9),(10,'2025-12-10','2025-12-20',1100.00,700.00,400.00,'20 menús enmicados','20 menú en hoja normal frente y vuelta con emicado','HIZO EL ANTICIPO POR TRANFERENCIA',2,10),(11,'2025-12-10','2025-12-11',200.00,200.00,0.00,'Plantillas con suaje','2 PLANTILLAS DE 5CM CON SUAJE\r\n1° ANIVERSARIO LUCTUOSO CESAR ALEJANDRO CRUZ\r\n13 DE ABRIL DE 1988\r\n11DE DIC DEL 2024','',4,11),(12,'2025-12-10','2025-12-14',450.00,0.00,450.00,'Lona 1.5x1','1 LONA DE 1.5X1 CON OJILLOS Y BASTILLA','',4,12),(13,'2025-12-11','2025-12-14',200.00,0.00,200.00,'Pantalla azul','ENTRA PANTALLA AZUL Y SE TRABA','',4,13),(14,'2025-12-12','2025-12-14',300.00,300.00,0.00,'Stickers Postres Belén','CIRCULOS DE 4 CM \r\nPOSTRES BELEN\r\nCON AMOR Y CALIDAD PARA TU PALADAR\r\nIMAGEN EN WHATSAAP','PAGADO',5,14),(15,'2025-12-14','2025-12-18',200.00,200.00,0.00,'Recuerdo aniversario','JUAN RODRIGUEZ OCHOA\r\n5 ANIVERSARIO\r\nFECHA DE NACIMIENTO 07/02/1950\r\nFECHA DE LUTO 24/12/2020','',5,15),(16,'2025-12-16','2026-02-10',140.00,70.00,70.00,'Separadores de libros','6 SEPARADOR DE LIBROS \r\nDISEÑO EN FISICO','',5,16),(17,'2025-12-16','2026-02-10',600.00,300.00,300.00,'Tarjetas + lona 1x1','100 TARJETAS DE PRESENTACION\r\nAGREGAR KE GUSIS Y EL NOMBRE DE LA PERSONA\r\n1 LONA DE 1X1 CON EL MISMO DISEÑO','',4,17),(18,'2025-12-16','2025-12-19',200.00,0.00,200.00,'Error papelera','Fallo Relacionado con la papelera de reciclaje','',3,18),(19,'2025-12-16','2025-12-21',250.00,250.00,0.00,'Suaje círculos bautizo','3 PLANILLAS CON SUAJE DE CIRCULOS DE 6CM\r\nRECUERDO DE MI BAUTIZO AILANY JULIETH 28/DIC/2025\r\nMANDO EL COLOR POR WHATS','',4,19),(20,'2025-12-17','2025-12-16',450.00,0.00,450.00,'Activación AutoCAD','activación de AutoCAD hecho por Osmar y queda pendiente activación de Windows sin costo','',5,20),(21,'2025-12-18','2025-12-19',280.00,100.00,180.00,'Etiquetas XV vinil','70 etiquetas para botella de XV años en recorte de vinil dorado','Cotizar y avisarle al cliente antes, dejo $100 de anticipo',2,21),(22,'2025-12-18','2025-12-23',150.00,0.00,150.00,'Etiquetas luctuoso','ETIQUETA ANIVERSARIO LUCTUOSO\r\n40 ETIQUETAS CIRCULARES DE 5CM CON SUAJE\r\nGraciela Rolón González \r\nTercer aniversario luctuoso \r\n01/01/2026','',4,22),(23,'2025-12-18','2025-12-21',150.00,150.00,0.00,'Etiquetas Mis XV','6 ETIQUETAS 4X8 VERTICAL\r\n6ETIQUETAS 6X4 HORIZONTAL\r\nMIS VX OLGA LILIENI 10/ENERO/26\r\nCOLOR VERDE PISTACHE','',4,23),(24,'2025-12-18','2025-12-19',200.00,200.00,0.00,'Pases VIP','25 Pases VIP de8.5x5.5, información por whats','',2,24),(25,'2025-12-20','2025-12-24',500.00,250.00,250.00,'Diseño por WhatsApp','MANDO IMAGEN A WHATSAAP','',2,25),(26,'2025-12-20','2025-12-23',350.00,350.00,0.00,'Letras vinil negro','LETRAS EN VINIL COLOR NEGRO\r\nMANDO POR WHATSAPP\r\n40 de ancho por lo que de de alto','',5,26),(27,'2025-12-20','2025-12-20',250.00,200.00,50.00,'Círculos XV Zara','CIRCULOS MIS XV AÑOS ZARA XIMENA','ANTICIPO ENVIADO POR TRANSFERENCIA',5,27),(28,'2025-12-20','2025-12-21',160.00,100.00,60.00,'Invitaciones impresión','imprimir 10 invitaciones 18 x 12 frente y vuelta\r\nla imagen con la foto de la quinceañera que diga lo que mande al numero de WhatsApp\r\npor que mando la puro foto\r\ny en la parte de atrás que lleve el escrito del documento de Word','',5,28),(29,'2025-12-22','2025-12-23',185.00,100.00,85.00,'Vinil blanco','vinil blanco datos en el what','',6,29),(30,'2025-12-22',NULL,600.00,0.00,600.00,'AutoCAD + Revit','AUTOCAD Y REVIT 400 c/u','REGRESAR PARA ACTIVAR REVIT QUEDO CON VERSION DE PRUEBA Y SE COBRARIAN 200 MAS CUANDO SE ACTIVE',6,30),(31,'2025-12-24','2025-12-23',280.00,0.00,280.00,'Invitación posada','INVITACION DIGITAL POSADA','TRANSFIRIO',5,31),(32,'2026-01-05','2026-01-16',1850.00,1000.00,850.00,'Millar notas venta','MILLAR DE NOTAS DE VENTA 1/2 CARTA VERTICAL CON COPIA FOLIO A PARTIR DEL 1001','',6,32),(33,'2026-01-05','2026-01-06',400.00,300.00,100.00,'Invitación XV Evelin','INVITACION DIGITAL 15 AÑOS\r\nEVELIN VANESA ALVARADO FRANCO\r\n25 ABRIL 2026 6:00 PM MISA TEMPLO DEL SR MILAGROSO \r\nRECEPCION BUGAMBILIAS 8:0 PM\r\nPAPAS: ALMA DELIA FRANCO VALDEZ Y VICTOR ADRIAN ALVARADO CARRILLO\r\nPADRINOS: MARIA DEL CARMEN BLANCO ALVARADO Y MAGDALENO CARRILLO SANCHEZ\r\nTEMATICA NORTEÑA/VAQUERA\r\nCONFIRMAR ASISTENCIA 3742195482','',6,39),(34,'2026-01-05','2026-01-10',400.00,400.00,0.00,'Invitación boda/bautizo','INVITACION 12X14 CM 30 INVITACIONES\r\nBODA Y BAUTIZMO\r\nRENE PEÑA Y MARLEN DIAZ\r\nDATOS EN WHATS','',4,40),(35,'2026-01-06','2026-01-07',2700.00,0.00,2700.00,'Calcomanía con couche','Detalles en el Whastapp','',3,41),(36,'2026-01-08','2026-02-10',380.00,200.00,180.00,'Bordado 2 sudaderas','2 Sudaderas para bordado, a la altura del pecho del lado izquierdo de 10cm de ancho\r\nEl archivo esta en escritorio de la computadora 2\r\nLas sudaderas las trajo el cliente','Efectivo, Transfirio el resto',2,42),(37,'2026-01-09','2026-01-10',105.00,105.00,0.00,'Vinil letras “Bienvenido”','LETRAS VINIL NEGRO\r\n30 CM DE ANCHO\r\nBIENVENIDO\r\nTE AMAMOS','',5,43),(38,'2026-01-09',NULL,1100.00,0.00,1100.00,'2 sellos chicos','2 Sellos chicos','',2,44),(39,'2026-01-09','2026-01-14',410.00,230.00,180.00,'Lona 1.80x0.90','1 lona de 1.80x.90 con dobles de 10cm de diámetro para tubo','TRANFERENCIA ANTICIPO',2,45),(40,'2026-01-10',NULL,400.00,0.00,400.00,'Grabado termo láser','GRABADO DE TERMO LASER','',6,46),(41,'2026-01-10','2026-01-15',200.00,100.00,100.00,'Grabado láser termos','GRABADO LASER TERMOS','',6,47),(42,'2026-01-10','2026-01-13',250.00,250.00,0.00,'Tabloides + invitaciones','12 invitaciones reservado y el resto de las otras \r\n5 tabloides','',6,48),(43,'2026-01-11','2026-01-11',300.00,150.00,150.00,'Diseño Frozen','DISEÑO FROZEN\r\nADAIA\r\n4 AÑOS','',5,49),(44,'2026-01-11','2026-01-13',400.00,0.00,400.00,'Windows + dispositivo','Nombre del dispositivo	LAPTOP-BN3HOH7J\r\nProcesador	Intel(R) Celeron(R) N4120 CPU @ 1.10GHz   1.10 GHz\r\nRAM instalada	4.00 GB (3.77 GB utilizable)\r\nId. del dispositivo	67346AB0-0DC1-46C0-ADD7-9C917C253D13\r\nId. del producto	00356-07402-87374-AAOEM\r\nTipo de sistema	Sistema operativo de 64 bits, procesador x64\r\nLápiz y entrada táctil	La entrada táctil o manuscrita no está disponible para esta pantalla','INSERTAR ALMACENAMIENTO EXTERNO',5,50),(45,'2026-01-12','2026-01-24',1600.00,1600.00,0.00,'Millar notas báscula','MILLAR DE NOTAS BASCULA A PARTIR DEL FOLIO 7001','',6,51),(46,'2026-01-13',NULL,450.00,0.00,450.00,'Instalar Office','AGREGAR OFFICE\r\nCORREO DEL CLIENTE\r\nleo.rodri@gmail.com\r\ncontraseña del correo: Lideal01.','',5,52),(47,'2026-01-13',NULL,0.00,0.00,0.00,'Revisión teclado','REVISION DE TECLADO\r\nSE MANDA A TEQUILA','',5,53),(48,'2026-01-13','2026-01-16',400.00,0.00,400.00,'Orden sin detalle','','',5,54),(49,'2026-01-13','2026-02-10',100.00,100.00,0.00,'Vinil tornasol XV','VINIL TORNASOL \r\nBIENVENIDOS A MIS XV LIZETH','',5,55),(50,'2026-01-13','2026-01-20',750.00,750.00,0.00,'Bordado mandil','BORDADO EN MANDIL','bonificarle 500 que dejo en Tequila',6,56),(51,'2026-01-14',NULL,1700.00,850.00,850.00,'Millar notas 1/2 carta','1 Millar de notas de venta 1/2 carta con copia','',3,57),(52,'2026-01-14',NULL,0.00,0.00,0.00,'Cotización Epson L3250','Necesita los drivers para epson L3250\r\n\r\n$480 del de SSD 120Gb\r\n$250 de la Memoria RAM (indispensable)\r\n$700 Servicio (Formateo e intalacion de Windows, Office y Respaldo 70Gb)\r\n$200 Limpieza y pasta termica\r\n$400 Regulador Vorago\r\nTotal $2,030','',3,5),(53,'2026-01-14',NULL,0.00,0.00,0.00,'Eliminar virus','Borrar todo porque le entro virus','',2,58),(54,'2026-01-15','2026-01-23',70.00,0.00,70.00,'Texto para foil','ESCRITO PARA FOIL EN COUCHE 20X20  HAY UNA IMAGEN DE REFERENCIA EN WHATSAAP\r\nTíos Adrián y Sandra\r\nNecesito unas manos extras que me guíen en el camino…\r\n¡Y las suyas son perfectas!\r\n¿Quieren ser mis padrinos de bautizo?','',5,47),(55,'2026-01-16',NULL,1800.00,0.00,1800.00,'Vinil trovicel 1x3','vinil trovicel 1mt de alto por ancho 3 mt\r\nimagenes de crepas, wafles con frutas, marquesitas.\r\nque lleve los nombres crepas, wafle, marquesitas.','',5,59),(56,'2026-01-17',NULL,0.00,0.00,0.00,'Laptop lenta + antivirus','Esta lenta y tarda bastante en abrir los programas\r\nTambien poner antivirus ','',2,60),(57,'2026-01-19','2026-01-23',420.00,420.00,0.00,'14 invitaciones impresas','14 invitaciones impresión frente y vuelta con corte y dobles','',6,61),(58,'2026-01-19','2026-02-05',1600.00,600.00,1000.00,'Vinil con suaje','VINIL CON SUAJE','',6,62),(59,'2026-01-19','2026-01-27',1100.00,500.00,600.00,'50 hojas opalina','50 HOJAS OPALINA DELGADA OFICIO','',6,63),(60,'2026-01-20','2026-01-23',330.00,0.00,330.00,'Etiquetas bautismo + luctuoso','-70 etiquetas de 4.5x5 en calcomanía de bautismo\r\n60 etiquetas 5to aniversario luctuoso 4x4cm couche','',4,64),(61,'2026-01-21',NULL,450.00,0.00,450.00,'Sello autoentintable chico','SELLO AUTOENTINTABLE CHICO 1.4X4.4CM','',4,65),(62,'2026-01-21','2026-01-29',450.00,450.00,0.00,'Lona Raspados Soky','LONA 1.5X1\r\nRASPADOS SOKY\r\nRASPADOS ARTESANALES Y PUPUSAS REVUELTAS, QUESO CON OROPO Y CALABZA CON QUESO','',4,66),(63,'2026-01-23','2026-01-24',450.00,450.00,0.00,'Etiqueta botella 21x3','HACER ETIQUETA PARA BOTELLA DE LA MEDIDA DE 21X3\r\nEL DISEÑO DICE LA CLIENTA QUE YA SE LO HICIERON AQUI','',6,67),(64,'2026-01-23','2026-02-04',1090.00,500.00,590.00,'Boletos rifa motocicleta','500 BOLETOS PARA RIFA DOS FOLIOS 1 TINTA COLOR NEGRO\r\nRIFA DE MOTOCICLETA Z250 ITALIKA MOD 2026 QUE SE LLEVARA A CABO CON LA LOTERIA NACIONAL\r\nCOSTO DEL BOLETO $250 CON DOS OPORTUNIDADES DE GANAR\r\nA BENEFICIO DE LAS FIESTAS DEL SEÑOR MILAGROSO EL DIA VIERNES 3 DE ABRIL 2026','',3,68),(65,'2026-01-24','2026-02-01',650.00,300.00,350.00,'Letreros vinil trovicel','13 LETREROS VINIL TROVICEL 30X20 CM\r\n6 PROHIBIDO TABACO (MANDO LA IMAGEN POR WHATS)\r\n1 QUE DIGA (COCINA) EN FONDO AZUL\r\n1 QUE DIGA (SERVICIOS DE ENFERMERIA) EN FONDO BLANCO\r\n2 QUE DIGAN (DORMITORIOS) EN FONDO AZUL\r\n2 QUE DIGAN (ÁREA DE LAVANDERÍA) EN FONDO AZUL\r\n1 QUE DIGA (PELIGRO GAS INFLAMABLE) EN FONDO AMARILLO','',2,3),(66,'2026-01-25','2026-01-31',210.00,105.00,105.00,'Invitación K-pop Gianna','30 invitaciones de 10x7cm de alto guerreas kpop\r\nGIANNA\r\n5 AÑOS\r\nSABADO 28 DE FEBREERO\r\n4:30PM, EN MI CASA\r\n33 2455 9354 Imagen de referencia','',2,69),(67,'2026-01-26','2026-01-30',30.00,30.00,0.00,'Invitación misa aniversario','DECIMOTERCERO ANIVERSARIO DE LA SEÑORA ELISA LORETO FLORES\r\nPARA HACERLES UNA INVITACION A MISA\r\n5:00PM\r\n02/02/2026\r\nDe 5cm  de ancho por lo que de de alto, máximo 17cm\r\n1 hoja','',6,70),(68,'2026-01-27','2026-01-29',900.00,500.00,400.00,'Cambio disco + Win11','Cambio de disco duro (1 Tera)\r\nSin respaldo\r\nInstalar Windows 11\r\nOffice\r\nLimpieza de hardware con cambio de pasta térmica\r\nCotizar cambio de bateria','',3,71),(69,'2026-01-27','2026-01-31',300.00,100.00,200.00,'Impresión coroplast','IMPRESION DE CORPLAST','',6,1),(70,'2026-01-28','2026-02-01',350.00,150.00,200.00,'Lona Tacos El Cheque','1 LONA DE 1X1\r\nTACOS EL CHEQUE \r\nLE OFRECE:\r\nLONCHES, TACOS Y CHIMICHANGAS (QUESADILLA CON CHAMPIÑON, PIÑA Y CARNE A SU ELECCION) ADOBADA, BISTEC, CHORIZO Y TRIPA\r\nSERVICILIO A DOMICILIO: 3861261323','',6,73),(71,'2026-01-28',NULL,0.00,0.00,0.00,'Millar recetas','1 MILLAR DE RECETAS','',6,74),(72,'2026-01-30','2026-02-10',50.00,50.00,0.00,'Tarjeta PVC','TARJETA PVC','',4,75),(73,'2026-01-31','2026-02-13',700.00,0.00,700.00,'Actualizar Windows + SSD','ACTUALIZAR A OTRO WINDOWS PARA QUE ESTE MAS RAPIDA, ESTA LENTA \r\nCOTIZAR BATERIA','Cambio de disco HDD por un disco SSD\r\n\r\nPAGADO TRANSFIRIO',5,76),(74,'2026-01-31',NULL,0.00,0.00,0.00,'Sin internet','NO AGARRA SEÑAL DE INTERNET, EL CLIENTE FORMATEO LA LAPTOP Y SIGUIO IGUAL','',5,77),(75,'2026-02-03','2026-02-12',2160.00,700.00,1460.00,'Vinil suaje 1 metro','1 METRO VINIL SUAJE CIRCULO (IMAGEN WHATS) 4CM\r\n1 METRO VINIL SUAJE CUADRADO (IMAGEN WHATS) 4X3\r\n1 METRO VINIL SUAJE CIRCULO LOGO ANTIGUO 4CM\r\n1 METRO VINIL PLATA SUAJE\r\n1 METRO VINIL DORADO SUAJE','QUEDA PENDIENTE COTIZACION (DISEÑO Y VINIL)',4,78),(76,'2026-02-05',NULL,400.00,400.00,0.00,'Vinil 70 cm imágenes','VINIL 70 CM LA IMAGEN  CUADRA LA MISERICORDIA  \r\nQUE SE LLENE EL MT CON OTRAS IMAGENES DEL TAMAÑO QUE QUEPAN','',2,79),(77,'2026-02-06','2026-02-07',0.00,0.00,0.00,'Instalar Office','INSTALAR OFFICE','',6,80),(78,'2026-02-10',NULL,0.00,0.00,0.00,'Orden sin detalle','','',5,81),(79,'2026-02-11',NULL,0.00,0.00,0.00,'Editar logo ganadería','Editar Logo de Ganadería Pulido\r\nLas especificaciones fueron dadas en persona','',3,82),(80,'2026-02-11','2026-02-16',0.00,0.00,0.00,'Formateo con respaldo','ESTA LENTA, QUIERE FORMATEO CON RESPALDO','',2,83),(81,'2026-02-11','2026-02-16',0.00,0.00,0.00,'Impresión baja tinta','IMPRIME CON TINTA MUY BAJITO PERO SI TIENE TINTA','',2,83),(82,'2026-02-14',NULL,0.00,0.00,0.00,'No enciende','No enciende ','',5,85),(83,'2026-02-14',NULL,0.00,0.00,0.00,'Corto al encender','HACE CORTO AL PRENDER, SE LES FUE LA LUZ Y AL MOMENTO DE PRENDERLA PARPADEA ','',2,86),(84,'2026-02-16',NULL,1550.00,1000.00,550.00,'Lona 7.6x0.76','1 LONA DE 7.6X.76','',6,87),(85,'2026-02-17',NULL,550.00,200.00,350.00,'Sello mediano','SELLO AUTOENTINTABLE MEDIANO 6X2\r\nLLANTERA MOVIL LOS PRIMOS\r\nSERVICIOS LAS 24HRS\r\n3861465586','',6,88);
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
  `estatus` enum('Proceso','Enviado a Tequila','Listo para Entrega','Cliente Avisado','Entregado','Cancelado','Retrasado') NOT NULL,
  `CostoDiseño` decimal(10,2) DEFAULT NULL,
  `EsDigital` tinyint(1) NOT NULL DEFAULT '0',
  `MedioEntrega` varchar(30) DEFAULT NULL,
  `idNota` int NOT NULL,
  `idDiseñador` int DEFAULT NULL,
  PRIMARY KEY (`idDiseño`),
  KEY `fk_NotaDiseño_Nota1_idx` (`idNota`),
  KEY `fk_NotaDiseño_UsuarioTrabaja` (`idDiseñador`),
  CONSTRAINT `fk_NotaDiseño_Nota1` FOREIGN KEY (`idNota`) REFERENCES `nota` (`idNota`),
  CONSTRAINT `fk_NotaDiseño_UsuarioTrabaja` FOREIGN KEY (`idDiseñador`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notadiseño`
--

LOCK TABLES `notadiseño` WRITE;
/*!40000 ALTER TABLE `notadiseño` DISABLE KEYS */;
INSERT INTO `notadiseño` VALUES (1,'Entregado',0.00,0,NULL,1,NULL),(2,'Entregado',0.00,0,NULL,2,NULL),(3,'Entregado',0.00,0,NULL,3,4),(4,'Entregado',200.00,0,NULL,6,5),(5,'Entregado',0.00,0,NULL,7,NULL),(6,'Entregado',80.00,0,NULL,8,4),(7,'Entregado',150.00,0,NULL,9,2),(8,'Entregado',200.00,0,NULL,10,2),(9,'Entregado',100.00,0,NULL,11,4),(10,'Entregado',150.00,0,NULL,12,4),(11,'Entregado',150.00,0,NULL,14,5),(12,'Entregado',100.00,0,NULL,15,5),(13,'Entregado',100.00,0,NULL,16,5),(14,'Entregado',150.00,0,NULL,17,4),(15,'Entregado',100.00,0,NULL,19,4),(16,'Entregado',30.00,0,NULL,21,NULL),(17,'Entregado',100.00,0,NULL,22,4),(18,'Entregado',120.00,0,NULL,23,4),(19,'Entregado',150.00,0,NULL,24,2),(20,'Entregado',100.00,0,NULL,25,5),(21,'Entregado',0.00,0,NULL,26,NULL),(22,'Entregado',150.00,0,NULL,27,5),(23,'Entregado',0.00,0,NULL,28,NULL),(24,'Entregado',0.00,0,NULL,29,NULL),(25,'Entregado',280.00,0,NULL,31,5),(26,'Entregado',0.00,0,NULL,32,NULL),(27,'Entregado',400.00,0,NULL,33,5),(28,'Entregado',150.00,0,NULL,34,4),(29,'Entregado',0.00,0,NULL,35,3),(30,'Entregado',0.00,0,NULL,36,NULL),(31,'Entregado',0.00,0,NULL,37,NULL),(32,'Enviado a Tequila',0.00,0,NULL,38,NULL),(33,'Entregado',0.00,0,NULL,39,NULL),(34,'Retrasado',0.00,0,NULL,40,NULL),(35,'Entregado',0.00,0,NULL,41,NULL),(36,'Entregado',0.00,0,NULL,42,NULL),(37,'Entregado',100.00,0,NULL,43,5),(38,'Entregado',0.00,0,NULL,45,NULL),(39,'Entregado',0.00,0,NULL,49,NULL),(40,'Entregado',0.00,0,NULL,50,NULL),(41,'Retrasado',0.00,0,NULL,51,NULL),(42,'Entregado',0.00,0,NULL,54,5),(43,'Retrasado',150.00,0,NULL,55,NULL),(44,'Entregado',0.00,0,NULL,57,NULL),(45,'Entregado',0.00,0,NULL,58,NULL),(46,'Entregado',0.00,0,NULL,59,NULL),(47,'Entregado',200.00,0,NULL,60,4),(48,'Cliente Avisado',0.00,0,NULL,61,4),(49,'Entregado',150.00,0,NULL,62,5),(50,'Entregado',100.00,0,NULL,63,2),(51,'Entregado',150.00,0,NULL,64,2),(52,'Entregado',0.00,0,NULL,65,NULL),(53,'Entregado',150.00,0,NULL,66,2),(54,'Entregado',25.00,0,NULL,67,2),(55,'Entregado',0.00,0,NULL,69,NULL),(56,'Entregado',150.00,0,NULL,70,NULL),(57,'Retrasado',0.00,0,NULL,71,NULL),(58,'Entregado',0.00,0,NULL,72,NULL),(59,'Entregado',200.00,0,NULL,75,4),(60,'Enviado a Tequila',0.00,0,NULL,76,NULL),(61,'Retrasado',0.00,0,NULL,79,NULL),(62,'Enviado a Tequila',150.00,0,NULL,84,2),(63,'Retrasado',0.00,0,NULL,85,NULL);
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
  `estatus` enum('Proceso','Enviado a Tequila','Listo para Entrega','Cliente Avisado','Entregado','Cancelado','Retrasado') NOT NULL,
  `DescripcionEquipo` varchar(250) NOT NULL,
  `idNota` int NOT NULL,
  `idTecnico` int DEFAULT NULL,
  PRIMARY KEY (`idMantenimiento`),
  KEY `fk_NotaMantenimiento_Nota1_idx` (`idNota`),
  KEY `fk_NotaMantenimiento_UsuarioTrabaja` (`idTecnico`),
  CONSTRAINT `fk_NotaMantenimiento_Nota1` FOREIGN KEY (`idNota`) REFERENCES `nota` (`idNota`),
  CONSTRAINT `fk_NotaMantenimiento_UsuarioTrabaja` FOREIGN KEY (`idTecnico`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notamantenimiento`
--

LOCK TABLES `notamantenimiento` WRITE;
/*!40000 ALTER TABLE `notamantenimiento` DISABLE KEYS */;
INSERT INTO `notamantenimiento` VALUES (1,'Laptop','Acer','','','Cargador','','Entregado','Color negra',4,1),(2,'Impresora','Epson','L210','','Cable de energia','','Entregado','Color negro con golpe trasero',5,NULL),(3,'LAPTOP','ATVIO','W1415A','S/C','CARGADOR','','Entregado','Desgaste en pantalla y teclado',13,3),(4,'Laptop','HP','15-da0090la','3566','Cargador, 2 Mouses, Mousepad y mochila','','Entregado','Laptop negra buenas condiciones',18,NULL),(5,'LAPTOP','DELL','LATITUDE 7490','','CARGADOR','','Entregado','Negra con calca',20,3),(6,'LAPTOP','DELL','INSPIRON 153000','160492','CARGADOR','Regresar para activar Revit (+200)','Cliente Avisado','Desinstalar CivilCAD',30,1),(7,'LAPTOP','HP','14CF2112WM','0907','CARGADOR','Insertar almacenamiento externo','Entregado','Rosa-plateada',44,NULL),(8,'CPU','LENOVO','THINKCENTRE','Lideal01.','','','Retrasado','CPU chico cuadrado con antena rota',46,NULL),(9,'Laptop','Lenovo','ThinkPad','','Maletin, cargador','','Retrasado','Color negro',47,NULL),(10,'LAPTOP','ASUS','CHROMEBOOK','','CARGADOR, BOLSA','','Entregado','Plateado',48,NULL),(11,'CPU','Acteck','','Sin contraseña','Cable energia','','Cliente Avisado','Gabinete negro',52,NULL),(12,'COMPUTADORA TODO EN UNO','ACER','','1234','Cable energia','','Retrasado','Negra con gris',53,NULL),(13,'LAPTOP','HP','14-ba00fla','1981','Cable carga y maletin azul','','Retrasado','Plata',56,NULL),(14,'Laptop','DELL','Inspiron 15','DellCorei7','Cargador','','Entregado','Azul con desgaste y calcas teclado',68,NULL),(15,'LAPTOP','HP','14-cf2517l4','1920','CARGADOR','Cambio HDD por SSD','Entregado','Gris con espejo mariposas',73,3),(16,'LAPTOP','HP','ELITEBOOK','0712','CARGADOR','','Retrasado','Plateado bordes redondos',74,NULL),(17,'LAPTOP','ASUS','','','CARGADOR','','Entregado','Gris',77,NULL),(18,'IMPRESORA','EPSON','L1250','','Cable corriente','','Retrasado','Negra',78,NULL),(19,'CPU','LENOVO','','FABIOLA11','Cable','','Entregado','Negro',80,NULL),(20,'IMPRESORA','EPSON','L355','','Cable','','Entregado','Negro',81,NULL),(21,'CPU','Lenovo','Think centre','','Cable alimentación','','Retrasado','CPU chico con cinta en medio',82,NULL),(22,'CPU','ASUS','','','Cable','','Retrasado','Negro',83,NULL);
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
INSERT INTO `tipomantenimiento` VALUES (1,'Mantenimiento Preventivo'),(2,'Mantenimiento Correctivo'),(3,'Mantenimiento Predictivo'),(4,'Mantenimiento de Actualización'),(5,'Software'),(6,'Impresoras');
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
  `FechaNacimiento` date DEFAULT NULL,
  `Contraseña` varchar(250) NOT NULL,
  `Estatus` enum('Activo','Inactivo') NOT NULL,
  PRIMARY KEY (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Claudia Guadalupe Moya Gomez','Claudia','1984-08-29','$2y$10$zjW5qB.tQPL27yATmcJvTO.FK8e2Uk10wN4pyRYVTcsMF1utD2wsS','Activo'),(2,'Alexia Yanett Avila Ornelas','Alexia','2001-11-29','$2y$10$4r7Hc4P8hPmnFpODE3gRMePPQ1nsYpaExnSlyr6l45AcbD8HCjfH2','Activo'),(3,'Jose Maria','Chema','2000-10-30','$2y$10$SOYAvlY8gAOb9UhaEkb7vO4lMuhPaB/mhuy0WHtIuRn6xK45RpbpO','Activo'),(4,'Deisy Manuela Avila Ornelas','Deisy','1997-10-04','$2y$10$bPaqst0BqMutvpIFlnQIpOVefVspDsh1nu5a3KSHOFtlbF/bhisji','Activo'),(5,'Estefania Camacho Rodriguez','Estefania','2001-10-15','$2y$10$BNQsL1ZIfFSApLxv.1XK2O/jhXqsVRT8VSvTQ0gOg20bav7S45I9i','Activo'),(6,'Norma Godina','Norma','1992-04-28','$2y$10$.qtdCabTeO7.SpQtksehpOBekPTCDghsomH2ZTOSvtoraoNjULg7u','Activo');
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarioroles`
--

LOCK TABLES `usuarioroles` WRITE;
/*!40000 ALTER TABLE `usuarioroles` DISABLE KEYS */;
INSERT INTO `usuarioroles` VALUES (12,1,1),(10,2,2),(11,2,3),(15,3,3),(16,3,4),(13,4,3),(14,5,3),(17,6,2),(18,6,3);
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

-- Dump completed on 2026-02-20  2:44:44
