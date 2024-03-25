<?php
use Monolog\Handler\SendGridHandler;
use Monolog\Processor\WebProcessor;
require_once("load_env.php");
// use Monolog\Level;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set($_ENV['APP_TIMEZONE']);

$logFileStream = new StreamHandler(__DIR__ . '/../app.log', Logger::DEBUG);
// $mailHandler = new SendGridHandler(
//     $_ENV['SENDGRID_USER'], 
//     $_ENV['SENDGRID_KEY'], 
//     $_ENV['SENDGRID_SENDER'],
//     $_ENV['SENDGRID_RECIPIENT'],
//     'Roadhouse Web Alert',
//     Logger::CRITICAL
// );
$dateFormat = "Y M j, g:i a";
$formatter = new LineFormatter(null, $dateFormat);
$logFileStream->setFormatter($formatter);

$logger = new Logger('app');
$logger->pushHandler($logFileStream);

$dbLogger = new Logger('db');
// $dbLogger->pushHandler($mailHandler);
$dbLogger->pushHandler($logFileStream);
$apiLogger = new Logger('api');
$webProcessor = new WebProcessor();
$apiLogger->pushProcessor($webProcessor);
$apiLogger->pushHandler($logFileStream);

$uploadsLogger = $logger->withName('uploads');
