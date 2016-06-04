<?php

namespace Bartleby\Mongo;

use Bartleby\Core\Controller;
use Bartleby\Core\IPersistentController;
use \MongoClient;
use \MongoCursorException;
use \MongoDB;
use \Bartleby\Core\JsonResponse;

require_once __DIR__ . '/MongoConfiguration.php';
require_once dirname(__DIR__) . '/Core/Controller.php';

class MongoController extends Controller implements IPersistentController {

    /* @var User */
    private $_user;


    /**
     * The MongoDB
     *
     * @var MongoDB
     */
    private $_db;

    /**
     * The Mongo client
     *
     * @var MongoClient
     */
    private $_mongoClient;


    /**
     *  constructor.
     * @param MongoConfiguration $configuration
     */
    public function __construct(MongoConfiguration $configuration) {
        $this->_configuration = $configuration;
    }


    /**
     * @return MongoDB
     */
    protected function getDB() {
        try{
            if (!isset($this->_db)){
                $client= $this->getMongoClient();
                if(isset($client)){
                    $this->_db =$client->selectDB ($this->getConfiguration()->MONGO_DB_NAME());
                }
            }
            return $this->_db;
        }catch (\MongoConnectionException $e){
               throw new \Exception("MongoDB Connection Issue. ".$e->getMessage());
        }
    }


    /**
     * @return MongoClient
     */
    protected function getMongoClient() {
        if (!isset($this->_mongoClient)){
            $this->_mongoClient = new MongoClient ();
        }
        return $this->_mongoClient;
    }


    /**
     * @return \Bartleby\Mongo\MongoConfiguration
     */
    protected function getConfiguration(){
        return $this->_configuration;
    }

    ///////////////////////////
    // IPersistentController
    ///////////////////////////


    public function getUser(){
        if (isset($this->_user)){
            return $this->_user;
        }
        $id=$this->getCurrentUserID();
        if(isset($id)){
            /* @var MongoConfiguration */
            $mongoConf=$this->getConfiguration();
            $db=$this->getDB();
            $usersCollection=$mongoConf->MONGO_USERS_COLLECTION();
            $usernameKey=$mongoConf->MONGO_USER_NAME_KEY_PATH();
            $users = $db->{$usersCollection};
            try {
                $q = array (
                    $usernameKey =>$userName
                );
                $user = $users->findOne ( $q );
                if (isset ( $user )) {
                    $passwordMatches=($user->{$mongoConf->MONGO_USER_PASSWORD_KEY_PATH()} === $this->_configuration->salt($password));
                    if (!$passwordMatches){
                        return new JsonResponse($user,200);
                    }
                    return new JsonResponse($user,200);
                } else {
                    return new JsonResponse($user,404);
                }
            } catch ( MongoCursorException $e ) {
                return new JsonResponse('MongoCursorException' . $e->getCode() . ' ' . $e->getMessage(), 417);
            }

        }else{
            return User::visitor();
        }

    }

    public function authenticationIsValid (){
        $id=$this->getCurrentUserID();
        // @todo Check the validity
        return true;
    }





}