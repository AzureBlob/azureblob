<?php session_start(); ?>
<?php set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR . get_include_path()); ?>
<?php 
	ini_set('error_log', './phperror.log');
	ini_set('error_reporting', true);
	ini_set('display_errors', true);
?>
<?php 
    if(!empty ($_POST)) {
        if (isset ($_POST['remember_me'])) {
            unset ($_POST['remember_me']);
            setcookie('azure_account', serialize($_POST), time() + (60 * 60 * 24 * 14));
        }
        $_SESSION['azure_account'] = $_POST;
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
        <link rel="StyleSheet" href="/account.css">
    </head>
    
    <body>
        <div id="master">
            
            <div id="header" class="accountHeader">
                <div class="logo"><a href="/browse.php">Windows Azure Blob Storage browser</a></div>
                <div class="navigation"><a href="/logout.php">Logout</a></div>
                <div class="clear">&nbsp;</div>
            </div>
            
            <div id="middle">
                <?php if (!isset ($_SESSION['azure_account']['account_name']) || !isset ($_SESSION['azure_account']['account_key'])): ?> 

                    <div class="warning">Missing account name or key. <a href="/index.php">Please try again</a>.</div>

                <?php else: ?>

                    <?php
                        require_once 'lib/AzureBlob.php';
                        $azureBlob = new AzureBlob($_SESSION['azure_account']['account_name'], $_SESSION['azure_account']['account_key'], $_SESSION['azure_account']['account_uri']);
                        $containers = $azureBlob->listContainers();

                        $test = $containers->getContainers();
                        $default = $test[0]->getName();
                        $blobs = array ();
                        if (!isset ($_SESSION['azure_container']) || !isset ($_SESSION['azure_container']['container'])) {
                            $blobs = $azureBlob->listBlobs($default);
                            $_SESSION['azure_container']['container'] = $default;
                        } else {
                            $blobs = $azureBlob->listBlobs($_SESSION['azure_container']['container']);
                        }
                    ?>

                    <div id="mgmtBar">
                        <div class="manageLeft">
                            <form action="/container.php" method="post">
                                <label for="container">Container</label>:
                                <select name="container" id="container">
                                    <?php foreach ($containers->getContainers() as $container): ?>
                                    <option value="<?php echo $container->getName() ?>" <?php echo ($container->getName() === $_SESSION['azure_container']['container'] ? 'selected="selected"' : null) ?>><?php echo $container->getName() ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="submit" value="switch">
                                <a href="/remove.php" onClick="return confirm('This will remove current container [<?php echo $_SESSION['azure_container']['container'] ?>] and all objects in it. Do you want to continue?');">Remove current container</a>
                            </form>
                        </div>

                        <div class="manageRight">
                            <form action="/create.php" method="post">
                                <label for="label">Label for new container</label>:
                                <input type="text" name="label" id="label" value="">
                                <label for="accessType">Access type</label>:
                                <select name="accessType" id="accessType">
                                    <option value="0">Private</option>
                                    <option value="1">Public on blob</option>
                                    <option value="2">Public on container</option>
                                </select>
                                <input type="submit" value="create">
                            </form>
                        </div>
                    </div>
                    <div class="clear">&nbsp;</div>

                    <?php if (empty ($blobs)): ?>

                    <p>No files currently stored here. <a href="/add.php">Add one right now.</a></p>

                    <?php else: ?>

                        <table id="blobobjects">

                            <tr class="title">
                                <th>Name</th><th>URL</th><th>Mime-type</th><th>Last modified</th><th><a href="/add.php">+</a></th>
                            </tr>

                        <?php $rowIdx = 0; ?>
                        <?php foreach ($blobs as $blob): ?>

                            <tr class="<?php echo (0 === $rowIdx % 2 ? 'even' : 'odd') ?>">
                                <td><?php echo $blob->getName() ?></td>
                                <td><a href="<?php echo $blob->getUrl() ?>"><?php echo $blob->getUrl() ?></a></td>
                                <td><?php echo $blob->getProperties()->getContentType() ?></td>
                                <td><?php echo $blob->getProperties()->getLastModified()->format('r'); ?></td>
                                <td><a href="/del.php?filename=<?php echo urlencode($blob->getName()) ?>">x</a></td>
                            </tr>
                            <?php $rowIdx++ ?>

                        <?php endforeach; ?>

                        </table>

                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div id="footer">
                <p>This online tool is not part of the <a href="http://www.windowsazure.com" 
                title="Windows Azure hosting solutions">Windows Azure</a> platform.</p>
                <p>Fork us on <a href="https://github.com/PHPBenelux/azureblob" title="Fork us on GitHub">GitHub</a> or read
                    <a href="http://dragonbe.azurewebsites.net" title="DragonBe in the cloud">DragonBe's cloud blog</a>.</p>
                <p>This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-ShareAlike 3.0 Unported License</a>.<br><br>
                <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a></p>
            </div>
        </div>
    </body>
</html>
