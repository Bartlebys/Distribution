<?php

namespace Bartleby\Pages;

require_once __DIR__.'/BasePage.php';

use Bartleby\Core\HTMLResponse;
use Bartleby\Core\CallDataRawWrapper;

final class NotFoundCallData extends CallDataRawWrapper {

}

final class NotFound extends BasePage {

    function GET() {
        return $this->getDocument();
    }
    
    function POST() {
        return $this->getDocument();
    }

    function setup() {
        parent::setup();
        $this->addCSS($this->absoluteUrl('/static/css/fixed.css'));
        $this->title="Not found";
    }
    
    function mainContent() {
        return '<div class="site-wrapper">
      <div class="site-wrapper-inner">
        <div class="cover-container">
          '.$this->getNavigation('*').'
          <div class="inner cover">
            <h1 class="cover-heading">404</h1>
            <p class="lead">
              <a href="https://pereira-da-silva.com/" class="btn btn-lg btn-default">I would prefer not to!</a>
            </p>
          </div>
        </div>
      </div>
    </div>'.$this->footer();
    }


}