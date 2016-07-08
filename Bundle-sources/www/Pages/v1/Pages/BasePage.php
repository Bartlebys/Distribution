<?php

namespace Bartleby\Pages;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoHTML5Page.php';

use Bartleby\Core\CallData;
use Bartleby\Mongo\MongoHTML5Page;


class BasePageCallData extends CallData {

}
class BasePage extends  MongoHTML5Page{

    protected function _copyleft(){
        return '<p>Powered by Bartleby | Authenticated:'.($this->isAuthenticated("xxxx")?'<a href="/api/v1/user/logout/?dID=xxxx" _target="_blank">With ID '.$this->getCurrentUserID("xxxx").'</a>':'No').'</p>';
    }

}