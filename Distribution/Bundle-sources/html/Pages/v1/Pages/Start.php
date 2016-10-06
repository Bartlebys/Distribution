<?php

// DEMO CLASS FOR TEST PURPOSES

namespace Bartleby\Pages;

require_once __DIR__.'/BasePage.php';

use Bartleby\Core\HTMLResponse;

final class StartCallData extends BasePageCallData {

}

final class Start extends BasePage {

    function GET(StartCallData $parameters) {
        return $this->getDocument();
    }

    function POST(StartCallData $parameters) {
        return $this->getDocument();
    }

    /**
     * @return HTMLResponse
     */
    public function getDocument() {
        $r=new HTMLResponse();
        $r->statusCode=200;
        $r->document='<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Default Start  page</title>
</head>
<body>
	<header>
		<nav><ul></ul></nav>
	</header>
	<section>
		<article>
			<p>Start page</p>
		</article>
	</section>
	<footer>'.$this->_copyleft().'</footer>
</body>
</html>';
        return $r;
    }

}