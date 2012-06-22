azureblob
=========

Sometimes you just need to browse your blob storage, therefor I created a simple PHP tool that just does that.

NOTE! requires the PEAR package WindowsAzure

  channel-discover pear.windowsazure.com
  pear install WindowsAzure/WindowsAzure-0.1.0

At this point I hardcoded my container in, might want to shift this to another form-element

Nicest thing is it stores nothing of your account, unless you check the "remember me" checkbox, so usabable for a more general audience ;-)
