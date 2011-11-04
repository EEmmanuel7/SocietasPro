<?php
/**
 * Config model
 *
 * There is no table name because we don't actually want to allow access
 * to the generic functions from BaseModel, because they won't work on this
 * kind of table.
 *
 * @author Chris Worfolk <chris@societaspro.org>
 * @package SocietasPro
 * @subpackage Admin
 */

require_once("basemodel.php");

class ConfigModel extends BaseModel {

	private $model;
	
	/**
	 * Call parent constructor to create database object
	 */
	function __construct () {
		parent::__construct();
	}
	
	/**
	 * Set configuration option
	 *
	 * @param string $option Option
	 * @param string  $value Value
	 * @return boolean Success
	 */
	public function setOption ($option, $value) {
	
		// update the database
		$sql = "UPDATE ".DB_PREFIX."config SET
				configValue = '".escape($value)."'
				WHERE configOption = '".escape($option)."' ";
		$this->db->query($sql);
		
		// log to audit trail
		auditTrail(11, Configuration::get($option), $value);
		
		// reload config
		Configuration::reload();
		
		// return successful
		$this->setMessage(LANG_SUCCESS);
		return true;
	
	}

}