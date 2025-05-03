-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 11:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kadodo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrateur`
--

CREATE TABLE `administrateur` (
  `id_admin` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `departement_admin` enum('SYSTEM','SCOLARITE','COMPTABILITE','DIRECTION') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrateur`
--

INSERT INTO `administrateur` (`id_admin`, `id_utilisateur`, `departement_admin`) VALUES
(28, 236, 'SYSTEM');

-- --------------------------------------------------------

--
-- Table structure for table `annonce`
--

CREATE TABLE `annonce` (
  `id_annonce` int(11) NOT NULL,
  `id_moderateur` int(11) NOT NULL,
  `titre_annonce` varchar(150) NOT NULL,
  `contenu_annonce` text NOT NULL,
  `etat_annonce` varchar(30) NOT NULL,
  `date_pub_annonce` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attribution_cours`
--

CREATE TABLE `attribution_cours` (
  `id_attribution` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `id_filiere` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorie_forum`
--

CREATE TABLE `categorie_forum` (
  `id_categorie_forum` int(11) NOT NULL,
  `nom_categorie_forum` varchar(60) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorie_forum`
--

INSERT INTO `categorie_forum` (`id_categorie_forum`, `nom_categorie_forum`, `description`) VALUES
(1, 'Annonces officielles', 'Annonces importantes de l\'administration et des professeurs'),
(2, 'Aide aux devoirs', 'Entraide entre étudiants pour les devoirs et exercices'),
(3, 'Discussions générales', 'Espace de discussion libre pour tous les sujets liés à l\'école'),
(4, 'Questions aux professeurs', 'Espace dédié aux questions pour les professeurs');

-- --------------------------------------------------------

--
-- Table structure for table `classe`
--

CREATE TABLE `classe` (
  `id_classe` int(11) NOT NULL,
  `nom_classe` varchar(50) NOT NULL,
  `id_filiere` int(11) NOT NULL,
  `id_niveau` int(11) NOT NULL,
  `id_professeur` int(11) DEFAULT NULL,
  `nombre_etudiants` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_publication_forum` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `id_conversation` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `id_destinataire` int(11) NOT NULL,
  `derniere_mise_a_jour` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cours`
--

CREATE TABLE `cours` (
  `id_cours` int(11) NOT NULL,
  `nom_cours` varchar(50) NOT NULL,
  `id_professeur` int(11) NOT NULL,
  `salle` varchar(50) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `nombre_heures` int(11) DEFAULT 2,
  `id_documents` int(11) NOT NULL,
  `nombre_document` int(11) NOT NULL,
  `lib_cours` varchar(50) DEFAULT NULL,
  `id_classe` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cours_filiere`
--

CREATE TABLE `cours_filiere` (
  `id_cours_filiere` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `id_filiere` int(11) NOT NULL,
  `id_niveau` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

CREATE TABLE `document` (
  `id_doc` int(11) NOT NULL,
  `date_upload` datetime DEFAULT NULL,
  `lib_doc` varchar(100) DEFAULT NULL,
  `chemin_doc` varchar(100) DEFAULT NULL,
  `id_filiere` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `id_professeur` int(11) NOT NULL,
  `type_doc` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emploi_du_temps`
--

CREATE TABLE `emploi_du_temps` (
  `id_emploi_de_temps` int(11) NOT NULL,
  `id_filiere` int(11) NOT NULL,
  `id_professeur` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `salle` varchar(50) DEFAULT NULL,
  `jour` varchar(20) DEFAULT NULL,
  `statut` varchar(20) DEFAULT 'actif' CHECK (`statut` in ('actif','inactif'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `etudiant`
--

CREATE TABLE `etudiant` (
  `id_etudiant` int(11) NOT NULL,
  `matricule_etudiant` char(15) DEFAULT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_classe` int(11) NOT NULL,
  `id_niveau` int(11) NOT NULL,
  `id_cours` int(11) DEFAULT NULL,
  `id_filiere` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `etudiant_cours`
--

CREATE TABLE `etudiant_cours` (
  `id_etudiant_cours` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `date_attribution` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filiere`
--

CREATE TABLE `filiere` (
  `id_filiere` int(11) NOT NULL,
  `libelle_filiere` varchar(60) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_professeur` int(11) NOT NULL,
  `id_classe` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `id_emploi_du_temps` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance`
--

CREATE TABLE `finance` (
  `id_finance` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `montant_total` double NOT NULL,
  `montant_en_attente` double NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gestion_finance`
--

CREATE TABLE `gestion_finance` (
  `id_gestion_finance` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `montant_total` double NOT NULL,
  `montant_en_attente` double NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id_message` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `id_destinataire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime NOT NULL,
  `date_lecture` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id_message` int(11) NOT NULL,
  `id_conversation` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `fichier` varchar(255) DEFAULT NULL,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `lu` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moderateur`
--

CREATE TABLE `moderateur` (
  `id_moderateur` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_publication_forum` int(11) DEFAULT NULL,
  `id_blog` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `niveau`
--

CREATE TABLE `niveau` (
  `id_niveau` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_professeur` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `id_emploi_du_temps` int(11) NOT NULL,
  `id_classe` int(11) NOT NULL,
  `id_filiere` int(11) NOT NULL,
  `libelle_niveau` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `niveau`
--

INSERT INTO `niveau` (`id_niveau`, `id_etudiant`, `id_professeur`, `id_cours`, `id_emploi_du_temps`, `id_classe`, `id_filiere`, `libelle_niveau`) VALUES
(1, 0, 0, 0, 0, 0, 0, 'L1'),
(2, 0, 0, 0, 0, 0, 0, 'L2'),
(3, 0, 0, 0, 0, 0, 0, 'L3');

-- --------------------------------------------------------

--
-- Table structure for table `note`
--

CREATE TABLE `note` (
  `id_note` int(11) NOT NULL,
  `note` double DEFAULT NULL,
  `Semestre` varchar(50) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `message` text NOT NULL,
  `date_creation` datetime NOT NULL,
  `date_lecture` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id_notification` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_destinataire` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `lu` tinyint(1) DEFAULT 0,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parametre`
--

CREATE TABLE `parametre` (
  `id_parametre` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions_admin`
--

CREATE TABLE `permissions_admin` (
  `id_permission` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `nom_permission` varchar(50) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions_admin`
--

INSERT INTO `permissions_admin` (`id_permission`, `id_utilisateur`, `nom_permission`, `date_creation`) VALUES
(1, 10, 'GESTION_UTILISATEURS', '2025-03-25 12:11:44'),
(2, 10, 'GESTION_ROLES', '2025-03-25 12:11:44'),
(3, 11, 'GESTION_UTILISATEURS', '2025-03-25 12:12:28'),
(4, 11, 'GESTION_ROLES', '2025-03-25 12:12:28');

-- --------------------------------------------------------

--
-- Table structure for table `professeur`
--

CREATE TABLE `professeur` (
  `id_professeur` int(11) NOT NULL,
  `matricule_professeur` char(15) NOT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  `id_cours` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publication_blog`
--

CREATE TABLE `publication_blog` (
  `id_publication_blog` int(11) NOT NULL,
  `id_moderateur` int(11) NOT NULL,
  `titre` varchar(50) NOT NULL,
  `contenu` text NOT NULL,
  `categorie_blog` varchar(50) NOT NULL,
  `date_creation` datetime NOT NULL,
  `date_mise_a_jour` datetime NOT NULL,
  `photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publication_forum`
--

CREATE TABLE `publication_forum` (
  `id_publication_forum` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `titre` text NOT NULL,
  `contenu` text NOT NULL,
  `photo` varchar(60) NOT NULL,
  `date_creation` datetime NOT NULL,
  `date_mise_a_jour` datetime NOT NULL,
  `id_categorie_forum` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id_role` int(11) NOT NULL,
  `nom_role` varchar(60) NOT NULL,
  `description_role` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id_role`, `nom_role`, `description_role`) VALUES
(1, 'etudiant', 'Rôle pour les étudiants'),
(2, 'professeur', 'Rôle pour les professeurs'),
(3, 'admin', 'Rôle pour les administrateurs'),
(4, 'moderateur', 'Rôle pour les modérateurs');

-- --------------------------------------------------------

--
-- Table structure for table `salle`
--

CREATE TABLE `salle` (
  `id_salle` int(11) NOT NULL,
  `nom_salle` varchar(50) NOT NULL,
  `capacite` int(11) DEFAULT 30,
  `equipements` text DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tentatives_connexion`
--

CREATE TABLE `tentatives_connexion` (
  `id` int(11) NOT NULL,
  `matricule` varchar(100) NOT NULL,
  `date_tentative` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id_transaction` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `montant` double NOT NULL,
  `type_paiement` varchar(50) NOT NULL,
  `statut_paiement` enum('paye','en_attente','en_retard') NOT NULL,
  `date_paiement` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id_utilisateur` int(11) NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_online` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_utilisateur` varchar(50) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `email` varchar(20) NOT NULL,
  `id_role` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `photo_profile` varchar(255) DEFAULT 'default.jpg',
  `date_creation` datetime NOT NULL,
  `date_mise_a_jour` datetime NOT NULL,
  `matricule` varchar(100) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `tel` varchar(50) DEFAULT NULL,
  `statut_compte` enum('actif','inactif') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom_utilisateur`, `mot_de_passe`, `email`, `id_role`, `bio`, `photo_profile`, `date_creation`, `date_mise_a_jour`, `matricule`, `prenom`, `nom`, `tel`, `statut_compte`) VALUES
(236, '', '$2y$10$KKWLCs4Q4msGs3u9zuJvc.YAGrNHqozoH5nMwMF2aeD3Bmhq.hV3O', 'a96.paul96@gmail.com', 3, NULL, 'default.jpg', '2025-04-16 21:48:55', '0000-00-00 00:00:00', 'ADM-1', 'Ltk', 'Mxz', '93809680', 'actif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrateur`
--
ALTER TABLE `administrateur`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `annonce`
--
ALTER TABLE `annonce`
  ADD PRIMARY KEY (`id_annonce`);

--
-- Indexes for table `attribution_cours`
--
ALTER TABLE `attribution_cours`
  ADD PRIMARY KEY (`id_attribution`),
  ADD UNIQUE KEY `unique_attribution` (`id_cours`,`id_filiere`),
  ADD KEY `fk_attribution_filiere` (`id_filiere`);

--
-- Indexes for table `categorie_forum`
--
ALTER TABLE `categorie_forum`
  ADD PRIMARY KEY (`id_categorie_forum`);

--
-- Indexes for table `classe`
--
ALTER TABLE `classe`
  ADD PRIMARY KEY (`id_classe`),
  ADD KEY `fk_classe_filiere` (`id_filiere`),
  ADD KEY `fk_classe_niveau` (`id_niveau`);

--
-- Indexes for table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`);

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`id_conversation`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

--
-- Indexes for table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id_cours`);

--
-- Indexes for table `cours_filiere`
--
ALTER TABLE `cours_filiere`
  ADD PRIMARY KEY (`id_cours_filiere`),
  ADD KEY `id_cours` (`id_cours`),
  ADD KEY `id_filiere` (`id_filiere`),
  ADD KEY `id_niveau` (`id_niveau`);

--
-- Indexes for table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id_doc`),
  ADD KEY `id_filiere` (`id_filiere`),
  ADD KEY `id_cours` (`id_cours`),
  ADD KEY `id_professeur` (`id_professeur`);

--
-- Indexes for table `emploi_du_temps`
--
ALTER TABLE `emploi_du_temps`
  ADD PRIMARY KEY (`id_emploi_de_temps`);

--
-- Indexes for table `etudiant`
--
ALTER TABLE `etudiant`
  ADD PRIMARY KEY (`id_etudiant`),
  ADD KEY `fk_etudiant_cours` (`id_cours`),
  ADD KEY `fk_etudiant_classe` (`id_classe`);

--
-- Indexes for table `etudiant_cours`
--
ALTER TABLE `etudiant_cours`
  ADD PRIMARY KEY (`id_etudiant_cours`),
  ADD UNIQUE KEY `unique_etudiant_cours` (`id_etudiant`,`id_cours`),
  ADD KEY `fk_etudiant_cours_etudiant` (`id_etudiant`),
  ADD KEY `fk_etudiant_cours_cours` (`id_cours`);

--
-- Indexes for table `filiere`
--
ALTER TABLE `filiere`
  ADD PRIMARY KEY (`id_filiere`);

--
-- Indexes for table `finance`
--
ALTER TABLE `finance`
  ADD PRIMARY KEY (`id_finance`);

--
-- Indexes for table `gestion_finance`
--
ALTER TABLE `gestion_finance`
  ADD PRIMARY KEY (`id_gestion_finance`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id_message`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_conversation` (`id_conversation`),
  ADD KEY `id_expediteur` (`id_expediteur`);

--
-- Indexes for table `moderateur`
--
ALTER TABLE `moderateur`
  ADD PRIMARY KEY (`id_moderateur`),
  ADD KEY `fk_moderateur_utilisateur` (`id_utilisateur`);

--
-- Indexes for table `niveau`
--
ALTER TABLE `niveau`
  ADD PRIMARY KEY (`id_niveau`);

--
-- Indexes for table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`id_note`),
  ADD KEY `id_cours` (`id_cours`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `id_destinataire` (`id_destinataire`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `idx_notifications_utilisateur` (`id_utilisateur`);

--
-- Indexes for table `parametre`
--
ALTER TABLE `parametre`
  ADD PRIMARY KEY (`id_parametre`);

--
-- Indexes for table `permissions_admin`
--
ALTER TABLE `permissions_admin`
  ADD PRIMARY KEY (`id_permission`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Indexes for table `professeur`
--
ALTER TABLE `professeur`
  ADD PRIMARY KEY (`id_professeur`);

--
-- Indexes for table `publication_blog`
--
ALTER TABLE `publication_blog`
  ADD PRIMARY KEY (`id_publication_blog`);

--
-- Indexes for table `publication_forum`
--
ALTER TABLE `publication_forum`
  ADD PRIMARY KEY (`id_publication_forum`),
  ADD KEY `fk_publication_categorie` (`id_categorie_forum`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `salle`
--
ALTER TABLE `salle`
  ADD PRIMARY KEY (`id_salle`);

--
-- Indexes for table `tentatives_connexion`
--
ALTER TABLE `tentatives_connexion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_matricule_date` (`matricule`,`date_tentative`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id_transaction`),
  ADD KEY `fk_transaction_etudiant` (`id_etudiant`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrateur`
--
ALTER TABLE `administrateur`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `annonce`
--
ALTER TABLE `annonce`
  MODIFY `id_annonce` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `attribution_cours`
--
ALTER TABLE `attribution_cours`
  MODIFY `id_attribution` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `categorie_forum`
--
ALTER TABLE `categorie_forum`
  MODIFY `id_categorie_forum` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `classe`
--
ALTER TABLE `classe`
  MODIFY `id_classe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `id_conversation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `cours`
--
ALTER TABLE `cours`
  MODIFY `id_cours` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `cours_filiere`
--
ALTER TABLE `cours_filiere`
  MODIFY `id_cours_filiere` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `document`
--
ALTER TABLE `document`
  MODIFY `id_doc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `emploi_du_temps`
--
ALTER TABLE `emploi_du_temps`
  MODIFY `id_emploi_de_temps` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `etudiant`
--
ALTER TABLE `etudiant`
  MODIFY `id_etudiant` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `etudiant_cours`
--
ALTER TABLE `etudiant_cours`
  MODIFY `id_etudiant_cours` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `filiere`
--
ALTER TABLE `filiere`
  MODIFY `id_filiere` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `finance`
--
ALTER TABLE `finance`
  MODIFY `id_finance` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gestion_finance`
--
ALTER TABLE `gestion_finance`
  MODIFY `id_gestion_finance` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=274;

--
-- AUTO_INCREMENT for table `moderateur`
--
ALTER TABLE `moderateur`
  MODIFY `id_moderateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `niveau`
--
ALTER TABLE `niveau`
  MODIFY `id_niveau` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `id_note` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parametre`
--
ALTER TABLE `parametre`
  MODIFY `id_parametre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions_admin`
--
ALTER TABLE `permissions_admin`
  MODIFY `id_permission` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `professeur`
--
ALTER TABLE `professeur`
  MODIFY `id_professeur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `publication_blog`
--
ALTER TABLE `publication_blog`
  MODIFY `id_publication_blog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `publication_forum`
--
ALTER TABLE `publication_forum`
  MODIFY `id_publication_forum` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `salle`
--
ALTER TABLE `salle`
  MODIFY `id_salle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `tentatives_connexion`
--
ALTER TABLE `tentatives_connexion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id_transaction` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attribution_cours`
--
ALTER TABLE `attribution_cours`
  ADD CONSTRAINT `fk_attribution_cours` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`),
  ADD CONSTRAINT `fk_attribution_filiere` FOREIGN KEY (`id_filiere`) REFERENCES `filiere` (`id_filiere`);

--
-- Constraints for table `classe`
--
ALTER TABLE `classe`
  ADD CONSTRAINT `fk_classe_filiere` FOREIGN KEY (`id_filiere`) REFERENCES `filiere` (`id_filiere`),
  ADD CONSTRAINT `fk_classe_niveau` FOREIGN KEY (`id_niveau`) REFERENCES `niveau` (`id_niveau`);

--
-- Constraints for table `conversation`
--
ALTER TABLE `conversation`
  ADD CONSTRAINT `conversation_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `conversation_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Constraints for table `cours_filiere`
--
ALTER TABLE `cours_filiere`
  ADD CONSTRAINT `cours_filiere_ibfk_1` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`),
  ADD CONSTRAINT `cours_filiere_ibfk_2` FOREIGN KEY (`id_filiere`) REFERENCES `filiere` (`id_filiere`),
  ADD CONSTRAINT `cours_filiere_ibfk_3` FOREIGN KEY (`id_niveau`) REFERENCES `niveau` (`id_niveau`);

--
-- Constraints for table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`id_filiere`) REFERENCES `filiere` (`id_filiere`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_ibfk_2` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_ibfk_3` FOREIGN KEY (`id_professeur`) REFERENCES `professeur` (`id_professeur`) ON DELETE CASCADE;

--
-- Constraints for table `etudiant`
--
ALTER TABLE `etudiant`
  ADD CONSTRAINT `fk_etudiant_classe` FOREIGN KEY (`id_classe`) REFERENCES `classe` (`id_classe`),
  ADD CONSTRAINT `fk_etudiant_cours` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE SET NULL;

--
-- Constraints for table `etudiant_cours`
--
ALTER TABLE `etudiant_cours`
  ADD CONSTRAINT `fk_etudiant_cours_cours` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_etudiant_cours_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_etudiant`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur` (`id_utilisateur`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
