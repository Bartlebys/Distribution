<?php

namespace Bartleby\EndPoints;

require_once __DIR__ . '/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;

final class BartlebySyncRemoveGhostsCallData extends BartlebySyncAbstractEndPointCallData {

}

final class BartlebySyncRemoveGhosts extends BartlebySyncAbstractEndPoint {

    function call(BartlebySyncRemoveGhostsCallData $parameters) {
        $this->ioManager = $this->getIoManager();
        $details = $this->ioManager->removeGhosts();
        return new JsonResponse($details, 201);
    }
}
