<?php

var_dump($_SESSION);
die;

$_SESSION['azure_container']['container'] = null;
if (isset ($_POST['container'])) {
    $_SESSION['azure_container']['container'] = $_POST['container'];
}
header('Location: /azureblob/browse.php');