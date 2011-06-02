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
  `nick` varchar(64) NOT NULL COMMENT 'Идентификатор пользователя',
  `password` char(32) NOT NULL COMMENT 'хеш пароля',
  `role` set('guest','staff','administrator') NOT NULL DEFAULT 'guest',
  PRIMARY KEY (`nick`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица контроля доступа';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl`
--

LOCK TABLES `acl` WRITE;
/*!40000 ALTER TABLE `acl` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `folder` tinyint(1) NOT NULL DEFAULT '0',
  `parent` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `folder` (`folder`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,1,'','TDD'),(2,1,'TDD','PHPUnit'),(3,1,'','о сайте'),(5,0,'о сайте','Движок'),(9,0,'о сайте','обо мне'),(20,1,'','Криптография'),(21,1,'Криптография','openssl'),(22,1,'Криптография','GnuPG'),(23,0,'openssl','Авторизация сертификатом');
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
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL DEFAULT '0',
  `headline` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (5,'описание движка сайта и хостинга','&nbsp;&nbsp;&nbsp; Начну с того, что у меня очень слабое железо: сайт хостится на роутере <a href=\"http://www.dlink.ru/ru/products/2/786.html\">dlink dir-320</a> вместе с торентокачалкой rtorrent, samba, ftp...<br>&nbsp;&nbsp;&nbsp; Все это \"добро\" жутко тормозит, а мне захотелось бложек на кириллическом домене РФ и я решил создать сайтец на самописном движке ориентированном на слабое железо.<br>&nbsp;&nbsp;&nbsp; Сначала я пытался поставить wordpress - боже, какой ужас я испытал! 2-3 минуты грузятся страницы!! =( Потом я попробовал joomla, несколько лучше, но не то. И после этого, имея некоторый навык работы с ExtJS я решил что сделаю что-то свое. И так мы имеем: <br>&nbsp;- Процессор <a href=\"http://www.broadcom.com/products/Wireless-LAN/802.11-Wireless-LAN-Solutions/BCM5354\">BCM5354</a> с частотой 240 Мгц, 16 Кб кеша данных и столько же кеша команд;<br>&nbsp;- Памяти 32 Мб ОЗУ и 4 Мб флеш. Очень скромно;<br>&nbsp;- Винчестер Western Digital WDBAAU0010HBK - это 1 Тб на USB 2.0. Собственно жесткий диск характеристика не значительная в данном случае;<br>И из софта у нас есть:<br>&nbsp;- Прошивка \"от Олега\" для роутера;<br>&nbsp;- СУБД MySQL 4.1.22;<br>&nbsp;- PHP 5.2.12;<br>&nbsp;- Lighttpd 1.4.26;<br>&nbsp;- ExtJS 3.3.0<br><br>В данный момент я пишу этот сайтец и свежую версию его можно взять <a href=\"https://subversion.assembla.com/svn/shlomin/\">здесь</a>.<br>','2010-11-23 20:15:17'),(9,'Обо мне','<a href=\"http://twitter.com/#%21/mshlomin\"><img src=\"http://blogodom.ru/wp-content/uploads/2009/02/twit11.gif\" title=\"””\" height=\"”38?\" width=\"”160?\"></a>','2010-11-24 11:02:24'),(23,'Создание сертификатов и настройка сервера для клиентской авторизации','&nbsp;&nbsp;&nbsp; Мне понадобилось сделать авторизацию на сайте с помощью клиентского сертификата полученного с помощью пакета openssl. Ниже я описываю последовательность действий, в первую очередь для себя, чтобы не забыть.<br>&nbsp;&nbsp;&nbsp; Первое что нужно в этом деле - корневой сертификат. Этот сертификат вообще говоря должен уже существовать где-нибудь и мы должны доверять по-умолчанию источнику этого сертификата. Дальше нужно создать запрос на сертификат и отправить источнику, он нам его подпишет и мы сможет его использовать в полной мере. Но это все за деньги =) Мы можем создать корневой сертификат самостоятельно.<br><div style=\"text-align: center;\"><span style=\"font-weight: bold;\">Создание корневого самоподписанного сертификата</span><br></div><br>Вот команда:<code class=\"prettyprint lang-sh\">openssl req -new -newkey rsa:1024 -nodes -keyout ca.key -x509 -days 500 \\<br>-subj /C=RU/ST=NW/L=Spb/O=caorg/OU=ca/CN=cacompany/emailAddress=почта@хост.ru -out ca.crt</code><br>описание параметров:<br><br><ul><li>&nbsp;&nbsp;&nbsp; req - Запрос на создание нового сертификата;</li><li>&nbsp;&nbsp;&nbsp; new - Создание запроса на сертификат (Certificate Signing Request=CSR);</li><li>&nbsp;&nbsp;&nbsp; newkey rsa:1023 Автоматически будет создан новый закрытый RSA ключ длиной 1024 бита;</li><li>&nbsp;&nbsp;&nbsp; nodes - Не шифровать закрытый ключ;</li><li>&nbsp;&nbsp;&nbsp; keyout - Закрытый ключ сохранить в файл ca.key;</li><li>&nbsp;&nbsp;&nbsp; x509 - Вместо создания CSR (см. опцию -new) создать самоподписанный сертификат;</li><li>&nbsp;&nbsp;&nbsp; days - Срок действия сертификата 500 дней;</li><li>&nbsp;&nbsp;&nbsp; subj /C=RU/ST=NW/L=Spb/O=caorg/OU=ca/CN=cacompany/emailAddress=почта@хост.ru</li></ul>Данные сертификата, пары параметр=значение, перечисляются через \'/\'.<br><ul><li>&nbsp;&nbsp;&nbsp; С - Двухсимвольный код страны (Country);</li><li>&nbsp;&nbsp;&nbsp; ST - Название региона (State Name);</li><li>&nbsp;&nbsp;&nbsp; L - Название города (Locality Name);</li><li>&nbsp;&nbsp;&nbsp; O - Название организации (Organization Name);</li><li>&nbsp;&nbsp;&nbsp; OU - Название отдела (Organization Unit);</li><li>&nbsp;&nbsp;&nbsp; CN - Имя сертификата, при создании серверных сертификатов используется доменное имя сайта, для клиентских сертификатов может быть использовано что угодно (Common Name);</li><li>&nbsp;&nbsp;&nbsp; emailAddress - почтовый адрес (E-mail address)</li></ul>Важно! Параметр CN обязательно должен быть равен имени сайта, например mysite.ru, если вы делаете корневой сертификат для сервера. Если же сертификат будет использоваться для других целей, пишете все что хотите. В данном случае мы можем туда написать имя несуществующей компании-криптопровайдера.<br>В данном примере я привел простой способ, есть немного более сложный: указывать параметры сертификата не в команде в конфигурационном файле.<br><br><div style=\"text-align: center;\"><span style=\"font-weight: bold;\">создание сертификата сервера</span><br></div><br><div style=\"text-align: center;\">генерируем секретный ключ<br></div><code class=\"prettyprint lang-sh\">openssl genrsa -des3 -out server.key 1024</code><br><br><div style=\"text-align: center;\">запрос на сертификат<br></div><code class=\"prettyprint lang-sh\">openssl req -new -key server.key -out server.csr -subj /C=RU/ST=NW/L=Spb/O=serverorg/OU=server/CN=supersecure/emailAddress=почта@хост.ru</code><br><br><div style=\"text-align: center;\">подписывание сертификата корневым<br></div><code class=\"prettyprint lang-sh\">openssl x509 -req -in server.csr -out server.crt -sha1 -CA ca.crt -CAkey ca.key -CAcreateserial -days 3650</code><br><br><div style=\"text-align: center;\"><span style=\"font-weight: bold;\">создание сертификата клиента</span><br></div><br><code class=\"prettyprint lang-sh\">openssl req -new -newkey rsa:1024 -nodes -keyout client.key -subj/C=RU/ST=NW/L=Spb/O=client/OU=client/CN=supersecure/emailAddress=почта@хост.ru -out client.csr</code><br><code class=\"prettyprint lang-sh\">openssl ca -config ca.conf -in client.csr -out client.crt -batch</code><br><br><div style=\"text-align: center;\">экспорт в pkcs12 для браузеров<br></div><code class=\"prettyprint lang-sh\">openssl pkcs12 -export -in client.crt -inkey client.key -certfile ca.crt -out client.p12 -passout pass:1234</code><br><br><div style=\"text-align: center;\">проверка правильности<br></div><code class=\"prettyprint lang-sh\">openssl verify -CAfile ca.crt client.crt</code><br><code class=\"prettyprint lang-sh\">openssl verify -CAfile ca.crt server.crt</code><br><span style=\"font-size: smaller;\"><em><code class=\"prettyprint lang-sh\"></code></em></span>','2010-11-24 12:33:45');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
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
  `cost` decimal(2,1) NOT NULL,
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
INSERT INTO `realty` VALUES (1,0,0,1,3,2,1,'82/56/8','9.9','Алтуфьево','Вечнозеленый бульвар 11','Москва','очень хороший дом'),(2,1,0,0,2,3,1,'105/89/12','5.1','','Скобяное шоссе д.11','Сергиев Посад',''),(3,1,1,4,1,2,0,'15','9.9','Славянский бульвар','Улица и дом','Софрино',''),(5,0,0,0,2,2,0,'14','9.9','Аннино','дом и улица','Зеленоград',''),(6,0,1,3,3,2,0,'45/44/43','0.9','Автозаводская','Красная площадь д.2','Сергиев Посад','оооо'),(7,0,1,3,1,2,1,'45/34/12','4.4','','дом и улица','Сергиев Посад','нет заметки'),(8,0,1,1,2,3,0,'45/34/12','4.4','','дом и улица','Сергиев Посад','нет заметки');
/*!40000 ALTER TABLE `realty` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-05-30 18:31:44
