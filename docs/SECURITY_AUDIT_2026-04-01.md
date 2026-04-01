# Audit de sécurité Vivat

Date : 1 avril 2026  
Projet : Vivat  
Périmètre : backend web, API publique, espace contributeur, admin Filament, paiements Stripe, formulaires publics

## Objectif

Ce document résume :

- comment l’audit a été réalisé
- quelles surfaces d’attaque ont été vérifiées
- quelles protections existaient déjà
- quelles protections ont été ajoutées
- ce qu’il reste encore à renforcer

L’approche utilisée est une approche défensive. Le but n’était pas de lancer des attaques réelles contre l’application en production, mais de relire le code comme le ferait un attaquant pour identifier les points faibles plausibles, puis de corriger les plus importants.

## Méthode d’audit

L’audit a été fait en plusieurs étapes.

### 1. Cartographie des points d’entrée

Les routes et zones exposées ont été relues pour identifier :

- les endpoints publics
- les endpoints authentifiés
- les endpoints sensibles liés aux paiements
- les endpoints d’administration
- les formulaires potentiellement abusables par des bots

Fichiers principalement inspectés :

- `routes/web.php`
- `routes/api.php`
- `app/Providers/AppServiceProvider.php`
- `app/Http/Controllers/Web/AuthController.php`
- `app/Http/Controllers/Web/NewsletterController.php`
- `app/Http/Controllers/Web/SearchController.php`
- `app/Http/Controllers/Api/NewsletterController.php`
- `app/Http/Controllers/Api/PaymentController.php`
- vues des formulaires publics

### 2. Recherche des zones à risque SQL

Une relecture ciblée a été faite sur les appels potentiellement sensibles :

- `DB::raw`
- `selectRaw`
- `whereRaw`
- `orderByRaw`
- `statement`
- `unprepared`

Objectif :

- vérifier s’il y avait des concaténations SQL directes dangereuses
- distinguer les requêtes réellement risquées des usages acceptables avec bindings

Conclusion :

- aucune injection SQL évidente n’a été trouvée dans les zones inspectées
- les requêtes sensibles utilisent majoritairement Eloquent ou des bindings SQL
- certains `orderByRaw` et `whereRaw` existent, mais avec paramètres bindés, ce qui réduit fortement le risque

### 3. Vérification brute force et abus d’endpoints

Les protections contre les abus ont été relues :

- throttling login
- throttling register
- throttling newsletter
- throttling paiements contributeur
- throttling API générique

Objectif :

- repérer les endpoints encore ouverts aux abus
- harmoniser le throttling web et API

### 4. Vérification anti-bot

Les formulaires publics ont été inspectés pour voir :

- s’ils avaient seulement une validation serveur
- ou s’ils avaient aussi une protection anti-bot légère

Conclusion initiale :

- pas de CAPTCHA
- pas de honeypot
- donc les formulaires publics restaient faciles à cibler par des bots simples

### 5. Vérification des comportements dangereux

Une attention particulière a été portée aux comportements suivants :

- logout via `GET`
- endpoints de recherche ouverts sans limite
- endpoints publics API sans throttle dédié
- reset password trop facilement spammable

## Sécurité déjà présente avant cet audit

Avant cette passe, plusieurs protections existaient déjà.

### Authentification et mots de passe

- les mots de passe sont hashés côté Laravel
- règles de mot de passe renforcées :
  - minimum 12 caractères
  - majuscule
  - minuscule
  - chiffre
  - symbole
- changement de mot de passe ajouté dans l’espace contributeur
- flow complet de réinitialisation du mot de passe ajouté

### Contrôle d’accès

- routes contributeur protégées par `auth` + rôles
- routes admin API protégées par `auth:sanctum` + `role:admin`
- plusieurs actions sensibles vérifient déjà l’appartenance utilisateur/ressource

### Paiements Stripe

- throttling sur les endpoints de paiement contributeur
- protections contre certaines confirmations invalides
- statuts de paiement plus propres, y compris `abandoned`
- meilleure gestion des intents Stripe incomplets

### Workflow éditorial

- blocage de suppression pour certaines soumissions sensibles
- meilleure séparation entre brouillons, soumissions, articles publiés et remboursements

## Renforcements ajoutés pendant cet audit

Les changements suivants ont été ajoutés.

### 1. Throttling supplémentaire

Nouveaux rate limiters ajoutés dans `app/Providers/AppServiceProvider.php` :

- `password-reset-link`
- `search-suggestions`
- `api-auth-login`
- `api-auth-register`
- `api-newsletter-subscribe`
- `api-newsletter-actions`

Objectif :

- limiter les tentatives répétées
- réduire le brute force
- réduire le spam bot
- réduire l’abus de certaines API publiques

### 2. Suppression du logout via GET

Le `GET /logout` a été retiré dans `routes/web.php`.

Pourquoi :

- un logout via GET est inutilement permissif
- cela peut provoquer des déconnexions involontaires via liens ou contenus externes

Le logout reste disponible via `POST`, ce qui est le comportement attendu.

### 3. Throttling sur les suggestions de recherche

La route `/search/suggestions` a été mise sous throttle dédié.

Pourquoi :

- c’est un endpoint facile à spammer
- il peut être utilisé pour faire du scraping interne ou charger inutilement la base

### 4. Honeypot anti-bot sur les formulaires publics

Un champ caché `company_website` a été ajouté sur :

- l’inscription
- la newsletter
- le mot de passe oublié

Et une vérification serveur a été ajoutée dans :

- `Web\AuthController`
- `Web\NewsletterController`

Pourquoi :

- beaucoup de bots simples remplissent tous les champs disponibles
- ce type de piège est léger, discret et ne dégrade pas l’expérience utilisateur normale

### 5. Throttling sur les endpoints API publics

Les routes suivantes ont été protégées :

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/newsletter/subscribe`
- `POST /api/newsletter/unsubscribe`
- `GET /api/newsletter/confirm`

Pourquoi :

- les protections existaient surtout côté web
- il fallait aligner les endpoints API publics pour éviter un contournement par API directe

### 6. Centralisation de la stratégie sécurité

Une configuration dédiée a été ajoutée dans `config/security.php`.

Objectif :

- centraliser les règles de sécurité applicatives
- éviter de disperser la logique de protection dans plusieurs fichiers sans point de référence
- rendre les futures extensions plus propres

Cette configuration contient actuellement :

- l’activation locale / production de la protection anti-bot
- la liste des user-agents explicitement bloqués
- les messages utilisés par la couche de défense

### 7. Middleware anti-bot ciblé

Un middleware dédié a été ajouté pour filtrer les user-agents manifestement automatisés ou offensifs.

Objectif :

- bloquer certains outils connus de scraping ou d’attaque triviale
- ajouter une couche de protection légère avant la logique métier
- rester prudent pour éviter les faux positifs

Le blocage vise des agents clairement suspects comme :

- `curl`
- `wget`
- `python-requests`
- `sqlmap`
- `nikto`
- `masscan`
- `nmap`
- `zgrab`

Le middleware a été appliqué aux surfaces publiques les plus exposées :

- auth web
- auth API
- newsletter web
- newsletter API
- suggestions de recherche
- mot de passe oublié

### 8. Deuxième passe sur les endpoints admin puissants

Une deuxième couche de throttling a été ajoutée sur les endpoints admin les plus sensibles.

Trois familles ont été distinguées :

- `admin-pipeline-actions`
- `admin-moderation-actions`
- `admin-financial-actions`

Objectif :

- éviter les doubles déclenchements accidentels
- limiter les boucles de dispatch coûteuses
- réduire les risques d’abus sur les opérations les plus puissantes

Endpoints concernés :

- génération d’articles
- génération asynchrone
- publication d’article
- fetch RSS
- enrichissement IA
- sélection de sujets
- export CSV tendances
- analyse tendances
- seed d’articles home
- approbation / rejet de soumissions
- remboursements admin

### 9. Journalisation sécurité dédiée

Un canal de log dédié `security` a été ajouté.

Objectif :

- séparer les événements de sécurité des logs applicatifs généraux
- rendre les incidents plus faciles à repérer
- conserver une vraie piste d’audit

Événements désormais journalisés explicitement :

- user-agents suspects bloqués
- webhook Stripe invalide ou mal signé
- webhook Stripe reçu pour un paiement inconnu
- tentatives incohérentes ou interdites sur certains paiements
- erreurs de vérification Stripe au moment de la confirmation

## Fichiers modifiés dans cette passe

- `app/Providers/AppServiceProvider.php`
- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/Web/AuthController.php`
- `app/Http/Controllers/Web/NewsletterController.php`
- `resources/views/site/register.php`
- `resources/views/site/forgot_password.php`
- `resources/views/site/layout.php`

## Risques couverts par cette passe

### Brute force

Mieux couvert sur :

- login web
- login API
- register web
- register API
- reset password

### Spam bots

Mieux couvert sur :

- formulaire newsletter
- formulaire d’inscription
- formulaire mot de passe oublié

### Abus d’endpoints publics

Mieux couvert sur :

- suggestions de recherche
- newsletter API
- auth API

### Abus d’endpoints admin puissants

Mieux couvert sur :

- déclenchements pipeline
- génération de contenu
- modération éditoriale
- remboursements

### Détection et traçabilité

Mieux couvert sur :

- incidents Stripe suspects
- blocages anti-bot
- tentatives de paiement incohérentes

### SQL injection

Analyse réalisée, mais aucun correctif majeur nécessaire n’a été appliqué pendant cette passe car aucune injection SQL directe évidente n’a été trouvée dans les zones inspectées.

## Ce qui reste encore à améliorer

L’audit montre que la sécurité est meilleure, mais pas encore “terminée”.

### Priorité haute

- ajouter des protections plus fines sur certains endpoints admin du pipeline IA
- renforcer la journalisation des événements de sécurité
- revoir certains endpoints API contributeur pour vérifier les transitions invalides
- traiter complètement les demandes de dépublication côté admin

### Priorité moyenne

- ajouter éventuellement un CAPTCHA si le site subit un vrai trafic bot
- renforcer encore les protections autour des remboursements et cas Stripe rares
- ajouter des alertes ou logs plus lisibles pour les abus répétés

### Priorité basse

- revue plus large de tous les contrôleurs API un par un
- audit plus poussé des comportements de recherche et scraping externe

## Conclusion

Le site n’était pas “ouvert”, mais plusieurs zones publiques restaient encore trop faciles à abuser :

- formulaires publics
- auth API
- newsletter API
- suggestions de recherche
- logout via GET

Après cette passe :

- le brute force est mieux limité
- les bots simples sont davantage freinés
- les endpoints publics sont plus homogènes
- certaines mauvaises pratiques ont été retirées

La sécurité globale est donc meilleure, mais il reste encore une deuxième passe utile sur :

- les endpoints admin sensibles
- le pipeline IA
- la journalisation sécurité
- certains cas limites métier

## Résumé simple

Ce qui a été fait :

- vérification des routes et contrôleurs sensibles
- recherche de risques SQL et abuse cases
- ajout de throttling ciblé
- suppression du logout via GET
- ajout d’un honeypot anti-bot
- alignement de la sécurité entre web et API

Ce qu’il reste :

- protections admin plus fines
- meilleure observabilité sécurité
- deuxième passe sur les endpoints les plus puissants
