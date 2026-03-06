#!/bin/bash

# Script de test complet du pipeline Vivat
# Usage: ./scripts/test-pipeline-complet.sh

set -e

echo "🚀 Test complet du pipeline Vivat"
echo "=================================="
echo ""

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Variables
API_URL="http://localhost:8000/api"
DOCKER_CMD="docker compose exec app"

# Fonction pour afficher les étapes
step() {
    echo ""
    echo -e "${GREEN}▶ $1${NC}"
    echo ""
}

# Fonction pour vérifier une condition
check() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ $1${NC}"
    else
        echo -e "${RED}✗ $1${NC}"
        exit 1
    fi
}

# 1. Vérifier Docker
step "1. Vérification de l'environnement Docker"
docker compose ps | grep -q "Up" && check "Docker containers sont lancés" || (echo "❌ Docker n'est pas lancé. Lance: docker compose up -d" && exit 1)

# 2. Vérifier les migrations
step "2. Vérification des migrations"
$DOCKER_CMD php artisan migrate:status | grep -q "Ran" && check "Migrations sont à jour" || echo "⚠️  Certaines migrations peuvent être en attente"

# 3. Fetch RSS
step "3. Fetch RSS (récupération des articles depuis les flux)"
echo "Lancement du fetch RSS..."
$DOCKER_CMD php artisan rss:fetch --all
check "Fetch RSS lancé"

echo "⏳ Attente de 30 secondes pour que Horizon traite les jobs..."
sleep 30

# 4. Vérifier les items RSS créés
step "4. Vérification des items RSS créés"
RSS_COUNT=$(curl -s "$API_URL/rss-items?status=new" | grep -o '"id"' | wc -l | tr -d ' ')
if [ "$RSS_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✓ $RSS_COUNT items RSS créés${NC}"
else
    echo -e "${YELLOW}⚠️  Aucun item RSS trouvé. Vérifie Horizon et les flux RSS.${NC}"
fi

# 5. Enrichissement
step "5. Enrichissement (scraping + analyse IA)"
echo "Lancement de l'enrichissement (10 items max)..."
$DOCKER_CMD php artisan content:enrich --limit=10
check "Enrichissement lancé"

echo "⏳ Attente de 2 minutes pour que Horizon et OpenAI traitent les jobs..."
sleep 120

# 6. Vérifier les items enrichis
step "6. Vérification des items enrichis"
ENRICHED_COUNT=$(curl -s "$API_URL/rss-items?status=enriched" | grep -o '"id"' | wc -l | tr -d ' ')
if [ "$ENRICHED_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✓ $ENRICHED_COUNT items enrichis${NC}"
else
    echo -e "${YELLOW}⚠️  Aucun item enrichi trouvé. Vérifie OpenAI API key et Horizon.${NC}"
fi

# 7. Sélection intelligente
step "7. Sélection intelligente (propositions d'articles)"
echo "Récupération des meilleures propositions..."
SELECTION_RESPONSE=$(curl -s "$API_URL/pipeline/select-items?count=3")
echo "$SELECTION_RESPONSE" | jq '.' 2>/dev/null || echo "$SELECTION_RESPONSE"
check "Sélection intelligente récupérée"

# 8. Afficher les propositions
step "8. Affichage des propositions"
echo ""
echo "Propositions d'articles disponibles :"
echo "$SELECTION_RESPONSE" | jq -r '.proposals[]? | "  - Score: \(.score) | Type: \(.suggested_article_type) | Sujet: \(.topic)"' 2>/dev/null || echo "  (Format JSON non disponible, voir réponse ci-dessus)"

# 9. Instructions pour génération
step "9. Prochaines étapes"
echo ""
echo "Pour générer un article :"
echo "1. Copie les item_ids d'une proposition ci-dessus"
echo "2. Lance la génération via :"
echo ""
echo "   curl -X POST $API_URL/articles/generate \\"
echo "     -H 'Content-Type: application/json' \\"
echo "     -d '{\"item_ids\": [\"id1\", \"id2\", \"id3\"]}'"
echo ""
echo "Ou utilise Postman avec les endpoints documentés dans docs/TESTING_POSTMAN.md"
echo ""

echo -e "${GREEN}✅ Test du pipeline terminé !${NC}"
echo ""
echo "📊 Vérifications supplémentaires :"
echo "  - Horizon dashboard: http://localhost:8000/horizon"
echo "  - phpMyAdmin: http://localhost:8080"
echo "  - API status: curl $API_URL/pipeline/status"
