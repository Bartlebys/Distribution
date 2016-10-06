<?php

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Mongo\MongoCallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use \MongoCollection;
use \MongoRegex;

class  EntityExistsByIdCallData extends MongoCallDataRawWrapper {
    const id = 'id';
}

class  EntityExistsById extends MongoEndPoint {

    /**
     * We use the triggers to determine if an entity with this entity may have been deleted.
     * - If we don't find any Creation trigger we return 404
     * - If  the last trigger is creative we return http status 200 if it is destructive we return 404
     * @return JsonResponse
     */
    function call() {
        /* @var EntityExistsByIdCallData */
        $parameters=$this->getModel();
        $db = $this->getDB();
        /* @var \MongoCollection */

        $id = $parameters->getValueForKey(EntityExistsByIdCallData::id);
        if (!isset($id)) {
            return new JsonResponse("Id is undefined", 412);
        }

        $collection = $db->triggers;

        // https://docs.mongodb.com/manual/reference/operator/query/
        $q ['UIDS'] = [
            '$regex' => new MongoRegex("/^$id/i")
        ];

        ////////////////////////////////////////////
        // space UID confinements
        // but not observationUID
        // in case the operation came from a non observed zone
        ////////////////////////////////////////////

        try {
            // Restrict to this spaceUID
            $q[SPACE_UID_KEY] = $this->getSpaceUID(false);
        } catch (\Exception $e) {
            return new JsonResponse("spaceUID is undefined", 412);
        }

        try {
            $r = array();
            $cursor = $collection->find($q);
            // Sort ?
            if ($cursor->count(TRUE) > 0) {
                foreach ($cursor as $obj) {
                    $r[] = $obj;
                }
                $lastTrigger=$r[count($r)-1];
                if (array_key_exists('action',$lastTrigger)
                    && array_key_exists('index',$lastTrigger) ){
                    $action=$lastTrigger['action'];
                    if (strpos('Delete',$action)===false){
                        return new JsonResponse(
                        ['deletionIndex'=>$lastTrigger['index'],'UID'=>$lastTrigger['_id']], 404);
                    }else{
                        return new JsonResponse(
                            VOID_RESPONSE, 200);
                    }
                }
            }else{
                return new JsonResponse(
                    $r, 404);
            }
            } catch (\Exception $e) {
            return new JsonResponse(['code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ],
                417
            );
        }

    }
}

?>