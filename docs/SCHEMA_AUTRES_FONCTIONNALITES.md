# Schéma DB — Autres fonctionnalités du site (Vivat)

> **À jour** : schéma pour les fonctionnalités hors pipeline (utilisateurs, rôles, contribution, paiement, personnalisation, newsletter).  
> **Ce schéma peut encore changer** avant implémentation.

Côté pipeline (génération d’articles), le schéma est dans `docs/MIGRATIONS_REFERENCE.md` (UUID, tables sources, rss_feeds, rss_items, enriched_items, clusters, articles générés, etc.). Ici : **users**, **categories** (éditorial site), **sub_categories**, **articles** (site / rédaction), **contributed_articles**, **payments**, **user_categories** (centres d’intérêt), **refunds**.

**Note** : ce schéma utilise **INT AUTO_INCREMENT** pour les clés. Une décision à prendre plus tard : garder INT pour ces tables ou aligner sur UUID comme le pipeline.

---

## Tables (SQL de référence)

### users
- `id` INT PRIMARY KEY AUTO_INCREMENT  
- `name` VARCHAR(150) NOT NULL  
- `email` VARCHAR(150) UNIQUE NOT NULL  
- `password` VARCHAR(255) NOT NULL  
- `role` ENUM('admin', 'contributor') NOT NULL DEFAULT 'contributor'  
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP  
- `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP  

### categories (éditorial site)
- `id` INT PRIMARY KEY AUTO_INCREMENT  
- `name` VARCHAR(100) NOT NULL  
- `slug` VARCHAR(100) UNIQUE NOT NULL  
- `description` TEXT  
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP  

### sub_categories
- `id` INT PRIMARY KEY AUTO_INCREMENT  
- `category_id` INT NOT NULL → FK categories(id)  
- `name` VARCHAR(100) NOT NULL  
- `slug` VARCHAR(100) UNIQUE NOT NULL  
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP  

### articles (contenu site / rédaction)
- `id` INT PRIMARY KEY AUTO_INCREMENT  
- `title` VARCHAR(255) NOT NULL  
- `slug` VARCHAR(255) UNIQUE NOT NULL  
- `content` LONGTEXT NOT NULL  
- `excerpt` TEXT  
- `reading_time` INT  
- `category_id` INT NOT NULL → FK categories(id)  
- `sub_category_id` INT → FK sub_categories(id)  
- `author_id` INT → FK users(id)  
- `status` ENUM('published', 'archived') DEFAULT 'published'  
- `published_at` DATETIME  
- `updated_at` DATETIME  
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP  

### contributed_articles
- `id` INT PRIMARY KEY AUTO_INCREMENT  
- `user_id` INT NOT NULL → FK users(id)  
- `title` VARCHAR(255) NOT NULL  
- `content` LONGTEXT NOT NULL  
- `excerpt` TEXT  
- `category_id` INT NOT NULL → FK categories(id)  
- `sub_category_id` INT → FK sub_categories(id)  
- `status` ENUM('pending', 'approved', 'rejected', 'published') DEFAULT 'pending'  
- `article_id` INT → FK articles(id) (article publié une fois approuvé)  
- `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP  
- `moderated_at` DATETIME  

### payments
- `id` INT PRIMARY KEY AUTO_INCREMENT  
- `user_id` INT NOT NULL → FK users(id)  
- `contributed_article_id` INT NOT NULL → FK contributed_articles(id)  
- `amount` DECIMAL(8,2) NOT NULL  
- `currency` VARCHAR(10) DEFAULT 'EUR'  
- `status` ENUM('pending', 'processing', 'paid', 'failed', 'refunded') DEFAULT 'pending'  
- `payment_intent_id` VARCHAR(255) (Stripe)  
- `paid_at` DATETIME  
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP  

### user_categories (centres d’intérêt / personnalisation)
- `id` INT PRIMARY KEY AUTO_INCREMENT  
- `user_id` INT → FK users(id) (NULL = visiteur identifié par cookie)  
- `category_id` INT NOT NULL → FK categories(id)  
- `cookie_id` VARCHAR(255) (pour visiteurs non connectés)  
- `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP  

### refunds
- `id` INT PRIMARY KEY AUTO_INCREMENT  
- `payment_id` INT NOT NULL → FK payments(id)  
- `amount` DECIMAL(8,2) NOT NULL  
- `refunded_at` DATETIME DEFAULT CURRENT_TIMESTAMP  

---

## Liens avec le contexte fonctionnel

| Table | Fonctionnalité (section 2 CONTEXTE_PROJET) |
|-------|--------------------------------------------|
| users | 1) Accès et rôles (admin, contributeur) |
| categories, sub_categories | 3) Navigation et structure éditoriale |
| articles | 4) Consultation, 3) Pages Hub |
| contributed_articles | 10) Contribution, 11) Publication ponctuelle |
| payments, refunds | 11) Paiement / remboursement |
| user_categories | 2) Personnalisation et centres d’intérêt, 9) Newsletter (thèmes) |

---

## À clarifier / peut encore changer

- Alignement ou non avec le pipeline : une seule table `articles` (pipeline + site) ou deux (articles générés vs articles rédaction) ? Le pipeline actuel a déjà une table `articles` (UUID) dans `MIGRATIONS_REFERENCE.md`.
- Rôles : ici `admin` et `contributor` ; dans le contexte on a aussi Visiteur (non connecté) et Modérateur (à fusionner avec admin ?).
- Statuts contributed_articles : pending, approved, rejected, published ; lien avec article_id quand approuvé/publié.
- INT vs UUID pour ces tables si on veut un schéma unifié avec le pipeline.

---

*Référence : CONTEXTE_PROJET.md sections 2 (fonctionnalités) et 3 (stack). Ce schéma peut encore changer.*
