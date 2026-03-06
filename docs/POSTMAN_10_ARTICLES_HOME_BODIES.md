# 10 articles du HomeArticlesSeeder — Bodies Postman

À utiliser avec **POST** `http://localhost:8000/api/articles` (headers : `Accept: application/json`, `Content-Type: application/json`, `Authorization: Bearer {{token}}`).

Pour chaque article : 1) envoyer le body → récupérer l’`id` dans la réponse 201 → 2) **POST** `http://localhost:8000/api/articles/{id}/publish` pour publier.

---

## Option rapide : créer les 10 articles en une commande

Si les **9 catégories** sont déjà créées (Postman ou phpMyAdmin), tu peux créer et publier les 10 articles d’un coup :

```bash
docker compose exec app php artisan db:seed --class=HomeArticlesSeeder
```

Cela recrée les 10 articles (mêmes slugs et contenus que les bodies ci‑dessous), les publie et assigne la bonne catégorie à chacun. Tu peux relancer la commande pour réinitialiser ces articles (les anciens avec ces slugs sont supprimés avant recréation).

Si tu préfères tout faire à la main via Postman, utilise les bodies ci‑dessous et remplace les `category_id` par les vrais UUID.

---

## Les 9 catégories prévues (nom + slug)

Récupère les **id** (UUID) avec **GET** `http://localhost:8000/api/public/categories`. Si ces catégories n’existent pas encore, crée-les avec **POST** `http://localhost:8000/api/categories` (admin) en utilisant les noms et slugs ci-dessous.

**Si tu as encore les 14 catégories du PipelineSeeder** et que tu veux les 9 rubriques home : lance le seeder des 9 catégories puis utilise phpMyAdmin pour supprimer les anciennes si besoin.

- **Créer les 9 catégories** (Laravel) :  
  `php artisan db:seed --class=VivatNineCategoriesSeeder`  
  (depuis ta machine ou `docker compose exec app php artisan db:seed --class=VivatNineCategoriesSeeder`)
- **Voir la base** : ouvre **phpMyAdmin** → http://localhost:8080 — connexion : utilisateur `vivat`, mot de passe `vivat_secret` (base `vivat`). Table `categories` pour voir ou supprimer les entrées.

Dans les bodies, **category_id** contient un **placeholder UUID**. Remplace chaque placeholder par l’UUID réel de la catégorie correspondante (même ordre que le tableau).

| # | Nom           | Slug (à utiliser en base) | Placeholder UUID à remplacer |
|---|----------------|----------------------------|------------------------------|
| 1 | Au quotidien   | `au-quotidien`             | `00000000-0000-0000-0000-000000000001` |
| 2 | Énergie        | `energie`                  | `00000000-0000-0000-0000-000000000002` |
| 3 | Finance        | `finance`                  | `00000000-0000-0000-0000-000000000003` |
| 4 | Technologie    | `technologie`              | `00000000-0000-0000-0000-000000000004` |
| 5 | Chez soi       | `chez-soi`                 | `00000000-0000-0000-0000-000000000005` |
| 6 | Mode           | `mode`                     | `00000000-0000-0000-0000-000000000006` |
| 7 | Santé          | `sante`                    | `00000000-0000-0000-0000-000000000007` |
| 8 | Voyage         | `voyage`                   | `00000000-0000-0000-0000-000000000008` |
| 9 | Famille        | `famille`                  | `00000000-0000-0000-0000-000000000009` |

**Exemple** : si GET categories renvoie `{"id": "a1b2c3d4-...", "name": "Santé", "slug": "sante", ...}`, remplace dans tous les bodies `00000000-0000-0000-0000-000000000007` par `a1b2c3d4-...`.

---

## Article 1 — IA : la révolution médicale avance sans garde-fou (hot_news) — **Catégorie : Santé**

```json
{
  "title": "IA : la révolution médicale avance sans garde-fou",
  "slug": "ia-revolution-medicale-sans-garde-fou",
  "excerpt": "Dans un nouveau rapport, l'OMS appelle les États à renforcer d'urgence les règles juridiques et éthiques pour protéger patients et soignants.",
  "content": "<p>En quelques années, l'intelligence artificielle a profondément changé la pratique médicale. Les médecins l'utilisent pour poser des diagnostics, alléger les tâches administratives ou encore mieux communiquer avec les patients. Mais qui est responsable lorsqu'un système d'IA se trompe ou cause un préjudice ? Apprenant à partir de données, l'IA en reproduit les biais ou les lacunes : elle peut manquer un diagnostic, proposer un traitement inadéquat ou accentuer les inégalités de santé.</p>\n\n<p>Dans son nouveau rapport <em>Artificial Intelligence in Health : State of Readiness across the WHO European Region</em>, l'OMS appelle les États à adapter leurs cadres juridiques et éthiques pour mieux protéger patients et soignants. Moins d'un pays sur dix dispose aujourd'hui de normes de responsabilité encadrant l'usage de l'IA en santé. Plus largement, seuls 4 des 50 États interrogés ont mis en place une stratégie nationale pour accompagner son développement.</p>\n\n<blockquote><p>« Nous sommes à la croisée des chemins, déclare le docteur Natasha Azzopardi-Muscat, directrice de la division Systèmes de santé à l'OMS/Europe. Soit l'IA sera utilisée pour améliorer la santé et le bien-être des personnes, alléger la charge de travail de nos travailleurs de la santé épuisés et faire baisser les coûts des soins de santé, soit elle pourrait nuire à la sécurité des patients, compromettre la protection de la vie privée et creuser les inégalités en matière de soins. »</p></blockquote>\n\n<h2>Des usages déjà bien réels…</h2>\n<ul>\n<li>32 pays (64 %) utilisent des outils d'IA pour assister le diagnostic, notamment en imagerie médicale.</li>\n<li>La moitié des pays ont introduit des chatbots pour l'engagement et le suivi des patients.</li>\n<li>26 pays ont identifié des priorités nationales pour l'usage de l'IA dans leurs systèmes de santé.</li>\n</ul>\n\n<h2>… mais sans filets juridiques ni stratégiques solides</h2>\n<ul>\n<li>Seuls 4 pays sur 50 disposent d'une stratégie nationale dédiée à l'IA en santé.</li>\n<li>Moins d'un pays sur 10 a mis en place des normes juridiques de responsabilité qui déterminent qui est tenu pour responsable lorsqu'un système d'IA se trompe ou cause un préjudice.</li>\n<li>86 % des pays considèrent l'incertitude juridique comme le principal frein à l'adoption de l'IA, et 78 % évoquent des contraintes financières importantes.</li>\n</ul>",
  "meta_title": "IA : la révolution médicale avance sans garde-fou",
  "meta_description": "Dans un nouveau rapport, l'OMS appelle les États à renforcer d'urgence les règles juridiques et éthiques pour protéger patients et soignants.",
  "reading_time": 2,
  "status": "review",
  "quality_score": 75,
  "article_type": "hot_news",
  "cover_image_url": "https://picsum.photos/1200/800?random=1",
  "keywords": ["IA", "santé", "OMS", "éthique"],
  "category_id": "00000000-0000-0000-0000-000000000007"
}
```

---

## Article 2 — L'autosuffisance alimentaire (long_form) — **Catégorie : Au quotidien**

```json
{
  "title": "L'autosuffisance alimentaire est possible mais à certaines conditions",
  "slug": "autosuffisance-alimentaire-conditions",
  "excerpt": "Une étude détaille les leviers pour tendre vers l'autonomie alimentaire locale : surfaces, main-d'œuvre et réduction du gaspillage.",
  "content": "<p>Peut-on nourrir une région ou un pays avec sa propre production ? La question de l'autosuffisance alimentaire revient régulièrement dans le débat public, entre crises sanitaires, conflits et dérèglement climatique. Une étude récente menée sur plusieurs territoires européens montre que l'objectif est atteignable, à condition de réunir plusieurs facteurs.</p>\n\n<h2>Des surfaces suffisantes mais mal réparties</h2>\n<p>Le premier levier concerne les surfaces agricoles. Dans de nombreuses régions, la superficie dédiée à l'alimentation humaine directe (légumes, céréales panifiables, légumineuses) pourrait être augmentée au détriment des cultures d'exportation ou de l'élevage intensif. Les auteurs soulignent toutefois que la répartition des terres et l'accès pour les jeunes agriculteurs restent des freins majeurs.</p>\n\n<h2>Main-d'œuvre et structuration des filières</h2>\n<p>Deuxième condition : une main-d'œuvre formée et des filières structurées. Les circuits courts et la transformation locale nécessitent des investissements en outillage et en formation. Sans politique publique volontariste, les exploitations restent dépendantes des débouchés lointains et des standards industriels.</p>\n\n<h2>Réduire le gaspillage</h2>\n<p>Enfin, aucun scénario d'autosuffisance ne tient sans une baisse drastique du gaspillage alimentaire. Aujourd'hui, environ un tiers de la production mondiale est perdu ou jeté. Réduire ces pertes permet à la fois de soulager la pression sur les terres et d'améliorer la résilience des territoires.</p>",
  "meta_title": "L'autosuffisance alimentaire est possible mais à certaines conditions",
  "meta_description": "Une étude détaille les leviers pour tendre vers l'autonomie alimentaire locale.",
  "reading_time": 5,
  "status": "review",
  "quality_score": 75,
  "article_type": "long_form",
  "cover_image_url": "https://picsum.photos/800/600?random=2",
  "keywords": ["autosuffisance", "alimentation", "agriculture", "circuits courts"],
  "category_id": "00000000-0000-0000-0000-000000000001"
}
```

---

## Article 3 — Transition énergétique 2030 (long_form) — **Catégorie : Énergie**

```json
{
  "title": "Transition énergétique : les objectifs 2030 en Europe",
  "slug": "transition-energetique-objectifs-2030-europe",
  "excerpt": "Où en sont les États membres par rapport aux engagements climat et aux énergies renouvelables ? Bilan à mi-parcours.",
  "content": "<p>L'Union européenne s'est fixé des objectifs ambitieux pour 2030 : réduction de 55 % des émissions de gaz à effet de serre par rapport à 1990, et 42,5 % d'énergies renouvelables dans la consommation finale. À mi-parcours, le bilan est contrasté selon les pays.</p>\n\n<h2>Les bons élèves</h2>\n<p>Plusieurs États du Nord et de l'Est ont déjà dépassé leurs objectifs intermédiaires grâce à un mix hydraulique, éolien et solaire bien développé. La Suède, le Danemark et la Finlande figurent en tête du classement. L'Allemagne, malgré sa sortie du nucléaire, a accru la part du solaire et de l'éolien.</p>\n\n<h2>Retards et dépendances</h2>\n<p>En revanche, certains pays restent très dépendants du charbon ou du gaz et accusent du retard. Les investissements dans les réseaux et le stockage restent insuffisants pour intégrer massivement le renouvelable intermittent. La Commission européenne a rappelé la nécessité d'accélérer les procédures d'autorisation pour les projets d'énergies renouvelables.</p>",
  "meta_title": "Transition énergétique : les objectifs 2030 en Europe",
  "meta_description": "Où en sont les États membres par rapport aux engagements climat ? Bilan à mi-parcours.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "long_form",
  "cover_image_url": "https://picsum.photos/800/600?random=3",
  "keywords": ["transition énergétique", "Europe", "énergies renouvelables", "2030"],
  "category_id": "00000000-0000-0000-0000-000000000002"
}
```

---

## Article 4 — Domino's bottes (standard) — **Catégorie : Mode**

```json
{
  "title": "Domino's crée des bottes inspirées de ses sacs à pizza",
  "slug": "dominos-bottes-sacs-pizza",
  "excerpt": "La marque surfe sur le style streetwear pour sa nouvelle collection limitée, en collaboration avec un designer belge.",
  "content": "<p>Après les vêtements et accessoires dérivés de l'univers fast-food, Domino's Pizza lance une paire de bottes dont le design s'inspire directement des sacs isothermes utilisés pour livrer les pizzas. La collection, en édition limitée, est le fruit d'une collaboration avec un designer belge connu pour ses pièces décalées.</p>\n\n<p>Les bottes reprennent le motif rouge et bleu de la marque et sont dotées d'une semelle épaisse type « platform ». Elles seront vendues en ligne à partir du mois prochain. Ce type d'opération marketing vise à renforcer la notoriété de la marque auprès des jeunes consommateurs.</p>",
  "meta_title": "Domino's crée des bottes inspirées de ses sacs à pizza",
  "meta_description": "La marque surfe sur le style streetwear pour sa nouvelle collection limitée.",
  "reading_time": 2,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/400/300?random=4",
  "keywords": ["mode", "streetwear", "Domino's", "collaboration"],
  "category_id": "00000000-0000-0000-0000-000000000006"
}
```

---

## Article 5 — Météo (standard) — **Catégorie : Au quotidien**

```json
{
  "title": "Neige, pluie, orage : la météo des prochains jours",
  "slug": "neige-pluie-orage-meteo-prochains-jours",
  "excerpt": "Bulletin météo pour les régions concernées : un temps instable et frais s'installe jusqu'en milieu de semaine.",
  "content": "<p>Les prévisionnistes annoncent une dégradation nette du temps pour les prochains jours. Des précipitations neigeuses sont attendues en altitude dès ce soir, et des averses pluvieuses ou orageuses concerneront les plaines.</p>\n\n<h2>Vent et températures</h2>\n<p>Le vent se renforcera en cours de journée, avec des rafales pouvant dépasser 80 km/h sur les côtes. Les températures resteront fraîches pour la saison, avec des maximales comprises entre 5 et 10 °C. Il est conseillé de prévoir des vêtements adaptés et de la prudence sur les routes en cas de neige.</p>",
  "meta_title": "Neige, pluie, orage : la météo des prochains jours",
  "meta_description": "Bulletin météo : un temps instable et frais s'installe jusqu'en milieu de semaine.",
  "reading_time": 1,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": "https://picsum.photos/400/300?random=5",
  "keywords": ["météo", "neige", "orage", "prévisions"],
  "category_id": "00000000-0000-0000-0000-000000000001"
}
```

---

## Article 6 — Dossiers Epstein (standard, sans image) — **Catégorie : Famille**

```json
{
  "title": "Six noms occultés des dossiers Epstein sans explication par le ministère américain de la Justice",
  "slug": "six-noms-occultes-dossiers-epstein-justice",
  "excerpt": "Les avocats des victimes demandent la levée des redactions dans les documents récemment rendus publics.",
  "content": "<p>Plusieurs centaines de pages de documents liés à l'affaire Jeffrey Epstein ont été rendues publiques ces dernières semaines. Toutefois, six noms apparaissent encore sous forme de redactions, sans que le ministère américain de la Justice n'ait fourni d'explication juridique détaillée pour justifier leur occultation.</p>\n\n<p>Les avocats des victimes ont déposé une requête pour demander la levée de ces caviardages, estimant que l'intérêt public et le droit des parties à connaître l'ensemble des éléments l'emportent sur les motifs invoqués par le gouvernement. La juridiction saisie n'a pas encore statué.</p>",
  "meta_title": "Six noms occultés des dossiers Epstein sans explication",
  "meta_description": "Les avocats des victimes demandent la levée des redactions dans les documents rendus publics.",
  "reading_time": 3,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": null,
  "keywords": ["Epstein", "Justice", "États-Unis", "dossiers"],
  "category_id": "00000000-0000-0000-0000-000000000009"
}
```

---

## Article 7 — Biodiversité marine (standard, sans image) — **Catégorie : Voyage**

```json
{
  "title": "Biodiversité marine : un rapport alarmant sur les récifs",
  "slug": "biodiversite-marine-rapport-alarmant-recifs",
  "excerpt": "Les scientifiques appellent à des mesures de protection renforcées face au blanchissement et à la surpêche.",
  "content": "<p>Un rapport international publié ce mois-ci dresse un état des lieux préoccupant des écosystèmes marins. Les récifs coralliens, déjà menacés par le réchauffement et l'acidification des océans, subissent des épisodes de blanchissement de plus en plus fréquents et intenses.</p>\n\n<h2>Pression de la pêche et pollutions</h2>\n<p>La surpêche et les pratiques destructrices (chalutage en eaux profondes, rejets de plastiques) accentuent la pression. Les auteurs recommandent l'extension des aires marines protégées et un encadrement plus strict des activités en mer. Sans action rapide, une large part des récifs pourrait disparaître d'ici à la fin du siècle.</p>",
  "meta_title": "Biodiversité marine : un rapport alarmant sur les récifs",
  "meta_description": "Les scientifiques appellent à des mesures de protection renforcées face au blanchissement et à la surpêche.",
  "reading_time": 4,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": null,
  "keywords": ["biodiversité", "récifs", "océan", "protection"],
  "category_id": "00000000-0000-0000-0000-000000000008"
}
```

---

## Article 8 — Ouverture sommet climat COP (hot_news) — **Catégorie : Énergie**

```json
{
  "title": "Ouverture du sommet climat : les enjeux de la COP",
  "slug": "ouverture-sommet-climat-enjeux-cop",
  "excerpt": "Les négociations s'ouvrent sur fond de pressions économiques et d'appels à accélérer la réduction des émissions.",
  "content": "<p>La conférence des parties sur le climat s'est ouverte ce lundi avec en ligne de mire le respect des engagements de l'accord de Paris. Les pays sont attendus sur le renforcement de leurs contributions nationales et sur les financements en faveur des États les plus vulnérables.</p>\n\n<p>Les derniers rapports du GIEC rappellent que le monde doit réduire ses émissions bien plus vite que prévu pour limiter le réchauffement à 1,5 °C. Les débats porteront notamment sur la sortie des énergies fossiles et les mécanismes de solidarité Nord-Sud.</p>",
  "meta_title": "Ouverture du sommet climat : les enjeux de la COP",
  "meta_description": "Les négociations s'ouvrent sur fond de pressions économiques et d'appels à accélérer la réduction des émissions.",
  "reading_time": 3,
  "status": "review",
  "quality_score": 75,
  "article_type": "hot_news",
  "cover_image_url": "https://picsum.photos/800/600?random=8",
  "keywords": ["COP", "climat", "négociations", "émissions"],
  "category_id": "00000000-0000-0000-0000-000000000002"
}
```

---

## Article 9 — Labels consommation responsable (long_form) — **Catégorie : Chez soi**

```json
{
  "title": "Consommation responsable : les labels décryptés",
  "slug": "consommation-responsable-labels-decryptes",
  "excerpt": "Comment s'y retrouver parmi les certifications et labels durables ? Guide pratique pour mieux choisir.",
  "content": "<p>Bio, commerce équitable, FSC, MSC, Écolabel européen… Les étiquettes et logos se multiplient sur les produits. Ce guide vous aide à distinguer les labels les plus exigeants des simples arguments marketing.</p>\n\n<h2>Labels officiels et cahiers des charges</h2>\n<p>Les labels encadrés par la réglementation (comme le label bio européen) imposent un cahier des charges contrôlé par des organismes certificateurs. D'autres démarches sont portées par des associations ou des filières et peuvent varier en exigence. Il est utile de vérifier quels critères sont réellement pris en compte : environnement, social, bien-être animal, etc.</p>\n\n<h2>Éviter le greenwashing</h2>\n<p>Certaines marques créent leurs propres logos ou formules (« naturel », « respectueux de la planète ») sans garantie vérifiable. Privilégier les labels reconnus et, lorsque c'est possible, les produits locaux et de saison reste une base solide pour une consommation plus responsable.</p>",
  "meta_title": "Consommation responsable : les labels décryptés",
  "meta_description": "Comment s'y retrouver parmi les certifications et labels durables ? Guide pratique.",
  "reading_time": 6,
  "status": "review",
  "quality_score": 75,
  "article_type": "long_form",
  "cover_image_url": "https://picsum.photos/800/600?random=9",
  "keywords": ["labels", "consommation", "durable", "certification"],
  "category_id": "00000000-0000-0000-0000-000000000005"
}
```

---

## Article 10 — Justice réforme prescription (standard, sans image) — **Catégorie : Famille**

```json
{
  "title": "Justice : réforme des délais de prescription",
  "slug": "justice-reforme-delais-prescription",
  "excerpt": "Le projet de loi sera examiné au Sénat la semaine prochaine. Les associations de victimes demandent un allongement des délais.",
  "content": "<p>Le gouvernement a déposé un projet de loi visant à modifier les délais de prescription en matière civile et pénale. L'objectif affiché est de mieux prendre en compte la parole des victimes, notamment dans les affaires de violences ou d'abus, où le délai actuel est souvent jugé trop court.</p>\n\n<p>Les associations de victimes réclament depuis des années un allongement des délais pour permettre aux personnes concernées de déposer plainte ou de se constituer partie civile. Le texte sera examiné au Sénat à partir de la semaine prochaine.</p>",
  "meta_title": "Justice : réforme des délais de prescription",
  "meta_description": "Le projet de loi sera examiné au Sénat. Les associations de victimes demandent un allongement des délais.",
  "reading_time": 2,
  "status": "review",
  "quality_score": 75,
  "article_type": "standard",
  "cover_image_url": null,
  "keywords": ["justice", "prescription", "réforme", "victimes"],
  "category_id": "00000000-0000-0000-0000-000000000009"
}
```

---

## Récap

| # | Slug | article_type | Catégorie | Image |
|---|------|--------------|-----------|-------|
| 1 | ia-revolution-medicale-sans-garde-fou | hot_news | Santé | oui |
| 2 | autosuffisance-alimentaire-conditions | long_form | Au quotidien | oui |
| 3 | transition-energetique-objectifs-2030-europe | long_form | Énergie | oui |
| 4 | dominos-bottes-sacs-pizza | standard | Mode | oui |
| 5 | neige-pluie-orage-meteo-prochains-jours | standard | Au quotidien | oui |
| 6 | six-noms-occultes-dossiers-epstein-justice | standard | Famille | non |
| 7 | biodiversite-marine-rapport-alarmant-recifs | standard | Voyage | non |
| 8 | ouverture-sommet-climat-enjeux-cop | hot_news | Énergie | oui |
| 9 | consommation-responsable-labels-decryptes | long_form | Chez soi | oui |
| 10 | justice-reforme-delais-prescription | standard | Famille | non |

Pour chaque article : **POST** `/api/articles` avec le body → noter l’`id` → **POST** `/api/articles/{id}/publish`.

**Rappel** : remplace dans chaque body les placeholders `00000000-0000-0000-0000-000000000001` … `00000000-0000-0000-0000-000000000009` par les vrais UUID des catégories (GET `/api/public/categories`). Si les 9 catégories (Au quotidien, Énergie, Finance, Technologie, Chez soi, Mode, Santé, Voyage, Famille) n’existent pas encore, crée-les avec **POST** `/api/categories` (admin).
