FROM php:8.3-cli

RUN apt-get update && apt-get install -y cron libicu-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html

COPY docker/cron/crontab /etc/cron.d/recurring-cron

RUN chmod 0644 /etc/cron.d/recurring-cron \
    && crontab /etc/cron.d/recurring-cron

CMD ["cron", "-f"]
