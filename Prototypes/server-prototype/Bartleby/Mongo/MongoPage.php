<?php

namespace Bartleby\Mongo;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoController.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/CallDataRawWrapper.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/HTMLResponse.php';

use Bartleby\Core\CallDataRawWrapper;
use \MongoClient;


abstract class MongoPage extends MongoController {

    public $POST = "POST";
    public $GET = "GET";

    protected $_title='';
    protected $_charset='utf-8';
    protected $_lang='en';


    // HEAD
    private $_TOP_SCRIPTS=array();
    // End of the document.
    private $_BOTTOM_SCRIPTS=array();
    //
    private $_METAS=array();
    //
    private $_CSS=array();
    


    /***
     * Adds a css file link.
     * @param string $relativePath
     */
    protected  function addCSS($relativePath){
        $this->_CSS[]=$relativePath;
    }

    /***
     * Adds a script
     * @param $script
     */
    protected  function addTopScript($script){
        $this->_TOP_SCRIPTS[]=$script;
    }

    /***
     * Adds a script
     * @param $script
     */
    protected  function addBottomScript($script){
        $this->_BOTTOM_SCRIPTS[]=$script;
    }

    /**
     * Import a JS File 
     * e.g : $this->importJSFile('static/js/TimeSSE.js');
     * @param $relativePath
     */
    protected  function importJSFile($relativePath){
        $this->_BOTTOM_SCRIPTS[]='<script src="'.$this->absoluteUrl($relativePath).'"></script>';
    }
    
    /***
     * Adds a Meta
     * e.g: $this->addMeta('<meta name="keywords" lang="en" content="arts">')
     * @param $metaLine
     */
    protected  function addMeta($metaLine){
        $this->_METAS[]=$metaLine;
    }


    // The accessors

    protected function _CSSLink(){
        $links='';
        foreach ($this->_CSS as $link) {
            $links .= '
    <link href="'.$link.'" rel="stylesheet">';
        }
        return $links;
    }



    protected  function _top_scripts(){
        $scripts='';
        foreach ($this->_TOP_SCRIPTS as $script) {
            $scripts .= $script;
        }
        return $scripts;
    }


    protected  function _bottom_scripts(){
        $scripts='';
        foreach ($this->_BOTTOM_SCRIPTS as $script) {
            $scripts .= $script;
        }
        return $scripts;
    }

    protected  function _metas(){
        $metas='';
        foreach ($this->_METAS as $meta) {
            $metas .= $meta;
        }
        return $metas;
    }

    //

    public function absoluteUrl($relativePath){
        return $this->getConfiguration()->BASE_URL().ltrim($relativePath,'/');
    }


    /**
     * Returns the Api URL
     */
    function  getApiBaseURL(){
        $apiSignature='api/v1/';
        $baseURL=$this->getConfiguration()->BASE_URL().$apiSignature;
        return $baseURL;
    }


    

}