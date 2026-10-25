<?php
// Copy this file to config.php and adjust the values below.
// config.php holds your real settings and is NEVER uploaded to GitHub.

define('APP_NAME', 'StockPilot');

// The address is detected automatically, so the project works in any folder name
$host   = preg_match('/^[A-Za-z0-9.\-:\[\]]+$/', $_SERVER['HTTP_HOST'] ?? '') ? $_SERVER['HTTP_HOST'] : 'localhost';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', $scheme . '://' . $host . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'inventory_mvc');
define('DB_USER', 'root');       // XAMPP default. On a live server use a NON-root user
define('DB_PASS', '');           // write your database password here (empty on XAMPP)

define('APP_DEBUG', false);      // true only while developing, false on a live server
define('API_KEY', 'change-me-to-a-long-random-secret');   // change this to your own secret

ini_set('display_errors', APP_DEBUG ? '1' : '0');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Karachi');