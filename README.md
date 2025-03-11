Useful commands

gedit .bashrc
export PATH="~/.config/composer/vendor/bin:$PATH"
source ~/.bashrc

composer clearcache
composer selfupdate

nvm use 18

mysqladmin -u root -p create notifications
php artisan migrate

php artisan serve
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

