-- MySQL dump 10.13  Distrib 5.5.50, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sr
-- ------------------------------------------------------
-- Server version	5.5.50-0ubuntu0.14.04.1-log

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
-- Dumping routines for database 'sr'
--
/*!50003 DROP PROCEDURE IF EXISTS `correct_problem` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `correct_problem`()
BEGIN
	DECLARE cnt INT;
	DECLARE cntall INT;
	DECLARE sum DOUBLE;
	DECLARE _pid INT;
	DECLARE done INT DEFAULT 0;
	DECLARE i CURSOR FOR SELECT pid FROM ProblemSet;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;
	OPEN i;
	try : WHILE NOT done DO
		FETCH i INTO _pid;
		IF done THEN LEAVE try; END IF;
		SELECT COUNT(*) FROM Submission WHERE pid=_pid AND ACCounted=1 INTO cntall;
		SELECT COUNT(*) FROM Submission WHERE pid=_pid AND ACCounted=1 AND status=0 INTO cnt;
		SELECT SUM(score) FROM Submission WHERE pid=_pid AND ACCounted=1 INTO sum;
		UPDATE ProblemSet SET submitCount=cntall, solvedCount=cnt, scoreSum=sum WHERE pid=_pid;
	END WHILE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `correct_user` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `correct_user`()
BEGIN
	DECLARE cntall INT;
	DECLARE cnt INT;
	DECLARE cnt2 INT;
	DECLARE _uid INT;
	DECLARE done INT DEFAULT 0;
	DECLARE i CURSOR FOR SELECT uid FROM User;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;
	OPEN i;
	try : WHILE NOT done DO
		FETCH i INTO _uid;
		IF done THEN LEAVE try; END IF;
		SELECT COUNT(*) FROM Submission WHERE uid=_uid AND ACCounted=1 INTO cntall;
		SELECT COUNT(*), COUNT(DISTINCT pid) FROM Submission WHERE uid=_uid AND ACCounted=1 AND status=0 INTO cnt, cnt2;
		UPDATE User SET submitCount=cntall, solvedCount=cnt, acCount=cnt2 WHERE uid=_uid;
	END WHILE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `upd_ac_count` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `upd_ac_count`(
	_sid INT,
	_uid INT,
	_pid INT,
	_score DOUBLE,
    _status INT
)
BEGIN
	DECLARE ever INT;
    IF _status=0 THEN
		SELECT COUNT(*) FROM Submission
			WHERE uid=_uid AND pid=_pid AND status=0 AND (ACCounted=1 OR cid IS NULL)
			INTO ever;
		IF NOT ever THEN
			UPDATE User SET acCount=acCount+1 WHERE uid=_uid;
		END IF;
		UPDATE User SET solvedCount=solvedCount+1 WHERE uid=_uid;
		UPDATE ProblemSet SET solvedCount=solvedCount+1 WHERE pid=_pid;
	END IF;
    UPDATE User SET submitCount=submitCount+1 WHERE uid=_uid;
    UPDATE ProblemSet SET submitCount=submitCount+1, scoreSum=scoreSum+_score WHERE pid=_pid;
	UPDATE Submission SET ACCounted=1 WHERE sid=_sid;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `upd_ac_count_cid` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `upd_ac_count_cid`(workspace INT)
BEGIN
	DECLARE _sid INT;
	DECLARE _uid INT;
	DECLARE _pid INT;
	DECLARE _score DOUBLE;
    DECLARE _status INT;
	DECLARE done INT DEFAULT 0;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;
	
	try : WHILE NOT done DO
		SELECT sid, uid, pid, score, status FROM Submission
			WHERE ACCounted=0 AND cid=workspace
			LIMIT 1
			INTO _sid, _uid, _pid, _score, _status;
		IF done THEN LEAVE try; END IF;
		CALL upd_ac_count(_sid, _uid, _pid, _score, _status);
	END WHILE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-02-27  0:26:31
