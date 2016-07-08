<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 08/07/2016
 * Time: 09:31
 */

namespace Bartleby\Commons\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\Core\JsonResponse;
use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;


final class ExportCallData extends MongoCallDataRawWrapper {

    const  filterByRootObjectUID='filterByRootObjectUID';
    
}

class Export extends MongoEndPoint{

    /**
     * Returns the whole data space.
     */
    function GET(){
        $collectionsNames=$this->getConfiguration()->getCollectionNameList();
        $dataSpace=["collections"=>[]];
        $spaceUID=$this->getSpaceUID(false);

        /* @var ExportCallData */
        $parameters=$this->getModel();

        $db=$this->getDB();
        foreach ($collectionsNames as $collectionName) {
            /* @var \MongoCollection */
            $collection = $db->{$collectionName};
            $q = array ('spaceUID' =>$spaceUID);

            try {
                $cursor = $collection->find($q);
                if ($cursor->count ( TRUE ) > 0) {
                    $dataSpace["collections"][$collectionName] = [];
                    foreach ( $cursor as $obj ) {
                        $dataSpace["collections"][$collectionName] []= $obj;
                    }
                }
                if (isset($r)) {

                } else {
                    return new JsonResponse(VOID_RESPONSE,404);
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

        return new JsonResponse($dataSpace,200);
    }

}