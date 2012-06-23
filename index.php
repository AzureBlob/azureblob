<?php session_start(); ?>
<?php
if (isset ($_COOKIE['azure_account'])) {
    $_SESSION['azure_account'] = unserialize($_COOKIE['azure_account']);
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
            
            <form name="signin" action="/browse.php" method="post">
                <dl id="azure_account_details">
                    <dt><label for="account_name">Account name:</label></dt>
                    <dd><input type="text" name="account_name" id="account_name" value="<?php echo (isset ($_SESSION['azure_account']['account_name']) ? $_SESSION['azure_account']['account_name'] : null) ?>"></dd>
                    <dt><label for="account_key">Primary key:</label></dt>
                    <dd><input type="text" name="account_key" id="account_key" value="<?php echo (isset ($_SESSION['azure_account']['account_key']) ? $_SESSION['azure_account']['account_key'] : null) ?>"></dd>
                    <dt><label for="account_uri">Storage URI:</label></dt>
                    <dd><input type="text" name="account_uri" id="account_key" value="<?php echo (isset ($_SESSION['azure_account']['account_uri']) ? $_SESSION['azure_account']['account_uri'] : null) ?>"></dd>
                    <dt><label for="remember_me">Remember me:</label></dt>
                    <dd><input type="checkbox" name="remember_me" id="remember_me" value="1" <?php echo (isset ($_COOKIE['azure_account']) ? 'checked="checked"' : null) ?>></dd>
                    <dt>&nbsp;</dt>
                    <dd><input type="submit" value="Browse"></dd>
                </dl>
            </form>
            
        </div>
    </body>
</html>