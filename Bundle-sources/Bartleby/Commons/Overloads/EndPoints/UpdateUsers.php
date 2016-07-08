<?php

namespace Bartleby\EndPoints\Overloads; // (!) Important  

require_once BARTLEBY_ROOT_FOLDER.'Commons/_generated/EndPoints/UpdateUsers.php';

use Bartleby\Core\KeyPath;
use Bartleby\Core\CallDataRawWrapper;
use Bartleby\EndPoints\UpdateUsersCallData;

class UpdateUsers extends \Bartleby\EndPoints\UpdateUsers {

    function call() {
        /* @var UpdateUserCallData */
        $parameters=$this->getModel();
        $spaceUID=$this->getSpaceUID(false);
        $users=$arrayOfObject=$parameters->getValueForKey(UpdateUsersCallData::users);
        foreach ($users as $user) {
            $foundSpaceUID=KeyPath::valueForKeyPath($user,SPACE_UID_KEY);
            if($foundSpaceUID!=$spaceUID){
                $this->_context->consignIssue('Dataspace inconsistency has been blocked',__FILE__,__LINE__);
                return new JsonResponse([
                    'foundSpaceUID'=>$foundSpaceUID,
                    'spaceUID'=>$spaceUID,
                    'context'=>$this->_context
                ],403);
            }
        }

        ////////////////////////////////
        // VERIFY THE PREVIOUS SPACEUID ?
        /////////////////////////////////

        // We currently donnot verify previous UID has the Multi update requires super admins privileges.


        return parent::call($parameters);
    }

}