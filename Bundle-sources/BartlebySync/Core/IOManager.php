<?php

namespace BartlebySync\Core;

require_once BARTLEBY_ROOT_FOLDER.'Core/Configuration.php';
require_once BARTLEBY_ROOT_FOLDER.'Core/Context.php';

require_once 'BartlebySyncConst.php';

use Bartleby\Configuration;
use Bartleby\Core\Context;

/**
 *  Standard IO Functions
 *  Implement those method to create
 *  a new persistency layer
 * @author bpds
 */
interface IOManagerPersistency {

	/**
	 * @return Context
	 */
	public function getContext();

	/**
	 * IOManager constructor should provide a context object.
	 * @param \Bartleby\Core\Context $context
	 */
	public function __construct(Context $context);


	public function exists($filename) ;

	public function put_contents($filename, $data);

	public function get_contents($filename);

	public function mkdir($dir);

	public function rename($oldname, $newname);

    /**
     * @param $source
     * @param $destination
     * @return mixed
     */
    public function copy( $source, $destination );

	public function delete($filename);

	public function move_uploaded($filename, $destination);

	public function listRelativePathsIn ($rootPath,$prefix='');

}

interface  IOManager extends IOManagerPersistency{


	/***
	 * Returns the status
	 * @return int
	 */
	public function getStatus();

	/**
	 * Returns the absolute path of a given resource
	 * @param string $treeId
	 * @param string $relativePath
	 * @return string|NULL
	 */
	public function absoluteUrl($treeId, $relativePath);

	/**
	 *  Returns the current public uri for a given resource
	 * @param string $treeId
	 * @param string $relativePath
	 * @return string|NULL
	 */
	public function uriFor($treeId, $relativePath);


	/**
	 * The tree id is persistent not the currentPublicId
	 * That  may change during the life cycle
	 *  For example in case of ACL invalidation for a group member
	 *   It is the public exposed tree root folder
	 * @param string $treeId
	 * @return array|NULL
	 */
	public function createTree( $treeId);



	/**
	 * Deletes the tree
	 * @param string $treeId
	 * @return array|NULL
	 */
	public function deleteTree( $treeId);



	/**
	 * Changes the public identifier.
	 *
	 * @param String $treeId
	 */
	public function touchTree($treeId);


	/**
	 *  Saves the Hash map
	 * @param string $treeId
	 * @param string $hashMap
	 * @return boolean
	 */
	public function saveHashMap($treeId, $hashMap);


	/**
	 *  Creates the repository
	 *  And could perform any installation related task
	 */
	public function install($path);


    /**
     * A function that scans the repository and removes the Ghosts and inconsistent contexts
     * @return array
     */
    public function removeGhosts();

}


/**
 *  IOmanager abstract class
 * @author bpds
 *
 */
abstract class IOManagerAbstract  {

	/**
	 *  Used to define a status code
	 * @var integer
	 */
	public $status=0;


	/**
	 *  The current tree data
	 * @var array
	 */
	protected   $treeData=NULL;


	public function getStatus(){
		return $this->status;
	}

	public function repositoryAbsolutePath() {
		return REPOSITORY_WRITING_PATH;
	}


	public function absoluteUrl($treeId, $relativePath) {
		$currentId=$this->_currentPublicId($treeId);
		if($currentId!=NULL){
			return $this->repositoryAbsolutePath () . $this->_currentPublicId($currentId). DIRECTORY_SEPARATOR . $relativePath;
		}
		return NULL;
	}


	public function uriFor($treeId, $relativePath) {
		$currentId = $this->_currentPublicId ( $treeId );
		$absoluteUrl = $this->absoluteUrl ( $treeId, $relativePath );
		if ($currentId != NULL) {
			if ($this->exists ( $absoluteUrl )) {
				$uri = REPOSITORY_BASE_URL . $currentId . DIRECTORY_SEPARATOR . $relativePath;
				$uri= str_replace(" ","%20",$uri );
					// @todo 401 if not authorized;
					// $this->status=401
					return $uri;
			}
		}
		$this->status = 404;
		return NULL;
	}

	public function createTree( $treeId){
		$currentPublicId = $this->_createAPublicId($treeId);
		$systemDataFolder = $this->_treeInfosFolderPathFor($treeId);
		$messages=array();
		// Create the system data folder
		if($this->exists($systemDataFolder)){
			return NULL;
		}
		 if (!$this->mkdir ($systemDataFolder)){
			$messages[]= $systemDataFolder.' mkdir error (System data folder)';
			return $messages;
		}

		// Put the current public id, owner, and an array of groups
		$this->treeData=array( $currentPublicId, Configuration::ANONYMOUS,  array(Configuration::ANONYMOUS), 777);

		if($this->put_contents($systemDataFolder.TREE_INFOS_FILENAME, json_encode($this->treeData))==false){
			$messages[]=$treeId.'createTree tree infos file_put_contents error '.$systemDataFolder.TREE_INFOS_FILENAME;
		}
		// Create the public id folder
		$currentPublicIdFolder=$this->repositoryAbsolutePath ().$currentPublicId. DIRECTORY_SEPARATOR ;
		if(!$this->mkdir($currentPublicIdFolder)){
			$messages[]= $currentPublicIdFolder.' mkdir error (public folder)';
		}
		// Create the meatdata folder in the public id folder
		if(!$this->mkdir($currentPublicIdFolder.METADATA_FOLDER.DIRECTORY_SEPARATOR)){
			$messages[]= $currentPublicIdFolder.METADATA_FOLDER.DIRECTORY_SEPARATOR.'   mkdir error  (Metadata folder)';
		}
		if(count($messages)>0){
			return $messages;
		}
		return NULL;
	}


	/**
	 * Deletes the tree
	 * @param string $treeId
	 * @return array|NULL
	 */
	public function deleteTree( $treeId){
		$messages = array ();
		$currentPublicId = $this->_currentPublicId ( $treeId );
		if ($currentPublicId == NULL) {
			$messages [] = 'tree does not exists';
		} else {
			$currentPublicIdFolder = $this->repositoryAbsolutePath () . $currentPublicId . DIRECTORY_SEPARATOR;
			$treeInfoFolder=$this->_treeInfosFolderPathFor ( $treeId );
			if ($this->exists ( $currentPublicIdFolder ) && $this->exists($treeInfoFolder)) {
				if ($this->delete($currentPublicIdFolder)==false || $this->delete($treeInfoFolder)==false) {
					$messages [] =  'Public or tree folder deletion has failed.'.$currentPublicIdFolder.' '.$treeInfoFolder;
				}
			} else {
				$messages [] =  'Public or tree folder data does not exist. '.$currentPublicIdFolder.' '.$treeInfoFolder;
			}
		}
		if (count ( $messages ) > 0) {
			return $messages;
		}
		return NULL;
	}




	public function touchTree($treeId) {
		$messages = array ();
		$currentPublicId = $this->_currentPublicId ( $treeId ); // populates $this->treeData
		if ($currentPublicId == NULL) {
			$messages [] = 'tree does not exists';
		} else {
			$currentPublicIdFolder = $this->repositoryAbsolutePath () . $currentPublicId . DIRECTORY_SEPARATOR;
			if ($this->exists ( $currentPublicIdFolder )) {
				// @TODO to be refactored We do not rename on touch.
				/*
				$oldPublicId = $this->treeData [0];
				$newPublicId = $this->_createAPublicId ($treeId);
				$newPublicIdFolder = $this->repositoryAbsolutePath () . $newPublicId . DIRECTORY_SEPARATOR;
				$this->treeData [0] = $newPublicId;
				if ($this->put_contents ( $this->_treeInfosFolderPathFor ( $treeId ) . TREE_INFOS_FILENAME, json_encode ( $this->treeData ) ) == false) {
					$messages [] = $treeId . ' tree infos file_put_contents error ' . $this->_treeInfosFolderPathFor ( $treeId ) . TREE_INFOS_FILENAME;
				} else {

					if ($this->rename ( $currentPublicIdFolder, $newPublicIdFolder ) == false) {
						$messages [] = $treeId . ' moving folder error ';
						// we need to try to reset the tree infos (fault resilience)
						$this->treeData [0] = $oldPublicId;
						$this->put_contents ( $this->_treeInfosFolderPathFor ( $treeId ) . TREE_INFOS_FILENAME, json_encode ( $this->treeData ) );
					}
				}
				*/
			} else {
				$messages [] = $currentPublicIdFolder . ' does not exist';
			}
		}
		if (count ( $messages ) > 0) {
			return $messages;
		}
		return NULL;
	}


	public function saveHashMap($treeId, $finalHashMap) {
		$destination = $this->absoluteUrl ( $treeId, METADATA_FOLDER .DIRECTORY_SEPARATOR. HASHMAP_FILENAME );
		return $this->put_contents($destination,$finalHashMap);
	}



	// Protected

	/**
	 * Creates a unique public id for a given server.
	 * @return string
	 */
	protected  function _createAPublicId($treeId){
		if (DEBUG_MODE_WITH_REAL_TREE_ID == false) {
			return md5(uniqid());
		}else{
			return $treeId;
		}

	}


	/**
	 * The infos folder for a given tree
	 * @param string $treeId
	 * @return string
	 */
	protected  function _treeInfosFolderPathFor($treeId){
		if (DEBUG_MODE_WITH_REAL_TREE_ID == false) {
			// The metadata folder uses the unique  tree id
			return $this->repositoryAbsolutePath() . SYSTEM_DATA_PREFIX . md5(BARTLEBY_SYNC_SECRET_KEY . $treeId) . DIRECTORY_SEPARATOR;
		} else {
			return $this->repositoryAbsolutePath() . SYSTEM_DATA_PREFIX . $treeId . DIRECTORY_SEPARATOR;
		}

	}

	/**
	 *  Returns the current public id of a given tree
	 * @param string $treeId
	 * @return string
	 */
	protected function _currentPublicId($treeId){
		if($this->treeData==NULL){
			$p=$this->_treeInfosFolderPathFor($treeId).TREE_INFOS_FILENAME;
			if($this->exists($p)){
				$this->treeData= json_decode( $this->get_contents($p));
				return $this->treeData[0];
			}
			return NULL;
		}else{
			return $this->treeData[0];
		}
	}


    public function removeGhosts(){
        // Void implementation
        return array();
    }

	// ABSTRACT METHOD

	public function exists($filename){
		return false;
	}

	public function put_contents($filename, $data){
		return false;
	}

	public function get_contents($filename){
		return NULL;

	}

	public function mkdir($dir){
		return false;
	}
	public function rename($oldname, $newname){
		return false;
	}

	public function copy( $source, $destination ){
		return false;
	}

	public function delete($filename){
		return false;
	}

	public function move_uploaded($filename, $destination){
		return false;
	}
	public function listRelativePathsIn ($rootPath,$prefix=''){
		return NULL;
	}

}
?>
