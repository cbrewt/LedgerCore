<?php


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Core\Router;

require_once '../Core/functions.php';

const BASE_PATH = __DIR__ . "/../";
require BASE_PATH . "vendor/autoload.php";

// Bootstrap the application
require base_path('bootstrap.php');

// ------------------------------------------------------------
// Method spoofing (POST + _method) for HTML forms
// ------------------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['_method'])) {
    $spoofed = strtoupper(trim((string) $_POST['_method']));

    // Only allow the verbs you actually use in routes
    if (in_array($spoofed, ['DELETE', 'PATCH'], true)) {
        $method = $spoofed;
        $_SERVER['REQUEST_METHOD'] = $spoofed;
    }
}

// Initialize the router
$router = new Router();
$routes = require base_path('routes.php');

// Parse URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route the request
$router->route($uri, $method);