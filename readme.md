# ci.swissuplabs.com

## Requirements

 -  PHP 5.6 and newer
 -  Node 6.11.2 and NPM
 -  Composer
 -  Git

## Installation

```bash
# 1. fetch the sources
git clone git@github.com:swissup/ci.swissuplabs.com.git && cd ci.swissuplabs.com

# 2. install php dependencies
composer install

# 3. prepare laravel application
cp .env.example .env
php artisan key:generate

# 4. install npm modules (omit `--production` key if you are frontend developer)
npm install --production

# 5. run the site (skip this step if you have a ready to use webserver)
php artisan serve
```
