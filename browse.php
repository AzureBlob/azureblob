<?php session_start(); ?>
<?php
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
            
            <div style="text-align: right;"><a href="/azureblob/logout.php">Logout</a></div>
            
            <?php 
                if(!empty ($_POST)) {
                    if (isset ($_POST['remember_me'])) {
                        unset ($_POST['remember_me']);
                        setcookie('azure_account', serialize($_POST), time() + (60 * 60 * 24 * 14));
                    }
                    $_SESSION['azure_account'] = $_POST;
                }
            ?>
            <?php if (!isset ($_SESSION['azure_account']['account_name']) || !isset ($_SESSION['azure_account']['account_key'])): ?> 
            
                <div class="warning">Missing account name or key. <a href="/azureblob/index.php">Please try again</a>.</div>
                
            <?php else: ?>
            
                <?php
                    
                    $config = new Configuration();
                    $config->setProperty(BlobSettings::ACCOUNT_NAME, $_SESSION['azure_account']['account_name']);
                    $config->setProperty(BlobSettings::ACCOUNT_KEY, $_SESSION['azure_account']['account_key']);
                    $config->setProperty(BlobSettings::URI, $_SESSION['azure_account']['account_uri']);

                    // Create blob REST proxy.
                    $blobRestProxy = BlobService::create($config);

                    $blobs = array ();

                    try {
                        // List blobs.
                        $blob_list = $blobRestProxy->listBlobs("plopstore");
                        $blobs = $blob_list->getBlobs();

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

                <?php if (empty ($blobs)): ?>

                    <p>No files currently stored here.</p>

                <?php else: ?>

                    <table>

                        <tr>
                            <th>Name</th><th>URL</th><th>Mime-type</th><th>Last modified</th><th><a href="/azureblob/add.php">+</a></th>
                        </tr>

                    <?php foreach ($blobs as $blob): ?>

                        <tr>
                            <td><?php echo $blob->getName() ?></td>
                            <td><a href="<?php echo $blob->getUrl() ?>"><?php echo $blob->getUrl() ?></a></td>
                            <td><?php echo $blob->getProperties()->getContentType() ?></td>
                            <td><?php echo $blob->getProperties()->getLastModified()->format('r'); ?></td>
                            <td><a href="/azureblob/del.php?filename=<?php echo urlencode($blob->getName()) ?>">x</a></td>
                        </tr>

                    <?php endforeach; ?>

                    </table>

                <?php endif; ?>
            <?php endif; ?>
            
        </div>
    </body>
</html>