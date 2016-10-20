# Command line Tutorial 

## Install HTTPIE if necessary 

You can install [HTTPie] (https://github.com/jkbrzt/httpie)

## 1- Setup the base URL

    BASE_URL=http://localhost/api/v1/

## 2- Reachability and Configuration

### Is the app reachable ?

    http GET ${BASE_URL}/reachable


Successful response 

    HTTP/1.1 200 OK
    Access-Control-Allow-Methods: *
    Access-Control-Allow-Origin: *
    Connection: Keep-Alive
    Content-Length: 4
    Content-Type: application/json
    Date: Wed, 06 Jan 2016 08:26:19 GMT
    Keep-Alive: timeout=5, max=100
    Server: Apache
    X-Powered-By: PHP/5.6.10
    
    "{}"

### How is the app configured ?

    http -v GET ${BASE_URL}infos 

## 3- Test of commons services

### Try to create a user (ACL may block the creation)

    echo '{"user": {"email": "bpds@me.com", "password":"xxx", "dID":"0000"}}' | http -v -f POST ${BASE_URL}user

If you are blocked you can temporarly add a permissive rule into your configuration 

    'CreateUser->call'=>array('level' =>PERMISSION_NO_RESTRICTION)
    
### 4 - Test the existence of en entity

```shell
http GET ${BASE_URL}exists/<replace by the UID>/in/<replace by the spaceUID>
```