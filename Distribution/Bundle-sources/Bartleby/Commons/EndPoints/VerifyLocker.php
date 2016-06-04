<?php

namespace Bartleby\EndPoints;
require_once BARTLEBY_ROOT_FOLDER. 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Core\JsonResponse;
use Bartleby\Models\User;
use \MongoCursorException;
use \MongoClient;
use Bartleby\Configuration;

final class VerifyLockerCallData extends MongoCallDataRawWrapper {

    /**
     * The locker UID
     */
    const lockerUID = 'lockerUID';

    /**
     * The sent code should be allways salted with the shared salt key client side.
     * You should never transmit or store clear codes.
     */
    const code = 'code';


}

final class VerifyLocker extends MongoEndPoint{

    function POST(VerifyLockerCallData $parameters) {

        $currentLockerUID = $parameters->getValueForKey(VerifyLockerCallData::lockerUID);
        $proposedCode = $parameters->getValueForKey(VerifyLockerCallData::code);

        $db=$this->getDB();
        /* @var \MongoCollection */
        $collection = $db->lockers;
        if (!isset($currentLockerUID)){
            return new JsonResponse('Query is void',412);
        }
        $q = array ('_id' =>$currentLockerUID);
        try {
            $locker = $collection->findOne($q);
            if (isset($locker)) {
                if (array_key_exists('userUID',$locker) &&
                    array_key_exists('code',$locker)&&
                    array_key_exists('startDate',$locker)&&
                    array_key_exists('endDate',$locker)){
                    $userUID=$locker['userUID'];
                    // We should be able to grab the current user
                    // The verification require a valid logged user
                    $currentUser=$parameters->getCurrentUser();
                    if (array_key_exists('_id',$currentUser)){
                        // User UID
                        if ($currentUser['_id']==$userUID){
                            // CODE
                            $code=$locker['code'];
                            if ($proposedCode!=$code){
                                return new JsonResponse('Code Missmatch',401);
                            }
                            // TIME
                            $startDate=new \DateTime($locker['startDate']);
                            $endDate=new \DateTime($locker['endDate']);
                            $now=new \DateTime('now');
                            if ($now->getTimestamp() > $startDate->getTimestamp() &&
                                $now->getTimestamp() < $endDate->getTimestamp()){
                                return new JsonResponse($locker,200);
                            }else{
                                return new JsonResponse('Locker is not actually usable (date issue)',401);
                            }
                        }else{
                            return new JsonResponse('User UID missmatch', 403);
                        }
                    }else {
                        return new JsonResponse('Current user is not valid', 412);
                    }
                }else{
                    return new JsonResponse('Found Locker is not valid',401);
                }
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