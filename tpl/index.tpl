<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; Charset=UTF-8">
        <title>Windows Azure Blob Browser</title>
        <meta name="Description" content="A simple interface to browse and 
              manage files on Windows Azure">
        <meta name="Keywords" content="windows azure blob storage browse manage">
        <link rel="StyleSheet" href="/css/style.css">
    </head>
    
    <body>
        <div id="master">
            
            <div id="header"><h1>Windows Azure Blob Storage browser</h1></div>
            
            <div id="middle">
                
                <div class="product">&nbsp;</div>
                
                <div class="form">
                    <h1>Sign in</h1>
                    <form id="WindowsAzureAccount" name="signin" action="/?page=browse" method="post">
                        <dl id="azure_account_details">
                            <dt><label for="account_name">Account name:</label></dt>
                            <dd><input type="text" name="account_name" id="account_name" value="{{account_name}}"></dd>
                            <dt><label for="account_key">Primary key:</label></dt>
                            <dd><input type="text" name="account_key" id="account_key" value="{{account_key}}"></dd>
                            <dt><label for="account_uri">Storage URI:</label></dt>
                            <dd><input type="text" name="account_uri" id="account_key" value="{{account_uri}}"></dd>
                            <dt><label for="remember_me">Remember me:</label></dt>
                            <dd><input type="checkbox" name="remember_me" id="remember_me" value="1" {{remember}}></dd>
                            <dt>&nbsp;</dt>
                            <dd><input type="submit" value="Sign in"></dd>
                        </dl>
                    </form>
					<h2>Privacy</h2>
					<p>You're credentials are not kept, only stored in a session for the duration of your
					visit on the site. When checking "remember me" a cookie is stored on your local computer
					with your account details.</p>
					<h2>Windows Azure</h2>
					<p>This service only works on <strong>Windows Azure Blob Storage</strong> accounts. You
					can always suggest new features on our 
					<a href="http://github.com/PHPBenelux/azureblob/issues">Github issue list</a>.</p>
                </div>
                <div class="clear">&nbsp;</div>
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
