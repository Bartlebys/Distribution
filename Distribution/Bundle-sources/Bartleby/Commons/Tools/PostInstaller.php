<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 04/06/2016
 * Time: 07:52
 */

namespace Bartleby\Tools;

require_once BARTLEBY_PUBLIC_FOLDER . 'Configuration.php';

use \MongoClient;
use Bartleby\Core\Stages;

class PostInstaller {

    function logMessage($message=""){
        echo ($message."<br>\n");
    }

    /*@var MongoDb */
    protected $_db;

    function run($configuration){
        
        $this->logMessage ("");
        $this->logMessage ("Running Bartleby's POST INSTALLER");
        try {
            $this->logMessage("Connecting to MONGO");
            $m = new MongoClient();
        } catch (Exception $e) {
            $this->logMessage("Mongo client must be installed ". $e->getMessage());
        }
        $this->logMessage("Selecting the database  ".$configuration->MONGO_DB_NAME());
        $db = $m->selectDB($configuration->MONGO_DB_NAME());// Selecting  base

        // INDEXES

        $this->logMessage("Creating 'spaceUID' Index");
        $db->triggers->createIndex(array('spaceUID' => 1));
        $this->logMessage("Creating 'index'");
        $db->triggers->createIndex(array('index' => 1), array());
    }

}