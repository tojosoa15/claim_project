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
-- Structure de la table `document_category`
--

DROP TABLE IF EXISTS `document_category`;
CREATE TABLE IF NOT EXISTS `document_category` (
  `id` int NOT NULL,
  `category_code` varchar(250) NOT NULL,
  `category_name` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `document_category`
--

INSERT INTO `document_category` (`id`, `category_code`, `category_name`) VALUES
(1, 'statements', 'statements'),
(2, 'factssheets', 'factssheets'),
(3, 'contract-notes', 'Lettre de motivation'),
(4, 'devidend-notices', 'devidend-notices');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
