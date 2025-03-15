Uses Redis queue and schedules to process subscribers and send emails. Tries to follow Laravel best practices. 

In short:
- Batchable queue jobs that handle mail notifications
- Commands for cron execution
- Repositories to handle DB queries
- Interfaces that are used as contracts for repositories
- Service classes to hold app logic
- Facades for easier access to service classes
- Providers that bind a service class to a facade using singletons
- Chunking DB results for faster retrieval and splitting
- MySql partitioning to split large data to smaller chunks
- Requests to validate form data
- Enums for better code clarity
- DTOs for defining and transferring data better
- Some unit tests, but more should be done
- Uses Http client for making the API calls

Commands to run:

- composer install
- php artisan migrate
- php artisan db:seed --class=SubscriptionSeeder
- php artisan serve
- php artisan schedule:work
- php artisan queue:work

Additional steps:
- Update MAIL_* variable in .env
- QUEUE_CONNECTION=redis

Commands used during development:

gedit .bashrc
export PATH="~/.config/composer/vendor/bin:$PATH"
source ~/.bashrc

composer clearcache
composer selfupdate

nvm use 18

mysqladmin -u root -p create notifications

composer run dev

php artisan make:command PriceSubscriptionCommand

php artisan make:mail PriceNotificationMail

php artisan schedule:list

php artisan schedule:work
OR
crontab -e

* * * * * cd /var/www/html/notifications && php artisan schedule:run >> /var/www/html/notifications/storage/logs/price_output.log 2>&1

php artisan make:job SendPriceNotificationJob

sudo apt-get update 
sudo apt-get install php-pear php-dev
sudo apt-get install php-redis
sudo pecl install redis
sudo gedit /etc/php/8.3/cli/php.ini
sudo systemctl restart php8.3-fpm.service
php -m | grep redis

redis config path - sudo nano /etc/redis/redis.conf

php artisan queue:work

php artisan migrate:refresh --path=/database/migrations/create_subscriptions_table.php

SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

vendor/bin/phpunit tests/Unit/Services/NotificationServiceTest.php 
vendor/bin/phpunit tests/Unit/Controllers/SubscriptionControllerTest.php

php artisan db:seed –class=SubscriptionSeeder

php artisan cache:clear && php artisan config:clear && redis-cli flushall

