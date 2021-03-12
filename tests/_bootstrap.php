<?php

define('WS', realpath(__DIR__.'/../my127ws.phar'));

/** @deprecated */
function run($command) {
    return shell_exec(WS.' '.$command);
}

require_once __DIR__.'/Fixture.php';
require_once __DIR__.'/../vendor/autoload.php';
