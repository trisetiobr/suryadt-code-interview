## SETUP
- install laravel project
```
composer install
```
- copy .env.example > .env
- run commands. The db:seed will generate random 20 users, every you run this command it will reset the user database
``````
php artisan migrate
php artisan migrate --database=sqlite_test
php artisan db:seed
``````
- setup cron in your server
```
* * * * * cd /path-to-your-laravel-project && php artisan schedule:run >> /dev/null 2>&1
```

## TESTING
- running unit test
```
php artisan test
``````
- sending email birthday you can run manually
```
php artisan birthday:send '09:00'
```
