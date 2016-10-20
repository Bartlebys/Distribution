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

    const observationUID = 'observationUID';

    const spaceUID = 'spaceUID';

    const lastIndex = 'lastIndex';

    const runUID = 'runUID';

    const showDetails ='showDetails';

    const sort = 'sort';

}

final class SSETriggers extends MongoEndPoint {

    private $_counter = 0;

    /* @var \MongoDB */
    private $_db;

    /* @var MongoCollection */
    private  $_triggers;

    private $_lastIndex = -1;

    private $_observationUID = NULL;

    private $_runUID = NULL;

    private $_showDetails = false;

    private $_sort = NULL;

    function GET() {
        /* @var SSETriggersCallData */
        $parameters=$this->getModel();
        $s=$this;

        $this->_lastIndex = $parameters->getValueForKey(SSETriggersCallData::lastIndex);
        if (!isset($this->_lastIndex)){
            $this->_lastIndex = -1;
        }
        $this->_observationUID = $this->getObservationUID(true);
        $this->_runUID = $this->getRunUID(true);
        $this->_sort = $parameters->getValueForKey(SSETriggersCallData::sort);

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

                $q = [];

                // Filter by SpaceUID
                $spaceUID=$this->getSpaceUID(true);
                if (isset($spaceUID) && $spaceUID!="") {
                    $q[SPACE_UID_KEY] = $spaceUID;
                }

                // Filter by Observation UID.
                if (isset($this->_observationUID) && $this->_observationUID!="" ) {
                    $q[OBSERVATION_UID_KEY] = $this->_observationUID;
                }

                // Filter by runUID (is essential to prevent data larsen).
                if (isset($this->_runUID) && $this->_runUID!="" ) {
                    $q [RUN_UID_KEY] = [
                        // Not equal
                        '$ne' => $this->_runUID
                    ];
                }

                $q ['index'] = [
                    '$gte' => $this->_lastIndex + 1
                ];

                $cursor = $this->_triggers->find($q);

                if (isset($this->_sort)){
                    $cursor = $cursor->sort([$this->_sort=>1]);
                }

                foreach ($cursor as $trigger) {
                    $serverTime = time();
                    $this->_counter++;
                    $this->_lastIndex = $trigger['index'];
                    $sender = $trigger['senderUID'];
                    $runUID = $trigger[RUN_UID_KEY];
                    $origin = $trigger['origin'];
                    $action = $trigger['action'];
                    $payloads = "";
                    if (array_key_exists('payloads',$trigger)){
                        $payloads = $trigger['payloads'];
                    }
                    $uids = $trigger['UIDS'];
                    $collectionName = $trigger['targetCollectionName'];
                    $observationUID = $trigger[OBSERVATION_UID_KEY];
                    if ($this->_showDetails == false) {
                        // Used by clients includes the payloads
                        $sse->sendMsg($serverTime, 'relay', '{"i":' . $this->_lastIndex .
                            ',"o":"' . $observationUID .
                            '","r":"' . $runUID .
                            '","c":"' . $collectionName .
                            '","a":"' . $action .
                            '","u":"' . $uids .
                            '","p":'.json_encode($payloads).'}');
                    } else {
                        // Used to display the trigger
                        $sse->sendMsg($serverTime, 'relay', '{"i":' . $this->_lastIndex .
                            ',"o":"' . $observationUID .
                            '","r":"' . $runUID .
                            '","c":"' . $collectionName .
                            '","s":"' . $sender .
                            '","n":"' . $origin .
                            '","a":"' . $action .
                            '","u":"' . $uids .
                            '","p":'.json_encode($payloads)
                            .'}');
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

