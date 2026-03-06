# Prompt d'enrichissement — Version lisible

Ce document montre **à quoi ressemble visuellement** le prompt envoyé à OpenAI dans `EnrichContentJob` (lignes 96-120). Le prompt est composé de deux messages : **system** et **user**.

---

## Prompt complet (en une fois)

**System :**

```
Tu es un analyste de contenu SEO expert. Tu analyses des articles et produis une analyse structurée avec des mots-clés SEO ciblés (longue traîne, spécifiques, faible concurrence). Privilégie les termes recherchés par les utilisateurs mais peu concurrentiels. Réponds uniquement en JSON.
```

**User :** *(les parties entre crochets sont remplies dynamiquement à partir de l’article scrapé)*

```
Titre: [titre de l'article scrapé]

Titres de sections: [liste des H2/H3 séparés par des virgules, max 10]

Contenu:
[texte principal de l'article, tronqué à 6000 caractères]

Analyse ce contenu et génère un JSON avec :
- lead: résumé 1-2 phrases
- headings: tableau des titres H2/H3
- key_points: tableau de 3-7 points clés
- seo_keywords: tableau de 5-10 mots-clés SEO pertinents (termes spécifiques, pas génériques, longue traîne si possible)
- primary_topic: le sujet principal en 2-4 mots (ex: 'transition énergétique', 'biodiversité marine')
- quality_score: 0-100 (qualité rédactionnelle et informative)
- seo_score: 0-100 (potentiel SEO estimé : originalité du sujet, spécificité des mots-clés, intérêt de recherche)
```

---

## 3. Exemple complet (à quoi ressemble le user en vrai)

*Exemple avec des valeurs fictives pour illustrer la structure.*

```
Titre: La transition énergétique en Europe s'accélère

Titres de sections: Contexte, Objectifs 2030, Les pays en pointe, Freins et controverses

Contenu:
L'Union européenne a renforcé ses objectifs de réduction des émissions...
[suite du texte extrait du site, jusqu'à 6000 caractères]

Analyse ce contenu et génère un JSON avec :
- lead: résumé 1-2 phrases
- headings: tableau des titres H2/H3
- key_points: tableau de 3-7 points clés
- seo_keywords: tableau de 5-10 mots-clés SEO pertinents (termes spécifiques, pas génériques, longue traîne si possible)
- primary_topic: le sujet principal en 2-4 mots (ex: 'transition énergétique', 'biodiversité marine')
- quality_score: 0-100 (qualité rédactionnelle et informative)
- seo_score: 0-100 (potentiel SEO estimé : originalité du sujet, spécificité des mots-clés, intérêt de recherche)
```

---

## 4. Où c’est défini dans le code

| Élément | Fichier | Lignes |
|--------|---------|--------|
| Message system | `app/Jobs/EnrichContentJob.php` | 114 |
| Construction du user (titre + sections + contenu) | id. | 96-98 |
| Consignes JSON (bloc 2 du user) | id. | 99-106 |
| Envoi à OpenAI | id. | 108-120 |

Le modèle utilisé est `config('services.openai.model', 'gpt-4o')`, avec `response_format: json_object`, `temperature: 0.3`, `max_tokens: 2000`.

---

*Référence : EnrichContentJob::callOpenAI() — février 2026*
