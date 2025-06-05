#!/usr/bin/env sh
set -e

echo "[entrypoint] Démarrage de l'application Radiothérapie Bunker Management..."

# Note: APP_ENV=prod et APP_DEBUG=false sont configurés par défaut en production
echo "[entrypoint] Démarrage de l'application en mode production sécurisé..."

# Configurer les paramètres PHP pour la production
for php_ini in /etc/php*/*/php.ini /etc/php/*/php.ini; do
  if [ -f "$php_ini" ]; then
    echo "[entrypoint] Configuration PHP pour production dans $php_ini"
    sed -i 's/display_errors = On/display_errors = Off/g' "$php_ini"
    # Garder log_errors = On pour la journalisation
    sed -i 's/log_errors = Off/log_errors = On/g' "$php_ini"
    # Utiliser E_ERROR pour la production (E_ALL serait trop verbeux)
    sed -i 's/error_reporting = E_ALL/error_reporting = E_ERROR/g' "$php_ini"
  fi
done

# Suppression du fichier de diagnostic temporaire s'il existe
if [ -f "/var/www/public/debug_info.php" ]; then
  echo "[entrypoint] Suppression du fichier de diagnostic temporaire"
  rm -f /var/www/public/debug_info.php
fi

# Attendre quelques secondes pour s'assurer que la base de données est prête
echo "[entrypoint] Attente de la base de données MySQL (5 secondes)..."
sleep 5

# Vérifier si on doit réinitialiser complètement la base de données
# Cette variable d'environnement peut être définie dans l'interface Render
if [ "$RESET_DATABASE" = "true" ]; then
  echo "[entrypoint] RESET_DATABASE=true détecté - Réinitialisation complète de la base de données..."
  

  # Application des migrations
  echo "[entrypoint] Application des migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

  # Initialisation des données
  echo "[entrypoint] Initialisation des données..."
  php bin/console app:create-initial-data --no-interaction

  echo "[entrypoint] Réinitialisation de la base de données terminée avec succès!"

  # Note: après avoir utilisé RESET_DATABASE=true une fois, vous devriez le désactiver
  # dans les paramètres d'environnement de Render pour les déploiements suivants
else
  # Mode standard: vérifier et migrer si nécessaire
  echo "[entrypoint] Vérification de l'existence de la table admin..."
  TABLE_ADMIN_EXISTS=$(php bin/console doctrine:query:sql "SHOW TABLES LIKE 'admin'" --quiet)

  if [ -z "$TABLE_ADMIN_EXISTS" ] ; then
    echo "[entrypoint] Table 'admin' non existante → exécution des migrations Doctrine."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    echo "[entrypoint] Initialisation des données initiales..."
    php bin/console app:create-initial-data --no-interaction
  else
    echo "[entrypoint] Table 'admin' trouvée → vérification des mises à jour nécessaires."
    
    # Exécuter les migrations si des changements de schéma existent
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    
    # On s'assure que les données de base sont bien présentes et à jour
    echo "[entrypoint] Mise à jour des données initiales si nécessaire..."
    php bin/console app:create-initial-data --no-interaction
  fi
fi

# Démarrage des services PHP-FPM et Caddy
# Installation de net-tools si nécessaire pour netstat
if ! command -v netstat >/dev/null 2>&1; then
  echo "[entrypoint] Installation de net-tools pour netstat..."
  apt-get update -q && apt-get install -qy net-tools
fi

# On démarre d'abord PHP-FPM en arrière-plan, puis on vérifie qu'il est bien démarré
echo "[entrypoint] Démarrage de PHP-FPM en arrière-plan..."

# Détermine quel binaire php-fpm utiliser
if command -v php-fpm83 >/dev/null 2>&1; then
  PHP_FPM_BIN="php-fpm83"
elif command -v php-fpm8.2 >/dev/null 2>&1; then
  PHP_FPM_BIN="php-fpm8.2"
else
  PHP_FPM_BIN="php-fpm"
fi

# Démarrer PHP-FPM en arrière-plan
$PHP_FPM_BIN &
PHP_FPM_PID=$!

# Attendre que PHP-FPM soit prêt et lie le port 9000
echo "[entrypoint] Attente que PHP-FPM soit prêt (port 9000)..."
for i in {1..30}; do
  if netstat -tuln | grep -q ':9000 '; then
    echo "[entrypoint] PHP-FPM est prêt sur le port 9000."
    break
  fi
  echo "[entrypoint] Attente que PHP-FPM lie le port 9000... ($i/30)"
  sleep 1
done

# Démarrage de Caddy
echo "[entrypoint] Démarrage de Caddy en foreground..."
caddy run --config /etc/caddy/Caddyfile --adapter caddyfile

# Nous ne démarrons plus PHP-FPM ici car il est déjà démarré en arrière-plan plus haut
# Caddy est maintenant le processus principal (foreground) qui maintient le container actif
