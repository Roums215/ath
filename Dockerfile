##########  Étape 1 : assets frontend (statiques)  ##########
FROM alpine:3.20 AS assets-builder
WORKDIR /app

# Copier les assets sources
COPY assets/ assets/
COPY public/ public/

# Installer bash pour des commandes plus sophistiquées
RUN apk add --no-cache bash

# Créer tous les répertoires nécessaires pour les assets
RUN mkdir -p public/assets/css public/assets/js public/assets/styles public/assets/controllers

# S'assurer que les répertoires existent avant de copier
RUN bash -c 'for dir in css styles js controllers images fonts; do \
        if [ -d "assets/$dir" ]; then \
            mkdir -p "public/assets/$dir"; \
            cp -r "assets/$dir"/* "public/assets/$dir/" 2>/dev/null || true; \
        fi; \
    done'

# Créer un fichier manifest.json simple pour le cache busting
RUN echo '{"version":"'$(date +%s)'"}' > public/assets/manifest.json

##########  Étape 2 : vendors (Composer)  ##########
FROM composer:2.7 AS vendor
WORKDIR /app

# 1. On ne copie que les fichiers de dépendances
COPY composer.json composer.lock ./

# 2. Installation SANS scripts Symfony
RUN composer install \
        --no-dev --prefer-dist --optimize-autoloader \
        --no-scripts --no-interaction

##########  Étape 3 : runtime  ##########
FROM alpine:3.20

# PHP + Caddy déjà compilés
RUN apk add --no-cache \
        php83 php83-fpm \
        php83-session \
        php83-ctype php83-json php83-dom php83-simplexml php83-tokenizer php83-iconv \
        php83-opcache php83-intl php83-pdo_mysql \
        php83-pdo_pgsql php83-pgsql \
        php83-zip php83-mbstring php83-gd php83-fileinfo php83-curl php83-phar \
        caddy tini tzdata

# Configuration FPM - désactivation du clear_env pour préserver les variables d'environnement
RUN mkdir -p /run/php && \
    sed -i 's/;daemonize = yes/daemonize = no/' /etc/php83/php-fpm.conf && \
    echo "clear_env = no" >> /etc/php83/php-fpm.d/www.conf

WORKDIR /var/www

# 3. Vendors (mis en cache) d'abord…
COPY --from=vendor /app/vendor /var/www/vendor
COPY --from=vendor /app/composer.* /var/www/

# 4. … puis le reste du projet (code, bin/, public/…)
COPY . /var/www

# Copier les assets compilés depuis l'étape assets-builder
COPY --from=assets-builder /app/public/assets /var/www/public/assets

# 5. On a besoin du binaire Composer pour les auto-scripts
COPY --from=vendor /usr/bin/composer /usr/bin/composer

# 6. Définir clairement l'environnement de production avant les commandes
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV APP_SECRET=376274c64b9e7d97adb2f439d1228a66

# 7. Nettoyage explicite et création du cache de production et dossiers sessions
RUN rm -rf var/cache/* var/log/* \
 && mkdir -p var/sessions var/sessions/prod \
 && composer run-script --no-dev post-install-cmd --no-interaction \
 && php bin/console cache:clear --env=prod --no-warmup --no-debug \
 && php bin/console cache:warmup --env=prod --no-debug \
 && chown -R nobody:nobody var && chmod -R 775 var

# Caddy sert les fichiers statiques et relaie les requêtes PHP
COPY infra/render/Caddyfile /etc/caddy/Caddyfile

# Configuration pour la production
ENV APP_ENV=prod
EXPOSE 80

# Création d'un script d'initialisation pour exécuter les migrations et initialiser les données
COPY infra/render/docker-entrypoint.sh /var/www/docker-entrypoint.sh
RUN chmod +x /var/www/docker-entrypoint.sh

ENTRYPOINT ["/sbin/tini","--"]
CMD ["/var/www/docker-entrypoint.sh"]
