-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 28, 2013 at 05:45 PM
-- Server version: 5.5.27
-- PHP Version: 5.5.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `apppickeryii`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`rvadmin`@`localhost` PROCEDURE `usp_DeveloperCreate`(IN  wp_usersID   BIGINT(20),
                              IN  epf_artistID INT(11),
                              OUT returnCode   TINYINT
                              )
BEGIN
  DECLARE lookupText VARCHAR(50);

  SET returnCode = 0;

  IF (SELECT count(*)
      FROM
        ap_reader
      WHERE
        ap_reader.wp_usersID = wp_usersID
      LIMIT
        1) > 0 THEN
    SET lookupText = "ReaderExistsCantCreateDeveloper";
    CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
  END IF;

  IF returnCode = 0 THEN
    IF epf_artistID IS NOT NULL THEN 

      IF (SELECT count(*)
          FROM
            epf_artist
          WHERE
            artist_id = epf_artistID
          LIMIT
            1) > 0 THEN

        INSERT IGNORE INTO ap_developer (wp_usersID, epf_artistID) VALUES (wp_usersID, epf_artistID);
        IF ROW_COUNT() = 0 THEN
          SET lookupText = "InsertError";
          CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
        END IF;

      ELSE
        SET returnCode = -99;
      END IF;
    ELSE

      INSERT IGNORE INTO ap_developer (wp_usersID, epf_artistID) VALUES (wp_usersID, NULL);
      IF ROW_COUNT() = 0 THEN
        SET lookupText = "InsertError";
        CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
      END IF;

    END IF;
  END IF;
END$$

CREATE DEFINER=`rvadmin`@`localhost` PROCEDURE `usp_DeveloperSelect`(IN  epf_artistID INT(11),
                                     OUT developerFound     tinyint,
                                     OUT `name`       VARCHAR(200),
                                     OUT search_terms VARCHAR(2000),
                                     OUT view_url     VARCHAR(1000)
                                     )
BEGIN
  DECLARE artist_type_id_developer INT(11);

  SELECT artist_type_id
  FROM epf_artist_type
  WHERE epf_artist_type.name = "Software Artist"
  INTO artist_type_id_developer;

  SELECT epf_artist.name, epf_artist.search_terms, epf_artist.view_url
  FROM epf_artist
  WHERE
    artist_id = epf_artistID
    AND is_actual_artist = 1
    AND artist_type_id = artist_type_id_developer
  LIMIT 1
  INTO `name`, search_terms, view_url;

  SET developerFound = FOUND_ROWS();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_GiveReaderPromocode`(IN `user_id` BIGINT(20), IN `epf_application_id` INT(11), OUT `promocode` VARCHAR(20), OUT `returnCode` INT(11))
BEGIN
  DECLARE promocodeID BIGINT UNSIGNED;
  DECLARE userID BIGINT UNSIGNED;
  DECLARE dateTaking TIMESTAMP;
  DECLARE promocodeApp VARCHAR(1000);
  DECLARE lookupText VARCHAR(50);

  SET dateTaking = NOW();
  SET returnCode = 0;

  
  SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;
  START TRANSACTION;

    SELECT t2.id, t2.code, t1.display_name
      FROM ap_promocode t1
        INNER JOIN ap_promocode_code t2 ON t1.id = t2.promocode_id
      WHERE
        t1.application_id = epf_application_id
        AND t2.available = 1
        AND COALESCE(t2.expiry_at, DATE(dateTaking)) >= DATE(dateTaking)
      LIMIT 1
      INTO promocodeID, promocode, promocodeApp;

    IF NOT isnull(promocodeID) THEN 
		SELECT user_id
			FROM ap_promocode_user AS apu
			WHERE apu.user_id = user_id
			INTO userID;
		
		IF isnull(userID) THEN
			INSERT INTO ap_promocode_user
			(user_id, lastPromocode, lastPromocodeApp, lastPromocodeDate)
			VALUES (user_id, promocode, promocodeApp, dateTaking);
		ELSE
			 UPDATE ap_promocode_user AS apu
			SET
			  lastPromocode = promocode,
			  lastPromocodeApp = promocodeApp,
			  lastPromocodeDate = dateTaking
			WHERE apu.user_id = user_id;
		END IF;
	
      IF row_count() = 0 THEN
        SET lookupText = "TakePromocodeFailed";
        CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
      END IF;
    ELSE
      SET lookupText = "NoAvailablePromocode";
      CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
    END IF;

    IF returnCode = 0 THEN
      UPDATE ap_promocode_code t1 SET t1.available = 0, t1.taken_at = dateTaking WHERE t1.id = promocodeID;
      IF row_count() = 0 THEN
        ROLLBACK;
        SET lookupText = "TakePromocodeFailed";
        CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
      END IF;
    END IF;

    IF returnCode = 0 THEN
      COMMIT;
    END IF;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END$$

CREATE DEFINER=`rvadmin`@`localhost` PROCEDURE `usp_PromocodeCreate`(IN epf_application_id INT(11),
                              IN promocode VARCHAR(20),
                              IN advertMsg VARCHAR(1000),
                              IN datePromoExpires DATE,
                              IN isAvail TINYINT,
                              OUT newID BIGINT(20) UNSIGNED,
                              OUT returnCode TINYINT)
BEGIN
  DECLARE lookupText VARCHAR(50);
  DECLARE appID INT(11);
  DECLARE appName VARCHAR(1000);
  DECLARE artistID INT(11);
  DECLARE headerRecordCount TINYINT;

  SET returnCode = 0;

  SELECT t1.application_id, t1.title
    FROM epf_application t1
    WHERE t1.application_id = epf_application_id
    INTO appID, appName;

  IF isnull(appID) THEN
    SET lookupText = "EpfApplicationNotFound";
    CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
  END IF;

  
  SELECT artist_id FROM epf_artist_application WHERE application_id = epf_application_id
    INTO artistID;

  
  SELECT COUNT(t1.ID) t1 FROM ap_promocode_header t1 WHERE t1.epf_application_id = epf_application_id INTO headerRecordCount;

  IF headerRecordCount = 0 THEN
    INSERT IGNORE INTO ap_promocode_header
      (epf_application_id, appDisplayName, advertMessage, dateCreated, ap_developerID)
      VALUES
      (epf_application_id, appName, advertMsg, date(now()), artistID);

    SET headerRecordCount = row_count();
  END IF;

  IF headerRecordCount > 0 THEN
    INSERT IGNORE INTO ap_promocode_code
      (promocode, epf_application_id, dateCreated, dateExpiry, dateTaken, isAvailable)
      VALUES
      (promocode, epf_application_id, date(now()), datePromoExpires, NULL, 1);

    IF ROW_COUNT() != 0 THEN
      SELECT last_insert_id() INTO newID;
    ELSE
      SET lookupText = "InsertError";
      CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
    END IF;

  ELSE
    SET lookupText = "InsertError";
    CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
  END IF;
END$$

CREATE DEFINER=`rvadmin`@`localhost` PROCEDURE `usp_PromocodesSelAllAvailable`(OUT totalRows INT
                                    )
BEGIN

  SELECT SQL_CALC_FOUND_ROWS
      t1.epf_application_id, t1.appDisplayName as `title`, t3.view_url, t3.artwork_url_small, t1.advertMessage,
      COUNT(t1.epf_application_id) AS "Available", t3.artist_name
    FROM ap_promocode_header t1
      INNER JOIN ap_promocode_code t2 ON t1.epf_application_id = t2.epf_application_id
      LEFT JOIN epf_application t3 ON t1.epf_application_id = t3.application_id
    WHERE t2.isAvailable = 1 AND COALESCE(t2.dateExpiry, DATE(NOW())) >= DATE(NOW())
    GROUP BY t1.epf_application_id
    ORDER BY t1.dateCreated DESC
    LIMIT 18446744073709551615;
  
  SELECT FOUND_ROWS() INTO totalRows;

END$$

CREATE DEFINER=`rvadmin`@`localhost` PROCEDURE `usp_PromocodesSelAllSnatched`(IN days TINYINT UNSIGNED,
                                       OUT totalRows INT
                                      )
BEGIN

  SELECT SQL_CALC_FOUND_ROWS
      t1.epf_application_id, t1.appDisplayName as `title`, t3.view_url, t3.artwork_url_small, t1.advertMessage,
      COUNT(t1.epf_application_id) AS "Snatched", t3.artist_name
    FROM ap_promocode_header t1
      INNER JOIN ap_promocode_code t2 ON t1.epf_application_id = t2.epf_application_id
      LEFT JOIN epf_application t3 ON t1.epf_application_id = t3.application_id
    WHERE t2.dateTaken IS NULL OR t2.dateTaken >= date_sub(DATE(NOW()), INTERVAL days DAY)
    GROUP BY t1.epf_application_id
    HAVING sum(t2.isAvailable) = 0
    ORDER BY t2.dateCreated DESC
    LIMIT 18446744073709551615;
  
  SELECT FOUND_ROWS() INTO totalRows;

END$$

CREATE DEFINER=`rvadmin`@`localhost` PROCEDURE `usp_PromocodesSelPgAvailable`(IN rowLimit INT,
                                       IN rowOffset INT,
                                       OUT totalRows INT)
BEGIN
  SELECT "Procedure not yet implemented";
  SET totalRows = 0;
END$$

CREATE DEFINER=`rvadmin`@`localhost` PROCEDURE `usp_ReaderCreate`(IN  wp_usersID   BIGINT(20),
                           OUT returnCode   TINYINT
                          )
BEGIN
  DECLARE lookupText VARCHAR(50);

  SET returnCode = 0;

  IF (SELECT count(*)
      FROM ap_developer
      WHERE ap_developer.wp_usersID = wp_usersID
      LIMIT 1) > 0 THEN
    SET lookupText = "DeveloperExistsCantCreateReader";
    CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
  END IF;

  IF returnCode = 0 THEN
    INSERT IGNORE INTO ap_reader (wp_usersID) VALUES (wp_usersID);
    IF ROW_COUNT() = 0 THEN
      SET lookupText = "InsertError";
      CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
    END IF;
  END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_ReaderLastPromocodeDetails`(IN `user_id` BIGINT(20) UNSIGNED, OUT `lastPromocode` VARCHAR(20), OUT `lastPromocodeApp` VARCHAR(1000), OUT `lastPromocodeDate` TIMESTAMP, OUT `returnCode` TINYINT)
BEGIN
  DECLARE userID BIGINT;
  DECLARE lookupText VARCHAR(50);

  SET returnCode = 0;
  SELECT t1.user_id, t1.lastPromocode, t1.lastPromocodeApp, t1.lastPromocodeDate
    FROM ap_promocode_user t1
    WHERE t1.user_id = user_id
    INTO userID, lastPromocode, lastPromocodeApp, lastPromocodeDate;

  IF isnull(userID) THEN
    SET lookupText = "ReaderNotFound";
    CALL usp_ReturnCodeByLookupText(lookupText, returnCode);
  END IF;
END$$

CREATE DEFINER=`rvadmin`@`localhost` PROCEDURE `usp_reportPromocodesActivity`(IN fromDate DATE, IN toDate DATE)
BEGIN
  SELECT t5.ID AS `ID`, t5.display_name AS `User`, t5.user_registered AS 'Registered',
    t5.user_email AS `Email`, t3.title AS `App`, t2.promocode AS `Promocode`, time(t2.dateTaken) AS `Time`,
    DATE(t2.dateTaken) AS `Date`
    FROM ap_promocode_header t1
      INNER JOIN ap_promocode_code t2 ON t1.epf_application_id = t2.epf_application_id
      LEFT JOIN epf_application t3 on t1.epf_application_id = t3.application_id
      LEFT JOIN ap_reader t4 on t2.promocode = t4.lastPromocode
      LEFT JOIN wp_users t5 on t4.wp_usersID = t5.ID
    WHERE DATE(t2.dateTaken) >= fromDate
      AND DATE(t2.dateTaken) <= toDate;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_ReturnCodeByLookupText`(IN `lookupText` VARCHAR(50), OUT `returnCode` INT(11))
BEGIN

  SET returnCode = NULL; 
  SELECT ap_routines_return_code.returnCode
  FROM ap_routines_return_code
  WHERE ap_routines_return_code.lookupText = lookupText
  INTO returnCode;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_ReturnCodeMsgByReturnCode`(IN `returnCode` INT(11), OUT `lookupText` VARCHAR(50), OUT `friendlyMsg` VARCHAR(255))
BEGIN

  SELECT ap_routines_return_code.lookupText, ap_routines_return_code.friendlyMsg
  FROM ap_routines_return_code
  WHERE ap_routines_return_code.lookupText = lookupText
  INTO lookupText, friendlyMsg;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mc_mailing`
--

CREATE TABLE IF NOT EXISTS `mc_mailing` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `mc_users`
--

CREATE TABLE IF NOT EXISTS `mc_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mailing_id` int(5) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mailing_id` (`mailing_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mc_users`
--
ALTER TABLE `mc_users`
  ADD CONSTRAINT `mc_users_ibfk_1` FOREIGN KEY (`mailing_id`) REFERENCES `mc_mailing` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
