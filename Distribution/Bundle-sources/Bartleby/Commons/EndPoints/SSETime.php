<?php


namespace Bartleby\EndPoints;
require_once dirname(dirname(__DIR__)) . '/Mongo/MongoEndPoint.php';
require_once dirname(dirname(__DIR__)) . '/Core/ServerSentEvent.php';

use Bartleby\Core\CallData;
use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Core\ServerSentEvent;

final class SSETimeCallData extends CallData {
}

final class SSETime extends MongoEndPoint {

    function GET(SSETimeCallData $parameters) {
        // Creation of the SSE
        $sse = new ServerSentEvent(3600); // 1 time per second
        $s=$this;
        // Definition of the closure
        $f=function() use ($s,$sse,$parameters) {
            //$s->getDb();
            $serverTime = time();
            $sse->sendMsg($serverTime,'tic', '{"serverTime":"' . date("h:i:s", time()).'"}');
        };
        $sse->callBack=$f;
        return $sse;
    }

}

