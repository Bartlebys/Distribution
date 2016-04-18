<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 13/07/15
 * Time: 16:23
 */


require_once "Deploy.php";

class LocalDeploy extends Deploy implements IDeploy{

    function __construct(\Hypotypose $hypotypose){
        $this->_hypothypose=$hypotypose;
        fLog(cr().'LOCAL deploy is running'.cr().cr(),true);
    }

    function copyFilesImplementation($filePath,$destination){
        if(!file_exists( dirname ($destination))) {
            mkdir(dirname($destination), 0777, true);
        }
        fLog('COPYING FROM : '.$filePath.cr().'TO'.$destination.cr(),true);
        copy($filePath,$destination);
    }
}