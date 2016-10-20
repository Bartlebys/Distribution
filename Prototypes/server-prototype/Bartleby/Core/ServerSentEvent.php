<?php

namespace Bartleby\Core;

use Closure;

require_once BARTLEBY_ROOT_FOLDER.'Core/IResponse.php';

/**
 * Class ServerSentEvent
 * Implements Closure based Server Side events.
 * You should override check SSETime for a concrete Example
 *
 * IMPORTANT NOTE (!)
 * This implementation need to run as MOD on linux apache
 * mod_php (run as Apache's user)
 * the  ob_flush() call Is Needed on Linux
 * This code will not work using FAST CGI or CGI
 *
 * @package Bartleby\Core
 */
class ServerSentEvent extends  \stdClass implements IResponse {

    /* @var $callBack Closure */
    public $callBack;

    private $_frequencyPerHour;

    private $_sleepDuration;

    public function __construct($frequencyPerHour){
        $this->_frequencyPerHour= (isset($frequencyPerHour)&& $frequencyPerHour>0) ? $frequencyPerHour : 10 ;
        $this->_sleepDuration=(3600/$this->_frequencyPerHour);
    }

    public function usePrettyPrint($enabled){
        // We don't want to do anything in this case.
    }


    /**
     * Sends the response
     */
    function send() {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header("Connection: keep-alive");
        while(true) {
            if ($this->_is_closure($this->callBack)) {
                $this->callBack->__invoke();
            }else{
                throw new \Exception("Closure expected in ServerSentEvent");
            }
            sleep($this->_sleepDuration);
        }
    }

    private function _is_closure($t) {
        return is_object($t) && ($t instanceof Closure);
    }


    public function sendMsg($id,$eventName, $msg) {
        echo "id: $id" . PHP_EOL;
        echo "event: $eventName". PHP_EOL;
        echo "data: $msg" . PHP_EOL;
        echo PHP_EOL;
        if (PHP_OS != "Darwin") {
            ob_flush(); // Needed on Linux (!)
        }
        flush();
    }

}