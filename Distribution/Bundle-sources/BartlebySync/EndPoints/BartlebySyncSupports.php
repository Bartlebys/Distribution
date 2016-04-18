<?php

namespace Bartleby\EndPoints;

require_once __DIR__ .'/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;

final class BartlebySyncSupportsCallData extends BartlebySyncAbstractEndPointCallData{
}

final class BartlebySyncSupports extends BartlebySyncAbstractEndPoint {

    function call(BartlebySyncSupportsCallData $parameters){

        return new JsonResponse(array('version'=>BARTLEBY_SYNC_VERSION), 200);
    }

}