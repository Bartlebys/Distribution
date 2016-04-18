<?php


namespace Bartleby\EndPoints;

require_once __DIR__ . '/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;

final class BartlebySyncUploadFileToCallData extends BartlebySyncAbstractEndPointCallData {

    /**
     * The id of the tree to be created
     * @var string
     */
    public $treeId = NULL;

    public $destination = NULL;

    public $syncIdentifier = NULL;

}

final class BartlebySyncUploadFileTo extends BartlebySyncAbstractEndPoint {

    function call(BartlebySyncUploadFileToCallData $parameters) {

        if (!isset($parameters->treeId)) {
            return new JsonResponse(VOID_RESPONSE, 404);
        }

        if (strlen($parameters->treeId) < MIN_TREE_ID_LENGTH) {
            return new JsonResponse(VOID_RESPONSE, 406);
        }

        if (isset ($parameters->destination) && isset ($parameters->syncIdentifier)) {

            $this->ioManager = $this->getIoManager();
            $treeFolder = $this->ioManager->absolutePath($parameters->treeId, '');

            if (isset ($treeFolder) && $this->ioManager->exists($treeFolder)) {

                $destination = $parameters->destination;
                $syncIdentifier = $parameters->syncIdentifier;
                $isAFolder = (substr($destination, -1) == "/");
                $d = $this->ioManager->absolutePath($parameters->treeId, $destination);

                if ($isAFolder == true) {

                    // We create directly the folder without the sync identifier
                    if ($this->ioManager->mkdir($d)) {
                        return new JsonResponse(VOID_RESPONSE, 201);
                    } else {
                        return new JsonResponse("Mkdir failure" . $destination, 400);
                    }

                } else {

                    // there is a source it should be a file.
                    $d = dirname($destination) . DIRECTORY_SEPARATOR . $syncIdentifier . basename($destination);
                    $destinationPath = $this->ioManager->absolutePath($parameters->treeId, $d);

                    // We create the folder if necessary.
                    $this->ioManager->mkdir(dirname($destinationPath));

                    ////////////////
                    // Use $_FILES
                    ////////////////

                    if (isset ($_FILES ['source'])) {
                        // NSURLSession do not set $_FILES
                        // But if a client populates  $_FILES it can be a relevant approach.
                        if (!$this->ioManager->move_uploaded($_FILES ['source'] ['tmp_name'], $destinationPath)) {
                            return new JsonResponse('Error while moving the temp file' . $_FILES ['source'] ['tmp_name'], 404);
                        }
                        // We prefer to resume on failure
                        // Lack of else is a choice
                    }

                    ////////////////////////
                    // USE a stream input.
                    ////////////////////////


                    // We prefer not to load the file in memory.
                    //@todo may be should we integrate streams in iOManager?

                    // direct stream handling without that requires less memory than
                    // $flow= $this->ioManager->get_contents("php://input");
                    // $this->ioManager->put_contents($destinationPath,$flow);

                    $flow = fopen("php://input", "r");
                    /* Open a file for writing */
                    $fp = fopen($destinationPath, "w");
                    /* Read the data 1 KB at a time and write to the file */
                    while ($data = fread($flow, 1024)) {
                        fwrite($fp, $data);
                    }
                    fclose($fp);
                    fclose($flow);


                    if ($this->ioManager->exists($destinationPath)) {
                        return new JsonResponse(VOID_RESPONSE, 201);
                    } else {
                        return new JsonResponse('An error has occured the uploaded has not been created' . $destinationPath, 404);
                    }
                }

            } else {
                return new JsonResponse('Unexisting tree id ' . $treeFolder, 400);
            }
        } else {
            return new JsonResponse('The components destination and syncIdentifier are required', 400);
        }
    }

}



