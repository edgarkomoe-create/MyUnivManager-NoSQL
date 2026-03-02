-- =============================================================================
-- MyUnivManager - Complete Database Setup
-- Database: universite
-- SOLUTION: Exécutez ce script en deux étapes dans phpMyAdmin
-- =============================================================================

-- ÉTAPE 1: Créer la base de données
-- Collez les lignes 1-10 SEULES d'abord, puis exécutez
-- Puis sélectionnez la base "universite" dans la liste déroulante
-- Puis collez et exécutez le reste (à partir de la ligne 12)

-- =============================================================================
DROP DATABASE IF EXISTS universite;
CREATE DATABASE universite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- ÉTAPE 2: Sélectionnez "universite" dans le menu déroulant des bases de données
-- puis exécutez le reste du script ci-dessous
-- =============================================================================

USE universite;

-- =============================================================================
-- TABLE: utilisateurs (Users/Login)
-- =============================================================================
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    role ENUM('admin', 'enseignant', 'etudiant') DEFAULT 'etudiant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABLE: etudiants (Students)
-- =============================================================================
CREATE TABLE etudiants (
    idEtudiant INT AUTO_INCREMENT PRIMARY KEY,
    numCarte VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    tel VARCHAR(20),
    filiere VARCHAR(100) NOT NULL,
    annee_entree INT,
    date_naissance DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_filiere (filiere),
    INDEX idx_numCarte (numCarte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABLE: enseignants (Teachers)
-- =============================================================================
CREATE TABLE enseignants (
    idEns INT AUTO_INCREMENT PRIMARY KEY,
    numEns VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    departement VARCHAR(100),
    grade VARCHAR(100),
    specialite VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_numEns (numEns)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABLE: cours (Courses)
-- =============================================================================
CREATE TABLE cours (
    idCours INT AUTO_INCREMENT PRIMARY KEY,
    codeCours VARCHAR(20) UNIQUE NOT NULL,
    intitule VARCHAR(200) NOT NULL,
    description TEXT,
    credits_ects INT,
    semestre INT,
    niveau VARCHAR(50),
    departement VARCHAR(100),
    prerequis VARCHAR(200),
    idEns_responsable INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idEns_responsable) REFERENCES enseignants(idEns) ON DELETE SET NULL,
    INDEX idx_codeCours (codeCours),
    INDEX idx_semestre (semestre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABLE: inscriptions (Enrollments)
-- =============================================================================
CREATE TABLE inscriptions (
    idInscription INT AUTO_INCREMENT PRIMARY KEY,
    idEtudiant INT NOT NULL,
    idCours INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    note DECIMAL(5,2),
    statut ENUM('inscrit', 'valide', 'abandonne') DEFAULT 'inscrit',
    FOREIGN KEY (idEtudiant) REFERENCES etudiants(idEtudiant) ON DELETE CASCADE,
    FOREIGN KEY (idCours) REFERENCES cours(idCours) ON DELETE CASCADE,
    UNIQUE KEY unique_inscription (idEtudiant, idCours),
    INDEX idx_etudiant (idEtudiant),
    INDEX idx_cours (idCours)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- INSERT TEST DATA
-- =============================================================================

-- Users (passwords are hashed: admin123/test123)
-- admin123 = $2y$10$92IXUNpkm1XQevnss7sHe.9ojlH5NjxaL4dP0/V10EPr7ZfulGUGm
-- test123 = $2y$10$JlH6f1nMToOtEV9a0y1gJOzApX1gYbBJjAPv0qOxXBH.mzU6FyJiy
INSERT INTO utilisateurs (email, password, prenom, role) VALUES
('admin@univ.edu', '$2y$10$92IXUNpkm1XQevnss7sHe.9ojlH5NjxaL4dP0/V10EPr7ZfulGUGm', 'Administrateur', 'admin'),
('prof.martin@univ.edu', '$2y$10$JlH6f1nMToOtEV9a0y1gJOzApX1gYbBJjAPv0qOxXBH.mzU6FyJiy', 'Jean', 'enseignant'),
('prof.dupont@univ.edu', '$2y$10$JlH6f1nMToOtEV9a0y1gJOzApX1gYbBJjAPv0qOxXBH.mzU6FyJiy', 'Marie', 'enseignant'),
('etudiant.ali@univ.edu', '$2y$10$JlH6f1nMToOtEV9a0y1gJOzApX1gYbBJjAPv0qOxXBH.mzU6FyJiy', 'Ali', 'etudiant'),
('etudiant.sophia@univ.edu', '$2y$10$JlH6f1nMToOtEV9a0y1gJOzApX1gYbBJjAPv0qOxXBH.mzU6FyJiy', 'Sophia', 'etudiant');

-- Teachers
INSERT INTO enseignants (numEns, nom, prenom, email, departement, grade, specialite) VALUES
('ENS001', 'Martin', 'Jean', 'prof.martin@univ.edu', 'Informatique', 'Professeur', 'Bases de Données'),
('ENS002', 'Dupont', 'Marie', 'prof.dupont@univ.edu', 'Informatique', 'Maître de conférences', 'Programmation Web'),
('ENS003', 'Bernard', 'Pierre', 'prof.bernard@univ.edu', 'Mathématiques', 'Professeur', 'Algèbre linéaire'),
('ENS004', 'Lefevre', 'Sophie', 'prof.lefevre@univ.edu', 'Informatique', 'Maître de conférences', 'Réseaux');

-- Students
INSERT INTO etudiants (numCarte, nom, prenom, email, tel, filiere, annee_entree, date_naissance) VALUES
('ETUD001', 'Ali', 'Ahmed', 'etudiant.ali@univ.edu', '06 12 34 56 78', 'SI', 2023, '2004-05-15'),
('ETUD002', 'Sophia', 'Léa', 'etudiant.sophia@univ.edu', '06 87 65 43 21', 'SI', 2023, '2004-08-20'),
('ETUD003', 'Lucas', 'Thomas', 'lucas.thomas@univ.edu', '06 11 22 33 44', 'BDGL', 2023, '2004-03-10'),
('ETUD004', 'Emma', 'Charlotte', 'emma.charlotte@univ.edu', '06 55 66 77 88', 'RSI', 2023, '2004-11-05'),
('ETUD005', 'Karim', 'Hassan', 'karim.hassan@univ.edu', '06 99 88 77 66', 'SI', 2024, '2005-02-14'),
('ETUD006', 'Nina', 'Julie', 'nina.julie@univ.edu', '06 44 33 22 11', 'BDGL', 2024, '2005-07-22');

-- Courses
INSERT INTO cours (codeCours, intitule, description, credits_ects, semestre, niveau, departement, prerequis, idEns_responsable) VALUES
('INFO101', 'Introduction à Python', 'Fondamentaux de la programmation Python', 3, 1, 'L1', 'Informatique', 'Aucun', 2),
('INFO102', 'Bases de données SQL', 'Conception et requêtes SQL', 4, 1, 'L1', 'Informatique', 'Aucun', 1),
('INFO201', 'Développement Web', 'HTML, CSS, PHP, JavaScript', 5, 2, 'L2', 'Informatique', 'INFO101', 2),
('INFO202', 'Architecture Réseaux', 'Principes des réseaux informatiques', 4, 2, 'L2', 'Informatique', 'Aucun', 4),
('MATH101', 'Algèbre Linéaire', 'Matrices, espaces vectoriels', 4, 1, 'L1', 'Mathématiques', 'Aucun', 3),
('BDGL101', 'Gestion de Projets', 'Méthodes agiles et SCRUM', 3, 1, 'L1', 'Informatique', 'Aucun', 1),
('RSI101', 'Sécurité Informatique', 'Principes de sécurité IT', 4, 2, 'L2', 'Informatique', 'INFO102', 4);

-- Enrollments (Students enrolled in courses)
INSERT INTO inscriptions (idEtudiant, idCours, statut) VALUES
(1, 1, 'valide'),
(1, 2, 'valide'),
(1, 3, 'inscrit'),
(2, 1, 'valide'),
(2, 2, 'inscrit'),
(3, 5, 'valide'),
(3, 6, 'valide'),
(4, 2, 'inscrit'),
(4, 7, 'inscrit'),
(5, 1, 'inscrit'),
(5, 4, 'inscrit'),
(6, 2, 'valide');

-- =============================================================================
-- VÉRIFICATION FINALE
-- =============================================================================
SELECT '✓ Base de données créée !' AS status;
SELECT CONCAT('✓ ', COUNT(*), ' utilisateurs créés') FROM utilisateurs;
SELECT CONCAT('✓ ', COUNT(*), ' étudiants créés') FROM etudiants;
SELECT CONCAT('✓ ', COUNT(*), ' enseignants créés') FROM enseignants;
SELECT CONCAT('✓ ', COUNT(*), ' cours créés') FROM cours;
SELECT CONCAT('✓ ', COUNT(*), ' inscriptions créées') FROM inscriptions;
