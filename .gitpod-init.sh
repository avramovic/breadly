mysql -u root -e "create database breadly"
cp .env.example .env
sed -i "s|APP_URL=http://localhost|APP_URL=${GITPOD_WORKSPACE_URL}|g" .env
sed -i "s|APP_URL=https://|APP_URL=https://8000-|g" .env
composer install
php artisan key:generate
php artisan migrate --seed