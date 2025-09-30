-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 30 sep. 2025 à 06:15
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
-- Structure de la table `document_fund`
--

DROP TABLE IF EXISTS `document_fund`;
CREATE TABLE IF NOT EXISTS `document_fund` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `path` varchar(250) NOT NULL,
  `created_at` datetime NOT NULL,
  `fund_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fund_id` (`fund_id`) USING BTREE,
  KEY `category_id` (`category_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `document_fund`
--

INSERT INTO `document_fund` (`id`, `name`, `path`, `created_at`, `fund_id`, `category_id`) VALUES
(1, 'Passeport', 'test_doc_statement.pdf', '2025-09-26 10:00:00', 15, 1),
(2, 'Curriculum Vitae', 'test_doc_statement.pdf', '2025-09-26 10:05:00', 16, 2),
(11, 'Curriculum Vitae', 'test_doc_statement.pdf', '2025-09-26 10:05:00', 16, 2),
(3, 'Lettre de motivation', 'test_doc_statement.pdf', '2025-09-26 10:07:00', 16, 3),
(4, 'Diplôme Licence', 'test_doc_statement.pdf', '2025-09-26 11:00:00', 19, 4),
(5, 'Contrat de travail', 'test_doc_statement.pdf', '2025-09-26 11:15:00', 15, 4),
(6, 'Facture électricité', 'test_doc_statement.pdf', '2025-09-26 12:00:00', 15, 1),
(7, 'Relevé bancaire', 'test_doc_statement.pdf', '2025-09-26 12:10:00', 16, 4),
(8, 'Photo identité', 'test_doc_statement.pdf', '2025-09-26 12:20:00', 19, 3),
(9, 'Certificat médical', 'test_doc_statement.pdf', '2025-09-26 13:00:00', 15, 2),
(10, 'Avis d’imposition', 'test_doc_statement.pdf', '2025-09-26 13:30:00', 16, 2);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
