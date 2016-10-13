<?php

interface XcdataModelDelegateInterface {
    /**
     * @param $prefix
     * @param $baseClassName
     * @return string
     */
    function getCollectionClassName($prefix, $baseClassName);
}

?>