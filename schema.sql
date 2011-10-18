-- MySQL dump 10.13  Distrib 5.1.53, for suse-linux-gnu (i686)
--
-- Host: localhost    Database: shlomin
-- ------------------------------------------------------
-- Server version	5.1.53-log

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
-- Table structure for table `acl`
--

DROP TABLE IF EXISTS `acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl` (
  `login` varchar(64) NOT NULL COMMENT 'Идентификатор пользователя',
  `hash` char(32) NOT NULL COMMENT 'хеш пароля',
  `role` set('guest','staff','administrator') NOT NULL DEFAULT 'guest',
  PRIMARY KEY (`login`),
  KEY `role` (`role`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица контроля доступа';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl`
--

LOCK TABLES `acl` WRITE;
/*!40000 ALTER TABLE `acl` DISABLE KEYS */;
INSERT INTO `acl` VALUES ('admin','f1c2820be12b42bb0c4f7208919b5c0a','administrator');
/*!40000 ALTER TABLE `acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` smallint(6) NOT NULL,
  `headline` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `createDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `changeDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (35,41,'Анализ сетевой активности с помощью MRTG','Описание программы MRTG анализирующей сетевой трафик через snmp','2011-10-18 16:55:06','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `sequence` int(11) NOT NULL DEFAULT '999',
  `parent` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `dateCreate` datetime NOT NULL,
  `dateChange` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (37,999,'Программирование','PHP','2011-10-18 10:19:56','0000-00-00 00:00:00'),(39,999,'Программирование','python','2011-10-18 10:20:42','0000-00-00 00:00:00'),(41,999,'Администрирование','Сетевое администирование','2011-10-18 10:21:28','0000-00-00 00:00:00'),(63,999,'','Программирование','2011-10-18 11:27:00','0000-00-00 00:00:00'),(64,999,'','Администрирование','2011-10-18 11:27:10','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageID` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user` varchar(32) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pageID` (`pageID`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `log` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
INSERT INTO `log` VALUES (1,'2011-09-20 10:33:06',''),(2,'2011-09-20 10:33:44','Строка \"\" неверного формата'),(3,'2011-09-20 10:34:46','Строка \"\" неверного формата'),(4,'2011-09-20 10:38:04','Строка \"\" неверного формата'),(5,'2011-09-20 10:39:19','Строка \"\" неверного формата'),(6,'2011-09-20 10:41:29','Строка \"\" неверного формата'),(7,'2011-09-20 10:52:48','Неудалось добавить строку \"Array\" из-за ошибки Duplicate entry \'petya@mail\' for key \'PRIMARY\''),(8,'2011-09-20 10:52:49','Неудалось добавить строку \"Array\" из-за ошибки Duplicate entry \'vasya@mail\' for key \'PRIMARY\''),(9,'2011-09-20 10:52:49','Неудалось добавить строку \"Array\" из-за ошибки Duplicate entry \'natasha@mail\' for key \'PRIMARY\''),(10,'2011-09-20 10:52:49','Неудалось добавить строку \"Array\" из-за ошибки Duplicate entry \'tolya@mail\' for key \'PRIMARY\''),(11,'2011-09-20 10:52:49','Неудалось добавить строку \"Array\" из-за ошибки Duplicate entry \'petya@mail\' for key \'PRIMARY\''),(12,'2011-09-20 10:52:49','Строка \"fake\r\" неверного формата'),(13,'2011-09-20 10:58:54','Неудалось добавить строку \"Array\n(\n    [0] => petya@mail\n    [1] => us\n    [2] => alabama\n    [3] => qwerty\n)\n\" из-за ошибки Duplicate entry \'petya@mail\' for key \'PRIMARY\''),(14,'2011-09-20 10:58:54','Неудалось добавить строку \"Array\n(\n    [0] => vasya@mail\n    [1] => ru\n    [2] => \n    [3] => qwerty\n)\n\" из-за ошибки Duplicate entry \'vasya@mail\' for key \'PRIMARY\''),(15,'2011-09-20 10:58:54','Неудалось добавить строку \"Array\n(\n    [0] => natasha@mail\n    [1] => uk\n    [2] => \n    [3] => \n)\n\" из-за ошибки Duplicate entry \'natasha@mail\' for key \'PRIMARY\''),(16,'2011-09-20 10:58:54','Неудалось добавить строку \"Array\n(\n    [0] => tolya@mail\n    [1] => us\n    [2] => alaska\n    [3] => \n)\n\" из-за ошибки Duplicate entry \'tolya@mail\' for key \'PRIMARY\''),(17,'2011-09-20 10:58:54','Неудалось добавить строку \"Array\n(\n    [0] => petya@mail\n    [1] => us\n    [2] => alabama\n    [3] => снова Петя\n)\n\" из-за ошибки Duplicate entry \'petya@mail\' for key \'PRIMARY\''),(18,'2011-09-20 10:58:54','Неудалось добавить строку \"Array\n(\n    [0] => sasha@mail\n    [1] => ru\n    [2] => \n    [3] => \n)\n\" из-за ошибки Duplicate entry \'sasha@mail\' for key \'PRIMARY\''),(19,'2011-09-20 10:58:54','Забыли почту'),(20,'2011-09-20 10:58:54','Забыли страну'),(21,'2011-09-20 10:58:55','Строка \"fake\r\" неверного формата'),(22,'2011-09-20 10:58:55','Неудалось добавить строку \"Array\n(\n    [0] => zhenya@mail\n    [1] => ru\n    [2] => \n    [3] => \n)\n\" из-за ошибки Duplicate entry \'zhenya@mail\' for key \'PRIMARY\''),(23,'2011-09-20 11:15:03','Неудалось добавить строку \"Array\n(\n    [0] => petya@mail\n    [1] => comp\n    [2] => US\n    [3] => alabama\n    [4] => снова Петя\n)\n\" из-за ошибки Duplicate entry \'petya@mail\' for key \'PRIMARY\''),(24,'2011-09-20 11:15:03','пустая почта в \"Array\n(\n    [0] => \n    [1] => comp\n    [2] => RU\n    [3] => \n    [4] => \n)\n\"'),(25,'2011-09-20 11:15:03','Строка \"fake;;;\r\" неверного формата'),(26,'2011-09-20 11:15:03','Строка \"fake\r\" неверного формата');
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realty`
--

DROP TABLE IF EXISTS `realty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `realty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal` tinyint(1) NOT NULL DEFAULT '0',
  `obj` int(11) NOT NULL,
  `house` int(11) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT '1',
  `floor` int(11) NOT NULL DEFAULT '1',
  `last_floor` tinyint(1) NOT NULL DEFAULT '0',
  `square` char(11) NOT NULL,
  `cost` decimal(4,2) NOT NULL,
  `metro` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL,
  `city` varchar(64) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deal` (`deal`),
  KEY `obj` (`obj`),
  KEY `house` (`house`),
  KEY `amount` (`amount`),
  KEY `floor` (`floor`),
  KEY `cost` (`cost`),
  KEY `last_floor` (`last_floor`),
  KEY `metro` (`metro`),
  KEY `city` (`city`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realty`
--

LOCK TABLES `realty` WRITE;
/*!40000 ALTER TABLE `realty` DISABLE KEYS */;
INSERT INTO `realty` VALUES (1,0,0,1,3,2,1,'82/56/8','97.94','Алтуфьево','Вечнозеленый бульвар 11','Москва','очень хороший дом'),(2,1,0,0,2,3,1,'105/89/12','5.10','','Скобяное шоссе д.11','Сергиев Посад',''),(3,1,1,4,1,2,0,'15','9.90','Славянский бульвар','Улица и дом','Софрино',''),(5,0,0,0,2,2,0,'14','9.90','Аннино','дом и улица','Зеленоград',''),(6,0,1,3,3,2,0,'45/44/43','0.90','Автозаводская','Красная площадь д.2','Сергиев Посад','оооо'),(7,0,1,3,1,2,1,'45/34/12','4.40','','дом и улица','Сергиев Посад','нет заметки'),(8,0,1,1,2,3,0,'45/34/12','4.40','','дом и улица','Сергиев Посад','нет заметки');
/*!40000 ALTER TABLE `realty` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `email` varchar(32) NOT NULL,
  `company` varchar(20) NOT NULL,
  `country` char(2) NOT NULL,
  `state` varchar(20) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('natasha@mail','comp','UK','',''),('petya@mail','comp','US','alabama','qwerty'),('sasha@mail','comp','RU','',''),('tolya@mail','comp','US','alaska',''),('vasya@mail','comp','RU','','qwerty'),('zhenya@mail','comp','RU','','');
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

-- Dump completed on 2011-10-18 16:56:49
