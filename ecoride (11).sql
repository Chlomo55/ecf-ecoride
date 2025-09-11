-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 10 sep. 2025 à 15:02
-- Version du serveur : 8.0.31
-- Version de PHP : 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ecoride`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `nom` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_chauffeur` int NOT NULL,
  `id_passager` int NOT NULL,
  `note` float DEFAULT NULL,
  `avis` text,
  `probleme` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `covoiturage`
--

DROP TABLE IF EXISTS `covoiturage`;
CREATE TABLE IF NOT EXISTS `covoiturage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_chauffeur` int NOT NULL,
  `voiture_id` int NOT NULL,
  `depart` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `arrivee` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `heure_depart` datetime NOT NULL,
  `heure_arrivee` datetime NOT NULL,
  `prix` float NOT NULL,
  `place` int NOT NULL,
  `etat` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `passager`
--

DROP TABLE IF EXISTS `passager`;
CREATE TABLE IF NOT EXISTS `passager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `covoiturage_id` int DEFAULT NULL,
  `date_participation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `note_donne` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`covoiturage_id`),
  KEY `covoiturage_id` (`covoiturage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(55) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `pass` varchar(60) NOT NULL,
  `photo` longblob NOT NULL,
  `credit` int NOT NULL,
  `category` varchar(30) NOT NULL,
  `note` float NOT NULL,
  `role` varchar(30) NOT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `reset_code_expire` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `voiture`
--

DROP TABLE IF EXISTS `voiture`;
CREATE TABLE IF NOT EXISTS `voiture` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `immatriculation` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `marque` varchar(30) NOT NULL,
  `modele` varchar(30) NOT NULL,
  `couleur` varchar(30) NOT NULL,
  `energie` varchar(30) NOT NULL,
  `nb_place` int NOT NULL,
  `fumeur` tinyint(1) NOT NULL,
  `animaux` tinyint(1) NOT NULL,
  `preferences` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `immatriculation` (`immatriculation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
