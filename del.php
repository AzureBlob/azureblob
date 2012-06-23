<?php session_start(); ?>
<?php

if (!isset ($_SESSION['azure_account']['account_name'])|| !isset ($_SESSION['azure_account']['account_key'])) {
    header('Location: /azureblob');
}

$container = 'plopstore';
$blobName = urldecode($_GET['filename']);

require_once 'lib/AzureBlob.php';
$azureBlob = new AzureBlob($_SESSION['azure_account']['account_name'], $_SESSION['azure_account']['account_key'], $_SESSION['azure_account']['account_uri']);
if ($azureBlob->removeBlob($container, $blobName)) {
    header('Location: /azureblob/browse.php');
}
/*
require_once 'WindowsAzure/WindowsAzure.php';
use WindowsAzure\Common\Configuration;
use WindowsAzure\Blob\BlobSettings;
use WindowsAzure\Blob\BlobService;
use WindowsAzure\Common\ServiceException;

$config = new Configuration();
$config->setProperty(BlobSettings::ACCOUNT_NAME, $_SESSION['azure_account']['account_name']);
$config->setProperty(BlobSettings::ACCOUNT_KEY, $_SESSION['azure_account']['account_key']);
$config->setProperty(BlobSettings::URI, $_SESSION['azure_account']['account_uri']);

// Create blob REST proxy.
$blobRestProxy = BlobService::create($config);

try {
    // Delete container.
    $blobRestProxy->deleteBlob($_SESSION['azure_container']['container'], $blob);
    header('Location: /azureblob/browse.php');
}
catch(ServiceException $e){
    // Handle exception based on error codes and messages.
    // Error codes and messages are here: 
    // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message."<br />";
}*/