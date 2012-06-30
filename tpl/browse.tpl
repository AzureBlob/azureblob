<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; Charset=UTF-8">
        <title>Windows Azure Blob Browser</title>
        <meta name="Description" content="A simple interface to browse and 
              manage files on Windows Azure">
        <meta name="Keywords" content="windows azure blob storage browse manage">
        <link rel="StyleSheet" href="/css/account.css">
    </head>
    
    <body>
        <div id="master">
            
            <div id="header" class="accountHeader">
                <div class="logo"><a href="/?page=browse">Windows Azure Blob Storage browser</a></div>
                <div class="navigation"><a href="/?page=logout">Logout</a></div>
                <div class="clear">&nbsp;</div>
            </div>
            
            <div id="middle">
                
                    
                    <div id="mgmtBar">
                        <div class="manageLeft">
                            <form action="/?page=container" method="post">
                                <label for="container">Container</label>:
                                <select name="container" id="container">
									{{container_list}}
								</select>
                                <input type="submit" value="switch">
                                <a href="/?page=remove" onClick="return confirm('This will remove current container [{{current_container}}] and all objects in it. Do you want to continue?');">Remove container [{{current_container}}]</a>
                            </form>
                        </div>

                        <div class="manageRight">
                            <form action="/?page=create" method="post">
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

                    
                        <table id="blobobjects">

                            <tr class="title">
                                <th>Name</th><th>URL</th><th>Mime-type</th><th>Last modified</th><th><a href="/?page=add">+</a></th>
                            </tr>

                            {{blob_list}}                    
                            
                        
                        </table>

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
