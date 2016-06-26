<?php

namespace Bartleby\EndPoints;

require_once __DIR__ .'/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;
use Bartleby\EndPoints\BartlebySyncAbstractEndPoint;
use Bartleby\EndPoints\BartlebySyncAbstractEndPointCallData;

final class BartlebySyncGetHashMapCallData extends BartlebySyncAbstractEndPointCallData{

    /**
     * The id of the tree to be created
     * @var string*/
    public $treeId=NULL;

    /**
     * @var bool redirect to a repository URI
     */
    public $redirect=true;

    /**
     * Returns the value if there is a redirection the redirection applies.
     * @var bool return the value
     */
    public $returnValue=false;

}

final class BartlebySyncGetHashMap extends BartlebySyncAbstractEndPoint {

    function call(BartlebySyncGetHashMapCallData $parameters){

        $redirect=$this->_castToBoolean($parameters->redirect);
        $returnValue=$this->_castToBoolean($parameters->returnValue);

        if ( !isset($parameters->treeId) ) {
            return new JsonResponse(VOID_RESPONSE, 406);
        }

        if (strlen($parameters->treeId) < MIN_TREE_ID_LENGTH) {
            return new JsonResponse(VOID_RESPONSE, 406);
        }

        $this->ioManager = $this->getIoManager ();
        $path = $this->ioManager->absoluteUrl ( $parameters->treeId, METADATA_FOLDER . '/'. HASHMAP_FILENAME );
        if (! $this->ioManager->exists ( $path )) {
            return new JsonResponse(VOID_RESPONSE, 404 );
        }
        if ($returnValue && ! $redirect) {
            $result = $this->ioManager->get_contents ( $path );
            return new JsonResponse($result, $this->ioManager->getStatus());
        }

        $uri = $this->ioManager->uriFor ( $parameters->treeId, METADATA_FOLDER .'/'. HASHMAP_FILENAME );
        if ($redirect) {
            header('Location:  ' . $uri . '?antiCache=' . uniqid(), true, 307);
            exit ();
        } else {
            $infos = array ();
            $infos ["uri"] = $uri;
            return new JsonResponse( $infos, 200 );
        }

    }
}

