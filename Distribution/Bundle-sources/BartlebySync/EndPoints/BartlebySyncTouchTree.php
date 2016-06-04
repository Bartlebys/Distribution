<?php

namespace Bartleby\EndPoints;

require_once __DIR__ . '/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;

final class BartlebySyncTouchTreeCallData extends BartlebySyncAbstractEndPointCallData{

    /**
     * The id of the tree to be created
     * @var string
     */
    public $treeId = NULL;

}

final class BartlebySyncTouchTree extends BartlebySyncAbstractEndPoint{

    function call(BartlebySyncTouchTreeCallData $parameters){
        if (!isset($parameters->treeId) || strlen($parameters->treeId) < MIN_TREE_ID_LENGTH) {
            return new JsonResponse(VOID_RESPONSE, 406);
        }
        $this->ioManager = $this->getIoManager();
        $result = $this->ioManager->touchTree($parameters->treeId);
        if ($result == NULL) {
            return new JsonResponse(VOID_RESPONSE, 201);
        } else {
            return new JsonResponse(VOID_RESPONSE, 404);
        }

    }
}