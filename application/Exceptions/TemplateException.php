<?php
/**
 * Handles database errors.
 *
 * @author Chris Worfolk <chris@buzzsports.com>
 * @package SocietasPro
 * @subpackage Exceptions
 */

namespace Exceptions;

class TemplateException extends \Exception {

	/**
	 * Constructor
	 *
	 * @param string $msg Message
	 */
	function __construct ($msg = "") {
	
		// set message
		$this->message = $msg;
		
		// build details string
		$str  = "File: " . $this->getFile() . "\n";
		$str .= "Line: " . $this->getLine() . "\n";
		$str .= "Message: " . $this->getMessage() . "\n";
		$str .= "Trace: " . $this->getTraceAsString();
		
		// log gerror message
		//logError($this->getCode(), $str);
		
		// output
		if (MODE == "DEBUG") {
		
			echo(nl2br($str));
			exit(1);
		
		} else {
		
			throw new HttpErrorException(500, false);
		
		}
	
	}

}
