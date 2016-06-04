# FAQ 


## Do i need MongoDB ?
Currently Bartleby's api are relying on : PHP / MONGODB + JSON

## So What are the server prerequisits?

1. Min version of **PHP is 5**
2. **enable mcrypt** 
3. **allow cookies**
4. configure the **MongoDb php client**
5. to support **Server Sent Event** (aka SSE) on LINUX + APACHE we need to run as PHP as a module *apache mod_php* (run as Apache's user). **FAST CGI or CGI do not work currently**

### How to install mcrypt for PHP5 on Debian ?
```
sudo apt-get install php5-mcrypt
sudo php5enmod mcrypt
```
Then restart the web server.

####  How to test that mcrypt is available?
```
<BaseURL>www/tools/is-mcrypt-supported.php
```

####  How to test that cookies are enabled?
```
<BaseURL>www/tools/are-cookies-enabled.php
```
