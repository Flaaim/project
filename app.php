<?php



use App\Controllers\AppController;
use Pimple\Container;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use App\Controllers\AbstractController;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

require __DIR__.'/vendor/autoload.php';

$container = new Container();
$container['config'] = require __DIR__.'/src/config.php';
$container['db'] = function ($c){
    $db = $c['config']['database'];
    $url = 'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=utf8mb4';
    return new PDO($url, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
};

if ($container['config']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$container['twig'] = function ($c) {
    $loader = new FilesystemLoader(__DIR__ . '/src/views');
    $twig = new Environment($loader, [
        'cache' => __DIR__ . '/cache/views',
        'auto_reload' => true,
        'debug' => $c['config']['debug'],
    ]);
    $twig->addGlobal('app', $c);
    if ($c['config']['debug']) {
        $twig->addExtension(new DebugExtension());
    }
    return $twig;
};

$dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r) {
    $r->addRoute('GET', '/', [AppController::class, 'index']);
    $r->addRoute('GET', '/about[/]', [AppController::class, 'about']);
});
$uri = $_SERVER['REQUEST_URI'];
$pos = strpos($uri, '?');
if ($pos !== false) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
$routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo $container['twig']->render('404.twig');
        break;
    case Dispatcher::FOUND:

        [$class, $method] = $routeInfo[1];
        /** @var AbstractController $instance */
        $instance = new $class;
        $instance->setContainer($container);
        $instance->setModel($class);
        call_user_func_array([$instance, $method], [$routeInfo[2]]);
        break;
}