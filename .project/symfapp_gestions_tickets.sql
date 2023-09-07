-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 07 sep. 2023 à 17:58
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
-- Base de données : `symfapp_gestions_tickets`
--

-- --------------------------------------------------------

--
-- Structure de la table `mail_configuration`
--

DROP TABLE IF EXISTS `mail_configuration`;
CREATE TABLE IF NOT EXISTS `mail_configuration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `password` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `smtp_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `smtp_port` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `smtp_tls` tinyint(1) NOT NULL,
  `cc_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `mail_configuration`
--

INSERT INTO `mail_configuration` (`id`, `login`, `password`, `smtp_address`, `smtp_port`, `smtp_tls`, `cc_address`, `subject`) VALUES
(1, 'stevehoareau18.dev@gmail.com', 'vQ3mm0PxgQTQtLg', 'smtp.freesmtpservers.com', '25', 1, 'steve.hoareau1@gmail.com', 'PLATEFORME TICKETING');

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `relance`
--

DROP TABLE IF EXISTS `relance`;
CREATE TABLE IF NOT EXISTS `relance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `treatment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `reason` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `reopen` tinyint(1) NOT NULL,
  `relance_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_50BBC126471C0366` (`treatment_id`),
  KEY `IDX_50BBC126A76ED395` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `relance`
--

INSERT INTO `relance` (`id`, `treatment_id`, `user_id`, `reason`, `email`, `reopen`, `relance_date`) VALUES
(3, 12, 1, 'ce n\'est pas la bonne version...', NULL, 1, '2023-09-04 13:05:57');

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

DROP TABLE IF EXISTS `service`;
CREATE TABLE IF NOT EXISTS `service` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `service`
--

INSERT INTO `service` (`id`, `name`) VALUES
(1, 'DÉVELOPPEMENT'),
(2, 'COMMERCIAL'),
(3, 'TECHNIQUE'),
(4, 'ADMINISTRATIF');

-- --------------------------------------------------------

--
-- Structure de la table `ticket`
--

DROP TABLE IF EXISTS `ticket`;
CREATE TABLE IF NOT EXISTS `ticket` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int DEFAULT NULL,
  `creator_id` int NOT NULL,
  `create_date` datetime NOT NULL,
  `problem` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `result` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `result_date` datetime DEFAULT NULL,
  `transfered` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_97A0ADA3ED5CA9E6` (`service_id`),
  KEY `IDX_97A0ADA361220EA6` (`creator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `ticket`
--

INSERT INTO `ticket` (`id`, `service_id`, `creator_id`, `create_date`, `problem`, `result`, `result_date`, `transfered`) VALUES
(3, 3, 2, '2023-08-31 10:58:08', 'Le client à besoin d\'une mise à jour office', 'mis à jour', '2023-09-04 13:08:53', 0),
(4, 3, 2, '2023-08-31 13:17:32', 'Le client à besoin d\'un devis', NULL, NULL, 0),
(5, 1, 5, '2023-09-04 15:08:12', 'Faire un point sur le projet Gestion d\'intervention', 'Module ajouté', '2023-09-07 11:43:22', 0),
(6, 1, 5, '2023-09-04 15:29:04', 'Le client a besoin d\'un devis pour la gestion de sa page facebook', NULL, NULL, 0),
(7, 1, 1, '2023-09-06 17:10:30', 'test', NULL, NULL, 0),
(8, 1, 3, '2023-09-07 11:16:12', 'Le client a besoin d\'une application de gestion de ticket', NULL, NULL, 0),
(9, 1, 3, '2023-09-07 11:16:43', 'Le client a besoin d\'une application de gestion d\'intervention', NULL, NULL, 0),
(10, 1, 3, '2023-09-07 11:17:15', 'Le client a besoin de renseignement pour le prix total du projet', NULL, NULL, 0),
(12, 1, 1, '2023-09-07 17:09:58', 'Le client a besoin d\'une page Facebook', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `treatment`
--

DROP TABLE IF EXISTS `treatment`;
CREATE TABLE IF NOT EXISTS `treatment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `caterer_id` int NOT NULL,
  `observations` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_98013C31700047D2` (`ticket_id`),
  KEY `IDX_98013C312CD89E08` (`caterer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `treatment`
--

INSERT INTO `treatment` (`id`, `ticket_id`, `caterer_id`, `observations`, `status`, `start_date`, `end_date`) VALUES
(9, 3, 2, 'Le client n\'est pas en contrat', 'RELAYÉ', '2023-08-31 14:54:27', '2023-08-31 14:55:41'),
(10, 3, 3, 'Le client n\'a pas le logiciel', 'RELAYÉ', '2023-08-31 14:58:35', '2023-08-31 15:02:18'),
(11, 3, 2, 'C\'est fait', 'RELAYÉ', '2023-08-31 15:04:04', '2023-08-31 15:57:10'),
(12, 3, 5, 'pris en charge', 'Fermé', '2023-08-31 15:57:21', '2023-08-31 16:12:25'),
(15, 4, 2, 'Le client a besoin d\'un devis', 'RELAYÉ', '2023-09-01 13:54:32', '2023-09-01 13:54:51'),
(16, 4, 3, 'Le client ...', 'TRANSFÉRÉ // EN ATTENTE', '2023-09-01 13:54:59', NULL),
(19, 3, 1, 'ce n\'est pas la bonne version...', 'RELAYÉ', '2023-09-04 13:05:57', '2023-09-04 13:08:32'),
(20, 3, 5, 'Mis à jour', 'Fermé', '2023-09-04 13:08:42', '2023-09-04 13:08:53'),
(21, 4, 1, 'r.a.s', 'EN COURS', '2023-09-04 16:51:24', NULL),
(22, 5, 4, 'Le client veut ajouter un module', 'Fermé', '2023-09-07 11:30:00', '2023-09-07 11:43:22'),
(23, 9, 4, 'Il n\'y a pas de cahier des charges', 'EN COURS', '2023-09-07 11:42:08', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `username` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `roles` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
  `password` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `firstname` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`),
  KEY `IDX_8D93D649ED5CA9E6` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `service_id`, `email`, `username`, `roles`, `password`, `name`, `firstname`, `active`) VALUES
(1, 4, 'steve.hoareau1@gmail.com', 'hsteve', '[\"ROLE_ADMIN\"]', '$2y$13$8nTibHJVfx/HMWieA2AOrOWiphxNFuh4WuFJLplQzaTJRaQjkXM/a', 'HOAREAU', 'Steve', 1),
(2, 1, 'john.doe@gmail.com', 'jdoe', '[\"ROLE_USER\"]', '$2y$13$mDIrZZjKFNIo3Wv.8qvhs.0A0dN4INnwRmmH9aGQC8HVKiW425BBO', 'John', 'Doe', 1),
(3, 2, 'maxime.clain@gmail.com', 'mclain', '[\"ROLE_USER\"]', '$2y$13$1JzmK1GRWESFaoCeh3wEhe1bTCPknyGzCXALoEZyvsA0We0LMnErq', 'Clain', 'Maxime', 1),
(4, 1, 'tony.ranguin@gmail.com', 'tranguin', '[\"ROLE_USER\"]', '$2y$13$/Gu77M3TuK53uoiBxHAjVOzRZLRnvq8GsLf7HfVYnRG92RXAKrT56', 'Ranguin', 'Tony', 1),
(5, 3, 'kotou.boina@gmail.com', 'kboina', '[\"ROLE_USER\"]', '$2y$13$ToxjxzFxUgkn0aI90BSQuuP0mQS0dfiohEpE3g0i6ASg4FIcTCPCO', 'Boina', 'Kotou', 1);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `relance`
--
ALTER TABLE `relance`
  ADD CONSTRAINT `FK_50BBC126471C0366` FOREIGN KEY (`treatment_id`) REFERENCES `treatment` (`id`),
  ADD CONSTRAINT `FK_50BBC126A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `FK_97A0ADA361220EA6` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_97A0ADA3ED5CA9E6` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`);

--
-- Contraintes pour la table `treatment`
--
ALTER TABLE `treatment`
  ADD CONSTRAINT `FK_98013C312CD89E08` FOREIGN KEY (`caterer_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_98013C31700047D2` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`id`);

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D649ED5CA9E6` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
