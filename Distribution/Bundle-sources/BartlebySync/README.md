# BartlebySync 1.0 

BartlebySync is a delta synchronizer for documents and data between devices.It allows to synchronizes local and distant grouped files tree.The standard synchronization topology relies on a client software and a light blind Restfull service, but can work locally and using P2P.


## Approach ##

- delegate as much as possible of the synchronization logic to the clients to distribute the load and to save server charge and bandwidth
- keep it as minimal and simple as possible
- do not focus on conflict resolution but on fault resilience (there is no transactional guarantee)
- allow very efficient caching and mem caching strategy (we will provide advanced implementation samples)
- support any encryption and cryptographic strategy
- allow advanced hashing strategy ( like : considering that a modified file should not be synchronized because the modification is not significant. e.g a metadata has changed.)

## HashMap  ##

For BartlebySync a **HashMap** is a dictionary with for a given folder the list of all its files relative path as a key and a Hash as a value or the inverse.

The master maintains one HashMap per root folder, the hash map is always crypted client side.


Json representation :

```javascript
	{
		 "hashToPath" : {
    		 "1952419745" : "47b2e7fb27643408f95f7c66d995fbe9.music",
    		 "2402594160" : "folder1/4fd6de231a723be15375552928c9c52a.track",
  		}
	}
```
## DeltaPathMap ##

A **DeltaPathMap** references the differences between two **HashMap** and furnish the logic to planify downloading or uploading command operations for clients according to their role.

Json representation :

```javascript
	{
		"createdPaths":[],
		"copiedPaths":["folder1/a.mp3","folder2/a.mp3"],
		"deletedPaths":[],
		"movedPaths":["x.txt","folder1/y.txt"],
		"updatedPaths":["folder1/4fd6de231a723be15375552928c9c52a.track"],
	}
```

## Synchronization process synopsis ##

With 1 Source client (Objc), 1 sync service(php), and n Destination clients(Objc)

1. Source -> downloads the **HashMap** (if there is no HashMap the delta will be the current local)
2. Source -> proceed to **DeltaPathMap**  creation and command provisionning
3. Source -> uploads files with a .<SyncID> prefix to the service
4. Source -> uploads the hasMap of the current root folder and finalize the transaction (un prefix the files, and call the sanitizing procedure =  removal of orpheans, **Optionaly** the synch server can send a push notification to the slave clients to force the step 5)
5. Destination -> downloads the current **HashMap** ( Or its HashMapView representation??)
6. Destination -> proceeds to **DeltaPathMap** creation and command provisionning
7. Destination -> downloads the files (on any missing file the task list is interrupted, the local hash map is recomputed and we step back to 5)
8. Destination -> on completion the synchronization is finalized. (We redownload the **HashMap** and compare to conclude if stepping back to 5 is required.)

# BartlebySync PHP #
A very simple PHP sync restfull service to use in conjonction with BartlebySync objc, swift client

### Status codes ###

* 1xx: Informational - Request received, continuing process
* 2xx: Success - The action was successfully received, understood, and accepted
* 3xx: Redirection - Further action must be taken in order to complete the request
* 4xx: Client Error - The request contains bad syntax or cannot be fulfilled
* 5xx: Server Error - The server failed to fulfill an apparently valid request

#### Notable client errors ####

* 401 => 'Unauthorized' : if auth is required
* 423 => 'Locked' : if locked

##### Status code references ####
[www.w3.org] (http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html), [www.ietf.org] (http://www.ietf.org/assignments/http-status-codes/http-status-codes.xml)

#### Commands  : ####

Any command is encoded in an array.
Json Encoded command [BCopy,<BDestination>,<BSource>] : [1,'a/a.caf','b/c/c.caf'] will copy the file from 'b/c/c.caf' to 'a/a.caf'

##### Sync CMD ####
```c
typedef NS_ENUM (NSUInteger,
                  BSyncCommand) {
    BCreate   = 0 , 
    BUpdate   = 1 ,
    BMove     = 2 , 
    BCopy     = 3 , 
    BDelete   = 4 
} ;


typedef NS_ENUM(NSUInteger,
                BSyncCMDParamRank) {
    BCommand     = 0,
    BDestination = 1,
    BSource      = 2
} ;

```



## Installation of BartlebySync PHP ## 

Prerequisite, you should have a deployed Bartleby's app.

### Copy the BartlebySync module folder

BartlebySync Module folder Should be installed beside Bartleby's folder.
By default the file repository will be located in a "files/" folder beside Bartleby's folder
Normally The Repository Folder "files/" should be allocated to the "www-data" user and the right  set to 711.

### BartlebySyncConfiguration.php

- Define the BARTLEBY_SYNC_CREATIVE_KEY and BARTLEBY_SYNC_SECRET_KEY
- Define if necessary BARTLEBY_SYNC_ROOT_PATH, REPOSITORY_HOST, REPOSITORY_WRITING_PATH 


### Bartleby's app Configuration.php

In getEndpointsSearchPaths() add the module's endpoint folder to the searchpaths
```php
    function getEndpointsSearchPaths() {
        $searchPaths = parent::getEndpointsSearchPaths();
        ...
        $searchPaths[]=dirname(dirname($this->_executionDirectory)).'/BartlebySync/EndPoints/';
        return $searchPaths;
    }
```

Add the persmission in _configure()

```php  
    ...
 
    // BartlebySync
    'BartlebySyncSupports->call'=>array('level' => PERMISSION_NO_RESTRICTION),
    'BartlebySyncInstall->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
    'BartlebySyncCreateTree->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
    'BartlebySyncTouchTree->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
    'BartlebySyncGetHashMap->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
    'BartlebySyncGetFile->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
    'BartlebySyncUploadFileTo->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
    'BartlebySyncFinalizeTransactionIn->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
    'BartlebySyncFinalizeTransactionIn->cleanUp'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),// May be suspended ( it is used on finalizeTransactionIn)
    'BartlebySyncRemoveGhosts->call'=>array('level' => PERMISSION_BY_IDENTIFICATION)
```

Add the routes aliases 

```php  
    protected function  _getEndPointsRouteAliases() {
        $routes = parent::_getEndPointsRouteAliases();
        $mapping = array(
            ... 
            // BartlebySync
            'GET:/BartlebySync/isSupported'=>array('BartlebySyncSupports','call'),
            'GET:/BartlebySync/reachable'=>array('Reachable','GET'),
            'POST:/BartlebySync/install'=>array('BartlebySyncInstall','call'),
            'POST:/BartlebySync/create/tree/{treeId}'=>array('BartlebySyncCreateTree','call'),
            'POST:/BartlebySync/touch/tree/{treeId}'=>array('BartlebySyncTouchTree','call'),
            'GET:/BartlebySync/tree/{treeId}'=>array('BartlebySyncTouchTree','call'),//touch alias
            'GET:/BartlebySync/hashMap/tree/{treeId}'=>array('BartlebySyncGetHashMap','call'),
            'GET:/BartlebySync/file/tree/{treeId}'=>array('BartlebySyncGetFile','call'),
            'POST:/BartlebySync/uploadFileTo/tree/{treeId}'=>array('BartlebySyncUploadFileTo','call'),
            'POST:/BartlebySync/finalizeTransactionIn/tree/{treeId}'=>array('BartlebySyncFinalizeTransactionIn','call'),
            'POST:/BartlebySync/cleanUp/tree/{treeId}'=>array('BartlebySyncFinalizeTransactionIn','cleanUp'), // May be suspended ( it is used on finalizeTransactionIn)
            'POST:/BartlebySync/removeGhosts'=>array('BartlebySyncRemoveGhosts','call')
        );
        $routes->addAliasesToMapping($mapping);
        return $routes;
    }
```



## "The ultimate" Commandline tutorial ##

Copy and paste the command line in your shell environment.

### 1 Prerequesite:"Install HTTPie" ###
You can install [HTTPie] (https://github.com/jkbrzt/httpie)

### 2 Test if BartlebySync is supported ###

Define the BASE_URL temp variable : 

```shell
    BASE_URL=http://yd.local/api/v1/
````

Test if BartlebySync is supported : 

```shell
     http GET ${BASE_URL}BartlebySync/isSupported    
```
Successful Response HTTP status code 200: 

```shell
    HTTP/1.1 200 OK
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 17
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 08:57:47 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    {
        "version": "1.5"
    }
```

### 3 Call BartlebySync Reachability endpoint

```shell
    http GET ${BASE_URL}BartlebySync/reachable
```

Successful Response HTTP status code 200: 

```shell
    HTTP/1.1 200 OK
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 4
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 10:06:38 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    "{}"
```

### 4 create local assets 

Create a Sample folder 

```shell
    mkdir ~/Desktop/Samples/
```

Create a Sample files  

```shell
    touch ~/Desktop/Samples/text1.txt
    echo "Eureka1" > ~/Desktop/Samples/text1.txt
    touch ~/Desktop/Samples/text2.txt
    echo "Eureka2" > ~/Desktop/Samples/text2.txt
    touch ~/Desktop/Samples/hashmap.data
    echo  "[]" > ~/Desktop/Samples/hashmap.data
````

### 5 install the repository 

```shell
    http -v -f POST ${BASE_URL}BartlebySync/install/
````

Successful Response HTTP status code 201: 

```shell
    HTTP/1.1 201 Created
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 4
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 10:08:09 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    "{}"
```

### 5 creates trees 

```shell
    http -v -f POST  ${BASE_URL}BartlebySync/create/tree/1 
    http -v -f POST  ${BASE_URL}BartlebySync/create/tree/2
    http -v -f POST  ${BASE_URL}BartlebySync/create/tree/3
```

Successful Response HTTP status code 201: 
 
```shell
    HTTP/1.1 201 Created
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 4
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 10:15:27 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    "{}"
```

### 5A delete the tree 3 

```shell
    http -v -f DELETE ${BASE_URL}BartlebySync/delete/tree/3
```

Successful Response HTTP status code 200

```shell
     HTTP/1.1 200 OK
     Access-Control-Allow-Methods: *
     Access-Control-Allow-Origin: *
     Connection: Keep-Alive
     Content-Length: 4
     Content-Type: application/json
     Date: Fri, 20 May 2016 07:32:36 GMT
     Keep-Alive: timeout=5, max=100
     Server: Apache
     X-Powered-By: PHP/5.6.10
     
     "{}"
```



    

### 5B touch the tree "1" to reset its public id, then try an unexisting ID

```shell
    http -v -f POST ${BASE_URL}BartlebySync/touch/tree/1
```
    
Successful Response HTTP status code 201

```shell 
    HTTP/1.1 201 Created
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 4
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 11:11:34 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    "{}"
```
  
Try an unexisting ID

```shell
    http -v -f POST ${BASE_URL}BartlebySync/touch/tree/unexisting-tree
```

Should Respond HTTP status code 404

```shell
    HTTP/1.1 404 Not Found
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 4
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 11:13:32 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    "{}"
```

### 6 try to Grab the hashmap that should not exists

```shell
    http -v GET  ${BASE_URL}BartlebySync/hashMap/tree/1/ redirect==true returnValue==false
```

Should Respond HTTP status code 404

```shell
    HTTP/1.1 404 Not Found
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 4
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 11:15:37 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    "{}"
```

### 7 Upload the files
    
```shell
    SYNC_ID="my_sync_id_"
    http -v -f POST  ${BASE_URL}BartlebySync/uploadFileTo/tree/1/ destination='file1.txt' syncIdentifier=${SYNC_ID} source@~/Desktop/Samples/text1.txt
    http -v -f POST  ${BASE_URL}BartlebySync/uploadFileTo/tree/1/ destination='file2.txt' syncIdentifier=${SYNC_ID} source@~/Desktop/Samples/text2.txt
```
    
HTTPie details on successful upload should Respond HTTP status code 201 ("created")

```shell
    POST /api/v1/BartlebySync/uploadFileTo/tree/1/ HTTP/1.1
    Accept: */*
    Accept-Encoding: gzip, deflate
    Connection: keep-alive
    Content-Length: 364
    Content-Type: multipart/form-data; boundary=5d042e80f3f4472882d2a071d2806ce2
    Host: yd.local
    User-Agent: HTTPie/0.9.2
    
    --5d042e80f3f4472882d2a071d2806ce2
    Content-Disposition: form-data; name="destination"
    
    a/file1.txt
    --5d042e80f3f4472882d2a071d2806ce2
    Content-Disposition: form-data; name="syncIdentifier"
    
    my_sync_id_
    --5d042e80f3f4472882d2a071d2806ce2
    Content-Disposition: form-data; name="source"; filename="text1.txt"
    
    Eureka1
    
    --5d042e80f3f4472882d2a071d2806ce2--
    
    HTTP/1.1 201 Created
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 4
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 11:18:22 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    "{}"
```

### 8 Finalize the upload session

To remain simple we donnot inject the real hash map data but a placeholder.

```shell

    http -v -f POST ${BASE_URL}BartlebySync/finalizeTransactionIn/tree/1/ commands='[[0 ,"file1.txt"],[0 ,"file2.txt"]]' syncIdentifier=${SYNC_ID} hashMap@~/Desktop/Samples/hashmap.data 

```

NB: You could manually inject a checksum via cksum

```shell
    cksum ~/Desktop/Samples/text1.txt
    1812593931 8 /Users/bpds/Desktop/Samples/text1.txt
    cksum ~/Desktop/Samples/text2.txt
    1851787394 8 /Users/bpds/Desktop/Samples/text2.txt
   
    Update the hashmap.data file 
    {
        "pthToH" :  {
            "text1.txt" : 1812593931,
            "text2.txt" : 1851787394,
        }
    }
```
    
OBJC and swift client are implementing the delta logic, and all the synchronization scenari.
    

### Down Stream samples 


Download a hashmap 

```shell
   http -v GET  ${BASE_URL}BartlebySync/hashMap/tree/1/ redirect==false returnValue==true
```



Download a file 
```shell
    http -v GET ${BASE_URL}BartlebySync/file/tree/1/ path=='file1.txt' redirect==false returnValue==true
```    
    
The response with redirect==true returnValue==false

```shell
   
    HTTP/1.1 200 OK
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 13
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 13:59:42 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    [
        "Eureka1\n"
    ]
    
```    
    
The response with redirect==true 

```shell
    http -v GET ${BASE_URL}BartlebySync/file/tree/1/ path=='file1.txt' redirect==true
    
   
    GET /api/v1/BartlebySync/file/tree/1/?redirect=true&path=file1.txt HTTP/1.1
    Accept: */*
    Accept-Encoding: gzip, deflate
    Connection: keep-alive
    Host: yd.local
    User-Agent: HTTPie/0.9.2
    
            
    HTTP/1.1 307 Temporary Redirect
    Connection: Keep-Alive
    Content-Length: 0
    Content-Type: text/html; charset=UTF-8
    Date: Tue, 29 Dec 2015 14:01:19 GMT
    Keep-Alive: timeout=5, max=100
    Location: http://yd.repository.local:9999/70e441ffc22a069b927d8b3791256f52/file1.txt
    Server: Apache
    X-Powered-By: PHP/5.6.10
```

You can access directly to the file in a browser : http://yd.repository.local:9999/70e441ffc22a069b927d8b3791256f52/file1.txt

   
# Remove Ghosts 

Remove Ghosts ( in case of repository corruption e.g : manual deletion of assets or injection of files) 

```shell
    http -v -f POST ${BASE_URL}BartlebySync/removeGhosts 

    HTTP/1.1 201 Created
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 418
    Content-Type: application/json
    Date: Tue, 29 Dec 2015 14:17:14 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    {
        "deletedPath": [
            ".../files/.DS_Store", 
            ".../files/ok.txt"
        ], 
        "messages": [
            ".../files/.DS_Store is not a folder | ", 
            ".../files/ok.txt is not a folder | "
        ]
    }
```

# Native Clients #

- OSX Bsync commandline 
- OSX BsyncXPC services
- Bsync lib (iOS, tvOS, OSX)


# BartlebySync 1.0 #

- BartlebySync 1.0 supports hashMapView : A hashMapView file is a hasmap that is stored with the regular files that should be used  Master>Slave downstream only
- BartlebySync 1.0 supports  folder directives.json {"source":"http://...", "destination":"file://...","role":"<[slave,master,both]>","dataView":"<name of view none for global hashmap>","repositoryRelativePath":"/medias/"} 
- BartlebySync 1.0 supports Interruptibily per command (once a command has be executed on resume it is skipped) ?
- BartlebySync 1.0 and offers a Sync progress interface ?
