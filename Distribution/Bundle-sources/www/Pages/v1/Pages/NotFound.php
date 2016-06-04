<?php

namespace Bartleby\Pages;

require_once __DIR__.'/BasePage.php';

use Bartleby\Core\HTMLResponse;

final class NotFoundCallData extends BasePageCallData {

}

final class NotFound extends BasePage {

    function GET(NotFoundCallData $parameters) {
        return $this->getDocument();
    }


    function POST(NotFoundCallData $parameters) {
        return $this->getDocument();
    }


    /**
     * @return HTMLResponse
     */
    public function getDocument() {
        $r=new HTMLResponse();
		$r->statusCode=404;
        $r->document='<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Default 404 page</title>
</head>
<body>
	<header>
		<nav><ul></ul></nav>
	</header>
	<section>
		<article>
			<p>Not found</p>
		</article>
	</section>
	<footer>'.$this->_copyleft().'</footer>
</body>
</html>';
        return $r;
    }

}