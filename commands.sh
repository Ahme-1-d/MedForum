composer install &&
npm install &&
npm run build &&
symfony server:start &&
php bin/console messenger:consume async -vv