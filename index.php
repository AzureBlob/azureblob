<?php
// Let's set everything first
date_default_timezone_set('Europe/Brussels');
ini_set('post_max_size','100MB');
ini_set('upload_max_filesize','100MB');
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR . get_include_path());
session_start();

// Define some constants first
define('AZURE_BLOB_FQD', 'plopster.blob.core.windows.net');
define('AZURE_CDN_FQD', 'az287601.vo.msecnd.net');
define('AZURE_BASE_URI', 'core.windows.net');
define('AZURE_BLOB_URI', 'blob.core.windows.net');
define('AZURE_TABLE_URI', 'table.core.windows.net');
define('AZURE_QUEUE_URI', 'queue.core.windows.net');
define('COOKIE_NAME', 'azure_account');
define('CONTAINER', 'azure_container');

// Let's set our pages up first
$pages = array ('index','browse','container','add','del','create','remove','logout','listqueues');
$page = isset ($_GET['page']) ? $_GET['page'] : 'index';

function render($contents, $key, $value)
{
	return str_replace('{{' . $key . '}}', $value, $contents);
}
function cdn($originalUrl)
{
	return str_replace(AZURE_BLOB_FQD, AZURE_CDN_FQD, $originalUrl);
}
// Let's capture 404 File not found exceptions first
if (!in_array($page, $pages)) {
	$page = '404';
}

// Now it's time to process the pages
switch ($page) {

	// Rendering the initial page
	case 'index':
		$data = file_get_contents(realpath('./tpl/index.tpl'));
		$remember = null;
		if (isset ($_COOKIE[COOKIE_NAME])) {
		    $_SESSION[COOKIE_NAME] = unserialize($_COOKIE[COOKIE_NAME]);
			$remember = 'checked="checked"';
		}
		$keys = array ('account_name', 'account_key', 'account_uri');
		foreach ($keys as $key) {
			$data = render($data, $key, isset ($_SESSION[COOKIE_NAME][$key]) ? $_SESSION[COOKIE_NAME][$key] : null);
		}
		$data = render($data, 'remember', $remember);
		echo $data;
		break;

	// Browse the files stored on a windows azure blob storage
	case 'browse':
	    if(!empty ($_POST)) {
			if (isset ($_POST['remember_me'])) {
				unset ($_POST['remember_me']);
				setcookie(COOKIE_NAME, serialize($_POST), time() + (60 * 60 * 24 * 14));
			}
			$_SESSION[COOKIE_NAME] = $_POST;
		}
		require_once 'AzureBlob.php';
		$azureBlob = new AzureBlob($_SESSION[COOKIE_NAME]['account_name'], $_SESSION[COOKIE_NAME]['account_key'], $_SESSION[COOKIE_NAME]['account_uri']);
		$data = file_get_contents(realpath('./tpl/browse.tpl'));

		$containers = $azureBlob->listContainers();
		$containerList = $containers->getContainers();
		$default = $containerList[0]->getName();
		$blobs = array ();
		if (!isset ($_SESSION[CONTAINER]) || !isset ($_SESSION[CONTAINER]['container'])) {
			$_SESSION[CONTAINER]['container'] = $default;
		}
		$data = render($data, 'current_container', $_SESSION[CONTAINER]['container']);

		$containerItemList = array ();
		$containerData = file_get_contents(realpath('./tpl/container.tpl'));
		foreach ($containerList as $containerItem) {
			$containerOption = $containerData;
			$containerOption = render($containerOption, 'container_name', $containerItem->getName());
			$containerOption = render($containerOption, 'container_selected', $_SESSION[CONTAINER]['container'] === $containerItem->getName() ? 'selected="selected"' : null);
			$containerItemList[] = $containerOption;
		}
		$data = render($data, 'container_list', implode(PHP_EOL, $containerItemList));

		$blobs = $azureBlob->listBlobs($_SESSION[CONTAINER]['container']);
		$idx = 0;
		$blobList = array ();
		$rowData = file_get_contents(realpath('./tpl/row.tpl'));
		foreach ($blobs as $blob) {
		    $row = $rowData;
			$rowClass = 0 === $idx % 2 ? 'even' : 'odd';
			$row = render($row, 'class', $rowClass);
			$row = render($row, 'blob_name', $blob->getName());
			$row = render($row, 'blob_url', cdn($blob->getUrl()));
			$row = render($row, 'blob_type', $blob->getProperties()->getContentType());
			$row = render($row, 'blob_date', $blob->getProperties()->getLastModified()->format('r'));
			$blobList[] = $row;
		}

		$data = render($data, 'blob_list', implode(PHP_EOL, $blobList));

		echo $data;
		break;
	case 'add':
		if (!isset ($_SESSION[COOKIE_NAME]['account_name'])|| !isset ($_SESSION[COOKIE_NAME]['account_key'])) {
		    header('Location: /?page=browse');
		}
		if (!isset ($_FILES['blob'])) {
			$data = file_get_contents(realpath('./tpl/add.tpl'));
			echo $data;
		} else {
			$contents = file_get_contents($_FILES['blob']['tmp_name']);
			$name = $_FILES['blob']['name'];
			require_once 'WindowsAzure/WindowsAzure.php';
			$mimeType = (isset ($_POST['mimeType']) ? $_POST['mimeType'] : WindowsAzure\Common\Internal\Resources::BINARY_FILE_TYPE);

			require_once 'AzureBlob.php';
			$azureBlob = new AzureBlob($_SESSION[COOKIE_NAME]['account_name'], $_SESSION[COOKIE_NAME]['account_key'], $_SESSION[COOKIE_NAME]['account_uri']);
			$container = $_SESSION[CONTAINER]['container'];
			if ($azureBlob->addBlob($container, $name, $contents, array ('content-type' => $mimeType))) {
				header('Location: /?page=browse');
			}
		}
		break;
	case 'del':
		if (!isset ($_SESSION[COOKIE_NAME]['account_name'])|| !isset ($_SESSION[COOKIE_NAME]['account_key'])) {
		    header('Location: /?page=browse');
		}
		$container = $_SESSION['azure_container']['container'];
		$blobName = urldecode($_GET['filename']);

		require_once 'AzureBlob.php';
		$azureBlob = new AzureBlob($_SESSION[COOKIE_NAME]['account_name'], $_SESSION[COOKIE_NAME]['account_key'], $_SESSION[COOKIE_NAME]['account_uri']);
		if ($azureBlob->removeBlob($container, $blobName)) {
		    header('Location: /?page=browse');
		}
		break;
	case 'create':
		if (!isset ($_SESSION[COOKIE_NAME]['account_name'])|| !isset ($_SESSION[COOKIE_NAME]['account_key'])) {
		    header('Location: /?page=browse');
		}
		require_once 'WindowsAzure/WindowsAzure.php';
		$accessTypes = array (
		    0 => WindowsAzure\Blob\Models\PublicAccessType::NONE,
			1 => WindowsAzure\Blob\Models\PublicAccessType::BLOBS_ONLY,
			2 => WindowsAzure\Blob\Models\PublicAccessType::CONTAINER_AND_BLOBS,
		);
		if (!in_array((int) $_POST['accessType'], array_keys($accessTypes))) {
		    header('Location: /?page=browse');
		}
		$accessType = $accessTypes[(int) $_POST['accessType']];
		$label = str_replace(' ', '_', $_POST['label']);

		require_once 'lib/AzureBlob.php';
		$azureBlob = new AzureBlob($_SESSION[COOKIE_NAME]['account_name'], $_SESSION[COOKIE_NAME]['account_key'], $_SESSION[COOKIE_NAME]['account_uri']);
		if ($azureBlob->createContainer($label, $accessType)) {
			$_SESSION['container']['container'] = $label;
		    header('Location: /?page=browse');
		}
		break;
	case 'container':
		if (isset ($_POST['container'])) {
		    $_SESSION[CONTAINER]['container'] = $_POST['container'];
		}
		header('Location: /?page=browse');
		break;
	case 'remove':
		require_once 'lib/AzureBlob.php';
		$azureBlob = new AzureBlob($_SESSION[COOKIE_NAME]['account_name'], $_SESSION[COOKIE_NAME]['account_key'], $_SESSION[COOKIE_NAME]['account_uri']);

		$containers = $azureBlob->listContainers()->getContainers();

		$default = $containers[0]->getName();

		if ($azureBlob->removeContainer($_SESSION[CONTAINER]['container'])) {
		    $_SESSION[CONTAINER]['container'] = $default;
			header('Location: /?page=browse');
		}
		break;
	case 'logout':
		unset($_SESSION[COOKIE_NAME]);
		unset($_SESSION[CONTAINER]);
		header('Location: /');
		break;
        case 'listqueues':
            require_once 'WindowsAzure/WindowsAzure.php';
            $config = new WindowsAzure\Common\Configuration();
            $config->setProperty(WindowsAzure\Queue\QueueSettings::ACCOUNT_NAME, $_SESSION[COOKIE_NAME]['account_name']);
            $config->setProperty(WindowsAzure\Queue\QueueSettings::URI, sprintf('%s.%s', $_SESSION[COOKIE_NAME]['account_name'], AZURE_QUEUE_URI));
            $config->setProperty(WindowsAzure\Queue\QueueSettings::ACCOUNT_KEY, $_SESSION[COOKIE_NAME]['account_key']);
            
            $queueRestProxy = WindowsAzure\Queue\QueueService::create($config);
            $queues = array ();
            try {
                $queues = $queueRestProxy->listQueues();
            } catch (WindowsAzure\Common\ServiceException $e) {
                echo sprintf('%s: %s', $e->getCode(), $e->getMessage());
            }
            var_dump($queues);
            
            break;
	case '404':
	default:
		$data = file_get_contents(realpath('./tpl/404.tpl'));
		header('HTTP/1.1 404 Not Found');
		echo $data;
		break;
}
