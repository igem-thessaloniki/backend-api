<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['database'] = function ($c) {
    $settings = $c->get('settings')['database'];
    $connector = $settings['connector'];
    $host = $settings['host'];
    $port = $settings['port'];
    $database = $settings['database'];
    $charset = $settings['charset'];
    $dsn = "$connector:host=$host;port=$port;dbname=$database;charset=$charset";
    $username = $settings['username'];
    $password = $settings['password'];
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new \PDO($dsn, $username, $password, $opt);
    return $pdo;
};