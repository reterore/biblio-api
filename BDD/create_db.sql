-- Supprimer les tables dans l'ordre des dépendances
DROP TABLE IF EXISTS emprunts;
DROP TABLE IF EXISTS livres_auteurs;
DROP TABLE IF EXISTS livres;
DROP TABLE IF EXISTS auteurs;
DROP TABLE IF EXISTS clients;

-- Table des clients
CREATE TABLE clients (
                         id_client INT AUTO_INCREMENT PRIMARY KEY,
                         nom VARCHAR(255) NOT NULL,
                         prenom VARCHAR(255) NOT NULL,
                         email VARCHAR(255) NOT NULL,
                         tel VARCHAR(10) NOT NULL,
                         date_inscription DATE NOT NULL,
                         date_naissance DATE NOT NULL,
                         adresse VARCHAR(255) NOT NULL
);

-- Table des auteurs
CREATE TABLE auteurs (
                         id_auteur INT AUTO_INCREMENT PRIMARY KEY,
                         nom VARCHAR(255) NOT NULL,
                         prenom VARCHAR(255) NOT NULL,
                         date_naissance DATE NOT NULL,
                         date_mort DATE DEFAULT NULL
);

-- Table des livres
CREATE TABLE livres (
                        id_livre INT AUTO_INCREMENT PRIMARY KEY,
                        titre VARCHAR(255) NOT NULL,
                        isbn VARCHAR(20) DEFAULT NULL,
                        date_parution DATE DEFAULT NULL
);

-- Table de jonction livres_auteurs (relation N:N)
CREATE TABLE livres_auteurs (
                                id_livre INT NOT NULL,
                                id_auteur INT NOT NULL,
                                PRIMARY KEY (id_livre, id_auteur),
                                FOREIGN KEY (id_livre) REFERENCES livres(id_livre)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE,
                                FOREIGN KEY (id_auteur) REFERENCES auteurs(id_auteur)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
);

-- Table des emprunts
CREATE TABLE emprunts (
                          id_emprunt INT AUTO_INCREMENT PRIMARY KEY,
                          id_client INT NOT NULL,
                          id_livre INT NOT NULL,
                          date_emprunt DATE NOT NULL,
                          date_limite_retour DATE NOT NULL,
                          date_retour DATE DEFAULT NULL,
                          FOREIGN KEY (id_client) REFERENCES clients(id_client)
                              ON DELETE CASCADE
                              ON UPDATE CASCADE,
                          FOREIGN KEY (id_livre) REFERENCES livres(id_livre)
                              ON DELETE CASCADE
                              ON UPDATE CASCADE
);
