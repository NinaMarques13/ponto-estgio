FROM php:8.4-apache

# Instala as dependências do sistema necessárias para o PHP e Node.js
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    curl \
    nodejs \
    npm

# Limpa o cache do apt para diminuir o tamanho da imagem
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala as extensões do PHP necessárias para o Laravel e PostgreSQL
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instala o Composer mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Configura o Apache para apontar para a pasta /public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilita o mod_rewrite do Apache (necessário para as rotas do Laravel)
RUN a2enmod rewrite

# Copia os arquivos do projeto para o container
COPY . .

# Instala as dependências do PHP (sem os pacotes de dev)
RUN composer install --no-dev --optimize-autoloader

# Instala as dependências do Front-end e faz o build (Vite/Tailwind)
RUN npm install
RUN npm run build

# Dá as permissões corretas para as pastas do Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Cria um script de inicialização para rodar as migrations automaticamente
RUN echo '#!/bin/bash\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan migrate --force\n\
apache2-foreground' > /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

# Expõe a porta 80
EXPOSE 80

# Inicia o servidor rodando nosso script
CMD ["/usr/local/bin/start.sh"]
