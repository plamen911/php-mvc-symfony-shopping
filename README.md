.checkout
=========

A Symfony project created on March 26, 2017, 5:14 pm.

App URL: http://phpmvc.lynxlake.org/

```
php bin/console server:run
php bin/console server:run localhost:3000

php bin/console generate:bundle
php bin/console generate:controller
php bin/console doctrine:generate:form AppBundle:User
php bin/console doctrine:generate:entity

php bin/console doctrine:schema:update
php bin/console doctrine:schema:update --force

php bin/console generate:doctrine:crud

php bin/console debug:route

php bin/console cache:clear
php bin/console cache:clear --env=prod
php bin/console cache:clear --env=prod --no-warmup
php bin/console assets:install web
php bin/console assets:install web --symlink

composer require symfony/var-dumper

which composer
```

```
ssh ubuntu@192.168.100.100
cd ~/.ssh/
ls
vim authorized_keys
ssh-keygen -b 4096 -t rsa
ls | grep deploy
cat deploy      # private key
cat deploy.pub  # public key
cat authorized_keys
cd /var/www/
git --version
cd /var/logs/
vim prod.log
git log --oneline
git checkout 9589927
```

```
vim deploy.sh
```

```
#!/bin/sh
git pull origin master
composer install
php bin/console doctrine:schema:update --force --env=prod
php bin/console cache:clear --env=prod
php bin/console assets:install --env=prod
```

```
sh deploy.sh
```

https://deployer.org/

```
dep deploy
composer require deployer/deployer --dev
php vendor/bin/dep init
php vendor/bin/dep deploy
```

phpmd/phpmd - PHPMD - PHP Mess Detector - https://phpmd.org/about.html

squizlabs/php_codesniffer

http://www.php-fig.org/psr/psr-2/ - PSR-2: Coding Style Guide

to quit Vim type `:q<Enter>` to exit OR `:wq<Enter>`

http://symfony.com/doc/current/bundles/FOSUserBundle/index.html
https://sonata-project.org/bundles
http://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html
http://symfony.com/doc/current/controller/upload_file.html

http://elcodi.io/ - a fully-functional e-commerce application on top of the Symfony framework

