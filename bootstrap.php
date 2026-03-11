<?php

use Core\App;
use Core\Container;
use Core\Database;

$container = new Container();

$container->singleton(Database::class, function (Container $container) {
    $config = require base_path('config.php');
    return Database::getInstance();
});

App::setContainer($container);
