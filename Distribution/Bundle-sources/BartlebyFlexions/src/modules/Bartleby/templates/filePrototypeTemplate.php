<?php

/*
 * This template can be used to copy prototype files.
 */

require_once FLEXIONS_MODULES_DIR . 'Bartleby/templates/Requires.php';


if (isset($f,$filemame,$package,$prototypePath)){
    $f->fileName = $filemame;
    $f->package = $package;
    echo file_get_contents($prototypePath);
}else{
    return NULL;
}