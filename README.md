<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Vivat — Développement local

### Vider le cache (sans PHP installé en local)

Si le projet tourne avec **Docker** (e.g. `docker compose up`), PHP s’exécute dans le conteneur. Pas besoin d’avoir PHP installé sur ta machine.

1. Ouvrir un terminal.
2. Aller dans le dossier du projet :  
   `cd "/Users/manalboulahya/Documents/EHB - 3/Stage/Vivat-1"`  
   (ou le chemin correspondant sur ta machine.)
3. Lancer :  
   **`docker compose exec app php artisan cache:clear`**

Si tu n’utilises pas Docker (pas de `docker compose up`), soit tu installes PHP (php.net, MAMP, XAMPP, etc.), soit le cache est vidé sur l’environnement partagé (même serveur / même Redis) par quelqu’un qui y a accès.

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Développement local (Docker)

Si tu n'as pas PHP installé en local, utilise Docker pour les commandes Laravel.

**Vider le cache** (depuis la racine du projet) :
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
```

En une seule ligne :
```bash
docker compose exec app php artisan cache:clear && docker compose exec app php artisan config:clear && docker compose exec app php artisan view:clear
```

Assure-toi que les conteneurs tournent (`docker compose up -d`) avant d’exécuter ces commandes.

### Après un git pull

Après avoir récupéré les dernières modifications, **exécuter les migrations** pour que la base (ex. colonne `language` sur `articles`) soit à jour :

```bash
docker compose exec app php artisan migrate
```

Sans Docker (PHP en local) : `php artisan migrate`

Si tu vois une erreur *Unknown column 'language' in 'where clause'*, c’est que cette migration n’a pas encore été jouée.

### Pourquoi je vois moins de pages d’articles qu’un collègue ?

La liste d’articles est paginée à **12 par page**. Le nombre de pages dépend donc du nombre d’articles **dans ta base locale** (7 pages ≈ 84 articles, 18 pages ≈ 216 articles). Chaque poste a sa propre base ; ce n’est pas un bug du code.

**Pour avoir (au moins) un jeu de données complet côté seed :**

```bash
docker compose exec app php artisan db:seed
```

Cela crée les 9 catégories, les sous-catégories et environ **17 articles par catégorie** (~153 articles, une douzaine de pages). Ensuite, optionnel :

```bash
docker compose exec app php artisan db:seed --class=AdditionalArticlesSeeder
docker compose exec app php artisan db:seed --class=HomeArticlesSeeder
```

**Pour avoir exactement la même base qu’un collègue** (même nombre d’articles, mêmes données) : il faut **partager la base**. (Utilisateur MySQL : `vivat`, mot de passe : `vivat_secret`.)

- **Sur la machine qui a la base à jour** (celle qui a 18 pages), exporter :

```bash
docker compose exec mysql mysqldump -u vivat -pvivat_secret vivat > vivat_dump.sql
```

- **Sur la machine du collègue** : s’assurer que la base `vivat` existe (sinon la créer via phpMyAdmin ou un utilisateur ayant les droits), puis importer le fichier reçu (`vivat_dump.sql`) :

```bash
docker compose exec -T mysql mysql -u vivat -pvivat_secret vivat < vivat_dump.sql
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
