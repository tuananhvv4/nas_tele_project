<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Core\Application;
use Core\View;

$app = Application::getInstance(BASE_PATH);
$app->bootstrap();

View::init(BASE_PATH . '/resources/views');

$app->run();
