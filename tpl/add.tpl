
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; Charset=UTF-8">
        <title>Windows Azure Blob Browser | Add a new blob</title>
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
                <div id="center">
                    <div id="splash">
                        
                        <div class="product"></div>
                        <div class="fileLoader">
                            <h1>Load up your blob file</h1>
                            <p><cite>Maximum filesize (50 MB)</cite></p>

                            <form name="file_uploader" action="/?page=add" enctype="multipart/form-data" method="post">

                                <input type="file" name="blob">
                                <select name="mimeType" id="mimeType">
                                    <option value="application/octed-stream">Binary</option>
                                    <option value="text/plain">Text</option>
                                    <option value="image/png">PNG</option>
                                    <option value="image/jpg">JPG/JPEG</option>
                                    <option value="video/m4v">m4v Video</option>
                                    <option value="video/quicktime">Quicktime</option>
                                    <option value="application/vnd.ms-powerpoint">Microsoft PowerPoint</option>
                                    <option value="application/vnd.ms-excel">Microsoft Excel</option>
                                    <option value="application/msword">Microsoft Word</option>
                                    <option value="application/x-iwork-keynote-sffkey">Apple Keynote</option>
                                    <option value="application/pdf">Acrobat PDF</option>
                                </select>
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
