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
find storage bootstrap -type d -exec chmod 777 {} +
php artisan key:generate
php artisan migrate
php artisan storage:link
# Setup GitHub webhook secret and access token. Download test tools and prepare environment
php artisan app:setup

# 4. install npm modules (omit `--production` key if you are frontend developer)
npm install --production

# 5. run the site (skip this step if you have a ready to use webserver)
php artisan serve
```

## Upgrade

```bash
git pull
composer install
php artisan migrate
php artisan app:setup
php artisan queue:restart
```

## Configuring Supervisor

If you would like to use database driver to process queue jobs, you need to
configure supervisor.

Supervisor is a process monitor for the Linux operating system, and will
automatically restart your `queue:work` process if it fails.

Generate config using `echo_supervisord_conf` script:

```bash
echo_supervisord_conf > /home/www/ci.swissuplabs.com/supervisord.conf
```

Add new section into it:

```ini
[program:ci.swissuplabs.com]
process_name=%(program_name)s_%(process_num)02d
command=php /home/www/ci.swissuplabs.com/artisan queue:work
autostart=true
autorestart=true
user=swissuplabs
numprocs=2
redirect_stderr=true
stdout_logfile=/home/www/ci.swissuplabs.com/worker.log
```

Start supervisor:

```bash
supervisord -c /home/www/ci.swissuplabs.com/supervisord.conf
supervisorctl reread
supervisorctl update
supervisorctl start ci.swissuplabs.com:*
```
