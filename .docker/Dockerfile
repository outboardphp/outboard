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
        software-properties-common \
        unzip \
        wget \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && git config --global --add safe.directory /var/www/html \

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && npm install --global corepack@latest \
    && corepack enable pnpm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
