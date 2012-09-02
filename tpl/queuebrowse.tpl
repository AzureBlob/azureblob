<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; Charset=UTF-8">
        <title>Windows Azure Queue Browser</title>
        <meta name="Description" content="A simple interface to browse and 
              manage queues on Windows Azure">
        <meta name="Keywords" content="windows azure blob storage browse manage">
        <link rel="StyleSheet" href="/css/account.css">
    </head>
    
    <body>
        <div id="master">
            
            <div id="header" class="accountHeader">
                <div class="logo"><a href="/?page=browse">Windows Azure Queue browser</a></div>
                <div class="navigation"><a href="/?page=logout">Logout</a></div>
                <div class="clear">&nbsp;</div>
            </div>
            
            <div id="middle">
                
                    
                    <div id="mgmtBar">
                        <div class="manageLeft">
                            <form action="/?page=switchqueue" method="post">
                                <label for="queue">Queue</label>:
                                <select name="queue" id="queue">
                                        {{queue_list}}
                                </select>
                                <input type="submit" value="switch">
                                <a href="/?page=removequeue" onClick="return confirm('This will remove current queue [{{current_queue}}] and all objects in it. Do you want to continue?');">Remove queue [{{current_queue}}]</a>
                            </form>
                        </div>

                        <div class="manageRight">
                            <form action="/?page=createqueue" method="post">
                                <label for="label">Label for new queue</label>:
                                <input type="text" name="label" id="label" value="">
                                <input type="submit" value="create">
                            </form>
                        </div>
                    </div>
                    <div class="clear">&nbsp;</div>

                    
                        <table id="queueobjects">

                            <tr class="title">
                                <th>Name</th><th>URL</th><th>Last modified</th><th><a href="/?page=addmessage">+</a></th>
                            </tr>

                            {{message_list}}                    
                            
                        
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
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-32888028-1']);
_gaq.push(['_setDomainName', 'phpdev.nu']);
_gaq.push(['_trackPageview']);
(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
    </body>
</html>
