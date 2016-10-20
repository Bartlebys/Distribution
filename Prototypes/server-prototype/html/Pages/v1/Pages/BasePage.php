<?php

namespace Bartleby\Pages;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoHTML5Page.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Pages/Bootstrap3XPage.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/CallDataRawWrapper.php';

use Bartleby\Mongo\MongoConfiguration;
use Bartleby\Commons\Pages\Bootstrap3XPage;
use Bartleby\Commons\Pages\Bootstrap3XPageCallData;
use Bartleby\Core\CallData;
use Bartleby\Mongo\MongoHTML5Page;



abstract class BasePage extends Bootstrap3XPage {


    private $_miniDebugFooterEnabled = false;

    // MENU
    /* @var array */
    protected $_menusItems=[];

    function setup() {
        parent::setup();
        $this->addCSS($this->absoluteUrl('/static/css/base.css'));
        // todo performance issue Those header should be cached.
        $permissions=$this->_context->getConfiguration()->getPermissionsRules();
        $h="{\n";
        foreach ($permissions as $key=>$permission) {
            $key=substr($key,0,strpos($key,'->'));
            $h .= '         "'.$key.'":'.$this->_getHTTPHeaders($key).",\n";
        }
        $h=substr($h,0,strlen($h)-2);
        $h .= "\n}\n";

        $httpHEADERScript='<script>
           var baseApiURL="'.$this->getApiBaseURL().'";
           var httpHeaders='.$h.';
        </script>';

        $this->addTopScript($httpHEADERScript);
        $this->importJSFile('static/js/Bartlebys.js');
        $this->importJSFile('static/js/Helper.js');
    }
    
    protected function addMenu($label,$href){
        if (is_string($label) && is_string($href)){
            $this->_menusItems[]=array($label,$this->absoluteUrl($href));
        }
    }

    protected function getMenuList($selected){
        $menus='';
        $i=0;
        $selected=$this->absoluteUrl($selected);
        foreach ($this->_menusItems as $menu) {
            // We ignore the querystring to determine selection
            $isSelected = (strtok($selected,'?') == strtok($menu[1],'?'));
            $class = $isSelected ? 'class="active"' : '';
            $href = $isSelected ? '': $menu[1];
            $menus .= '<li '.$class.'><a href="'.$href.'">'.$menu[0].'</a></li>';
            $i++;
        }
        return $menus;
    }

    /**
     * Returns the main navigation
     * @param $selected the relative path of the item.
     * @return string
     */
    function getNavigation($selected){
        if (count($this->_menusItems)==0){
            $spaceUID=$this->getSpaceUID(true);
            if(isset($spaceUID) && $this->isAuthenticated($spaceUID) ) {
                $this->addMenu('home','/?spaceUID='.$spaceUID);
                $this->addMenu('time','/time?spaceUID='.$spaceUID);
                $this->addMenu('triggers','/triggers?showDetails=true&spaceUID='.$spaceUID);
                $this->addMenu('tools','/tools?spaceUID='.$spaceUID);
                $this->addMenu('Sign Out','/signOut?spaceUID='.$spaceUID);
            } else{
                $this->addMenu('home','/');
                $this->addMenu('time','/time');
                $this->addMenu('triggers','/triggers?showDetails=true');
                $this->addMenu('tools','/tools');
                $this->addMenu('Sign In','/signIn');
            }
        }

        return '<div class="masthead clearfix">
            <div class="inner">
              <h3 class="masthead-brand">Bartleby\'s</h3>
              <nav>
                <ul class="nav masthead-nav">
                '.$this->getMenuList($selected).'
                </ul>
              </nav>
            </div>
          </div>';
    }


    function footer(){
        return '
    <footer>
          <p><a href="https://bartlebys.org">Bartleby\'s</a>, by <a href="https://pereira-da-silva.com/">@bpereiradasilva </a></p>
          ' . (($this->_miniDebugFooterEnabled) ? '<p>'.json_encode($this->_context->getVariables(),JSON_PRETTY_PRINT).'</p>' : "") . '
     </footer>';
    }

    function getSignOutPageURL(){
        $url=$this->getConfiguration()->BASE_URL().'signOut/?spaceUID='.$this->getSpaceUID(true);
        return $url;
    }

    function getSignInPageURL(){
        $url=$this->getConfiguration()->BASE_URL().'signIn/';
        return $url;
    }


    protected function _displayBlock(){
        $spaceUID = $this->getSpaceUID(true);
        if(isset($spaceUID) && $this->isAuthenticated($spaceUID) ) {
            return '
                <div class="row" id="displayDiv">
                    <pre class="codeFormat"><code id="outputResult"></code></pre>
                </div>';
        }else{
            return '';
        }
    }

    protected function  _getHTTPHeaders($action){
        $spaceUID=$this->getSpaceUID(true);
        if (isset($spaceUID) && $spaceUID!=""){
            return json_encode($this->getConfiguration()->httpHeadersWithToken($this->getSpaceUID(false),$action));
        }else{
            // Void headers
            return json_encode([]);
        }
    }


}