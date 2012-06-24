<?php
session_start();

require_once 'lib/AzureBlob.php';
$azureBlob = new AzureBlob($_SESSION['azure_account']['account_name'], $_SESSION['azure_account']['account_key'], $_SESSION['azure_account']['account_uri']);

$containers = $azureBlob->listContainers()->getContainers();

$default = $containers[0]->getName();

if ($azureBlob->removeContainer($_SESSION['azure_container']['container'])) {
    $_SESSION['azure_container']['container'] = $default;
    header('Location: /browse.php');
}
?>
<p>Something went wrong</p>