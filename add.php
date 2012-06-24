<?php session_start(); ?>
<?php
if (!isset ($_SESSION['azure_account']['account_name'])|| !isset ($_SESSION['azure_account']['account_key'])) {
    header('Location: /');
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; Charset=UTF-8">
        <title>Windows Azure Blob Browser</title>
        <meta name="Description" content="A simple interface to browse and 
              manage files on Windows Azure">
        <meta name="Keywords" content="windows azure blob storage browse manage">
    </head>
    
    <body>
        <div id="master">
            
            <?php if (!isset ($_FILES['blob'])): ?>
            
                <form name="file_uploader" action="/add.php" enctype="multipart/form-data" method="post">

                    <input type="file" name="blob">
                    <input type="submit" value="Upload">

                </form>
            
            <?php else: ?>
            
            <?php
                $contents = file_get_contents($_FILES['blob']['tmp_name']);
                $name = $_FILES['blob']['name'];
                
                require_once 'lib/AzureBlob.php';
                $azureBlob = new AzureBlob($_SESSION['azure_account']['account_name'], $_SESSION['azure_account']['account_key'], $_SESSION['azure_account']['account_uri']);
                $container = $_SESSION['azure_container']['container'];
                if ($azureBlob->addBlob($container, $name, $contents)) {
                    echo '<script type="text/javascript">document.location.href=\'/browse.php\';</script>';
                }
            ?>
            
            <?php endif; ?>
        </div>
    </body>
</html>