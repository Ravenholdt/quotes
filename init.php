<?php

use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';
require 'DB.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
