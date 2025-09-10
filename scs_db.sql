-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 10, 2025 at 06:33 AM
-- Server version: 9.1.0
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `scs_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
CREATE TABLE IF NOT EXISTS `currency` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_ccy` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `value_ccy` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forex_rate`
--

DROP TABLE IF EXISTS `forex_rate`;
CREATE TABLE IF NOT EXISTS `forex_rate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forex_rate`
--

INSERT INTO `forex_rate` (`id`, `type`, `value`) VALUES
(1, 'USD', 45.7854),
(2, 'EUR', 50.7854),
(3, 'GBP', 58.7574);

-- --------------------------------------------------------

--
-- Table structure for table `fund`
--

DROP TABLE IF EXISTS `fund`;
CREATE TABLE IF NOT EXISTS `fund` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `nav_id` int NOT NULL,
  `reference` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `fund_name` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `no_of_shares` float NOT NULL,
  `nav` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `total_amount_ccy` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `total_amount_mur` float NOT NULL,
  `fund_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_fund_nav` (`nav_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fund`
--

INSERT INTO `fund` (`id`, `user_id`, `nav_id`, `reference`, `fund_name`, `no_of_shares`, `nav`, `total_amount_ccy`, `total_amount_mur`, `fund_date`) VALUES
(15, 5, 6, 'SMMF192', 'Swan Money Market Fund(MUR)', 1345, 'MUR 58.50', 'MUR 88320.00', 88320, '2024-09-01 10:42:02'),
(16, 5, 5, 'SIF191', 'Swan Income Fund', 10000, 'USD 58.50', 'USD 10130.00', 455850, '2025-06-02 10:28:02'),
(19, 5, 7, 'SM193', 'Swan Money - GBP', 1120, 'MUR 12.1', 'MUR 88320.00', 88320, '2025-08-07 10:28:02');

-- --------------------------------------------------------

--
-- Table structure for table `nav_funds`
--

DROP TABLE IF EXISTS `nav_funds`;
CREATE TABLE IF NOT EXISTS `nav_funds` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_name` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `type_nav` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nav_funds`
--

INSERT INTO `nav_funds` (`id`, `code_name`, `type_nav`, `value`) VALUES
(1, 'SFE', 'MUR', 35.63),
(2, 'SEMEF', 'MUR', 11.11),
(3, 'SIF', 'USD', 10.13),
(4, 'MMF', 'USD', 102.13),
(5, 'SPE', 'USD', 11.49),
(6, 'MMF', 'MUR', 58.88),
(7, 'SFE', 'GBP', 12.1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
