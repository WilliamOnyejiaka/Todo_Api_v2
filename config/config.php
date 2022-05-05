<?php
declare(strict_types=1);

require __DIR__ .  "/../vendor/autoload.php";

ini_set("display_errors",1);
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));

$dotenv->load();


function config($key){

  return (array(
    'host' => $_ENV['DB_HOST'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'database_name' => $_ENV['DATABASE'],
    'secret_key' => $_ENV['SECRET_KEY'],
    'hash' => $_ENV['HASH'],
    'allow_cors' => strcmp(strval($_ENV['ALLOW_CORS']),"true") == 0 ? true : false,
  ))[$key];
}
?>
