<?php

require_once dirname(__DIR__).'/Configuration.php';
use Bartleby\Configuration;

$directory=dirname(__DIR__).'/';
$configuration=new Configuration($directory,BARTLEBY_ROOT_FOLDER);;

if(function_exists('mcrypt_encrypt')) {
    print '"mcrypt" is available. ';
}else{
    print '"mcrypt" is not available! ';
}
if ($configuration->getCryptedAuthCookieValue('NO_BODY','NO_DOCUMENT')=='NO_DOCUMENT'){
    print "But the authentication engine is not able to use it. That's a minor security issue that exposes publicly the userID via a cookie.";
}else{
    print '"userId" is crypted!';
}
