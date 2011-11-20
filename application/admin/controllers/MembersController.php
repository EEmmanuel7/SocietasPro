<?php
/**
 * Members administration.
 *
 * @author Chris Worfolk <chris@societaspro.org>
 * @package SocietasPro
 * @subpackage Admin
 *
 * @todo There is code for adding custom fields in here
 */

namespace admin;

class MembersController extends \BaseController implements \iController {

	private $model;
	private $fieldsModel;
	
	function __construct () {
	
		parent::__construct();
		
		// create a members model
		require_once("models/MembersModel.php");
		$this->model = new \MembersModel();
		
		// create a members field model
		require_once("models/MembersFieldsModel.php");
		$this->fieldsModel = new \MembersFieldsModel();
	
	}
	
	/**
	 * Create a new member
	 */
	public function create () {
	
		// check for actions
		if (reqSet("action") == "create") {
			$this->model->write($_REQUEST);
			$this->engine->setMessage($this->model->getMessage());
		}
		
		// output the page
		$this->engine->assign("form", $this->standardForm("create"));
		$this->engine->display("members/create.tpl");
	
	}
	
	/**
	 * Create a new members field
	 */
	public function createfield () {
	
		// check for actions
		if (reqSet("action") == "create") {
			$this->fieldsModel->write($_REQUEST);
			$this->engine->setMessage($this->fieldsModel->getMessage());
		}
		
		// output the page
		$this->engine->assign("form", $this->fieldsForm("create"));
		$this->engine->display("members/createfield.tpl");
	
	}
	
	/**
	 * Export as CSV
	 */
	public function csv () {
	
		// csv object
		$csv = new \CsvBuilder(LANG_MEMBERS);
		
		// get an array of members
		$members = $this->model->get();
		
		// begin output
		$csv->addRow(array(LANG_ID,LANG_EMAIL,LANG_FORENAME,LANG_SURNAME,LANG_PRIVILEGES,LANG_ADDRESS,LANG_NOTES));
		
		// loop through members
		foreach ($members as $member) {
			$data = array (
				$member->memberID,
				$member->memberEmail,
				$member->memberForename,
				$member->memberSurname,
				$this->model->getPrivilege($member->memberPrivileges),
				$member->memberAddress,
				$member->memberNotes
			);
			$csv->addRow($data);
		}
		
		// log as an action in the audit trail
		auditTrail(12);
		
		// output the result
		$csv->output();
	
	}
	
	/**
	 * Edit a member
	 */
	public function edit () {
	
		// check for actions
		if (reqSet("action") == "edit") {
			$this->model->write($_REQUEST, \FrontController::getParam(0));
			$this->engine->setMessage($this->model->getMessage());
		}
		
		// output page
		$member = $this->model->getById(\FrontController::getParam(0));
		if ($member === false) { throw new \HttpErrorException(404); }
		
		$this->engine->assign("form", $this->standardForm("edit", $member->getAllData()));
		$this->engine->display("members/edit.tpl");
	
	}
	
	/**
	 * Edit a members field
	 */
	public function editfield () {
	
		// check for actions
		if (reqSet("action") == "edit") {
			$this->fieldsModel->write($_REQUEST, \FrontController::getParam(0));
			$this->engine->setMessage($this->fieldsModel->getMessage());
		}
		
		// output page
		$field = $this->fieldsModel->getById(\FrontController::getParam(0));
		if ($field === false) { throw new \HttpErrorException(404); }
		
		$this->engine->assign("form", $this->fieldsForm("edit", $field->getAllData()));
		$this->engine->display("members/editfield.tpl");
	
	}
	
	/**
	 * Form for members fields
	 *
	 * @param string $action Form variable
	 * @param array $data Default values
	 */
	private function fieldsForm ($action, $data = array()) {
	
		$form = new \FormBuilder();
		$form->addInput("name", LANG_NAME, arrSet($data, "fieldName"));
		$form->addSelect("type", LANG_TYPE, $this->fieldsModel->getTypes(), arrSet($data, "fieldType"));
		$form->addTextArea("options", LANG_OPTIONS, arrSet($data, "fieldOptions"));
		$form->addHidden("id", arrSet($data, "fieldID"));
		$form->addHidden("action", $action);
		$form->addSubmit();
		$form->setDefaultElement("name");
		
		return $form->build();
	
	}
	
	/**
	 * Custom fields
	 */
	public function fields () {
	
		// check for actions
		if (reqSet("action") == "mass") {
			if ($info = $this->determineMassAction()) {
				switch ($info["action"]) {
					case "delete":
						$this->fieldsModel->deleteById($info["ids"], 25);
						break;
				}
			}
			$this->engine->setMessage($this->fieldsModel->getMessage());
		}
		
		// output the page
		$fields = $this->fieldsModel->get();
		$this->engine->assign("fields", $fields);
		$this->engine->display("members/fields.tpl");
	
	}
	
	/**
	 * Import members
	 */
	public function import () {
	
		// build objects
		$form = new \FormBuilder();
		
		// check for actions
		if (reqSet("action") == "import") {
		
			// move file to a common location
			$fileHash = strtolower(md5($_FILES["upload"]["name"].microtime(true)));
			$fileLoca = TEMP_DIR."/".$fileHash.".csv";
			move_uploaded_file($_FILES["upload"]["tmp_name"], $fileLoca);
			
			// read file
			$file = file($fileLoca);
			
			// create a wizard
			$wizard = new \ImportMembersWizard($file);
			
			// get headers and build form
			$headers = $wizard->getColumnHeaders();
			$options = $wizard->getColumnOptions();
			
			foreach ($headers as $key => $val) {
				$form->addSelect("col".$key, $val, $options, $wizard->matchHeaderToColumn($val));
			}
			
			$form->addHidden("data", $fileHash);
			$form->addHidden("action", "import2");
			$form->addSubmit(LANG_IMPORT);
			
			// output page
			$this->engine->assign("form", $form->build());
			$this->engine->display("members/import2.tpl");
		
		} else {
		
			// check for actions
			if (reqSet("action") == "import2") {
		
				// get the data file
				$fileLoca = TEMP_DIR."/".strSantiseFilename($_REQUEST["data"]).".csv";
				$file = file($fileLoca);
				
				// create a wizard
				$wizard = new \ImportMembersWizard($file);
				
				// create a map based on user's selections
				$map = array();
				
				foreach ($_REQUEST as $key => $val) {
					if (substr($key, 0, 3) == "col") {
						//$newKey = intval(str_replace("col", "", $key));
						$map[$val] = intval(str_replace("col", "", $key));
					}
				}
				
				$result = $wizard->importUsingMap($map);
				$this->engine->setMessage($result);
			
			}
			
			$form->addFile("upload", LANG_CSV);
			$form->addHidden("action", "import");
			$form->addSubmit();
			
			// output page
			$this->engine->assign("form", $form->build());
			$this->engine->display("members/import.tpl");
		
		}
	
	}
	
	/**
	 * Index page
	 */
	public function index () {
	
		// check for actions
		if (reqSet("action") == "mass") {
			if ($info = $this->determineMassAction()) {
				switch ($info["action"]) {
					case "delete":
						$this->model->deleteById($info["ids"], 17);
						break;
				}
			}
			$this->engine->setMessage($this->model->getMessage());
		}
		
		// gather variables for page
		$pageNum = \FrontController::getParam(0);
		$totalPages = totalPages($this->model->count());
		$members = $this->model->get($pageNum);
		
		// output the page
		$this->engine->assign("members", $members);
		$this->engine->assign("totalPages", $totalPages);
		$this->engine->display("members/index.tpl");
	
	}
	
	/**
	 * Create a standard form for editing members
	 *
	 * @param string $action Form variable
	 * @param array $data Default values
	 */
	private function standardForm ($action, $data = array()) {
	
		$form = new \FormBuilder();
		$form->addInput("email", LANG_EMAIL, arrSet($data, "memberEmail"));
		$form->addInput("forename", LANG_FORENAME, arrSet($data, "memberForename"));
		$form->addInput("surname", LANG_SURNAME, arrSet($data, "memberSurname"));
		$form->addSelect("privileges", LANG_PRIVILEGES, $this->model->getPrivileges(), arrSet($data, "memberPrivileges"));
		$form->addTextArea("address", LANG_ADDRESS, arrSet($data, "memberAddress"));
		$form->addTextArea("notes", LANG_NOTES, arrSet($data, "memberNotes"));
		
		/*$fields = $this->fieldsModel->get();
		foreach ($fields as $field) {
			$fieldID = "custom".$field->fieldID;
			switch ($field->fieldType) {
				case "textarea":
					$form->addTextArea($fieldID, $field->fieldName, "");
					break;
				default:
					$form->addInput($fieldID, $field->fieldName, "");
			}
		}*/
		
		$form->addHidden("id", arrSet($data, "memberID"));
		$form->addHidden("action", $action);
		$form->addSubmit();
		$form->setDefaultElement("email");
		
		return $form->build();
	
	}

}
