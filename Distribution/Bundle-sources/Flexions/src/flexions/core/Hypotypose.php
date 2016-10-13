<?php

//  Created by Benoit Pereira da Silva on 20/04/2013.
//  Copyright (c) 2013  http://www.chaosmos.fr

// This file is part of Flexions

// Flexions is free software: you can redistribute it and/or modify
// it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// Flexions is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU LESSER GENERAL PUBLIC LICENSE for more details.

// You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
// along with Flexions  If not, see <http://www.gnu.org/Licenses/>

require_once  __DIR__.'/DefaultStages.php';


/**
 *  An hypotypose is a structured description that
 *  uses  agnostic data structure
 *
 *  An  hypotypose instance is persistent during all flexions phases
 *
 * @package   flexions
 * @author    benoit@chaosmos.fr
 * @version   1.0.0
 */
final class Hypotypose extends stdClass {


    /**
     * Excluded path are generated but not dumped
     * @var array
     */
    public $excludePath = array();

    /**
     * @var string the current global stage
     */
    public $stage = DefaultStages::NO_STAGE;


    public $version = '';// No version by default

    function majorVersionPathSegmentString() {
        $components = explode('.', $this->version);
        if (count($components) > 0 && $components[0] != '') {
            return 'v' . $components[0] . '/';
        } else {
            return '';
        }
    }

    function stagePathSegmentString() {
        if ($this->stage != DefaultStages::NO_STAGE) {
            return $this->stage . '/';
        } else {
            return '';
        }
    }


    function majorVersionString() {
        $components = explode('.', $this->version);
        if (count($components) > 0) {
            return $components[0];
        } else {
            return '';
        }
    }


    /**
     * The list of the flexed files
     * @var array
     */
    public $flexedList = array();

    /**
     * class prefix
     * @var string
     */
    public $classPrefix = "";


    /**
     * @var string a facility to gain access to command line destination
     */
    public $exportFolderPath = '';


    /* @var Hypotypose */
    private static $_instance;

    /**
     * Call this method to get the current singleton
     * @return Hypotypose
     */
    public static function Instance() {
        if (self::$_instance === NULL) {
            self::$_instance = new Hypotypose ();
        }
        return self::$_instance;
    }

    /***
     * Reinitialize the singleton
     * @return Hypotypose
     */
    public static function NewInstance(){
        self::$_instance=NULL;
        return self::Instance();
    }

    /**
     *
     * @param Flexed $flexed
     */
    public function addFlexed(Flexed $flexed) {
        // Do not copy flexed files that are excluded or not
        if ((strlen($flexed->source) > Flexed::MIN_SOURCE_SIZE)
            && ($flexed->fileNameIsIn($this->excludePath) === false)
        ) {
            $this->flexedList [] = $flexed;
        }
    }


    public function getFlatFlexedList() {
        return $this->flexedList;
    }


    public function removePrefixFromString($string) {
        if (isset($this->classPrefix) && $this->classPrefix != "" && strpos($string, $this->classPrefix) === 0) {
            return substr($string, strlen($this->classPrefix));
        } else {
            return $string;
        }
    }


    public function ucFirstRemovePrefixFromString($string) {
        return ucfirst($this->removePrefixFromString($string));
    }
}
