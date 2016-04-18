<?php

namespace Bartleby\EndPoints;

require_once dirname(dirname(__DIR__)) . '/Mongo/MongoEndPoint.php';

use Bartleby\Core\CallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use Bartleby\Mongo\MongoEndPoint;


final class ReachableCallData extends CallDataRawWrapper{

}

final class Reachable extends MongoEndPoint {

    /**
     * Return 200 if the api is reachable
     * Permission is set to: 'Reachable->GET'=> array('level'=> PERMISSION_NO_RESTRICTION)
     * @param ReachableCallData $parameters
     * @return JsonResponse
     */
    function GET(ReachableCallData $parameters){
        return new JsonResponse(VOID_RESPONSE, 200);
    }

    // Auth is required
    /***
     * Return 200 if the api is reachable and the credentials of the user valid for the current context.
     * Permission  is set to: 'Reachable->verify'=> array('level'=> PERMISSION_IDENTIFIED_BY_COOKIE),
     * @param ReachableCallData $parameters
     * @return JsonResponse
     */
    function verify(ReachableCallData $parameters){
        return new JsonResponse(VOID_RESPONSE, 200);
    }


}