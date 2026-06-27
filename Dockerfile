FROM php:8.3-apache

# Extensions système
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip \
        git \
        curl \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        opcache \
        bcmath \
        xml \
        tokenizer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite pour les routes Laravel
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuration Apache : pointer vers public/
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Configuration PHP : opcache + limites
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.validate_timestamps=0"; \
    echo "upload_max_filesize=20M"; \
    echo "post_max_size=20M"; \
    echo "memory_limit=256M"; \
} > /usr/local/etc/php/conf.d/gezr.ini

WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# Installer les dépendances PHP sans les packages dev
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && php artisan package:discover --ansi

# Créer les dossiers storage nécessaires
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p bootstrap/cache

# Permissions initiales (seront réappliquées au démarrage)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Script d'entrée
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
