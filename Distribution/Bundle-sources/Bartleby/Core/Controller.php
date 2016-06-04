<?php

namespace Bartleby\Core;

require BARTLEBY_ROOT_FOLDER . 'Commons/_generated/Models/Trigger.php';

use Bartleby\Models\Trigger;
use \MongoCollection;



interface IPersistentController{

    public function getUser();

    public function authenticationIsValid ();

}

/**
 * Class Controller
 * @package Bartleby\Core
 */
class Controller {

    /**
     * @var Configuration
     */
    protected $_configuration;

    /**
     * @var string
     */
    protected $_userID;


    /**
     * Constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration) {
        $this->_configuration = $configuration;
    }

    /**
     *
     * @return bool
     */
    public function isAuthenticated($spaceUID){
        $this->_userID=$this->getCurrentUserID($spaceUID);
        return isset($this->_userID)?true:false;
    }


    /**
     * Returns the current user ID for a given related UID dID
     * @return null|string
     */

    /**
     * Return the current user id for the dID
     * @param $spaceUID
     * @return null|string
     */
    public function getCurrentUserID($spaceUID){
        if(isset($this->_userID)){
            return $this->_userID;
        }
       $this->_userID=$this->_configuration->getUserIDFromCookie($spaceUID);
        return $this->_userID;
    }


    /**
     * Grabs the SpaceUID or throws and Exception
     * @return mixed|string the dataSpace UID.
     * @throws \Exception
     */
    public function getSpaceUID(){
        $spaceUID=NULL;
        $headers=getallheaders();
        if(is_array($headers)){
            if(array_key_exists(SPACE_UID_KEY,$headers)==true){
                $spaceUID=$headers[SPACE_UID_KEY];
            }
        }
        if (isset($spaceUID)){
            return $spaceUID;
        }
        throw new \Exception("Undefined space UID");
    }




    /**
     * Locks the current execution preventing concurrent access to a critical code section in a given file.
     *      
     *      $sID=$this->lock(__FILE__)
     *      if ($sID === false){
     *          // Throw an exception.
     *      }
     *
     *      do some operaiton that require locking
     *      ...
     *
     *      $this->unlock($sID)
     *
     * @return resource a positive semaphore identifier on success, or <b>FALSE</b> on
     */
    public function lock($file){
        return $file;
        /*
        $key = ftok ( $file, 'R' );
        $max = 1;
        $permissions = 0666;
        $autoRelease = 1;

        $semaphoreIdentifier = sem_get ( $key ,$max,$permissions,$autoRelease);
        $success=sem_acquire ( $semaphoreIdentifier );
        return $success ? $semaphoreIdentifier : false;
        */
    }

    /*
     * Release the semaphore
     * @param $semaphoreIdentifier
     */
    public function unlock($semaphoreIdentifier){
        //sem_release ( $semaphoreIdentifier);
    }

    /**
     * Inserts a trigger into the triggers collection to be relayed via SSE.
     *
     * @param string $spaceUID
     * @param string $senderUID
     * @param string $homologousAction  e.g: `CreateUser would trigger homologous action `ReadUser`
     * @param mixed $reference can be a collection or a single instance.
     */
    public function relayTrigger($spaceUID,$senderUID,$homologousAction,$reference){

        // LOCK todo verify concurrency behaviour
        $sID=$this->lock(__FILE__);



        if (isset($spaceUID) && isset($homologousAction) && isset($reference)){
            $UIDS=$this->_extractUIDS($reference);

            if(!isset($senderUID) || $senderUID==""){
                if($homologousAction=="ReadUser" && count($UIDS)==1){
                    // It should be an Auto-creation.
                    $senderUID=$UIDS[0];
                }else{
                    $senderUID='?('.count($UIDS).')';
                }

            }

            if (count($UIDS)>0){

                // Insert the trigger.
                $db=$this->getDB();
                /* @var \MongoCollection */
                $collection = $db->triggers;

                //$UIDSString=join(',',$UIDS);
                $trigger=new Trigger();
                $trigger->spaceUID=$spaceUID;
                $trigger->senderUID=$senderUID;
                $trigger->index=$collection->count();
                $trigger->action=$homologousAction;
                $trigger->UIDS=join(',',$UIDS);

                // Default write policy
                $options = array (
                    "w" => 1,
                    "j" => true
                );

                //Todo - how to encode trigger correctly before insertion?
                // We should have 
                
                $array=array(
                                "spaceUID"=>$trigger->spaceUID,
                                "senderUID"=>$trigger->senderUID,
                                "index"=>$trigger->index,
                                "action"=>$trigger->action,
                                "UIDS"=>$trigger->UIDS
                );

                $r = $collection->insert( $array,$options );

                /////////////
                // UNLOCK !
                /////////////
                $this->unlock($sID);

                if ($r['ok']==1) {

                    return new JsonResponse(VOID_RESPONSE,201);
                } else {
                    return new JsonResponse($r,412);
                }


            }else{
                /////////////
                // UNLOCK !
                /////////////
                $this->unlock($sID);
                throw new \Exception("Void UIDS for trigger $spaceUID $senderUID $homologousAction $reference",0);
            }
        }else{
            /////////////
            // UNLOCK !
            /////////////
            $this->unlock($sID);
            throw new \Exception("Inconsitent trigger $spaceUID $senderUID $homologousAction $reference",0);
        }

    }

    /**
     * Extracts the UIDS from a given reference.
     * @param $reference
     * @param array $UIDS
     * @return array
     */
    private function _extractUIDS($reference,$UIDS=array()) {
        if (isset($reference)){
            if (is_array($reference)){
                if (array_key_exists("_id",$reference)){
                    $UIDS[]=$reference["_id"];
                }else{
                    foreach ($reference as $element) {
                        if (is_array($element)){
                            return $this->_extractUIDS($element);
                        }elseif (is_string($element)){
                            $UIDS[]=$element;
                        }else{
                            //
                        }
                    }
                }
            }elseif (is_string($reference)){
                $UIDS[]=$reference;
            }
        }
        return $UIDS;
    }

}