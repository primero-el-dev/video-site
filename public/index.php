<?php

use App\Kernel;

ini_set('session.save_handler', 'memcached');
ini_set('session.save_path', 'localhost:11211');

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};