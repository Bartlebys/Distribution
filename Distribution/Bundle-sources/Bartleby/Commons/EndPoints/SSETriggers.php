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

    const  runUID = 'runUID';

    const showDetails ='showDetails';

}

final class SSETriggers extends MongoEndPoint {

    private $_counter = 0;

    /* @var \MongoDB */
    private $_db;

    /* @var MongoCollection */
    private  $_triggers;

    private $_lastIndex = -1;

    private $_spaceUID = NULL;

    private $_runUID = NULL;

    private $_showDetails = false;

    function GET(SSETriggersCallData $parameters) {

        $s=$this;

        $this->_lastIndex = $parameters->getValueForKey(SSETriggersCallData::lastIndex);
        if (!isset($this->_lastIndex)){
            $this->_lastIndex = -1;
        }
        $this->_spaceUID = $parameters->getValueForKey(SSETriggersCallData::spaceUID);
        $this->_runUID = $parameters->getValueForKey(SSETriggersCallData::runUID);

        if ($parameters->keyExists(SSETriggersCallData::showDetails)){
           $showDetailsValue = $parameters->getValueForKey(SSETriggersCallData::showDetails);
            $this->_showDetails = (strtolower($showDetailsValue)=='true');
        }

        $this->_db=$this->getDB();
        $this->_triggers=$this->_db->triggers;

        // Creation of the SSE
        $sse = new ServerSentEvent(60*60); // 1 time per second

        // Definition of the closure
        $f=function() use ($s,$sse,$parameters) {

            try {

                $q = array();
                $q ['index'] = array(
                    '$gte' => $this->_lastIndex + 1
                );
                // Filter by SpaceUID.
                if (isset($this->_spaceUID)) {
                    $q['spaceUID'] = $this->_spaceUID;
                }

                // Filter by runUID (is essential to prevent data larsen).
                if (isset($this->_runUID)) {
                    $q ['runUID'] = [
                        // Not equal
                        '$ne' => $this->_runUID
                    ];
                }

                $cursor = $this->_triggers->find($q);
                foreach ($cursor as $trigger) {
                    $serverTime = time();
                    $this->_counter++;
                    $this->_lastIndex = $trigger['index'];
                    $sender = $trigger['senderUID'];
                    $runUID = $trigger['runUID'];
                    $origin = $trigger['origin'];
                    $action = $trigger['action'];
                    $uids = $trigger['UIDS'];
                    $collectionName = $trigger['collectionName'];
                    $dataSpace = $trigger['spaceUID'];
                    if ($this->_showDetails == false) {
                        // Used by clients
                        $sse->sendMsg($serverTime, 'relay', '{"i":' . $this->_lastIndex . ',"d":"' . $dataSpace . '","r":"' . $runUID . '","c":"' . $collectionName . '","a":"' . $action . '","u":"' . $uids . '"}');
                    } else {
                        // Used to display the trigger
                        $sse->sendMsg($serverTime, 'relay', '{"i":' . $this->_lastIndex . ',"d":"' . $dataSpace . '","r":"' . $runUID . '","c":"' . $collectionName . '","s":"' . $sender . '","o":"' . $origin . '","a":"' . $action . '","u":"' . $uids . '"}');
                    }
                }

            } catch (\Exception $e) {
                $serverTime = time();
                $result=["e"=>$e->getMessage()];
                $sse->sendMsg($serverTime, 'exception', json_encode($result));
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

