<?php

namespace Bartleby\EndPoints;

require_once __DIR__ .'/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;


final class BartlebySyncFinalizeTransactionInCallData extends BartlebySyncAbstractEndPointCallData{

    /**
     * The id of the tree to be created
     * @var string*/
    public $treeId=NULL;

    public $commands=NULL;

    public $syncIdentifier=NULL;

    // The final HashMap
    public $hashMap=NULL;

}

final class BartlebySyncFinalizeTransactionIn extends BartlebySyncAbstractEndPoint {

    /**
     * Finalizes the synchronization transaction with a bunch, then save the hashMap.
     *
     * @param BartlebySyncFinalizeTransactionInCallData $parameters
     * @return JsonResponse
     */
    function call(BartlebySyncFinalizeTransactionInCallData $parameters) {

        if (isset ($parameters->syncIdentifier) && isset ($parameters->commands) && isset($parameters->hashMap)) {
            $commands = $parameters->commands;
            // We accept encoded string
            if (!is_array($commands)) {
                try {
                    $commands = json_decode($parameters->commands);
                } catch (\Exception $e) {
                    return new JsonResponse('Invalid json command array = ' . $parameters->commands, 400);
                }
            }
            if (is_array($commands)) {
                if (!isset ($parameters->treeId)) {
                    return new JsonResponse('Undefined treeId', 404);
                }
                if (strlen($parameters->treeId) < MIN_TREE_ID_LENGTH) {
                    return new JsonResponse(VOID_RESPONSE, 406);
                }
                $errors = $this->getInterpreter()->interpretBunchOfCommand($parameters->treeId, $parameters->syncIdentifier, $commands, $parameters->hashMap);
                if ($errors == NULL) {
                    // We do not want to clean up on success
                    return new JsonResponse(VOID_RESPONSE, 201);

                } else {

                    // We cleanup if we encountered errors during finalization.
                    // We cannot guarantee a consistent state.
                    // This case should not occur !
                    if (CLEAN_UP_ON_ERROR){
                        $this->cleanUp ($parameters);
                    }

                    return new JsonResponse(array(
                            "message"=>"We have encountered a finalization error. It should be reported to the system adminstrator",
                            "errors" => $errors,
                            "commands" => $commands),
                        417);
                }
            } else {
                return new JsonResponse ('commands must be an array = ' . $parameters->commands, 400);
            }
        } else {
            return new JsonResponse('commands :' . $parameters->commands . ', hashMapSourcePath:' . $_FILES ['hashmap'] . ',  syncIdentifier:' . $parameters->syncIdentifier . ' are required', 400);
        }
    }

    /**
     * We cleanup for a given synchronization ID
     * @param BartlebySyncFinalizeTransactionInCallData $parameters
     * @return JsonResponse
     */
    function cleanUp(BartlebySyncFinalizeTransactionInCallData $parameters) {

        if (!isset ($parameters->treeId)) {
            return new JsonResponse('Undefined treeId', 404);
        }
        if (strlen($parameters->treeId) < MIN_TREE_ID_LENGTH) {
            return new JsonResponse(VOID_RESPONSE, 406);
        }


        if (!isset ($parameters->syncIdentifier)) {
            return new JsonResponse('Undefined syncIdentifier', 404);
        }

        if (strlen($parameters->syncIdentifier)< 20 ){
            return new JsonResponse('syncIdentifier should be 20 char min.', 417);
        }

        $this->ioManager = $this->getIoManager();
        $rootPath = $this->ioManager->absoluteUrl($parameters->treeId, '');
        $fileList = $this->ioManager->listRelativePathsIn($rootPath);
        $deletedPath = array();
        $unModifiedPath = array();

        foreach ($fileList as $relativePath) {
            if (substr($relativePath, -1) != "/") {
                // It is not a folder.
                $pathInfos = pathinfo($relativePath);
                $fileName = $pathInfos ['basename'];
                if ($this->_stringStartsWith($fileName,$parameters->syncIdentifier)) {
                    $absoluteUrl = $this->ioManager->absoluteUrl($parameters->treeId, $relativePath);
                    $this->ioManager->delete($absoluteUrl);
                    $deletedPath [] = $relativePath;
                } else {
                    $unModifiedPath[] = $relativePath;
                }
            };
        };
        return new JsonResponse(array("deleted" => $deletedPath, "notModified" => $unModifiedPath), 200);
    }

    private function _stringStartsWith($haystack, $needle) {
        return (strpos($haystack, $needle) !== FALSE);
    }

}