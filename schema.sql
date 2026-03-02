-- schema.sql
-- MySQL setup for MyUnivManager

CREATE DATABASE IF NOT EXISTS universite;
USE universite;

CREATE TABLE IF NOT EXISTS etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    num_etudiant VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    filiere VARCHAR(100) NOT NULL,
    annee_entree YEAR NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- trigger to update updated_at automatically (already handled by column definition)
