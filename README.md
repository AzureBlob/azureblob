azureblob
=========

Sometimes you just need to browse your blob storage, therefor I created a simple PHP tool that just does that.

NOTE! requires the PEAR package WindowsAzure

```bash
  channel-discover pear.windowsazure.com
  pear install WindowsAzure/WindowsAzure-0.1.0
```

Nicest thing is it stores nothing of your account, unless you check the "remember me" checkbox, so usabable for a more general audience ;-)

The following resources have been followed to achieve this application

* **Blob Storage Howto:** https://www.windowsazure.com/en-us/develop/php/how-to-guides/blob-service/
* **Windows Azure Error Codes:** http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
* **Blob Storage Container ACL:** http://msdn.microsoft.com/en-us/library/windowsazure/dd179391.aspx

I now implemented [Zend Framework](http://framework.zend.com) to have a clean framework for rendering the interfaces, while maintaining a clean API to access the Windows Azure Storage components.

**NOTE**: I am still using the Windows Azure pear library as it is more up-to-date.

It uses the [Windows Azure SDK for PHP](https://github.com/WindowsAzure/azure-sdk-for-php) and some PEAR packages. See https://github.com/WindowsAzure/azure-sdk-for-php for more information.
You can now test-drive this Windows Azure Blob Storage browser tool yourself at http://azureblob.phpdev.nu.

Created by [DragonBe](https://github.com/DragonBe)
