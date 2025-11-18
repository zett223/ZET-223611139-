<?php
spl_autoload_register(function ($class) {
    $base = __DIR__ . '/..';
    $map = [
        'Src\\Config\\' => $base . '/src/config/',
        'Src\\Controllers\\' => $base . '/src/controllers/',
        'Src\\Helpers\\' => $base . '/src/helpers/',
        'Src\\Middlewares\\' => $base . '/src/middlewares/',
        'Src\\Repositories\\' => $base . '/src/Repositories/',
        'Src\\Validation\\' => $base . '/src/Validation/',
    ];

    foreach ($map as $prefix => $dir) {
        $len = strlen($prefix);
        if (strncmp($class, $prefix, $len) === 0) {
            $relative = substr($class, $len);
            $path = $dir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($path)) {
                require $path;
            }
            return;
        }
    }

    $fallback = $base . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($fallback)) {
        require $fallback;
    }
});

$cfg = require __DIR__ . '/../config/env.php';

use Src\Helpers\Response;
use Src\Middlewares\CorsMiddleware;

//Cors preflight early (options
CorsMiddleware::handle($cfg);
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uri = strtok($_SERVER['REQUEST_URI'], '?');
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path = '/' . trim(str_replace($base, '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

//Routes map
$routes = [
    ['GET', '/api/v1/health', 'Src\\Controllers\\HealthController@show'],
    ['GET', '/api/v1/version', 'Src\\Controllers\\VersionController@show'],
    ['POST', '/api/v1/auth/login', 'Src\\Controllers\\AuthController@login'],
    ['GET', '/api/v1/users', 'Src\\Controllers\\UserController@index'],
    ['GET', '/api/v1/users/{id}', 'Src\\Controllers\\UserController@show'],
    ['POST', '/api/v1/users', 'Src\\Controllers\\UserController@store'],
    ['PUT', '/api/v1/users/{id}', 'Src\\Controllers\\UserController@update'],
    ['DELETE', '/api/v1/users/{id}', 'Src\\Controllers\\UserController@destroy'],
    ['POST', '/api/v1/upload', 'Src\\Controllers\\UploadController@store'],
];

function matchRoute($routes, $method, $path)
{
    foreach ($routes as $r) {
        [$m, $p, $h] = $r;
        if ($m !== $method) continue;
        $regex = preg_replace('#\{[^\/]+\}#', '([^\/-]+)', $p);
        if (preg_match('#^' . $regex . '$#', $path, $mch)) {
            array_shift($mch);
            return [$h, $mch];
        }
    }
    return [null, null];
}
[$handler, $params] = matchRoute($routes, $method, $path);
if (!$handler) {
    Response::jsonError(404, 'Route not found');
}

[$class, $action] = explode('@', $handler);
if (!method_exists($class, $action)) {
    Response::jsonError(405, 'Method not allowed');
}

call_user_func_array([new $class($cfg), $action], $params);
