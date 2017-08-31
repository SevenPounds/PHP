<?php
require_once APP_LIB_PATH.'/WhiteboardModel/BookIndex.php';
require_once APP_LIB_PATH.'/WhiteboardModel/ResourceType.php';
require_once APP_LIB_PATH.'/WhiteboardModel/ResourceUsage.php';

class ResourceIndex{	
	public $book;	
	public $unit;
	public $course;
	public $code;
	public $packagecode;
	public $usage;
	public $category;
	public $resourcetype;
	public $belongtodirectory;
	public $advancedcardclass;
	public $version;
	public $createtime;
	public $uploadtime;
	public $lastmodified;
	public $keywords;
	public $title;
	public $author;
	public $thumbnail;
	public $resourcedescriptor;
	public $description;
	public $status;
	public $id;
	public $size;
	public $resourcefile;
	public $shared;
	public $visualfolder;

	function __construct(){
		$this->id = '';
		$this->localname='';
		$this->size = '';
		$this->filename = '';
		$this->resourcefile = '';
		$this->book = new BookIndex();
		$this->unit = '';
		$this->course = '';
		$this->code = '';
		$this->packagecode = '';
		$this->usage = ResourceUsage::$Undefined;
		$this->category = ResourceCategory::$Undefined;
		$this->resourcetype = ResourceType::$Undefined;
		$this->belongtodirectory = '';
		$this->advancedcardclass = '';
		$this->version = '2.01';
		$this->createtime = date('Y-m-d H:i:s');
		$this->uploadtime = '';
		$this->lastmodified = date('Y-m-d H:i:s');
		$this->keywords = '';
		$this->title = '';
		$this->author='';
		$this->thumbnail = '';
		$this->resourcedescriptor = '';
		$this->description = '';
		$this->shared = '1';  //1代表未分享；2代表分享
		$this->visualfolder = '';
		$this->status = '2' ; //1代表审核通过 ;2代表未审核
	}
	
}
?>