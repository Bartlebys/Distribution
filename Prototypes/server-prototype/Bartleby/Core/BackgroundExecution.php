<?php

namespace Bartleby\Core;

/**
 * A simple back ground execution process
 * Usage sample : 
 *				$process = new BackgroundExecution();
 *				$process->runPHP('
 *							$generator=new HashMapGenerator();
 *							$generator->HashMapForRelativePaths('.$relativePaths.');
 *						');
 *						
 *  @author bpds
 */
class BackgroundExecution{

	private $_pid=NULL;
	
	public  function runPHP($phpString,$outputFile = '/dev/null'){
		return $this->_pid = shell_exec( 'php -r \''.$phpString.'\' > ' .$outputFile.' 2>&1 &\; echo $! ' );
	}
	public  function runPHPFile($phpFile,$outputFile = '/dev/null'){
		return $this->_pid = shell_exec( 'php -f \''.$phpFile.'\' > ' .$outputFile.' 2>&1 &\; echo $! ' );
	}
	
	public function isRunning() {
		try {
			$result = shell_exec('ps '. $this->_pid);
			if(count(preg_split("/\n/", $result)) > 2) {
				return TRUE;
			}
		} catch(Exception $e) {		
		}
		return FALSE;
	}

	public function getPid(){
		return $this->_pid;
	}
	
	public function kill(){
		if (isset($this->_pid)){
			shell_exec('kill '. $this->_pid);
		}
	}
	
}
