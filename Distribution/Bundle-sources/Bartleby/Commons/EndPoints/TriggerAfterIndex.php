<?php

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Mongo\MongoCallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use \MongoCollection;

class  TriggerAfterIndexsCallData extends MongoCallDataRawWrapper {
    const lastIndex = 'lastIndex';
}

class  TriggerAfterIndex extends MongoEndPoint {

    function call() {
        /* @var TriggerAfterIndexCallData */
        $parameters=$this->getModel();
        $db = $this->getDB();
        /* @var \MongoCollection */

        $lastIndex = $parameters->getValueForKey(SSETriggersCallData::lastIndex);
        if (!isset($lastIndex)) {
            return new JsonResponse("lastIndex is undefined", 412);
        }

        $collection = $db->triggers;

        $q ['index'] = [
            '$gte' => ($lastIndex + 1)
        ];

        ////////////////////////////////////////////
        // space and Observation UID confinements
        // and runUID eviction
        ////////////////////////////////////////////
        
        try {
            // Restrict to this spaceUID
            $q[SPACE_UID_KEY] = $this->getSpaceUID(false);
        } catch (\Exception $e) {
            return new JsonResponse("spaceUID is undefined", 412);
        }

        try {
            // Restrict to this spaceUID
            $q[OBSERVATION_UID_KEY] = $this->getObservationUID(false);
        } catch (\Exception $e) {
            return new JsonResponse("observationUID is undefined", 412);
        }
        try {
            // Filter owned Triggers
            $q [RUN_UID_KEY] = [
                // Not equal
                '$ne' => $this->getRunUID(false)
            ];
        } catch (\Exception $e) {
            return new JsonResponse("runUID is undefined", 412);
        }

        try {
            $r = array();
            $cursor = $collection->find($q);
            // Sort ?
            if ($cursor->count(TRUE) > 0) {
                foreach ($cursor as $obj) {
                    $r[] = $obj;
                }
            }

            if (count($r) > 0) {
                return new JsonResponse($r, 200);
            } else {
                return new JsonResponse(VOID_RESPONSE, 404);
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