<?php

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\Core\JsonResponse;
use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;


final class ReachableCallData extends MongoCallDataRawWrapper {

}

final class Reachable extends MongoEndPoint {

    /**
     * Return 200 if the api is reachable
     * Permission is set to: 'Reachable->GET'=> array('level'=> PERMISSION_NO_RESTRICTION)
     * @param ReachableCallData $parameters
     * @return JsonResponse
     */
    function GET(){
        /* @var ReachableCallData */
        $parameters=$this->getModel();
        return new JsonResponse(VOID_RESPONSE, 200);
    }

    // Auth is required
    /***
     * Return 200 if the api is reachable and the credentials of the user valid for the current context.
     * Permission  is set to: 'Reachable->verify'=> array('level'=> PERMISSION_BY_IDENTIFICATION),
     * @param ReachableCallData $parameters
     * @return JsonResponse
     */
    function verify(){
        /* @var ReachableCallData */
        $parameters=$this->getModel();
        return new JsonResponse(VOID_RESPONSE, 200);
    }


}