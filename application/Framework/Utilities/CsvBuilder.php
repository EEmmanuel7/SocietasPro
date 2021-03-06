<?php
/**
 * Build and output a CSV file
 *
 * @author Chris Worfolk <chris@societaspro.org>
 * @package SocietasPro
 * @subpackage Utilities
 */

namespace Framework\utilities;

class CsvBuilder {

	private $output = "";
	private $filename;
	
	/**
	 * Constructor
	 *
	 * @param string $filename Filename that will be suggested, without the .csv
	 */
	function __construct ($filename = "data") {
	
		$filename = str_replace(" ", "_", $filename);
		$this->filename = strtolower($filename);
	
	}
	
	/**
	 * Add a row to the CSV file
	 *
	 * @param array $data Array of values
	 * @return boolean Success
	 */
	public function addRow ($data) {
	
		$data = str_replace("\"", "\\\"", $data);
		$line = implode('","', $data);
		$line = '"'.$line."\"\n";
		$this->output .= $line;
		return true;
	
	}
	
	/**
	 * Output the CSV file
	 *
	 * @param boolean $setHeaders Set the headers
	 */
	public function output ($setHeaders = true) {
	
		if ($setHeaders) {
			header("Content-type: application/csv");
			header("Content-Disposition: attachment; filename=".$this->filename.".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
		}
		
		print $this->output;
	
	}

}