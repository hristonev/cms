organizer
=========

A Symfony project created on August 10, 2017, 4:26 pm.

# Requirements:
- PHP 7.*
- MySQL (14.14)
- Composer (1.4.1)
- NPM (3.5.2)
- Node (4.2.6)

# Contents
- Install
    - [GIT](#git-setup)
    - [Composer](#composer-setup)
    - [Bower](#bower-setup)
    - [Database](#database-setup)
- [Features](#features)

# Setup

## Git setup
> git clone https://gitlab.com/hristonev/miss-benny.git

## Composer setup
Installs backend bundles and libraries.
> php composer.phar install

## Bower setup
Installs frontend assets. Using gulp to manifest assets in public
> npm install

> npm install -g bower

> bower install # install front-end assets

> gulp # manifest assets to web public folder

> gulp --production # minify, uglify assets for production environment 

## Database setup
> php bin/console doctrine:schema:create
> php bin/console doctrine:migration:migrate

[Contents](#contents)
# Features