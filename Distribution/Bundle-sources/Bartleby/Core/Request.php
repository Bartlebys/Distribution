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

    /* @var $_parameters array*/
    private $_parameters=array();


    private $_flow;

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
        $this->_parameters=$_REQUEST;
        if(array_key_exists('request',$this->_parameters)){
            unset($this->_parameters['request']);
        }
        if(array_key_exists('XDEBUG_SESSION_START',$this->_parameters)){
            unset($this->_parameters['XDEBUG_SESSION_START']);
        }
        if(array_key_exists('XDEBUG_SESSION',$this->_parameters)){
            unset($this->_parameters['XDEBUG_SESSION']);
        }

        // (!) We donnot want to add attachments data to the parameters.
        // But we can sometimes use "php://input"
        // to extract JSON encoded params from the body
        if ( (!array_key_exists('HTTP_CONTENT_DISPOSITION',$_SERVER))
            || strpos($_SERVER['HTTP_CONTENT_DISPOSITION'],'attachment')===false){
            $flow=file_get_contents("php://input" );
            $flowVariables=json_decode($flow,true);
            if(isset($flowVariables)){
                $this->_parameters=array_merge($flowVariables,$this->_parameters);
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
    public function getParameters() {
        return $this->_parameters;
    }

    /**
     *
     * @return string
     */
    public function getHTTPMethod() {
        return $this->_method;
    }

}