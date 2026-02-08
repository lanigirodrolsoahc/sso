-- MariaDB dump 10.19  Distrib 10.6.5-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sso
-- ------------------------------------------------------
-- Server version	10.6.5-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `application`
--

DROP TABLE IF EXISTS `application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `key` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application`
--

LOCK TABLES `application` WRITE;
/*!40000 ALTER TABLE `application` DISABLE KEYS */;
INSERT INTO `application` VALUES (1,'SSO','3rTrC2qKVSv29YjG9URsnsaWsuaadNG9R+N3u1Ks9ueuY/xwBkBEgeIqaRovAoQT4WERgez1XJGFEIcM1eaa/IrHK1RRD603F9NB1yabPpOSywZVPmIBOeifON0/oL4D+J8redMTnRQHypLArRze7SJNgm06jpKUdC8EiIVjYu6Atv9BIqZ5PDQvHChno7ceLlHaLdkq0ZKQrRwU4zKWZQ==','sso app');
/*!40000 ALTER TABLE `application` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avatar`
--

DROP TABLE IF EXISTS `avatar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avatar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `content` varchar(191) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avatar`
--

LOCK TABLES `avatar` WRITE;
/*!40000 ALTER TABLE `avatar` DISABLE KEYS */;
INSERT INTO `avatar` VALUES (1,'guard','&#128130;'),(2,'person','&#x1F9CD;'),(3,'worker','&#x1F477;'),(4,'woman','&#x1F469;'),(5,'princess','&#x1F478;'),(6,'clown','&#x1F921;'),(7,'elf','&#x1F9DD;'),(8,'mage','&#x1F9D9;'),(9,'alien','&#x1F47D;'),(10,'grandpa','&#x1F9B3;'),(11,'bold','&#x1F9B2;'),(12,'hairy','&#x1F9B1;'),(13,'readhead','&#x1F9B0;'),(14,'bearded','&#x1F9D4;'),(15,'glasses','&#x1F9D3;'),(16,'boy','&#x1F466;'),(17,'grandma','&#x1F475;'),(18,'headsharf','&#x1F9D5;'),(19,'lotus','&#x1F9D8;'),(20,'unicorn','&#x1F984;'),(36,'police','&#x1F46E;'),(39,'detective','&#x1F575;&#xFE0F;'),(66,'superhero','&#x1F9B8;'),(69,'supervillain','&#x1F9B9;'),(72,'cat','&#x1F408;'),(73,'dog','&#x1F415;'),(74,'fox','&#x1F98A;'),(75,'tiger','&#x1F42F;'),(76,'bear','&#x1F43B;'),(77,'panda','&#x1F43C;'),(78,'monkey','&#x1F412;'),(79,'koala','&#x1F428;'),(80,'lion','&#x1F981;'),(81,'pig','&#x1F416;'),(82,'vampire','&#x1F9DB;'),(85,'zombie','&#x1F9DF;'),(88,'genie','&#x1F9DE;'),(91,'fairy','&#x1F9DA;'),(94,'mermaid','&#x1F9DC;'),(97,'troll','&#x1F9CC;'),(98,'robot','&#x1F916;'),(99,'smile','&#x1F600;'),(102,'wink','&#x1F609;'),(103,'cool','&#x1F60E;'),(104,'thinking','&#x1F914;'),(105,'nerd','&#x1F913;'),(106,'angel','&#x1F47C;'),(107,'devil','&#x1F608;'),(108,'cowboy','&#x1F920;'),(109,'ninja','&#x1F977;'),(110,'mask','&#x1F637;'),(111,'grinning','&#x1F600;'),(112,'melting','&#x1FAE0;'),(113,'hearts','&#x1F495;'),(114,'heartRibbon','&#x1F49D;'),(115,'heartSquare','&#x1F49F;'),(116,'alienMonster','&#x1F47E;'),(117,'ghost','&#x1F47B;'),(118,'swear','&#x1F92C;'),(119,'party','&#x1F973;'),(120,'dizzy','&#x1F635;'),(121,'exploding','&#x1F92F;'),(122,'hotFace','&#x1F975;'),(123,'dottedFace','&#x1FAE5;'),(124,'heartsSmile','&#x1F970;'),(125,'mushroom','&#x1F344;'),(126,'hibiscus','&#x1F33A;'),(127,'beetle','&#x1F41E;'),(128,'dolphin','&#x1F42C;'),(129,'dragon','&#x1F409;'),(130,'peacock','&#x1F99A;'),(131,'dodo','&#x1F9A4;'),(132,'flamingo','&#x1F9A9;'),(133,'penguin','&#x1F427;'),(134,'rooster','&#x1F413;'),(135,'pawPaw','&#x1F43E;'),(136,'rabbitFace','&#x1F430;'),(137,'mouseFace','&#x1F42D;'),(138,'cowFace','&#x1F42E;'),(139,'horse','&#x1F40E;'),(140,'tiger','&#x1F405;'),(141,'foxy','&#x1F98A;'),(142,'signFemale','&#x2640;&#xFE0F;'),(143,'signMale','&#x2642;&#xFE0F;'),(144,'signTrans','&#x26A7;'),(145,'idCard','&#x1FAAA;'),(146,'moai','&#x1F5FF;'),(147,'xRays','&#x1FA7B;'),(148,'hammerWrench','&#x1F6E0;&#xFE0F;'),(149,'computerDisk','&#x1F4BD;'),(150,'guitar','&#x1F3B8;'),(151,'violin','&#x1F3BB;'),(152,'lipstick','&#x1F484;'),(153,'crown','&#x1F451;'),(154,'hatWoman','&#x1F452;'),(155,'hatTop','&#x1F3A9;'),(156,'shorts','&#x1FA73;'),(157,'dress','&#x1F457;'),(158,'artistPalette','&#x1F3A8;'),(159,'chessPawn','&#x265F;&#xFE0F;'),(160,'joker','&#x1F0CF;'),(161,'crystalBall','&#x1F52E;'),(162,'pool8ball','&#x1F3B1;'),(163,'curling','&#x1F94C;'),(164,'boxingGlove','&#x1F94A;'),(165,'martialUniform','&#x1F94B;'),(166,'tennis','&#x1F3BE;'),(167,'basketball','&#x1F3C0;'),(168,'soccer','&#x26BD;'),(169,'trophy','&#x1F3C6;'),(170,'ribbon','&#x1F380;'),(171,'christmasTree','&#x1F384;'),(172,'jackOLantern','&#x1F383;'),(173,'shopBell','&#x1F6CE;&#xFE0F;'),(174,'rocket','&#x1F680;'),(175,'horizontalLights','&#x1F6A5;'),(176,'motorCycle','&#x1F3CD;&#xFE0F;'),(177,'sunrise','&#x1F305;'),(178,'liberty','&#x1F5FD;'),(179,'desertIsland','&#x1F3DD;&#xFE0F;'),(180,'camping','&#x1F3D5;&#xFE0F;'),(181,'snowflake','&#x2744;&#xFE0F;'),(182,'snowman','&#x26C4;'),(183,'rainbow','&#x1F308;'),(184,'firstMoonQuarter','&#x1F31B;'),(185,'alarmClock','&#x23F0;');
/*!40000 ALTER TABLE `avatar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `belonging`
--

DROP TABLE IF EXISTS `belonging`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `belonging` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rightId` int(10) unsigned NOT NULL,
  `groupId` int(10) unsigned NOT NULL,
  `value` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rightId` (`rightId`,`groupId`),
  KEY `groupIdIdx` (`groupId`),
  CONSTRAINT `groupIdIdx` FOREIGN KEY (`groupId`) REFERENCES `group` (`id`),
  CONSTRAINT `rightIdIdx` FOREIGN KEY (`rightId`) REFERENCES `right` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `belonging`
--

LOCK TABLES `belonging` WRITE;
/*!40000 ALTER TABLE `belonging` DISABLE KEYS */;
INSERT INTO `belonging` VALUES (1,1,1,2),(2,2,1,2);
/*!40000 ALTER TABLE `belonging` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group`
--

LOCK TABLES `group` WRITE;
/*!40000 ALTER TABLE `group` DISABLE KEYS */;
INSERT INTO `group` VALUES (1,'SSO-ADMIN','Administration SSO');
/*!40000 ALTER TABLE `group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `membership`
--

DROP TABLE IF EXISTS `membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membership` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subjectType` varchar(50) NOT NULL,
  `subject` int(10) unsigned NOT NULL,
  `targetType` varchar(50) NOT NULL,
  `target` int(10) unsigned NOT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `stop` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjectType` (`subjectType`,`subject`,`targetType`,`target`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membership`
--

LOCK TABLES `membership` WRITE;
/*!40000 ALTER TABLE `membership` DISABLE KEYS */;
INSERT INTO `membership` VALUES (1,'user',1,'group',1,NULL,NULL);
/*!40000 ALTER TABLE `membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passolds`
--

DROP TABLE IF EXISTS `passolds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `passolds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `archived` timestamp NOT NULL DEFAULT current_timestamp(),
  `content` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`content`),
  CONSTRAINT `userIdPassKey` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passolds`
--

LOCK TABLES `passolds` WRITE;
/*!40000 ALTER TABLE `passolds` DISABLE KEYS */;
/*!40000 ALTER TABLE `passolds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `right`
--

DROP TABLE IF EXISTS `right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `right` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  `applicationId` int(10) unsigned NOT NULL,
  `described` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`applicationId`),
  KEY `appIdKey` (`applicationId`),
  CONSTRAINT `appIdKey` FOREIGN KEY (`applicationId`) REFERENCES `application` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `right`
--

LOCK TABLES `right` WRITE;
/*!40000 ALTER TABLE `right` DISABLE KEYS */;
INSERT INTO `right` VALUES (1,'admin',1,'administrateur SSO, tout droit sur toute application'),(2,'giver',1,'fournisseur de droit si possédant');
/*!40000 ALTER TABLE `right` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `token`
--

DROP TABLE IF EXISTS `token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `content` mediumtext NOT NULL,
  `creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expiration` timestamp GENERATED ALWAYS AS (`updated` + interval 1 hour) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`type`),
  CONSTRAINT `userIdKey` FOREIGN KEY (`userId`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `token`
--

LOCK TABLES `token` WRITE;
/*!40000 ALTER TABLE `token` DISABLE KEYS */;
/*!40000 ALTER TABLE `token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstName` varchar(150) NOT NULL,
  `lastName` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `initials` varchar(7) NOT NULL,
  `creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `lastConnection` timestamp NULL DEFAULT NULL,
  `lastPwdChange` timestamp NULL DEFAULT NULL,
  `validityLimit` timestamp NULL DEFAULT NULL,
  `lastSessionCheck` timestamp NULL DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `mobile` varchar(25) DEFAULT NULL,
  `short` varchar(5) DEFAULT NULL,
  `avatarId` int(10) unsigned NOT NULL DEFAULT 1,
  `hired` timestamp NULL DEFAULT NULL,
  `fired` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxUserLogin` (`login`),
  UNIQUE KEY `idxUserBeing` (`firstName`,`lastName`),
  KEY `avatarIdKey` (`avatarId`),
  CONSTRAINT `avatarIdKey` FOREIGN KEY (`avatarId`) REFERENCES `avatar` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'admin','sso','sso@sso.sso','admin','4nNdp8rDwtkhk',1,'ADM','1984-04-07 22:00:00','1984-04-07 22:00:00',NULL,NOW(),NULL,NULL,NULL,NULL,NULL,1,NULL,NULL);
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

-- Dump completed on 2025-12-30 16:38:18
