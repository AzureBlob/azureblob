<?php

use AzureBlob\AzureBlob;
use DI\Container;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

defined('AZBLOB_ENV') ||
    define('AZBLOB_ENV', (getenv('AZBLOB_ENV') ?: 'production'));

ini_set('display_errors', 1);
ini_set('post_max_size', '2G');
ini_set('upload_max_filesize', '2G');
ini_set('session.name', 'AZBLOBSID');
ini_set('session.save_path', __DIR__ . '/../data/session');

session_start();

//$predisParams = ['tcp://127.0.0.1?alias=master'];
//$predisOptions = ['replication' => true];
//$predis = new \Predis\Client($predisParams, $predisOptions);

$container = new Container();
AppFactory::setContainer($container);

$container->set('view', function() {
    return Twig::create(__DIR__ . '/../templates',
        ['cache' => false]); //__DIR__ . '/../data/cache']);
});

$app = AppFactory::create();
$app->add(TwigMiddleware::createFromContainer($app));
$app->addRoutingMiddleware();

if ('production' !== AZBLOB_ENV) {
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);
}

$azureBlob = new AzureBlob();
if ([] !== $_SESSION) {
    $azureBlob = new AzureBlob($_SESSION['AZBLOB_NAME'], $_SESSION['AZBLOB_KEY']);
}

/*$app->get('/', function (Request $request, Response $response, array $args) {
    return $this->get('view')->render($response, 'home.twig', ['message' => 'hello']);
});
$app->run();
die;*/

$app->get('/', function (Request $request, Response $response, array $args) use ($app) {
    if ([] !== $_COOKIE && isset($_COOKIE['AZBLOB_ACC'])) {
        $token = base64_decode($_COOKIE['AZBLOB_ACC']);
        $creds = explode('][', $token);
        $_SESSION['AZBLOB_NAME'] = $creds[0];
        $_SESSION['AZBLOB_KEY'] = $creds[1];
        setcookie('AZBLOB_ACC', base64_encode($token), time() + 2592000);
        $app->redirect('/', '/storage');
    }
    return $this->get('view')->render($response, 'home.twig', ['message' => 'hello']);
})
->setName('home');

$app->post('/storage', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {
    $params = (array) $request->getParsedBody();
    $storageName = $params['account_name'];
    $storageKey = $params['account_key'];
    $storageRemember = $params['remember_me'];
    $_SESSION['AZBLOB_NAME'] = $storageName;
    $_SESSION['AZBLOB_KEY'] = $storageKey;
    if (1 === (int) $storageRemember) {
        setcookie('AZBLOB_ACC', base64_encode($storageName . '][' . $storageKey), time() + 2592000);
    }
    return $response
        ->withHeader('Location', '/storage')
        ->withStatus(302);
})
->setName('storagePost');

$app->get('/storage', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {
    $result = $azureBlob->listContainers();
    return $this->get('view')->render($response, 'containers.twig', ['containers' => $result]);
})
->setName('storage');

$app->post('/storage/add-container', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {
    $params = (array) $request->getParsedBody();
    $container = $params['container'];
    $options = new CreateContainerOptions();
    $options->setPublicAccess('blob');
    try {
        $azureBlob->createContainer($container, $options);
    } catch (ServiceException $e) {
        throw $e;
    }
    return $response
        ->withHeader('Location', '/storage')
        ->withStatus(302);
});

$app->get('/storage/add-container', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {
    return $this->get('view')->render($response, 'container.twig');
});

$app->get('/storage/remove-container/{container}', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {
    $azureBlob->removeContainer($args['container']);
    return $response
        ->withHeader('Location', '/storage')
        ->withStatus(302);
});

$app->get('/storage/container/{container}', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {
    $result = $azureBlob->listBlobs($args['container']);
    return $this->get('view')->render($response, 'blobs.twig',
        ['blobs' => $result, 'ab' => $azureBlob, 'container' => $args['container']]
    );
});

$app->get('/storage/container/{container}/add-blob', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {
    return $this->get('view')->render($response, 'blob.twig', ['container' => $args['container']]);
});

$app->post('/storage/container/{container}/upload', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {
    $prefix = (isset($_POST['prefix']) && '' !== $_POST['prefix'] ? $_POST['prefix'] : null);
    if ([] !== $_FILES) {
        if (is_uploaded_file($_FILES['rawfile']['tmp_name'])) {
            $azureBlob->uploadFile(
                $args['container'],
                $_FILES['rawfile']['tmp_name'],
                $_FILES['rawfile']['type'],
                $_FILES['rawfile']['name'],
                $prefix
            );
        }
    }
    return $response
        ->withHeader('Location', '/storage/container/' . $args['container'])
        ->withStatus(302);
});

$app->get('/storage/container/{container}/remove-blob', function (Request $request, Response $response, array $args) use ($app, $azureBlob) {

    $blob = urldecode($_GET['blob']);
    $azureBlob->removeBlob($args['container'], $blob);
    return $response
        ->withHeader('Location', '/storage/container/' . $args['container'])
        ->withStatus(302);
});

$app->get('/logout', function (Request $request, Response $response, array $args) use ($app) {
    if ([] !== $_COOKIE && isset($_COOKIE['AZBLOB_ACC'])) {
        setcookie('AZBLOB_ACC', '', time() - 3600);
    }
    session_destroy();
    return $response
        ->withHeader('Location', '/')
        ->withStatus(302);
});

/*$app->error(function (\Exception $exception, $code) use ($app) {
//    var_dump($exception->getCode());die;
    switch ($code) {
        case 404:
            $message = 'Requested page could not be found';
            break;
        case 500:
            if (409 === $exception->getCode()) {
                $message = 'The specified container already exists.';
            } else {
                $message = 'Our chaos monkey was not prepared for this';
            }
            break;
        default:
            $message = $exception->getMessage();
            break;
    }
    return $this->get('view')->render($response, 'error.twig', [
        'message' => $message,
        'trace' => $exception->getTraceAsString(),
    ]);
});*/

$app->run();
