# Schéma base existante (ID93677_vivat)

Référence pour la base fournie par le chef de projet (`ID93677_vivat.sql`). Ces tables sont **alignées** dans le projet via la migration `2024_01_01_000000_create_legacy_site_tables.php`. Le **pipeline** (scraping + génération d’articles IA) reste inchangé et utilise ses propres tables (sources, rss_*, enriched_items, clusters, **articles**, etc.).

---

## Tables de la base existante

### tbl_cont_pg — Articles / pages de contenu (site)

| Colonne           | Type         | Description |
|-------------------|--------------|-------------|
| contID            | int(11) PK   | Id article (AUTO_INCREMENT) |
| contTitle         | varchar(250) | Titre |
| contDesc          | text         | Description / résumé |
| contContent       | longtext     | Contenu HTML |
| contKeywords      | text         | Mots-clés |
| contImgs          | varchar(255) | Images (chemin ou liste) |
| contImgsAlt       | text         | Textes alternatifs images |
| contLang          | char(10)     | Langue (ex. fr) |
| contRef1, contRef2, contRef3 | varchar(200) | Références (liens vers tbl_ref, ex. catégories) |
| online            | int(11)      | En ligne (0/1) |
| contDate          | date         | Date de l’article |
| contPgs           | int(11)      | Nombre de pages / type page |
| meta_title        | varchar(255) | SEO titre |
| meta_desc         | varchar(255) | SEO description |
| creation          | datetime     | Création |
| modification      | datetime     | Dernière modification |
| contPublishDate   | int(11)      | Date de publication (timestamp ou id) |

**Index** : `TBL_CONT_PG_IDX_PG_CONTENT` sur (contID, contTitle, contLang, contRef1, contRef2, contRef3, online, contDate, contPgs).

---

### tbl_ref — Catégories / références éditoriales

| Colonne   | Type         | Description |
|-----------|--------------|-------------|
| id        | int(11) PK   | Id (AUTO_INCREMENT) |
| refID     | int(11)      | Parent (0 = racine) |
| refTitle  | varchar(255) | Libellé (ex. « Au quotidien », « Événement ») |
| refLang   | varchar(2)   | Langue |
| refType   | varchar(255) | Type (optionnel) |
| refUrl    | varchar(255) | URL / slug (ex. quotidien/, energie/) |
| meta_title| varchar(255) | SEO |
| meta_desc | varchar(255) | SEO |
| meta_kw   | varchar(255) | Mots-clés SEO |
| top_desc  | varchar(255) | Description courte |

Structure hiérarchique : `refID = 0` pour les racines, `refID = id` d’un parent pour les enfants.

---

### tbl_usr — Utilisateurs (site)

| Colonne          | Type         | Description |
|------------------|--------------|-------------|
| usrID            | int(11) PK   | Id (AUTO_INCREMENT) |
| usrNickName      | varchar(255) | Login / pseudo |
| usrPw            | varchar(255) | Mot de passe (hash) |
| usrRealLastName  | varchar(255) | Nom |
| usrRealFirstName | varchar(255) | Prénom |
| usrEmail         | varchar(255) | Email |
| usrType          | tinyint(1)   | Type / rôle (1 = admin?, 2 = éditeur?) |

---

### logs

| Colonne | Type         | Description |
|---------|--------------|-------------|
| id      | int(11)      | (pas de PK dans le dump) |
| user    | tinyint(2)   | Référence utilisateur |
| action  | varchar(6)   | add, mod, … |
| module  | varchar(12)  | ex. articles |
| detail  | varchar(255) | Détail (ex. titre article) |
| when    | datetime     | Date/heure |

Table MyISAM dans le dump (conservé en migration pour compatibilité).

---

### cloaked_ip

Table technique (IP / bots). Structure alignée pour import complet du dump si besoin.

---

## Mapping pipeline vs base existante

| Rôle                | Pipeline (inchangé)     | Base existante (site) |
|---------------------|-------------------------|------------------------|
| Articles            | `articles` (UUID)       | `tbl_cont_pg` (contID INT) |
| Catégories          | `categories` (UUID)     | `tbl_ref` (id INT, hiérarchie refID) |
| Utilisateurs        | `users` (Laravel)       | `tbl_usr` (usrID INT) |

- **Pipeline** : sources → fetch RSS → enrichissement → clusters → **génération d’articles IA** → table `articles` (slug, excerpt, content, category_id, cluster_id, status, etc.). À ne pas modifier pour le scraping et la génération.
- **Site / contenu existant** : préchargé depuis le dump dans `tbl_cont_pg`, `tbl_ref`, `tbl_usr`. La migration crée les tables vides ; les données s’importent depuis `ID93677_vivat.sql` (voir ci‑dessous).

---

## Import des données

1. **Créer les tables** : `php artisan migrate` (crée notamment les tables legacy + pipeline).
2. **Importer les données existantes** :  
   - Soit importer tout le dump dans une base dédiée puis copier uniquement les INSERT vers `tbl_cont_pg`, `tbl_ref`, `tbl_usr` (et éventuellement `logs`, `cloaked_ip`) dans la base du projet.  
   - Soit extraire du fichier `ID93677_vivat.sql` les blocs `INSERT INTO tbl_cont_pg ...`, `INSERT INTO tbl_ref ...`, `INSERT INTO tbl_usr ...` (et logs, cloaked_ip si besoin) et les exécuter sur la base du projet **après** avoir lancé les migrations (les tables doivent exister et être vides ou compatibles).

Les tables de backup/copie du dump (`tbl_cont_pg_backup`, `tbl_cont_pg_copy`, etc.) ne sont pas reprises dans la migration ; seules les tables principales ci‑dessus le sont.

---

*Référence : dump phpMyAdmin `ID93677_vivat.sql`, migration `2024_01_01_000000_create_legacy_site_tables.php`.*
