-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 17, 2025 at 10:01 AM
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
-- Table structure for table `transaction`
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
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id`, `fund_id`, `type_id`, `cn_number`, `no_of_units`, `currency`, `net_amount_inv_redeemed`, `transaction_date`) VALUES
(1, 15, 1, 'CN097', 1000, 'MUR', 10130, '2025-09-01 11:06:12'),
(2, 16, 2, 'CN026', 11000, 'USD', 1123430, '2025-09-11 07:07:49'),
(3, 19, 3, 'CN023', 55000, 'USD', 557150, '2025-09-11 07:07:49'),
(4, 15, 1, 'CN097', 1000, 'MUR', 10130, '2024-01-29 11:06:12'),
(5, 16, 2, 'CN095', 10000, 'MUR', 356300, '2024-01-28 10:15:20'),
(6, 19, 3, 'CN051', 11000, 'USD', 1123430, '2024-01-20 09:30:00'),
(7, 16, 4, 'CN026', 100, 'MUR', 3563, '2024-01-20 14:25:00'),
(8, 15, 5, 'CN023', 55000, 'USD', 557150, '2024-01-20 17:00:00'),
(9, 19, 6, 'CN016', 27000, 'USD', 2757510, '2024-01-17 15:42:30'),
(10, 15, 3, 'CN077', 1000, 'USD', 10130, '2024-01-16 12:11:45'),
(11, 15, 4, 'CN025', 55000, 'USD', 557150, '2024-01-11 09:23:12'),
(12, 16, 6, 'CN006', 10000, 'MUR', 356300, '2024-01-10 10:05:00'),
(13, 19, 5, 'CN896', 11000, 'USD', 1123430, '2024-01-10 08:40:10'),
(14, 15, 1, 'CN201', 2000, 'USD', 20000, '2024-02-01 09:00:00'),
(15, 19, 2, 'CN202', 15000, 'USD', 1500000, '2024-02-02 11:10:20'),
(16, 16, 3, 'CN203', 1200, 'MUR', 400000, '2024-02-03 13:50:33'),
(17, 15, 4, 'CN204', 500, 'USD', 5000, '2024-02-04 15:30:10'),
(18, 15, 5, 'CN205', 8000, 'USD', 75000, '2024-02-05 16:05:55'),
(19, 19, 6, 'CN206', 25000, 'USD', 2600000, '2024-02-06 09:12:00'),
(20, 15, 3, 'CN207', 700, 'USD', 8000, '2024-02-07 08:00:00'),
(21, 16, 4, 'CN208', 900, 'MUR', 310000, '2024-02-08 12:15:00'),
(22, 15, 5, 'CN209', 6000, 'USD', 65000, '2024-02-09 14:30:00'),
(23, 19, 2, 'CN210', 14000, 'USD', 1400000, '2024-02-10 17:45:00'),
(24, 16, 6, 'CN211', 10000, 'MUR', 400000, '2024-02-11 09:05:05'),
(25, 15, 1, 'CN212', 3000, 'USD', 32000, '2024-02-12 10:20:10'),
(26, 19, 5, 'CN213', 5000, 'USD', 52000, '2024-02-13 11:35:15'),
(27, 16, 4, 'CN214', 200, 'MUR', 80000, '2024-02-14 13:50:00'),
(28, 15, 3, 'CN215', 400, 'USD', 4500, '2024-02-15 15:10:00'),
(29, 19, 2, 'CN216', 17000, 'USD', 1700000, '2024-02-16 16:25:00'),
(30, 16, 6, 'CN217', 15000, 'MUR', 600000, '2024-02-17 09:40:00'),
(31, 15, 5, 'CN218', 9000, 'USD', 95000, '2024-02-18 11:55:00'),
(32, 19, 1, 'CN219', 1000, 'USD', 10000, '2024-02-19 14:00:00'),
(33, 16, 2, 'CN220', 8000, 'MUR', 300000, '2024-02-20 10:30:00'),
(34, 15, 6, 'CN221', 7500, 'USD', 85000, '2024-02-21 12:15:00'),
(35, 19, 3, 'CN222', 6500, 'USD', 72000, '2024-02-22 09:45:00'),
(36, 16, 5, 'CN223', 5600, 'MUR', 220000, '2024-02-23 08:30:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
