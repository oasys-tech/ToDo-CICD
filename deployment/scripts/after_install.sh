#!/bin/bash

# reset deploy log
: >/var/www/deployment/deploy.log

# Change Permission
echo "start change permission" >> /var/www/deployment/deploy.log
semanage fcontext -a -t httpd_sys_rw_content_t /var/www/storage
semanage fcontext -a -t httpd_sys_rw_content_t /var/www/bootstrap/cache
chown apache:apache -R /var/www/
echo "end change permission" >> /var/www/deployment/deploy.log

# backend build & migrate
echo "start backend build & migrate" >>/var/www/deployment/deploy.log
cd /var/www
composer install &>>/var/www/deployment/deploy.log
sudo -u apache php artisan migrate --force &>> /var/www/deployment/deploy.log
echo "end backend build & migrate" >> /var/www/deployment/deploy.log

# Clear any previous cached views and optimize the application
echo "start clear cache" >> /var/www/deployment/deploy.log
sudo -u apache php /var/www/artisan cache:clear &>> /var/www/deployment/deploy.log
sudo -u apache php /var/www/artisan view:clear &>> /var/www/deployment/deploy.log
sudo -u apache php /var/www/artisan config:cache &>> /var/www/deployment/deploy.log
sudo -u apache php /var/www/artisan optimize &>> /var/www/deployment/deploy.log
sudo -u apache php /var/www/artisan route:cache &>> /var/www/deployment/deploy.log
echo "end clear cache" >> /var/www/deployment/deploy.log

# frontend build
echo "start frontend build" >> /var/www/deployment/deploy.log
sudo -u apache npm install &>> /var/www/deployment/deploy.log
set +e
sudo -u apache npm run production &>> /var/www/deployment/deploy.log
set -e
echo "end frontend build" >> /var/www/deployment/deploy.log

exit 0
