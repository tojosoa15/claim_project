-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 15 sep. 2025 à 08:28
-- Version du serveur : 9.1.0
-- Version de PHP : 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `user_claim_db`
--

DELIMITER $$
--
-- Procédures
--
DROP PROCEDURE IF EXISTS `AuthentificateUser`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `AuthentificateUser` (IN `p_email_address` VARCHAR(255), IN `p_password` VARCHAR(255))   BEGIN
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_password_match INT DEFAULT 0;

    -- Vérifier que l'email existe
    SELECT COUNT(*) INTO v_exists
    FROM user_claim_db.account_informations
    WHERE email_address = p_email_address;

    IF v_exists = 0 THEN
        -- Si l'email est introuvable
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Email introuvable.';
    ELSE
        -- Vérifier le mot de passe
        SELECT COUNT(*) INTO v_password_match
        FROM user_claim_db.account_informations
        WHERE email_address = p_email_address
          AND password = p_password;

        IF v_password_match = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Mot de passe incorrect.';
        ELSE
            -- Récupérer les infos de l'utilisateur
            SELECT 'ok reussi' as message;
        END IF;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `ChekEmailExists`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `ChekEmailExists` (IN `p_email_address` VARCHAR(255))   BEGIN
    DECLARE v_exists INT DEFAULT 0;

    -- Vérifie si l'utilisateur existe
    SELECT COUNT(*) INTO v_exists
    FROM user_claim_db.account_informations
    WHERE email_address = p_email_address;

    IF v_exists = 0 THEN
        SELECT 'Email introuvable.' AS message;
    ELSE
        -- Retourne l'utilisateur concerné
        SELECT *
        FROM user_claim_db.account_informations
        WHERE email_address = p_email_address;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `GetAllClaims`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllClaims` (IN `p_page` INT, IN `p_page_size` INT)   BEGIN
    DECLARE v_order_by VARCHAR(1000);
    DECLARE v_offset INT;
    DECLARE v_sql VARCHAR(4000);
    
    SET p_page = GREATEST(IFNULL(p_page, 1), 1); -- Garantit au moins 1
    SET p_page_size = GREATEST(IFNULL(p_page_size, 10), 10); -- Garantit au moins 10

    SET v_order_by = '';
    SET v_offset = (p_page - 1) * p_page_size;

    -- Construction de la requête
    SET v_sql = CONCAT('
        SELECT 
            CL.id AS claim_id,
            CL.received_date,
            CL.number,
            CL.name,
            CL.registration_number,
            CL.ageing,
            CL.phone,
            ST.status_name AS status_name,
            CL.affected
        FROM claims CL
        INNER JOIN status ST ON CL.status_id = ST.id
        ', v_order_by, '
        LIMIT ', v_offset, ', ', p_page_size);
    
    -- Exécution de la requête
    SET @sql = v_sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DROP PROCEDURE IF EXISTS `GetAllRoles`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllRoles` (IN `p_page` INT, IN `p_page_size` INT)   BEGIN
    DECLARE v_order_by VARCHAR(1000);
    DECLARE v_offset INT;
    DECLARE v_sql VARCHAR(4000);
    
    SET p_page = GREATEST(IFNULL(p_page, 1), 1); -- Garantit au moins 1
    SET p_page_size = GREATEST(IFNULL(p_page_size, 10), 10); -- Garantit au moins 10

    SET v_order_by = '';
    SET v_offset = (p_page - 1) * p_page_size;

    -- Construction de la requête
    SET v_sql = CONCAT('
        SELECT 
            id, 
			role_code,
			role_name,
			description
        FROM roles
        ', v_order_by, '
        LIMIT ', v_offset, ', ', p_page_size);
    
    -- Exécution de la requête
    SET @sql = v_sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DROP PROCEDURE IF EXISTS `GetAllStatus`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllStatus` ()   BEGIN
    SELECT * FROM user_claim_db.status;
END$$

DROP PROCEDURE IF EXISTS `GetAssignmentById`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAssignmentById` (IN `p_claims_number` VARCHAR(255))   BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM user_claim_db.assignment WHERE claims_number = p_claims_number
    ) THEN
        SELECT 'Numero de claim introuvable.' AS message;
    END IF;

    -- Récupérer les données
    SELECT 
        A.users_id,
        A.assignment_date,
        A.assignement_note,
        A.status_id, 
	A.claims_number
    FROM user_claim_db.assignment A
    WHERE A.claims_number = p_claims_number;
END$$

DROP PROCEDURE IF EXISTS `GetAssignmentList`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAssignmentList` (IN `p_claims_number` VARCHAR(100), IN `p_status_name` VARCHAR(45), IN `p_role_name` VARCHAR(45), IN `p_business_name` VARCHAR(150))   BEGIN
    SELECT
    	a.users_id,
        a.claims_number,
        s.status_name,
        ai.business_name,
        GROUP_CONCAT(DISTINCT r.role_name SEPARATOR ', ') AS role_names,
        a.assignment_date
    FROM assignment                 AS a
    LEFT JOIN status                AS s  ON s.id        = a.status_id
    LEFT JOIN account_informations  AS ai ON ai.users_id = a.users_id
    LEFT JOIN user_roles            AS ur ON ur.users_id = a.users_id
    LEFT JOIN roles                 AS r  ON r.id        = ur.roles_id
    WHERE  (p_claims_number  IS NULL OR p_claims_number  = '' OR a.claims_number   = p_claims_number)
       AND (p_status_name    IS NULL OR p_status_name    = '' OR s.status_name     = p_status_name)
       AND (p_role_name      IS NULL OR p_role_name      = '' OR r.role_name       = p_role_name)
       AND (p_business_name  IS NULL OR p_business_name  = '' OR ai.business_name  = p_business_name)
    GROUP BY
        a.claims_number,
        s.status_name,
        ai.business_name,
        a.assignment_date
    ORDER BY a.assignment_date DESC;
END$$

DROP PROCEDURE IF EXISTS `GetClaimPartialInfo`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetClaimPartialInfo` (IN `p_claim_number` VARCHAR(100), IN `p_email` VARCHAR(100))   BEGIN
    -- Vérifier l’email
    IF p_email IS NULL OR p_email = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'L''email est un paramètre obligatoire.';
    END IF;

    -- Vérifier si l'utilisateur existe
    IF NOT EXISTS (
        SELECT 1 FROM account_informations WHERE email_address = p_email
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aucun utilisateur trouvé avec cet email.';
    END IF;

    --  Sélection des informations principales du véhicule et claim
    SELECT
        CPI.id AS id,
        CL.name,
        CPI.claim_number,
        CPI.make,
        CPI.model,
        CPI.cc,
        CPI.fuel_type,
        CPI.transmission,
        CPI.engine_no,
        CPI.chasis_no,
        CPI.vehicle_no,
        VI.color,
        VI.odometer_reading,
        VI.is_the_vehicle_total_loss,
        VI.condition_of_vehicle,
        VI.place_of_survey,
        VI.point_of_impact,
        CPI.garage,
        CPI.garage_address,
        CPI.garage_contact_no,
        CPI.eor_value,
        SI.invoice_number,
        SI.survey_type,
        SI.date_of_survey,
        SI.time_of_survey,
        SI.pre_accident_valeur,
        SI.showroom_price,
        SI.wrech_value,
        SI.excess_applicable
    FROM claim_partial_info CPI
    INNER JOIN assignment A ON A.claims_number = CPI.claim_number
    INNER JOIN users U ON A.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    INNER JOIN claims CL ON CL.number = CPI.claim_number
    LEFT JOIN surveyor_db.survey S ON S.claim_number = CPI.claim_number
    LEFT JOIN surveyor_db.vehicle_information VI ON VI.verification_id = S.id
    LEFT JOIN surveyor_db.survey_information SI ON SI.verification_id = S.id
    WHERE AI.email_address = p_email
      AND CPI.claim_number = p_claim_number;

    -- Sélection des part_details associés
    SELECT
        PD.id AS part_detail_id,
        PD.part_name,
        PD.supplier,
        PD.quantity,
        PD.quality,
        PD.cost_part,
        PD.discount_part,
        PD.vat_part,
        PD.part_total
    FROM garage_db.part_details PD
    WHERE PD.estimate_of_repair_id IN (
        SELECT id FROM garage_db.estimate_of_repair WHERE claim_number = p_claim_number
    );

    -- Sélection des labour_details associés
    SELECT
        LD.part_detail_id,
        LD.eor_or_surveyor,
        LD.activity,
        LD.number_of_hours,
        LD.hourly_cost_labour,
        LD.discount_labour,
        LD.vat_labour,
        LD.labour_total
    FROM garage_db.labour_details LD
    WHERE LD.part_detail_id IN (
        SELECT id FROM garage_db.part_details
        WHERE estimate_of_repair_id IN (
            SELECT id FROM garage_db.estimate_of_repair WHERE claim_number = p_claim_number
        )
    );

    -- Sélection des additional_labour_details associés
    SELECT
        AL.eor_or_surveyor,
        AL.painting_cost,
        AL.painting_materiels,
        AL.sundries,
        AL.num_of_repaire_days,
        AL.discount_add_labour,
        AL.vat,
        AL.add_labour_total
    FROM garage_db.additional_labour_details AL
    WHERE AL.estimate_of_repairs_id IN (
        SELECT id FROM garage_db.estimate_of_repair WHERE claim_number = p_claim_number
    );

     SELECT
            totals.cost_part,
            totals.discount_part,
            totals.vat_part,
            totals.part_total,
            totals.cost_labour,
            totals.discount_labour,
            totals.vat_labour,
            totals.labour_total,
            totals.cost_total,
            totals.discount_total,
            totals.vat_total,
            totals.total
        FROM user_claim_db.claims CL
        INNER JOIN user_claim_db.assignment SA ON CL.number = SA.claims_number
        INNER JOIN user_claim_db.status ST ON SA.status_id = ST.id
        INNER JOIN surveyor_db.survey S ON S.claim_number = CL.number
        LEFT JOIN (
            SELECT
                EOR.verification_id,
                ROUND(COALESCE(SUM(PD.cost_part), 0),2) AS cost_part,
                ROUND(COALESCE(SUM(PD.discount_part),0),2) AS discount_part,
                COALESCE(SUM(CAST(COALESCE(PD.vat_part,'0') AS DECIMAL)),0) AS vat_part,
                ROUND(COALESCE(SUM((PD.cost_part-COALESCE(PD.discount_part,0))*(1+CAST(COALESCE(PD.vat_part,'0') AS DECIMAL)/100)),0),2) AS part_total,
                ROUND(COALESCE(SUM(LD.number_of_hours*LD.hourly_const_labour),0),2) AS cost_labour,
                ROUND(COALESCE(SUM(LD.discount_labour),0),2) AS discount_labour,
                COALESCE(SUM(CAST(COALESCE(LD.vat_labour,'0') AS DECIMAL)),0) AS vat_labour,
                ROUND(COALESCE(SUM((LD.number_of_hours*LD.hourly_const_labour-COALESCE(LD.discount_labour,0))*(1+CAST(COALESCE(LD.vat_labour,'0') AS DECIMAL)/100)),0),2) AS labour_total,
                ROUND(COALESCE(SUM(PD.cost_part)+SUM(LD.number_of_hours*LD.hourly_const_labour),0),2) AS cost_total,
                ROUND(COALESCE(SUM(PD.discount_part)+SUM(LD.discount_labour),0),2) AS discount_total,
                ROUND(COALESCE(
                    SUM((PD.cost_part-COALESCE(PD.discount_part,0))*(CAST(COALESCE(PD.vat_part,'0') AS DECIMAL)/100)) +
                    SUM((LD.number_of_hours*LD.hourly_const_labour-COALESCE(LD.discount_labour,0))*(CAST(COALESCE(LD.vat_labour,'0') AS DECIMAL)/100)),0),2
                ) AS vat_total,
                ROUND(COALESCE(
                    SUM((PD.cost_part-COALESCE(PD.discount_part,0))*(1+CAST(COALESCE(PD.vat_part,'0') AS DECIMAL)/100)) +
                    SUM((LD.number_of_hours*LD.hourly_const_labour-COALESCE(LD.discount_labour,0))*(1+CAST(COALESCE(LD.vat_labour,'0') AS DECIMAL)/100)),0),2
                ) AS total
            FROM surveyor_db.estimate_of_repair EOR
            LEFT JOIN surveyor_db.part_detail PD ON PD.estimate_of_repair_id = EOR.id
            LEFT JOIN surveyor_db.labour_detail LD ON LD.part_detail_id = PD.id
            GROUP BY EOR.verification_id
        ) AS totals ON totals.verification_id = S.id
        WHERE CL.number = p_claim_number;
END$$

DROP PROCEDURE IF EXISTS `GetListByUser`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetListByUser` (IN `p_email` VARCHAR(255), IN `p_status` VARCHAR(255), IN `p_search_name` VARCHAR(255), IN `p_sort_by` VARCHAR(50), IN `p_page` INT, IN `p_page_size` INT, IN `p_search_num` VARCHAR(255), IN `p_search_reg_num` VARCHAR(255), IN `p_search_phone` VARCHAR(255))   BEGIN
    DECLARE v_where TEXT;
    DECLARE v_order_by TEXT;
    DECLARE v_offset INT;
    DECLARE v_sql TEXT;

    -- Set default values with validation
    SET p_email = IFNULL(p_email, '');
    SET p_status = IFNULL(p_status, '');
    SET p_search_name = IFNULL(p_search_name, '');
    SET p_sort_by = IFNULL(p_sort_by, 'date');
    SET p_page = GREATEST(IFNULL(p_page, 1), 1);
    SET p_page_size = IFNULL(p_page_size, 10);
    SET p_search_num = IFNULL(p_search_num, '');
    SET p_search_reg_num = IFNULL(p_search_reg_num, '');
    SET p_search_phone = IFNULL(p_search_phone, '');

    -- Validate email
    IF p_email = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'L''email est un paramètre obligatoire et ne peut pas être vide.';
    END IF;

    -- Check if user exists
    IF NOT EXISTS (SELECT 1 FROM account_informations WHERE email_address = p_email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aucun utilisateur trouvé avec cet email.';
    END IF;

    -- Initialize WHERE clause
    SET v_where = ' WHERE 1=1 ';

    -- Apply dynamic filters
    IF p_status <> '' THEN
        SET v_where = CONCAT(v_where, ' AND ST.status_name = ', QUOTE(p_status));
    END IF;
    IF p_search_name <> '' THEN
        SET v_where = CONCAT(v_where, ' AND CL.name LIKE ''%', p_search_name, '%''');
    END IF;
    IF p_search_num <> '' THEN
        SET v_where = CONCAT(v_where, ' AND CL.number LIKE ''%', p_search_num, '%''');
    END IF;
    IF p_search_reg_num <> '' THEN
        SET v_where = CONCAT(v_where, ' AND CL.registration_number LIKE ''%', p_search_reg_num, '%''');
    END IF;
    IF p_search_phone <> '' THEN
        SET v_where = CONCAT(v_where, ' AND CL.phone LIKE ''%', p_search_phone, '%''');
    END IF;

    -- Filter by user (email)
    SET v_where = CONCAT(v_where, ' AND ACI.email_address = ', QUOTE(p_email));

    -- Sorting logic
    IF p_sort_by = 'status' THEN
        SET v_order_by = ' ORDER BY ST.status_name ASC';
    ELSEIF p_sort_by = 'received_date' THEN
        SET v_order_by = ' ORDER BY CL.received_date DESC';
    ELSE
        SET v_order_by = ' ORDER BY CL.ageing DESC';
    END IF;

    -- Calculate offset
    SET v_offset = (p_page - 1) * p_page_size;

    -- Construct the query
    SET v_sql = CONCAT('
        SELECT
            CL.received_date,
            CL.number,
            CL.name,
            CL.registration_number,
            CL.ageing,
            CL.phone,
            ST.status_name
        FROM claims CL
        INNER JOIN assignment A ON CL.number = A.claims_number
        INNER JOIN users US ON US.id = A.users_id
        INNER JOIN account_informations ACI ON ACI.users_id = US.id
        INNER JOIN status ST ON A.status_id = ST.id
        ', v_where, v_order_by, '
        LIMIT ', v_offset, ', ', p_page_size);

    -- Execute the query
    SET @sql = v_sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DROP PROCEDURE IF EXISTS `GetListByUserPag`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetListByUserPag` (IN `p_email` VARCHAR(255), IN `p_status` VARCHAR(255), IN `p_search_name` VARCHAR(255), IN `p_sort_by` VARCHAR(50), IN `p_page` INT, IN `p_page_size` INT, IN `p_search_num` VARCHAR(255), IN `p_search_reg_num` VARCHAR(255), IN `p_search_phone` VARCHAR(255))   BEGIN
    DECLARE v_where TEXT;
    DECLARE v_order_by TEXT;
    DECLARE v_offset INT;
    DECLARE v_sql TEXT;
    DECLARE v_sql_count TEXT;
    DECLARE v_total INT DEFAULT 0;
    DECLARE v_status_filter TEXT;

    -- Set default values
    SET p_email = IFNULL(p_email, '');
    SET p_status = IFNULL(p_status, '');
    SET p_search_name = IFNULL(p_search_name, '');
    SET p_sort_by = IFNULL(p_sort_by, 'date_DESC'); -- Valeur par défaut
    SET p_page = GREATEST(IFNULL(p_page, 1), 1);
    SET p_page_size = IFNULL(p_page_size, 10);
    SET p_search_num = IFNULL(p_search_num, '');
    SET p_search_reg_num = IFNULL(p_search_reg_num, '');
    SET p_search_phone = IFNULL(p_search_phone, '');

    -- Validation de l'email
    IF p_email = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'L''email est obligatoire.';
    END IF;

    -- Vérifier si l'utilisateur existe
    IF NOT EXISTS (
        SELECT 1 FROM account_informations WHERE email_address = p_email
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Utilisateur introuvable.';
    END IF;

    -- Construction du WHERE dynamique
    SET v_where = ' WHERE 1=1';

    IF p_status = 'Breach' THEN
    SET v_where = CONCAT(v_where, ' AND CL.ageing >= 48 AND ST.status_name = ''new''');
    ELSEIF p_status <> '' THEN
        SET v_status_filter = CONCAT('''', REPLACE(p_status, ',', ''','''), '''');
        SET v_where = CONCAT(v_where, ' AND ST.status_name IN (', v_status_filter, ')');
    END IF;
    IF p_search_name <> '' THEN
        SET v_where = CONCAT(v_where, ' AND CL.name LIKE ''%', p_search_name, '%''');
    END IF;
    IF p_search_num <> '' THEN
        SET v_where = CONCAT(v_where, ' AND CL.number LIKE ''%', p_search_num, '%''');
    END IF;
    IF p_search_reg_num <> '' THEN
        SET v_where = CONCAT(v_where, ' AND CL.registration_number LIKE ''%', p_search_reg_num, '%''');
    END IF;
    IF p_search_phone <> '' THEN
        SET v_where = CONCAT(v_where, ' AND CL.phone LIKE ''%', p_search_phone, '%''');
    END IF;

    SET v_where = CONCAT(v_where, ' AND ACI.email_address = ', QUOTE(p_email));

    -- Définir l'ordre de tri basé sur p_sort_by
    IF p_sort_by = 'received_date-asc' THEN
        SET v_order_by = ' ORDER BY CL.received_date ASC';
    ELSEIF p_sort_by = 'received_date-desc' THEN
        SET v_order_by = ' ORDER BY CL.received_date DESC';
    ELSEIF p_sort_by = 'ageing-asc' THEN
        SET v_order_by = ' ORDER BY CL.ageing ASC';
    ELSEIF p_sort_by = 'ageing-desc' THEN
        SET v_order_by = ' ORDER BY CL.ageing DESC';
    ELSE
        SET v_order_by = ' ORDER BY CL.received_date DESC'; -- tri par défaut
    END IF;

    -- Calcul du décalage
    SET v_offset = (p_page - 1) * p_page_size;

    -- Construction et exécution du SQL de comptage
    SET v_sql_count = CONCAT('
        SELECT COUNT(*) INTO @v_total
        FROM claims CL
        INNER JOIN assignment A ON CL.number = A.claims_number
        INNER JOIN users US ON US.id = A.users_id
        INNER JOIN account_informations ACI ON ACI.users_id = US.id
        INNER JOIN status ST ON A.status_id = ST.id
        ', v_where);

    SET @v_total = 0;
    SET @stmt = v_sql_count;
    PREPARE stmt_count FROM @stmt;
    EXECUTE stmt_count;
    DEALLOCATE PREPARE stmt_count;

    SELECT @v_total INTO v_total;

    -- Construction de la requête principale avec pagination
    SET v_sql = CONCAT('
        SELECT
            CL.received_date,
            CL.number,
            CL.name,
            CL.registration_number,
            CL.ageing,
            CL.phone,
            ST.status_name
        FROM claims CL
        INNER JOIN assignment A ON CL.number = A.claims_number
        INNER JOIN users US ON US.id = A.users_id
        INNER JOIN account_informations ACI ON ACI.users_id = US.id
        INNER JOIN status ST ON A.status_id = ST.id
        ', v_where, v_order_by, '
        LIMIT ', v_offset, ', ', p_page_size);

    -- Exécution de la requête principale
    SET @sql = v_sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    -- Résultat de pagination
    SELECT 
        v_total AS total_claims,
        CEIL(v_total / p_page_size) AS total_pages,
        p_page AS current_page,
        GREATEST(p_page - 1, 1) AS previous_page,
        LEAST(p_page + 1, CEIL(v_total / p_page_size)) AS next_page,
        p_page_size AS page_size;
END$$

DROP PROCEDURE IF EXISTS `GetMethodCommunication`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMethodCommunication` ()   BEGIN
    SELECT
       CM.*
    FROM user_claim_db.communication_methods CM;
END$$

DROP PROCEDURE IF EXISTS `GetPaymentDetailsByInvoice`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPaymentDetailsByInvoice` (IN `p_invoice_no` VARCHAR(100), IN `p_email` VARCHAR(100))   BEGIN
    DECLARE v_exists INT DEFAULT 0;

    -- Vérifier que le paiement existe ET que l'email correspond
    SELECT COUNT(*) INTO v_exists
    FROM payment p
    INNER JOIN claims c ON c.number = p.claim_number
    INNER JOIN users u ON u.id = p.users_id
    INNER JOIN account_informations ai ON ai.users_id = u.id
    WHERE p.invoice_no = p_invoice_no
      AND ai.email_address = p_email;

    IF v_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Paiement introuvable ou email incorrect.';
    ELSE
        SELECT 
            p.invoice_no,
            st.status_name,
            p.invoice_date,
            p.claim_number,
            c.name AS client,
            ai.business_name AS attention,
            c.registration_number,
            p.claim_amount,
            p.vat,
            ROUND(p.claim_amount + (p.claim_amount * p.vat / 100), 2) AS total_amount
        FROM payment p
        INNER JOIN user_claim_db.status ST ON p.status_id = ST.id
        INNER JOIN claims c ON c.number = p.claim_number
        INNER JOIN users u ON u.id = p.users_id
        INNER JOIN account_informations ai ON ai.users_id = u.id
        WHERE p.invoice_no = p_invoice_no
          AND ai.email_address = p_email;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `GetPaymentListByUser`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPaymentListByUser` (IN `p_email` VARCHAR(255), IN `p_status` VARCHAR(255), IN `p_invoice_no` VARCHAR(100), IN `p_claim_number` VARCHAR(100), IN `p_sort_by` VARCHAR(50), IN `p_page` INT, IN `p_page_size` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME)   BEGIN
    DECLARE v_where TEXT;
    DECLARE v_order_by TEXT;
    DECLARE v_offset INT;
    DECLARE v_sql TEXT;
    DECLARE v_sql_count TEXT;
    DECLARE v_limit_clause TEXT;
    DECLARE v_total INT DEFAULT 0;

    -- Valeurs par défaut
    SET p_email = IFNULL(p_email, '');
    SET p_status = IFNULL(p_status, '');
    SET p_invoice_no = IFNULL(p_invoice_no, '');
    SET p_claim_number = IFNULL(p_claim_number, '');
    SET p_sort_by = IFNULL(p_sort_by, 'date_submitted-desc');
    SET p_page = GREATEST(IFNULL(p_page, 1), 1);
    SET p_page_size = IFNULL(p_page_size, 10);

    -- Vérification de l'e-mail
    IF p_email = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'L''email est obligatoire.';
    END IF;

    -- Vérifier si l'utilisateur existe
    IF NOT EXISTS (
        SELECT 1 FROM account_informations WHERE email_address = p_email
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Utilisateur introuvable.';
    END IF;

    -- Validation : Si une date est fournie, l'autre doit l'être aussi
    IF (p_start_date IS NOT NULL AND p_end_date IS NULL)
        OR (p_end_date IS NOT NULL AND p_start_date IS NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Les deux dates (date_debut et date_fin) doivent être renseignées ensemble.';
    END IF;

    -- WHERE dynamique
    SET v_where = ' WHERE 1=1';

    IF p_status <> '' THEN
        SET v_where = CONCAT(v_where, ' AND ST.status_name = ', QUOTE(p_status));
    END IF;

    IF p_invoice_no <> '' THEN
        SET v_where = CONCAT(v_where, ' AND P.invoice_no LIKE ''%', p_invoice_no, '%''');
    END IF;

    IF p_claim_number <> '' THEN
        SET v_where = CONCAT(v_where, ' AND P.claim_number LIKE ''%', p_claim_number, '%''');
    END IF;

    -- Filtre sur les dates si les deux sont renseignées
    IF p_start_date IS NOT NULL AND p_end_date IS NOT NULL THEN
        SET v_where = CONCAT(v_where, ' AND P.date_submitted BETWEEN ''', p_start_date, ''' AND ''', p_end_date, '''');
        SET v_limit_clause = ''; -- désactiver pagination
    ELSE
        SET v_offset = (p_page - 1) * p_page_size;
        SET v_limit_clause = CONCAT(' LIMIT ', v_offset, ', ', p_page_size);
    END IF;

    SET v_where = CONCAT(v_where, ' AND ACI.email_address = ', QUOTE(p_email));

    -- Tri
    IF p_sort_by = 'date_submitted-asc' THEN
        SET v_order_by = ' ORDER BY P.date_submitted ASC';
    ELSEIF p_sort_by = 'date_submitted-desc' THEN
        SET v_order_by = ' ORDER BY P.date_submitted DESC';
    ELSEIF p_sort_by = 'date_payment-asc' THEN
        SET v_order_by = ' ORDER BY P.date_payment ASC';
    ELSEIF p_sort_by = 'date_payment-desc' THEN
        SET v_order_by = ' ORDER BY P.date_payment DESC';
    ELSE
        SET v_order_by = ' ORDER BY P.date_submitted DESC';
    END IF;

    -- SQL de comptage
    SET v_sql_count = CONCAT('
        SELECT COUNT(*) INTO @v_total
        FROM payment P
        INNER JOIN users U ON P.users_id = U.id
        INNER JOIN account_informations ACI ON ACI.users_id = U.id
        INNER JOIN status ST ON ST.id = P.status_id
        ', v_where);

    SET @v_total = 0;
    SET @stmt = v_sql_count;
    PREPARE stmt_count FROM @stmt;
    EXECUTE stmt_count;
    DEALLOCATE PREPARE stmt_count;

    SELECT @v_total INTO v_total;

    -- Requête principale
    SET v_sql = CONCAT('
        SELECT
            P.invoice_no,
            P.date_submitted,
            P.date_payment,
            ST.status_name,
            P.claim_number,
            P.claim_amount
        FROM payment P
        INNER JOIN users U ON P.users_id = U.id
        INNER JOIN account_informations ACI ON ACI.users_id = U.id
        INNER JOIN status ST ON ST.id = P.status_id
        ', v_where, v_order_by, v_limit_clause);

    SET @sql = v_sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

END$$

DROP PROCEDURE IF EXISTS `GetUserByRole`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserByRole` (IN `p_role_id` INT)   BEGIN
    -- Vérifie que l'ID du rôle est valide
    SET p_role_id = IFNULL(p_role_id, 1); -- Valeur par défaut 1 si NULL
    
    SELECT 
        u.id AS user_id,
        ai.business_name,
        ai.email_address ,
        r.role_name
    FROM 
        `users` AS u 
	LEFT JOIN
		account_informations ai ON u.id = ai.users_id
    LEFT JOIN 
        user_roles AS ur ON u.id = ur.users_id 
    LEFT JOIN 
        roles AS r ON ur.roles_id = r.id 
    WHERE 
        r.id = p_role_id;
END$$

DROP PROCEDURE IF EXISTS `GetUserClaimStats`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserClaimStats` (IN `p_email` VARCHAR(255))   BEGIN
    DECLARE v_total_claims INT DEFAULT 0;
    DECLARE v_new_claims INT DEFAULT 0;
    DECLARE v_queries_claims INT DEFAULT 0;
    DECLARE v_ageing_claims INT DEFAULT 0;

    -- Vérifier l’email
    IF p_email IS NULL OR p_email = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'L''email est un paramètre obligatoire.';
    END IF;

    -- Vérifier si l'utilisateur existe
    IF NOT EXISTS (
        SELECT 1 FROM account_informations WHERE email_address = p_email
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aucun utilisateur trouvé avec cet email.';
    END IF;

    -- received
    SELECT COUNT(*) INTO v_total_claims
    FROM claims CL
    INNER JOIN assignment A ON A.claims_number = CL.number
    INNER JOIN users U ON A.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    WHERE AI.email_address = p_email;

    -- Claims avec status = 'New'
    SELECT COUNT(*) INTO v_new_claims
    FROM claims CL
    INNER JOIN assignment A ON A.claims_number = CL.number
    INNER JOIN users U ON A.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    INNER JOIN status ST ON ST.id = A.status_id
    WHERE AI.email_address = p_email AND ST.status_name = 'New';

    -- Claims avec status = 'Queries'
    SELECT COUNT(*) INTO v_queries_claims
    FROM claims CL
    INNER JOIN assignment A ON A.claims_number = CL.number
    INNER JOIN users U ON A.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    INNER JOIN status ST ON ST.id = A.status_id
    WHERE AI.email_address = p_email AND ST.status_name = 'Queries';

    -- About to breach
    SELECT COUNT(*) INTO v_ageing_claims
    FROM claims CL
    INNER JOIN assignment A ON A.claims_number = CL.number
    INNER JOIN users U ON A.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    INNER JOIN status ST ON ST.id = CL.status_id
    WHERE AI.email_address = p_email AND CL.ageing >= 48 AND ST.status_name = 'new';

    -- Retourner les résultats sous forme d'une seule ligne
    SELECT
        v_total_claims AS received,
        v_new_claims AS new,
        v_ageing_claims AS about_to_breach,
        v_queries_claims AS queries;
END$$

DROP PROCEDURE IF EXISTS `GetUserPaymentStats`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserPaymentStats` (IN `p_email` VARCHAR(255))   BEGIN
    DECLARE v_total_paiements INT DEFAULT 0;
    DECLARE v_under_review INT DEFAULT 0;
    DECLARE v_paid INT DEFAULT 0;
    DECLARE v_approved INT DEFAULT 0;

    -- Vérification de l’email
    IF p_email IS NULL OR p_email = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'L''email est un paramètre obligatoire.';
    END IF;

    -- Vérifier si l'utilisateur existe
    IF NOT EXISTS (
        SELECT 1 FROM account_informations WHERE email_address = p_email
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aucun utilisateur trouvé avec cet email.';
    END IF;

    -- Total des paiements
    SELECT COUNT(*) INTO v_total_paiements
    FROM payment P
    INNER JOIN users U ON P.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    WHERE AI.email_address = p_email;

    -- Paiements avec status = 'Under review'
    SELECT COUNT(*) INTO v_under_review
    FROM payment P
    INNER JOIN users U ON P.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    INNER JOIN status ST ON ST.id = P.status_id
    WHERE AI.email_address = p_email AND ST.status_name = 'Under review';

    -- Paiements avec status = 'Approved'
    SELECT COUNT(*) INTO v_approved
    FROM payment P
    INNER JOIN users U ON P.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    INNER JOIN status ST ON ST.id = P.status_id
    WHERE AI.email_address = p_email AND ST.status_name = 'Approved';

    -- Paiements avec status = 'Paid'
    SELECT COUNT(*) INTO v_paid
    FROM payment P
    INNER JOIN users U ON P.users_id = U.id
    INNER JOIN account_informations AI ON AI.users_id = U.id
    INNER JOIN status ST ON ST.id = P.status_id
    WHERE AI.email_address = p_email AND ST.status_name = 'Paid';

    -- Retourner les résultats sous forme d'une ligne
    SELECT
        v_total_paiements AS total,
        v_under_review AS under_review,
        v_approved AS approved,
        v_paid AS paid;
END$$

DROP PROCEDURE IF EXISTS `GetUserProfile`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserProfile` (IN `p_email_address` VARCHAR(255))   BEGIN
    SELECT
        AI.business_name,
        AI.business_registration_number,
        AI.business_address,
        AI.city,
        AI.postal_code,
        AI.phone_number,
        AI.email_address,
        AI.website,
        AI.backup_email,
        AI.password,
       
        FI.vat_number,
        FI.tax_identification_number,
        FI.bank_name,
        FI.bank_account_number,
        FI.swift_code,

        ASG.primary_contact_name,
        ASG.primary_contact_post,
        ASG.notification,
        ASG.updated_at AS administrative_updated_at,

        GROUP_CONCAT(CM.method_name ORDER BY CM.method_name SEPARATOR ', ') AS communication_methods

    FROM user_claim_db.users U
    LEFT JOIN user_claim_db.account_informations AI
        ON U.id = AI.users_id
    LEFT JOIN user_claim_db.financial_informations FI
        ON U.id = FI.users_id
    LEFT JOIN user_claim_db.administrative_settings ASG
        ON U.id = ASG.users_id
    LEFT JOIN user_claim_db.admin_settings_communications ASCM
        ON ASG.id = ASCM.admin_setting_id
    LEFT JOIN user_claim_db.communication_methods CM
        ON ASCM.method_id = CM.id
    WHERE AI.email_address = p_email_address;
END$$

DROP PROCEDURE IF EXISTS `InsertAssignment`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertAssignment` (IN `p_users_id` INT, IN `p_assignment_date` DATETIME, IN `p_assignement_note` TEXT, IN `p_status_id` INT, IN `p_claims_number` VARCHAR(100))   BEGIN
    IF NOT EXISTS (SELECT 1
                   FROM   user_claim_db.claims
                   WHERE  number = p_claims_number) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Réclamation introuvable (claims_id).';
    END IF;

    IF NOT EXISTS (SELECT 1
                   FROM   user_claim_db.users
                   WHERE  id = p_users_id) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Utilisateur introuvable (users_id).';
    END IF;
    INSERT INTO user_claim_db.assignment (
        users_id,
        assignment_date,
        assignement_note,
        status_id,
        claims_number
    ) VALUES (
        p_users_id,
        p_assignment_date,
        p_assignement_note,
        p_status_id,
        p_claims_number
    );

    UPDATE user_claim_db.claims
    SET    affected = 1
    WHERE  number   = p_claims_number;   -- ou id = p_claims_id selon votre clé
END$$

DROP PROCEDURE IF EXISTS `InsertFullUserFromJSON`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertFullUserFromJSON` (IN `p_json_data` JSON)   BEGIN
    DECLARE v_users_id INT;

    -- 1. Insert into users
    INSERT INTO users(created_at, updated_at)
    VALUES (NOW(), NOW());

    SET v_users_id = LAST_INSERT_ID();

    -- 2. Insert into account_information
    INSERT INTO account_informations (
        users_id, business_name, business_registration_number,
        business_address, city, postal_code, phone_number,
        email_address, password, website, backup_email
    ) VALUES (
        v_users_id,
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.businessName')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.businessRegistrationNumber')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.businessAddress')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.city')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.postalCode')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.phoneNumber')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.emailAddress')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.password')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.website')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.accountInformation.backupEmail'))
    );

     -- 3. Insert into financial_informations
    INSERT INTO financial_informations (
        users_id, vat_number, tax_identification_number,
        bank_name, bank_account_number, swift_code
    ) VALUES (
        v_users_id,
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.financialInformation.vatNumber')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.financialInformation.taxIdentificationNumber')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.financialInformation.bankName')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.financialInformation.bankAccountNumber')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.financialInformation.swiftCode'))
    );


    -- 4. Insert into administrative_settings
    INSERT INTO administrative_settings (
        users_id, primary_contact_name, primary_contact_post, notification
    ) VALUES (
        v_users_id,
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.administrativeSettings.primaryContactName')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.administrativeSettings.primaryContactPost')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.administrativeSettings.notification'))
    );

    -- 5. Insert into user_roles
    INSERT INTO user_roles (
        users_id, roles_id, assigned_at, is_active
    ) VALUES (
        v_users_id,
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.rolesId')),
        NOW(),
        1
    );
   
    SELECT v_users_id AS user_id;
END$$

DROP PROCEDURE IF EXISTS `InsertUser`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertUser` (IN `p_json_data` JSON)   BEGIN
    DECLARE v_users_id INT;

    -- 1. Insert into users
    INSERT INTO users(created_at, updated_at)
    VALUES (NOW(), NOW());

    SET v_users_id = LAST_INSERT_ID();

    -- 2. Insert into account_information
    INSERT INTO account_information (
        verification_id, business_name, business_registration_number,
        business_address, city, postal_code, phone_number,
        email_address, password, website, backup_email
    ) VALUES (
        v_users_id,
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.business_name')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.business_registration_number')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.business_address')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.city')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.postal_code')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.phone_number')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.email_address')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.password')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.website')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.backup_email'))
    );

    -- 3. Insert into financial_informations
    INSERT INTO financial_informations (
        users_id, vat_number, tax_identification_number,
        bank_name, city, swift_code
    ) VALUES (
        v_users_id,
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.vat_number')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.tax_identification_number')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.bank_name')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.bank_account_number')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.swift_code'))
    );

    -- 4. Insert into administrative_settings
    INSERT INTO administrative_settings (
        users_id, primary_contact_name, primary_contact_post, notification
    ) VALUES (
        v_users_id,
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.primary_contact_name')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.primary_contact_post')),
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.notification'))
    );

    -- 5. Insert into user_roles
    INSERT INTO user_roles (
        users_id, roles_id, assigned_at, is_active
    ) VALUES (
        v_users_id,
        JSON_UNQUOTE(JSON_EXTRACT(p_json_data, '$.roles_id')),
        NOW(),
        1
    );
END$$

DROP PROCEDURE IF EXISTS `UpdateAdminSettings`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateAdminSettings` (IN `p_email_address` VARCHAR(255), IN `p_primary_contact_name` VARCHAR(100), IN `p_primary_contact_post` VARCHAR(100), IN `p_notification` BOOLEAN, IN `p_method_names` TEXT)   BEGIN
    DECLARE v_users_id INT;
    DECLARE v_admin_setting_id INT;
    DECLARE v_method_name VARCHAR(255);
    DECLARE v_pos INT DEFAULT 0;
    DECLARE v_next_pos INT DEFAULT 0;
    DECLARE v_len INT;
   
    -- Récupérer le users_id
    SELECT users_id INTO v_users_id
    FROM user_claim_db.account_informations
    WHERE email_address = p_email_address
    LIMIT 1;

    IF v_users_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email introuvable.';
    END IF;

    -- Récupérer l'admin_setting_id
    SELECT id INTO v_admin_setting_id
    FROM user_claim_db.administrative_settings
    WHERE users_id = v_users_id
    LIMIT 1;

    IF v_admin_setting_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Paramètres administratifs introuvables.';
    END IF;

    -- Mise à jour administrative_settings
    UPDATE user_claim_db.administrative_settings
    SET primary_contact_name = p_primary_contact_name,
        primary_contact_post = p_primary_contact_post,
        notification = p_notification,
        updated_at = NOW()
    WHERE users_id = v_users_id;

    -- Suppression des anciennes méthodes
    DELETE FROM user_claim_db.admin_settings_communications
    WHERE admin_setting_id = v_admin_setting_id;

    -- Boucle d'insertion des nouvelles méthodes
    SET v_len = CHAR_LENGTH(p_method_names);
    WHILE v_pos < v_len DO
        SET v_next_pos = LOCATE(',', p_method_names, v_pos + 1);
        IF v_next_pos = 0 THEN
            SET v_next_pos = v_len + 1;
        END IF;

        SET v_method_name = TRIM(SUBSTRING(p_method_names, v_pos + 1, v_next_pos - v_pos - 1));

        IF v_method_name != '' THEN
            INSERT INTO user_claim_db.admin_settings_communications (admin_setting_id, method_id)
            SELECT v_admin_setting_id, id
            FROM user_claim_db.communication_methods
            WHERE method_name = v_method_name;
        END IF;

        SET v_pos = v_next_pos;
    END WHILE;

    SELECT 'Mise à jour réussie' AS message;

END$$

DROP PROCEDURE IF EXISTS `UpdateAssignment`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateAssignment` (IN `p_users_id` INT, IN `p_assignment_date` DATETIME, IN `p_assignement_note` TEXT, IN `p_status_id` INT, IN `p_claims_number` VARCHAR(100))   BEGIN
    -- Vérifier que l'enregistrement existe
    IF NOT EXISTS (
        SELECT 1 FROM user_claim_db.assignment WHERE claims_number = p_claims_number
    ) THEN 
        SELECT 'Numero de claim introuvable (claims_number).' AS message;
    END IF;


    UPDATE user_claim_db.assignment
    SET
	claims_number = p_claims_number,
        users_id = p_users_id,
        assignment_date = NOW(),
        assignement_note = p_assignement_note,
        status_id = p_status_id
    WHERE claims_number = p_claims_number 
    AND users_id = p_users_id;

END$$

DROP PROCEDURE IF EXISTS `UpdateSecuritySetting`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateSecuritySetting` (IN `p_email_address` VARCHAR(100), IN `p_new_password` VARCHAR(100), IN `p_new_backup_email` VARCHAR(100))   BEGIN
    DECLARE v_rows_updated INT DEFAULT 0;

    -- Mise à jour du mot de passe si fourni
    IF p_new_password IS NOT NULL AND p_new_password != '' THEN
        UPDATE user_claim_db.account_informations
        SET password = p_new_password
        WHERE email_address = p_email_address;
        
        SET v_rows_updated = v_rows_updated + ROW_COUNT();
    END IF;

    -- Mise à jour du backup email si fourni
    IF p_new_backup_email IS NOT NULL AND p_new_backup_email != '' THEN
        UPDATE user_claim_db.account_informations
        SET backup_email = p_new_backup_email
        WHERE email_address = p_email_address;

        SET v_rows_updated = v_rows_updated + ROW_COUNT();
    END IF;

    -- Vérification si au moins une mise à jour a eu lieu
    IF v_rows_updated = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aucune mise à jour effectuée. Paramètres vides ou email introuvable.';
    ELSE
        SELECT CONCAT('Mise à jour effectuée sur ', v_rows_updated, ' champ(s).') AS message;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `UpdateUserPassword`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserPassword` (IN `p_email_address` VARCHAR(255), IN `p_new_password` VARCHAR(250))   BEGIN
    UPDATE user_claim_db.account_informations
    SET password = p_new_password
    WHERE email_address = p_email_address;
   
   -- Vérification
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aucune mise à jour effectuée. Email introuvable ou site déjà à jour.';
    ELSE
        SELECT 'Mise à jour mot de passe réussie' AS message;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `UpdateUserWebsite`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserWebsite` (IN `p_email_address` VARCHAR(255), IN `p_new_website` VARCHAR(255))   BEGIN
    -- Mise à jour du champ website
    UPDATE user_claim_db.account_informations
    SET website = p_new_website
    WHERE email_address = p_email_address;

    -- Vérification
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aucune mise à jour effectuée. Email introuvable ou site déjà à jour.';
    ELSE
        SELECT 'Mise à jour site web réussie' AS message;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `account_informations`
--

DROP TABLE IF EXISTS `account_informations`;
CREATE TABLE IF NOT EXISTS `account_informations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `users_id` int DEFAULT NULL,
  `business_name` varchar(150) NOT NULL,
  `business_registration_number` varchar(150) NOT NULL,
  `business_address` varchar(250) NOT NULL,
  `city` varchar(45) NOT NULL,
  `postal_code` varchar(45) NOT NULL,
  `phone_number` varchar(100) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `password` varchar(250) NOT NULL,
  `website` varchar(150) DEFAULT NULL,
  `backup_email` varchar(255) NOT NULL,
  `date_of_birth` datetime NOT NULL,
  `nic` varchar(50) NOT NULL,
  `country_of_nationality` varchar(50) NOT NULL,
  `home_number` varchar(50) NOT NULL,
  `kyc` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_address_UNIQUE` (`email_address`),
  UNIQUE KEY `users_id_UNIQUE` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `account_informations`
--

INSERT INTO `account_informations` (`id`, `users_id`, `business_name`, `business_registration_number`, `business_address`, `city`, `postal_code`, `phone_number`, `email_address`, `password`, `website`, `backup_email`, `date_of_birth`, `nic`, `country_of_nationality`, `home_number`, `kyc`) VALUES
(1, 1, 'Brondon', '48 AG 23', 'Squard Orchard', 'Quatre Bornes', '7000', '56589857', 'tojo@gmail.com', '$2y$12$DQcPA1dClkAMmVYnjFesKedCBkiLuZj7mD0gqgzegunGQ5X9/Rw16', 'www.test8.com', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00'),
(2, 2, 'Christofer', '1 JN 24', 'La Louis', 'Quatre Bornes', '7120', '57896532', 'rene@gmail.com', '$2y$12$Wg3ISNFeWVw.yGV9u7EVtOpMCk7z64KZ9SpKZIgXaoeeuYZe8pbKC', 'www.rene.com', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00'),
(3, 3, 'Kierra', '94 NOV 06', 'Moka', 'Saint Pierre', '7520', '54789512', 'raharison@gmail.com', '$2y$12$nHmXmOQnSx4Nt0H7DX3Ye.OIa7BEjRz1Ez.gK3uxG8C1JwBBLmbCa', 'www.raharison.com', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00'),
(4, 4, 'Surveyor 2', 'Surveyor 2', 'addr Surveyor 2', 'Quatre bornes', '7200', '55678923', 'surveyor2@gmail.com', '$2y$12$A9/pwjw/3xpJAn2ZKt3CSOCW89.DkGB1Ez.MxQZVptmtCMdTbPjce', 'www.surveyor.com', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00'),
(5, 5, 'Santatra Miharimbola', '1 JN 2025', 'Avenue victoria', 'Quatre Bornes', '7500', '55897899', 'santatra@gmail.com', '$2y$12$Wg3ISNFeWVw.yGV9u7EVtOpMCk7z64KZ9SpKZIgXaoeeuYZe8pbKC', 'www.santat1.com', 'santatra.r@gmail.com', '1995-09-06 00:00:00', 'W01728617827821', 'Mauritius', '628468273', '2026-09-01 00:00:00'),
(6, 6, 'Garage 1', 'Garage 1', 'Addr Garage 1', 'Quatre bornes', '7200', '45677444', 'garage2@gmail.com', '$2y$12$nHmXmOQnSx4Nt0H7DX3Ye.OIa7BEjRz1Ez.gK3uxG8C1JwBBLmbCa', 'www.garage2.com', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00'),
(7, 7, 'Spare Part 2', 'Spare Part 2', 'Addr Spare Part 2', 'Quatre bornes', '7200', '34667777', 'sparepart@gmail.com', '$2y$12$nHmXmOQnSx4Nt0H7DX3Ye.OIa7BEjRz1Ez.gK3uxG8C1JwBBLmbCa', 'www.sparepart2.com', '', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00'),
(8, 10, 'Miha', '67236', 'Qutre bornes', 'Quatre borne', '101', '3U873839', 'miha@gmail.com', '123456', 'miha@website.com', 'mia@gmail.com', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00'),
(9, 11, 'Super admin', '123456789', '123 Rue Principale', 'Paris', '75001', '+33123456789', 'raharisontojo4@gmail.com', 'Tojo@1235', 'https://monentreprise.com', 'tt@gmail.com', '0000-00-00 00:00:00', '', '', '', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `administrative_settings`
--

DROP TABLE IF EXISTS `administrative_settings`;
CREATE TABLE IF NOT EXISTS `administrative_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `users_id` int DEFAULT NULL,
  `primary_contact_name` varchar(255) NOT NULL,
  `primary_contact_post` varchar(150) NOT NULL,
  `notification` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_id_UNIQUE` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `administrative_settings`
--

INSERT INTO `administrative_settings` (`id`, `users_id`, `primary_contact_name`, `primary_contact_post`, `notification`, `updated_at`) VALUES
(1, 1, 'rene', 'testpost', '0', '2025-07-23 07:43:44'),
(2, 11, '15', '222', 'Test notification', '2025-07-24 10:40:18');

-- --------------------------------------------------------

--
-- Structure de la table `admin_settings_communications`
--

DROP TABLE IF EXISTS `admin_settings_communications`;
CREATE TABLE IF NOT EXISTS `admin_settings_communications` (
  `admin_setting_id` int NOT NULL,
  `method_id` int NOT NULL,
  PRIMARY KEY (`admin_setting_id`,`method_id`),
  KEY `IDX_42D45B4519883967` (`method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `admin_settings_communications`
--

INSERT INTO `admin_settings_communications` (`admin_setting_id`, `method_id`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Structure de la table `assignment`
--

DROP TABLE IF EXISTS `assignment`;
CREATE TABLE IF NOT EXISTS `assignment` (
  `claims_number` varchar(100) NOT NULL,
  `users_id` int NOT NULL,
  `assignment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `assignement_note` text,
  `status_id` int NOT NULL,
  KEY `fk_assignment_status1_idx` (`status_id`),
  KEY `fk_assignment_users1` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `assignment`
--

INSERT INTO `assignment` (`claims_number`, `users_id`, `assignment_date`, `assignement_note`, `status_id`) VALUES
('M0119921', 1, '2025-07-04 11:03:40', NULL, 2),
('M0119923', 5, '2025-07-03 20:00:00', 'test', 4),
('M0119921', 6, '2025-07-03 20:00:00', 'Test affectation garage 1', 1),
('M0119925', 5, '2025-07-06 07:00:00', 'urgent', 3),
('M0119926', 5, '2025-07-07 06:30:00', 'à vérifier', 2),
('M0119927', 5, '2025-07-08 08:15:00', 'dommages mineurs', 2),
('M0119928', 5, '2025-07-09 05:45:00', 'prioritaire', 1),
('M0119929', 5, '2025-07-10 11:00:00', 'réclamation en attente', 2),
('M0119930', 5, '2025-07-11 10:30:00', 'à traiter rapidement', 2),
('M0119931', 5, '2025-07-12 12:10:00', 'sinistre confirmé', 1),
('M0119932', 5, '2025-07-13 07:20:00', 'visite sur site prévue', 2),
('M0119933', 5, '2025-07-14 06:00:00', 'urgence faible', 1),
('M0119934', 5, '2025-07-15 13:45:00', 'pièces manquantes', 1),
('M0119935', 5, '2025-08-26 19:42:22', NULL, 2),
('M0119936', 5, '2025-07-17 07:40:00', 'sinistre complet', 1),
('M0119937', 5, '2025-08-07 09:43:08', 'note', 1),
('M0119938', 5, '2025-08-07 09:43:29', 'note1', 2),
('M0119939', 5, '2025-08-07 09:45:20', 'note2', 1),
('M0119922', 5, '2025-08-25 21:00:00', '', 1),
('M0119924', 6, '2025-08-25 21:00:00', '', 1);

-- --------------------------------------------------------

--
-- Structure de la table `blacklisted_token`
--

DROP TABLE IF EXISTS `blacklisted_token`;
CREATE TABLE IF NOT EXISTS `blacklisted_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expires_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `blacklisted_token`
--

INSERT INTO `blacklisted_token` (`id`, `token`, `expires_at`) VALUES
(1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTMyNTAwNjgsImV4cCI6MTc1MzI1NzI2OCwicm9sZXMiOlsiZ2FyYWdlIl0sInVzZXJuYW1lIjoicmVuZUBnbWFpbC5jb20ifQ.TtllsQbeQ4uM5cYIdoheYigVg9EZLjA4IBZ4wugl_wlmdq2G_4ZJ3xQvapFlfw70hVh3D1PNcgbGfSljjYh5mE3nfoBnPcF6qaz9Tj85LaRZTPAkbOXWLmeuJH6gzP1v-ouKEIeqOsNTqDliovVjrtArj2s7ZJSdAXhE4tHuZ0QXRFWVXEKCVcZ22609uzI1IBo1FGcsymik4rfLNstdFBpwR61V2mkWrBRpcafJyyXs0NrXPIlqFxU5IZJ8u88yG3vowhnEAVpjC3PM1rvR9X5Qd3AO8ymvzRWJ4To6RpGH2Ai3rNHuveiGC6t75DoH-7t5c7d-X5ItawJWpY1kbJNgNqZ32P-7YKViwFAoTUTbxi5ML0GCs-ym8VCghMBqxID91gtuYX6S9Dgmw3fbHHK2cZeUwaWJ17uNzu3qBWm0xBmksRgxwP8CEKIArw_JXL7GdQkLkqGOK0egRWXRbEmQkU8IcP1ZT0jqoVjEHvwKewU2GhVw_5UrOBe7QHAemFYzUYdurepzDXOSHAqZmBy_g18fueUe2w1OPpEImlhJQHso4iWpkMcZO-TzzRoS74BCQ_bqDhfxpkd8uLeTojad-hm70hpP5nMsgBxOfsdQOeimNeh5PI5Uo2EHHyPq32WiKEGXJjx_IA5UFvrp374KKjhA6Ipx7ca2rwJrnYY', '2025-07-23 07:54:28'),
(2, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTM5NDUwMzQsImV4cCI6MTc1NDAzMTQzNCwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.O1D6iH0IneKhI5wzEcFSmEfMKDryJQxqh_IDtSJXzfMpOOhJM12ij39Tw1YenxN-sd2kt-FuBelu9HOJniTIKynzekn94GYjR9sWrVnlMMWnzdtpCybiUiaraJwZf-budZlm0cjgj_xJiaE5yrvAzyLrXYfcYljX1ITgzhR8mfpcA0dDsO6u8EtIaPNV55KRLrAsjwYGIxQUhh4da1sONyjTSG7MhM5mTK0BXiTsrWvaCdMBwyyYWvpMvV90htYo1RN8TAJwiwdWzbCgXH6DbuVmiO0Lb7e340tce3t-b286vC3bp9P4JHCWfMfBfP4p6rSOGeuMgyKsOG5bnnjeohzIi5dKc0fEXmdc-F4lEpr0XEgqvSEkFg8sNhQ33Pk7IUXhSuHF5JhROgIiPfEGFDfQcD1LmJfJFRw6WkW-ybsentaYRIbOdh1aSDxGwgrig6QwQmkoaHYOQ0NkutWsgjxLoHnrU1raXokGcltxhu2FaF0IrdkXffVJFo6BtlJfx31LTv2DrhDzijWBWx4klyjRMkgp7TAn305-RqhDpRMBFGqAN_q2sS2KnUeEm0ZawHxI3Fqe0adgjX30nAer6AW9-JosuyhO2oo2fUnwv4YVgH8AL4g-EDyLBmqsUiT3YlqIOWS9Z9v6Dd_m84UgozbIuiesjfDHJS7pVYT5JsY', '2025-08-01 06:57:14'),
(3, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYxOTQ4NzgsImV4cCI6MTc1NjI4MTI3OCwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.4SvywcFd4Zq-cG-uvpKGkFMhbGN96P7nO3E4bTa4Vqs_JSVXB4mm-ZRFM0sDbXUlAdpp6Fpd_RVpF4Kb8uEbr00UORvdHoP65LxZo3cVBdQWFk9UiP6ySRwvOtz58d-toURFe2IodZaZbUo4192Sh5QSMTdBkzM0xnIvuIOO_7rscfYcczcqVvapGa36zyec0mWRHNOGDu9iqSB2ybQBJyG-Wma4muRvoJQW16lfENXLsjD4xPos3qjdPix4OqQ3XqBLXKFp3m_l_JDL29-JupHHjhzmMQCG78A1Os9zGWYphvrDWHordcJKLk2-QK67sHGJxq4LGYXFgt3YXg_ZxyG00TUQGeSr8OmhMMVOC6JDvKnfiEmYZOnThhu6gNUZ5EYvAvzjRbr3Ka2J_fGp9tMlbkCmjBdKSomoygI1858YBmq9z-qALqAOhKaMmMFxm6r4twBjYj6UDZczsZqKhRTr_zSUkDHfUAgJCa2kjKOHTos3hvzJ3E1_btNPobV19JY61emUWSjjlERn-59oXVKy9-2bGEfFJj1Q9vhIKb_RGjWeSIh6vKHKkFR-ff5ZvsJaqkH9IrjfP9TP-qecmXiPt0Ly0W2-1TaPe8hTLrov6Agw8ecAbSrnWTJuJIClJWXGEustC7ZjINKdtTK251J93L1ygv36KTn_wW3o75c', '2025-08-27 07:54:38'),
(4, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyMDk1OTIsImV4cCI6MTc1NjI5NTk5Miwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.tqWqo7PJKfEEAfHOzPiSoIC4Z4BtkgayyCoKemIC98TT6HMN61WX2-HEkDEh_UWX92dieQn6GaLNACWU3EzGEYuI3JSGeU1cgii6Dmki9HTbmGiCpVR5qep_uOJfbv4SxPMDr0kmFcCdq6CAoGGHH04vIWNTaWJRsUO3MEHgXJ1vrdYvQ-a3Ok1xjmxTwQAwfGmk5_a9p8M3SiI_No9MfhmhgrZ4hPvolpvyG5orqxS-MygYIHUOpbIx05mpRf76JOy8xtJvwBf_cxJl8XL2WY4w1ZE3xhx2X2FAMzXc7Z8f5Xve5W10iFVz6DHv2ojWaf6H3poDTqi0iBjf4WveYZloTrhnRa2vIOuW73zafnmJkHDhEzqFsp_nvAheTpY8zl_Wg69KuSqp5oSiWXQe3WsMMuqm33ZQz_fjQrmkd4CxcqMTywlsMd_LclfOrQy6d_0CdtPD4naPCaa-OgZgRgCEONY5gJAc9h1c_GyFjAH-75udq4JGxHhubH0DB7oyCNijd9K5IyuStrX52sFri060G2dENyivx9sHvzsZm_mvNMfnDkwgHfU5F7_yEX8H-vlsuAd7Y1oMKztGrs4jDmoI_TU4mlJebPenQMwfLzB48HL7tPEZ5-moLLOJROpE3a_eU5uxcScYGr8szTSzccz45kuKRA9JMsTEGkmAgGI', '2025-08-27 11:59:52'),
(5, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyMzM2ODcsImV4cCI6MTc1NjMyMDA4Nywicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.fLitjbN3pTNfdcUsL2-bE5xiUedm9qLQa6I8Fw6X4Mh5tsJaW5NERwsZflZhR4eDe9tJqIbX3gqYWNx8wb9h1aetlzMGzNzARnWBDGoLE7UfnkXV_mcQr1BTYCZ2U_IUwcUZFrR1v1Krvb5IYnIczhe_5bqsIJpzI2PRuDar8eBzkIf1DwahB5bRIekb_XJFzKUVXJK_KMCH5s5SiguaBYAJiAcdTOqMbbhjywVXA_6YbD88Cvy_medEWnPUDfazMIJnVvOG_BdBRBlix9_7cU3H6PydUr6kRQMT8LfqQrZoHAhTwJzm15YysxYAQmWkRfdM7_th2CeYQ5zApaHv4Zl1uOT5FApaz4DKSAbhzjB06pFJaeGPrN9zmtMWEGdCEle81NBSqQE1spiCfoFqiM2Vmqw_4pX63PooH55yUsdw5EZ-mZ97slGDwsjZns0VTblkRrUoS6asWLJqykJe-t1TgbM6VJbnEEMu5dk4m0Dg249d-0uDVhAkkSp2jNzAFc0GgoikvOfz0Wjd4CZCw_EW0G-bH-37OLwQpUkUOxEUeYbORykJrnxvy-MRL56TmFm6CSKK3AhNEThCoPBOmJebKxG5OuQtJ3KIyUd-y6ygNiQmKraqHur8Twl_64eO5HIjq6CSfxhK6m_FJI_PVv-N4SC7jZHLxcQao_x29DM', '2025-08-27 18:41:27'),
(6, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyNDM2MjQsImV4cCI6MTc1NjMzMDAyNCwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzdXJ2ZXlvcjJAZ21haWwuY29tIiwiaWQiOjQsImJ1c2luZXNzX25hbWUiOiJTdXJ2ZXlvciAyIn0.yyNCrvmXkDNwvAyqcy3jxSBGcVkukpqGPg2tb0n9BKdeTEoIrvztrfl3UC9j7anQn-g4-QKQ973DIMXxKwZ1klrdv5uWtKtEHenp7Puzgq3JTAIbTL4TvkSeU6jOXMo1D3123hWPsfWUoLCWHAWERv1PJjyp4mlGub211YEyBm9QuzMBV-hY4HedOJMxorBk1MZYZooW1xuWrdvns4agdjI3UucaF29gaRV71V8MFpa5zQKxpwnCyC7QG5Nn8nYnlr-LRyDHUooNT9hpU-aARQQftb_Pb77LKLQGScQkaPfp0BELUmYRuGknwin7X9NWVr8H66RSmWMnzdEFhgFrjpe4v5TL2lu7qp9oEf8V-mninhE9TTS09G4g31dgDTYHuTlhPldWp6IwcMq85qdDaKXqFh8FFEc7oOKTzVF3XT7iDNo9WZP9affWf9P3qolF_k8zvbPt77OVFTDenLNbMOnSA6NwvVIHYWCxPrW1ipWt-Vpm8uBhKbSvSragocobfnPwu0yCI_tSRocoFha3evXI26hGER36xmfbTSiED-doDaVEXWjn-fBJciFC4nAWtYt_-AnFWZE-MA_Z5kvYDcqSUaJBRZNptxxu86VXxYdTqFsyKz2rDlI4NKi8T2-c_xFtS9WuKPQ2Yvf1r5dgmBY4vgQk-g7IArjuK0tXIBo', '2025-08-27 21:27:04'),
(7, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyNDM3MDcsImV4cCI6MTc1NjMzMDEwNywicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzdXJ2ZXlvcjJAZ21haWwuY29tIiwiaWQiOjQsImJ1c2luZXNzX25hbWUiOiJTdXJ2ZXlvciAyIn0.aVPMZXvA-h_0d_5ev7ZHzk0AlN4jCbvTNeKOxWcHvevhPZ6tH07-CB2SukynWoOl8Gz2M5zOtqsllORZbOSb1WVW_V692tKy5WIRIk0P_jLJNhkqJm3iIa2vZa4Gs0oIYa__yIQgWqVLV_m-1nfBSn3fE3n2vaqA6KTaRXs5vlvp0XzOJlACfsp5cg_YDWxIoWMpBHkR1vFYy94RhVgbGVawZFBysmpmjjDWlLXNhOs8Hjn5xBVSIzfBq0zAjLCBef6GOHGyA3-jScXXuCDTgbzexZoffDO_vaD91QksJQMphS32UYsY73KUBhDNaAqTA4tRiUV2anlhFs_nit3dxeeX9sM3OXqKAQKDiCpaSeL05W4rL9LYoJSZle3vS4D2INiHJz4MlTeE11fiy1Y_W3LCTYQbYvQcGuEfp8BSBD3j55_-wXRTYHqTzupsygCop6uMQXC3ijp6m68wBDA305TQHD_cbAsMXP17hTvcLLKZpOLkSRwii4IvV0qOB8G6p3w8WU_cQmjjUI1vVA2uGQNj-TmktIfM3KTvJZXEhQW45s5G7hLVVkF4TyB81Ya5hGXd1auL6VzZFo_G2P4YVcnZsmOGU_vn6Qyx_GZEEva0EfuwupL5CccrTLaniZfw1Nuu3bIiiNJCO_Fc4qs0NEJYJF4-vTljqOf1kXHZSbw', '2025-08-27 21:28:27'),
(8, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyNDM3MjIsImV4cCI6MTc1NjMzMDEyMiwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.fhmqFe0Fu7qgotL6zTK_Izi3Tn_hwqaFWym_F7PHbkAT3dsNq7MmDo03c71ZvxKZIagwm2_7RKME5SfBKiZKNTfHWCVSDkSKT1bgtPtmLi8GvTOE2cbKqlhc-HGvDEgvYRrtS2eUI-k72vefY3iwk1oF6vJWwsLaPHBO8Cpo3ZHH223sAPFkEIcqoXe3HUgMB58-FhwfVl04Zc3femOgTQVLIoBiuM8Ykp4aDxHxADNYr0_I9ZUfAjjIcVOrmuP83vOg3OC_OjMwUI3DFprRUZvETOqynMhOmF04anDoNJfXjaiLfIP5xlW20gZ8y4KPuUogXtYtNqxZHPPMWC3bwrVnQX5DfVKh3J8WuDGBNyj2aw26L7eSvHhjfSF9J2bpHAe9TBUi0LEvauilfzHfBxu5dPDNhV_H146pGtnPJXvnn93FPo7I1sEm-rZGgjtVv9bpdhn0H5S4lSvSSnTsGqdfQPOnWd5M39P2bES0k-iZ1WbZWacPk5gtV3SCehh8udLbdKX6QD2QSWfytQVcGM13rdg4fEXGPsSdx5vmSxeki4AlWdWdc-wGtiXFnBjXnKqZQxKNASJb0in8j7jp6CnBvAybpSyv-vjeBodE_5oIMIMsiV8S0ScqA0TtuQTamVvBWpeEFkhlGZedlbbsch3tPTGjI8jCW9CZEESRPw4', '2025-08-27 21:28:42'),
(9, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyNDQwOTgsImV4cCI6MTc1NjMzMDQ5OCwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.FFVr1NT4XzJzomJRxQZB_Q2nbN88DDEowk1MdltA2OGNciu0BeodYOTmzUqrSwB_s0lJz-3ESc4xw77FrXOOmsDfDqbGB0Cy_KVxaUIG8rYWncIjQ8yvj0pLJhY3aAxd6ajCM54DxdN6xSt4Zwbzdd4pLBj1pMTks9d_3uCXCxxmrwHhIBdN1t2e4ulWU6sMULw6VuF5QQnP9Egugf2JgEb0JEGNIxJ65bcQdMzBuJmJBVDeGeoCK_VdCUCA6o7rkLWZ-BR2uN0zJyHAUuhP9V5xRuzXynQnE4drYl8vFlK2NFZDQvWPk5Q_f_WGhQYrRU2bT1plqenMwFxRlVlLDMa-0ORKAIaQ2aN4nv7dxanlkZY2xZLqo89oPniy5QtUf27VbvLcxB361oD8PLMlRGrARgu3tdjcov2OAUH5axEyI9jQpxTp3OD3XKYN3vzu-rj2vXE4BUaz5rZkqXdfliJd6AQsDXT2LDgCv6iAhROAnUZxkQ8_q7e3CB1nfIWm8gJVzQUK8klSC1zu0pNAQM5OuElGuDdVbgYN8FQJVQff4JWzYKqq5qNJdGNbSK7yRk_-Wfu5MjSFr90gyOTUMPJvTstH6ZwO5W3GREkAQpCwtHBouxGz0LOyZGxjyTTPlYfNTQ8scoNUyC6MBEg-DdnJA9qRLeNqu3v4oAdcshY', '2025-08-27 21:34:58'),
(10, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyNDQ4MzksImV4cCI6MTc1NjMzMTIzOSwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.PlQJY7Y3QdEygziisjwLwOwY6P1oh0yL5MCD6skeK7gj-OajWD60m2b6Z_m8ydmLncRS6_tar5M2g9pH1KpaDvG1s_1Cr0UladxzdlADbU6iX1fj3EaRIQQgNr9Eqw4o8aQEgRUlUs58lMgfUpIw0YwAVI7pBCGNSQuqfdJz4Bqw30k0lLsEx05lYzN_835FdXb0CaXljYLCMP3cSiLS45Qyy3haaNnpYSG8BropHCQrFolDPqmYvua_W05o1YSTVTKC3Lsl6ozqp3GLnV087KalCvGIoVTNTw9Lv7UD08DpAKkE3PX3vKTfERQPhSjsTEggkLMP91hNd5Ec-OWEKPBie2ypnfjFoM5dPHE9ICmFNMqT0uPIJGO3SadqD3wya_XpTs14Mka8wfCCbzDqB_6unUx65WyjkjQWSmdpG7UMQbhoE3PAU53122DsVB-JMO9wd1n3tLhwwk1LGesoSvcfrxWHeLcmE-6VN4ot70VczDwz-bPkZHo2KI1OYV7GX_lG4zlIuG3jRMwiVTPlwXeRSilRoK6S-wzfd4Cj9BZI4i3eydgnWs2S2VfB_TApvNk8klbQf5H-qXr18mtxpsLbmgtArZ6IKVsBEBn1xOLU02Tu7luAytbD3ABmtiX4nwytJD2Bpsd_Lt76Hr4USY6KvQ9qkVBynKUVc790TtQ', '2025-08-27 21:47:19'),
(11, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyNDUxMTQsImV4cCI6MTc1NjMzMTUxNCwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.yBu5L2lnp86ElzXInLnKg0i5flyf71FQ71VLccpVRm_9FUBxzq4A6i2hYFqEA7gOD4C1ybDIJWmTiomIri60lyxFJJmodw1nQwMYo2nuf_2PUw9vxBS7P-hRAE4tXyQDfqS4Ufqw6fPJbFPmyDDGyecbsJAd4_4Khq5hrpJhMi2AgY9oXL6LmvrlVW2XmFTrS6zfMmjU3j2aZDeuIerkddh8PwNt102fH8rOqfANSuSQ7PsL7iyrsZsPr4W7cCRSFuIaJw3v20rnnOvkcTD9hHU2HlfxX7NDjBShJDFcJjcvrrhaizLD5VZfeuY9YYcmjNzbFrp8wFWPhDoT8SILw_85r0VWG9dufxhVKLhs_EQZeabJ5Kyb2oPge_SWhPfeCZebwlna09azg9tfBNJcquuaQKk7s-xwBq4yTMbZBlTX5qtkDwM0PueodSzm0qZUyeXyoS0fY5JJqvf4Xwyeop4tGDGnxR6zAHkU6dDmAt9ztPejbv7P64oyJOG-IX2PCyTj3BzRX9CbpRZs7J5-Nl39x-eeRL4tiu5CKZ0xz_zWFxPjdm4Qwzvz2pYitVhL2IBM_cWJnFBx79AtJG-7QTpE4vvs6TIJWiMPK0ZO7NNYFL76Dbz_O2lKQLlKUtZwh58J4oK4JJYrlZrafYhL4n3pDkT6J-xWnzXj6rZMkKo', '2025-08-27 21:51:54'),
(12, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTYyNzg4ODgsImV4cCI6MTc1NjM2NTI4OCwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.vWVRJ9T--vAGyNqyoCGz6SwYKhceXx9GL7zJwhxMzgZrLbriuB0-zI_fQTzGteV42YxZII-dRXdWOW8QIZ-R8a8aZcKJZMuGjp-v3LhN4uphZwgUbTkGf3aQNPYQKUnMOO7go1Is9ixIr4MRkEl8igBEoQcAExkCmrP1NaJfFTOscQbDqws59GtbNmezr3L0qkze7-LMcnffhacTwZZ1c2xSMwGuon0t05F8ha7WuSHgPwFisZ6_csqi0TpiERPAH9a2dT1SZKi47c0fAADakxkYIv2h8j29UPKAKjLWyQo_BHBKM6ERpA8Al7E9McKFMtvT_mmZ_reaHMdSU7d50LaGnfiITKsXvu0Setj3M3xlud2UYyCuVEe2va0LPl1K0HagsAs4R3BeRig58NnsLxBqc90I_mbVOY9MvVscXHlrTcTyTT69GIJSXX3RfjEbizG_cNiMSv6JryNbQNJqnoXdgBNbUsaD2pzw2um9quNr4pIt9HTLKOcywQh4a7guPDV4bl0nMM_sUVm04hJwU7yh4J9zVj5mpr4r4WsA2eWAXLxFmYlJyPwZqJSyI4sU3A55f5FrZSpJhoXF9OSdDwaKzv0r8rMsi9lrX-A37C4fkY2Zi3o8mUEYfxBJmEBVsNE-7Cg2VOAvJBYLtBsDSnlYik_Phrc5DvkLrKmP9TI', '2025-08-28 07:14:48'),
(13, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTY3OTYxNDYsImV4cCI6MTc1Njg4MjU0Niwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.Ij1YL3a16SGMmdWkWpTYPBAc32SuBZ80TeOf2qaFvQYprOOyprP7e7KzsPdkC_CE7n8fIrlQaS-6lt2mN8xKA6e9LScnL3afLEtKx0bkquNKow7uEQCkiqev4TeRy3_RLNAqOEhCzxcNF1PNQaTw0gVyhjtWICSr4Dk-GSVZwIrrZYIhvNyi2o5s6Vdo8RBUTpJ7YWmCF_VfQ4YvolQNNWiVAA9YFe6K_nuqYHu-z6ThGwUXwU7KVuI64e72fvE2HgXs3JHOCsEgzeFIQXRzXdKZZxYhXRybVHlckTng8HI82zRCaR9gsGp98WFUNEJTIzst8O2DILOiPeAdiLjOcip3CerewfRmjUQa9FZxwcLWeOE9GkSLDFh78xYjv8R9iBaxER0XP8Frrr94l_Jw0WKVEUGCTommap3IF_XkmH1a3rEwyG-WaZcINjRaT_IdQ2n6UuO84bje-NnQ_ush8LQb50D6VbqqxWgRCUk8T84rQXObJB3O3f06QGg5bnIQCKVQqjykM_pqXTiPlfMejBp7i5Cq3pRq12-LOxV0zNtaSKpdLQQ1II_zK9YbeLBXM8qKlvR9vp0fqeSPsFNTImq-Wf0r-DjfsqZKNs08HVkEli0d3M1OGRCLAGsZ59Tv39jMwlyaAeQmyXwX_4TdRBxw6520IdU1S96ZMLfUnMo', '2025-09-03 06:55:46'),
(14, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTc2NTkyNzIsImV4cCI6MTc1NzY3MzY3Miwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.yi2OlsLzb_XbB-PRXBlSQJAHqNJP7x1gtXMFSXOIbJiTnYFygUrkO2t8KLTjQy4-4WgkGiXNxeuVYCqbQ1nIh9Hsw_04HunZHqn1HCjX9l6BarK5Mq4E8EEgFrLGb0nepeeTcl7InDBTjVfAH9t6GVcJSVkqu8gkh8xfCQWKFQMNyrNj7xp4Wvo8wB-TNPgHOwfa2afXqH-wEkaZRCRICugFghHz5h2hIif6OoMYenzI99GAh4yjxI4oIihSO9AaBh90gRDlb675gDNNZN6_odcRU_b-Q1Fd7isyw8_v_g1C0Hr2BAMjt5a6DBWrShh4JJM6AJTBPRVAZcJExmG4CCt6XHEf-JuURtLKTCaFj_Ie11WmIRy8x2YRoSqBP7G8zhO0ySC40W7PpbcQr-s5OalUDgsSJUfXbYWNXjS8R7FM1L1GX3qhjOCnXKXx0uFeuOiKmQWMzSywswDnhol-Ark9MVXMYczDN-_iVVgSgjgEsvsniLP7wgjQV76XqpdlFu-iM3wgO7NMklQ4j5jxOHQQmw475Eioq-6y_kozXpproEBDQoQmwtN2oh7g34tgVe-DbPYM_iCfbhi69TqCH4JU61jjWi3qb0pYX6dNj7oOPGQH0TS64260K60sE5Fa3-tLbPFP486zg4bS3-65tIqhY9VEGMwrszmNMaXgDPQ', '2025-09-12 10:41:12'),
(15, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTc2NTkyODAsImV4cCI6MTc1NzY3MzY4MCwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.dkoz4UyHauxe4h1rZcNQxK7jnsf4UuKb6WQvh6Ljo9TuAQwqHqcbPkz529RDI19sj-pMfqRWqwOx5Ec_dNIw37W_55wnewaBXYXn14LReSsGYm_bGOcMRePwr7KkOpKwa7szMSNdL6fi2SkLkE-z2dmPA9G2Uy0YLIghi1M02iGJyOkjVsPE8pXVCzdgiWKwOi5IVN6iP_r1N0cs2T9Gt3nMe6vMANQkGumKPMApx86BX5S6IuUd-naTdiWk3Xknjty0SUr7dz2iB9Y77cThUVfJvTfVzjDtQ0jD1svD6oiDjeRRS6TOQ9gLUyC5MpGupDxglUHwKsw07wqnJjitG8VdAO_tJZkKsi0RPOeW4cqzkYGAO5ryjNZ2MoP-0qqk97rdBF9GlRGaJCe7CMdkqDDmBeH7QRwHUTpPcFMpqa99IPMst9iy1WMdknjCjJZWfT6UviBtDBBAG2v1S9n_cmERbhwBx6OGjtlgwyg1NNH7jrGdSixmCNiwBBHdBciHX1AfbD_Aphvq_dyyYG7EIzNApHwtVgAGU-UPaHt8IY7k4miZrgaWFVj5dWRugwn_2Syo1FfmcbiSb8kArrdysUpUV67UV7bXhc5lMm8R-ln8e-rdx4RMH8A1SaeHXNZPxm9fPxUiJa-jQhhr2Ouw7HZASuvoZCaphrkqmMc2qJ0', '2025-09-12 10:41:20'),
(16, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTc2NjMwNDcsImV4cCI6MTc1NzY3NzQ0Nywicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.g8IH_i9h_pKjmKGNEFBF06rzOnMXWMT4JgoZvz3gp46MJz9rnGfYhgc5VObIdGgG8W-KffWobBDu4e1lsYJYqqbTwNZZbkGZ0Mt3v84vRkBGnJbE30lqZp1whsCpHprVNMIAwy2PL5g4xmgnxMGHFm6EpDcMNmxMjzPBD2WHpBeEXXYbh3MWdYjjpNSdB_YhZEmhCJD0p5vuvZMF4lhQzVnHF2TnKuUt3mV2maNjdU9RAsYrJdQ2T2-cRM2sy7BekWpjNXCPfdanB5T3qG-HqytoUPTjiQv_Tt275scmLAICBRzYf6uEwT9HDNIMYJEfAfAbOMz1GnNOGALyJAySp8c0p9kd2J-xXLoplhpofjwNE0gOBqYPhl8zLWkPxr1sT6N---R1O1ZSb_nsE1scWjKdAmnJ1zu9IWW6I65dFYqtSbZOr7WY61THne6-blQ006zlHlLRUWTgF_Q4DKo93mpFed8bo88s7onmkzhdqOL5HJDQT7tsCnf3WY2z3l3_-nycDg-H7KWSt1lrPV0MzyIL7PoDq-1_L5cORov7KXpP8GcBxYlqXKFPGcNgnQm7GfF0y3ptTKkkon6l8H8G7O1TVBmWuvlgqdDPAPuNhgfmA53D5Bs6aCizlVX8qMxxJ4nrasRwtbk0Njkh7cH1sQA1wPlJFyv9y68Z2j0bagU', '2025-09-12 11:44:07'),
(17, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTc2NzQ3MzUsImV4cCI6MTc1NzY4OTEzNSwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.XC_cUbQv0nAisdYTe8FZWjM1SIzdUxSFYx3HV6Zgss8mT6vuCPf9R9395pd-i8A3U2u6HJwTQpembbo1lpbLxt6oicY4Cv4XWc0V4lXsEjZlOiTtwJ3Ml-W6d2fq_EQLy9g7ybmSEooFEF9vbnHf0Mc8vv7O-dFyPPA2ynGQKLGKp0JnT9IryA5nSVmMX1C8Bl4a2BAcCA2nCyghbvVBQ_1Nh3OcnzUPU06K0PTzxfXt0EpTGiPb7q4uacOcGM7jE8kEqlCSR9HiHmk-0PpVVsOGfXCPQpSRX4BqCcrOnPVTUegqnFrp8jqbE_TFvuYIhE6m47UCQb3-Grz-p04tbmUMPBknxM40A0uub34jvhs_gGhhfn6t73YBRhZLgSJg6mEEuLJUJjpa6Kp9SQKPvFL65OB1_wXSzh_mO3b7UieLQvqXrkmA7-xEFONLmY6eXBKcSV5oX30awb0PdL30kUzcXi6rBD5EC9vS2mYhRvzs-YZbTxY8tQEIsSQW50zNSD6jyRSZKsqSroI7vyHWctJE32ngC6q6BEFvZmbobB5mjsVVYM9LyAkdn30HbIV88XbDbSirxg9GFbe7hQImXLODcHueu4d2pqJ5JiGkkWPZwmSGFbtHe-lfKDICU9207XK0tEWHirsGBpmcIBSv5hX6-D35iTT-m9ir4A9ZAMM', '2025-09-12 14:58:55'),
(18, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTc2NzUxMzYsImV4cCI6MTc1NzY4OTUzNiwicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.f2pCgE4v8qUdC9ZfAVj1fTfRPPMkFLJO-KTFwt17bghOodoSUppLaL00bUCD8dE-2Y87rxBWU0sU7YHNOG5D1MewQ_4QNjUARSibba2Y6-hrz104P4JFVIBUPZWHEzi3VtZDllhBbDRFwSXSk6xURZCmy77YjtPNYmgcG0FBvgWksEHvuPRMiaYiuFIRwXg9ICvc4XDqjBH4Yz-gxgmworPWPIMaHHfuXe2EatmyRs-ymLsnK8azdjpPEmGZ9E9mY6P-RRhrA_bW_dd7BXff4JtXDuJf1fw1xMkSL4VRtyIjwiaCLl_mp_Rj1KviXP2lMAgH99b-JDpUMWqPD0zjAqbwXPEA0bdaCd39X3wgR68zjv3UQrrACin4Bj46E_MKEWVLEiDr8J3EbEhEaD3eqgfZxtneUDGwUfYMm0i_UZb74P8Mf-gzDQLLuBgBgpQ-27EhNUjUMLPqZQMyyJCWg4h90FBCkQKqu2RiqH-QB7mIpCVyy-uvg87tfNO1dWdyjgl3iLefxGRAsTHGC3R4fjrNtOl6xpZK7gKoLX1MHMERMuGM6icownXBtFvL_OD8u4EppdepRGInJ1M1Fyxb9bNuyLTWZf8DPbHuE739PHYRlqLmEu9oIcqct0rBrGkg5-CQ9jLDVTYOFtOMF1kmq1vcCrIfqW4lKuXuTljgYfE', '2025-09-12 15:05:36'),
(19, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTc2NzUyMjcsImV4cCI6MTc1NzY4OTYyNywicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.Ja63pswtpQZUoCHubomXjIgVi-m2ob3gpkzUEbfT7TFuS80wciEBahy-hTedJYd77VRbAOP4TQQsFvnCVsRAI7xkV6Rkaa6ElfGVwjQoSK-Owb5aas7Qlpyd72TUoYovBYjoWA3-1iHRaMTrjR1HWX6_cfMBSgISDGe6cRHdZ9Bsdsgw5kz8LvNoAaoyXvDlfD45NQ5QpEU5T_j4-d2JpK2AallSGs4bNC6nZtWb63-_WmRTQq03l3TaRVXfdt-AD8zjCBLVk-1VzuAi331h28wTJBy1Pv9eN4Gut9N0l7wZX_bTh6bRXUiIdhHE3KV58YjQ-5Fw0EksfP10nNa3Rh0s-WZ0zFKWVC0M01Z6qP2DZK9ikpxA7BSHA8jGMvEKNfhHaup4WGCeKxgoyW37badDKpkZgucnmItjYjcvpKyL-VLh0P-ekZ1fTqMou5GI1iXkHuCInm_rGfRRksp35UXlQ8xvgyYgyACHOJ0MamU00SMyqQDWdwHzq65thdppQD5PFuzxRsi81fiazM8J4MNHMDP1N6lqCCYdaNhZWEgoZ_jtsNPYz1CjGfr6FtLj574QqgooA8VtctyzK-FvGCIPmFzyrurK8uTFQP1cB7VxsrhRl7o-wkPZecgQx-iSiPiuPIybTO23T-fmVSpGzyl7edbMrai5GqJgwyePEL8', '2025-09-12 15:07:07'),
(20, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NTc2NzUzODcsImV4cCI6MTc1NzY4OTc4Nywicm9sZXMiOlsic3VydmV5b3IiXSwidXNlcm5hbWUiOiJzYW50YXRyYUBnbWFpbC5jb20iLCJpZCI6NSwiYnVzaW5lc3NfbmFtZSI6IlN1cnZleW9yIDMifQ.ZnLNR_lWYwnMEiWHxK47XwzQA8xWg7BJ72Adj1XuQ4us6PwYYQRi6CBpW7ia9b_9AyLHiBkJNhrLG91_7Qe-mwVvDOS5dx_6zOBN8XIOGCqEeuaTGgZFiW9S2cr72OJKcCdZtJHtGrCM79PvjbedU4SSZncZx4QOF9NyCkXL7beriCItQxWO2tHhIweHx90LH60ffO1RZZ8pL-ukCFowrKcEh6g8MHagQZwI6IB_AIguYfQKWBmRbvab7ePq23dYVw8bcIBX1Kb0LWh6zWirWlMACqrrwvZt8WJ9eAEhbE1vwp9AJeUFY5Ny6REhqgBXHvJD1HHqamvgwEDYiW6v7BE794TfNTGiX8BVand7SAVj1kCza9itWMaX_jmobSw0WupulGHvPsK14JguMTEynYdz-B6bbGsBgyBpZQiOaXxZFMVipuhmc-YhcHnqkFOtrQGJNBeLZpu9RWZYlKU9L94kScAftn87irUzKHT0-ptnCY0dUoqeVRVHDcHegwgJMZRt5i8fCHNhCCg8TutvzGYed8WOdFPmMlr0OWET85mShq9AxMnLCsnLn2rEi7w2ua0FNsokbFNJ7uM1jrXQHc5aQhvaDL-qMtuBfIuGv-C3tpGFq_GgcHkSMVN_bPc48P3Paan4vmCp7humlg8A5MQwCXXkRESM1Dv5kJh3BBA', '2025-09-12 15:09:47');

-- --------------------------------------------------------

--
-- Structure de la table `claims`
--

DROP TABLE IF EXISTS `claims`;
CREATE TABLE IF NOT EXISTS `claims` (
  `id` int NOT NULL AUTO_INCREMENT,
  `received_date` date NOT NULL DEFAULT (curdate()),
  `number` varchar(255) NOT NULL,
  `name` varchar(45) NOT NULL,
  `registration_number` varchar(45) NOT NULL,
  `ageing` int NOT NULL,
  `phone` varchar(255) NOT NULL,
  `affected` tinyint DEFAULT NULL,
  `status_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_claims_status1_idx` (`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `claims`
--

INSERT INTO `claims` (`id`, `received_date`, `number`, `name`, `registration_number`, `ageing`, `phone`, `affected`, `status_id`) VALUES
(1, '2025-06-29', 'M0119921', 'Brandon Philipps', '9559 AG 23', 120, '55487956', 1, 1),
(2, '2025-06-30', 'M0119922', 'Christofer Curtis', '1 JN 24', 96, '54789632', 1, 1),
(3, '2025-06-01', 'M0119923', 'Kierra', '95 ZN 15', 72, '58796301', 1, 4),
(4, '2025-07-02', 'M0119924', 'Test dev 1', '1525 ZN 45', 48, '48503895', 1, 1),
(6, '2025-07-05', 'M0119925', 'Amanda Vickers', '8596 XD 44', 60, '59203456', 1, 1),
(7, '2025-07-06', 'M0119926', 'Daniel Moore', '4412 BG 12', 36, '59216789', 0, 1),
(8, '2025-07-07', 'M0119927', 'Lucinda Evans', '7925 ZA 09', 15, '59984512', 1, 2),
(9, '2025-07-08', 'M0119928', 'Marcus Reed', '2233 BY 22', 90, '59321478', 1, 2),
(10, '2025-07-09', 'M0119929', 'Jasmine White', '6547 CG 88', 105, '59123658', 0, 1),
(11, '2025-07-10', 'M0119930', 'Nathan Scott', '8833 LM 01', 20, '59632145', 1, 2),
(12, '2025-07-11', 'M0119931', 'Sarah Foster', '1144 JN 99', 50, '59876543', 0, 1),
(13, '2025-07-12', 'M0119932', 'Tyler Brown', '4321 GT 76', 80, '59001234', 1, 1),
(14, '2025-07-13', 'M0119933', 'Emily Davis', '2312 QQ 56', 110, '59234567', 0, 2),
(15, '2025-07-14', 'M0119934', 'George Clark', '9988 RS 34', 65, '59432167', 1, 1),
(16, '2025-07-15', 'M0119935', 'Isabelle Turner', '7865 YT 90', 30, '59764321', 0, 2),
(17, '2025-07-16', 'M0119936', 'Liam Johnson', '3021 AZ 78', 25, '59112233', 1, 1),
(18, '2025-08-07', 'M0119937', 'Jean', '7387283 AN 52', 13, '3263723829', 1, 3),
(19, '2025-08-07', 'M0119938', 'Marie', '28938 IT 08', 11, '827323739', 1, 4),
(20, '2025-08-07', 'M0119939', 'Lulu', '893892 TF 03', 10, '8297379230', 1, 9);

-- --------------------------------------------------------

--
-- Structure de la table `claim_partial_info`
--

DROP TABLE IF EXISTS `claim_partial_info`;
CREATE TABLE IF NOT EXISTS `claim_partial_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `claim_number` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `make` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `model` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cc` int DEFAULT NULL,
  `fuel_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transmission` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `engine_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `chasis_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vehicle_no` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `garage` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `garage_address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `garage_contact_no` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `eor_value` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_claim` (`claim_number`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `claim_partial_info`
--

INSERT INTO `claim_partial_info` (`id`, `claim_number`, `make`, `model`, `cc`, `fuel_type`, `transmission`, `engine_no`, `chasis_no`, `vehicle_no`, `garage`, `garage_address`, `garage_contact_no`, `eor_value`) VALUES
(1, 'M0119928', 'Toyota', 'Corolla', 1800, 'Petrol', 'Automatic', 'ENG12345', 'CHS67890', 'VEH001', 'Garage ABC', '123 Main Street, City', '123-456-7890', 15000.00),
(2, 'M0119929', 'Honda', 'Civic', 2000, 'Diesel', 'Manual', 'ENG54321', 'CHS09876', 'VEH002', 'Garage XYZ', '456 Elm Street, City', '098-765-4321', 18000.00),
(3, 'M0119930', 'Ford', 'Focus', 1600, 'Petrol', 'Automatic', 'ENG11111', 'CHS22222', 'VEH003', 'Garage LMN', '789 Oak Street, City', '555-123-4567', 14000.00),
(4, 'M0119923', 'Toyota', 'Corolla', 1500, 'Petrol', 'Automatic', 'ENG123456789', 'CHS987654321', 'ABC-123', 'Garage ABC', '123, Rue du Test, Quatre Bornes', '52521212', 105000.00),
(5, 'M0119925', 'Hyundai', 'i30', 77233, 'Petrol', 'Manuel', '036 NI 09', 'CHS987632', '787273 TG 09', 'Garage T', 'Quatre Bornes', '25327638', 250000.00),
(6, 'M0119926', 'Mazda', 'BT50', 1200, 'Petrol', 'Manuel', '036 NI 09', 'CHS987654321', '626 GT 23', 'Garage TE', 'Port Louis', '543729836', 105082.00),
(7, 'M0119931', 'Nissan', 'Altima', 2000, 'Petrol', 'Automatic', 'ENG22222', 'CHS33333', 'VEH004', 'Garage QRS', '321 Pine Street, City', '321-654-9870', 17500.00),
(8, 'M0119932', 'BMW', 'X5', 3000, 'Diesel', 'Automatic', 'ENG33333', 'CHS44444', 'VEH005', 'Garage UVW', '654 Maple Street, City', '432-987-6540', 45000.00),
(9, 'M0119933', 'Audi', 'A4', 1800, 'Petrol', 'Manual', 'ENG44444', 'CHS55555', 'VEH006', 'Garage RST', '987 Cedar Avenue, City', '987-654-3210', 38000.00),
(10, 'M0119934', 'Mercedes', 'C200', 2200, 'Diesel', 'Automatic', 'ENG55555', 'CHS66666', 'VEH007', 'Garage LMN', '159 Oak Avenue, City', '456-789-1230', 42000.00),
(11, 'M0119935', 'Kia', 'Sportage', 1600, 'Petrol', 'Manual', 'ENG66666', 'CHS77777', 'VEH008', 'Garage OPQ', '753 Birch Street, City', '654-321-0987', 19500.00),
(12, 'M0119936', 'Toyota', 'Camry', 2500, 'Petrol', 'Automatic', 'ENG77777', 'CHS88888', 'VEH009', 'Garage XYZ', '852 Spruce Street, City', '789-012-3456', 36000.00),
(13, 'M0119937', 'Volkswagen', 'Golf', 1400, 'Petrol', 'Manual', 'ENG88888', 'CHS99999', 'VEH010', 'Garage DEF', '963 Willow Street, City', '321-987-6540', 22000.00),
(14, 'M0119938', 'Hyundai', 'Tucson', 2000, 'Diesel', 'Automatic', 'ENG99999', 'CHS11111', 'VEH011', 'Garage GHI', '741 Cherry Avenue, City', '432-123-9870', 33000.00),
(15, 'M0119939', 'Honda', 'Accord', 1800, 'Petrol', 'Automatic', 'ENG11111', 'CHS22222', 'VEH012', 'Garage JKL', '852 Maple Lane, City', '543-210-6789', 28000.00);

-- --------------------------------------------------------

--
-- Structure de la table `communication_methods`
--

DROP TABLE IF EXISTS `communication_methods`;
CREATE TABLE IF NOT EXISTS `communication_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `method_code` varchar(45) NOT NULL,
  `method_name` varchar(45) NOT NULL,
  `description` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `method_code_UNIQUE` (`method_code`),
  UNIQUE KEY `method_name_UNIQUE` (`method_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `communication_methods`
--

INSERT INTO `communication_methods` (`id`, `method_code`, `method_name`, `description`, `updated_at`) VALUES
(1, 'email', 'Email', 'gffyfyuf', '2025-07-21 11:47:01'),
(2, 'sms', 'SMS', 'ggygu', '2025-07-21 11:47:01'),
(3, 'portal', 'Portal', 'hhghgh', '2025-07-21 11:47:24');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `employment_information`
--

DROP TABLE IF EXISTS `employment_information`;
CREATE TABLE IF NOT EXISTS `employment_information` (
  `id` int NOT NULL AUTO_INCREMENT,
  `users_id` int NOT NULL,
  `present_occupation` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `company_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `company_address` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `office_phone` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `monthly_income` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_id` (`users_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `financial_informations`
--

DROP TABLE IF EXISTS `financial_informations`;
CREATE TABLE IF NOT EXISTS `financial_informations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `users_id` int NOT NULL,
  `vat_number` varchar(255) NOT NULL,
  `tax_identification_number` varchar(255) NOT NULL,
  `bank_name` varchar(150) NOT NULL,
  `bank_account_number` bigint NOT NULL,
  `swift_code` varchar(255) NOT NULL,
  `bank_holder_name` varchar(50) NOT NULL,
  `bank_address` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `bank_country` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_id_UNIQUE` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `financial_informations`
--

INSERT INTO `financial_informations` (`id`, `users_id`, `vat_number`, `tax_identification_number`, `bank_name`, `bank_account_number`, `swift_code`, `bank_holder_name`, `bank_address`, `bank_country`) VALUES
(1, 1, 'VAT0012345678', 'TIN4567890123', 'Global Bank PLC', 1234567890123456, 'GLBPPLM0123', 'Jean Dupont', '10 Rue de la République, Paris', 'France'),
(2, 11, '15', '222', 'mcb', 1111111111111, 'V446', 'Aisha Patel', '221B Baker Street, London', 'United Kingdom');

-- --------------------------------------------------------

--
-- Structure de la table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_payment` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` int NOT NULL,
  `users_id` int NOT NULL,
  `claim_number` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `claim_amount` float NOT NULL,
  `vat` enum('0','15') COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `invoice_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_paiement_status` (`status_id`),
  KEY `fk_paiement_users` (`users_id`),
  KEY `fk_paiement_claims` (`claim_number`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `payment`
--

INSERT INTO `payment` (`id`, `invoice_no`, `date_submitted`, `date_payment`, `status_id`, `users_id`, `claim_number`, `claim_amount`, `vat`, `invoice_date`) VALUES
(1, '230736', '2025-07-29 21:00:00', '2025-07-15 21:00:00', 6, 5, 'M0119921', 10000, '15', '2025-08-11 10:40:10'),
(2, '23076', '2025-07-29 21:00:00', '2025-07-15 21:00:00', 6, 5, 'M0119926', 20000, '15', '2025-08-11 10:40:10'),
(3, '230737', '2025-07-27 21:00:00', '2025-07-27 21:00:00', 7, 5, 'M0119927', 372999, '', '2025-08-11 10:40:10'),
(4, '230738', '2025-07-24 21:00:00', '2025-07-25 21:00:00', 7, 5, 'M0119923', 787834, '', '2025-08-11 10:40:10'),
(5, '230739', '2025-07-19 21:00:00', '2025-07-21 21:00:00', 7, 5, 'M0119924', 12000, '', '2025-08-11 10:40:10'),
(6, '230740', '2025-07-18 21:00:00', '2025-07-20 21:00:00', 8, 5, 'M0119925', 21999, '', '2025-08-11 10:40:10'),
(7, '230741', '2025-07-28 21:00:00', '2025-07-29 21:00:00', 6, 5, 'M0119928', 787372, '', '2025-08-11 10:40:10'),
(8, '230742', '2025-07-26 21:00:00', '2025-07-27 21:00:00', 7, 5, 'M0119929', 10377, '', '2025-08-11 10:40:10'),
(9, '230743', '2025-07-25 21:00:00', '2025-07-26 21:00:00', 8, 5, 'M0119930', 107392, '', '2025-08-11 10:40:10'),
(10, '230744', '2025-07-24 21:00:00', '2025-07-25 21:00:00', 6, 5, 'M0119931', 7837720, '', '2025-08-11 10:40:10'),
(11, '230745', '2025-07-23 21:00:00', '2025-07-24 21:00:00', 7, 5, 'M0119932', 1783770, '', '2025-08-11 10:40:10');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_code` varchar(45) NOT NULL,
  `role_name` varchar(45) NOT NULL,
  `description` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_code_UNIQUE` (`role_code`),
  UNIQUE KEY `role_name_UNIQUE` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `role_code`, `role_name`, `description`, `updated_at`) VALUES
(1, 'surveyor', 'Surveyor', 'Utilisateur qui fait la vérificatoin', '2025-06-26 22:08:34'),
(2, 'garage', 'Garage', 'Utilisateur qui fait la réparation', '2025-06-26 22:08:34'),
(3, 'spare_part', 'Spare Part', 'Utilisateur qui est le fournisseur des pièces', '2025-06-26 22:09:57'),
(4, 'car_rentale', 'Car Rentale', 'Utilisateur pour la location voiture', '2025-06-26 22:09:57');

-- --------------------------------------------------------

--
-- Structure de la table `status`
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status_code` varchar(45) DEFAULT NULL,
  `status_name` varchar(45) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `status`
--

INSERT INTO `status` (`id`, `status_code`, `status_name`, `description`) VALUES
(1, 'new', 'New', 'Première statut des claims après affectatin'),
(2, 'draft', 'Draft', 'Status pendant intervention d\'un utilisateur'),
(3, 'in_progress', 'In Progress', 'Status après submit des formulaires'),
(4, 'completed', 'Completed', 'Status quand le paiement est effectué'),
(5, 'rejected', 'Rejected', 'Statut pour rejecter un claim'),
(6, 'under review', 'Under review', 'Paiement en cours d\'évaluation'),
(7, 'paid', 'Paid', 'payé'),
(8, 'approved', 'Approved', 'Paiement approuvé'),
(9, 'queries', 'Queries', 'status querie');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `created_at`, `updated_at`) VALUES
(1, '2025-06-23 07:54:40', '2025-06-23 07:54:40'),
(2, '2025-06-23 07:54:46', '2025-06-23 07:54:46'),
(3, '2025-06-23 07:54:53', '2025-06-23 07:54:53'),
(4, '2025-06-26 22:49:06', '2025-06-26 22:49:06'),
(5, '2025-06-26 22:49:14', '2025-06-26 22:49:14'),
(6, '2025-06-26 22:53:25', '2025-06-26 22:53:25'),
(7, '2025-06-26 22:53:30', '2025-06-26 22:53:30'),
(8, '2025-07-23 10:12:06', '2025-07-23 10:12:06'),
(9, '2025-07-23 10:12:35', '2025-07-23 10:12:35'),
(10, '2025-07-23 10:14:34', '2025-07-23 10:14:34'),
(11, '2025-07-24 10:40:18', '2025-07-24 10:40:18');

-- --------------------------------------------------------

--
-- Structure de la table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE IF NOT EXISTS `user_roles` (
  `users_id` int NOT NULL,
  `roles_id` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint DEFAULT '1',
  PRIMARY KEY (`users_id`,`roles_id`),
  KEY `fk_user_roles_users1_idx` (`users_id`),
  KEY `fk_user_roles_Roles1` (`roles_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `user_roles`
--

INSERT INTO `user_roles` (`users_id`, `roles_id`, `assigned_at`, `is_active`) VALUES
(1, 1, '2025-06-26 22:47:37', 1),
(2, 2, '2025-06-26 22:47:37', 1),
(3, 3, '2025-06-26 22:48:09', 1),
(4, 1, '2025-06-26 22:53:08', 1),
(5, 1, '2025-06-26 22:53:08', 1),
(6, 2, '2025-06-26 22:56:16', 1),
(7, 3, '2025-06-26 22:56:16', 1),
(11, 1, '2025-07-24 10:40:18', 1);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `account_informations`
--
ALTER TABLE `account_informations`
  ADD CONSTRAINT `fk_account_informations_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `administrative_settings`
--
ALTER TABLE `administrative_settings`
  ADD CONSTRAINT `fk_administrative_settings_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `admin_settings_communications`
--
ALTER TABLE `admin_settings_communications`
  ADD CONSTRAINT `FK_42D45B45260B1BF7` FOREIGN KEY (`admin_setting_id`) REFERENCES `administrative_settings` (`id`),
  ADD CONSTRAINT `fk_admin_settings_communication_communication_methods1` FOREIGN KEY (`method_id`) REFERENCES `communication_methods` (`id`);

--
-- Contraintes pour la table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `fk_assignment_status1` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`),
  ADD CONSTRAINT `fk_assignment_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `fk_claims_status1` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`);

--
-- Contraintes pour la table `financial_informations`
--
ALTER TABLE `financial_informations`
  ADD CONSTRAINT `fk_financial_informations_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_Roles1` FOREIGN KEY (`roles_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `fk_user_roles_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
