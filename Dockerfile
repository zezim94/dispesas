# Usando a imagem oficial do PHP com Apache
FROM php:8.1-apache

# Instala dependências necessárias para o PHP e PostgreSQL
RUN apt-get update && apt-get install -y \
    git zip unzip curl libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql mysqli \
    && docker-php-ext-enable pdo_pgsql pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instala o Composer (copia a versão do Composer)
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho dentro do contêiner
WORKDIR /var/www/html

# Copia o arquivo composer.json e composer.lock para o contêiner
COPY composer.json composer.lock ./

# Executa o Composer para instalar as dependências do PHP (sem scripts e dependências de desenvolvimento)
RUN composer install --no-dev --no-scripts --prefer-dist --no-progress

# Copia o código do projeto para dentro do contêiner
COPY . .

# Expondo a porta 80 para que o Apache sirva a aplicação
EXPOSE 80
