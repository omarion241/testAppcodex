-- Script de base de données : gestion_stock
CREATE DATABASE IF NOT EXISTS gestion_stock CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE gestion_stock;

CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin'
) ENGINE=InnoDB;

CREATE TABLE entrepot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    adresse VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE article (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(100) NOT NULL,
    designation VARCHAR(150) NOT NULL,
    categorie VARCHAR(100),
    prix_achat DECIMAL(10,2) NOT NULL,
    prix_vente DECIMAL(10,2) NOT NULL,
    stock_minimum INT DEFAULT 0,
    unite VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entrepot_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT DEFAULT 0,
    FOREIGN KEY (entrepot_id) REFERENCES entrepot(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE client (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    telephone VARCHAR(50),
    email VARCHAR(150),
    adresse VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE fournisseur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    telephone VARCHAR(50),
    email VARCHAR(150),
    adresse VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fournisseur_id INT NOT NULL,
    bl VARCHAR(120),
    date_achat DATE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    statut_paiement VARCHAR(50) NOT NULL,
    FOREIGN KEY (fournisseur_id) REFERENCES fournisseur(id)
) ENGINE=InnoDB;

CREATE TABLE detail_achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    achat_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (achat_id) REFERENCES achat(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES article(id)
) ENGINE=InnoDB;

CREATE TABLE vente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NULL,
    date_vente DATE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    statut_paiement VARCHAR(50) NOT NULL,
    FOREIGN KEY (client_id) REFERENCES client(id)
) ENGINE=InnoDB;

CREATE TABLE detail_vente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vente_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (vente_id) REFERENCES vente(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES article(id)
) ENGINE=InnoDB;

CREATE TABLE mouvement_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    entrepot_source INT NULL,
    entrepot_destination INT NULL,
    type VARCHAR(50) NOT NULL,
    quantite INT NOT NULL,
    date_mouvement DATE NOT NULL,
    FOREIGN KEY (article_id) REFERENCES article(id),
    FOREIGN KEY (entrepot_source) REFERENCES entrepot(id),
    FOREIGN KEY (entrepot_destination) REFERENCES entrepot(id)
) ENGINE=InnoDB;

CREATE TABLE paiement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    achat_id INT NULL,
    vente_id INT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_paiement DATE NOT NULL,
    FOREIGN KEY (achat_id) REFERENCES achat(id),
    FOREIGN KEY (vente_id) REFERENCES vente(id)
) ENGINE=InnoDB;

CREATE TABLE dette (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    client_id INT NULL,
    fournisseur_id INT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_creation DATE NOT NULL,
    statut VARCHAR(50) DEFAULT 'ouverte',
    FOREIGN KEY (client_id) REFERENCES client(id),
    FOREIGN KEY (fournisseur_id) REFERENCES fournisseur(id)
) ENGINE=InnoDB;

-- Données d'exemple
INSERT INTO utilisateur (nom, email, mot_de_passe) VALUES
('Admin', 'admin@demo.com', '$2y$12$ZqIZF71o855YOpqSdFjAQe6tMiy6elea178y6zjMxpVUVMtnpYpxK');

INSERT INTO entrepot (nom, adresse) VALUES
('Entrepôt Nord', 'Zone Industrielle A'),
('Entrepôt Sud', 'Zone Industrielle B');

INSERT INTO article (reference, designation, categorie, prix_achat, prix_vente, stock_minimum, unite) VALUES
('REF-001', 'Article A', 'Catégorie 1', 10.00, 15.00, 5, 'pcs'),
('REF-002', 'Article B', 'Catégorie 2', 20.00, 30.00, 3, 'pcs');

INSERT INTO stock (entrepot_id, article_id, quantite) VALUES
(1, 1, 50),
(1, 2, 20),
(2, 1, 15);

INSERT INTO fournisseur (nom, telephone) VALUES
('Fournisseur Alpha', '0600000000');

INSERT INTO client (nom, telephone) VALUES
('Client Beta', '0700000000');

INSERT INTO achat (fournisseur_id, bl, date_achat, total, statut_paiement) VALUES
(1, 'BL-001', CURDATE(), 200.00, 'especes');

INSERT INTO detail_achat (achat_id, article_id, quantite, prix_unitaire) VALUES
(1, 1, 10, 10.00),
(1, 2, 5, 20.00);

INSERT INTO vente (client_id, date_vente, total, statut_paiement) VALUES
(1, CURDATE(), 120.00, 'especes');

INSERT INTO detail_vente (vente_id, article_id, quantite, prix_unitaire) VALUES
(1, 1, 4, 15.00),
(1, 2, 2, 30.00);

INSERT INTO mouvement_stock (article_id, entrepot_source, entrepot_destination, type, quantite, date_mouvement) VALUES
(1, NULL, 1, 'entree', 10, CURDATE()),
(1, 1, NULL, 'sortie', 4, CURDATE());

INSERT INTO paiement (type, achat_id, vente_id, montant, date_paiement) VALUES
('especes', 1, NULL, 200.00, CURDATE()),
('especes', NULL, 1, 120.00, CURDATE());

INSERT INTO dette (type, client_id, fournisseur_id, montant, date_creation, statut) VALUES
('client', 1, NULL, 50.00, CURDATE(), 'ouverte'),
('fournisseur', NULL, 1, 80.00, CURDATE(), 'ouverte');
