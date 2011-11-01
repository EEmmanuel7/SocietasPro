<?php
/**
 * This is the default page for the admin panel.
 *
 * @author Chris Worfolk <chris@societaspro.org>
 * @package SocietasPro
 * @subpackage Admin
 *
 * @todo Finish implementation of tweets
 */

class DefaultController extends BaseController implements iController {

	function __construct () {
		parent::__construct();
	}
	
	public function index () {
	
		// get a database object
		require_once("database.php");
		$db = Database::getInstance();
		
		// get tweets
		require_once("twitter/TwitterTimeline.php");
		$timeline = new TwitterTimeline("SocietasPro");
		$tweets = $timeline->getAsArray();
		
		//print_r($tweets); die();
		
		// set variables
		$this->engine->assign("total_members", $db->fetchOne("SELECT COUNT(memberID) FROM ".DB_PREFIX."members"));
		$this->engine->assign("total_subscribers", $db->fetchOne("SELECT COUNT(subscriberID) FROM ".DB_PREFIX."subscribers"));
		
		// output age
		$this->engine->display("default/index.tpl");
	
	}

}
