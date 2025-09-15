-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 15 sep. 2025 à 08:26
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
-- Base de données : `scs_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `business_development_contacts`
--

DROP TABLE IF EXISTS `business_development_contacts`;
CREATE TABLE IF NOT EXISTS `business_development_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `portable` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `business_development_contacts`
--

INSERT INTO `business_development_contacts` (`id`, `name`, `email`, `phone`, `portable`) VALUES
(1, 'John smith', 'rm@swancapitalsolutions.com', '(+230) 564 7834', '(+230) 5467 9087');

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bd_name` varchar(50) NOT NULL,
  `bd_email` varchar(50) NOT NULL,
  `bd_phone` varchar(50) NOT NULL,
  `bd_portable` varchar(50) NOT NULL,
  `sc_address` varchar(50) NOT NULL,
  `sc_email` varchar(50) NOT NULL,
  `sc_phone` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `contact`
--

INSERT INTO `contact` (`id`, `bd_name`, `bd_email`, `bd_phone`, `bd_portable`, `sc_address`, `sc_email`, `sc_phone`) VALUES
(1, 'John smith', 'rm@swancapitalsolutions.com', '(+230) 5467 9087', '(+230) 3 6263 6813', '10 Intendance street, Port Louis', 'info@swanforlife.com', '(+230) 52376236');

-- --------------------------------------------------------

--
-- Structure de la table `currency`
--

DROP TABLE IF EXISTS `currency`;
CREATE TABLE IF NOT EXISTS `currency` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_ccy` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value_ccy` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forex_rate`
--

DROP TABLE IF EXISTS `forex_rate`;
CREATE TABLE IF NOT EXISTS `forex_rate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `forex_rate`
--

INSERT INTO `forex_rate` (`id`, `type`, `value`) VALUES
(1, 'USD', 45.7854),
(2, 'EUR', 50.7854),
(3, 'GBP', 58.7574);

-- --------------------------------------------------------

--
-- Structure de la table `fund`
--

DROP TABLE IF EXISTS `fund`;
CREATE TABLE IF NOT EXISTS `fund` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `reference` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fund_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `no_of_shares` float NOT NULL,
  `total_amount_ccy` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `total_amount_mur` float NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fund`
--

INSERT INTO `fund` (`id`, `user_id`, `reference`, `fund_name`, `no_of_shares`, `total_amount_ccy`, `total_amount_mur`, `created_at`) VALUES
(15, 5, 'SMMF192', 'Swan Money Market Fund(MUR)', 1345, 'MUR 88320.00', 88320, '2024-09-01 10:42:02'),
(16, 5, 'SIF191', 'Swan Income Fund', 10000, 'USD 10130.00', 455850, '2025-06-02 10:28:02'),
(19, 5, 'SM193', 'Swan Money - GBP', 1120, 'MUR 88320.00', 88320, '2025-08-07 10:28:02');

-- --------------------------------------------------------

--
-- Structure de la table `nav_funds`
--

DROP TABLE IF EXISTS `nav_funds`;
CREATE TABLE IF NOT EXISTS `nav_funds` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `currency` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` float NOT NULL,
  `fund_id` int NOT NULL,
  `nav_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `nav_funds`
--

INSERT INTO `nav_funds` (`id`, `code_name`, `currency`, `value`, `fund_id`, `nav_date`) VALUES
(1, 'SFE', 'MUR', 35.63, 15, '2024-05-18 13:31:18'),
(2, 'SEMEF', 'MUR', 11.11, 15, '2025-08-25 13:33:08'),
(3, 'SIF', 'USD', 10.13, 16, '2025-08-15 13:33:01'),
(4, 'MMF', 'USD', 102.13, 16, '2025-09-07 13:33:01'),
(5, 'SPE', 'USD', 11.49, 16, '2025-07-13 13:33:01'),
(6, 'MMF', 'MUR', 58.88, 15, '2024-05-25 13:33:01'),
(7, 'SFE', 'GBP', 12.1, 19, '2025-02-22 13:33:01'),
(8, 'SEF', 'MUR', 102.35, 15, '2024-05-01 00:00:00'),
(9, 'SSE', 'MUR', 104.2, 15, '2024-05-05 00:00:00'),
(10, 'FSE', 'MUR', 107.15, 15, '2024-05-22 13:31:44'),
(11, 'SSR', 'MUR', 110.5, 15, '2025-08-05 00:00:00'),
(12, 'FRE', 'MUR', 115.75, 15, '2025-09-05 00:00:00'),
(13, 'EEF', 'MUR', 112.3, 15, '2025-06-05 00:00:00'),
(14, 'SSF', 'USD', 50.1, 16, '2024-07-11 00:00:00'),
(16, 'FFS', 'USD', 65.25, 16, '2024-07-14 00:00:00'),
(17, 'SSD', 'USD', 20.4, 16, '2024-07-20 00:00:00'),
(18, 'RDS', 'USD', 78.1, 16, '2025-07-24 00:00:00'),
(19, 'DSS', 'USD', 55.2, 16, '2024-07-28 00:00:00'),
(20, 'FSE', 'GBP', 75.25, 19, '2024-11-05 00:00:00'),
(21, 'EFS', 'GBP', 34.1, 19, '2024-11-23 00:00:00'),
(22, 'FSE', 'GBP', 79.8, 19, '2024-11-11 00:00:00'),
(23, 'SSR', 'GBP', 82.5, 19, '2024-11-14 00:00:00'),
(24, 'TFE', 'GBP', 100.3, 19, '2025-03-01 00:00:00'),
(25, 'RFE', 'GBP', 88.9, 19, '2025-07-15 00:00:00'),
(26, 'SMMF192', 'MUR', 12.5, 15, '2025-09-01 00:00:00'),
(27, 'SMMF192', 'MUR', 47.75, 15, '2025-09-05 00:00:00'),
(28, 'SMMF192', 'MUR', 54.85, 15, '2025-09-07 00:00:00'),
(29, 'SMMF192', 'MUR', 90, 15, '2025-09-08 00:00:00'),
(30, 'SMMF', 'USD', 33.75, 16, '2025-09-05 00:00:00'),
(31, 'SMMF', 'USD', 12.85, 16, '2025-09-15 00:00:00'),
(32, 'SMMF', 'USD', 78, 16, '2025-09-25 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `swan_centre_contacts`
--

DROP TABLE IF EXISTS `swan_centre_contacts`;
CREATE TABLE IF NOT EXISTS `swan_centre_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `address` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `swan_centre_contacts`
--

INSERT INTO `swan_centre_contacts` (`id`, `address`, `email`, `phone`) VALUES
(1, '10 Intendance street, Port Louis', 'info@swanforlife.com', '(+230) 52376236');

-- --------------------------------------------------------

--
-- Structure de la table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE IF NOT EXISTS `transaction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fund_id` int NOT NULL,
  `type_id` int NOT NULL,
  `cn_number` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `no_of_units` float NOT NULL,
  `currency` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `net_amount_inv_redeemed` float NOT NULL,
  `transaction_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_transaction_type` (`type_id`),
  KEY `fk_transaction_type_fund` (`fund_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `transaction`
--

INSERT INTO `transaction` (`id`, `fund_id`, `type_id`, `cn_number`, `no_of_units`, `currency`, `net_amount_inv_redeemed`, `transaction_date`) VALUES
(1, 15, 1, 'CN097', 1000, 'MUR', 10130, '2025-09-01 11:06:12'),
(2, 16, 2, 'CN026', 11000, 'USD', 1123430, '2025-09-11 07:07:49'),
(3, 19, 3, 'CN023', 55000, 'USD', 557150, '2025-09-11 07:07:49');

-- --------------------------------------------------------

--
-- Structure de la table `transaction_type`
--

DROP TABLE IF EXISTS `transaction_type`;
CREATE TABLE IF NOT EXISTS `transaction_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `transaction_type`
--

INSERT INTO `transaction_type` (`id`, `code`, `name`) VALUES
(1, 'redemption', 'Redemption'),
(2, 'new_investment', 'New investment'),
(3, 'gifts', 'Gifts'),
(4, 'switch_out', 'Switch out'),
(5, 'switch_in', 'Switch in'),
(6, 'additional_investment', 'Additional investment');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
