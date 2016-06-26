<?php

namespace Bartleby
\EndPoints;

require_once __DIR__ . '/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;
use Bartleby\EndPoints\BartlebySyncAbstractEndPoint;
use Bartleby\EndPoints\BartlebySyncAbstractEndPointCallData;

final class BartlebySyncGetFileCallData extends BartlebySyncAbstractEndPointCallData {

    /**
     * The id of the tree to be created
     * @var string
     */
    public $treeId = NULL;

    /**
     * @var bool redirect to a repository URI
     */
    public $redirect=true;

    /**
     * Returns the value if there is a redirection the redirection applies.
     * @var bool return the value
     */
    public $returnValue=false;

    /**
     * The relative path
     * @var string
     */
    public $path = NULL;


}

final class BartlebySyncGetFile extends BartlebySyncAbstractEndPoint {

    function call(BartlebySyncGetFileCallData $parameters) {

        $redirect=$this->_castToBoolean($parameters->redirect);
        $returnValue=$this->_castToBoolean($parameters->returnValue);

        if (!isset($parameters->treeId)) {
            return new JsonResponse(VOID_RESPONSE, 406);
        }

        if (strlen($parameters->treeId) < MIN_TREE_ID_LENGTH) {
            return new JsonResponse(VOID_RESPONSE, 406);
        }

        if (!isset($parameters->path)) {
            return new JsonResponse(VOID_RESPONSE, 404);
        }

        $this->ioManager = $this->getIoManager();
        $path = $this->ioManager->absoluteUrl($parameters->treeId, $parameters->path);
        if (!$this->ioManager->exists($path)) {
            return new JsonResponse(VOID_RESPONSE, 404);
        }
        if ($returnValue && !$redirect) {
            //This approach can be very expensive.
            $result = $this->ioManager->get_contents($path);
            return new JsonResponse($result, $this->ioManager->getStatus());
        }
        
        // Using an URI is more flexible.
        // It can facilitate load balancing by distributing to multiple repository.
        $uri = $this->ioManager->uriFor($parameters->treeId, $parameters->path);

        if ($redirect) {
            // This is the best approach
            // Redirect with a 307 code
            header('Location:  ' . $uri . '?antiCache=' . uniqid(), true, 307);
            exit ();
        } else {

            // But if it fails we can use
            // A two step approach.

            $infos = array();
            $infos ["uri"] = $uri;
            return new JsonResponse($infos, 200);
        }

    }
}

