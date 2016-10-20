<?php

namespace BartlebySync\Core;

use Bartleby\Core\Context;

require_once BARTLEBY_SYNC_ROOT_PATH.'/Core/IOManager.php';

/**
 * Concrete IOManager using a file system
 * @author bpds
 */
final class IOManagerFS extends IOManagerAbstract implements IOManagerPersistency {


	/* @var Context  */
	protected $_context=null;

	/**
	 * IOManagerAbstract constructor.
	 * @param Context $context
	 */
	public function __construct(Context $context){
		$this->_context=$context;
		$this->status = 200;
	}

	/**
	 * @return Context
	 */
	public function getContext(){
		return $this->_context;
	}


	public function exists($filename) {
		return @file_exists ( $filename );
	}
	
	public function put_contents($filename, $data) {
		return @file_put_contents ( $filename, $data );
	}
	
	public function get_contents($filename){
		return @file_get_contents($filename);
	}
	
	public function mkdir($dir) {
		if (! file_exists ( $dir )) {
			$result=@mkdir ( $dir, 0755, true );
			return $result;
		}else{
			$this->getContext()->consignIssue($dir.' already exists',__FILE__,__LINE__);
		}
		return true;
	}
	
	public function rename($oldname, $newname) {
        $this->delete($newname);
		return @rename ( $oldname, $newname );
	}
	
	public function copy( $source, $destination ){
        $this->delete($destination);
		return @copy($source, $destination);
	}

    /**
     * Deletes a file or recursively a folder
     * Returns true if the file or the folder does not exists.
     * @see IOManagerPersistency::delete()
     * @param $filename
     * @return bool
     */
	public function delete($filename){
		if(!file_exists($filename)){
			return true;
		}
		if(is_dir($filename)){
			// we delete folders with a recursive deletion method
			return $this->_rmdir($filename,true);
		}else{
			return @unlink($filename);
		}
	}

    /**
     * @param $dir
     * @param $result
     * @return bool
     */
    private function _rmdir($dir,$result) {
		if (is_dir($dir)) {
			$objects = @scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir.DIRECTORY_SEPARATOR.$object) == "dir")
						$result=$result&&$this->_rmdir($dir.DIRECTORY_SEPARATOR.$object,$result);
					else 
						$result=$result&&@unlink($dir.DIRECTORY_SEPARATOR.$object);
				}
			}
			$result=$result&&@rmdir($dir);
		}
		return $result;
	}


    /**
     * @param $filename
     * @param $destination
     * @return bool
     */
    public function move_uploaded($filename, $destination) {
        $this->delete($destination);
		$this->mkdir( dirname ( $destination ));
		return @move_uploaded_file ( $filename, $destination );
	}


    /**
     * @param $dirPath
     * @param string $prefix
     * @return array
     */
    public function  listRelativePathsIn ($dirPath,$prefix=''){
		$dir = rtrim($dirPath, '\\/');
		$result = array();
		foreach (@scandir($dir) as $f) {
			if ($f !== '.' and $f !== '..') {
				if (is_dir("$dir/$f")) {
					$result = array_merge($result , $this->listRelativePathsIn("$dir/$f", "$prefix$f/"));
				} else {
					$result[] = $prefix.$f;
				}
			}
		}
		return $result;
	}

    public function removeGhosts(){
        $deletedPath=array();
        $foundPath=array();
        $pathFoundInTreeInfo=array();
        $dir=$this->repositoryAbsolutePath();
        $messages=array();
        foreach (@scandir($dir) as $f) {
            $path="$dir$f";
            if (! is_dir($path)){
                $messages[]="$path is not a folder | ";
                $deletedPath[]=$path;
                $this->delete($path);
                continue;
            }
            if($f!='.' && $f!='..'){
                $foundPath[]=$path;
                $p=$dir.$f.'/'.TREE_INFOS_FILENAME;
                if($this->exists($p)){
                    $this->treeData= json_decode( $this->get_contents($p));
                    $associatedPath=$dir.$this->treeData[0].'/';
                    $pathFoundInTreeInfo[]=$associatedPath;
                    if($this->exists($associatedPath)==false){
                        //IF there is TREE INFO file but no associated folder.
                        //ITS is a GHOST Delete the folder
                        $messages[]="$p is associated with a path that do not exists ($associatedPath) | ";
                        $deletedPath[]=$path;
                        $this->delete($path);
                    }
                }
            }
        }
        foreach ($foundPath as $p) {
            $path_parts = pathinfo($p);
            $fileName=$path_parts['basename'];
            $startByDot=(substr ($fileName,0,1 ) == ".");
            if( !in_array($p.'/',$pathFoundInTreeInfo) && !$startByDot){
                //  IF THERE IS NO ASSOCIATED TREE_INFOS_FILENAME IT IS A GHOST
                $messages[]="$p has no tree info file | ";
                $deletedPath[]=$p;
                $this->delete($p);
            }

        }
        return array(
                        "deletedPath"=> $deletedPath,
                        "messages"=>$messages
        );
    }


	/***
	 * Returns true if the installation has occured
	 * This method proceed to repository folder creation and verify that Php can write files.
	 * @param $path
	 * @return bool
	 */
	public function install($path) {

		clearstatcache();
		$installationIsWorking=true;
		$context=$this->getContext();

		if (!isset($path)){
			$context->consignIssue('Repository path is undefined',__FILE__,__LINE__);
			$path = $this->repositoryAbsolutePath();
		}

		$context->consignIssue('Current system user: '.get_current_user(),__FILE__,__LINE__);
		$writable= ( is_writable($path) ? "yes" : "no");
		$context->consignIssue('Is writable: '.$writable,__FILE__,__LINE__);


		if (!$fp = fopen($path, 'r')) {
			$context->consignIssue('Unable to open '.$path,__FILE__,__LINE__);
		}else{
			$meta = @stream_get_meta_data($fp);
			$context->consignIssue('Meta '.json_encode($meta),__FILE__,__LINE__);
		}

		if (!$this->exists ( $path )){
			$creation=$this->mkdir ( $path );
			if ($creation==false){
				$context->consignIssue('Folder creation as failed: '.$path,__FILE__,__LINE__);
			}
		}else{
			$context->consignIssue('Repository exists',__FILE__,__LINE__);
		}


		// Try to create a file in the created folder
		// This is a must so we set


		$content='ok';
		$filePath=$path.'temp.txt';
		$result=$this->put_contents($filePath,$content);
		if ($result==false){
			$context->consignIssue('Creation of '.$filePath.' has failed',__FILE__,__LINE__);
			$installationIsWorking=false;
		}else{
			// Remove this file
			$result=$this->delete($filePath);
			if ($result==false){
				$context->consignIssue('Deletion of '.$filePath.'temp.txt'.' has failed',__FILE__,__LINE__);
				$installationIsWorking=false;
			}
		}

		// Try to create a file in the parent folder
		// But do not block if it fails
		$content='ok';
		$filePath=dirname($path).'/temp.txt';
		$result=$this->put_contents($filePath,$content);
		if ($result==false){
			$context->consignIssue('Creation of '.$filePath.' has failed',__FILE__,__LINE__);
		}else{
			// Remove this file
			$result=$this->delete($filePath);
			if ($result==false){
				$context->consignIssue('Deletion of '.$filePath.'temp.txt'.' has failed',__FILE__,__LINE__);
			}
		}

		return $installationIsWorking;
	}



}
?>