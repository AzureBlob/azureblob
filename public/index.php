<?php
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

$app = new Silex\Application();
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../templates',
));
if ('production' !== AZBLOB_ENV) {
    $app['debug'] = true;
}

$azureBlob = new \AzureBlob\AzureBlob();
if ([] !== $_SESSION) {
    $azureBlob = new \AzureBlob\AzureBlob($_SESSION['AZBLOB_NAME'], $_SESSION['AZBLOB_KEY']);
}

$app->get('/', function () use ($app) {
    if ([] !== $_COOKIE && isset($_COOKIE['AZBLOB_ACC'])) {
        $token = base64_decode($_COOKIE['AZBLOB_ACC']);
        $creds = explode('][', $token);
        $_SESSION['AZBLOB_NAME'] = $creds[0];
        $_SESSION['AZBLOB_KEY'] = $creds[1];
        setcookie('AZBLOB_ACC', base64_encode($token), time() + 2592000);
        return $app->redirect($app['url_generator']->generate('storage'));
    }
    return $app['twig']->render('home.twig', ['message' => 'hello']);
})
->bind('home');

$app->post('/storage', function (\Symfony\Component\HttpFoundation\Request $request) use ($app, $azureBlob) {
    $storageName = $request->get('account_name');
    $storageKey = $request->get('account_key');
    $storageRemember = $request->get('remember_me');
    $_SESSION['AZBLOB_NAME'] = $storageName;
    $_SESSION['AZBLOB_KEY'] = $storageKey;
    if (1 === (int) $storageRemember) {
        setcookie('AZBLOB_ACC', base64_encode($storageName . '][' . $storageKey), time() + 2592000);
    }
    return $app->redirect($app['url_generator']->generate('storage'));
})
->bind('storagePost');

$app->get('/storage', function () use ($app, $azureBlob) {
    $result = $azureBlob->listContainers();
    return $app['twig']->render('containers.twig', ['containers' => $result]);
})
->bind('storage');

$app->post('/storage/add-container', function (\Symfony\Component\HttpFoundation\Request $request) use ($app, $azureBlob) {
    $container = $request->get('container');
    $options = new \WindowsAzure\Blob\Models\CreateContainerOptions();
    $options->setPublicAccess('blob');
    try {
        $azureBlob->createContainer($container, $options);
    } catch (\WindowsAzure\Common\ServiceException $e) {
        throw $e;
    }
    return $app->redirect($app['url_generator']->generate('storage'));
});

$app->get('/storage/add-container', function () use ($app, $azureBlob) {
    return $app['twig']->render('container.twig');
});

$app->get('/storage/remove-container/{container}', function ($container) use ($app, $azureBlob) {
    $azureBlob->removeContainer($container);
    return $app->redirect($app['url_generator']->generate('storage'));
});

$app->get('/storage/container/{container}', function ($container) use ($app, $azureBlob) {
    $result = $azureBlob->listBlobs($container);
    return $app['twig']->render('blobs.twig',
        ['blobs' => $result, 'ab' => $azureBlob, 'container' => $container]
    );
});

$app->get('/storage/container/{container}/add-blob', function ($container) use ($app, $azureBlob) {
    return $app['twig']->render('blob.twig', ['container' => $container]);
});

$app->post('/storage/container/{container}/upload', function ($container) use ($app, $azureBlob) {
    $prefix = (isset($_POST['prefix']) && '' !== $_POST['prefix'] ? $_POST['prefix'] : null);
    if ([] !== $_FILES) {
        if (is_uploaded_file($_FILES['rawfile']['tmp_name'])) {
            $azureBlob->uploadFile(
                $container,
                $_FILES['rawfile']['tmp_name'],
                $_FILES['rawfile']['type'],
                $_FILES['rawfile']['name'],
                $prefix
            );
        }
    }
    return $app->redirect($app['url_generator']->generate('storage') . '/container/' . $container);
});

$app->get('/storage/container/{container}/remove-blob', function ($container) use ($app, $azureBlob) {

    $blob = urldecode($_GET['blob']);
    $azureBlob->removeBlob($container, $blob);
    return $app->redirect($app['url_generator']->generate('storage') . '/container/' . $container);
});

$app->get('/logout', function () use ($app) {
    if ([] !== $_COOKIE && isset($_COOKIE['AZBLOB_ACC'])) {
        setcookie('AZBLOB_ACC', '', time() - 3600);
    }
    session_destroy();
    return $app->redirect($app['url_generator']->generate('home'));
});

$app->error(function (\Exception $exception, $code) use ($app) {
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
    return $app['twig']->render('error.twig', [
        'message' => $message,
        'trace' => $exception->getTraceAsString(),
    ]);
});

$app->run();
