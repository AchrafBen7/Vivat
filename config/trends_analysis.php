<?php

return [

    'system_prompt' => 'Tu es un expert en analyse éditoriale et en tendances pour un site média (environnement, santé, énergie, société). Tu reçois un export CSV d\'articles issus de plusieurs sources (flux RSS récupérés et enrichis par IA). Chaque ligne = un article avec : date, title, category, source, primary_topic, seo_keywords, quality_score, seo_score, url, status. Tu réponds en français, de façon structurée et exploitable.',

    'user_prompt_template' => <<<'PROMPT'
Voici les données CSV des articles (séparateur ;) :

```csv
{csv_content}
```

Ta mission est de faire une analyse complète et structurée pour décider QUEL article nous devrions écrire et COMMENT (type d'article, longueur, angle). Réponds en français, de façon très claire et exploitable.

1) CONNEXIONS ENTRE ARTICLES
   Identifie les groupes d'articles qui parlent du même sujet ou de sujets très proches (mots-clés communs, primary_topic, titres).
   Pour chaque groupe important : donne un label de sujet, le nombre d'articles, les sources concernées, et une courte explication du lien entre eux (corrélation, tendance, actualité commune).

2) CORRÉLATIONS ET TENDANCES
   En t'appuyant sur les dates, les primary_topic et les seo_keywords : quelles tendances vois-tu (sujets qui reviennent souvent, pics par période, par source ou par catégorie) ?
   Quels sujets sont "chauds" (très présents récemment) vs plus "de fond" (récurrents sur la période) ?

3) MEILLEUR SUJET POUR ÉCRIRE UN ARTICLE
   En te basant sur les connexions et tendances : quel est LE meilleur sujet pour écrire un article maintenant ?
   Justifie en 3–5 phrases (pertinence, multi-sources, potentiel SEO, actualité).
   Propose un titre d'article et une phrase de contexte priorité (ex. : "Sur X articles analysés, Y portent sur ce sujet (tendance). Ce sujet est prioritaire.").

4) POIDS À ATTRIBUER (recommandations)
   Pour notre algorithme de sélection (critères : fraîcheur, qualité, SEO, diversité des sources, fréquence du sujet), quels poids recommandes-tu pour ce jeu de données (ex. : fraîcheur 30 %, qualité 25 %, SEO 25 %, diversité 10 %, fréquence sujet 10 %) ?
   Explique brièvement pourquoi (ex. : "Beaucoup d'actualité récente → augmenter fraîcheur.").

5) HOT NEWS vs ARTICLE DE FOND
   Parmi les groupes/sujets identifiés :
     Lesquels qualifierais-tu de "hot news" (actualité chaude, brève) ? Pour chacun : indique la fourchette de mots recommandée (ex. : 400–650 mots) et le ton (percutant, factuel).
     Lesquels qualifierais-tu d'article de fond (analyse, synthèse multi-sources) ? Pour chacun : indique la fourchette de mots recommandée (ex. : 1000–1800 mots) et le ton (approfondi, analytique).
   Pour le meilleur sujet que tu as choisi en (3) : précise si c'est un article "hot news" ou "de fond" et donne la fourchette de mots exacte et le ton à utiliser (contexte pour l'IA : "Ton article doit faire entre X et Y mots, [ton].").

Résume à la fin en une "fiche rédactionnelle" : sujet retenu, type (hot news / de fond), nombre de mots (min–max), ton, phrase de contexte priorité, et 3–5 mots-clés SEO à intégrer.
PROMPT,

    'max_csv_chars' => (int) env('TRENDS_ANALYSIS_MAX_CSV_CHARS', 45000),

];
