# AzureBlob

<a href="https://azuredeploy.net/?repository=https://github.com/AzureBlob/azureblob" target="_blank">
    <img src="http://azuredeploy.net/deploybutton.png"/>
</a>

## Introduction

AzureBlob is a tool that allows you to manage blob storage containers and files on [Microsoft Azure Blob Storage]. It allows you to simply create and remove containers, upload, update or remove files for your [PHP](http://php.net) applications.

Simplicity is key, so we use [Silex](http://silex.sensiolabs.org) with [Twig](http://twig.sensiolabs.org) templates to provide a front-end. In the background we use a simple wrapper AzureBlob class that will proxy everything through to [WindowsAzure SDK for PHP](https://github.com/Azure/azure-sdk-for-php).

All dependencies are loaded through [Composer](http://getcomposer.org).

## Web Frontend

You can try it out yourself by visiting [azureblob.azurewebsites.net](http://azureblob.azurewebsites.net). You need to have already an existing Azure Blob Storage configured on [Microsoft Azure]. If you're interested in trying it out, sign up for a free trial at [Microsoft Azure].

## Roadmap

- Implement proper validation
- Implement Redis Caching for session data

## Licence

This work is [MIT licenced](http://opensource.org/licenses/MIT). Please read the [LICENCE](LICENCE) for more details.


[Microsoft Azure]: http://azure.microsoft.com
[Microsoft Azure Blob Storage]: http://azure.microsoft.com/en-us/services/storage/
