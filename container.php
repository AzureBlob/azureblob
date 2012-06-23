<?php
session_start();
if (isset ($_POST['container'])) {
    $_SESSION['azure_container']['container'] = $_POST['container'];
}
header('Location: /browse.php');