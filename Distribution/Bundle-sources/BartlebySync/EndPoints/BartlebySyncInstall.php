<?php

namespace Bartleby\EndPoints;

require_once __DIR__ . '/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;

final class BartlebySyncInstallCallData extends BartlebySyncAbstractEndPointCallData {

    /**
     * The repository path
     * @var string
     */
    public $repositoryPath = NULL;
    
}

final class BartlebySyncInstall extends BartlebySyncAbstractEndPoint {

    function call(BartlebySyncInstallCallData $parameters) {
        $this->ioManager = $this->getIoManager();
        $this->ioManager->install($parameters->repositoryPath);
        return new JsonResponse(VOID_RESPONSE, 201);
    }
}
