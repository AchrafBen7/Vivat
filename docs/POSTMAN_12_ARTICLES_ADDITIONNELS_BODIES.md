# 12 articles additionnels Bodies Postman

Bodies complets pour **POST** `http://localhost:8000/api/articles` afin de remplir la section « Dernières actualités » (12 cartes).

**Headers** : `Accept: application/json`, `Content-Type: application/json`, `Authorization: Bearer {{token}}`

**Workflow** : pour chaque article, 1) **POST** `/api/articles` avec le body → récupérer l’`id` → 2) **POST** `/api/articles/{id}/publish` pour publier.

---

## Récupérer les `category_id`

**GET** `http://localhost:8000/api/public/categories` pour obtenir les UUID des catégories. Remplace `{{category_id}}` dans chaque body par l’UUID correspondant au slug indiqué.

| Slug catégorie   | Champ à remplacer |
|------------------|-------------------|
| `environnement`  | Article 1, 6      |
| `societe`        | Article 2, 4      |
| `au-quotidien`   | Article 3         |
| `politique`      | Article 5         |
| `sante`          | Article 7         |
| `technologie`    | Article 8         |
| `transport`      | Article 9         |
| `habitat`        | Article 10        |
| `biodiversite`   | Article 11        |
| `economie`       | Article 12        |

---

## Article 1 Bruxelles déchets verts

```json
{
  "title": "Bruxelles : la collecte des déchets verts sera assurée tous les quinze jours",
  "slug": "bruxelles-collecte-dechets-verts-quinze-jours",
  "excerpt": "La Région confirme le nouveau calendrier à partir du mois prochain. Les citoyens sont invités à consulter les zones.",
  "content": "<p>La collecte des déchets verts à Bruxelles passera à une fréquence bimensuelle à partir du mois prochain. Les autorités indiquent que cette mesure permettra d'optimiser les tournées tout en maintenant la qualité du service.</p>",
  "meta_title": "Bruxelles : collecte des déchets verts tous les 15 jours",
  "meta_description": "La Région confirme le nouveau calendrier à partir du mois prochain.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/800/600?random=20",
  "keywords": ["Bruxelles", "déchets", "collecte", "environnement"],
  "category_id": "{{category_id_environnement}}"
}
```

---

## Article 2 Jack Depp

```json
{
  "title": "Jack Depp : à 23 ans, le fils de Johnny Depp et Vanessa Paradis sort de l'ombre",
  "slug": "jack-depp-fils-johnny-vanessa-paradis",
  "excerpt": "Le jeune artiste dévoile son premier projet musical et évoque son parcours entre Los Angeles et Paris.",
  "content": "<p>Jack Depp, fils de Johnny Depp et Vanessa Paradis, lance sa carrière musicale. À 23 ans, il présente son premier EP et confie sa volonté de se construire en dehors de l'ombre de ses parents.</p>",
  "meta_title": "Jack Depp : le fils de Johnny Depp et Vanessa Paradis sort de l'ombre",
  "meta_description": "Le jeune artiste dévoile son premier projet musical.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "hot_news",
  "cover_image_url": "https://picsum.photos/1200/800?random=21",
  "keywords": ["Jack Depp", "Johnny Depp", "musique", "cinéma"],
  "category_id": "{{category_id_societe}}"
}
```

---

## Article 3 Boris Dilliès météo

```json
{
  "title": "Le néerlandais de Boris Dilliès ? « We zien » ce qu'on verra !",
  "slug": "neerlandais-boris-dillies-we-zien",
  "excerpt": "Le présentateur météo belge s'essaie au néerlandais dans une séquence devenue virale sur les réseaux sociaux.",
  "content": "<p>Boris Dilliès, présentateur météo de la RTBF, a tenté de présenter le bulletin en néerlandais. La séquence amusante a été largement partagée et commentée.</p>",
  "meta_title": "Le néerlandais de Boris Dilliès ? « We zien » ce qu'on verra !",
  "meta_description": "Le présentateur météo belge s'essaie au néerlandais.",
  "reading_time": 2,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/400/300?random=22",
  "keywords": ["météo", "RTBF", "néerlandais", "Boris Dilliès"],
  "category_id": "{{category_id_au-quotidien}}"
}
```

---

## Article 4 IAD nouveau directeur

```json
{
  "title": "Après des accusations de management toxique, l'IAD désigne un nouveau directeur",
  "slug": "iad-nouveau-directeur-management-toxique",
  "excerpt": "Le groupe immobilier annonce la nomination d'un directeur général par intérim en attendant une réorganisation complète.",
  "content": "<p>L'IAD, acteur majeur de l'immobilier en Belgique, a nommé un nouveau directeur par intérim suite aux accusations de management toxique qui ont ébranlé l'entreprise. Une enquête interne est en cours.</p>",
  "meta_title": "IAD : nouveau directeur après accusations de management toxique",
  "meta_description": "Le groupe immobilier annonce la nomination d'un directeur par intérim.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/800/600?random=23",
  "keywords": ["IAD", "immobilier", "management", "direction"],
  "category_id": "{{category_id_societe}}"
}
```

---

## Article 5 Epstein Justice

```json
{
  "title": "Six noms occultés des dossiers Epstein sans explication par le ministère américain de la Justice",
  "slug": "justice-six-noms-epstein-ministère",
  "excerpt": "Les avocats des victimes demandent la levée des redactions dans les documents récemment rendus publics.",
  "content": "<p>Plusieurs pages de documents liés à l'affaire Epstein ont été rendues publics. Six noms restent caviardés sans justification détaillée du ministère américain de la Justice.</p>",
  "meta_title": "Six noms occultés des dossiers Epstein sans explication",
  "meta_description": "Les avocats des victimes demandent la levée des redactions.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/800/600?random=24",
  "keywords": ["Epstein", "Justice", "États-Unis", "dossiers"],
  "category_id": "{{category_id_politique}}"
}
```

---

## Article 6 Planète déchets Bruxelles

```json
{
  "title": "Planète : Bruxelles renforce la collecte des déchets organiques",
  "slug": "planete-dechets-verts-collecte-bruxelles",
  "excerpt": "Nouvelle fréquence et extension des zones de collecte dès le printemps.",
  "content": "<p>La Région bruxelloise étend la collecte des déchets organiques à de nouvelles zones et augmente la fréquence dans les quartiers les plus denses.</p>",
  "meta_title": "Planète : Bruxelles renforce la collecte des déchets organiques",
  "meta_description": "Nouvelle fréquence et extension des zones de collecte dès le printemps.",
  "reading_time": 3,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/800/600?random=25",
  "keywords": ["planète", "déchets", "Bruxelles", "collecte"],
  "category_id": "{{category_id_environnement}}"
}
```

---

## Article 7 Autosuffisance alimentaire

```json
{
  "title": "L'autosuffisance alimentaire est possible mais à certaines conditions",
  "slug": "sante-autosuffisance-alimentaire-conditions",
  "excerpt": "Une étude détaille les leviers pour tendre vers l'autonomie alimentaire locale.",
  "content": "<p>L'autosuffisance alimentaire locale est atteignable avec des surfaces adéquates, une main-d'œuvre formée et une réduction du gaspillage.</p>",
  "meta_title": "Autosuffisance alimentaire : possible à certaines conditions",
  "meta_description": "Une étude détaille les leviers pour tendre vers l'autonomie alimentaire locale.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": null,
  "keywords": ["santé", "autosuffisance", "alimentation", "agriculture"],
  "category_id": "{{category_id_sante}}"
}
```

---

## Article 8 IA générative entreprise

```json
{
  "title": "IA générative en entreprise : tendances 2026",
  "slug": "technologie-ia-generative-entreprise-2026",
  "excerpt": "Les outils d'IA s'intègrent progressivement dans les processus métiers. Tour d'horizon des usages concrets.",
  "content": "<p>L'intelligence artificielle générative progresse dans les entreprises. Rédaction, synthèse, support client : les cas d'usage se multiplient.</p>",
  "meta_title": "IA générative en entreprise : tendances 2026",
  "meta_description": "Les outils d'IA s'intègrent progressivement dans les processus métiers.",
  "reading_time": 6,
  "status": "review",
  "quality_score": 75,
  "article_type": "long_form",
  "cover_image_url": "https://picsum.photos/800/600?random=27",
  "keywords": ["IA", "entreprise", "technologie", "innovation"],
  "category_id": "{{category_id_technologie}}"
}
```

---

## Article 9 Vélo électrique Belgique

```json
{
  "title": "Vélo électrique : la Belgique parmi les champions européens",
  "slug": "transport-velo-electrique-belgique-croissance",
  "excerpt": "Les ventes de vélos à assistance électrique ont bondi de 25 % sur l'année. Les infrastructures suivent-elles ?",
  "content": "<p>La Belgique se place parmi les pays européens où le vélo électrique connaît la plus forte croissance. Les pistes cyclables et les parkings sécurisés restent un défi.</p>",
  "meta_title": "Vélo électrique : la Belgique parmi les champions européens",
  "meta_description": "Les ventes de vélos électriques ont bondi de 25 % sur l'année.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/800/600?random=28",
  "keywords": ["vélo", "électrique", "mobilité", "Belgique"],
  "category_id": "{{category_id_transport}}"
}
```

---

## Article 10 Rénovation énergétique aides

```json
{
  "title": "Rénovation énergétique : les aides en 2026",
  "slug": "habitat-renovation-energetique-aides-2026",
  "excerpt": "État des lieux des primes et subventions pour isoler et chauffer son logement.",
  "content": "<p>Les dispositifs d'aide à la rénovation énergétique évoluent chaque année. Voici ce qui change en 2026 pour les particuliers.</p>",
  "meta_title": "Rénovation énergétique : les aides en 2026",
  "meta_description": "État des lieux des primes et subventions pour isoler son logement.",
  "reading_time": 5,
  "status": "review",
  "quality_score": 75,
  "article_type": "long_form",
  "cover_image_url": "https://picsum.photos/800/600?random=29",
  "keywords": ["rénovation", "énergie", "primes", "habitat"],
  "category_id": "{{category_id_habitat}}"
}
```

---

## Article 11 Biodiversité oiseaux migration

```json
{
  "title": "Biodiversité : les oiseaux migrateurs perturbés par le climat",
  "slug": "biodiversite-oiseaux-migration-climat",
  "excerpt": "Les changements de température et de précipitations modifient les routes et les dates de migration.",
  "content": "<p>Les oiseaux migrateurs adaptent leurs parcours et leur calendrier aux modifications du climat. Les scientifiques observent des décalages importants.</p>",
  "meta_title": "Biodiversité : les oiseaux migrateurs perturbés par le climat",
  "meta_description": "Les changements climatiques modifient les routes de migration.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/800/600?random=30",
  "keywords": ["biodiversité", "oiseaux", "migration", "climat"],
  "category_id": "{{category_id_biodiversite}}"
}
```

---

## Article 12 Inflation Belgique

```json
{
  "title": "Inflation en Belgique : léger repli en février 2026",
  "slug": "economie-inflation-belgique-fevrier-2026",
  "excerpt": "L'indice des prix à la consommation poursuit sa baisse. Les ménages restent prudents sur les dépenses.",
  "content": "<p>L'inflation en Belgique continue de diminuer. Le coût de l'énergie et des denrées alimentaires reste toutefois source de préoccupation.</p>",
  "meta_title": "Inflation en Belgique : léger repli en février 2026",
  "meta_description": "L'indice des prix à la consommation poursuit sa baisse.",
  "reading_time": 3,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": null,
  "keywords": ["inflation", "Belgique", "économie", "prix"],
  "category_id": "{{category_id_economie}}"
}
```

---

## Résumé des slugs (pour ne pas dupliquer)

| # | Slug |
|---|------|
| 1 | `bruxelles-collecte-dechets-verts-quinze-jours` |
| 2 | `jack-depp-fils-johnny-vanessa-paradis` |
| 3 | `neerlandais-boris-dillies-we-zien` |
| 4 | `iad-nouveau-directeur-management-toxique` |
| 5 | `justice-six-noms-epstein-ministère` |
| 6 | `planete-dechets-verts-collecte-bruxelles` |
| 7 | `sante-autosuffisance-alimentaire-conditions` |
| 8 | `technologie-ia-generative-entreprise-2026` |
| 9 | `transport-velo-electrique-belgique-croissance` |
| 10 | `habitat-renovation-energetique-aides-2026` |
| 11 | `biodiversite-oiseaux-migration-climat` |
| 12 | `economie-inflation-belgique-fevrier-2026` |

---

## Utilisation rapide

1. **Login admin** : `POST http://localhost:8000/api/auth/login` avec `{"email":"admin@vivat.be","password":"password"}` → copier le `token`.
2. **Header** : `Authorization: Bearer {{token}}`
3. Remplacer `{{category_id_xxx}}` par les UUID réels de **GET** `/api/public/categories`.
4. Pour chaque body : **POST** `/api/articles` → récupérer `id` → **POST** `/api/articles/{id}/publish`.
