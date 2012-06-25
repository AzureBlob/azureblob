<?php session_start(); ?>
<?php

if (!isset ($_SESSION['azure_account']['account_name'])|| !isset ($_SESSION['azure_account']['account_key'])) {
    header('Location: /');
}

$container = $_SESSION['azure_container']['container'];
$blobName = urldecode($_GET['filename']);

require_once 'lib/AzureBlob.php';
$azureBlob = new AzureBlob($_SESSION['azure_account']['account_name'], $_SESSION['azure_account']['account_key'], $_SESSION['azure_account']['account_uri']);
if ($azureBlob->removeBlob($container, $blobName)) {
    header('Location: /browse.php');
}
?>
<p>Something has gone wrong, <a href="/browse.php">please try again</a>.</p>