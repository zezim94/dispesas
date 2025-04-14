# Use a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala extensões (por exemplo: mysqli, pdo_mysql, curl)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copia todos os arquivos para a pasta pública do Apache
COPY . /var/www/html/

# Ativa o mod_rewrite (opcional, para URL amigável)
RUN a2enmod rewrite
