<?php

namespace Bartleby\EndPoints\Overloads;

require_once BARTLEBY_ROOT_FOLDER.'Commons/_generated/EndPoints/UpdateUser.php';

use Bartleby\Core\KeyPath;
use Bartleby\Core\CallDataRawWrapper;
use Bartleby\EndPoints\UpdateUserCallData;
use Bartleby\Core\JsonResponse;

class UpdateUser extends \Bartleby\EndPoints\UpdateUser {

    function call() {

        /* @var UpdateUserCallData */
        $parameters=$this->getModel();

        $spaceUID=$this->getSpaceUID(false);
        $user=$parameters->getValueForKey(UpdateUserCallData::user);
        $userID=KeyPath::valueForKeyPath($user,"_id");
        $foundSpaceUID=KeyPath::valueForKeyPath($user,SPACE_UID_KEY);

        if($foundSpaceUID!=$spaceUID){
            $this->_context->consignIssue('Attempt to move a user to another Dataspace has been blocked',__FILE__,__LINE__);
            return new JsonResponse([
                'foundSpaceUID'=>$foundSpaceUID,
                'spaceUID'=>$spaceUID,
                'context'=>$this->_context
            ],403);
        }
        
        ////////////////////////////////
        // VERIFY THE PREVIOUS SPACEUID
        /////////////////////////////////
        
        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->users;
        $q = array ('_id' =>$userID);
        if (isset($q)&& count($q)>0){
        }else{
            return new JsonResponse('Query is void',412);
        }
        try {
            $r = $collection->findOne($q);
            if (isset($r)) {
       
                $previousSpaceUID=KeyPath::valueForKeyPath($r,SPACE_UID_KEY);
                if($previousSpaceUID!=$spaceUID){
                    $this->_context->consignIssue('Dataspace inconsistency has been blocked',__FILE__,__LINE__);
                    return new JsonResponse([
                        'foundSpaceUID'=>$foundSpaceUID,
                        'spaceUID'=>$spaceUID,
                        'context'=>$this->_context
                    ],403);
                }
                ///////////////////////////
                // CALL THE PARENT LOGIC
                ///////////////////////////
                
                return parent::call($parameters);
            } else {
                return new JsonResponse(VOID_RESPONSE,404);
            }
        } catch ( \Exception $e ) {
            return new JsonResponse([
                'code'=>$e->getCode(),
                'message'=>$e->getMessage(),
                'file'=>$e->getFile(),
                'line'=>$e->getLine(),
                'trace'=>$e->getTraceAsString()
            ],
                417
            );
        }



    }

}