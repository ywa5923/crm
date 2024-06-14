<?php

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

#$_SERVER['APP_ENV'] = 'prod';
#$_SERVER['DATABASE_URL'] = 'mysql://x12bkncp_admin:Punctulrosu12!@@127.0.0.1:3306/x12bkncp_symfony';
//DATABASE_URL="mysql://john:1234@127.0.0.1:3306/sym"
return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
