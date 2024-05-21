FROM ghcr.io/roadrunner-server/roadrunner:2024.1.2 AS roadrunner

FROM php:8.3-cli

WORKDIR /app

COPY --from=mlocati/php-extension-installer:2.2.5 /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    $EXTRA_PHP_EXTENSIONS \
    redis \
    sockets \
    zip \
    @composer-^2 \
    && rm /usr/local/bin/install-php-extensions

# Copy application files
COPY . .

# Install project dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --classmap-authoritative

# Expose port
EXPOSE 4000

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

CMD ["/usr/local/bin/rr", "serve"]
