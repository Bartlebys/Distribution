<?php

// DEMO CLASS FOR TEST PURPOSES

namespace Bartleby\Pages;


require_once __DIR__.'/BasePage.php';

use Bartleby\Pages\BasePage;
use Bartleby\Core\HTMLResponse;
use Bartleby\Core\CallDataRawWrapper;

final class TimeCallData extends CallDataRawWrapper {

}


final class Time extends BasePage {

    private $sseURL;

    function setup(){
        parent::setup();
        $this->addCSS($this->absoluteUrl('/static/css/fixed.css'));
        $this->_title="Server Time (via SSE)";
        // To support complex deployments we do inject $_SERVER['REQUEST_URI']
        // But remove the last component of the URI
        $this->sseURL = $this->getApiBaseURL().'time';
        // Add the SSE Script
        $this->importJSFile('static/js/TimeSSE.js');
        $this->addBottomScript('<script>timeSSE("'.$this->sseURL.'")</script>');
    }

    function GET() {
        return $this->getDocument();
    }

    function POST() {
        return $this->getDocument();
    }

    function mainContent() {
        return
    '<div class="site-wrapper">
      <div class="site-wrapper-inner">
        <div class="cover-container">
          '.$this->getNavigation("/time").'
          <div class="inner cover">
            <h1 class="cover-heading" id="output"></h1>
            <p><a href="'.$this->sseURL.'"> Time SSE </a></p>
          </div>
        </div>
      </div>
    </div>'.$this->footer();

    }

}