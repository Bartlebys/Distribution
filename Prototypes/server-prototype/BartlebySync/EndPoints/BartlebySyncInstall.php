<?php

namespace Bartleby\EndPoints;

require_once __DIR__ . '/BartlebySyncAbstractEndPoint.php';

use Bartleby\Core\JsonResponse;

final class BartlebySyncInstallCallData extends BartlebySyncAbstractEndPointCallData {

    
}

final class BartlebySyncInstall extends BartlebySyncAbstractEndPoint {

    function call() {
        /* @var BartlebySyncInstallCallData */
        $parameters=$this->getModel();
        $this->ioManager = $this->getIoManager();
        $result=$this->ioManager->install(REPOSITORY_WRITING_PATH);
        $context=$this->ioManager->getContext();
        if ($result==true){
            //$context->issue
            //'Repository path is undefined'
            if ($context->containsIssueWithText('Repository exists')){
                return new JsonResponse(VOID_RESPONSE, 200);
            }else{
                return new JsonResponse(VOID_RESPONSE, 201);
            }
        }else{
            return new JsonResponse(['message'=> $context->issues,'repositoryPath'=>REPOSITORY_WRITING_PATH], 417);
        }

    }
}
