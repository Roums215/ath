-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 11 fév. 2025 à 16:53
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
-- Base de données : `ramsaysante`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `Id_Admin` int(11) NOT NULL,
  `ANom` varchar(50) NOT NULL,
  `APrenom` varchar(50) NOT NULL,
  `AMdp` varchar(50) NOT NULL,
  `ACreation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`Id_Admin`, `ANom`, `APrenom`, `AMdp`, `ACreation`) VALUES
(5, 'admin', 'Administrateur', '123', '2025-01-12 02:37:31');

-- --------------------------------------------------------

--
-- Structure de la table `bunker`
--

CREATE TABLE `bunker` (
  `Id_Bunker` int(11) NOT NULL,
  `BNom` varchar(50) NOT NULL,
  `BEnum` varchar(50) DEFAULT NULL,
  `BEtat` enum('Pas de retard','Maintenance','Retard 5 min','Retard 10 min','Retard 15 min','Retard 20 min','Retard 25 min','Retard 30 min','Retard 45 min','Retard 1h','Retard 1h30','Retard 2h min','Hors service') DEFAULT 'Pas de retard',
  `BCreation` datetime DEFAULT current_timestamp(),
  `BDerniereModifPar` int(11) DEFAULT NULL,
  `Id_Site` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `bunker`
--


-- --------------------------------------------------------

--
-- Structure de la table `logs`
--

CREATE TABLE `logs` (
  `Id_Logs` int(11) NOT NULL,
  `LAction` enum('delete','update','create','connexion','creationCompte') NOT NULL,
  `LDate` datetime DEFAULT current_timestamp(),
  `Id_Personnel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `logs`
--



-- --------------------------------------------------------

--
-- Structure de la table `personnel`
--

CREATE TABLE `personnel` (
  `Id_Personnel` int(11) NOT NULL,
  `PNom` varchar(50) NOT NULL,
  `PPrenom` varchar(50) NOT NULL,
  `PMdp` varchar(255) NOT NULL,
  `Id_Site` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `personnel`
--


-- --------------------------------------------------------

--
-- Structure de la table `site`
--

CREATE TABLE `site` (
  `Id_Site` int(11) NOT NULL,
  `SNom` varchar(50) NOT NULL,
  `SLieu` varchar(100) DEFAULT NULL,
  `Id_Admin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `site`
--

INSERT INTO `site` (`Id_Site`, `SNom`, `SLieu`, `Id_Admin`) VALUES
(5, 'Beauregard', 'uploads/logos/1738918633_logoPoleBlanc.png', 5),
(6, 'Clairval', 'uploads/logos/1738667555_LOGO-RAMSAYBlancClairval.png', 5);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Id_Admin`);

--
-- Index pour la table `bunker`
--
ALTER TABLE `bunker`
  ADD PRIMARY KEY (`Id_Bunker`),
  ADD KEY `Id_Site` (`Id_Site`),
  ADD KEY `BDerniereModifPar` (`BDerniereModifPar`);

--

--
-- Index pour la table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`Id_Logs`),
  ADD KEY `Id_Personnel` (`Id_Personnel`);

--

--
-- Index pour la table `personnel`
--
ALTER TABLE `personnel`
  ADD PRIMARY KEY (`Id_Personnel`),
  ADD KEY `Id_Site` (`Id_Site`);

--
-- Index pour la table `site`
--
ALTER TABLE `site`
  ADD PRIMARY KEY (`Id_Site`),
  ADD KEY `Id_Admin` (`Id_Admin`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `Id_Admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `bunker`
--
ALTER TABLE `bunker`
  MODIFY `Id_Bunker` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--


--
-- AUTO_INCREMENT pour la table `logs`
--
ALTER TABLE `logs`
  MODIFY `Id_Logs` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--

--
-- AUTO_INCREMENT pour la table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `Id_Personnel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `site`
--
ALTER TABLE `site`
  MODIFY `Id_Site` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bunker`
--
ALTER TABLE `bunker`
  ADD CONSTRAINT `bunker_ibfk_1` FOREIGN KEY (`Id_Site`) REFERENCES `site` (`Id_Site`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bunker_ibfk_2` FOREIGN KEY (`BDerniereModifPar`) REFERENCES `personnel` (`Id_Personnel`) ON DELETE SET NULL ON UPDATE CASCADE;


--
-- Contraintes pour la table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`Id_Personnel`) REFERENCES `personnel` (`Id_Personnel`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `personnel`
--
ALTER TABLE `personnel`
  ADD CONSTRAINT `personnel_ibfk_1` FOREIGN KEY (`Id_Site`) REFERENCES `site` (`Id_Site`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `site`
--
ALTER TABLE `site`
  ADD CONSTRAINT `site_ibfk_1` FOREIGN KEY (`Id_Admin`) REFERENCES `admin` (`Id_Admin`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
