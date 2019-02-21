-- MySQL dump 10.16  Distrib 10.1.38-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: foj
-- ------------------------------------------------------
-- Server version	10.1.38-MariaDB-0ubuntu0.18.04.1

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
-- Table structure for table `Allowed_Problem`
--

DROP TABLE IF EXISTS `Allowed_Problem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Allowed_Problem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_pid` (`pid`),
  CONSTRAINT `fk_Allowed_Problem_ProblemSet` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Allowed_Problem_User` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Allowed_Problem`
--

LOCK TABLES `Allowed_Problem` WRITE;
/*!40000 ALTER TABLE `Allowed_Problem` DISABLE KEYS */;
/*!40000 ALTER TABLE `Allowed_Problem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Board`
--

DROP TABLE IF EXISTS `Board`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Board` (
  `idPost` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pid` int(11) DEFAULT '0',
  `title` varchar(64) CHARACTER SET utf8 NOT NULL,
  `content` mediumtext CHARACTER SET utf8 NOT NULL,
  `replyTo` int(11) NOT NULL DEFAULT '0',
  `postTime` datetime NOT NULL,
  PRIMARY KEY (`idPost`),
  KEY `fk_table1_ProblemSet1_idx` (`pid`),
  KEY `fk_Board_User1_idx` (`uid`),
  KEY `title_INDEX` (`title`),
  CONSTRAINT `fk_Board_ProblemSet1` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Board_User1` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Board`
--

LOCK TABLES `Board` WRITE;
/*!40000 ALTER TABLE `Board` DISABLE KEYS */;
/*!40000 ALTER TABLE `Board` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Bookmark`
--

DROP TABLE IF EXISTS `Bookmark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Bookmark` (
  `pid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `starred` tinyint(4) DEFAULT '0',
  `note` varchar(255) CHARACTER SET utf8 DEFAULT '',
  KEY `pid_uid` (`pid`,`uid`),
  KEY `uid` (`uid`),
  KEY `pid` (`pid`),
  CONSTRAINT `fk_Bookmark_ProblemSet` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Bookmark_User` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Bookmark`
--

LOCK TABLES `Bookmark` WRITE;
/*!40000 ALTER TABLE `Bookmark` DISABLE KEYS */;
/*!40000 ALTER TABLE `Bookmark` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Categorization`
--

DROP TABLE IF EXISTS `Categorization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Categorization` (
  `pid` int(11) NOT NULL,
  `idCategory` int(11) NOT NULL,
  UNIQUE KEY `pid` (`pid`,`idCategory`),
  KEY `fk_ProblemSet_has_Category_Category1_idx` (`idCategory`),
  KEY `fk_ProblemSet_has_Category_ProblemSet1_idx` (`pid`),
  CONSTRAINT `fk_ProblemSet_has_Category_Category1` FOREIGN KEY (`idCategory`) REFERENCES `Category` (`idCategory`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ProblemSet_has_Category_ProblemSet1` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Categorization`
--

LOCK TABLES `Categorization` WRITE;
/*!40000 ALTER TABLE `Categorization` DISABLE KEYS */;
/*!40000 ALTER TABLE `Categorization` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Category`
--

DROP TABLE IF EXISTS `Category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Category` (
  `idCategory` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8 NOT NULL,
  `properties` text CHARACTER SET utf8,
  `prototype` int(11) DEFAULT NULL,
  PRIMARY KEY (`idCategory`),
  KEY `name_INDEX` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Category`
--

LOCK TABLES `Category` WRITE;
/*!40000 ALTER TABLE `Category` DISABLE KEYS */;
/*!40000 ALTER TABLE `Category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Contest`
--

DROP TABLE IF EXISTS `Contest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contest` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) CHARACTER SET utf8 NOT NULL,
  `description` mediumtext CHARACTER SET utf8 NOT NULL,
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `contestMode` enum('OI','ACM','Codeforces','codejam','OI Traditional') CHARACTER SET utf8 DEFAULT NULL,
  `isShowed` tinyint(1) DEFAULT '0',
  `language` set('C','C++','C++11','Pascal','Java','Python') CHARACTER SET utf8 DEFAULT 'C,C++,Pascal',
  `private` tinyint(1) DEFAULT '0',
  `teamMode` tinyint(1) DEFAULT '0',
  `submitTime` datetime DEFAULT '2001-01-01 00:00:00',
  `isTemplate` tinyint(1) NOT NULL DEFAULT '0',
  `submitAfter` time DEFAULT NULL,
  `endAfter` time DEFAULT NULL,
  `isPinned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `endTime_idx` (`endTime`),
  KEY `idx_isPinned_cid` (`isPinned`,`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Contest`
--

LOCK TABLES `Contest` WRITE;
/*!40000 ALTER TABLE `Contest` DISABLE KEYS */;
/*!40000 ALTER TABLE `Contest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Contest_Forum`
--

DROP TABLE IF EXISTS `Contest_Forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contest_Forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `user` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `title` text CHARACTER SET utf8,
  `content` mediumtext CHARACTER SET utf8,
  `replyTo` int(11) DEFAULT NULL,
  `replyCnt` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cid` (`cid`),
  KEY `fk_Contest_Forum_User` (`uid`),
  KEY `idx_replyTo` (`replyTo`),
  CONSTRAINT `Contest_Forum_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Contest_Forum_Contest_Forum` FOREIGN KEY (`replyTo`) REFERENCES `Contest_Forum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idx_cid` FOREIGN KEY (`cid`) REFERENCES `Contest` (`cid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Contest_Forum`
--

LOCK TABLES `Contest_Forum` WRITE;
/*!40000 ALTER TABLE `Contest_Forum` DISABLE KEYS */;
/*!40000 ALTER TABLE `Contest_Forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Contest_has_ProblemSet`
--

DROP TABLE IF EXISTS `Contest_has_ProblemSet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contest_has_ProblemSet` (
  `cid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `scoreDecreaseSpeed` int(11) DEFAULT NULL,
  `title` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `id` int(11) NOT NULL,
  PRIMARY KEY (`cid`,`pid`),
  KEY `fk_Contest_has_ProblemSet_ProblemSet1_idx` (`pid`),
  KEY `fk_Contest_has_ProblemSet_Contest1_idx` (`cid`),
  CONSTRAINT `fk_Contest_has_ProblemSet_Contest1` FOREIGN KEY (`cid`) REFERENCES `Contest` (`cid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Contest_has_ProblemSet_ProblemSet1` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Contest_has_ProblemSet`
--

LOCK TABLES `Contest_has_ProblemSet` WRITE;
/*!40000 ALTER TABLE `Contest_has_ProblemSet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Contest_has_ProblemSet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Contest_has_User`
--

DROP TABLE IF EXISTS `Contest_has_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contest_has_User` (
  `cid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `startTime` datetime DEFAULT NULL,
  KEY `idx_cid` (`cid`),
  KEY `idx_uid` (`uid`),
  KEY `idx_cid_uid` (`cid`,`uid`),
  CONSTRAINT `fk_cid` FOREIGN KEY (`cid`) REFERENCES `Contest` (`cid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_uid` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Contest_has_User`
--

LOCK TABLES `Contest_has_User` WRITE;
/*!40000 ALTER TABLE `Contest_has_User` DISABLE KEYS */;
/*!40000 ALTER TABLE `Contest_has_User` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Declaration`
--

DROP TABLE IF EXISTS `Declaration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Declaration` (
  `idDeclaration` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `title` varchar(64) CHARACTER SET utf8 NOT NULL,
  `declaration` mediumtext CHARACTER SET utf8 NOT NULL,
  `postTime` datetime NOT NULL,
  PRIMARY KEY (`idDeclaration`),
  KEY `fk_Declaration_Contest_has_ProblemSet1` (`cid`,`pid`),
  CONSTRAINT `fk_Declaration_Contest_has_ProblemSet1` FOREIGN KEY (`cid`, `pid`) REFERENCES `Contest_has_ProblemSet` (`cid`, `pid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Declaration`
--

LOCK TABLES `Declaration` WRITE;
/*!40000 ALTER TABLE `Declaration` DISABLE KEYS */;
/*!40000 ALTER TABLE `Declaration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Estimate`
--

DROP TABLE IF EXISTS `Estimate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Estimate` (
  `cid` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  KEY `fk_Estimate_Contest` (`cid`),
  KEY `fk_Estimate_User` (`uid`),
  KEY `fk_Estimate_ProblemSet` (`pid`),
  CONSTRAINT `fk_Estimate_Contest` FOREIGN KEY (`cid`) REFERENCES `Contest` (`cid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Estimate_ProblemSet` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Estimate_User` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Estimate`
--

LOCK TABLES `Estimate` WRITE;
/*!40000 ALTER TABLE `Estimate` DISABLE KEYS */;
/*!40000 ALTER TABLE `Estimate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Group`
--

DROP TABLE IF EXISTS `Group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8 NOT NULL,
  `avatar` mediumtext CHARACTER SET utf8,
  `groupPicture` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8,
  `private` tinyint(1) DEFAULT '0',
  `count` int(11) DEFAULT '0',
  `invitationCode` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`gid`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Group`
--

LOCK TABLES `Group` WRITE;
/*!40000 ALTER TABLE `Group` DISABLE KEYS */;
/*!40000 ALTER TABLE `Group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Group_has_Task`
--

DROP TABLE IF EXISTS `Group_has_Task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group_has_Task` (
  `gid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `title` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`gid`,`tid`),
  KEY `fk_Group_has_Task_Task1` (`tid`),
  KEY `fk_Group_has_Task_Group1` (`gid`),
  CONSTRAINT `fk_Group_has_Task_Group1` FOREIGN KEY (`gid`) REFERENCES `Group` (`gid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Group_has_Task_Task1` FOREIGN KEY (`tid`) REFERENCES `Task` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Group_has_Task`
--

LOCK TABLES `Group_has_Task` WRITE;
/*!40000 ALTER TABLE `Group_has_Task` DISABLE KEYS */;
/*!40000 ALTER TABLE `Group_has_Task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Group_has_User`
--

DROP TABLE IF EXISTS `Group_has_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Group_has_User` (
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `isAccepted` tinyint(1) DEFAULT '0',
  `priviledge` enum('user','admin') CHARACTER SET utf8 DEFAULT 'user',
  PRIMARY KEY (`gid`,`uid`),
  KEY `fk_Group_has_User_User1` (`uid`),
  KEY `fk_Group_has_User_Group1` (`gid`),
  CONSTRAINT `fk_Group_has_User_Group1` FOREIGN KEY (`gid`) REFERENCES `Group` (`gid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Group_has_User_User1` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Group_has_User`
--

LOCK TABLES `Group_has_User` WRITE;
/*!40000 ALTER TABLE `Group_has_User` DISABLE KEYS */;
/*!40000 ALTER TABLE `Group_has_User` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Mail`
--

DROP TABLE IF EXISTS `Mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Mail` (
  `idMail` int(11) NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) NOT NULL,
  `to_uid` int(11) NOT NULL,
  `title` varchar(64) CHARACTER SET utf8 NOT NULL,
  `content` mediumtext CHARACTER SET utf8,
  `sendTime` datetime NOT NULL,
  `isRead` tinyint(1) DEFAULT '0',
  `readTime` datetime DEFAULT NULL,
  `from_user` varchar(32) CHARACTER SET utf8 NOT NULL,
  `to_user` varchar(32) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`idMail`),
  KEY `fk_Mail_User1` (`from_uid`),
  KEY `fk_Mail_User2` (`to_uid`),
  CONSTRAINT `fk_Mail_User1` FOREIGN KEY (`from_uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Mail_User2` FOREIGN KEY (`to_uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Mail`
--

LOCK TABLES `Mail` WRITE;
/*!40000 ALTER TABLE `Mail` DISABLE KEYS */;
/*!40000 ALTER TABLE `Mail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Miscellaneousness`
--

DROP TABLE IF EXISTS `Miscellaneousness`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Miscellaneousness` (
  `noticeBoard` text CHARACTER SET utf8
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Miscellaneousness`
--

LOCK TABLES `Miscellaneousness` WRITE;
/*!40000 ALTER TABLE `Miscellaneousness` DISABLE KEYS */;
/*!40000 ALTER TABLE `Miscellaneousness` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Orders`
--

DROP TABLE IF EXISTS `Orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` varchar(16) NOT NULL,
  `payid` varchar(60) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `itemDescription` varchar(256) NOT NULL,
  `expiration` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `realPrice` decimal(10,2) DEFAULT NULL,
  `method` tinyint(4) NOT NULL,
  `status` tinyint(4) DEFAULT '0',
  `createTime` datetime NOT NULL,
  `finishTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderid_UNIQUE` (`orderid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Orders`
--

LOCK TABLES `Orders` WRITE;
/*!40000 ALTER TABLE `Orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `Orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PayItem`
--

DROP TABLE IF EXISTS `PayItem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PayItem` (
  `itemid` int(11) NOT NULL AUTO_INCREMENT,
  `itemDescription` varchar(256) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `timeInt` bigint(20) NOT NULL,
  PRIMARY KEY (`itemid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PayItem`
--

LOCK TABLES `PayItem` WRITE;
/*!40000 ALTER TABLE `PayItem` DISABLE KEYS */;
/*!40000 ALTER TABLE `PayItem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ProblemSet`
--

DROP TABLE IF EXISTS `ProblemSet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ProblemSet` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) CHARACTER SET utf8 NOT NULL,
  `problemDescription` mediumtext CHARACTER SET utf8 NOT NULL,
  `inputDescription` mediumtext CHARACTER SET utf8 NOT NULL,
  `outputDescription` mediumtext CHARACTER SET utf8 NOT NULL,
  `inputSample` mediumtext CHARACTER SET utf8 NOT NULL,
  `outputSample` mediumtext CHARACTER SET utf8 NOT NULL,
  `dataConstraint` mediumtext CHARACTER SET utf8 NOT NULL,
  `dataConfiguration` mediumtext CHARACTER SET utf8 NOT NULL,
  `hint` mediumtext CHARACTER SET utf8,
  `source` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `submitCount` int(11) DEFAULT '0',
  `solvedCount` int(11) DEFAULT '0',
  `isShowed` tinyint(1) DEFAULT '0',
  `scoreSum` double DEFAULT '0',
  `uid` int(11) NOT NULL,
  `confCache` mediumtext CHARACTER SET utf8,
  `dataGroup` mediumtext CHARACTER SET utf8,
  `pushedServer` mediumtext CHARACTER SET utf8,
  `noSubmit` tinyint(1) NOT NULL DEFAULT '0',
  `reviewing` int(11) DEFAULT '0',
  PRIMARY KEY (`pid`),
  KEY `title_INDEX` (`title`),
  KEY `source_INDEX` (`source`),
  KEY `score_INDEX` (`scoreSum`),
  KEY `uid` (`uid`),
  CONSTRAINT `ProblemSet_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ProblemSet`
--

LOCK TABLES `ProblemSet` WRITE;
/*!40000 ALTER TABLE `ProblemSet` DISABLE KEYS */;
/*!40000 ALTER TABLE `ProblemSet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Solution`
--

DROP TABLE IF EXISTS `Solution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Solution` (
  `idSolution` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `filename` varchar(128) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`idSolution`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`),
  CONSTRAINT `Solution_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Solution_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Solution`
--

LOCK TABLES `Solution` WRITE;
/*!40000 ALTER TABLE `Solution` DISABLE KEYS */;
/*!40000 ALTER TABLE `Solution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Submission`
--

DROP TABLE IF EXISTS `Submission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Submission` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `tid` int(11) DEFAULT NULL,
  `codeLength` int(11) NOT NULL,
  `language` char(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT '-1',
  `judgeResult` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `time` int(11) DEFAULT NULL,
  `memory` int(11) DEFAULT NULL,
  `score` double DEFAULT '0',
  `submitTime` datetime DEFAULT NULL,
  `isShowed` tinyint(1) DEFAULT '1',
  `private` tinyint(1) DEFAULT '1',
  `gid` int(11) DEFAULT NULL,
  `sim` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ACCounted` tinyint(4) DEFAULT '0',
  `pushTime` datetime DEFAULT NULL,
  `langDetail` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`sid`),
  KEY `fk_Submission_User1_idx` (`uid`),
  KEY `fk_Submission_ProblemSet1_idx` (`pid`),
  KEY `language_INDEX` (`language`),
  KEY `status_INDEX` (`status`),
  KEY `fk_Submission_Contest1` (`cid`),
  KEY `fk_Submission_Group_has_Task1` (`tid`),
  KEY `fk_gid` (`gid`),
  KEY `status_ACCounted_cid_idx` (`status`,`ACCounted`,`cid`),
  CONSTRAINT `fk_Submission_Contest1` FOREIGN KEY (`cid`) REFERENCES `Contest` (`cid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Submission_Group_has_Task1` FOREIGN KEY (`tid`) REFERENCES `Group_has_Task` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Submission_ProblemSet1` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Submission_User1` FOREIGN KEY (`uid`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_gid` FOREIGN KEY (`gid`) REFERENCES `Group` (`gid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Submission`
--

LOCK TABLES `Submission` WRITE;
/*!40000 ALTER TABLE `Submission` DISABLE KEYS */;
/*!40000 ALTER TABLE `Submission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Task`
--

DROP TABLE IF EXISTS `Task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Task` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) CHARACTER SET utf8 NOT NULL,
  `description` mediumtext CHARACTER SET utf8,
  `language` set('C','C++','C++11','Pascal','Java','Python') CHARACTER SET utf8 DEFAULT 'C,C++,Pascal',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Task`
--

LOCK TABLES `Task` WRITE;
/*!40000 ALTER TABLE `Task` DISABLE KEYS */;
/*!40000 ALTER TABLE `Task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Task_has_ProblemSet`
--

DROP TABLE IF EXISTS `Task_has_ProblemSet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Task_has_ProblemSet` (
  `tid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `title` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`tid`,`pid`),
  KEY `fk_Task_has_ProblemSet_ProblemSet1` (`pid`),
  KEY `fk_Task_has_ProblemSet_Task1` (`tid`),
  CONSTRAINT `fk_Task_has_ProblemSet_ProblemSet1` FOREIGN KEY (`pid`) REFERENCES `ProblemSet` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Task_has_ProblemSet_Task1` FOREIGN KEY (`tid`) REFERENCES `Task` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Task_has_ProblemSet`
--

LOCK TABLES `Task_has_ProblemSet` WRITE;
/*!40000 ALTER TABLE `Task_has_ProblemSet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Task_has_ProblemSet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Team`
--

DROP TABLE IF EXISTS `Team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Team` (
  `idTeam` int(11) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `idParticipant0` int(11) NOT NULL,
  `idParticipant1` int(11) DEFAULT '0',
  `idParticipant2` int(11) DEFAULT '0',
  `cid` int(11) NOT NULL,
  `registrationTime` datetime DEFAULT NULL,
  `score` int(11) DEFAULT '0',
  `penalty` int(11) DEFAULT '0',
  `isFormal` tinyint(1) NOT NULL,
  PRIMARY KEY (`idTeam`),
  KEY `fk_Team_Contest1` (`cid`),
  KEY `fk_Team_User1` (`idParticipant0`),
  KEY `fk_Team_User2` (`idParticipant1`),
  KEY `fk_Team_User3` (`idParticipant2`),
  CONSTRAINT `fk_Team_Contest1` FOREIGN KEY (`cid`) REFERENCES `Contest` (`cid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Team_User1` FOREIGN KEY (`idParticipant0`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Team_User2` FOREIGN KEY (`idParticipant1`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_Team_User3` FOREIGN KEY (`idParticipant2`) REFERENCES `User` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Team`
--

LOCK TABLES `Team` WRITE;
/*!40000 ALTER TABLE `Team` DISABLE KEYS */;
/*!40000 ALTER TABLE `Team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Test`
--

DROP TABLE IF EXISTS `Test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Test` (
  `id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Test`
--

LOCK TABLES `Test` WRITE;
/*!40000 ALTER TABLE `Test` DISABLE KEYS */;
/*!40000 ALTER TABLE `Test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8 NOT NULL,
  `password` varchar(32) CHARACTER SET utf8 NOT NULL,
  `description` mediumtext CHARACTER SET utf8,
  `email` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `School` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `isEnabled` tinyint(1) NOT NULL DEFAULT '0',
  `submitCount` int(11) DEFAULT '0',
  `solvedCount` int(11) DEFAULT '0',
  `acCount` int(11) DEFAULT '0',
  `priviledge` enum('user','admin','restricted') CHARACTER SET utf8 NOT NULL DEFAULT 'user',
  `lastPage` int(11) NOT NULL DEFAULT '1',
  `language` enum('C','C++','C++11','Pascal','Java','Python') CHARACTER SET utf8 NOT NULL DEFAULT 'C++',
  `avatar` mediumtext CHARACTER SET utf8,
  `userPicture` varchar(128) CHARACTER SET utf8 DEFAULT '0.png',
  `showCategory` tinyint(1) DEFAULT '1',
  `LastIP` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `registrationTime` datetime DEFAULT NULL,
  `problemsPerPage` smallint(6) DEFAULT '20',
  `submissionPerPage` smallint(6) DEFAULT '20',
  `contestsPerPage` smallint(6) DEFAULT '20',
  `identifier` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `permission` varchar(256) CHARACTER SET utf8 DEFAULT '',
  `verificationKey` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `blogURL` varchar(256) CHARACTER SET utf8 NOT NULL,
  `expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  UNIQUE KEY `uid_UNIQUE` (`uid`),
  KEY `name_INDEX` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `User`
--

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-02-21 18:50:42
