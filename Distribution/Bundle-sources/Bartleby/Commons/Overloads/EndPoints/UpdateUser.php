<?php

namespace Bartleby\EndPoints\Overloads;

require_once BARTLEBY_ROOT_FOLDER.'Commons/_generated/EndPoints/UpdateUser.php';

use Bartleby\Core\KeyPath;
use Bartleby\Core\CallDataRawWrapper;
use Bartleby\EndPoints\UpdateUserCallData;
use Bartleby\Core\JsonResponse;

class UpdateUser extends \Bartleby\EndPoints\UpdateUser {

    function call(UpdateUserCallData $parameters) {
        $spaceUID=$this->getSpaceUID();
        $user=$parameters->getValueForKey(UpdateUserCallData::user);
        $userID=KeyPath::valueForKeyPath($user,"_id");
        $foundSpaceUID=KeyPath::valueForKeyPath($user,SPACE_UID_KEY);

        if($foundSpaceUID!=$spaceUID){
            return new JsonResponse('Attempt to move a user to another Dataspace has been blocked by'.__FILE__,403);
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
                    return new JsonResponse('Attempt to move a user to another Dataspace has been blocked by'.__FILE__,403);
                }
                ///////////////////////////
                // CALL THE PARENT LOGIC
                ///////////////////////////
                
                return parent::call($parameters);
            } else {
                return new JsonResponse(VOID_RESPONSE,404);
            }
        } catch ( \Exception $e ) {
            return new JsonResponse( array ('code'=>$e->getCode(),
                'message'=>$e->getMessage(),
                'file'=>$e->getFile(),
                'line'=>$e->getLine(),
                'trace'=>$e->getTraceAsString()
            ),
                417
            );
        }



    }

}