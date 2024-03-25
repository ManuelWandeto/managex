<?php

require_once(__DIR__ ."/../vendor/autoload.php");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();
