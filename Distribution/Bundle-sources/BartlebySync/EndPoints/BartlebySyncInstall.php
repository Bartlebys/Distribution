<?php

namespace Bartleby\EndPoints;

require_once __DIR__ .'/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;

final class BartlebySyncInstallCallData extends BartlebySyncAbstractEndPointCallData{

}

final class BartlebySyncInstall extends BartlebySyncAbstractEndPoint {

    function call(BartlebySyncInstallCallData $parameters){

        if ($parameters->key == BARTLEBY_SYNC_CREATIVE_KEY) {
            $this->ioManager = $this->getIoManager();
            $this->ioManager->install();
            return new JsonResponse(VOID_RESPONSE, 201);
        } else {
            return new JsonResponse(VOID_RESPONSE, 401);
        }

    }
}
