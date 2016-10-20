<?php

namespace Bartleby\EndPoints;

require_once __DIR__ . '/BartlebySyncAbstractEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Configuration.php';

use Bartleby\Core\JsonResponse;
use Bartleby\Configuration;

final class BartlebySyncCreateTreeCallData extends BartlebySyncAbstractEndPointCallData {

    /**
     * The id of the tree to be created
     * @var string
     */
    public $treeId = NULL;

}

final class BartlebySyncCreateTree extends BartlebySyncAbstractEndPoint {

    function call() {

        /* @var BartlebySyncCreateTreeCallData */
        $parameters=$this->getModel();
        
        if (!isset($parameters->treeId) || strlen($parameters->treeId) < MIN_TREE_ID_LENGTH) {
            return new JsonResponse(VOID_RESPONSE, 406);
        }
        $this->ioManager = $this->getIoManager();
        $result = $this->ioManager->createTree($parameters->treeId);
        if ($result == NULL) {
            return new JsonResponse(VOID_RESPONSE, 201);
        } else {
            if ( $this->_configuration->DEVELOPER_DEBUG_MODE() === true ) {
                return new JsonResponse(array("parameters" => $parameters,
                    "IOManager.explanation" => $result), 400);
            } else {
                return new JsonResponse(VOID_RESPONSE, 400);
            }

        }
    }
}