-- Base de données Uber-Cueillette (MySQL / phpMyAdmin)
-- Schéma reconstruit à partir du code PHP. Si vous avez votre export phpMyAdmin d'origine,
-- utilisez-le à la place de ce fichier.

CREATE DATABASE IF NOT EXISTS uber_cueillette CHARACTER SET utf8mb4;
USE uber_cueillette;

CREATE TABLE type_fruit (
    id_type_fruit INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE gouvernorat (
    id_gouvernorat INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE agriculteur (
    id_agriculteur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    CIN CHAR(8) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    adresse VARCHAR(255) NOT NULL,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE ouvrier (
    id_ouvrier INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    CIN CHAR(8) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    photo LONGBLOB,
    description TEXT,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE offre (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    id_type_fruit INT NOT NULL,
    id_gouvernorat INT NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    nombre_ouvriers INT NOT NULL,
    prix_journee DECIMAL(10,2) NOT NULL,
    date_limite DATE NOT NULL,
    id_agriculteur INT NOT NULL,
    FOREIGN KEY (id_type_fruit) REFERENCES type_fruit(id_type_fruit),
    FOREIGN KEY (id_gouvernorat) REFERENCES gouvernorat(id_gouvernorat),
    FOREIGN KEY (id_agriculteur) REFERENCES agriculteur(id_agriculteur)
);

CREATE TABLE candidature (
    id_candidature INT AUTO_INCREMENT PRIMARY KEY,
    id_offre INT NOT NULL,
    id_ouvrier INT NOT NULL,
    decision ENUM('encours','accepte','refuse') NOT NULL DEFAULT 'encours',
    date_candidature DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    note TINYINT NULL,
    commentaire TEXT NULL,
    remuneration DECIMAL(10,2) NULL,
    UNIQUE (id_offre, id_ouvrier),
    FOREIGN KEY (id_offre) REFERENCES offre(id_offre),
    FOREIGN KEY (id_ouvrier) REFERENCES ouvrier(id_ouvrier)
);

INSERT INTO type_fruit (libelle) VALUES ('Olives'), ('Agrumes'), ('Tomates');

INSERT INTO gouvernorat (libelle) VALUES
('Ariana'),('Béja'),('Ben Arous'),('Bizerte'),('Gabès'),('Gafsa'),('Jendouba'),('Kairouan'),
('Kasserine'),('Kébili'),('Le Kef'),('Mahdia'),('La Manouba'),('Médenine'),('Monastir'),('Nabeul'),
('Sfax'),('Sidi Bouzid'),('Siliana'),('Sousse'),('Tataouine'),('Tozeur'),('Tunis'),('Zaghouan');
