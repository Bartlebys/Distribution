<?php
namespace Bartleby\Pages;

require_once __DIR__.'/BasePage.php';

use Bartleby\Pages\BasePage;
use Bartleby\Core\HTMLResponse;
use Bartleby\Core\CallDataRawWrapper;


final class ToolsCallData extends CallDataRawWrapper {

}

class Tools extends BasePage  {

    function GET() {
        return $this->getDocument();
    }

    function POST() {
        return $this->getDocument();
    }

    function setup() {
        parent::setup();
        $this->title = 'Tools page';
        $this->importJSFile('static/js/ToolsPage.js');
    }

    function mainContent() {
        return '
    <div class="site-wrapper">
      <div class="site-wrapper-inner">
        <div class="cover-container">
         '.$this->getNavigation("/tools").$this->_mainBlock() .$this->_displayBlock().'
         </div>
        </div>    
      </div>
    </div>'.$this->footer();
    }

    private function _mainBlock(){
        $spaceUID=$this->getSpaceUID(true);
        if(isset($spaceUID) && $this->authenticationIsValid($spaceUID) ) {
            return '
          <div class="inner cover row col-md-12">
           <div class="btn-group" role="group" aria-label="...">
                <button type="button" class="btn btn-default runnable" id="generated_destructiveInstaller">Run destructive Installer</button>
                <button type="button" class="btn btn-default runnable" id="maintenance_ephemeralRemover">Cleanup Ephemeral Entities</button>
                <!-- <button type="button" class="btn btn-default runnable" id="echo">Echo</button> !-->
                <button type "button" class="btn btn-default" id="getInfos"> Call Get Infos endpoint</button>
                <button type "button" class="btn btn-default" id="getExport"> Export DataSpace</button>
          </div>
          ';
        }else{
            return '
          <div class="inner cover">
                <h2>Authentication is required</h2>
               <p><a href="'.$this->getSignInPageURL().'" class="btn btn-lg btn-default">Sign In!</a></p>
         </div>';
        }
    }



 



}