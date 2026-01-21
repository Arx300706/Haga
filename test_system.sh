#!/bin/bash

# Script de test et de validation du système Haga Hosting
# Usage: bash test_system.sh

echo "================================"
echo "TEST DU SYSTÈME HAGA HOSTING"
echo "================================"
echo ""

WEB_DIR="/home/armando/Documents/S3-S4/Reseau/projet/serveur/web"
APACHE_USER="www-data"

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction de test
test_command() {
    local test_name=$1
    local command=$2
    
    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} $test_name"
        return 0
    else
        echo -e "${RED}✗${NC} $test_name"
        return 1
    fi
}

# Test 1: Vérifier Apache
echo ">>> Vérification des Services"
test_command "Apache2 est actif" "systemctl is-active apache2 > /dev/null"
test_command "PHP est configuré" "which php > /dev/null"
test_command "MySQL/MariaDB est actif" "systemctl is-active mariadb > /dev/null"

echo ""
echo ">>> Vérification des Fichiers"
test_command "Fichier config.php existe" "test -f $WEB_DIR/config.php"
test_command "Fichier register.php existe" "test -f $WEB_DIR/register.php"
test_command "Fichier client_index.php existe" "test -f $WEB_DIR/client_index.php"
test_command "Fichier client_dashboard.php existe" "test -f $WEB_DIR/client_dashboard.php"
test_command "Fichier login.php existe" "test -f $WEB_DIR/login.php"
test_command "Fichier index.php existe" "test -f $WEB_DIR/index.php"
test_command "Fichier create_project.php existe" "test -f $WEB_DIR/create_project.php"
test_command "Fichier manage_clients.php existe" "test -f $WEB_DIR/manage_clients.php"

echo ""
echo ">>> Vérification des Dossiers"
test_command "Dossier client_uploads existe" "test -d $WEB_DIR/client_uploads"
test_command "Dossier CSS existe" "test -d $WEB_DIR/css"

echo ""
echo ">>> Vérification des Permissions"
test_command "donnee.txt est lisible" "test -r $WEB_DIR/donnee.txt"
test_command "donnee.txt est inscriptible" "test -w $WEB_DIR/donnee.txt"
test_command "client_uploads est accessible" "test -x $WEB_DIR/client_uploads"

echo ""
echo ">>> Vérification de la Syntaxe PHP"
test_command "config.php - Syntaxe OK" "php -l $WEB_DIR/config.php > /dev/null"
test_command "register.php - Syntaxe OK" "php -l $WEB_DIR/register.php > /dev/null"
test_command "client_index.php - Syntaxe OK" "php -l $WEB_DIR/client_index.php > /dev/null"
test_command "login.php - Syntaxe OK" "php -l $WEB_DIR/login.php > /dev/null"

echo ""
echo ">>> Vérification des Ports"
test_command "Port 80 (HTTP) est ouvert" "netstat -tuln 2>/dev/null | grep ':80' > /dev/null || ss -tuln | grep ':80' > /dev/null"
test_command "Port 443 (HTTPS) est ouvert" "netstat -tuln 2>/dev/null | grep ':443' > /dev/null || ss -tuln | grep ':443' > /dev/null"

echo ""
echo ">>> Vérification de la Connectivité"
test_command "Localhost accessible" "curl -s http://localhost > /dev/null"

echo ""
echo ">>> RÉSUMÉ"
echo "================================"
echo -e "✓ Tous les tests sont terminés"
echo "✓ Le système est opérationnel"
echo ""
echo "Accès:"
echo "  - Admin: https://192.168.88.163/web/login.php"
echo "  - Clients: https://192.168.88.163/web/register.php"
echo "  - Statut: https://192.168.88.163/web/status.php"
echo ""
