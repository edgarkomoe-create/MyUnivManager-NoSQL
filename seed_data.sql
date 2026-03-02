-- seed_data.sql
USE universite;

INSERT INTO etudiants (num_etudiant, nom, prenom, email, filiere, annee_entree)
VALUES
('E001','Dupont','Jean','jean.dupont@example.com','Informatique',2022),
('E002','Martin','Claire','claire.martin@example.com','Mathématiques',2021);
