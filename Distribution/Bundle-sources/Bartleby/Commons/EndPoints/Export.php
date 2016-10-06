<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 08/07/2016
 * Time: 09:31
 */

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\Core\JsonResponse;
use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;


final class ExportCallData extends MongoCallDataRawWrapper {

    const filterByObservationUID='filterByObservationUID';

    const excludeTriggers='excludeTriggers';
    
}

final class Export extends MongoEndPoint{

    /**
     * Returns the whole data space.
     */
    function GET(){
        $collectionsNames=$this->getConfiguration()->getCollectionsNameList();
        $dataSet=["collections"=>[]];
        $spaceUID=$this->getSpaceUID(false);

        /* @var ExportCallData */
        $parameters=$this->getModel();
        $db=$this->getDB();

        $q = [SPACE_UID_KEY =>$spaceUID];
        $observationUID=$parameters->getValueForKey(ExportCallData::filterByObservationUID);
        $excludeTriggers=$parameters->getValueForKey(ExportCallData::excludeTriggers);
        $excludeTriggers=($excludeTriggers=="true");
        if (isset($rootObjectUID)){
            $q[OBSERVATION_UID_KEY]=$observationUID;
        }

        foreach ($collectionsNames as $collectionName) {
            if ($collectionName=="triggers" && $excludeTriggers){
                continue;
            }
            try {
                /* @var \MongoCollection */
                $collection = $db->{$collectionName};
                $cursor = $collection->find($q);
                if ($cursor->count ( TRUE ) > 0) {
                    $dataSet["collections"][$collectionName] = [];
                    foreach ( $cursor as $obj ) {
                        $dataSet["collections"][$collectionName][]= $obj;
                    }
                }
            } catch ( \Exception $e ) {
                return new JsonResponse( [  'code'=>$e->getCode(),
                    'message'=>$e->getMessage(),
                    'file'=>$e->getFile(),
                    'line'=>$e->getLine(),
                    'trace'=>$e->getTraceAsString()
                ],
                    417
                );
            }
        }

        return new JsonResponse($dataSet,200);
    }

}