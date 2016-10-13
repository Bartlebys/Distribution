<?php

require_once 'XcdataModelDelegate.Interface.php';

// If necessary we define COLLECTION_OF
if (!defined('COLLECTION_OF')) {
    define("COLLECTION_OF", "CollectionOf");
}

/**
 * Class XcdataModelDelegate
 * Default implementation
 */
class XcdataModelDelegate implements XcdataModelDelegateInterface {

    /**
     * @param $prefix
     * @param $baseClassName
     * @return string
     */
    function getCollectionClassName($prefix, $baseClassName) {
        return ucfirst($prefix) . COLLECTION_OF . $baseClassName;
    }
}

?>