FROM php:8.4-fpm

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Etc/UTC

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

RUN ln -snf "/usr/share/zoneinfo/$TZ" /etc/localtime \
    && echo "$TZ" > /etc/timezone; \
    \
    apt-get update \
    && apt-get install -y --no-install-recommends \
        build-essential \
        ca-certificates \
        cron \
        curl \
        git \
        mariadb-client \
        unzip \
        wget \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && git config --global --add safe.directory /var/www/html
