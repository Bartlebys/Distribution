<?php
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation*/

if (isset ( $f )) {
    $classNameWithoutPrefix=$h->ucFirstRemovePrefixFromString($d->class);
    $callDataClassName=$classNameWithoutPrefix.'CallData';
    $f->fileName = $classNameWithoutPrefix.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'_generated/EndPoints/';
}

// Exclusion

$exclusionName = str_replace($h->classPrefix, '', $d->class);
if (isset($excludeActionsWith)) {
    foreach ($excludeActionsWith as $exclusionString) {
        if (strpos($exclusionName, $exclusionString) !== false) {
            return NULL; // We return null
        }
    }
}


/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Core/KeyPath.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';
require_once BARTLEBY_PUBLIC_FOLDER . 'Configuration.php';

use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Mongo\MongoCallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use \MongoCollection;
use Bartleby\Configuration;
use Bartleby\Core\KeyPath;

class  <?php echo $callDataClassName; ?> extends MongoCallDataRawWrapper {
<?php
$name=null;
$parameterIsAcollection=false;
while ($d->iterateOnParameters() === true) {
    $parameter = $d->getParameter();
    $name=$parameter->name;
    $typeOfProp=$parameter->type;
    $o=FlexionsTypes::OBJECT;
    $c=FlexionsTypes::COLLECTION;
    $parameterIsAcollection=($typeOfProp===$c);
    if (($typeOfProp===$o)||($typeOfProp===$c)) {
        $typeOfProp = $h->ucFirstRemovePrefixFromString($parameter->instanceOf);
        if($typeOfProp==$c){
            $typeOfProp=' array of '.$typeOfProp;
        }
    }

    if($parameter->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        $typeOfProp=$parameter->instanceOf.' '.$typeOfProp;
        echoIndentCR('// Enumeration of possibles values of '.$name, 1);
        foreach ($parameter->enumerations as $element) {
            if($parameter->instanceOf==FlexionsTypes::STRING){
                echoIndentCR('const ' .$enumTypeName.'_'.ucfirst($element).' = "'.$element.'";' ,1);
            }else{
                echoIndentCR('const ' .$enumTypeName.'_'.ucfirst($element).' = '.$element.';', 1);
            }
        }
    }
    if(isset($parameter->description) && strlen($parameter->description)>1){
        echoIndentCR('/* '.$parameter->description.' */',1);
    }

    echoIndentCR('const '.$name.'=\''.$name.'\';',1);
}
?>
}

 class  <?php echo $classNameWithoutPrefix; ?> extends MongoEndPoint {
<?php


// We use the last and unique parameter for CRUD endpoints (ids based)
// If there is no parameters it means it is a generic Get endpoint based on request.

$lastParameterName=isset($name)?$name:'NO_PARAMETERS';




$parameterIsNotAcollection=(!$parameterIsAcollection);

$successP = $d->getSuccessResponse();
if ($successP->type == FlexionsTypes::COLLECTION) {
    $resultIsNotACollection = false;
}else{
    $resultIsNotACollection=true;
}

$isGenericGETEndpoint=(strpos($d->class,'ByQuery')!==false);
$isGETByIdsEndpoint=(strpos($d->class,'ByIds')!==false);
if($isGenericGETEndpoint==false && $isGETByIdsEndpoint==false){
    $isGETByIdEndpoint=true;
}else{
    $isGETByIdEndpoint=false;
}
$isACreateEndpoint=(strpos($d->class,'Create')===0);

if($d->httpMethod=='POST') {
    if ($d->usesUrdMode()==true){

    // URD MODE

        echo('
    function call() {
        /* @var '.$callDataClassName.' */
        $parameters=$this->getModel();
        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';@
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true,
            "upsert" => true
        );
        '.
            (
            ($parameterIsNotAcollection===true)?
                '$obj=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
         if(!isset($obj) || count($parameters->getDictionary())==0){
          return new JsonResponse(\'Invalid void object\',406);
        }
        // Inject the rootUID and the spaceUID in any entity
        $obj[REGISTRY_ROOT_OBJECT_UID_KEY]=$this->getObservationUID(false);
        $obj[SPACE_UID_KEY]=$this->getSpaceUID(false);
            
        
        $q = array (MONGO_ID_KEY =>$obj[MONGO_ID_KEY]);'
                :
                '$arrayOfObject=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(!isset($arrayOfObject) || (is_array($arrayOfObject) && count($arrayOfObject)<1) ){
            return new JsonResponse(\'Invalid void array\',406);
        }'
            )
            .'
        try {
            '.(($parameterIsNotAcollection===true)?
                '$r = $collection->update ($q, $obj,$options );
            if ($r[\'ok\']==1) {
                $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),NULL);
                return new JsonResponse($s,200);
            } else {
                return new JsonResponse($r,412);
            }'
                :
                'foreach ($arrayOfObject as $obj){
                  // Inject the rootUID and the spaceUID in any entity
                  $obj[REGISTRY_ROOT_OBJECT_UID_KEY]=$this->getObservationUID(false);
                  $obj[SPACE_UID_KEY]=$this->getSpaceUID(false);
        
                $q = array (MONGO_ID_KEY => $obj[MONGO_ID_KEY]);
                $r = $collection->update( $q, $obj,$options);
                if ($r[\'ok\']==1) {
                    if (array_key_exists(\'updatedExisting\', $r)) {
                        $existed = $r[\'updatedExisting\'];
                        if ($existed == false) {
                            return new JsonResponse($q,404);
                        }
                    }
                }else{
                    return new JsonResponse($q,412);
                }
             }
             $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),NULL);
            return new JsonResponse($s,200);'
            ).'

        } catch ( \Exception $e ) {
            return new JsonResponse( [  \'code\'=>$e->getCode(),
                                        \'message\'=>$e->getMessage(),
                                        \'file\'=>$e->getFile(),
                                        \'line\'=>$e->getLine(),
                                        \'trace\'=>$e->getTraceAsString()
                                      ],
                                      417
                                    );
        }
     }'
        );
    }else {

        // CRUD MODE

        echo('
    function call() {
        /* @var '.$callDataClassName.' */
        $parameters=$this->getModel();
        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->' . $d->collectionName . ';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );' . (

            ($parameterIsNotAcollection === true) ?

                '
        $obj=$parameters->getValueForKey(' . $callDataClassName . '::' . $lastParameterName . ');
        if(!isset($obj) || count($parameters->getDictionary())==0){
            return new JsonResponse(\'Void submission\',406);
        }'

                :

                '
        $arrayOfObject=$parameters->getValueForKey(' . $callDataClassName . '::' . $lastParameterName . ');
        if(!isset($arrayOfObject) || (is_array($arrayOfObject) && count($arrayOfObject)<1) ){
            return new JsonResponse(\'Invalid void array\',406);
        }'

            )

            . '
        try {' . (

            ($parameterIsNotAcollection === true) ?

                '
            // Inject the rootUID and the spaceUID in any entity
            $obj[REGISTRY_ROOT_OBJECT_UID_KEY]=$this->getObservationUID(false);
            $obj[SPACE_UID_KEY]=$this->getSpaceUID(false);
            
            $r = $collection->insert ( $obj,$options );'

                    :

                    '
             // Inject the rootUID and the spaceUID in any entity
            foreach ($arrayOfObject as &$element) {     
                if (is_array($element)){         
                    $element[REGISTRY_ROOT_OBJECT_UID_KEY]=$this->getObservationUID(false);
                    $element[SPACE_UID_KEY]=$this->getSpaceUID(false);
                }
            }
            
            $r = $collection->batchInsert( $arrayOfObject,$options );'

            )

            . '
             if ($r[\'ok\']==1) {
                $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),NULL);
                return new JsonResponse($s,201);
            } else {
                return new JsonResponse($r,412);
            }
        } catch ( \Exception $e ) {
            '.(($isACreateEndpoint===true) ? ' 
            // MONGO E11000 duplicate key error
            if ( $e->getCode() == 11000 && $this->getConfiguration()->IGNORE_MULTIPLE_CREATION_IN_CRUD_MODE() == true){
                // We return A 200 not a 201
                $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),\'This is not the first attempt.\');
                return new JsonResponse($s,200);
            }
            ':'').'
            return new JsonResponse( [  \'code\'=>$e->getCode(),
                                        \'message\'=>$e->getMessage(),
                                        \'file\'=>$e->getFile(),
                                        \'line\'=>$e->getLine(),
                                        \'trace\'=>$e->getTraceAsString()
                                     ],
                                      417
                                    );
        }
     }');
    }
}elseif ( $d->httpMethod=='GET' || $isGenericGETEndpoint===true ){


    echo('
     function call() {
        /* @var '.$callDataClassName.' */
        $parameters=$this->getModel();
        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';'.cr());

    if ($isGETByIdEndpoint===true){
        //echo('// $isGETByIdEndpoint');
        echo(
'         $q = array (MONGO_ID_KEY =>$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.'));
        if (isset($q)&& count($q)>0){
        }else{
            return new JsonResponse(\'Query is void\',412);
        }');
    }elseif ($isGETByIdsEndpoint===true){
        echo(
'        $ids=$parameters->getValueForKey('.$callDataClassName.'::ids);
        $f=$parameters->getValueForKey('.$callDataClassName.'::result_fields);
        if(isset ($ids) && is_array($ids) && count($ids)){
            $q = array( MONGO_ID_KEY=>array( \'$in\' => $ids ));
        }else{
            return new JsonResponse(VOID_RESPONSE,204);
        }'
    );
    } elseif ($isGenericGETEndpoint===true){
        echo(
'      $q = $parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
       if(!isset($q)){
           return new JsonResponse(VOID_RESPONSE,417);
       }
       $f=$parameters->getValueForKey('.$callDataClassName.'::result_fields);');
    }


    echo('
        try {'.
    (

        ($resultIsNotACollection===true)?

            // RESULT IS NOT A COLLECTION

            '
            $r = $collection->findOne($q);
            if (isset($r)) {
                return new JsonResponse($r,200);
            } else {
                return new JsonResponse(VOID_RESPONSE,404);
            }'

            :
            // RESULT IS A COLLECTION

            '
           $r=array();
           if(isset($f)){
                $cursor = $collection->find( $q , $f );
           }else{
                $cursor = $collection->find($q);
           }
           // Sort ?
           $s=$parameters->getCastedDictionaryForKey('.$callDataClassName.'::sort);
           if (isset($s) && count($s)>0){
              $cursor=$cursor->sort($s);
           }
           if ($cursor->count ( TRUE ) > 0) {
			foreach ( $cursor as $obj ) {
				$r[] = $obj;
			}
		   }

            if (count($r)>0 ) {
                return new JsonResponse($r,200);
            } else {
                return new JsonResponse(VOID_RESPONSE,404);
            }'
    ) .'
       } catch ( \Exception $e ) {
            return new JsonResponse( [  \'code\'=>$e->getCode(),
                                        \'message\'=>$e->getMessage(),
                                        \'file\'=>$e->getFile(),
                                        \'line\'=>$e->getLine(),
                                        \'trace\'=>$e->getTraceAsString()
                                      ],
                                      417
                                    );
        }
     }');



}elseif ($d->httpMethod=='PUT'){
    echo('
    function call() {
        /* @var '.$callDataClassName.' */
        $parameters=$this->getModel();
        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );
        '.
        (
            ($parameterIsNotAcollection===true) ?
        '$obj=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
         if(!isset($obj) || count($parameters->getDictionary())==0){
          return new JsonResponse(\'Invalid void object\',406);
        }
        $q = array (MONGO_ID_KEY =>$obj[MONGO_ID_KEY]);'

            :

            '$arrayOfObject=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(!isset($arrayOfObject) || (is_array($arrayOfObject) && count($arrayOfObject)<1) ){
            return new JsonResponse(\'Invalid void array\',406);
        }'

        )
        .'
        try {
            '.(($parameterIsNotAcollection===true)?
            '$r = $collection->update ($q, $obj,$options );
            if ($r[\'ok\']==1) {
              if(array_key_exists(\'updatedExisting\',$r)){
                    $existed=$r[\'updatedExisting\'];
                    if($existed==true){
                         $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),NULL);
                        return new JsonResponse($s,200);
                    }else{
                        return new JsonResponse(VOID_RESPONSE,404);
                    }
                }
                 $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),NULL);
                return new JsonResponse($s,200);
            } else {
                return new JsonResponse($r,412);
            }'
            :
            'foreach ($arrayOfObject as $obj){
                $q = array (MONGO_ID_KEY => $obj[MONGO_ID_KEY]);
                $r = $collection->update( $q, $obj,$options);
                if ($r[\'ok\']==1) {
                    if (array_key_exists(\'updatedExisting\', $r)) {
                        $existed = $r[\'updatedExisting\'];
                        if ($existed == false) {
                            return new JsonResponse($q,404);
                        }
                    }
                }else{
                    return new JsonResponse($q,412);
                }
             }
              $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),NULL);
            return new JsonResponse($s,200);'
        ).'

        } catch ( \Exception $e ) {
            return new JsonResponse( [  \'code\'=>$e->getCode(),
                                        \'message\'=>$e->getMessage(),
                                        \'file\'=>$e->getFile(),
                                        \'line\'=>$e->getLine(),
                                        \'trace\'=>$e->getTraceAsString()
                                      ],
                                      417
                                    );
        }
     }'
    );


}elseif ($d->httpMethod=='DELETE'){
    // DELETE
    echo('
    function call() {
        /* @var '.$callDataClassName.' */
        $parameters=$this->getModel();
        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );
        '.
    (
        ($parameterIsNotAcollection===true)?

            '$q = array (MONGO_ID_KEY =>$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.'));'

            :

            '$ids=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(isset ($ids) && count($ids)>0){
            $q = array( MONGO_ID_KEY =>array( \'$in\' => $ids ));
        }else{
            $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),NULL);
            return new JsonResponse(s,204);
        }'

    )
        .'
        try {
            $r = $collection->remove ( $q,$options );
             if ($r[\'ok\']==1) {
                 $hasBeenRemoved=($r[\'n\'] >= 1);
                 if( $hasBeenRemoved || $this->getConfiguration()->IGNORE_MULTIPLE_DELETION_ATTEMPT() === true ){
                     $s=$this->responseStringWithTriggerIndex($this->createTrigger($parameters),$hasBeenRemoved?NULL:\'Already deleted\');
                     return new JsonResponse($s,200);
                 }else{
                     return new JsonResponse(VOID_RESPONSE,404);
                 }
            } else {
                return new JsonResponse($r,412);
            }
        } catch ( \Exception $e ) {
            return new JsonResponse( [  \'code\'=>$e->getCode(),
                                        \'message\'=>$e->getMessage(),
                                        \'file\'=>$e->getFile(),
                                        \'line\'=>$e->getLine(),
                                        \'trace\'=>$e->getTraceAsString()
                                     ],
                                     417
                                    );
        }
     }'
    );
}else{
    echo('// STRANGE METHOD '.$d->httpMethod);
}

/////////////////////////////
//Create the trigger method
/////////////////////////////

if($d->httpMethod != 'GET' && $isGenericGETEndpoint===false){

    if ($d->httpMethod=='DELETE'){
        $action=$classNameWithoutPrefix;
    }else{
        $baseName=str_replace('Create','',$classNameWithoutPrefix);
        $baseName=str_replace('Update','',$baseName);
        $action='Read'.$baseName;
        if ($resultIsNotACollection){
            $action .= "ById";
        }else{
            $action .= "ByIds";
        }
    }

    // Normal senderUID block
    $senderUIDBlock='$senderUID=$this->getCurrentUserID($this->getSpaceUID(true));';

    //senderUID block for CreateUser
    if ($classNameWithoutPrefix=="CreateUser"){
        $senderUIDBlock='$senderUID=KeyPath::valueForKeyPath($parameters->getDictionary(),\'user.creatorUID\');';
    }

    // senderUID block for CreateUsers
    if ($classNameWithoutPrefix=="CreateUsers"){
        $senderUIDBlock='$senderUID=NO_UID;
        $users=$parameters->getValueForKey(CreateUserCallData::users);
        if (is_array($users) && count($users)>0){
            $firstUser=$users[0];
            $senderUID=KeyPath::valueForKeyPath($firstUser,"creatorUID");
        }else{
           throw new \Exception("No valid sender UID can be extracted from users");
        }';
    }

    echo('
    
    
    /**
     * Creates and relay the action using a trigger
     * 
     * @param '.$callDataClassName.' $parameters
     * @return  int  -1 if an error has occured and the trigger index on success
     * @throws \Exception
     */
    function createTrigger('.$callDataClassName.' $parameters){
        $ref=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        $homologousAction="'.$action.'";
        '.$senderUIDBlock.'
        return $this->relayTrigger($senderUID,"'.$d->collectionName.'","'.$classNameWithoutPrefix.'",$homologousAction,$ref);
    }');

}?>
 }
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>