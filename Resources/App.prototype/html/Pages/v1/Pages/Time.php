<?php

// DEMO CLASS FOR TEST PURPOSES

namespace Bartleby\Pages;


require_once __DIR__.'/BasePage.php';

use Bartleby\Core\HTMLResponse;

final class TimeCallData extends BasePageCallData {

}

final class Time extends BasePage {

    function GET(TimeCallData $parameters) {
        $r=new HTMLResponse();
        $r->statusCode=200;
        $url=$this->getApiBaseURL().'time';
        $r->document='<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Server Sent Event Time Sample</title>
</head>
<body>
	<header>
		<nav><ul></ul></nav>
	</header>
	<section>
		<article>
			<p id="output"></p>
		</article>
	</section>
	<footer>'.$this->_copyleft().' <a href="'.$url.'"> Time SSE </a> <em>'.$url.'</em></footer>
</body>
<script>
    var output = document.getElementById("output");
    var source = new EventSource("'.$url.'");
    var counter = 1;
    var lastLine ;
    source.addEventListener("tic",function(evt){
            var currentData=JSON.parse(evt.data)
            output.innerHTML=currentData.serverTime;
            counter++;
            return;
    })
</script>
</html>';
        return $r;
    }




}