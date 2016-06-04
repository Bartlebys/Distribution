<?php
/**
* Created by PhpStorm.
* User: bpds
* Date: 11/07/15
* Time: 10:36
*/

interface ISwaggerDelegate{
/**
* @param $prefix
* @param $baseClassName
* @return string
*/
function getCollectionClassName($prefix,$baseClassName);
}
?>