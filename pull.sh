#! /bin/sh

git pull origin main
composer install --optimize-autoloader --no-dev
