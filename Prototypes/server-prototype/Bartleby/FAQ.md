# FAQ 

## Do i need MongoDB ?
Currently Bartleby's api are relying on : PHP / MONGODB + JSON

## So What are the server prerequisits?

You can deploy a [Zero Config Server] (../Installation.md)

1. Min version of **PHP is 5**
2. **enable mcrypt** 
3. **allow cookies**
4. configure the **MongoDb php client**
5. to support **Server Sent Event** (aka SSE) on LINUX + APACHE we need to run as PHP as a module *apache mod_php* (run as Apache's user). **FAST CGI or CGI do not work currently**
6. Be sure to enable **Semaphore / Shared memory support**

### How to install mcrypt for PHP5 on Debian ?
```
sudo apt-get install php5-mcrypt
sudo php5enmod mcrypt
```
Then restart the web server.

####  How to test that mcrypt is available?
```
<BaseURL>www/Diagnostics/is-mcrypt-supported.php
```

####  How to test that cookies are enabled?
```
<BaseURL>www/Diagnostics/are-cookies-enabled.php
```

####  How to test that semaphores and shared memory support are enabled?
```
<BaseURL>www/Diagnostics/are-semaphores-enabled.php
```


# Known Issues

## PHP 5.6 *always_populate_raw_post_data*
If you are using php 5.6 en encounter the warning "Deprecated: Automatically populating $HTTP_RAW_POST_DATA is deprecated and will be removed in a future version." 
This is a Langage level BUG. You should set 'always_populate_raw_post_data = -1' in your php.ini 