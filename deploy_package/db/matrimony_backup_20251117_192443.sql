-- MySQL dump 10.13  Distrib 5.7.44, for Linux (x86_64)
--
-- Host: localhost    Database: matrimony
-- ------------------------------------------------------
-- Server version	5.7.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `password` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'admin','$2y$10$L12p10PS1QdlQod1qiSuUu19bRKij3OrF5YtbjPXb2YeozLSMMlAK','admin@matrimony.com');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_activity_logs`
--

DROP TABLE IF EXISTS `admin_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL COMMENT 'create, update, delete, approve, reject, etc',
  `entity_type` varchar(50) NOT NULL COMMENT 'user, plan, payment, message, etc',
  `entity_id` int(11) DEFAULT NULL,
  `description` text,
  `old_data` json DEFAULT NULL,
  `new_data` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin` (`admin_id`,`created_at`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `admin_activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_activity_logs`
--

LOCK TABLES `admin_activity_logs` WRITE;
/*!40000 ALTER TABLE `admin_activity_logs` DISABLE KEYS */;
INSERT INTO `admin_activity_logs` VALUES (1,1,'1','login',0,'1','\"Admin login successful\"',NULL,'223.181.46.85','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-05 11:49:43'),(2,1,'1','login',0,'1','\"Admin login successful\"',NULL,'47.15.107.110','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-16 17:12:45'),(3,1,'1','login',0,'1','\"Admin login successful\"',NULL,'157.48.93.11','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-16 18:25:20');
/*!40000 ALTER TABLE `admin_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cms_pages`
--

DROP TABLE IF EXISTS `cms_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `content` longtext,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft' COMMENT 'draft, published',
  `is_featured` tinyint(1) DEFAULT '0',
  `view_count` int(11) DEFAULT '0',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `cms_pages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cms_pages`
--

LOCK TABLES `cms_pages` WRITE;
/*!40000 ALTER TABLE `cms_pages` DISABLE KEYS */;
INSERT INTO `cms_pages` VALUES (1,'About Us','about-us','<h2>Welcome to MakeMyLove</h2><p>MakeMyLove is India\'s leading matrimonial service dedicated to helping you find your perfect life partner. With thousands of verified profiles, advanced matching algorithms, and personalized assistance, we make your search for love easier and more meaningful.</p><h3>Our Mission</h3><p>To create happy, successful marriages by connecting compatible individuals through trust, technology, and tradition.</p>','About Us - MakeMyLove Matrimony','Learn about MakeMyLove matrimonial services, our mission, and how we help thousands find their life partners.',NULL,'published',0,0,NULL,'2025-11-05 06:41:50','2025-11-05 06:41:50','2025-11-05 06:41:50'),(2,'Privacy Policy','privacy-policy','<h2>Privacy Policy</h2><p>Last updated: October 31, 2025</p><h3>Information We Collect</h3><p>We collect personal information including name, email, phone number, photos, and profile details to provide matrimonial services.</p><h3>How We Use Your Information</h3><p>Your information is used to create your profile, match you with compatible partners, and provide customer support.</p><h3>Data Security</h3><p>We implement industry-standard security measures to protect your personal information.</p>','Privacy Policy - MakeMyLove','Read our privacy policy to understand how we collect, use, and protect your personal information.',NULL,'published',0,0,NULL,'2025-11-05 06:41:50','2025-11-05 06:41:50','2025-11-05 06:41:50'),(3,'Terms of Service','terms-of-service','<h2>Terms of Service</h2><p>By using MakeMyLove services, you agree to these terms and conditions.</p><h3>User Responsibilities</h3><p>Users must provide accurate information and use the platform responsibly.</p><h3>Account Security</h3><p>You are responsible for maintaining the confidentiality of your account credentials.</p>','Terms of Service - MakeMyLove','Read our terms of service and user agreement for MakeMyLove matrimonial platform.',NULL,'published',0,0,NULL,'2025-11-05 06:41:50','2025-11-05 06:41:50','2025-11-05 06:41:50'),(4,'Success Stories','success-stories','<h2>Real Love Stories</h2><p>Discover how MakeMyLove has helped thousands of couples find their perfect match and build beautiful relationships.</p><div class=\"success-story\"><h3>Rahul & Priya</h3><p>\"We found each other on MakeMyLove and it was love at first chat! Thank you for bringing us together.\" - Married in 2024</p></div>','Success Stories - Happy Couples','Read inspiring success stories from couples who found love through MakeMyLove matrimony.',NULL,'published',0,0,NULL,'2025-11-05 06:41:50','2025-11-05 06:41:50','2025-11-05 06:41:50'),(5,'Contact Us','contact-us','<h2>Get in Touch</h2><p>Have questions? We\'re here to help!</p><p><strong>Email:</strong> support@makemylove.com</p><p><strong>Phone:</strong> +91 9876543210</p><p><strong>Address:</strong> 123 Love Street, Mumbai, India</p>','Contact Us - MakeMyLove Support','Contact MakeMyLove customer support for assistance with your matrimonial profile and services.',NULL,'published',0,0,NULL,'2025-11-05 06:41:50','2025-11-05 06:41:50','2025-11-05 06:41:50');
/*!40000 ALTER TABLE `cms_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cust_id` int(5) NOT NULL,
  `email` varchar(60) NOT NULL,
  `age` varchar(10) NOT NULL,
  `height` int(10) NOT NULL,
  `sex` varchar(6) NOT NULL,
  `religion` varchar(20) NOT NULL,
  `caste` varchar(20) NOT NULL,
  `subcaste` varchar(20) NOT NULL,
  `district` varchar(20) NOT NULL,
  `state` varchar(20) NOT NULL,
  `country` varchar(10) NOT NULL,
  `maritalstatus` varchar(20) NOT NULL,
  `profilecreatedby` varchar(20) NOT NULL,
  `education` text NOT NULL,
  `education_sub` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `body_type` text NOT NULL,
  `physical_status` text NOT NULL,
  `drink` varchar(8) NOT NULL,
  `mothertounge` text NOT NULL,
  `colour` varchar(20) NOT NULL,
  `weight` int(5) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `diet` varchar(8) NOT NULL,
  `smoke` varchar(8) NOT NULL,
  `dateofbirth` date NOT NULL,
  `occupation` text NOT NULL,
  `occupation_descr` text NOT NULL,
  `annual_income` varchar(20) NOT NULL,
  `fathers_occupation` varchar(20) NOT NULL,
  `mothers_occupation` varchar(20) NOT NULL,
  `no_bro` int(5) NOT NULL,
  `no_sis` int(5) NOT NULL,
  `aboutme` text NOT NULL,
  `profilecreationdate` date NOT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `admin_notes` text,
  `interest_notifications` tinyint(1) DEFAULT '1',
  `auto_decline_interests` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cust_id` (`cust_id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (111,0,'jhgasdasd@hjsadkl.cop','27',0,'Male','Hindu','Thiyya','sub cast1','Wayanad','Kerala','India','Single','Self','Primary','','test','testyhtjsdf','Slim','No Problem','Sometime','Malayalam','Dark',58,'O +ve','Veg','Sometime','1996-01-12','dgdsgsdf','gdsg','4654456','erfdgdsg','dsgsdgdsfgdsfgdfg',1,1,'dfgdsgdsfg','2016-02-27',0,NULL,1,0),(112,7,'dadasd@asd.com','',0,'Male','Not Applicable','Roman Cathaolic','Not Applicable','','','Not Applic','Single','Self','Primary','','kjdhkdsjfghk','QKJHKJFHSDFJKH','Slim','No Problem','No','Malayalam','Dark',0,'O +ve','Veg','No','0000-00-00','','','','','',1,1,'','2016-02-27',0,NULL,1,0),(113,12,'asdasdasd@asdfsadf.com','18',0,'Male','Hindu','Thiyya','sub cast1','Wayanad','Kerala','India','Single','Self','PG','dsadasd','Aswin','Kuttappi','Slim','No Problem','No','Malayalam','Dark',58,'O +ve','Veg','No','1998-02-14','das','dasdas','8598','dasdasd','asdasdsd',1,1,'assdfsdf sdfas fasdf asdfasdf asdf','2016-02-28',0,NULL,1,0),(114,13,'asdasdasd@asdfsadf.com','18',0,'Female','Hindu','Thiyya','sub cast1','Wayanad','Kerala','India','Single','Self','PG','dsadasd','Reshma','Reshma','Slim','No Problem','No','Malayalam','Dark',58,'O +ve','Veg','No','1998-02-14','das','dasdas','8598','dasdasd','asdasdsd',1,1,'assdfsdf sdfas fasdf asdfasdf asdf','2016-02-28',0,NULL,1,0),(115,14,'asdasdasd@asdfsadf.com','18',0,'Male','Hindu','Thiyya','sub cast1','Wayanad','Kerala','India','Single','Self','PG','dsadasd','Rahul','Rahul','Slim','No Problem','No','Malayalam','Dark',58,'O +ve','Veg','No','1998-02-14','das','dasdas','8598','dasdasd','asdasdsd',1,1,'assdfsdf sdfas fasdf asdfasdf asdf','2016-02-28',0,NULL,1,0);
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homepage_config`
--

DROP TABLE IF EXISTS `homepage_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) NOT NULL COMMENT 'hero_banner, featured_profiles, success_stories, statistics, testimonials',
  `section_title` varchar(200) DEFAULT NULL,
  `section_content` text COMMENT 'JSON data for the section',
  `is_active` tinyint(1) DEFAULT '1',
  `display_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homepage_config`
--

LOCK TABLES `homepage_config` WRITE;
/*!40000 ALTER TABLE `homepage_config` DISABLE KEYS */;
INSERT INTO `homepage_config` VALUES (1,'hero_banner','Hero Banner','{\"heading\": \"Find Your Perfect Match\", \"subheading\": \"India\'s Most Trusted Matrimony Service\", \"cta_text\": \"Register Free\", \"cta_link\": \"/register.php\", \"background_image\": \"/images/hero-bg.jpg\"}',1,1,'2025-11-05 06:41:50','2025-11-05 06:41:50'),(2,'statistics','Statistics Counter','{\"stats\": [{\"label\": \"Happy Couples\", \"value\": \"50000+\"}, {\"label\": \"Active Members\", \"value\": \"100000+\"}, {\"label\": \"Success Rate\", \"value\": \"85%\"}, {\"label\": \"Years of Service\", \"value\": \"10+\"}]}',1,2,'2025-11-05 06:41:50','2025-11-05 06:41:50'),(3,'featured_profiles','Featured Profiles','{\"title\": \"Featured Profiles\", \"subtitle\": \"Browse our premium members\", \"count\": 8}',1,3,'2025-11-05 06:41:50','2025-11-05 06:41:50'),(4,'success_stories','Success Stories','{\"title\": \"Success Stories\", \"subtitle\": \"Real people, real love stories\", \"stories\": [{\"couple\": \"Rahul & Priya\", \"quote\": \"We found love on MakeMyLove!\", \"image\": \"/images/couple1.jpg\"}, {\"couple\": \"Amit & Neha\", \"quote\": \"Best decision we ever made!\", \"image\": \"/images/couple2.jpg\"}]}',1,4,'2025-11-05 06:41:50','2025-11-05 06:41:50'),(5,'testimonials','Testimonials','{\"title\": \"What Our Members Say\", \"testimonials\": [{\"name\": \"Rohan Kumar\", \"text\": \"Amazing platform! Found my soulmate within 3 months.\", \"rating\": 5}, {\"name\": \"Anjali Sharma\", \"text\": \"Professional service with genuine profiles. Highly recommended!\", \"rating\": 5}]}',1,5,'2025-11-05 06:41:50','2025-11-05 06:41:50'),(6,'custom_html','Custom HTML/CSS Block','{\"html\": \"<div class=\"custom-section\"><h2>Welcome</h2><p>Add your custom content here</p></div>\", \"css\": \".custom-section { padding: 20px; text-align: center; }\"}',0,10,'2025-11-05 06:41:50','2025-11-05 06:41:50');
/*!40000 ALTER TABLE `homepage_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homepage_search_categories`
--

DROP TABLE IF EXISTS `homepage_search_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_search_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_type` enum('location','religion','community') NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_value` varchar(100) NOT NULL,
  `category_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_type` (`category_type`),
  KEY `category_order` (`category_order`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homepage_search_categories`
--

LOCK TABLES `homepage_search_categories` WRITE;
/*!40000 ALTER TABLE `homepage_search_categories` DISABLE KEYS */;
INSERT INTO `homepage_search_categories` VALUES (1,'location','West Bengal','West Bengal',1,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(2,'location','Madhya Pradesh','Madhya Pradesh',2,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(3,'location','Gujarat','Gujarat',3,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(4,'location','Haryana','Haryana',4,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(5,'location','Delhi','Delhi',5,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(6,'location','Rajput','Rajput',6,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(7,'location','Maharashtra','Maharashtra',7,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(8,'location','Kerala','Kerala',8,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(9,'location','Jharkhand','Jharkhand',9,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(10,'location','Karnataka','Karnataka',10,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(11,'religion','Hindu','Hindu',1,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(12,'religion','Muslim','Muslim',2,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(13,'religion','Christian','Christian',3,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(14,'religion','Protestant','Protestant',4,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(15,'religion','Muslim Sunni','Muslim Sunni',5,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(16,'religion','Jain','Jain',6,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(17,'religion','Jain - Digamber','Jain - Digamber',7,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(18,'religion','Sikh','Sikh',8,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(19,'religion','Orthodox','Orthodox',9,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(20,'religion','Catholic','Catholic',10,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(21,'religion','Christian','Christian',11,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(22,'community','Hindu','Hindu',1,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(23,'community','Maratha','Maratha',2,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(24,'community','Bhumihar','Bhumihar',3,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(25,'community','Muslim','Muslim',4,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(26,'community','Kayastha','Kayastha',5,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(27,'community','Malayalee','Malayalee',6,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(28,'community','Rajput','Rajput',7,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(29,'community','Aggarwal','Aggarwal',8,1,'2025-11-16 17:17:12','2025-11-16 17:17:12');
/*!40000 ALTER TABLE `homepage_search_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homepage_sections`
--

DROP TABLE IF EXISTS `homepage_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_subtitle` varchar(255) DEFAULT NULL,
  `section_content` text,
  `section_image` varchar(255) DEFAULT NULL,
  `section_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homepage_sections`
--

LOCK TABLES `homepage_sections` WRITE;
/*!40000 ALTER TABLE `homepage_sections` DISABLE KEYS */;
INSERT INTO `homepage_sections` VALUES (1,'hero','Shaadi Partner','Love is Looking for You','An ideal life partner and consequently they are looking for different things in a ideal match making solution.','uploads/homepage/hero_1763317942.jpg',1,1,'2025-11-16 17:17:12','2025-11-16 18:32:22'),(2,'about','JOIN US EXCLUSIVE MATCHMAKING SERVICE FOR','Shaadi Partner','An ideal life partner and consequently they are looking for different things in a ideal match making solution.','uploads/homepage/about_1763317916.jpg',2,1,'2025-11-16 17:17:12','2025-11-16 18:31:56'),(3,'bride_groom','Bride & Groom',NULL,NULL,NULL,3,1,'2025-11-16 17:17:12','2025-11-16 17:17:12'),(4,'search_by','Search Profiles By',NULL,NULL,NULL,4,1,'2025-11-16 17:17:12','2025-11-16 17:17:12');
/*!40000 ALTER TABLE `homepage_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interest_quota_usage`
--

DROP TABLE IF EXISTS `interest_quota_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interest_quota_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `interests_sent` int(11) DEFAULT '0',
  `interests_received` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`,`date`),
  KEY `idx_user_date` (`user_id`,`date`),
  CONSTRAINT `interest_quota_usage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interest_quota_usage`
--

LOCK TABLES `interest_quota_usage` WRITE;
/*!40000 ALTER TABLE `interest_quota_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `interest_quota_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interests`
--

DROP TABLE IF EXISTS `interests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `message` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_interest` (`from_user_id`,`to_user_id`),
  KEY `idx_to_user_status` (`to_user_id`,`status`),
  CONSTRAINT `interests_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interests_ibfk_2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interests`
--

LOCK TABLES `interests` WRITE;
/*!40000 ALTER TABLE `interests` DISABLE KEYS */;
/*!40000 ALTER TABLE `interests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_blacklist`
--

DROP TABLE IF EXISTS `ip_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip` (`ip_address`),
  KEY `idx_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blacklist`
--

LOCK TABLES `ip_blacklist` WRITE;
/*!40000 ALTER TABLE `ip_blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL COMMENT 'Language code (e.g., en, es, hi)',
  `name` varchar(100) NOT NULL COMMENT 'Language name (e.g., English, Spanish)',
  `native_name` varchar(100) DEFAULT NULL COMMENT 'Native language name (e.g., English, EspaÃ±ol)',
  `is_rtl` tinyint(1) DEFAULT '0' COMMENT 'Right-to-left language',
  `is_active` tinyint(1) DEFAULT '1',
  `is_default` tinyint(1) DEFAULT '0',
  `flag_icon` varchar(50) DEFAULT NULL COMMENT 'Flag icon or emoji',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'en','English','English',0,1,1,'ðŸ‡¬ðŸ‡§','2025-11-05 06:41:49','2025-11-05 06:41:49'),(2,'hi','Hindi','à¤¹à¤¿à¤¨à¥à¤¦à¥€',0,1,0,'ðŸ‡®ðŸ‡³','2025-11-05 06:41:49','2025-11-05 06:41:49'),(3,'es','Spanish','EspaÃ±ol',0,0,0,'ðŸ‡ªðŸ‡¸','2025-11-05 06:41:49','2025-11-05 06:41:49'),(4,'ar','Arabic','Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©',1,0,0,'ðŸ‡¸ðŸ‡¦','2025-11-05 06:41:49','2025-11-05 06:41:49'),(5,'fr','French','FranÃ§ais',0,0,0,'ðŸ‡«ðŸ‡·','2025-11-05 06:41:49','2025-11-05 06:41:49');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `is_deleted_by_sender` tinyint(1) DEFAULT '0',
  `is_deleted_by_receiver` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_receiver` (`to_user_id`,`is_read`,`is_deleted_by_receiver`),
  KEY `idx_sender` (`from_user_id`,`is_deleted_by_sender`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partnerprefs`
--

DROP TABLE IF EXISTS `partnerprefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partnerprefs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `custId` int(10) NOT NULL,
  `agemin` varchar(3) NOT NULL,
  `agemax` int(3) NOT NULL,
  `maritalstatus` varchar(20) NOT NULL,
  `complexion` varchar(10) NOT NULL,
  `height` int(3) NOT NULL,
  `diet` varchar(10) NOT NULL,
  `religion` varchar(15) NOT NULL,
  `caste` varchar(20) NOT NULL,
  `subcaste` varchar(20) NOT NULL,
  `mothertounge` varchar(20) NOT NULL,
  `education` varchar(30) NOT NULL,
  `occupation` varchar(30) NOT NULL,
  `country` varchar(30) NOT NULL,
  `descr` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custId` (`custId`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partnerprefs`
--

LOCK TABLES `partnerprefs` WRITE;
/*!40000 ALTER TABLE `partnerprefs` DISABLE KEYS */;
INSERT INTO `partnerprefs` VALUES (1,6,'18',30,'Single','',180,'Veg','Not Applicable','Roman Cathaolic','','','Primary','','Not Applicable','Beautiful , Super, just for fun'),(2,7,'18',40,'Single','',150,'Veg','Not Applicable','Roman Cathaolic','','','Primary','','Not Applicable',''),(3,12,'18',40,'Single','',150,'Veg','Hindu','Thiyya','','','PG','sadasdasd','Hindu',''),(4,13,'18',40,'Single','',0,'Veg','Hindu','Thiyya','','','PG','das','Hindu',''),(5,14,'18',50,'Single','',0,'Veg','Hindu','Thiyya','','','PG','das','Hindu','asdasdas da asfd afsdfasdf asjdf akjsdf kjafsdks d');
/*!40000 ALTER TABLE `partnerprefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `refund_amount` decimal(10,2) DEFAULT '0.00',
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `notes` text,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `gateway` varchar(50) DEFAULT NULL COMMENT 'stripe, paypal, etc',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_transaction` (`transaction_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `user_subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cust_id` int(10) NOT NULL,
  `pic1` varchar(25) NOT NULL,
  `pic2` varchar(40) NOT NULL,
  `pic3` varchar(40) NOT NULL,
  `pic4` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cust_id` (`cust_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photos`
--

LOCK TABLES `photos` WRITE;
/*!40000 ALTER TABLE `photos` DISABLE KEYS */;
INSERT INTO `photos` VALUES (27,6,'img.jpg','picture.jpg','picture-2.jpg','user.png'),(28,7,'banner_img_3@2x.png','article_img_2.jpg','banner_img_5@2x.png','article_img_1.jpg'),(29,12,'article_img_1.jpg','article_img_2.jpg','banner_img_2.png','banner_img_2.png'),(30,13,'team-13.jpg','thumb-intro.jpg','avatar-1.jpg','1.jpg'),(31,14,'1.jpg','img-1.jpg','avatar-1.jpg','team-13.jpg');
/*!40000 ALTER TABLE `photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `max_contacts_view` int(11) DEFAULT '0' COMMENT '0 = unlimited',
  `max_messages_send` int(11) DEFAULT '0' COMMENT '0 = unlimited',
  `max_interests_express` int(11) DEFAULT '0' COMMENT '0 = unlimited',
  `can_chat` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'Free','Basic features for free users',0.00,365,5,10,5,0,1,'2025-11-05 06:41:48'),(2,'Silver','Silver membership with more features',29.99,30,50,100,25,0,1,'2025-11-05 06:41:48'),(3,'Gold','Gold membership with premium features',49.99,30,200,500,100,1,1,'2025-11-05 06:41:48'),(4,'Platinum','Unlimited access to all features',99.99,30,0,0,0,1,1,'2025-11-05 06:41:48');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_searches`
--

DROP TABLE IF EXISTS `saved_searches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saved_searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `search_name` varchar(100) NOT NULL,
  `search_filters` json NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `saved_searches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_searches`
--

LOCK TABLES `saved_searches` WRITE;
/*!40000 ALTER TABLE `saved_searches` DISABLE KEYS */;
INSERT INTO `saved_searches` VALUES (1,1,'Professionals in My City','{\"age_max\": 35, \"age_min\": 25, \"location\": \"Delhi\", \"education\": \"Graduate\"}',0,'2025-11-05 06:41:50','2025-11-05 06:41:50'),(2,1,'Quick Match','{\"age_max\": 30, \"age_min\": 25, \"marital_status\": \"Never Married\"}',1,'2025-11-05 06:41:50','2025-11-05 06:41:50');
/*!40000 ALTER TABLE `saved_searches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_history`
--

DROP TABLE IF EXISTS `search_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `search_filters` json NOT NULL,
  `results_count` int(11) DEFAULT '0',
  `searched_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_searched_at` (`searched_at`),
  CONSTRAINT `search_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_history`
--

LOCK TABLES `search_history` WRITE;
/*!40000 ALTER TABLE `search_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_logs`
--

DROP TABLE IF EXISTS `security_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL COMMENT 'failed_login, suspicious_activity, blocked_ip, etc',
  `description` text,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event_type`,`created_at`),
  KEY `idx_user` (`user_id`,`created_at`),
  KEY `idx_ip` (`ip_address`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_logs`
--

LOCK TABLES `security_logs` WRITE;
/*!40000 ALTER TABLE `security_logs` DISABLE KEYS */;
INSERT INTO `security_logs` VALUES (1,'admin_login_failed','Admin login attempt with non-admin account: testuser from IP: 127.0.0.1',NULL,'127.0.0.1','curl/8.5.0','2025-11-16 16:59:27'),(2,'admin_login_failed','Failed admin login attempt for: admin from IP: 127.0.0.1',NULL,'47.15.107.110','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','2025-11-16 17:10:26');
/*!40000 ALTER TABLE `security_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shortlists`
--

DROP TABLE IF EXISTS `shortlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shortlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_shortlist` (`user_id`,`profile_id`),
  KEY `profile_id` (`profile_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `shortlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shortlists_ibfk_2` FOREIGN KEY (`profile_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shortlists`
--

LOCK TABLES `shortlists` WRITE;
/*!40000 ALTER TABLE `shortlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `shortlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'text' COMMENT 'text, image, json, boolean',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `site_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'homepage_banner','/images/hero-bg.jpg','image','2025-11-05 06:41:50',NULL),(2,'site_logo','/images/logo.png','image','2025-11-05 06:41:50',NULL),(3,'site_name','Matrimony Portal','text','2025-11-05 06:41:50',NULL),(4,'site_tagline','Find Your Perfect Match','text','2025-11-05 06:41:50',NULL);
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_config`
--

DROP TABLE IF EXISTS `sms_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gateway` varchar(50) NOT NULL COMMENT 'twilio, msg91, etc',
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `sender_id` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `config_data` text COMMENT 'JSON for additional gateway-specific settings',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_config`
--

LOCK TABLES `sms_config` WRITE;
/*!40000 ALTER TABLE `sms_config` DISABLE KEYS */;
INSERT INTO `sms_config` VALUES (1,'twilio',NULL,NULL,NULL,0,'{\"account_sid\": \"\", \"auth_token\": \"\", \"from_number\": \"\"}','2025-11-05 06:41:49','2025-11-05 06:41:49'),(2,'msg91',NULL,NULL,NULL,0,'{\"auth_key\": \"\", \"route\": \"4\", \"country_code\": \"91\"}','2025-11-05 06:41:49','2025-11-05 06:41:49');
/*!40000 ALTER TABLE `sms_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_logs`
--

DROP TABLE IF EXISTS `sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending' COMMENT 'pending, sent, failed',
  `error_message` text,
  `gateway` varchar(50) DEFAULT NULL COMMENT 'twilio, msg91, etc',
  `gateway_message_id` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_event_type` (`event_type`),
  CONSTRAINT `sms_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_logs`
--

LOCK TABLES `sms_logs` WRITE;
/*!40000 ALTER TABLE `sms_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_templates`
--

DROP TABLE IF EXISTS `sms_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `event_trigger` varchar(50) NOT NULL COMMENT 'registration, plan_expiry, new_match, interest_received, message_received, etc',
  `subject` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `variables` text COMMENT 'JSON array of available variables',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_templates`
--

LOCK TABLES `sms_templates` WRITE;
/*!40000 ALTER TABLE `sms_templates` DISABLE KEYS */;
INSERT INTO `sms_templates` VALUES (1,'Welcome SMS','registration','Welcome to MakeMyLove','Hi {{name}}, Welcome to MakeMyLove! Your account has been created successfully. Start finding your perfect match today. Login: {{login_url}}','[\"name\", \"email\", \"login_url\"]',1,'2025-11-05 06:41:49','2025-11-05 06:41:49'),(2,'Plan Expiry Reminder','plan_expiry','Your Plan is Expiring Soon','Hi {{name}}, Your {{plan_name}} plan expires on {{expiry_date}}. Renew now to continue accessing premium features. Visit: {{renew_url}}','[\"name\", \"plan_name\", \"expiry_date\", \"renew_url\"]',1,'2025-11-05 06:41:49','2025-11-05 06:41:49'),(3,'New Interest Received','interest_received','New Interest from {{sender_name}}','Hi {{name}}, {{sender_name}} has expressed interest in your profile. Login to view and respond. {{profile_url}}','[\"name\", \"sender_name\", \"profile_url\"]',1,'2025-11-05 06:41:49','2025-11-05 06:41:49'),(4,'Interest Accepted','interest_accepted','Your Interest was Accepted!','Hi {{name}}, {{receiver_name}} has accepted your interest! You can now connect and message them. {{chat_url}}','[\"name\", \"receiver_name\", \"chat_url\"]',1,'2025-11-05 06:41:49','2025-11-05 06:41:49'),(5,'New Message Received','message_received','New Message from {{sender_name}}','Hi {{name}}, You have a new message from {{sender_name}}. Login to read and reply. {{inbox_url}}','[\"name\", \"sender_name\", \"inbox_url\"]',1,'2025-11-05 06:41:49','2025-11-05 06:41:49'),(6,'Payment Successful','payment_success','Payment Confirmation','Hi {{name}}, Your payment of Rs.{{amount}} for {{plan_name}} plan has been received successfully. Subscription valid till {{expiry_date}}. Thank you!','[\"name\", \"amount\", \"plan_name\", \"expiry_date\"]',1,'2025-11-05 06:41:49','2025-11-05 06:41:49');
/*!40000 ALTER TABLE `sms_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` varchar(10) NOT NULL,
  `translation_key` varchar(255) NOT NULL COMMENT 'Unique key for translation (e.g., welcome_message, login_button)',
  `translation_value` text NOT NULL COMMENT 'Translated text',
  `category` varchar(50) DEFAULT 'general' COMMENT 'Category: general, auth, profile, dashboard, etc',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_translation` (`language_code`,`translation_key`),
  KEY `idx_language` (`language_code`),
  KEY `idx_key` (`translation_key`),
  KEY `idx_category` (`category`),
  CONSTRAINT `translations_ibfk_1` FOREIGN KEY (`language_code`) REFERENCES `languages` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
INSERT INTO `translations` VALUES (1,'en','site_name','MakeMyLove','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(2,'en','welcome','Welcome','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(3,'en','home','Home','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(4,'en','about','About','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(5,'en','contact','Contact','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(6,'en','search','Search','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(7,'en','save','Save','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(8,'en','cancel','Cancel','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(9,'en','delete','Delete','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(10,'en','edit','Edit','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(11,'en','view','View','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(12,'en','close','Close','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(13,'en','submit','Submit','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(14,'en','loading','Loading...','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(15,'en','login','Login','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(16,'en','logout','Logout','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(17,'en','register','Register','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(18,'en','email','Email','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(19,'en','password','Password','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(20,'en','forgot_password','Forgot Password?','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(21,'en','remember_me','Remember Me','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(22,'en','login_success','Login successful!','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(23,'en','login_failed','Invalid credentials','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(24,'en','profile','Profile','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(25,'en','my_profile','My Profile','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(26,'en','edit_profile','Edit Profile','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(27,'en','personal_info','Personal Information','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(28,'en','contact_info','Contact Information','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(29,'en','preferences','Preferences','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(30,'en','photos','Photos','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(31,'en','upload_photo','Upload Photo','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(32,'en','dashboard','Dashboard','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(33,'en','my_matches','My Matches','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(34,'en','interests','Interests','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(35,'en','messages','Messages','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(36,'en','shortlist','Shortlist','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(37,'en','notifications','Notifications','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(38,'en','subscription_plans','Subscription Plans','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(39,'en','upgrade_plan','Upgrade Plan','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(40,'en','current_plan','Current Plan','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(41,'en','choose_plan','Choose Plan','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(42,'en','free_plan','Free Plan','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(43,'en','premium_plan','Premium Plan','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(44,'hi','site_name','à¤®à¥‡à¤• à¤®à¤¾à¤¯ à¤²à¤µ','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(45,'hi','welcome','à¤¸à¥à¤µà¤¾à¤—à¤¤ à¤¹à¥ˆ','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(46,'hi','home','à¤¹à¥‹à¤®','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(47,'hi','about','à¤•à¥‡ à¤¬à¤¾à¤°à¥‡ à¤®à¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(48,'hi','contact','à¤¸à¤‚à¤ªà¤°à¥à¤• à¤•à¤°à¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(49,'hi','search','à¤–à¥‹à¤œà¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(50,'hi','save','à¤¸à¤¹à¥‡à¤œà¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(51,'hi','cancel','à¤°à¤¦à¥à¤¦ à¤•à¤°à¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(52,'hi','delete','à¤¹à¤Ÿà¤¾à¤à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(53,'hi','edit','à¤¸à¤‚à¤ªà¤¾à¤¦à¤¿à¤¤ à¤•à¤°à¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(54,'hi','view','à¤¦à¥‡à¤–à¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(55,'hi','close','à¤¬à¤‚à¤¦ à¤•à¤°à¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(56,'hi','submit','à¤œà¤®à¤¾ à¤•à¤°à¥‡à¤‚','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(57,'hi','loading','à¤²à¥‹à¤¡ à¤¹à¥‹ à¤°à¤¹à¤¾ à¤¹à¥ˆ...','general','2025-11-05 06:41:49','2025-11-05 06:41:49'),(58,'hi','login','à¤²à¥‰à¤—à¤¿à¤¨','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(59,'hi','logout','à¤²à¥‰à¤—à¤†à¤‰à¤Ÿ','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(60,'hi','register','à¤ªà¤‚à¤œà¥€à¤•à¤°à¤£ à¤•à¤°à¥‡à¤‚','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(61,'hi','email','à¤ˆà¤®à¥‡à¤²','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(62,'hi','password','à¤ªà¤¾à¤¸à¤µà¤°à¥à¤¡','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(63,'hi','forgot_password','à¤ªà¤¾à¤¸à¤µà¤°à¥à¤¡ à¤­à¥‚à¤² à¤—à¤?','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(64,'hi','remember_me','à¤®à¥à¤à¥‡ à¤¯à¤¾à¤¦ à¤°à¤–à¥‡à¤‚','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(65,'hi','login_success','à¤²à¥‰à¤—à¤¿à¤¨ à¤¸à¤«à¤²!','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(66,'hi','login_failed','à¤…à¤®à¤¾à¤¨à¥à¤¯ à¤•à¥à¤°à¥‡à¤¡à¥‡à¤‚à¤¶à¤¿à¤¯à¤²','auth','2025-11-05 06:41:49','2025-11-05 06:41:49'),(67,'hi','profile','à¤ªà¥à¤°à¥‹à¤«à¤¼à¤¾à¤‡à¤²','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(68,'hi','my_profile','à¤®à¥‡à¤°à¥€ à¤ªà¥à¤°à¥‹à¤«à¤¼à¤¾à¤‡à¤²','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(69,'hi','edit_profile','à¤ªà¥à¤°à¥‹à¤«à¤¼à¤¾à¤‡à¤² à¤¸à¤‚à¤ªà¤¾à¤¦à¤¿à¤¤ à¤•à¤°à¥‡à¤‚','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(70,'hi','personal_info','à¤µà¥à¤¯à¤•à¥à¤¤à¤¿à¤—à¤¤ à¤œà¤¾à¤¨à¤•à¤¾à¤°à¥€','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(71,'hi','contact_info','à¤¸à¤‚à¤ªà¤°à¥à¤• à¤œà¤¾à¤¨à¤•à¤¾à¤°à¥€','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(72,'hi','preferences','à¤ªà¥à¤°à¤¾à¤¥à¤®à¤¿à¤•à¤¤à¤¾à¤à¤‚','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(73,'hi','photos','à¤«à¥‹à¤Ÿà¥‹','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(74,'hi','upload_photo','à¤«à¥‹à¤Ÿà¥‹ à¤…à¤ªà¤²à¥‹à¤¡ à¤•à¤°à¥‡à¤‚','profile','2025-11-05 06:41:49','2025-11-05 06:41:49'),(75,'hi','dashboard','à¤¡à¥ˆà¤¶à¤¬à¥‹à¤°à¥à¤¡','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(76,'hi','my_matches','à¤®à¥‡à¤°à¥‡ à¤®à¥ˆà¤š','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(77,'hi','interests','à¤°à¥à¤šà¤¿à¤¯à¤¾à¤‚','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(78,'hi','messages','à¤¸à¤‚à¤¦à¥‡à¤¶','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(79,'hi','shortlist','à¤¶à¥‰à¤°à¥à¤Ÿà¤²à¤¿à¤¸à¥à¤Ÿ','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(80,'hi','notifications','à¤¸à¥‚à¤šà¤¨à¤¾à¤à¤‚','dashboard','2025-11-05 06:41:49','2025-11-05 06:41:49'),(81,'hi','subscription_plans','à¤¸à¤¦à¤¸à¥à¤¯à¤¤à¤¾ à¤¯à¥‹à¤œà¤¨à¤¾à¤à¤‚','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(82,'hi','upgrade_plan','à¤¯à¥‹à¤œà¤¨à¤¾ à¤…à¤ªà¤—à¥à¤°à¥‡à¤¡ à¤•à¤°à¥‡à¤‚','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(83,'hi','current_plan','à¤µà¤°à¥à¤¤à¤®à¤¾à¤¨ à¤¯à¥‹à¤œà¤¨à¤¾','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(84,'hi','choose_plan','à¤¯à¥‹à¤œà¤¨à¤¾ à¤šà¥à¤¨à¥‡à¤‚','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(85,'hi','free_plan','à¤®à¥à¤«à¥à¤¤ à¤¯à¥‹à¤œà¤¨à¤¾','plans','2025-11-05 06:41:49','2025-11-05 06:41:49'),(86,'hi','premium_plan','à¤ªà¥à¤°à¥€à¤®à¤¿à¤¯à¤® à¤¯à¥‹à¤œà¤¨à¤¾','plans','2025-11-05 06:41:49','2025-11-05 06:41:49');
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_subscriptions`
--

DROP TABLE IF EXISTS `user_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  KEY `idx_user_status` (`user_id`,`status`),
  KEY `idx_end_date` (`end_date`),
  CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_subscriptions`
--

LOCK TABLES `user_subscriptions` WRITE;
/*!40000 ALTER TABLE `user_subscriptions` DISABLE KEYS */;
INSERT INTO `user_subscriptions` VALUES (1,1,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(2,12,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(3,11,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(4,10,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(5,8,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(6,14,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(7,9,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(8,13,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(9,7,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48'),(10,6,1,'2025-11-05','2026-11-05','active','2025-11-05 06:41:48','2025-11-05 06:41:48');
/*!40000 ALTER TABLE `user_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `profilestat` int(5) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(40) NOT NULL,
  `dateofbirth` date NOT NULL,
  `gender` varchar(5) NOT NULL,
  `userlevel` int(2) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_completeness` int(11) DEFAULT '0',
  `account_status` enum('active','suspended','deleted') DEFAULT 'active',
  `search_notifications` tinyint(1) DEFAULT '1',
  `last_search_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_account_status` (`account_status`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,0,'admin','$2y$10$ikpLhiSQEU5aqghH8YbCeu9socHvwswHqvVmtxEUUqBL9L2geLPBK','admin@nowhere.com','2016-02-17','male',1,NULL,0,'active',1,NULL),(6,0,'test','test','test@test.com','2016-02-11','femal',0,NULL,0,'active',1,NULL),(7,0,'shobi','shobi','jdshfkjsh@nowhere.com','0000-00-00','male',0,NULL,0,'active',1,NULL),(8,0,'Name','','E-Mail','0000-00-00','',0,NULL,0,'active',1,NULL),(9,0,'Raju','raju','raju@nowhere.com','0000-00-00','male',0,NULL,0,'active',1,NULL),(10,0,'kuttappi','kuttappi','kuttapi@kuttappi.com','0000-00-00','',0,NULL,0,'active',1,NULL),(11,0,'fdsdte','qe41234234','twetwet@sdfds.com','0000-00-00','',0,NULL,0,'active',1,NULL),(12,0,'aswin','aswin','aswin@nowhere.com','1997-01-20','male',0,NULL,0,'active',1,NULL),(13,0,'reshma','reshma','asdasdasd@asdfsadf.com','1998-02-14','femal',0,NULL,0,'active',1,NULL),(14,0,'rahul','rahul','asdasdasd@asdfsadf.com','1998-02-14','male',0,NULL,0,'active',1,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-17 19:24:44
