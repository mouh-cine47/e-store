-
--
-- Current Database: `inventory_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `inventory_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `inventory_db`;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Women','women'),(2,'Men','men'),(3,'Watches','watches'),(4,'Accessories','accessories'),(5,'Footwear','footwear'),(6,'Bags','bags'),(7,'Beauty','beauty'),(8,'Electronics','electronics');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,4,1,260.00),(2,1,3,1,200.00),(3,2,7,2,120.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_status_history_order_id` (`order_id`),
  KEY `idx_order_status_history_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
INSERT INTO `order_status_history` VALUES (1,1,'pending','Order received and is being processed.','2026-05-21 18:12:23'),(2,2,'pending','Order received and is being processed.','2026-06-02 16:32:23');
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','shipped','delivered') DEFAULT 'pending',
  `shipping_name` varchar(120) DEFAULT NULL,
  `shipping_phone` varchar(30) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,2,460.00,'pending','hakim','066666666','h2s7dc8xc','temara','Morroco','2026-05-21 18:12:23'),(2,3,240.00,'pending','messi','+1 5612146514','12s5d3df','miami','usa','2026-06-02 16:32:23');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_views`
--

DROP TABLE IF EXISTS `product_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `product_views_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_views`
--

LOCK TABLES `product_views` WRITE;
/*!40000 ALTER TABLE `product_views` DISABLE KEYS */;
INSERT INTO `product_views` VALUES (1,3,2,'::1','Local','Local','2026-06-01 13:00:56'),(2,1,2,'::1','Local','Local','2026-06-01 13:39:45'),(3,7,3,'127.0.0.1','Local','Local','2026-06-02 16:31:29'),(4,4,2,'::1','Local','Local','2026-06-03 20:54:35'),(5,9,2,'::1','Local','Local','2026-06-03 21:19:21'),(6,3,2,'::1','Local','Local','2026-06-03 21:59:27'),(7,7,2,'::1','Local','Local','2026-06-03 21:59:46'),(8,1,2,'::1','Local','Local','2026-06-03 21:59:52');
/*!40000 ALTER TABLE `product_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `color` varchar(60) DEFAULT NULL,
  `size` varchar(60) DEFAULT NULL,
  `collection_name` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'watch','watch',NULL,'casio',2,200.00,3,'uploads/products/bc9ed8da33c63691d3754aaa78f45c29.webp',1,2,'2026-05-18 16:04:03','2026-06-03 21:59:52','silver','s',NULL),(2,'computer','computer','512 SSD\r\n16 RAM','dell',8,5000.00,8,'uploads/products/71dfbdd75ed9a927133514705768e532.webp',1,0,'2026-05-21 08:59:45','2026-05-31 19:28:22','black',NULL,NULL),(3,'Daniel Steiger Gold Watch','daniel-steiger-gold-watch','Luxury gold-tone watch with minimalist dial and premium finish.','Daniel Steiger',3,200.00,11,NULL,1,2,'2026-05-21 09:50:58','2026-06-03 21:59:27','Gold','42mm','Signature'),(4,'Aster Black Chronograph','aster-black-chronograph','Chronograph with matte black case and refined leather strap.','Aster',3,260.00,7,NULL,1,1,'2026-05-21 09:50:58','2026-06-03 20:54:35','Black','44mm','Heritage'),(5,'Luna Rose Gold Watch','luna-rose-gold-watch','Elegant rose-gold watch with slim bracelet and clean indices.','Luna',3,180.00,10,NULL,1,0,'2026-05-21 09:50:58','2026-05-21 09:50:58','Rose Gold','38mm','Elegance'),(6,'Atlas Leather Belt','atlas-leather-belt','Full-grain leather belt with brushed metal buckle.','Atlas',4,45.00,30,NULL,1,0,'2026-05-21 09:50:58','2026-05-21 09:50:58','Brown','M','Classic'),(7,'Milano Leather Loafers','milano-leather-loafers','Premium loafers with soft lining and stitched sole.','Milano',5,120.00,13,NULL,1,2,'2026-05-21 09:50:58','2026-06-03 21:59:46','Black','43','Formal'),(8,'Silk Ivory Scarf','silk-ivory-scarf','Lightweight silk scarf with delicate woven pattern.','Silken',4,60.00,25,NULL,1,0,'2026-05-21 09:50:58','2026-05-21 09:50:58','Ivory','One Size','Studio'),(9,'Aurora Pearl Earrings','aurora-pearl-earrings','Minimal pearl earrings for a refined look.','Aurora',1,75.00,20,NULL,1,1,'2026-05-21 09:50:58','2026-06-03 21:19:21','Pearl','One Size','Glamour'),(10,'Urban Minimal Backpack','urban-minimal-backpack','Structured backpack with padded laptop sleeve.','Urban',6,95.00,18,NULL,1,0,'2026-05-21 09:50:58','2026-05-21 09:50:58','Gray','One Size','City'),(11,'Pure Glow Serum','pure-glow-serum','Hydrating serum with vitamin C and gentle brightening.','PureLab',7,39.00,40,NULL,1,0,'2026-05-21 09:50:58','2026-05-21 09:50:58','Clear','30ml','Glow'),(12,'Aero Noise-Cancel Headphones','aero-noise-cancel-headphones','Over-ear headphones with premium noise cancellation.','Aero',8,150.00,14,NULL,1,0,'2026-05-21 09:50:58','2026-05-21 09:50:58','Black','One Size','Studio');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@example.com','$2y$12$unS7Bqf87v3Y.UuzuqLoyuQOud0MEKrFlvIbRJ1bk0D.lbROehx5O','admin','2026-05-15 19:34:05'),(2,'hakim','hakim@gmail.com','$2y$10$YW6vpUv8XtBoTEHf66Q3AO6Wyb/kBovvzoq/TL8/rxGIwibVaZgxm','client','2026-05-21 09:52:37'),(3,'messi','messi@gmail.com','$2y$10$nPuRkEfhUBEWwHNzQbF8tevdDGxvcVzKh16PStcH9C/5d9ZBbWQOW','client','2026-06-01 17:54:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'inventory_db'
--

--
-- Dumping routines for database 'inventory_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-04  0:11:08
