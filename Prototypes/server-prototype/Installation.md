# Zero Config Dockerized Installation

1. install docker for mac
2. call `./install.sh` from `Script/Docker/`
 
That's all folks! 

You need more Details on the procedure check [Scripts/Docker/ReadMe.md] (Scripts/Docker/ReadMe.md)

# Requirements

- We officially support Linux (debian is recommanded) and macOS 10.1X
- Any Host (excepted: localhost ) should be configured with SSL support to conform to App transport Security.
- Installation of MongoDB 3.X is required

# PHP configuration

 1. Min version of **PHP is 5.X**(PHP7 is not currently validated)
 2. **enable mcrypt**
 3. **allow cookies**
 4. **semaphore support**
 4. configure the **MongoDb php client**
 5. to support **Server Sent Event** (aka SSE) on LINUX + APACHE we need to run as PHP as a module *apache mod_php* (run as Apache's user). **FAST CGI or CGI do not work currently**

# App - Configurations

## Check the consistency of the Current html/Configuration.php

Check the stage carefully.

```php
    const LOCAL='local';
    const DEVELOPMENT='development';
    const ALTERNATIVE='alternative';
    const PRODUCTION='production';
```

## BartlebySync module Configuration

This should be normally done by BartlebyCLI during app install un-bundling phase.
But you can adapt manually the Module const in _BartlebySync/BartlebySyncConfiguration.php_

```php

   $hostsPerStages = array(
        Stages::DEVELOPMENT => 'https://dev.api.<host>.com/',
        Stages::PRODUCTION => 'https://api.<host>.com/',
        Stages::ALTERNATIVE => 'https://alt.<host>.com',
        Stages::LOCAL => 'http://<host>.local/',
    );

    $this->_autoDefineHostAndStage($hostsPerStages);


    if ($stage==Stages::DEVELOPMENT){
        $this->_VERSION = 'v1';
        $this->_SECRET_KEY = 'Bartleby-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; // 32 Bytes min
        $this->_SHARED_SALT = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
        define('BARTLEBY_SYNC_SECRET_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); // Used create the data system folder
        define('REPOSITORY_HOST','https://<REPOSITORY_HOST>');
        define('REPOSITORY_WRITING_PATH', dirname(__DIR__) .DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR);
    }elseif($stage==Stages::PRODUCTION){
       $this->_VERSION = 'v1';
       $this->_SECRET_KEY = 'Bartleby-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; // 32 Bytes min
       $this->_SHARED_SALT = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
       define('BARTLEBY_SYNC_SECRET_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); // Used create the data system folder
       define('REPOSITORY_HOST','https://<REPOSITORY_HOST>');
       define('REPOSITORY_WRITING_PATH', dirname(__DIR__) .DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR);
    }elseif($stage==Stages::ALTERNATIVE){
       $this->_VERSION = 'v1';
       $this->_SECRET_KEY = 'Bartleby-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; // 32 Bytes min
       $this->_SHARED_SALT = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
       define('BARTLEBY_SYNC_SECRET_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); // Used create the data system folder
       define('REPOSITORY_HOST','https://<REPOSITORY_HOST>');
       define('REPOSITORY_WRITING_PATH', dirname(__DIR__) .DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR);
    }else{
       $this->_VERSION = 'v1';
       $this->_SECRET_KEY = 'Bartleby-55db2d88d520999D7887D9D99aE9-93X67-XXX'; // 32 Bytes min
       $this->_SHARED_SALT = 'xyx38-d890x-899h-123e-30x6-XXXX';
       define('BARTLEBY_SYNC_SECRET_KEY', '0f3600388455958834aca556180cfXXX'); // Used create the data system folder
       define('REPOSITORY_HOST', 'http://yd.repository.local:80/');
       define('REPOSITORY_WRITING_PATH', dirname(__DIR__) . '/files/');
    }
```

# Diagnostics

## Bsync file system
 
You can use the endpoint BartlebySync install to test your installation.

```
http -v -f POST ${BASE_URL}/BartlebySync/install/` 
```

Success == 200 (if the repository was already existing ) or 201 (if the repository was created)

```
HTTP/1.1 200 OK
Access-Control-Allow-Methods: *
Access-Control-Allow-Origin: *
Connection: Keep-Alive
Content-Length: 4
Content-Type: application/json
Date: Tue, 05 Jul 2016 15:57:24 GMT
Keep-Alive: timeout=5, max=100
Server: Apache
X-Powered-By: PHP/5.6.10

"{}"
```


If necessary you can adjust the fs right.

Response example in case of failure.
It gives you information about the OS user, and describes what works and what 

```

HTTP/1.1 417 Expectation Failed
Access-Control-Allow-Methods: *
Access-Control-Allow-Origin: *
Connection: Keep-Alive
Content-Length: 722
Content-Type: application/json
Date: Tue, 05 Jul 2016 15:50:07 GMT
Keep-Alive: timeout=5, max=100
Server: Apache/2.2.22
X-Powered-By: PHP/5.4.45-0+deb7u4

{
    "message": {
        "GateKeeper.php(75)": "Applicable rule level = 1. ",
        "IOManagerFS.php(209)": "Current system user: bartlebys",
        "IOManagerFS.php(211)": "Is writable: no",
        "IOManagerFS.php(215)": "Unable to open /home/bartlebys/domains/demo.bartlebys.org/public_html/files/",
        "IOManagerFS.php(229)": "Folder creation as failed: /home/bartlebys/domains/demo.bartlebys.org/public_html/files/",
        "IOManagerFS.php(241)": "Creation of /home/bartlebys/domains/demo.bartlebys.org/public_html/files/temp.txt has failed",
        "IOManagerFS.php(255)": "Creation of /home/bartlebys/domains/demo.bartlebys.org/public_html/temp.txt has failed"
    },
    "repositoryPath": "/home/bartlebys/domains/demo.bartlebys.org/public_html/files/"
}
```
