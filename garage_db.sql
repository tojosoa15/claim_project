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
-- Base de données : `garage_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `additional_labour_details`
--

DROP TABLE IF EXISTS `additional_labour_details`;
CREATE TABLE IF NOT EXISTS `additional_labour_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estimate_of_repairs_id` int NOT NULL,
  `eor_or_surveyor` varchar(10) DEFAULT NULL,
  `painting_cost` decimal(10,2) DEFAULT NULL,
  `painting_materiels` decimal(10,2) DEFAULT NULL,
  `sundries` decimal(10,2) DEFAULT NULL,
  `num_of_repaire_days` int DEFAULT NULL,
  `discount_add_labour` decimal(5,2) DEFAULT NULL,
  `vat` decimal(5,2) DEFAULT NULL,
  `add_labour_total` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ;

--
-- Déchargement des données de la table `additional_labour_details`
--

INSERT INTO `additional_labour_details` (`id`, `estimate_of_repairs_id`, `eor_or_surveyor`, `painting_cost`, `painting_materiels`, `sundries`, `num_of_repaire_days`, `discount_add_labour`, `vat`, `add_labour_total`, `created_at`, `updated_at`) VALUES
(1, 1, 'eor', 1200.00, 300.00, 50.00, 3, 100.00, 15.00, 1557.50, '2025-08-21 09:50:40', '2025-08-21 09:50:40'),
(2, 2, 'surveyor', 800.00, 200.00, 30.00, 2, 50.00, 0.00, 980.00, '2025-08-21 09:50:40', '2025-08-21 09:50:40'),
(3, 3, 'eor', 500.00, 100.00, 20.00, 1, 0.00, 15.00, 690.50, '2025-08-21 09:50:40', '2025-08-21 09:50:40'),
(4, 1, 'surveyor', 1500.00, 400.00, 70.00, 4, 200.00, 15.00, 2062.50, '2025-08-21 09:50:40', '2025-08-21 09:50:40'),
(5, 4, 'surveyor', 1500.00, 400.00, 70.00, 4, 200.00, 15.00, 2062.50, '2025-08-21 09:50:40', '2025-08-21 09:50:40'),
(6, 5, 'surveyor', 1500.00, 400.00, 70.00, 4, 200.00, 15.00, 2062.50, '2025-08-21 09:50:40', '2025-08-21 09:50:40');

-- --------------------------------------------------------

--
-- Structure de la table `estimate_of_repair`
--

DROP TABLE IF EXISTS `estimate_of_repair`;
CREATE TABLE IF NOT EXISTS `estimate_of_repair` (
  `id` int NOT NULL AUTO_INCREMENT,
  `claim_number` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `estimate_of_repair`
--

INSERT INTO `estimate_of_repair` (`id`, `claim_number`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'M0119926', 'Remplacement pare-chocs avant et peinture', '2025-08-01 06:30:00', '2025-08-01 06:30:00'),
(2, 'M0119928', 'Changement des phares et réglage capot', '2025-08-05 08:15:00', '2025-08-05 08:15:00'),
(3, 'M0119929', 'Réparation portière gauche + remplacement vitre', '2025-08-10 11:45:00', '2025-08-10 11:45:00'),
(4, 'M0119934', 'Peinture capot + débosselage léger', '2025-08-15 07:20:00', '2025-08-15 07:20:00'),
(5, 'M0119935', 'Changement pare-brise et révision mécanique', '2025-08-18 13:00:00', '2025-08-18 13:00:00'),
(6, 'M0119922', NULL, '2025-08-26 20:53:45', '2025-08-26 20:53:45'),
(7, 'M0119924', NULL, '2025-08-26 20:59:06', '2025-08-26 20:59:06');

-- --------------------------------------------------------

--
-- Structure de la table `labour_details`
--

DROP TABLE IF EXISTS `labour_details`;
CREATE TABLE IF NOT EXISTS `labour_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `part_detail_id` int NOT NULL,
  `eor_or_surveyor` enum('EOR','SURVEYOR') COLLATE utf8mb4_general_ci NOT NULL,
  `activity` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `number_of_hours` decimal(5,2) DEFAULT '0.00',
  `discount_labour` decimal(10,2) DEFAULT '0.00',
  `vat_labour` enum('0','15') COLLATE utf8mb4_general_ci DEFAULT '15',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hourly_cost_labour` decimal(10,2) DEFAULT '0.00',
  `labour_total` decimal(12,2) GENERATED ALWAYS AS ((((`number_of_hours` * `hourly_cost_labour`) - `discount_labour`) * (1 + (cast(`vat_labour` as decimal(10,0)) / 100)))) STORED,
  PRIMARY KEY (`id`),
  KEY `fk_labour_part` (`part_detail_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `labour_details`
--

INSERT INTO `labour_details` (`id`, `part_detail_id`, `eor_or_surveyor`, `activity`, `number_of_hours`, `discount_labour`, `vat_labour`, `created_at`, `updated_at`, `hourly_cost_labour`) VALUES
(1, 1, 'EOR', 'Installation', 3.00, 20.00, '15', '2025-08-01 07:00:00', '2025-08-27 08:01:01', 100.00),
(2, 2, 'SURVEYOR', 'Installation', 2.00, 0.00, '15', '2025-08-01 08:00:00', '2025-08-27 08:00:49', 100.00),
(3, 3, 'EOR', 'Maintenance', 1.50, 20.00, '15', '2025-08-05 09:00:00', '2025-08-27 08:01:48', 100.00),
(4, 4, 'EOR', 'Maintenance', 1.50, 20.00, '15', '2025-08-05 09:30:00', '2025-08-27 07:57:52', 100.00),
(5, 5, 'SURVEYOR', 'Installation', 4.00, 30.00, '15', '2025-08-10 12:20:00', '2025-08-27 08:39:34', 200.00),
(6, 6, 'EOR', 'Installation', 2.00, 0.00, '15', '2025-08-10 12:30:00', '2025-08-27 08:39:23', 140.00),
(7, 7, 'SURVEYOR', 'Repair', 3.50, 20.00, '15', '2025-08-15 08:00:00', '2025-08-27 08:39:42', 0.00),
(8, 8, 'EOR', 'Repair', 2.50, 10.00, '15', '2025-08-15 08:20:00', '2025-08-27 08:39:49', 0.00),
(9, 9, 'EOR', 'Installation', 3.00, 250.00, '15', '2025-08-18 14:00:00', '2025-08-27 08:39:54', 0.00),
(10, 10, 'SURVEYOR', 'Installation', 1.00, 0.00, '15', '2025-08-18 14:10:00', '2025-08-27 08:39:59', 0.00),
(11, 11, 'EOR', 'Maintenance', 2.50, 0.00, '', '2025-08-26 08:00:00', '2025-08-27 08:40:08', 45.00),
(12, 12, 'SURVEYOR', 'Maintenance', 1.50, 5.00, '', '2025-08-26 08:05:00', '2025-08-27 08:40:14', 40.00);

-- --------------------------------------------------------

--
-- Structure de la table `part_details`
--

DROP TABLE IF EXISTS `part_details`;
CREATE TABLE IF NOT EXISTS `part_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estimate_of_repair_id` int NOT NULL,
  `part_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `part_number` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT '0.00',
  `quantity` int DEFAULT '1',
  `discount_part` decimal(10,2) DEFAULT '0.00',
  `vat_part` enum('0','15') COLLATE utf8mb4_general_ci DEFAULT '15',
  `part_total` decimal(12,2) GENERATED ALWAYS AS ((((`unit_price` * `quantity`) - `discount_part`) * (1 + (cast(`vat_part` as decimal(10,0)) / 100)))) STORED,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `quality` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Standard',
  `cost_part` float NOT NULL,
  `supplier` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_part_estimate` (`estimate_of_repair_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `part_details`
--

INSERT INTO `part_details` (`id`, `estimate_of_repair_id`, `part_name`, `part_number`, `unit_price`, `quantity`, `discount_part`, `vat_part`, `created_at`, `updated_at`, `quality`, `cost_part`, `supplier`) VALUES
(1, 1, 'Pare-chocs avant', 'PC-TOY-001', 4500.00, 1, 500.00, '15', '2025-08-01 06:45:00', '2025-08-21 11:57:19', 'Premium', 3299, 'Garage TGE'),
(2, 1, 'Peinture métallisée', 'PNT-TOY-045', 1200.00, 1, 0.00, '15', '2025-08-01 06:46:00', '2025-08-21 12:00:43', 'Premium', 237, 'Garage tieu'),
(3, 2, 'Phare gauche', 'PHR-HON-210', 3200.00, 1, 20.00, '15', '2025-08-05 08:20:00', '2025-08-27 08:41:24', 'Standard', 3400, 'Garage TGE'),
(4, 2, 'Phare droit', 'PHR-HON-211', 3200.00, 1, 20.00, '15', '2025-08-05 08:21:00', '2025-08-27 08:41:21', 'Standard', 2800, 'Garage TGE'),
(5, 3, 'Portière gauche', 'PRT-NIS-334', 9500.00, 1, 500.00, '15', '2025-08-10 11:50:00', '2025-08-27 08:40:36', 'Economy', 289, ''),
(6, 3, 'Vitre portière gauche', 'VTR-NIS-335', 2800.00, 1, 0.00, '15', '2025-08-10 11:52:00', '2025-08-27 08:40:41', 'Economy', 2893, ''),
(7, 4, 'Capot', 'CPT-MAZ-112', 5000.00, 1, 30.00, '15', '2025-08-15 07:25:00', '2025-08-27 08:41:18', 'Premium', 3000, ''),
(8, 4, 'Peinture capot', 'PNT-MAZ-113', 1200.00, 1, 0.00, '15', '2025-08-15 07:26:00', '2025-08-27 08:40:48', 'Premium', 2000, ''),
(9, 5, 'Pare-brise', 'PBS-BMW-900', 13500.00, 1, 10.00, '15', '2025-08-18 13:05:00', '2025-08-27 08:41:14', 'Standard', 1000, ''),
(10, 5, 'Balais d\'essuie-glace', 'ESS-BMW-901', 1800.00, 1, 10.00, '15', '2025-08-18 13:06:00', '2025-08-27 08:41:11', 'Standard', 12000, ''),
(11, 6, 'Front Bumper', 'FB-2025-01', 150.00, 1, 10.00, '15', '2025-08-26 07:00:00', '2025-08-27 08:41:45', 'OEM', 150, 'Toyota Supplier Ltd'),
(12, 7, 'Left Headlight', 'HL-2025-09', 90.00, 2, 10.00, '15', '2025-08-26 07:05:00', '2025-08-27 08:41:50', 'Aftermarket', 180, 'AutoParts Co.');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
