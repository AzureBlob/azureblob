<?php
session_start();

if (empty ($_POST) || !isset ($_POST['accessType']) || !isset ($_POST['label'])) {
    header('Location: /browse.php');
}

require_once 'WindowsAzure/WindowsAzure.php';
$accessTypes = array (
    0 => WindowsAzure\Blob\Models\PublicAccessType::NONE,
    1 => WindowsAzure\Blob\Models\PublicAccessType::BLOBS_ONLY,
    2 => WindowsAzure\Blob\Models\PublicAccessType::CONTAINER_AND_BLOBS,
);
if (!in_array((int) $_POST['accessType'], array_keys($accessTypes))) {
    header('Location: /browse.php');
}
$accessType = $accessTypes[(int) $_POST['accessType']];
$label = str_replace(' ', '_', $_POST['label']);

require_once 'lib/AzureBlob.php';
$azureBlob = new AzureBlob($_SESSION['azure_account']['account_name'], $_SESSION['azure_account']['account_key'], $_SESSION['azure_account']['account_uri']);
if ($azureBlob->createContainer($label, $accessType)) {
    $_SESSION['azure_container']['container'] = $label;
    header('Location: /browse.php');
}
?>
<p>Something went wrong</p>