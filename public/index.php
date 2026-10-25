<?php
// Front controller: every request passes through this file
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

define('ROOT', dirname(__DIR__));   // the inventory-mvc folder

require ROOT . '/config/config.php';
require ROOT . '/app/Core/helpers.php';

// AUTOLOADER: when PHP meets "new User()", it looks for User.php in these folders
spl_autoload_register(function (string $class): void {
    foreach (['app/Core/', 'app/Controllers/', 'app/Models/'] as $dir) {
        $file = ROOT . '/' . $dir . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

(new Router())->dispatch();