#!/bin/sh
git pull origin master
composer install
php bin/console doctrine:schema:update --force --env=prod
php bin/console cache:clear --env=prod
php bin/console assets:install --env=prod