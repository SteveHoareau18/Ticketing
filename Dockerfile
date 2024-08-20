# Utiliser l'image PHP 8.2 CLI officielle
FROM php:8.2-cli

# Installer les dépendances système et les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    nano \
    openssh-server \
    && docker-php-ext-install \
    intl \
    zip \
    pdo_mysql

# Définir le répertoire de travail
WORKDIR /var/www/symfony

# Copier les fichiers de l'application
COPY . .

# Définir le répertoire de travail
WORKDIR /var/www/symfony/web

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Vérifier les versions de Node.js et npm
RUN node --version && npm --version

# Installer Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Créer un utilisateur non-root
RUN useradd -ms /bin/bash symfony && \
    echo "symfony:symfony" | chpasswd

RUN chown symfony:symfony /var/www/symfony -R

# Configurer SSH pour l'utilisateur symfony
RUN mkdir /home/symfony/.ssh && \
    chmod 700 /home/symfony/.ssh && \
    chown symfony:symfony /home/symfony/.ssh

# Changer l'utilisateur
USER symfony

# Installer les dépendances Node.js
RUN npm install
RUN npm run build

# Installer les dépendances PHP
RUN composer install

# Exposer les ports 8080 pour Symfony et 22 pour SSH
EXPOSE 8080 22

# Configurer le shell de l'utilisateur symfony pour qu'il démarre dans /var/www/symfony
RUN echo "cd /var/www/symfony" >> /home/symfony/.bashrc

# Revenir à l'utilisateur root pour démarrer les services
USER root

# Créer un script de démarrage
RUN echo '#!/bin/bash\n\
service ssh start\n\
symfony server:start --port=8080 --no-tls' > /start.sh && \
chmod +x /start.sh

# Commande par défaut pour démarrer le serveur SSH et Symfony
CMD ["/start.sh"]