<?php


class Flog {
	/**
	 * Call this method to get singleton
	 *
	 * @return Flog
	 */
	public static function Instance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new Flog();
		}
		return $inst;
	}
	
	/**
	 * @var string
	 */
	private $_logs='';
	
	/**
	 * @return the $_logs
	 */
	public function getLogs() {
		return $this->_logs;
	}

	/**
	 * @param string $logs
	 */
	public function addMessage($message='') {
		$this->_logs.=$message;
	}

}

?>