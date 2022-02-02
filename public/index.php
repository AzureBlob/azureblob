<?php
declare(strict_types=1);

use AzureBlob\Controllers\ContainerController;
use AzureBlob\Controllers\HomeController;
use AzureBlob\Controllers\StorageController;
use AzureBlob\Middleware\AuthMiddleware;
use AzureBlob\Services\AzureBlobService;
use AzureBlob\Utilities\CacheUtil;
use AzureBlob\Utilities\CacheUtilityInterface;
use DI\Bridge\Slim\Bridge;
use DI\Container;
use GuzzleHttp\Client as HttpClient;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RKA\Middleware\IpAddress;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;
use Twig\TwigFilter;

require_once __DIR__ . '/../vendor/autoload.php';

defined('AZBLOB_ENV') ||
    define('AZBLOB_ENV', (getenv('AZBLOB_ENV') ?: 'production'));

session_name('AZBLOBSID');
session_start();

// Monolog configuration and setup
$logFile = sprintf('app_%s.log', date('Ym'));
$logLevel = Logger::WARNING;
$displayErrors = false;
if ('production' !== AZBLOB_ENV) {
    $logLevel = Logger::DEBUG;
    $displayErrors = true;
}
$logger = new Logger('app');
$streamHandler = new StreamHandler(__DIR__ . '/../data/logs/' . $logFile, $logLevel);
$logger->pushHandler($streamHandler);

// Twig configuration
$twigCache = __DIR__ . '/../data/cache';
if ('production' !== AZBLOB_ENV) {
    $twigCache = false;
}
$twig = Twig::create(__DIR__ . '/../templates', [
    'cache' => false, //$twigCache,
]);
$twig->addExtension(new DebugExtension());
$shortString = new TwigFilter('short', function ($string) {
    $maxLenght = 25;
    if($maxLenght > strlen($string)) {
        return $string;
    }
    return substr($string, 0, $maxLenght) . '...';
});
$twig->getEnvironment()->addFilter($shortString);

// Slim configuration and setup
$diContainer = new Container();
$diContainer->set(Logger::class, $logger);
$diContainer->set(HttpClient::class, new HttpClient);
$diContainer->set(CacheUtilityInterface::class, new CacheUtil(
    __DIR__ . '/../data/cache',
    600
));
$diContainer->set(AuthMiddleware::class, new AuthMiddleware(
    $diContainer->get(Logger::class))
);
$diContainer->set(AzureBlobService::class, new AzureBlobService());
$diContainer->set(HomeController::class, new HomeController(
    $diContainer->get(Logger::class),
    $diContainer->get(AzureBlobService::class)
));
$diContainer->set(ContainerController::class, new ContainerController(
    $diContainer->get(AzureBlobService::class),
    $diContainer->get(Logger::class),
    $diContainer->get(CacheUtilityInterface::class)
));

$app = Bridge::create($diContainer);

// Route strategy definition
$routeCollector = $app->getRouteCollector();
$routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());

// Middleware configuration
$app->add(TwigMiddleware::create($app, $twig));
$app->addRoutingMiddleware();
$app->add(new IpAddress());
$app->add($diContainer->get(AuthMiddleware::class));
$errorMiddleware = $app->addErrorMiddleware($displayErrors, true, true, $logger);

$app->get('/', [HomeController::class, 'getHome'])->setName('home');
$app->post('/login', [HomeController::class, 'postSettings'])->setName('login');
$app->get('/logout', [HomeController::class, 'logout'])->setName('logout');
$app->group('/storage', function (RouteCollectorProxy $storage) use ($diContainer) {
   $storage
       ->get('', [StorageController::class, 'getContainerListing'])
       ->setName('container-listing');
   $storage
       ->get('/add-container', [StorageController::class, 'addContainer'])
       ->setName('add-container');
   $storage
       ->post('/add-container', [StorageController::class, 'createContainer'])
       ->setName('create-container');
   $storage
       ->get('/remove-container/{name}', [StorageController::class, 'removeContainer'])
       ->setName('remove-container');

   $storage->group('/container', function (RouteCollectorProxy $container) use ($diContainer) {
       $container
           ->get('/{name}', [ContainerController::class, 'getBlobListing'])
           ->setName('blob-listing');
       $container
           ->post('/{name}/upload', [ContainerController::class, 'uploadBlob'])
           ->setName('blob-upload');
       $container
           ->get('/{name}/remove-blob', [ContainerController::class, 'removeBlob'])
           ->setName('blob-remove');
   })->add($diContainer->get(AuthMiddleware::class));
})->add($diContainer->get(AuthMiddleware::class));

$app->run();
