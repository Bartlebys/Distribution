<?php

namespace Bartleby\EndPoints\Overloads; // (!) Important  

require_once BARTLEBY_ROOT_FOLDER.'Commons/_generated/EndPoints/UpdateUsers.php';

use Bartleby\Core\KeyPath;
use Bartleby\Core\CallDataRawWrapper;
use Bartleby\EndPoints\UpdateUsersCallData;

class UpdateUsers extends \Bartleby\EndPoints\UpdateUsers {

    function call(UpdateUsersCallData $parameters) {
        $spaceUID=$this->getSpaceUID();
        $users=$arrayOfObject=$parameters->getValueForKey(UpdateUsersCallData::users);
        foreach ($users as $user) {
            $foundSpaceUID=KeyPath::valueForKeyPath($user,SPACE_UID_KEY);
            if($foundSpaceUID!=$spaceUID){
                return new JsonResponse('Attempt to move a user to another Dataspace has been blocked by'.__FILE__,403);
            }
        }

        ////////////////////////////////
        // VERIFY THE PREVIOUS SPACEUID ?
        /////////////////////////////////

        // We currently donnot verify previous UID has the Multi update requires super admins privileges.


        return parent::call($parameters);
    }

}