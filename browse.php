<?php session_start(); ?>
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
                    require_once 'lib/AzureBlob.php';
                    $container = 'plopstore';
                    $azureBlob = new AzureBlob($_SESSION['azure_account']['account_name'], $_SESSION['azure_account']['account_key'], $_SESSION['azure_account']['account_uri']);
                    $blobs = $azureBlob->listBlobs($container);
                ?>

                
                <form action="/azureblob/container.php" method="post">
                    <label for="container">Container</label>:
                    <input type="text" name="container" id="container" value="<?php echo $container ?>">
                    <input type="submit" value="switch">
                </form>
                
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