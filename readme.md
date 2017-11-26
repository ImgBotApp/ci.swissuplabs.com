# ci.swissuplabs.com

## Requirements

 -  PHP 5.6 and newer
 -  Node 6.11.2 and NPM
 -  Composer
 -  Git

## Installation

 1. Get webhook secret and access token from github
 2. Prepare Application

    ```bash
    # 1. fetch the sources
    git clone git@github.com:swissup/ci.swissuplabs.com.git && cd ci.swissuplabs.com

    # 2. install php dependencies
    composer install

    # 3. prepare files and folders
    find storage bootstrap -type d -exec chmod 777 {} +
    php artisan storage:link

    # 4. prepare configuration
    cp .env.example .env
    php artisan key:generate
    vi .env

    # 5. setup application dependencies
    php artisan migrate
    php artisan app:setup

    # 6. install npm modules (if you are frontend developer)
    npm install

    # 7. run the site (skip this step if you have a ready to use webserver)
    php artisan serve
    ```

 3. [Configure crontab](#configure-crontab)
 4. [Configure supervisor](#configure-supervisor)

## Upgrade

```bash
# use artisan command
php artisan app:update

# or manually
php artisan down
git pull
composer install
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan migrate --force
php artisan app:setup
php artisan config:cache
php artisan queue:restart
php artisan up
```

## Configure Crontab

 1. Run this command to open editor:

    ```bash
    crontab -e
    ```

 2. Add the following line:

    ```bash
    * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
    ```

## Configure Supervisor

If you would like to use database driver to process queue jobs, you need to
configure supervisor.

1. Generate config using `echo_supervisord_conf` script:

    ```bash
    echo_supervisord_conf > /path-to-your-project/supervisord.conf
    ```

 2. Add new section into it:

    ```ini
    [program:ci.swissuplabs.com]
    process_name=%(program_name)s_%(process_num)02d
    command=php /path-to-your-project/artisan queue:work
    autostart=true
    autorestart=true
    user=swissuplabs
    numprocs=2
    redirect_stderr=true
    stdout_logfile=/path-to-your-project/worker.log
    ```

 3. Start supervisor:

    ```bash
    supervisord -c /path-to-your-project/supervisord.conf
    supervisorctl reread
    supervisorctl update
    supervisorctl start ci.swissuplabs.com:*
    ```
