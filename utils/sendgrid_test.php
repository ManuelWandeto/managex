<?php
require __DIR__ . '/../vendor/autoload.php'; 
// run from commandline to test mail with sendgrid api
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$email = new \SendGrid\Mail\Mail(); 
$email->setFrom($_ENV['SENDGRID_SENDER']);
$email->setSubject("Sending with SendGrid is Fun");
$email->addTo($_ENV['SENDGRID_RECIPIENT']);
$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
$email->addContent(
    "text/html", "<strong>Just a test </strong>"
);
$sendgrid = new \SendGrid($_ENV['SENDGRID_KEY']);
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '. $e->getMessage() ."\n";
}