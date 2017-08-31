<?php
require_once APP_LIB_PATH.'/WhiteboardModel/ResourceCategory.php';
class BookIndex{
	public $version;
	public $resourcecount;
	public $id;
	public $code;
	public $tchid;
	public $subject;
	public $title;
	public $grade;
	public $volumn;
	public $publisher;
	public $thumbnail;
	public $category;

	function __construct(){
		$this->id = '';
		$this->category = ResourceCategory::$Undefined;
		$this->resourcecount = 0;
		$this->code = '';
		$this->tchid= '';
		$this->subject = '';
		$this->title = '';
		$this->grade = '';
		$this->volumn = '';
		$this->publisher = '';
		$this->thumbnail = '';
		$this->version = 1;
	}
}
?>