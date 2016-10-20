<?php

namespace Bartleby\Pages;


// DEMO CLASS FOR TEST PURPOSES

require_once __DIR__.'/BasePage.php';

use Bartleby\Pages\BasePage;
use Bartleby\Core\CallDataRawWrapper;

final class StartCallData extends CallDataRawWrapper {

}

final class Start extends BasePage {

    function  setup() {
        parent::setup();
        $this->title = 'Start Page';
        if(file_exists(BARTLEBY_PUBLIC_FOLDER.'Protected/not-installed')){
            //@include_once BARTLEBY_PUBLIC_FOLDER.'Protected/generated_destructiveInstaller.php';
        }
    }

    function GET() {
        return $this->getDocument();
    }

    function POST() {
        return $this->getDocument();
    }

    function mainContent() {
        return '
    <div class="site-wrapper">
      <div class="site-wrapper-inner">
        <div class="cover-container">
         '.$this->getNavigation("/").'
          <div class="inner cover col-md-4 col-md-offset-4">
            <h1 class="cover-heading">¿Bartleby?</h1>
            <p>Bartleby Is a full stack generative suite to build fault tolerant native distributed applications that can perform on and off line and interact in real time with web applications.</p> 
           <img class="img-thumbnail"  src="'.$this->absoluteUrl("static/images/bartlebys.jpg").'"/>
            <p>Bartleby is open source.</p>
           <p><a href="https://github.com/Bartlebys" class="btn btn-lg btn-default">View sources on Github</a></p>
          </div>
        </div>
      </div>
    </div>'.$this->footer();
    }

}