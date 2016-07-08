<?php

namespace Bartleby\Core;

//use Bartleby\Core\RoutesAliases;


class Request {

    /**
     * @var $_method string
     */
    private $_method;

    /* @var $_path string*/
    private $_path;

    /* @var $_data array*/
    private $_data=array();

    /**
     * Request constructor.
     */
    public function __construct() {

        // Requests from the same server don't have a HTTP_ORIGIN header
        if (! array_key_exists ( 'HTTP_ORIGIN', $_SERVER )) {
            $_SERVER ['HTTP_ORIGIN'] = $_SERVER ['SERVER_NAME'];
        }

        // PATH
        $this->_path = '/';
        if(array_key_exists('request',$_REQUEST)){
            $this->_path .= $_REQUEST ['request'];
            $posOfQuestionMark=strpos($this->_path,'?');
            // We filter the query string from the path
            if ($posOfQuestionMark !== false){
                $this->_path = substr($this->_path,0,$posOfQuestionMark);
            }
        }

        // METHOD
        $this->_method = strtoupper ( $_SERVER ['REQUEST_METHOD'] );
        if ($this->_method == 'POST' && array_key_exists ( 'HTTP_X_HTTP_METHOD', $_SERVER )) {
            if ($_SERVER ['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->_method = 'DELETE';
            } else if ($_SERVER ['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->_method = 'PUT';
            } else {
                throw new \Exception ("Undefined Http Method");
            }
        }

        // PARAMETERS
        $this->_data=$_REQUEST;
        if(array_key_exists('request',$this->_data)){
            unset($this->_data['request']);
        }
        if(array_key_exists('XDEBUG_SESSION_START',$this->_data)){
            unset($this->_data['XDEBUG_SESSION_START']);
        }
        if(array_key_exists('XDEBUG_SESSION',$this->_data)){
            unset($this->_data['XDEBUG_SESSION']);
        }

        // (!) We donnot want to add attachments data to the parameters.
        // But we can sometimes use "php://input"
        // to extract JSON encoded params from the body
        if ( (!array_key_exists('HTTP_CONTENT_DISPOSITION',$_SERVER))
            || strpos($_SERVER['HTTP_CONTENT_DISPOSITION'],'attachment')===false){
            $flow=file_get_contents("php://input" );
            $flowVariables=json_decode($flow,true);
            if (isset($flowVariables) && is_array($flowVariables)) {
                $this->_data=array_merge($flowVariables,$this->_data);
            }
        }
        // CURRENTLY We don't urlDecode anymore the parameters keys,value

    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->_path;
    }


    /**
     * @return array
     */
    public function getData() {
        return $this->_data;
    }

    /**
     *
     * @return string
     */
    public function getHTTPMethod() {
        return $this->_method;
    }

}