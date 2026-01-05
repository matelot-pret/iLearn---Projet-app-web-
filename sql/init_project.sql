-- coller ce fichier dans l'onglet SQL de phpMyAdmin et l'éxecuter
-- Création de la base de donnée
CREATE DATABASE IF NOT EXISTS projetphp
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

-- Création de l'utilisateur
CREATE USER IF NOT EXISTS 'BDProjetPatrick&Jean'@'localhost'
IDENTIFIED BY 'projetphp_pass';

-- Droits sur la base
GRANT ALL PRIVILEGES ON projetphp.* TO 'BDProjetPatrick&Jean'@'localhost';

FLUSH PRIVILEGES;

-- Table utilisateur
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    matricule VARCHAR(50) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    est_admin TINYINT(1) NOT NULL DEFAULT 0,
    avatar VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table cours
CREATE TABLE IF NOT EXISTS cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    bloc VARCHAR(50) NOT NULL,
    section VARCHAR(100) NOT NULL
);

-- Table ressources
CREATE TABLE IF NOT EXISTS ressources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    type VARCHAR(20) NOT NULL,
    contenu TEXT NOT NULL,
    cours_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    statut ENUM('en_attente', 'approuve', 'rejete') NOT NULL DEFAULT 'en_attente',
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cours_id) REFERENCES cours(id)
        ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE
);

-- Table ressources_lues
CREATE TABLE IF NOT EXISTS ressources_lues (
    ressource_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_lue DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (ressource_id, utilisateur_id),

    FOREIGN KEY (ressource_id) REFERENCES ressources(id)
        ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE
);

-- Creation du premier admin 
-- le hash est généré par le fichier hash.php
-- ouvrir le fichier hash.php dans le navigateur et copier le hash obtenu
-- Pour se connecter utiliser "Samourai3" comme mot de passe 
INSERT INTO utilisateurs
(nom, prenom, matricule, mot_de_passe, est_admin)
VALUES
(
    'Admin',
    'Super',
    'ADMIN1',
    'coller le hash obtenu ici', 1
    1
)
ON DUPLICATE KEY UPDATE matricule = matricule;


