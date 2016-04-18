<?php

namespace BartlebySync\Core;

require_once 'BartlebySyncConst.php';

class CommandInterpreter {
	
	/**
	 * The $ioManager
	 *
	 * @var IOManager
	 */
	protected $ioManager = NULL;
	
	/**
	 * References the current list of files to be used for finalization.
	 *
	 * @var array
	 */
	private $listOfFiles = array ();
	
	/**
	 *
	 * @param IOManager $ioManager        	
	 */
	public function setIOManager($ioManager) {
		$this->ioManager = $ioManager;
	}
	
	/**
	 * Interprets the command bunch
     * We try be resilient to potential client side errors (doubles, sequences, orders, ...)
	 * That's why we proceed use a double pass approach and so on...
     *
	 * @param string $treeId        	
	 * @param string $syncIdentifier        	
	 * @param array $bunchOfCommand        	
	 * @param string $finalHashMap
	 * @return null on success and a string with the error in case of any error
	 */
	function interpretBunchOfCommand($treeId, $syncIdentifier, array $bunchOfCommand, $finalHashMap) {

		$failures = array ();
		$hasProceededToUnPrefixing = FALSE;

		// Order matters.
		// Sort the command to execute delete commands at the end (after create, copy and move)
		usort ( $bunchOfCommand, array (
				$this,
				'_compareCommand' 
		) );

        //////////////////////////////////
        // Let's remove possible double
        //////////////////////////////////

        $filteredBunchOfCommand=array();
        foreach ($bunchOfCommand as $command){
            $alreadyExists=false;
            foreach($filteredBunchOfCommand as $filteredCommand){
                if(count ($filteredCommand)===count($command)){
                    $nbOfArguments=count($filteredCommand);
                    $match=true;
                    for($i=0;$i<$nbOfArguments;$i++){
                        $match=(($filteredBunchOfCommand[$i]==$command[$i])&& $match);
                    }
                    if($match==true){
                        $alreadyExists=true;
                    }
                }
            }
            if($alreadyExists===false){
                $filteredBunchOfCommand[]=$command;
            }
        }

        $secondAttempt=array();

        //////////////////
        // First pass
        //////////////////

		foreach ( $filteredBunchOfCommand as $command ) {
			if (is_array ( $command )) {
				if ($hasProceededToUnPrefixing === FALSE && $command [BCommand] > BUpdate) {
					// Un prefix after running all  commands.
					$unPrefixingFailures = $this->_unPrefix ( $treeId, $syncIdentifier );
					if (count ( $unPrefixingFailures ) > 0) {
						return $unPrefixingFailures;
					}
					$hasProceededToUnPrefixing = TRUE;
				}
				$result = $this->_decodeAndRunCommand ( $command, $treeId );
				if ($result != NULL) {
                    // We store for a next pass
                    $secondAttempt[]=$command;
				}
			} else {
				$failures [] = $command . ' is not an array';
			}
			if (isset ( $result )) {
				$failures [] = $result;
			}
			$result = NULL;
		}

        //////////////////
        // Second pass
        //////////////////

        // If we encounter a problem of dependency
        // (order of operation e.g a move before a dependant copy)
        foreach ( $secondAttempt as $command ) {
            if (is_array ( $command )) {
                $result = $this->_decodeAndRunCommand( $command, $treeId);
                if ($result != NULL) {
                    $failures [] = $result;
                }
            }
            $result = NULL;
        }

        //////////////////
        // Second pass
        //////////////////

		if (count ( $failures ) > 0) {

            return $failures;

		} else {

            // Remove the prefix from the synchronized files
			if($hasProceededToUnPrefixing==FALSE){
                // Un prefix the files.
				$unPrefixingFailures = $this->_unPrefix ( $treeId, $syncIdentifier );
				if (count ( $unPrefixingFailures ) > 0) {
					return $unPrefixingFailures;
				}
			}

            // Save the hashMap.

			$this->ioManager->mkdir ( $this->ioManager->absolutePath ( $treeId, METADATA_FOLDER.'/' ) );
			if ($this->ioManager->saveHashMap ( $treeId, $finalHashMap )) {
				return NULL;
			} else {
				$failures [] = 'Error when saving the hashmap';
				return $failures;
			}
		}
	}



	private function _compareCommand($a, $b) {

/*
        'BCreate' -> 0
        'BUpdate' -> 1
        'BMove' -> 2
        'BCopy' -> 3
        'BDelete' -> 4


        $aOrder=$a[BCommand];
        $bOrder=$b[BCommand];

*/
		return ($a [BCommand] > $b [BCommand]);
	}

    /**
     * Finalizes the bunch of command
     *
     * @param $treeId
     * @param string $syncIdentifier
     * @internal param string $finalHashMapFilePath
     * @return array
     */
	private function _unPrefix($treeId, $syncIdentifier) {
		$failures = array ();
		foreach ( $this->listOfFiles as $file ) {
			if (substr ( $file, - 1 ) != "/") {
				$relativePath = dirname ( $file ) . DIRECTORY_SEPARATOR . $syncIdentifier . basename ( $file );
				$protectedPath = $this->ioManager->absolutePath ( $treeId, $relativePath );
				if ($this->ioManager->exists ( $protectedPath )) {
					$this->ioManager->rename ( $protectedPath, $this->ioManager->absolutePath ( $treeId, $file ) );
				} else {
					$failures [] = 'Unexisting path : ' . $protectedPath . ' -> ' . $treeId . ' (' . $relativePath . ') ';
				}
			} else {
				// It is a folder with do not prefix currently the folders
			}
		}
        return $failures;
	}
	
	/**
	 * Decodes and runs the command
	 *
	 * @param array $cmd        	
	 * @param string $treeId        	
	 * @return string on error, or null on success
	 */
	private function _decodeAndRunCommand( array $cmd, $treeId) {

		if (count ( $cmd )> 1 ) {
			$command = $cmd [0];
			// Absolute paths
			$destination = $this->ioManager->absolutePath ( $treeId, $cmd [BDestination] );
			$source = $this->ioManager->absolutePath ( $treeId, $cmd [BSource] );
            $sourceExistsString=($this->ioManager->exists($source))?"Yes":"No";
            $destinationExistsString=($this->ioManager->exists($destination))?"Yes":"No";

			switch ($command) {
				case BCreate :
					if (! isset ( $cmd [BDestination] )) {
						return 'BDestination must be non null :' . $cmd;
					}
					// There is no real FS action to perform
					// The file should only be "unPrefixed"
					// We only add the file to listOfFiles to be unPrefixed
					$this->listOfFiles [] = $cmd [BDestination];
					return NULL;
					break;
					case BUpdate :
						if (! isset ( $cmd [BDestination] )) {
							return 'BDestination must be non null :' . $cmd;
						}
						// There is no real FS action to perform
						// The file should only be "unPrefixed"
						// We only add the file to listOfFiles to be unPrefixed
						$this->listOfFiles [] = $cmd [BDestination];
						return NULL;
						break;
				case BCopy :
					if ($this->ioManager->copy ( $source, $destination )) {
						return NULL;
					} else {
                        if(($this->ioManager->exists($destination)==true)
                            && ($this->ioManager->exists($source)==false)){
                            return NULL; // We keep the current destination file (May be inferred by a bad client sequence)
                        }
						return 'BCopy error source:' . $source .'(exists ='.$sourceExistsString.') destination: ' . $destination.' (exists ='.$destinationExistsString.')';
                    }
					break;
				case BMove :
					if ($this->ioManager->rename ( $source, $destination )) {
						return NULL;
					} else {
                        if(($this->ioManager->exists($destination)==true)
                            && ($this->ioManager->exists($source)==false)){
                            return NULL; // We keep the current destination file (May be inferred by a bad client sequence)
                        }
						return 'BMove error source:' . $source .'(exists ='.$sourceExistsString.') destination: ' . $destination.' (exists ='.$destinationExistsString.')';
					}
					break;
				case BDelete :
					if ($this->ioManager->delete ( $destination )) {
						return NULL;
					} else {
                        if($this->ioManager->exists($destination)==false){
                            return NULL;// There was no need to delete an unexisting path
                        }
						return 'BDelete error on ' . $destination.'(exists ='.$destinationExistsString.')';
					}
				default :
					break;
			}
		}
		return 'CMD ' . json_encode ( $cmd ) . ' is not valid';
	}
}
?>