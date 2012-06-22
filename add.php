<?php session_start(); ?>
<?php
if (!isset ($_SESSION['azure_account']['account_name'])|| !isset ($_SESSION['azure_account']['account_key'])) {
    header('Location: /azureblob');
}
require_once 'WindowsAzure/WindowsAzure.php';

use WindowsAzure\Common\Configuration;
use WindowsAzure\Blob\BlobSettings;

use WindowsAzure\Blob\BlobService;
use WindowsAzure\Common\ServiceException;
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
            
                <form name="file_uploader" action="/azureblob/add.php" enctype="multipart/form-data" method="post">

                    <input type="file" name="blob">
                    <input type="submit" value="Upload">

                </form>
            
            <?php else: ?>
            
            <?php
            
                $config = new Configuration();
                $config->setProperty(BlobSettings::ACCOUNT_NAME, $_SESSION['azure_account']['account_name']);
                $config->setProperty(BlobSettings::ACCOUNT_KEY, $_SESSION['azure_account']['account_key']);
                $config->setProperty(BlobSettings::URI, $_SESSION['azure_account']['account_uri']);
                
                // Create blob REST proxy.
                $blobRestProxy = BlobService::create($config);
                
                $content = file_get_contents($_FILES['blob']['tmp_name']);
                $blob_name = str_replace(' ', '_', $_FILES['blob']['name']);

                try {
                    //Upload blob
                    $blobRestProxy->createBlockBlob("plopstore", $blob_name, $content);
                    echo '<script type="text/javascript">document.location.href=\'/azureblob/browse.php\';</script>';
                }
                catch(ServiceException $e){
                    // Handle exception based on error codes and messages.
                    // Error codes and messages are here: 
                    // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
                    $code = $e->getCode();
                    $error_message = $e->getMessage();
                    echo $code.": ".$error_message."<br />";
                }
            ?>
            
            
            <?php endif; ?>
        </div>
    </body>
</html>