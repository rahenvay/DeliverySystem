-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: delivery
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `pickup_point` varchar(225) NOT NULL,
  `destination` varchar(225) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','assigned','picked_up','in_transit','delivered','cancelled') NOT NULL,
  `driver_id` int DEFAULT NULL,
  `cancellation_reason` text,
  PRIMARY KEY (`order_id`),
  KEY `client_id` (`client_id`),
  KEY `driver_id` (`driver_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `user` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,4,'Esther hall no 210','Elijah Hall no 210',0.00,'delivered',NULL,NULL),(2,4,'Esther hall no 211','Elijah Hall no 211',0.00,'delivered',NULL,NULL),(3,4,'it 220','it 230',0.00,'delivered',NULL,NULL),(4,4,'library 2nd floor','it 224',0.00,'delivered',NULL,NULL),(5,4,'it 220','indonesia',0.00,'delivered',NULL,NULL),(6,3,'it 220','Elijah Hall no 210',300.00,'delivered',NULL,NULL),(7,4,'Bekasi, galaxy','AIU ',50.00,'delivered',NULL,NULL),(8,4,'Thailand','indonesia',450.00,'delivered',NULL,NULL),(9,4,'Merauke','Sabang',1000.00,'delivered',NULL,NULL),(10,4,'Thailand','indonesia',150.00,'delivered',NULL,NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `permission` enum('admin','client','driver') DEFAULT 'client',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'rahenvay@gmail.com','$2y$10$66w1TrsGJMrogXhGCXwMT.ASPe86YfzBX52oCWYAIBxllFhaROcbi','rahenvay naibaho','client','2024-09-16 23:48:44'),(3,'rahenvay0510@gmail.com','$2y$10$JC0eQNSe4JUXXTOhJES5ReJwtoOxLhDuLk4caW.LxfjpaT5.wrizW','Rahenvay Arvin Naibaho','client','2024-09-18 16:03:36'),(4,'bebi@gmail.com','$2y$10$pvAzZGHf4JcGC7Uv9DwCNOoz5YSLeZTLTug0CCPI3uyqkAMCT8QDi','Abebi Nakia Christine','client','2024-09-18 16:08:02'),(7,'bob@gmail.com','$2y$10$DA35LM2KU66w8m1hIUWNkeDg7J1U97SPec1yD8G31AFfK8E9O6abW','bob','client','2024-09-18 16:41:12'),(9,'rahen@gmail.com','$2y$10$Znw/F9NdA629IEX1MYPVZ.OC.EB75huu.NekhExpLYlAOXXp43TDi','rahen','client','2024-09-20 13:13:41'),(11,'admin@gmail.com','$2y$10$zZRZ3jQvLeiPwahv/wgrt.KSZQZz0mlqRyW0iKZhS4saqsqdHWPkq','admin','admin','2024-09-20 13:51:13'),(12,'driver@gmail.com','$2y$10$cxNUtKtvoJWaRqXAzeVaZu/0s/WnIXNW2R1cxnaT920QUuhUyHd5a','driver','driver','2024-09-20 13:52:40'),(14,'driver2@gmail.com','$2y$10$Qu.Huzv/IwUw6zNya6r.L.v516Cry7bXTCwVJQgGbBw.8dtUx.dwG','komeng','driver','2024-09-23 13:41:07');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-26 11:49:56
