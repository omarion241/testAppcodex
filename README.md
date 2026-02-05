# Application de gestion de stock (PHP procédural + MySQL)

## Installation rapide
1. Importer `database.sql` dans MySQL.
2. Vérifier la configuration dans `config/db.php`.
3. Démarrer un serveur PHP local :
   ```bash
   php -S 0.0.0.0:8000 -t .
   ```
4. Se connecter avec le compte demo : `admin@demo.com / Admin@123`.

## Fonctionnalités (formulaires, PHP, requêtes, exemples)

### 1) Authentification
- **Formulaire** : `auth/login.php`
  - Champs : email, mot de passe.
- **PHP** : validation des champs, `mysqli_real_escape_string`, session PHP.
- **Requêtes MySQL** :
  ```sql
  SELECT id, nom, mot_de_passe FROM utilisateur WHERE email = '...';
  ```
- **Exemple de données** :
  ```sql
  INSERT INTO utilisateur (nom, email, mot_de_passe)
  VALUES ('Admin', 'admin@demo.com', '<hash>');
  ```

### 2) Gestion des entrepôts
- **Formulaires** :
  - Ajout : `entrepot/add.php` (nom, adresse)
  - Mise à jour/suppression : `entrepot/list.php`
- **Requêtes MySQL** :
  ```sql
  INSERT INTO entrepot (nom, adresse) VALUES ('...', '...');
  UPDATE entrepot SET nom='...', adresse='...' WHERE id=...;
  DELETE FROM entrepot WHERE id=...;
  SELECT * FROM entrepot ORDER BY id DESC;
  ```
- **Exemples** :
  ```sql
  INSERT INTO entrepot (nom, adresse)
  VALUES ('Entrepôt Nord', 'Zone Industrielle A');
  ```

### 3) Gestion des articles
- **Formulaires** :
  - Ajout : `article/add.php`
  - Mise à jour/suppression : `article/list.php`
- **Champs** : référence, désignation, catégorie, prix achat/vente, stock minimum, unité.
- **Requêtes MySQL** :
  ```sql
  INSERT INTO article (reference, designation, categorie, prix_achat, prix_vente, stock_minimum, unite)
  VALUES ('REF-001', 'Article A', 'Catégorie 1', 10.00, 15.00, 5, 'pcs');
  SELECT * FROM article ORDER BY id DESC;
  ```
- **Exemples** :
  ```sql
  INSERT INTO article (reference, designation, categorie, prix_achat, prix_vente, stock_minimum, unite)
  VALUES ('REF-002', 'Article B', 'Catégorie 2', 20.00, 30.00, 3, 'pcs');
  ```

### 4) Gestion du stock
- **Formulaire** : `stock/mouvement.php`
  - Types : entrée, sortie, transfert.
- **Requêtes MySQL** :
  ```sql
  INSERT INTO mouvement_stock (article_id, entrepot_source, entrepot_destination, type, quantite, date_mouvement)
  VALUES (1, NULL, 1, 'entree', 10, '2024-01-01');

  UPDATE stock SET quantite = quantite + 10 WHERE entrepot_id=1 AND article_id=1;
  ```
- **Exemples** :
  ```sql
  INSERT INTO stock (entrepot_id, article_id, quantite) VALUES (1, 1, 50);
  ```

### 5) Achats
- **Formulaire** : `achat/add.php`
  - Fournisseur, BL, statut paiement, entrepôt, montant payé, détails achats.
- **Requêtes MySQL** :
  ```sql
  INSERT INTO achat (fournisseur_id, bl, date_achat, total, statut_paiement)
  VALUES (1, 'BL-001', CURDATE(), 200.00, 'especes');

  INSERT INTO detail_achat (achat_id, article_id, quantite, prix_unitaire)
  VALUES (1, 1, 10, 10.00);
  ```
- **Exemples** :
  ```sql
  INSERT INTO fournisseur (nom) VALUES ('Fournisseur Alpha');
  ```

### 6) Ventes
- **Formulaire** : `vente/add.php`
  - Client facultatif, entrepôt, statut paiement, montant payé, détails ventes.
- **Requêtes MySQL** :
  ```sql
  INSERT INTO vente (client_id, date_vente, total, statut_paiement)
  VALUES (1, CURDATE(), 120.00, 'especes');

  INSERT INTO detail_vente (vente_id, article_id, quantite, prix_unitaire)
  VALUES (1, 1, 4, 15.00);
  ```
- **Exemples** :
  ```sql
  INSERT INTO client (nom) VALUES ('Client Beta');
  ```

### 7) Paiements et dettes
- **Gérés lors des achats/ventes** : `achat/add.php`, `vente/add.php`.
- **Requêtes MySQL** :
  ```sql
  INSERT INTO paiement (type, vente_id, montant, date_paiement)
  VALUES ('especes', 1, 120.00, CURDATE());

  INSERT INTO dette (type, client_id, montant, date_creation, statut)
  VALUES ('client', 1, 50.00, CURDATE(), 'ouverte');
  ```
- **Exemples** :
  ```sql
  INSERT INTO dette (type, fournisseur_id, montant, date_creation, statut)
  VALUES ('fournisseur', 1, 80.00, CURDATE(), 'ouverte');
  ```

### 8) Rapports
- **Stock par entrepôt + alertes** : `rapport/stock.php`.
- **Ventes journalières, achats par période, bénéfice/perte** : `rapport/vente.php`.
- **Requêtes MySQL** (extrait) :
  ```sql
  SELECT e.nom AS entrepot, a.designation, s.quantite, a.stock_minimum
  FROM stock s
  JOIN entrepot e ON e.id = s.entrepot_id
  JOIN article a ON a.id = s.article_id;
  ```

## Structure des fichiers
```
/config/db.php
/auth/login.php
/auth/logout.php
/includes/header.php
/includes/footer.php
/entrepot/add.php
/entrepot/list.php
/article/add.php
/article/list.php
/achat/add.php
/vente/add.php
/stock/mouvement.php
/rapport/stock.php
/rapport/vente.php
```
