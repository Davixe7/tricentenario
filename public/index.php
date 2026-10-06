<?php
if (php_sapi_name() === 'cli-server') {
    $filePath = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($filePath)) {
        return false;
    }
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Router.php';

Auth::init();

$router = new Router();

// Auth routes
$router->add('GET', '/login', 'AuthController@showLogin');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/logout', 'AuthController@logout');
$router->add('POST', '/change-password', 'AuthController@changePassword');

// Dashboard redirect
$router->add('GET', '/', 'UserController@index');

// Areas CRUD
$router->add('GET', '/areas', 'AreaController@index');
$router->add('GET', '/areas/create', 'AreaController@create');
$router->add('POST', '/areas/store', 'AreaController@store');
$router->add('GET', '/areas/edit/{id}', 'AreaController@edit');
$router->add('POST', '/areas/update/{id}', 'AreaController@update');
$router->add('POST', '/areas/delete/{id}', 'AreaController@delete');

// Positions CRUD
$router->add('GET', '/positions', 'PositionController@index');
$router->add('GET', '/positions/create', 'PositionController@create');
$router->add('POST', '/positions/store', 'PositionController@store');
$router->add('GET', '/positions/edit/{id}', 'PositionController@edit');
$router->add('POST', '/positions/update/{id}', 'PositionController@update');
$router->add('POST', '/positions/delete/{id}', 'PositionController@delete');

// Users CRUD
$router->add('GET', '/users', 'UserController@index');
$router->add('GET', '/users/create', 'UserController@create');
$router->add('POST', '/users/store', 'UserController@store');
$router->add('GET', '/users/edit/{id}', 'UserController@edit');
$router->add('POST', '/users/update/{id}', 'UserController@update');
$router->add('POST', '/users/delete/{id}', 'UserController@delete');

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);