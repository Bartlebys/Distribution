<?php

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_PUBLIC_FOLDER . 'Configuration.php';

use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Mongo\MongoCallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use \MongoCollection;
use Bartleby\Configuration;

class  TriggersByIdsCallData extends MongoCallDataRawWrapper {
    const ids = 'ids';
}

class  TriggersByIds extends MongoEndPoint {

    function call() {
        /* @var TriggersByIdsCallData */
        $parameters=$this->getModel();
        $db = $this->getDB();
        /* @var \MongoCollection */
        $collection = $db->triggers;
        $ids = $parameters->getValueForKey(TriggersByIdsCallData::ids);
        if (isset ($ids) && is_array($ids) && count($ids)) {
            $q = array('_id' => array('$in' => $ids));

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
                // Restrict to this observationUID
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

        } else {
            return new JsonResponse(VOID_RESPONSE, 204);
        }

        ////////////////////////////////////////////
        // Query
        ////////////////////////////////////////////

        try {
            $r = array();
            $cursor = $collection->find($q);
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