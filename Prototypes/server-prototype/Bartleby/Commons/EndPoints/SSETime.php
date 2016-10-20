<?php


namespace Bartleby\EndPoints;
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/ServerSentEvent.php';


use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Core\ServerSentEvent;

final class SSETimeCallData extends MongoCallDataRawWrapper {
}

final class SSETime extends MongoEndPoint {

    function GET() {
        /* @var SSETimeCallData */
        $parameters=$this->getModel();
        // Creation of the SSE
        $sse = new ServerSentEvent(3600); // 1 time per second
        $s=$this;
        // Definition of the closure
        $f=function() use ($s,$sse,$parameters) {
            //$s->getDB();
            $serverTime = time();
            $sse->sendMsg($serverTime,'tic', '{"serverTime":"' . date("h:i:s", time()).'"}');
        };
        $sse->callBack=$f;
        return $sse;
    }

}

