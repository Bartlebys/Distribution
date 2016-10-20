<?php

// DEMO CLASS FOR TEST PURPOSES

namespace Bartleby\Pages;


require_once __DIR__.'/BasePage.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/CallDataRawWrapper.php';

use Bartleby\Core\HTMLResponse;
use Bartleby\Core\CallDataRawWrapper;

final class TriggersCallData extends CallDataRawWrapper {

    const lastIndexKey = "lastIndex";

}

final class Triggers extends BasePage {


    function setup(){
        parent::setup();
        $this->addCSS($this->absoluteUrl('/static/css/base.css'));
        $this->title="Triggers (via SSE)";
        // Add the SSE Script
        $this->importJSFile('static/js/TriggersSSE.js');
        $this->addBottomScript('<script>triggersSSE("'.$this->getSSETriggerURL().'")</script>');
    }

    function GET() {
        return $this->getDocument();
    }

    function POST() {
        return $this->getDocument();
    }

    function mainContent() {
        $lastIndex=$this->getModel()->getValueForKey(TriggersCallData::lastIndexKey);
        return
            '<div class="site-wrapper">
      <div class="site-wrapper-inner">
        <div class="cover-container">
            '.$this->getNavigation("/triggers").$this->_displayBlock().$this->_mainBlock().'
        </div>
      </div>
    </div>'.$this->footer();
    }

    private function _mainBlock(){
        $spaceUID=$this->getSpaceUID(true);
        if(isset($spaceUID) && $this->authenticationIsValid($spaceUID) ) {
        return '
          <div class="inner cover">
            <table class="table">
            <thead><tr><th>Index</th><th>ObservationUID</th><th>Sender</th><th>runUID</th><th>Action</th><th>UIDS</th><th>Triggers</th></tr></thead>
            <tbody id="output">
            </tbody>
            </table>
             <p><a href="'.$this->getSSETriggerURL().'">Triggers SSE</a></p>
         </div>';
        }else{
           return '
          <div class="inner cover">
                <h2>Authentication is required</h2>
               <p><a href="'.$this->getSignInPageURL().'" class="btn btn-lg btn-default">Sign In!</a></p>
         </div>';
        }
    }
    
    function getSSETriggerURL(){
        $url=$this->getApiBaseURL().'SSETriggers?showDetails=true';
        $spaceUID = $this->getSpaceUID(true);
        if (isset($spaceUID)) {
            $url .= '&spaceUID=' . $spaceUID;
        }
        $observationUID=$this->getObservationUID(true);
        if (isset($observationUID)){
            $url.='&observationUID='.$observationUID;
        }
        return $url;
    }



}