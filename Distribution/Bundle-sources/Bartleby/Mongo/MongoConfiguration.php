<?php


namespace Bartleby\Mongo;

require_once BARTLEBY_ROOT_FOLDER . 'Core/Configuration.php';

use Bartleby\Core\Configuration;

class MongoConfiguration extends Configuration {

    // MONGO DB DEFAULT VALUES

    protected $_MONGO_DB_NAME='Set up your Mongo db name';
    protected $_MONGO_USERS_COLLECTION='users';
    protected $_MONGO_SPACE_UID_KEY_PATH='spaceUID';
    protected $_MONGO_USER_PASSWORD_KEY_PATH='password';

    /**
     * @return string
     */
    public function get_MONGO_DB_NAME() {
        return $this->_MONGO_DB_NAME;
    }

    /**
     * @return string
     */
    public function get_MONGO_USERS_COLLECTION() {
        return $this->_MONGO_USERS_COLLECTION;
    }


    /**
     * @return string
     */
    public function get_MONGO_SPACE_UID_KEY_PATH() {
        return $this->_MONGO_SPACE_UID_KEY_PATH;
    }

    /**
     * @return string
     */
    public function get_MONGO_USER_PASSWORD_KEY_PATH() {
        return $this->_MONGO_USER_PASSWORD_KEY_PATH;
    }

}