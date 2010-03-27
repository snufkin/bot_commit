About
=====

This is a Drupal module using the Drupal IRC bot (http://drupal.org/project/bot). It implements an XML-RPC method
to receive details of commits. 

Setup
=====

1. On the site where you run the bot just install the module and set the message key.
2. Copy the PHP script and the xmlrpc.inc from the scripts directory to the server where you push your commits. 
Edit and set the key to the one you just configured above, also set the server to your bot server.
Edit the post-receive hook in your repository, to have eg: /usr/share/bin/php /home/git/git-irc.php
