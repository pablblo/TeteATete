-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 22 jan. 2025 à 20:06
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bdd_tat`
--

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

CREATE TABLE `cours` (
  `idCours` int(11) NOT NULL AUTO_INCREMENT,
  `Titre` varchar(45) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Heure` time DEFAULT NULL,
  `Taille` int(11) DEFAULT NULL,
  `Places_restants_Tuteur` int(11) DEFAULT NULL,
  `Places_restants_Eleve` int(11) DEFAULT NULL,
  PRIMARY KEY (`idCours`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluation`
--

CREATE TABLE `evaluation` (
  `idEvaluation` int(11) NOT NULL AUTO_INCREMENT,
  `Tuteur_ou_Eleve` tinyint(4) DEFAULT NULL,
  `Note` tinyint(4) DEFAULT NULL,
  `Commentaire` varchar(200) DEFAULT NULL,
  `idUserAuteur` int(11) NOT NULL,
  `idUserReceveur` int(11) NOT NULL,
  `idCours` int(11) DEFAULT NULL,
  PRIMARY KEY (`idEvaluation`),
  KEY `fk_Evaluation_User1_idx` (`idUserReceveur`),
  KEY `fk_UserAuteur` (`idUserAuteur`),
  CONSTRAINT `fk_UserAuteur` FOREIGN KEY (`idUserAuteur`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_UserReceveur` FOREIGN KEY (`idUserReceveur`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forum`
--

CREATE TABLE `forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `idInscription` int(11) NOT NULL AUTO_INCREMENT,
  `idCours` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `Date_Inscription` datetime DEFAULT current_timestamp(),
  `role` enum('eleve','instructeur') NOT NULL,
  PRIMARY KEY (`idInscription`),
  KEY `idCours` (`idCours`),
  KEY `idUser` (`idUser`),
  CONSTRAINT `inscription_ibfk_1` FOREIGN KEY (`idCours`) REFERENCES `cours` (`idCours`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inscription_ibfk_2` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `idMessage` int(11) NOT NULL AUTO_INCREMENT,
  `idCours` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `message` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`idMessage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message_contact`
--

CREATE TABLE `message_contact` (
  `idMessage_Contact` int(11) NOT NULL AUTO_INCREMENT,
  `Mail` varchar(100) DEFAULT NULL,
  `Message` varchar(250) DEFAULT NULL,
  `idUserAuteur` int(11) NOT NULL,
  `idUserReceveur` int(11) NOT NULL,
  PRIMARY KEY (`idMessage_Contact`),
  KEY `fk_idUserAuteur_idx` (`idUserAuteur`),
  KEY `fk_idUserReceveur_idx` (`idUserReceveur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `idUser` int(11) NOT NULL AUTO_INCREMENT,
  `Nom` varchar(45) DEFAULT NULL,
  `Prenom` varchar(45) DEFAULT NULL,
  `Mail` varchar(45) DEFAULT NULL,
  `Mot_de_passe` varchar(255) NOT NULL,
  `Classe` varchar(45) DEFAULT NULL,
  `Photo_de_Profil` blob DEFAULT NULL,
  `Admin` tinyint(4) DEFAULT 0,
  `reset_token` varchar(100) DEFAULT NULL,
  `Bio` varchar(255) DEFAULT NULL,
  `nbAvertissements` int(11) DEFAULT 0,
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `Mail` (`Mail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_cours`
--

CREATE TABLE `user_cours` (
  `idUser_Cours` int(11) NOT NULL AUTO_INCREMENT,
  `Tuteur_ou_Eleve` tinyint(4) DEFAULT NULL,
  `idUser` int(11) NOT NULL,
  `idCours` int(11) NOT NULL,
  PRIMARY KEY (`idUser_Cours`),
  KEY `fk_idUser_idx` (`idUser`),
  KEY `fk_idCours_idx` (`idCours`),
  CONSTRAINT `fk_idCours` FOREIGN KEY (`idCours`) REFERENCES `cours` (`idCours`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_idUser` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;