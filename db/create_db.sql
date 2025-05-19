-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 19 mai 2025 à 15:38
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
-- Base de données : `bibliotheque`
--

-- --------------------------------------------------------

--
-- Structure de la table `auteur`
--

CREATE TABLE `auteur` (
                          `id` int(11) NOT NULL,
                          `nom` varchar(255) NOT NULL,
                          `prenom` varchar(255) NOT NULL,
                          `date_naissance` date NOT NULL,
                          `date_mort` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `auteur`
--

INSERT INTO `auteur` (`id`, `nom`, `prenom`, `date_naissance`, `date_mort`) VALUES
                                                                                (1, 'Tolkien', 'J.R.R.', '1892-01-03', '1973-09-02'),
                                                                                (2, 'Rowling', 'J.K.', '1965-07-31', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
                          `id` int(11) NOT NULL,
                          `nom` varchar(255) NOT NULL,
                          `prenom` varchar(255) NOT NULL,
                          `email` varchar(255) NOT NULL,
                          `tel` varchar(10) NOT NULL,
                          `adresse` varchar(255) NOT NULL,
                          `date_inscription` date NOT NULL,
                          `date_naissance` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id`, `nom`, `prenom`, `email`, `tel`, `adresse`, `date_inscription`, `date_naissance`) VALUES
                                                                                                                  (1, 'Durand', 'Alice', 'alice.durand@example.com', '0601020304', '12 rue des Lilas, Paris', '2023-01-15', '1990-06-25'),
                                                                                                                  (2, 'Martin', 'Bob', 'bob.martin@example.com', '0604050607', '34 avenue Victor Hugo, Lyon', '2023-02-20', '1985-03-10');

-- --------------------------------------------------------

--
-- Structure de la table `emprunt`
--

CREATE TABLE `emprunt` (
                           `id` int(11) NOT NULL,
                           `client_id` int(11) NOT NULL,
                           `livre_id` int(11) NOT NULL,
                           `date_emprunt` date NOT NULL,
                           `date_limite_retour` date NOT NULL,
                           `date_retour` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `emprunt`
--

INSERT INTO `emprunt` (`id`, `client_id`, `livre_id`, `date_emprunt`, `date_limite_retour`, `date_retour`) VALUES
                                                                                                               (1, 1, 1, '2024-05-01', '2024-05-31', NULL),
                                                                                                               (2, 2, 2, '2024-04-20', '2024-05-20', '2024-05-10'),
                                                                                                               (3, 1, 2, '2025-05-18', '2025-05-18', '2025-05-18');

-- --------------------------------------------------------

--
-- Structure de la table `genre`
--

CREATE TABLE `genre` (
                         `id` int(11) NOT NULL,
                         `nom` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `genre`
--

INSERT INTO `genre` (`id`, `nom`) VALUES
                                      (1, 'Fantasy'),
                                      (2, 'Aventure'),
                                      (3, 'Science-fiction'),
                                      (4, 'Jeunesse');

-- --------------------------------------------------------

--
-- Structure de la table `livre`
--

CREATE TABLE `livre` (
                         `id` int(11) NOT NULL,
                         `genre_id` int(11) NOT NULL,
                         `titre` varchar(255) NOT NULL,
                         `isbn` varchar(255) NOT NULL,
                         `date_parution` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `livre`
--

INSERT INTO `livre` (`id`, `genre_id`, `titre`, `isbn`, `date_parution`) VALUES
                                                                             (1, 1, 'Le Seigneur des Anneaux', '9782070612884', '1964-07-29'),
                                                                             (2, 4, 'Harry Potter et la coupe de feu', '9780747532748', '1999-05-16'),
                                                                             (3, 3, 'Dune', '9781234567897', '1965-06-01');

-- --------------------------------------------------------

--
-- Structure de la table `livre_auteur`
--

CREATE TABLE `livre_auteur` (
                                `livre_id` int(11) NOT NULL,
                                `auteur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `livre_auteur`
--

INSERT INTO `livre_auteur` (`livre_id`, `auteur_id`) VALUES
                                                         (1, 1),
                                                         (2, 2),
                                                         (3, 1);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
                                      `id` bigint(20) NOT NULL,
                                      `body` longtext NOT NULL,
                                      `headers` longtext NOT NULL,
                                      `queue_name` varchar(190) NOT NULL,
                                      `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                                      `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                                      `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `auteur`
--
ALTER TABLE `auteur`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `client`
--
ALTER TABLE `client`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `emprunt`
--
ALTER TABLE `emprunt`
    ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_364071D719EB6921` (`client_id`),
  ADD KEY `IDX_364071D737D925CB` (`livre_id`);

--
-- Index pour la table `genre`
--
ALTER TABLE `genre`
    ADD PRIMARY KEY (`id`);

--
-- Index pour la table `livre`
--
ALTER TABLE `livre`
    ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AC634F994296D31F` (`genre_id`);

--
-- Index pour la table `livre_auteur`
--
ALTER TABLE `livre_auteur`
    ADD PRIMARY KEY (`livre_id`,`auteur_id`),
  ADD KEY `IDX_A11876B537D925CB` (`livre_id`),
  ADD KEY `IDX_A11876B560BB6FE6` (`auteur_id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
    ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `auteur`
--
ALTER TABLE `auteur`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `client`
--
ALTER TABLE `client`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `emprunt`
--
ALTER TABLE `emprunt`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `genre`
--
ALTER TABLE `genre`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `livre`
--
ALTER TABLE `livre`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `emprunt`
--
ALTER TABLE `emprunt`
    ADD CONSTRAINT `FK_364071D719EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  ADD CONSTRAINT `FK_364071D737D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id`);

--
-- Contraintes pour la table `livre`
--
ALTER TABLE `livre`
    ADD CONSTRAINT `FK_AC634F994296D31F` FOREIGN KEY (`genre_id`) REFERENCES `genre` (`id`);

--
-- Contraintes pour la table `livre_auteur`
--
ALTER TABLE `livre_auteur`
    ADD CONSTRAINT `FK_A11876B537D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_A11876B560BB6FE6` FOREIGN KEY (`auteur_id`) REFERENCES `auteur` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
