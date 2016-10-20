<?php
namespace Bartleby;

require_once dirname(__DIR__).'/Configuration.php';
use Bartleby\Configuration;

if (isset($_GET['cookiecheck'])) {
    if (isset($_COOKIE['testcookie'])) {
        print "Cookies are enabled".CR;
    } else {
        print "Cookies are not enabled".CR;
    }
} else {
    setcookie('testcookie', "testvalue");
    die(header("Location: " . $_SERVER['PHP_SELF'] . "?cookiecheck=1"));
}
?>