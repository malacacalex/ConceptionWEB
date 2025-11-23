-- Début du fichier SQL nettoyé
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

-- Structure de la table `annonce`
DROP TABLE IF EXISTS `annonce`;
CREATE TABLE `annonce` (
  `an_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `an_date_creation` date NOT NULL,
  PRIMARY KEY (`an_id`),
  KEY `an_id_client` (`an_id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Structure de la table `demenagement_image`
DROP TABLE IF EXISTS `demenagement_image`;
CREATE TABLE `demenagement_image` (
  `img_id` int(11) NOT NULL AUTO_INCREMENT,
  `img_id_annonce` int(11) NOT NULL,
  `img_nom_fichier` varchar(255) NOT NULL,
  `img_chemin` varchar(255) NOT NULL,
  PRIMARY KEY (`img_id`),
  KEY `img_id_annonce` (`img_id_annonce`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Structure de la table `evaluation`
DROP TABLE IF EXISTS `evaluation`;
CREATE TABLE `evaluation` (
  `ev_id` int(11) NOT NULL AUTO_INCREMENT,
  `ev_id_annonce` int(11) NOT NULL,
  `ev_id_demenageur` int(11) NOT NULL,
  `ev_id_client` int(11) NOT NULL,
  `ev_note` int(11) NOT NULL,
  `ev_commentaire` text,
  PRIMARY KEY (`ev_id`),
  KEY `ev_id_annonce` (`ev_id_annonce`),
  KEY `ev_id_demenageur` (`ev_id_demenageur`),
  KEY `ev_id_client` (`ev_id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Structure de la table `proposition`
DROP TABLE IF EXISTS `proposition`;
CREATE TABLE `proposition` (
  `pr_id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_id_annonce` int(11) NOT NULL,
  `pr_id_demenageur` int(11) NOT NULL,
  `pr_prix_propose` float NOT NULL,
  `pr_date_proposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pr_statut` enum('en attente','acceptée','refusée') NOT NULL,
  PRIMARY KEY (`pr_id`),
  KEY `pr_id_annonce` (`pr_id_annonce`),
  KEY `pr_id_demenageur` (`pr_id_demenageur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Structure de la table `utilisateur`
CREATE TABLE `utilisateur` (
  `ut_id` int(11) NOT NULL AUTO_INCREMENT,
  `ut_nom` varchar(100) NOT NULL,
  `ut_prenom` varchar(100) NOT NULL,
  `ut_email` varchar(191) NOT NULL,
  `ut_mdp` varchar(255) NOT NULL,
  `ut_role` enum('client','déménageur','admin') NOT NULL,
  `ut_date_inscription` date NOT NULL,
  `ut_statut` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ut_id`),
  UNIQUE KEY `ut_email` (`ut_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Structure de la table `question`
DROP TABLE IF EXISTS `question`;
CREATE TABLE `question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_annonce` int(11) NOT NULL,
  `id_demenageur` int(11) NOT NULL,
  `question` text NOT NULL,
  `date_question` datetime DEFAULT CURRENT_TIMESTAMP,
  `reponse` text DEFAULT NULL,
  `date_reponse` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_annonce` (`id_annonce`),
  KEY `id_demenageur` (`id_demenageur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contraintes pour les tables exportées
--

ALTER TABLE `annonce`
  ADD CONSTRAINT `annonce_ibfk_1` FOREIGN KEY (`an_id_client`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `demenagement_image`
  ADD CONSTRAINT `demenagement_image_ibfk_1` FOREIGN KEY (`img_id_annonce`) REFERENCES `annonce` (`an_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `evaluation`
  ADD CONSTRAINT `evaluation_ibfk_1` FOREIGN KEY (`ev_id_annonce`) REFERENCES `annonce` (`an_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_ibfk_2` FOREIGN KEY (`ev_id_demenageur`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluation_ibfk_3` FOREIGN KEY (`ev_id_client`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `proposition`
  ADD CONSTRAINT `proposition_ibfk_1` FOREIGN KEY (`pr_id_annonce`) REFERENCES `annonce` (`an_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `proposition_ibfk_2` FOREIGN KEY (`pr_id_demenageur`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`id_annonce`) REFERENCES `annonce` (`an_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_ibfk_2` FOREIGN KEY (`id_demenageur`) REFERENCES `utilisateur` (`ut_id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;