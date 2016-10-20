<?php

namespace Bartleby\Commons\Pages;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoHTML5Page.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\Core\HTMLResponse;
use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoHTML5Page;


/**
 * Class Bootstrap3XPage
 *
 * A base page class with all the required resources to be able to use Bootstrap 3.X
 * You should expose a www/static/css/style.css
 * @package Bartleby\Commons\Pages
 */
abstract class Bootstrap3XPage extends MongoHTML5Page{

    /////////////////////////
    //
    // * PAGE LAYOUT *
    //
    // <head>
    //   <!-- metas -->
    // </head>
    // <body>
    //  <!--mainContent-->
    //  <!--scripts -->
    // </body>
    //
    //////////////////////////

    protected $_useCdn=false;

    /***
     * Setup method is called before to create the document.
     */
    function setup(){
        $this->_addJQuery();
        $this->_addBootstrapJS();
    }


    /**
     * Returns the global HTML5 response
     * @return HTMLResponse
     */
    function getDocument() {
        $this->setup();
        $r=new HTMLResponse();
        $r->statusCode=200;
        $r->document='<!DOCTYPE html>
<html lang="'.$this->_lang.'">
  <head>
    <meta charset="'.$this->_charset.'">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    '.$this->_metas().'
    <title>'.$this->_title.'</title>'
            .$this->_BootStrap()
            .$this->_top_scripts().'
    <!-- CSS -->'
            .$this->_CSSLink()
            .$this->_IE_Block().
            '
  </head>
  <body>
  <!--main content -->'
            .$this->mainContent().'
  <!-scripts-->'
            .$this->_bottom_scripts() .'
  </body>
</html>';

        return $r;
    }

    /***
     * Called to render the main content.
     * @return string
     */
    function mainContent(){
        return '';
    }


    ////////////////
    // JS and CSS
    ////////////////


    private  function _addBootstrapJS(){
        if ($this->_useCdn==true){
            $this->addTopScript('
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>');
        }else{
            $this->addTopScript('
    <script src="'.$this->absoluteUrl('static/vendors/bootstrap/3.3.6/js/bootstrap.min.js').'"></script>');
        }
    }


    private  function _addJQuery(){
        if ($this->_useCdn==true){
            $this->addTopScript('
    <!-- jQuery (necessary for Bootstrap\'s JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>');
        }else{
            $this->addTopScript('
    <!-- jQuery (necessary for Bootstrap\'s JavaScript plugins) -->
    <script src="'.$this->absoluteUrl('static/vendors/jquery/jquery-1.12.4.min.js').'"></script>');
        }
    }

    private  function _IE_Block() {
        if ($this->_useCdn == true) {
            return '
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->';
        } else {
            return '
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="' . $this->absoluteUrl('static/vendors/html5shiv/3.7.3/html5shiv.min.js') . '"></script>
      <script src="' . $this->absoluteUrl('static/vendors/respond/1.4.2/html5shiv.min.js') . '"></script>
    <![endif]-->';
        }
    }

    /**
     * @return string return a CDN link for bootstrap
     */
    private function _BootStrap(){
        if ($this->_useCdn==true){
            return'
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">';
        }else{
            return'
    <link rel="stylesheet" href="'.$this->absoluteUrl('static/vendors/bootstrap/3.3.6/css/bootstrap.min.css').'">
    <link rel="stylesheet" href="'.$this->absoluteUrl('static/vendors/bootstrap/3.3.6/css/bootstrap-theme.min.css').'">';
        }
    }

}