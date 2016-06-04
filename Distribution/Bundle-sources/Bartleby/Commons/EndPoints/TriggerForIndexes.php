<?php

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_PUBLIC_FOLDER . 'Configuration.php';

use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Mongo\MongoCallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use \MongoCollection;
use Bartleby\Configuration;

class  TriggerForIndexesCallData extends MongoCallDataRawWrapper {
    const ids='ids';
    const result_fields='result_fields';
    /* the sort (MONGO DB) */
    const sort='sort';
}

class  TriggerAfterIndex extends MongoEndPoint {

    function call(TriggerForIndexesCallData $parameters) {
        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->triggers;
        $ids=$parameters->getValueForKey(TriggerForIndexesCallData::ids);
        $f=$parameters->getValueForKey(TriggerForIndexesCallData::result_fields);
        if(isset ($ids) && is_array($ids) && count($ids)){
            $q = array( '_id'=>array( '$in' => $ids ));
        }else{
            return new JsonResponse(VOID_RESPONSE,204);
        }
        try {
            $r=array();
            if(isset($f)){
                $cursor = $collection->find( $q , $f );
            }else{
                $cursor = $collection->find($q);
            }
            // Sort ?
            $s=$parameters->getCastedDictionaryForKey(TriggerForIndexesCallData::sort);
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
            }
        } catch ( \Exception $e ) {
            return new JsonResponse( array ('code'=>$e->getCode(),
                'message'=>$e->getMessage(),
                'file'=>$e->getFile(),
                'line'=>$e->getLine(),
                'trace'=>$e->getTraceAsString()
            ),
                417
            );
        }
    }
}

?>