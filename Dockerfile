FROM php:8.2-apache

# Extensiones que usa el proyecto (mysqli para $conexion->prepare/bind_param, etc.)
RUN docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite

# Bloquea el acceso web a archivos de infraestructura (dumps, compose, dotfiles...)
COPY docker/deny-sensitive.conf /etc/apache2/conf-enabled/deny-sensitive.conf

# DocumentRoot por defecto: /var/www/html
# El proyecto se monta en /var/www/html/bd_op (ver docker-compose.yml),
# de modo que la URL /bd_op/... coincide con las rutas absolutas del codigo.
