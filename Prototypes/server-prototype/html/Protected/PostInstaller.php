<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 04/06/2016
 * Time: 07:52
 */

namespace Bartleby;

require_once BARTLEBY_PUBLIC_FOLDER . 'Configuration.php';
require_once BARTLEBY_ROOT_FOLDER.'Commons/Tools/PostInstaller.php';

use \MongoClient;
use Bartleby\Core\Stages;

class PostInstaller extends \Bartleby\Tools\PostInstaller{

    function logMessage($message=""){
        echo ($message."<br>\n");
    }

    function run($configuration){
        // 1# run Bartleby's PostInstaller
        parent::run($configuration);
        // 2# do what you need to do.
        // $this->_db is set to the current DB.
        // Remove the flag
        @unlink(__DIR__ . '/not-installed');
    }

}