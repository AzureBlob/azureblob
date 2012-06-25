<?php session_start(); ?>
<?php
if (!isset ($_SESSION['azure_account']['account_name'])|| !isset ($_SESSION['azure_account']['account_key'])) {
    header('Location: /');
}
if (isset ($_FILES['blob'])) {
    $contents = file_get_contents($_FILES['blob']['tmp_name']);
    $name = $_FILES['blob']['name'];

    require_once 'lib/AzureBlob.php';
    $azureBlob = new AzureBlob($_SESSION['azure_account']['account_name'], $_SESSION['azure_account']['account_key'], $_SESSION['azure_account']['account_uri']);
    $container = $_SESSION['azure_container']['container'];
    if ($azureBlob->addBlob($container, $name, $contents)) {
        header('Location: /browse.php');
    }
                        
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
                <div id="center">
                    <div id="splash">
                        
                        <div class="product"></div>
                        <div class="fileLoader">
                            <h1>Load up your blob file</h1>
                            <p><cite>Maximum filesize (50 MB)</cite></p>

                            <form name="file_uploader" action="/add.php" enctype="multipart/form-data" method="post">

                                <input type="file" name="blob">
                                <input type="submit" value="Upload">

                            </form>
                        </div>
                        
                    </div>
                </div>
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