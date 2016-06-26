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
    const indexes='ids';
    const ignoreHoles='ignoreHoles';
}

class  TriggerAfterIndex extends MongoEndPoint {

    function call(TriggerForIndexesCallData $parameters) {
        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->triggers;
        $indexes=$parameters->getValueForKey(TriggerForIndexesCallData::indexes);
        $ignoreHoles=$parameters->getValueForKey(TriggerForIndexesCallData::ignoreHoles);

        // TODO support ignoreHoles

        if(isset ($indexes) && is_array($indexes) && count($indexes)){

            $q = array( 'indexes'=>array( '$in' => $indexes ));

            ////////////////////////////////////////////
            // SpaceUID confinement and runUID eviction
            ////////////////////////////////////////////

            try {
                // Restrict to this spaceUID
                $q['spaceUID'] = $this->getSpaceUID();
            } catch (\Exception $e) {
                return new JsonResponse("spaceUID is undefined", 412);
            }
            try {
                // Filter owned Triggers
                $q ['runUID'] = [
                    // Not equal
                    '$ne' => $this->getRunUID()
                ];
            } catch (\Exception $e) {
                return new JsonResponse("runUID is undefined", 412);
            }

        }else{
            return new JsonResponse(VOID_RESPONSE,204);
        }
        try {
            
            $r=array();
            $cursor = $collection->find($q);
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