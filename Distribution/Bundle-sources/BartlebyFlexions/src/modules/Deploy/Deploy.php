<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 13/07/15
 * Time: 17:11
 */



require_once FLEXIONS_ROOT_DIR . 'flexions/Core/Hypotypose.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/Core/Flexed.php';

interface IDeploy{
    function copyFilesImplementation($filePath,$destination);
}

class Deploy {

    /*@var $_hypothypose Hypotypose */
    protected $_hypothypose;


    /**
     * @var string this absolute destination should exists
     */
    protected $_absoluteBaseDestination='';

    function __construct(\Hypotypose $hypotypose){
        $this->_hypothypose=$hypotypose;
    }

    /**
     * @param $package
     * @param $absoluteDestination
     * @param bool|true $removelastpackagecomponent most of the time you want to remove for example the /php/ folder
     * @throws \Exception
     */
    function copyFiles($package,$absoluteDestination,$removelastpackagecomponent=true){

        if(substr($absoluteDestination,-1) != DIRECTORY_SEPARATOR){
            $absoluteDestination=$absoluteDestination.DIRECTORY_SEPARATOR;
        }
        $this->_absoluteBaseDestination=$absoluteDestination;
        if (isset($this->_hypothypose)) {
            $list = $this->_hypothypose->getFlatFlexedList();
            /* @var $flexed Flexed */
            foreach ( $list as $flexed ) {
                $filePath=$flexed->packagePath.$flexed->fileName;
                $packPosition=stripos($flexed->packagePath.$flexed->fileName,$package);
                //fLog('  '.$flexed->packagePath.$flexed->fileName.' '.$package.'->'.$packPosition.cr(),true);
                // This file should be copied
                if ($removelastpackagecomponent==true){
                    $packagecomponents=explode('/',$flexed->package);
                    array_shift($packagecomponents); // remove the last component
                    $joinedpackage=join('/',$packagecomponents);
                    $destination=$absoluteDestination.$joinedpackage.$flexed->fileName;
                }else{
                    $destination=$absoluteDestination.$flexed->package.$flexed->fileName;
                }
                if($packPosition!=false){
                    if($this instanceof IDeploy){
                        $this->copyFilesImplementation($filePath,$destination);
                    }else{
                        throw new \Exception('Deploy classes must implement IDeploy');
                    }
                }else{
                }
            }
        }else{
            throw new \Exception('LocalDeploy requires a valid hypotypose');
        }
    }

    /**
     * Equivalent to copy but we keep only the terminal folder.
     * @param $package
     * @param $absoluteDestination
     * @throws \Exception
     */
    function flatCopyFiles($package,$absoluteDestination){

        if(substr($absoluteDestination,-1) != DIRECTORY_SEPARATOR){
            $absoluteDestination=$absoluteDestination.DIRECTORY_SEPARATOR;
        }

        $this->_absoluteBaseDestination=$absoluteDestination;
        if (isset($this->_hypothypose)) {
            $list = $this->_hypothypose->getFlatFlexedList();
            /* @var $flexed Flexed */
            foreach ( $list as $flexed ) {
                $filePath=$flexed->packagePath.$flexed->fileName;
                $packPosition=stripos($flexed->packagePath.$flexed->fileName,$package);
                /* @var $packagecomponents array */
                $packagecomponents=explode('/',$flexed->package);
                if (count($packagecomponents)>0){
                    $packageSegment=$packagecomponents[count($packagecomponents)-1];
                    $destination=$absoluteDestination.$packageSegment.$flexed->fileName;
                }else{
                    $destination=$absoluteDestination.$flexed->fileName;
                }
                if($packPosition!=false){
                    if($this instanceof IDeploy){
                        $this->copyFilesImplementation($filePath,$destination);
                    }else{
                        throw new \Exception('Deploy classes must implement IDeploy');
                    }
                }else{
                }
            }
        }else{
            throw new \Exception('LocalDeploy requires a valid hypotypose');
        }
    }

}