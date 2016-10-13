# Bartleby Version 1.0 #

Bartleby is a robust framework for Fault Tolerent Natives Distributed Desktop & Mobile Apps. 
It provides a integrated full stack  (clients and servers Api)
Api are written in PHP and uses MongoDB as document store.

It offers a unique distributed execution strategy and a solid permission and security model that enables to build complex collaborative tools efficiently. It has been developed to be used in and is fully integrated with a code generator called 'Flexions'.

Bartleby 1.0 has been developed by Benoit Pereira da Silva [Benoit Pereira da Silva](https://pereira-da-silva.com) for [Chaosmos SAS](http://chaosmos.fr).
Bartleby is licensed to its customers. 

## Code generation ## 


### server side API 

- project skeleton
- per entity endpoints (CRUD)/ collections (CRUD) 
- Authentication and permissions.
- pages

### client side libraries

iOS / Mac OS x
- models
- endpoints commands
- collection controllers

## The www folder 

## OTHERS 

- .htacess (when using apache)
- Configuration.php
- destructiveInstaller.php (to be deleted on deployment)
- index.php (routes http://host.ext/ to pages/go.php

### API 

- api/ 
- api/<version>/endpoints/ 
- api/<version>/_generated/
- api/<version>/_generated/endpoints/
- api/<version>/_generated/models/
- api/go.php
- _generated/
- _generated/<version>/GeneratedConfiguration.php

### PAGES 

- pages/
- pages/go.php
- pages/<version>/
- pages/<version>/pages/


# API run cycle

+ index.php or .htacess invokes -> go.php
+ it instanciate the Gateway that resolve the route
+ the route is translated to classes and parameters are deserialized
+ the context is passed to the GateKeeper that allows or rejects the call according to the permissions rules
+ If a filter IN applies the data is proceeded
+ if allowed the Relevent method of the class is called with is parametric context.
+ If a filter OUT applies the data is proceeded


# Bartleby's Permission model 

Bartleby uses permissions that are declared in the configuration file.
Bartleby's Permission model based on levels. 

## Permission levels

From Level 1 (PERMISSION_NO_RESTRICTION access with no restriction) to levele 131072 (PERMISSION_IS_BLOCKED)
The gatekeeper check if the path is allowed in the current context.
The permission are verified on any method call.

+ PERMISSION_IS_STATIC = 0 (implicit)
+ PERMISSION_NO_RESTRICTION = 1 
+ PERMISSION_BY_TOKEN = 2
+ PERMISSION_PRESENCE_OF_A_COOKIE = 3
+ PERMISSION_BY_IDENTIFICATION = 4
+ PERMISSION_RESTRICTED_TO_ENUMERATED_USERS = 5 
+ PERMISSION_RESTRICTED_BY_QUERIES = 6 
+ PERMISSION_RESTRICTED_TO_GROUP_MEMBERS = 9
+ PERMISSION_IS_DYNAMIC=32768 // We reserve the ability to add new permission types
+ PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY=65536
+ PERMISSION_IS_BLOCKED=131072 // For security purpose we can lock super admin


## The GateKeeper 

Extracts the permission "rule" 
rule A = if there is permission for the local key = 'PathClassName->MethodName'

If the permission is dynanic (rule A or B returns a level >= PERMISSION_IS_DYNAMIC) GateKeeper can deduct the dynamic permission key.
The rule C = the dynamic rule loaded from the permission collection.

If the rule is valid the call occurs, else Bartleby responds an http status code 403=>'Forbidden'  (not 401=>'Unauthorized')
If the current user is SuperAdmin && PERMISSION_IS_BLOCKED the call is blocked.

### Levels descriptions  

#### Level 1 (PERMISSION_NO_RESTRICTION)
Access with no restriction
     
     array ( 'PathClassName->MethodName' => array( 'level' => PERMISSION_NO_RESTRICTION)
     
#### Level 2 (PERMISSION_BY_TOKEN)
Verification of the token 
We check if the token is valid within the context
The $spaceUID can be injected in the name string if there is a #spaceUID in the declaration
To determine the validity we compute the value as  MD5(SHARED_SALT.name)

     array ('PathClassName->MethodName' => array( 'level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT,'PathClassName#spaceUID') 
     array ('CreateUser->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'CreateUser#spaceUID') 
    
#### Level 3 (PERMISSION_PRESENCE_OF_A_COOKIE)
On successful authentication a cookie is placed (the client should store it)
Any call without that cookie is reputed not identified

    array ( 'PathClassName->MethodName' => array( 'level' => PERMISSION_PRESENCE_OF_A_COOKIE,'name'=>'user-#spaceUID','value'=>'') 

#### Level 4 (PERMISSION_BY_IDENTIFICATION)
Verification of the cookie 
We check if the cookie correspond to a valid identified user.

    array ( 'PathClassName->MethodName' => array( 'level' => PERMISSION_BY_IDENTIFICATION) 

#### Level 5 (PERMISSION_RESTRICTED_TO_ENUMERATED_USERS)
Per path permission for enumerated users 

    array ( 'PathClassName->MethodName' => array( 'level' => PERMISSION_RESTRICTED_TO_ENUMERATED_USERS, 'ids' => ['<UserID>'])
    array ( 'PathClassName->MethodName(entityUID)' => array( 'level' => PERMISSION_RESTRICTED_TO_ENUMERATED_USERS_FOR_A_GIVEN_ID,'ids' => ['<UserID>'])

#### Level 6 (PERMISSION_RESTRICTED_BY_QUERIES)

We run queries if one of the query is successfull the action is permitted. 
You can use AND_CURRENT_USERID => true or AND_PARAMETER_KEY=>"<your propertyname>" for the predicate evaluation.

       'UpdateUser->call'=>array(
          'level' => PERMISSION_RESTRICTED_BY_QUERIES,
          ARRAY_OF_QUERIES =>array(
              "hasCreatedCurrentUser"=>array(
                  SELECT_COLLECTION_NAME=>'users',
                  WHERE_VALUE_OF_ENTITY_KEY=>'_id',
                  EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

                  COMPARE_WITH_OPERATOR=>'==',
                  RESULT_ENTITY_KEY=>'creatorUID',
                  AND_CURRENT_USERID=>true
              ),
              "isCurrentUser"=>array(
                  SELECT_COLLECTION_NAME=>'users',
                  WHERE_VALUE_OF_ENTITY_KEY=>'_id',
                  EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

                  COMPARE_WITH_OPERATOR=>'==',
                  RESULT_ENTITY_KEY=>'_id',
                  AND_CURRENT_USERID=>true
              )
          )
      ),

      'DeleteUser->call'=>array(
          'level' => PERMISSION_RESTRICTED_BY_QUERIES,
          ARRAY_OF_QUERIES =>array(
              "hasCreatedCurrentUser"=>array(
                  SELECT_COLLECTION_NAME=>'users',
                  WHERE_VALUE_OF_ENTITY_KEY=>'_id',
                  EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'userId',

                  COMPARE_WITH_OPERATOR=>'==',
                  RESULT_ENTITY_KEY=>'creatorUID',
                  AND_CURRENT_USERID=>true
          ),
              "isCurrentUser"=>array(
                  SELECT_COLLECTION_NAME=>'users',
                  WHERE_VALUE_OF_ENTITY_KEY=>'_id',
                  EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'userId',

                  COMPARE_WITH_OPERATOR=>'==',
                  RESULT_ENTITY_KEY=>'_id',
                  AND_CURRENT_USERID=>true
              )
          )
      )
                  
                  

#### Level 7 (PERMISSION_RESTRICTED_TO_GROUP_MEMBERS)

Before to return or proceed to any operation the gate keeper will check if the root targetted objects entityUID are set to be in one of the current user group.


      array ( 'PathClassName->MethodName(entityUID)' => array( 'level' => PERMISSION_RESTRICTED_TO_GROUP_MEMBERS)

#### Level 32768 PERMISSION_IS_DYNAMIC
Permission level is dynamic we load the permission level from a collection of permissions.
Any permission level can be set e

    array ( 'PathClassName->MethodName()' => array( 'level' => PERMISSION_IS_DYNAMIC)
    array ( 'PathClassName->MethodName(entityUID)' => array( 'level' => PERMISSION_IS_DYNAMIC)
    
#### Level 65536 PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY
Permission is granted to the declared super admins.


#### Level 131072 (PERMISSION_IS_BLOCKED)
For security purpose we can even block super admins.


# Data Modeling conventions : 

We use a Swagger derived model to modelize the entities.

## Composition 

By default we use AllOf as root of the entity modeling to allow composition.

## References 
External references.

## Aliases 

Aliases are used to maintain loosely coupled sets of entities. 
An alias stores all the necessary data to find a unique set of resource and optionally their labels. 
You can use an alias embedded or use it as an external reference. 


## Triggers 
@todo => equivalent to distributed notification.

## Metadata

We can inject metadata in the Entities. 


# Bartleby's synchronisation 

It is possible to synchronize local collections to a collaborative server.
And to subscribe for server sent event for example for messaging purposes.
The Data of a synchronised package can be regenerated.



# Generated Foundations   

## List of required generated objects

Any Bartleby app should use those reserved object.
You can add properties.

+ User
+ Group
+ Permission 
+ Reference
+ Tag

## Minimal Schema 

TODO create a separate Flexion target to include automatically those.

        "..."
            
         
## Filters 

Sample of a filter IN 
       
        // Is this sample Every user will be named ZORRO before DB insertion
        $data=NULL;// Dummy data for the IDE
        $filterCreateUser=new FilterHookByClosure();
        $filterCreateUser->closure=function($data) {
            KeyPath::setValueByReferenceForKeyPath($data,"user.username","Zorro!");
            return $data;
        };
        $this->addFilterIn('CreateUser->call',$filterCreateUser);