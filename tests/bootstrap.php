<?php

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Prague');
Tester\Environment::setup();

const TEMP_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'temp';
