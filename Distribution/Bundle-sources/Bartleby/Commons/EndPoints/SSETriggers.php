<?php


namespace Bartleby\EndPoints;
require_once BARTLEBY_ROOT_FOLDER . 'Core/JsonResponse.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/ServerSentEvent.php';


use Bartleby\Core\JsonResponse;
use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Core\ServerSentEvent;
use MongoDB;
use MongoCollection;

final class SSETriggersCallData extends MongoCallDataRawWrapper {

    const spaceUID = 'spaceUID';

    const lastIndex = 'lastIndex';

}

final class SSETriggers extends MongoEndPoint {

    private $_counter = 0;

    /* @var \MongoDB */
    private $_db;

    /* @var MongoCollection */
    private  $_triggers;

    private $_lastIndex = -1;

    private $_spaceUID = NULL;

    function GET(SSETriggersCallData $parameters) {

        $s=$this;

        $this->_lastIndex = $parameters->getValueForKey(SSETriggersCallData::lastIndex);
        if (!isset($this->_lastIndex)){
            $this->_lastIndex=0;
        }


        $this->_spaceUID = $parameters->getValueForKey(SSETriggersCallData::spaceUID);
        /*
        if (!isset($this->_spaceUID)){
            return new JsonResponse('Data space is undefined',412);
        }*/

        $this->_db=$this->getDB();
        $this->_triggers=$this->_db->triggers;

        // Creation of the SSE
        $sse = new ServerSentEvent(60*60); // 1 time per second

        // Definition of the closure
        $f=function() use ($s,$sse,$parameters) {


            $q = array();
            //$q ['spaceUID']=$this->_spaceUID;
            $q ['index'] = array (
                '$gte' => $this->_lastIndex+1
            );

            // Filter by SpaceUID.
            if (isset($this->_spaceUID)){
                $q['spaceUID']=$this->_spaceUID;
            }

            $cursor=$this->_triggers->find($q);
            foreach ( $cursor as $trigger ) {
                $serverTime = time();
                $this->_counter++;
                $this->_lastIndex=$trigger["index"];
                $sender=$trigger["senderUID"];
                $action=$trigger["action"];
                $uids=$trigger["UIDS"];
                $dataSpace=$trigger["spaceUID"];
                $sse->sendMsg($serverTime, 'relay', '{"i":' .$this->_lastIndex .',"s":"' .$sender .'","a":"' .$action .'","u":"' .$uids .'","d":"' .$dataSpace .'"}');
            }
        };

        $sse->callBack=$f;
        return $sse;
    }

    function encodeTrigger($trigger){
        $jsonEncoded=json_encode($trigger);
        return str_replace('"','',$jsonEncoded);
    }

}

