-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 02 déc. 2025 à 16:55
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
-- Base de données : `zoo`
--

-- --------------------------------------------------------

--
-- Structure de la table `animaux`
--

CREATE TABLE `animaux` (
  `id` int(11) NOT NULL,
  `Nom` varchar(30) NOT NULL,
  `Espece` varchar(30) NOT NULL,
  `date_naissance` text NOT NULL,
  `Sexe` varchar(30) NOT NULL,
  `Enclos` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `animaux`
--

INSERT INTO `animaux` (`id`, `Nom`, `Espece`, `date_naissance`, `Sexe`, `Enclos`) VALUES
(17, 'Simba', 'Lion d\'Afrique', '2023-03-09', 'Mâle', 'Zone Savane 1'),
(18, 'Pumba', 'Lion d\'Afrique', '2025-12-01', 'Mâle', 'Zone Savane 1'),
(19, 'Sierra', 'Tigre du Bengale', '2022-07-14', 'Femelle', 'Zone Savane 1'),
(20, 'Zoro', 'Zèbre des plaines', '2025-08-28', 'Mâle', 'Zone Savane 2'),
(21, 'Gary', 'Girafe réticulée', '2025-01-09', 'Femelle', 'Zone Savane 2'),
(22, 'Croco', 'Crocodile du Nil', '2025-02-13', 'Femelle', 'Le Nil'),
(23, 'Craco', 'Crocodile du Nil', '2025-04-18', 'Mâle', 'Le Nil'),
(24, 'Bimbo', 'Hippopotame', '2025-06-11', 'Femelle', 'Bassin rocheux'),
(25, 'Octo', 'Otarie de Californie', '2025-10-08', 'Mâle', 'Bassin rocheux'),
(26, 'Shark', 'Requin-zèbre', '2025-04-18', 'Mâle', 'Grand Bassin 1'),
(27, 'Shakira', 'Requin-zèbre', '2025-08-09', 'Femelle', 'Grand Bassin 1'),
(28, 'Mache', 'Manchot royal', '2025-06-12', 'Mâle', 'Grand Bassin 2'),
(29, 'Macha', 'Manchot royal', '2025-11-05', 'Femelle', 'Grand Bassin 2');

-- --------------------------------------------------------

--
-- Structure de la table `enclos`
--

CREATE TABLE `enclos` (
  `Nom` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `enclos`
--

INSERT INTO `enclos` (`Nom`) VALUES
('Bassin rocheux'),
('Grand Bassin 1'),
('Grand Bassin 2'),
('Le Nil'),
('Zone Savane 1'),
('Zone Savane 2');

-- --------------------------------------------------------

--
-- Structure de la table `especes`
--

CREATE TABLE `especes` (
  `id` int(11) NOT NULL,
  `nom_race` varchar(30) NOT NULL,
  `type_nourriture` varchar(30) NOT NULL,
  `duree_vie` int(11) NOT NULL,
  `animal_aquatique` varchar(30) NOT NULL,
  `complementaire` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `especes`
--

INSERT INTO `especes` (`id`, `nom_race`, `type_nourriture`, `duree_vie`, `animal_aquatique`, `complementaire`) VALUES
(40, 'Lion d\'Afrique', 'charognards', 10, 'Non', ''),
(41, 'Tigre du Bengale', 'charognards', 12, 'Non', ''),
(42, 'Girafe réticulée', 'herbivores', 21, 'Non', ''),
(43, 'Zèbre des plaines', 'herbivores', 16, 'Non', ''),
(44, 'Hippopotame', 'piscivores', 14, 'Oui', ''),
(45, 'Crocodile du Nil', 'charognards', 7, 'Oui', ''),
(46, 'Otarie de Californie', 'nectarivores', 13, 'Oui', ''),
(47, 'Requin-zèbre', 'charognards', 5, 'Oui', ''),
(48, 'Manchot royal', 'frugivores', 15, 'Oui', '');

-- --------------------------------------------------------

--
-- Structure de la table `logs_activite`
--

CREATE TABLE `logs_activite` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `date_action` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `logs_activite`
--

INSERT INTO `logs_activite` (`id`, `user_id`, `action`, `module`, `details`, `ip_address`, `date_action`) VALUES
(1, 19, 'Création de réservation', 'reservations', 'Réservation #2 pour le 2025-12-03', '::1', '2025-12-01 15:09:39'),
(2, 18, 'Création de compte', 'utilisateurs', 'Création du compte Chef_Equipe pour Equipe Chef (ID: 20)', '::1', '2025-12-01 15:25:50'),
(3, 18, 'Création de compte', 'utilisateurs', 'Création du compte Veterinaire pour Chef Veto (ID: 21)', '::1', '2025-12-01 15:43:06'),
(4, 18, 'Création de compte', 'utilisateurs', 'Création du compte Employe pour pro Employe (ID: 22)', '::1', '2025-12-01 15:43:50'),
(5, 18, 'Création de compte', 'utilisateurs', 'Création du compte Benevole pour Vole Bene (ID: 23)', '::1', '2025-12-01 15:44:21'),
(6, 19, 'Création de réservation', 'reservations', 'Réservation #3 pour le 2025-12-03', '::1', '2025-12-02 00:51:10'),
(7, 20, 'Création de compte', 'utilisateurs', 'Création du compte Benevole pour Anatole Pierrot (ID: 24)', '::1', '2025-12-02 10:48:21'),
(8, 18, 'Ajout animal', 'Animaux', 'Animal \'Dromadere\' ajouté', '::1', '2025-12-02 10:58:18'),
(9, 18, 'Ajout animal', 'Animaux', 'Animal \'Pumba\' ajouté', '::1', '2025-12-02 14:15:59'),
(10, 18, 'Suppression animal', 'Animaux', 'Animal \'Pumba\' (ID: 16) supprimé', '::1', '2025-12-02 14:16:07'),
(11, 18, 'Suppression enclos', 'Enclos', 'Enclos \'Test\' supprimé', '::1', '2025-12-02 14:22:57'),
(12, 18, 'Suppression animal', 'Animaux', 'Animal \'Pumba\' (ID: 14) supprimé', '::1', '2025-12-02 14:24:41'),
(13, 18, 'Suppression animal', 'Animaux', 'Animal \'Simba\' (ID: 13) supprimé', '::1', '2025-12-02 14:24:44'),
(14, 18, 'Suppression animal', 'Animaux', 'Animal \'Simba\' (ID: 12) supprimé', '::1', '2025-12-02 14:24:46'),
(15, 18, 'Suppression animal', 'Animaux', 'Animal \'PatPat\' (ID: 11) supprimé', '::1', '2025-12-02 14:24:48'),
(16, 18, 'Suppression animal', 'Animaux', 'Animal \'Ours\' (ID: 10) supprimé', '::1', '2025-12-02 14:24:49'),
(17, 18, 'Suppression animal', 'Animaux', 'Animal \'Singe\' (ID: 9) supprimé', '::1', '2025-12-02 14:24:51'),
(18, 18, 'Suppression animal', 'Animaux', 'Animal \'Chien\' (ID: 8) supprimé', '::1', '2025-12-02 14:24:52'),
(19, 18, 'Suppression animal', 'Animaux', 'Animal \'Chat\' (ID: 7) supprimé', '::1', '2025-12-02 14:24:54'),
(20, 18, 'Suppression enclos', 'Enclos', 'Enclos \'Enclos 1\' supprimé', '::1', '2025-12-02 14:25:34'),
(21, 18, 'Suppression enclos', 'Enclos', 'Enclos \'Enclos 2\' supprimé', '::1', '2025-12-02 14:25:36'),
(22, 18, 'Suppression enclos', 'Enclos', 'Enclos \'Enclos 3\' supprimé', '::1', '2025-12-02 14:25:38'),
(23, 18, 'Suppression enclos', 'Enclos', 'Enclos \'Enclos 4\' supprimé', '::1', '2025-12-02 14:25:39'),
(24, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Lion d\'Afrique\' ajoutée', '::1', '2025-12-02 14:38:00'),
(25, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Tigre du Bengale\' ajoutée', '::1', '2025-12-02 14:38:39'),
(26, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Girafe réticulée\' ajoutée', '::1', '2025-12-02 14:39:05'),
(27, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Zèbre des plaines\' ajoutée', '::1', '2025-12-02 14:39:31'),
(28, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Hippopotame\' ajoutée', '::1', '2025-12-02 14:39:57'),
(29, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Crocodile du Nil\' ajoutée', '::1', '2025-12-02 14:40:21'),
(30, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Otarie de Californie\' ajoutée', '::1', '2025-12-02 14:40:42'),
(31, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Requin-zèbre\' ajoutée', '::1', '2025-12-02 14:41:01'),
(32, 18, 'Ajout espèce', 'Espèces', 'Espèce \'Manchot royal\' ajoutée', '::1', '2025-12-02 14:41:22'),
(33, 18, 'Ajout animal', 'Animaux', 'Animal \'Simba\' ajouté', '::1', '2025-12-02 14:42:08'),
(34, 18, 'Ajout animal', 'Animaux', 'Animal \'Pumba\' ajouté', '::1', '2025-12-02 14:42:33'),
(35, 18, 'Ajout animal', 'Animaux', 'Animal \'Sierra\' ajouté', '::1', '2025-12-02 14:42:56'),
(36, 18, 'Ajout animal', 'Animaux', 'Animal \'Zoro\' ajouté', '::1', '2025-12-02 14:43:34'),
(37, 18, 'Ajout animal', 'Animaux', 'Animal \'Gary\' ajouté', '::1', '2025-12-02 14:44:00'),
(38, 18, 'Ajout animal', 'Animaux', 'Animal \'Croco\' ajouté', '::1', '2025-12-02 14:44:52'),
(39, 18, 'Ajout animal', 'Animaux', 'Animal \'Craco\' ajouté', '::1', '2025-12-02 14:45:29'),
(40, 18, 'Ajout animal', 'Animaux', 'Animal \'Bimbo\' ajouté', '::1', '2025-12-02 14:46:13'),
(41, 18, 'Ajout animal', 'Animaux', 'Animal \'Octo\' ajouté', '::1', '2025-12-02 14:46:31'),
(42, 18, 'Ajout animal', 'Animaux', 'Animal \'Shark\' ajouté', '::1', '2025-12-02 14:46:50'),
(43, 18, 'Ajout animal', 'Animaux', 'Animal \'Shakira\' ajouté', '::1', '2025-12-02 14:47:59'),
(44, 18, 'Ajout animal', 'Animaux', 'Animal \'Mache\' ajouté', '::1', '2025-12-02 14:48:27'),
(45, 18, 'Ajout animal', 'Animaux', 'Animal \'Macha\' ajouté', '::1', '2025-12-02 14:48:48'),
(46, 19, 'Annulation réservation', 'Réservations', 'Réservation #2 annulée par le client', '::1', '2025-12-02 14:58:29');

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `role` enum('Client','Benevole','Employe','Veterinaire','Chef_Equipe','Directeur') NOT NULL,
  `module` varchar(50) NOT NULL,
  `peut_lire` tinyint(1) DEFAULT 0,
  `peut_creer` tinyint(1) DEFAULT 0,
  `peut_modifier` tinyint(1) DEFAULT 0,
  `peut_supprimer` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `role`, `module`, `peut_lire`, `peut_creer`, `peut_modifier`, `peut_supprimer`) VALUES
(1, 'Directeur', 'animaux', 1, 1, 1, 1),
(2, 'Directeur', 'especes', 1, 1, 1, 1),
(3, 'Directeur', 'employes', 1, 1, 1, 1),
(4, 'Directeur', 'utilisateurs', 1, 1, 1, 1),
(5, 'Directeur', 'reservations', 1, 1, 1, 1),
(6, 'Directeur', 'enclos', 1, 1, 1, 1),
(7, 'Chef_Equipe', 'animaux', 1, 1, 1, 1),
(8, 'Chef_Equipe', 'especes', 1, 1, 1, 1),
(9, 'Chef_Equipe', 'employes', 1, 1, 1, 0),
(10, 'Chef_Equipe', 'utilisateurs', 1, 1, 1, 0),
(11, 'Chef_Equipe', 'reservations', 1, 0, 1, 0),
(12, 'Chef_Equipe', 'enclos', 1, 1, 1, 1),
(13, 'Veterinaire', 'animaux', 1, 1, 1, 1),
(14, 'Veterinaire', 'especes', 1, 1, 1, 1),
(15, 'Veterinaire', 'employes', 1, 0, 0, 0),
(16, 'Veterinaire', 'utilisateurs', 0, 0, 0, 0),
(17, 'Veterinaire', 'reservations', 1, 0, 0, 0),
(18, 'Veterinaire', 'enclos', 1, 0, 0, 0),
(19, 'Employe', 'animaux', 1, 1, 1, 1),
(20, 'Employe', 'especes', 1, 1, 1, 1),
(21, 'Employe', 'employes', 1, 0, 0, 0),
(22, 'Employe', 'utilisateurs', 0, 0, 0, 0),
(23, 'Employe', 'reservations', 1, 0, 0, 0),
(24, 'Employe', 'enclos', 1, 0, 0, 0),
(25, 'Benevole', 'animaux', 1, 0, 0, 0),
(26, 'Benevole', 'especes', 1, 0, 0, 0),
(27, 'Benevole', 'employes', 1, 0, 0, 0),
(28, 'Benevole', 'utilisateurs', 0, 0, 0, 0),
(29, 'Benevole', 'reservations', 0, 0, 0, 0),
(30, 'Benevole', 'enclos', 1, 0, 0, 0),
(31, 'Client', 'reservations', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `places_disponibles`
--

CREATE TABLE `places_disponibles` (
  `id` int(11) NOT NULL,
  `date_visite` date NOT NULL,
  `places_reservees` int(11) NOT NULL DEFAULT 0,
  `places_totales` int(11) NOT NULL DEFAULT 200,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `places_disponibles`
--

INSERT INTO `places_disponibles` (`id`, `date_visite`, `places_reservees`, `places_totales`, `date_creation`, `date_modification`) VALUES
(2, '2025-12-03', 8, 200, '2025-12-01 15:09:39', '2025-12-02 00:51:10');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `date_visite` date NOT NULL,
  `forfait` enum('demi_journee','1_jour','2_jours_1_nuit') NOT NULL,
  `nombre_adultes` int(11) NOT NULL DEFAULT 0,
  `nombre_enfants` int(11) NOT NULL DEFAULT 0,
  `prix_total` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','confirmee','annulee') DEFAULT 'en_attente',
  `commentaire` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `nom`, `prenom`, `email`, `telephone`, `date_visite`, `forfait`, `nombre_adultes`, `nombre_enfants`, `prix_total`, `statut`, `commentaire`, `date_creation`, `date_modification`) VALUES
(2, 19, 'Durand', 'AAA', 'client@test.fr', '0612345678', '2025-12-03', '2_jours_1_nuit', 2, 0, 178.00, 'annulee', '', '2025-12-01 15:09:39', '2025-12-02 14:58:29'),
(3, 19, 'Durand', 'AAA', 'client@test.fr', '0612345678', '2025-12-03', '1_jour', 6, 0, 150.00, 'en_attente', '', '2025-12-02 00:51:10', '2025-12-02 00:51:10');

-- --------------------------------------------------------

--
-- Structure de la table `tarifs`
--

CREATE TABLE `tarifs` (
  `id` int(11) NOT NULL,
  `forfait` enum('demi_journee','1_jour','2_jours_1_nuit') NOT NULL,
  `type` enum('adulte','enfant') NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tarifs`
--

INSERT INTO `tarifs` (`id`, `forfait`, `type`, `prix`, `description`, `actif`, `date_debut`, `date_fin`) VALUES
(1, 'demi_journee', 'adulte', 15.00, 'Tarif adulte demi-journée (13h-18h)', 1, NULL, NULL),
(2, 'demi_journee', 'enfant', 8.00, 'Tarif enfant demi-journée (13h-18h)', 1, NULL, NULL),
(3, '1_jour', 'adulte', 25.00, 'Tarif adulte journée complète (9h-18h)', 1, NULL, NULL),
(4, '1_jour', 'enfant', 12.00, 'Tarif enfant journée complète (9h-18h)', 1, NULL, NULL),
(5, '2_jours_1_nuit', 'adulte', 89.00, 'Tarif adulte 2 jours + 1 nuit (hébergement inclus)', 1, NULL, NULL),
(6, '2_jours_1_nuit', 'enfant', 49.00, 'Tarif enfant 2 jours + 1 nuit (hébergement inclus)', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('Client','Benevole','Employe','Veterinaire','Chef_Equipe','Directeur') NOT NULL DEFAULT 'Client',
  `prenom` varchar(30) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(30) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `mdp` varchar(255) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `actif` tinyint(1) DEFAULT 1,
  `cree_par` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `role`, `prenom`, `nom`, `email`, `telephone`, `mdp`, `date_creation`, `date_modification`, `actif`, `cree_par`) VALUES
(18, 'Directeur', 'Utilisateur', 'Test', 'admin@test.fr', '0612345678', '$2y$10$os80XOR6srCN/70dwLQf0ujvQpayNZthhuUG9C69DbBSWtQWWyocO', '2025-12-01 14:58:42', '2025-12-01 14:58:42', 1, NULL),
(19, 'Client', 'AAA', 'Durand', 'client@test.fr', '0612345678', '$2y$10$kt4N3k9N5JRFL3kpDZQOduUcF67KEa5W0rg7uN0qt8Z1pF2nlW7Fq', '2025-12-01 15:07:20', '2025-12-01 15:07:20', 1, NULL),
(20, 'Chef_Equipe', 'Equipe', 'Chef', 'ce@test.fr', '1234567890', '$2y$10$bvQQpKhRxHz3uqa1nWIf5..MA6E/MZMheweZIp0AlcaNjNXKXU6RO', '2025-12-01 15:25:50', '2025-12-01 15:25:50', 1, 18),
(21, 'Veterinaire', 'Chef', 'Veto', 'veto@test.fr', '0123456789', '$2y$10$GoEuo3I.rgCQsjCOG9XbfugvOmPo8GZFRWtWEw5oyb9YjGPnQSPxC', '2025-12-01 15:43:06', '2025-12-01 15:43:06', 1, 18),
(22, 'Employe', 'pro', 'Employe', 'emp@test.fr', '0123456789', '$2y$10$CUq214YRCSIfGg5weLvAGehdi9K7MEmxjHcjcMoMEakZsjH37k1Ju', '2025-12-01 15:43:50', '2025-12-01 15:43:50', 1, 18),
(23, 'Benevole', 'Vole', 'Bene', 'ben@test.fr', '0123456789', '$2y$10$qQJuo0lYGXnBFoMz3r1eyukfkPx8aY6CPfMptjYOLP8pj.POmbu9C', '2025-12-01 15:44:21', '2025-12-02 00:37:24', 1, 18);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_reservations_aujourdhui`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_reservations_aujourdhui` (
`id` int(11)
,`nom` varchar(100)
,`prenom` varchar(100)
,`forfait` enum('demi_journee','1_jour','2_jours_1_nuit')
,`nombre_adultes` int(11)
,`nombre_enfants` int(11)
,`prix_total` decimal(10,2)
,`statut` enum('en_attente','confirmee','annulee')
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_stats_reservations`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_stats_reservations` (
`date_visite` date
,`total_reservations` bigint(21)
,`total_adultes` decimal(32,0)
,`total_enfants` decimal(32,0)
,`total_visiteurs` decimal(33,0)
,`revenu_total` decimal(32,2)
,`statuts` mediumtext
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_reservations_aujourdhui`
--
DROP TABLE IF EXISTS `v_reservations_aujourdhui`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_reservations_aujourdhui`  AS SELECT `r`.`id` AS `id`, `r`.`nom` AS `nom`, `r`.`prenom` AS `prenom`, `r`.`forfait` AS `forfait`, `r`.`nombre_adultes` AS `nombre_adultes`, `r`.`nombre_enfants` AS `nombre_enfants`, `r`.`prix_total` AS `prix_total`, `r`.`statut` AS `statut` FROM `reservations` AS `r` WHERE `r`.`date_visite` = curdate() AND `r`.`statut` <> 'annulee' ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_stats_reservations`
--
DROP TABLE IF EXISTS `v_stats_reservations`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stats_reservations`  AS SELECT `reservations`.`date_visite` AS `date_visite`, count(0) AS `total_reservations`, sum(`reservations`.`nombre_adultes`) AS `total_adultes`, sum(`reservations`.`nombre_enfants`) AS `total_enfants`, sum(`reservations`.`nombre_adultes` + `reservations`.`nombre_enfants`) AS `total_visiteurs`, sum(`reservations`.`prix_total`) AS `revenu_total`, group_concat(distinct `reservations`.`statut` separator ',') AS `statuts` FROM `reservations` GROUP BY `reservations`.`date_visite` ORDER BY `reservations`.`date_visite` DESC ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `animaux`
--
ALTER TABLE `animaux`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `enclos`
--
ALTER TABLE `enclos`
  ADD PRIMARY KEY (`Nom`);

--
-- Index pour la table `especes`
--
ALTER TABLE `especes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `logs_activite`
--
ALTER TABLE `logs_activite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_date` (`date_action`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_module` (`role`,`module`);

--
-- Index pour la table `places_disponibles`
--
ALTER TABLE `places_disponibles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date_visite` (`date_visite`),
  ADD KEY `idx_date` (`date_visite`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_visite` (`date_visite`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `tarifs`
--
ALTER TABLE `tarifs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_forfait_type` (`forfait`,`type`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `animaux`
--
ALTER TABLE `animaux`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `especes`
--
ALTER TABLE `especes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT pour la table `logs_activite`
--
ALTER TABLE `logs_activite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `places_disponibles`
--
ALTER TABLE `places_disponibles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `tarifs`
--
ALTER TABLE `tarifs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `logs_activite`
--
ALTER TABLE `logs_activite`
  ADD CONSTRAINT `logs_activite_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
