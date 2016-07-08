<?php

namespace Bartleby\Core;

require_once __DIR__ . '/IResponse.php';
require_once __DIR__ . '/Response.php';

class HTMLResponse extends Response implements IResponse {

    public $document;
    public $statusCode;
    private $_prettyPrint;

    function usePrettyPrint($enabled){
        $this->_prettyPrint=$enabled;
    }


    function send(){
        header('Content-Type: text/html;charset=UTF-8');
        header('HTTP/1.1 ' . $this->statusCode. ' ' . Response::getRequestStatus ($this->statusCode));
        echo $this->document;
    }

}