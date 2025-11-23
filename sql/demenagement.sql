-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Lun 06 Octobre 2025 à 09:57
-- Version du serveur :  5.7.11
-- Version de PHP :  5.6.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `demenagement`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonce` (avec modifications)
--

DROP TABLE IF EXISTS `annonce`;
CREATE TABLE `annonce` (
  `an_id` int(11) NOT NULL,
  `an_id_client` int(11) NOT NULL,
  `an_titre` varchar(255) NOT NULL,
  `an_description` text,
  `an_date_demenagement` date NOT NULL,
  `an_heure_debut` time DEFAULT NULL,
  `an_ville_depart` varchar(100) DEFAULT NULL,
  `an_ville_arrivee` varchar(100) DEFAULT NULL,
  `an_type_logement_depart` enum('maison','appartement') DEFAULT NULL,
  `an_etage_depart` int(11) DEFAULT NULL,
  `an_ascenseur_depart` tinyint(1) DEFAULT NULL,
  `an_type_logement_arrivee` enum('maison','appartement') DEFAULT NULL,
  `an_etage_arrivee` int(11) DEFAULT NULL,
  `an_ascenseur_arrivee` tinyint(1) DEFAULT NULL,
  `an_volume` float DEFAULT NULL,
  `an_poids` float DEFAULT NULL,
  `an_nombre_demenageurs` int(11) NOT NULL DEFAULT '2',
  `an_statut` enum('ouverte','en cours','terminée') NOT NULL,
  `an_date_creation` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `demenagement_image` (nouvelle table)
--

DROP TABLE IF EXISTS `demenagement_image`;
CREATE TABLE `demenagement_image` (
  `img_id` int(11) NOT NULL,
  `img_id_annonce` int(11) NOT NULL,
  `img_nom_fichier` varchar(255) NOT NULL,
  `img_chemin` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `evaluation`
--

DROP TABLE IF EXISTS `evaluation`;
CREATE TABLE `evaluation` (
  `ev_id` int(11) NOT NULL,
  `ev_id_annonce` int(11) NOT NULL,
  `ev_id_demenageur` int(11) NOT NULL,
  `ev_id_client` int(11) NOT NULL,
  `ev_note` int(11) NOT NULL,
  `ev_commentaire` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `proposition`
--

DROP TABLE IF EXISTS `proposition`;
CREATE TABLE `proposition` (
  `pr_id` int(11) NOT NULL,
  `pr_id_annonce` int(11) NOT NULL,
  `pr_id_demenageur` int(11) NOT NULL,
  `pr_prix_propose` int(11) NOT NULL,
  `pr_date_proposition` date NOT NULL,
  `pr_statut` enum('en attente','acceptée','refusée') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE `utilisateur` (
  `ut_id` int(11) NOT NULL,
  `ut_nom` varchar(100) NOT NULL,
  `ut_prenom` varchar(100) NOT NULL,
  `ut_email` varchar(255) NOT NULL,
  `ut_mdp` varchar(255) NOT NULL,
  `ut_role` enum('client','déménageur','admin') NOT NULL,
  `ut_date_inscription` date NOT NULL,
  `ut_statut` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `question`
--


CREATE TABLE question (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_annonce INT(11) NOT NULL,
  id_demenageur INT(11) NOT NULL,
  question TEXT NOT NULL,
  date_question DATETIME DEFAULT CURRENT_TIMESTAMP,
  reponse TEXT DEFAULT NULL,
  date_reponse DATETIME DEFAULT NULL,
  FOREIGN KEY (id_annonce) REFERENCES annonce(an_id) ON DELETE CASCADE,
  FOREIGN KEY (id_demenageur) REFERENCES utilisateur(ut_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `annonce`
--
ALTER TABLE `annonce`
  ADD PRIMARY KEY (`an_id`),
  ADD KEY `an_id_client` (`an_id_client`);

--
-- Index pour la table `demenagement_image`
--
ALTER TABLE `demenagement_image`
  ADD PRIMARY KEY (`img_id`),
  ADD KEY `img_id_annonce` (`img_id_annonce`);

--
-- Index pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`ev_id`),
  ADD KEY `ev_id_annonce` (`ev_id_annonce`),
  ADD KEY `ev_id_demenageur` (`ev_id_demenageur`),
  ADD KEY `ev_id_client` (`ev_id_client`);

--
-- Index pour la table `proposition`
--
ALTER TABLE `proposition`
  ADD PRIMARY KEY (`pr_id`),
  ADD KEY `pr_id_annonce` (`pr_id_annonce`),
  ADD KEY `pr_id_demenageur` (`pr_id_demenageur`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`ut_id`),
  ADD UNIQUE KEY `ut_email` (`ut_email`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `annonce`
--
ALTER TABLE `annonce`
  MODIFY `an_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `demenagement_image`
--
ALTER TABLE `demenagement_image`
  MODIFY `img_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `evaluation`
--
ALTER TABLE `evaluation`
  MODIFY `ev_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `proposition`
--
ALTER TABLE `proposition`
  MODIFY `pr_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `ut_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `annonce`
--
ALTER TABLE `annonce`
  ADD CONSTRAINT `annonce_ibfk_1` FOREIGN KEY (`an_id_client`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `demenagement_image`
--
ALTER TABLE `demenagement_image`
  ADD CONSTRAINT `demenagement_image_ibfk_1` FOREIGN KEY (`img_id_annonce`) REFERENCES `annonce` (`an_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD CONSTRAINT `evaluation_ibfk_1` FOREIGN KEY (`ev_id_annonce`) REFERENCES `annonce` (`an_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_ibfk_2` FOREIGN KEY (`ev_id_demenageur`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_ibfk_3` FOREIGN KEY (`ev_id_client`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `proposition`
--
ALTER TABLE `proposition`
  ADD CONSTRAINT `proposition_ibfk_1` FOREIGN KEY (`pr_id_annonce`) REFERENCES `annonce` (`an_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `proposition_ibfk_2` FOREIGN KEY (`pr_id_demenageur`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;