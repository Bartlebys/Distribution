<?php

namespace Bartleby\EndPoints;
require_once BARTLEBY_ROOT_FOLDER .'Core/Configuration.php';
require_once BARTLEBY_ROOT_FOLDER .'Commons/_generated/Models/User.php';
require_once BARTLEBY_ROOT_FOLDER. 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Core\JsonResponse;
use Bartleby\Models\User;
use \MongoCursorException;
use \MongoClient;
use Bartleby\Configuration;


final class AuthCallData extends MongoCallDataRawWrapper {
    
    /**
     * The user instance
     */
    const userUID = 'userUID';
    
    /**
     * The spaceUID
     */
    const spaceUID = 'spaceUID';

    /**@
     * The sent password should be always salted with the shared salt key client side.
     * You should never transmit or store clear passwords.
     */
    const password = 'password';


}

final class Auth extends MongoEndPoint{

    private $issues=array();

    function POST(AuthCallData $parameters){

        $configuration=$this->getConfiguration();
        $currentUserUID = $parameters->getValueForKey(AuthCallData::userUID);
        $password = $parameters->getValueForKey(AuthCallData::password);
        
        if (!isset($password) || strlen($password)<3 ){
            return new JsonResponse("Password is not valid",400);
        }
        $spaceUID=$this->getSpaceUID();

        // MongoDB
        $db=$this->getDB();

        $usersCollection= $configuration->MONGO_USERS_COLLECTION();

        if (!isset($currentUserUID) || strlen($currentUserUID)<3  || !isset($spaceUID) || strlen($spaceUID)<3){
            return new JsonResponse(VOID_RESPONSE,400);
        }

        $spaceUIDKey= $configuration->MONGO_SPACE_UID_KEY_PATH();
        $users = $db->{$usersCollection};

        try {
            $q = array (
                "_id" => $currentUserUID,
                $spaceUIDKey => $spaceUID
            );

            $user = $users->findOne( $q );
            if (isset($user)){

                    $passwordKey=$configuration->MONGO_USER_PASSWORD_KEY_PATH();
                    $savedPassword=$user[$passwordKey];
                    $saltedPassword=$configuration->salt($password);
                    $passwordMatches=(isset($password) && strlen($password)>1 && $savedPassword === $saltedPassword );

                    if ($passwordMatches){

                        // Is the user "suspended" ?
                        if (array_key_exists('status',$user)){
                            if ($user['status']==User::Status_Suspended){
                                $this->issues[]='This user is suspended';
                                return new JsonResponse($this->issues,423);
                            }
                        }

                        // Verify the conformity of the Dataspaces.
                        if (array_key_exists('spaceUID',$user)){
                            if ($user['spaceUID']!=$spaceUID){
                                $this->issues[]='DataSpace conflict the space UID are not matching';
                                return new JsonResponse($this->issues,409);
                            }
                        }else{
                            $this->issues[]='DataSpace conflict the space UID of the user is not defined';
                            return new JsonResponse($this->issues,409);
                        }

                        // Everything is OK
                        $cookieUID=$this->_openSession($spaceUID,$user["_id"]);
                        return new JsonResponse($this->issues,200);
                    }

                return new JsonResponse(VOID_RESPONSE,401);
            } else {
                if ($this->_configuration->DEVELOPER_DEBUG_MODE()==true){
                    return new JsonResponse(array("credentials"=>$parameters),404);
                }else{
                    return new JsonResponse(VOID_RESPONSE,404);
                }

            }
        } catch ( MongoCursorException $e ) {
            return new JsonResponse('MongoCursorException' . $e->getCode() . ' ' . $e->getMessage(), 417);
        }


    }


    function DELETE(AuthCallData $parameters) {
        $spaceUID=$this->getSpaceUID();
        if($this->_closeSessionFor($spaceUID)) {
            return new JsonResponse(VOID_RESPONSE, 202);
        }else{
            return new JsonResponse(VOID_RESPONSE, 200);
        }
    }




    /////////////////////////
    // Session
    /////////////////////////

    /**
     * Open the session
     * @param $spaceUID
     * @param $userID
     * @return string
     */
    protected function _openSession($spaceUID,$userID){
        return $this->_setCookie($spaceUID,$userID);
    }

    private function _closeSessionFor($spaceUID){
        $configuration=$this->getConfiguration();
        $cookieKey=$configuration->getAuthCookieKEYForRUID($spaceUID);
        if(array_key_exists($cookieKey,$_COOKIE)) {
            // Cookie expiration
            setcookie($cookieKey,'',time()-60,'/', null, false, false);
            //unset($_COOKIE[$cookieKey]);
            return true;
        }else{
            return false;
        }
    }

    private function _setCookie($spaceUID,$userID,$nbOfHours=240){
        $time=time();
        $configuration=$this->getConfiguration();
        $cookieKey=$configuration->getAuthCookieKEYForRUID($spaceUID);
        $cookieValue=$configuration->getCryptedAuthCookieValue($spaceUID,$userID);
        $expires=$time+$nbOfHours*60*60;
        //setcookie ($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {}
        if (setcookie($cookieKey,$cookieValue,$expires, '/', null, false, false)===false){
            $this->issues[]='The setcookie call has failed!';
        }
        return $cookieValue;
    }

}