<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require '../vendor/autoload.php';
$config['displayErrorDetails'] = true;
$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "yinrenlei00";
$config['db']['dbname'] = "demo";


$app = new \Slim\App(['settings'=>$config]);

// Get container
$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET CHARACTER SET utf8");
    return $pdo;
};

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('./templates', [
        'cache' => false
       // 'cache' => './cache'
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    return $view;
};

$app->get('/users',function($request, $response){
    $users = [
        ['username'=>'wang'],
        ['username'=>'hai'],
        ['username'=>'song'],
    ];
    return $this->view->render($response, 'users.html', [
        'users' => $users,
        'menuIndex'=>3
    ]);

});

$app->get('/',function($request, $response){
    $sql = "SELECT * FROM food limit 5";
    $db = $this->db;
    $stmt = $db->query($sql);
    $wines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $db = null;
    return $this->view->render($response, 'index.html', [
        'foods' => $wines,
        'menuIndex'=>1
    ]);

});

$app->get('/foods',function($request, $response){
    $sql = "SELECT * FROM food";
    $db = $this->db;
    $stmt = $db->query($sql);
    $wines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $db = null;
    return $this->view->render($response, 'foods.html', [
        'foods' => $wines,
        'menuIndex'=>2
    ]);
});

$app->get('/hello/{name}', function ($request, $response, $args) {
    $name =$args['name'];
    return $this->view->render($response, 'hello.html', [
        'name' => $name,
        'menuIndex'=>3
    ]);
});

// Run app
$app->run();