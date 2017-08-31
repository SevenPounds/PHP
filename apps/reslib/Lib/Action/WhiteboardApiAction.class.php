<?php
/**
 * date 2013.10.9 9:36:35
 * @author frsun
 *
 */
import (APP_LIB_PATH.'/WhiteboardModel/ResourceIndex.php');
import (SITE_PATH . '/vendor/rrt/resourceclient/src/gatewayInterface/res/resource_type.php');

class WhiteboardApiAction extends Action{
	
	private $_resclient = null;
	
	public function __construct(){
		parent::__construct();
		date_default_timezone_set('PRC');
		$this->_resclient =  D('CyCore')->Resource;
	}
	
	public function index(){
		
	}	
	
	/**
	 * 获取网盘资源
	 */
	public function getnetdiskresources(){		
		$pagesize = !empty($_REQUEST['rows'])?$_REQUEST['rows']:15;
		$pagenum = !empty($_REQUEST['page'])?$_REQUEST['page']:1;
		$keyword=!empty($_REQUEST['keyword'])?$_REQUEST['keyword']:'';  //供白板使用  by frsun 20120828
		$match=!empty($_REQUEST['match'])?$_REQUEST['match']:'false';   //供白板使用  by frsun 20120828
		$usage=!empty($_REQUEST['usage'])?$_REQUEST['usage']:'';
		$sidx = !empty($_REQUEST['sidx'])?$_REQUEST['sidx']:'title';
		$sord = !empty($_REQUEST['sord'])?$_REQUEST['sord']:'asc';
		$_author = !empty($_REQUEST['username'])?$_REQUEST['username']:'';
		$folder = !empty($_REQUEST['folder'])?$_REQUEST['folder']:'';
		$elresourcetype = !empty($_REQUEST['elresourcetype'])?$_REQUEST['elresourcetype']:'';  //供授课工具使用  by frsun 20130412
		$arr_usage = explode(',', $usage);
		if($sidx=='uploadtime'){
			$sidx = 'uploaddateline';
		}elseif($sidx=='resourcetype'){
			$sidx = 'restype';
		}
		$type = array();
		if(count($arr_usage)!=3){
			foreach ($arr_usage as $val){
					$type = array_merge($type,array($this->_transformWhiteboardUsage($val)));
			}
		}
		
		$condition = array();
		$condition['author'] = $_author;
		$condition['keyword'] = $keyword;
		$condition['match'] = $match;
		$condition['type'] = implode(',', $type);
		$condition['sidx'] = strtolower($sidx);
		$condition['sord'] = $sord;
		$condition['page'] = intval($pagenum);
		$condition['size'] = intval($pagesize);
		$data = D('Resource')->getNetdiskResources($condition);
		if($data['records']>0){
			$total_pages = ceil($data['records']/$condition['size']);
			$data_new = array();
			foreach ($data['rows'] as $val){
				$obj = $this->_transformResToWhiteBoard($val);
				$data_new = array_merge($data_new,array($obj));
			}
			
			$result = array('total'=>$total_pages,'rows'=>$data_new,'page'=>$condition['page'],'records'=>$data['records']);
		}else{
			$result = array('total'=>0,'rows'=>array(),'page'=>0,'records'=>0);
		}
		
		return json_encode($result);
		
	}
	
	/**
	 * 上传资源
	 */
	public function uploadpageresource(){
		$id=$_REQUEST['file_id']?$_REQUEST['file_id']:"";
		$filemeta = $_REQUEST['filemeta']?$_REQUEST['filemeta']:$_REQUEST['file_index'];
		if(empty($_FILES)){
			return 'upload file failed';
		}		
		$file = $_FILES['file']['tmp_name'];
		$fileParts = pathinfo($_FILES['file']['name']);
		$targetFolder = UPLOAD_PATH;
		
		if(!is_dir($targetFolder)){  //如果不存在该文件夹
			mkdir($targetFolder, 0777);  //创建文件夹
			chmod($targetFolder, 0777);  //改变文件模式
		}
		$targetPath = $targetFolder;
		$targetFile = rtrim($targetPath,'/') . '/' . md5($fileParts['filename']) . '.' . $fileParts['extension'];
		$des = $targetFile;

		move_uploaded_file($file, $des);
		
		$res_original = str_replace('\\"', "\"", $filemeta);		
		$res_original = json_decode($res_original);

		$res_new = array();
		$res_new['productid'] ='rrt';
		$res_new['title'] = $fileParts['filename'];
		$res_new['creator'] = $res_original->Author;
		$res_new['length'] = $res_original->Size;
		$res_new['description'] = $res_original->Keywords;
		$res_new['extension'] = $fileParts['extension'];
		$res_new['source'] = $res_original->ViewData->Source?$res_original->ViewData->Source:"UGC";
		$res_new['rrtlevel1'] = "08";
		$res_new['grade'] = $res_original->Book->Grade;
		$res_new['subject'] = $res_original->Book->Subject;
		$res_new['volumn'] = $res_original->Book->Volumn;
		$res_new['publisher'] = $res_original->Book->Publisher;
		$res_new['book'] = $res_original->Book->Code;
		$res_new['unit'] = $res_original->Unit;
		$res_new['course'] = $res_original->Course;
		$type1 = $this->_transformWhiteboardUsage($res_original->Usage);
		$type2 = $this->_transformWhiteboardType($res_original->ResourceType);
		$res_new['type'] = array($type1,$type2);
		$res_new['curstatus'] = $res_original->Status=="2"?"1":"0";
		$res_new['auditstatus'] = '0';
		$res_new['curversion'] = $res_original->Version;
		$res_new['uploadtime'] = $res_original->UploadTime;

	    $result =$this->_resclient->Res_UploadRes($des, $res_new);

	    if(file_exists($des)){
			@unlink($des);
		}

		if($result->statuscode == 200){
			$res_new = $this->_resclient->Res_GetResIndex($result->data)->data[0];
			$status = $this->_recordUploadInfo($res_new);
			return "1";
		}else{
			return "0";
		}
	}
	
	/**
	 * 获取上传限制
	 */
	public function uploadsizelimit(){
		$result = new stdClass();		
		$size = @ini_get("upload_max_filesize");
		if(strpos($size, 'M')){
			$uploadsize = str_replace('M', '', $size);
		}elseif(strpos($size, 'G')){
			$uploadsize = str_replace('G', '', $size*1024);
		}elseif(strpos($size, 'K')){
			$uploadsize = str_replace('K', '', $size/1024);
		}else{
			$uploadsize = 0;
		}
		$result->result = "$uploadsize";		
		return json_encode($result);
	}
	
	/**
	 * 获取资源详细信息
	 */
	public function getresourcedescriptor(){
	    $resid = $_REQUEST['resid'];
		$author = $_REQUEST['author']?$_REQUEST['author']:"";
		$resinfo = $this->_resclient->Res_GetResIndex($resid);
		$result = new stdClass();
		$result->result = new stdClass();
		$result->result->msg = '';
		if($resinfo->statuscode==200){
			$res30 = $resinfo->data[0];
			$res20 = $this->_transformResToRes20($res30);
			$result->result->data = json_encode(array($res20));
			$result->result->error = '200';
		}else{
			$result->result->data = 'no data';
			$result->result->error = '400';
		}
		return  json_encode($result);
	}
	
	/**
	 * 下载资源
	 */
	public function downloadresource(){
		$resid = $_REQUEST['resid'];
		$filename = $_REQUEST['filename'];
		if(empty($resid) || empty($filename)){
			return ;
		}
		if(!strpos($_SERVER["HTTP_USER_AGENT"],"Firefox"))
		{
			$filename = rawurlencode($filename);
		}
		// 	下载网关资源
		$downloadres = $this->_resclient->Res_GetResIndex($resid,true);
		//如果下载失败
		if(!$downloadres||$downloadres->statuscode!= 200){
			return false;
		}
		$_file = $downloadres->data[0]->file_url;
		$filename =  str_replace(',',' ',$filename);
		$filename =  rawurlencode($filename);
		$flag = strpos($_file,"?");
	    $location = '';
	    if($flag){
	      $location = $_file.'&filename='.$filename;
	    }else{
	      $location = $_file.'?filename='.$filename;
	    }
        $legalUrls = C('LEGAL_URL');
        $isLegal = false;
        foreach($legalUrls as $legalUrl){
            if(strstr($location,$legalUrl)){
                $isLegal = true; break;
            }
        }
        if($isLegal){
            header("Location: $location");
        }
	}
	
	/**
	 * 获取资源的下载地址或缩略图
	 * resid 资源id
	 * type 获取url的类型   eg: file,thumbnail 
	 */
	public function getresourceurl(){
		$resid = $_REQUEST['resid'];
		$type = $_REQUEST['type'];
		$condition = array();
		if(empty($resid)||empty($type)){
			return 'no parameters';
		}
		if($type=="thumbnail"){
			$condition = array("size"=>"120_90");
		}
		$obj = $this->_resclient->Res_GetResIndex($resid,true);
		$result = $obj && $obj->statuscode==200?$obj->data[0]->$type."_url":"";
		return $result;
	}
	
	
	/**
	 * 上传ebook文件资源
	 */
	public function uploadebookresource(){

		$creator = $_REQUEST['username']?$_REQUEST['username']:"";
		$bookCode = $_REQUEST['code']?$_REQUEST['code']:"";
		$unitCode = $_REQUEST['unit']?$_REQUEST['unit']:"";
		$courseCode = $_REQUEST['course']?$_REQUEST['course']:"";
		$token = $_REQUEST['token']?$_REQUEST['token']:"";
		$filename = $_REQUEST['filename']?$_REQUEST['filename']:"";
		$metadata = $_REQUEST['metadata']?$_REQUEST['metadata']:"";
		$source = $_REQUEST['source']?$_REQUEST['source']:"UGC";
		$restype = $_REQUEST['restype']?$_REQUEST['restype']:"";
		$filesize = $_REQUEST['filesize']?$_REQUEST['filesize']:0;
		if(empty($_FILES)){
			return 'upload file failed';
		}
		
		$file = $_FILES['file']['tmp_name'];
		$fileParts = pathinfo(iconv('gb2312','utf-8',$filename));
		$targetFolder = UPLOAD_PATH;
		if(!is_dir($targetFolder)){  //如果不存在该文件夹
			mkdir($targetFolder, 0777);  //创建文件夹
			chmod($targetFolder, 0777);  //改变文件模式
		}
		$targetPath = $targetFolder;
		$targetFile = rtrim($targetPath,'/') . '/' . md5($fileParts['filename']) . '.' . $fileParts['extension'];
		$des = $targetFile;
		move_uploaded_file($file, $des);
		
		$res_new = array();
		$res_new['productid'] ='rrt';
		$res_new['title'] = $fileParts['filename'];
		$res_new['creator'] = $creator;
		$res_new['length'] = $filesize;
		$res_new['extension'] = $fileParts['extension'];
		$res_new['source'] = $source;
		$res_new['rrtlevel1'] = "08";
		$res_new['type'] = array('1400',$restype);
		$res_new['grade'] = substr($bookCode, 0,2);
		$res_new['subject'] = substr($bookCode, 2,2);
		$res_new['volumn'] = substr($bookCode, 4,2);
		$res_new['publisher'] = substr($bookCode, 6,2);
		$res_new['book'] = $bookCode;
		$res_new['unit'] = $unitCode;
		$courseCode = $courseCode=="-1"?"00":$courseCode;
		$res_new['course'] = $courseCode;
		$res_new['curstatus'] = "1";
		$res_new['auditstatus'] = '0';
		$res_new['uploadtime'] = date('Y-m-d H:i:s');

		$result = $this->_resclient->Res_UploadRes($des, $res_new);
		if(file_exists($des)){
			@unlink($des);
		}
		Log::write(json_encode($result),"INFO");
		if($result->statuscode == 200){
			 $res_new = $this->_resclient->Res_GetResIndex($result->data)->data[0];
			$status = $this->_recordUploadInfo($res_new);
			return "1";
		}else{
			return "0";
		}
	}
	
	/**
	 * 转换白板的Usage到资源平台支持的resourcetype
	 * @param unknown_type $usage
	 */
	private function _transformWhiteboardUsage($usage){
		$usage_ = resource_type::undefined;
		switch ($usage){
			case ResourceUsage::$CardPackage:
				$usage_ =resource_type::voiceapp;// resource_type::voiceapp_cardpackage;
				break;
			case ResourceUsage::$Card:
				$usage_ = resource_type::voiceapp_card;
				break;
			case ResourceUsage::$TeachMaterial:
				$usage_ = resource_type::media;
				break;
			case ResourceUsage::$Courseware:
				$usage_ = resource_type::courseware;
				break;
			default:
				$usage_ = resource_type::undefined;
				break;
		}		
		return $usage_;
	}
	
	/**
	 * 转换白板的resourcetype到资源平台支持的类型
	 * @param unknown_type $type
	 */
	private function _transformWhiteboardType($type){
		$type_ = resource_type::undefined;
		switch ($type){
			case ResourceType::$Package:
				$type_ = resource_type::voiceapp_cardpackage;
				break;
			case ResourceType::$Pages:
				$type_ = resource_type::courseware_page;
				break;
			case ResourceType::$EBookRes:
				$type_ = resource_type::courseware_zip;
				break;
			default:
				$type_ = resource_type::undefined;
				break;
		}		
		return $type_;
	}
	
	/**
	 * 转换资源平台的资源类型对应于白板的usage
	 * @param unknown_type $type
	 */
	private function _transformToWhiteboardUsage($type){
		$usage_ = ResourceType::$Undefined;
		switch ($type){
			case resource_type::voiceapp_cardpackage:
				$usage_ = ResourceUsage::$CardPackage;
				break;
			case resource_type::voiceapp_card:
				$usage_ = ResourceUsage::$Card;
				break;
			case resource_type::media:
				$usage_ = ResourceUsage::$TeachMaterial;
				break;
			case resource_type::courseware:
				$usage_ = ResourceUsage::$Courseware;
				break;
			default:
				$usage_ = resource_type::undefined;
				break;
		}		
		return $usage_;
	}
	
	/**
	 * 转换资源网站的资源类型到白板支持的资源类型
	 * @param unknown_type $type1
	 * @param unknown_type $type2
	 * @return number
	 */
	private function _transformToWhiteboardType($type1,$type2){
		$type_ = ResourceType::$Undefined;
		if($type1 == resource_type::voiceapp_cardpackage){
			
		}elseif($type1 == resource_type::media){
			switch ($type2){
				case resource_type::media_txt:
					$type_ = ResourceType::$Text;
					break;
				case resource_type::media_image:
					$type_ = ResourceType::$Image;
					break;
				case resource_type::media_video:
					$type_ = ResourceType::$Video;
					break;
				case resource_type::media_audio:
					$type_ = ResourceType::$Audio;
					break;
				case resource_type::media_animation:
					$type_ = ResourceType::$Flash;
					break;
				case resource_type::media_zip:
					$type_ = ResourceType::$Zip;
					break;
			}			
		}elseif($type1 == resource_type::courseware){
			switch ($type2){
				case resource_type::courseware_ppt:
					$type_ = ResourceType::$PPT;
					break;
				case resource_type::courseware_page:
					$type_ = ResourceType::$Pages;
					break;
			}
		}
		return $type_;
	}
	
	/**
	 * 记录本地资源信息
	 * @param unknown_type $res
	 */
	private function _recordUploadInfo($res){
		$useinfo = D('User')->getUserInfoByLogin($res->general->creator);
		$cyuserdata = $this->cyuserdata;		
		$reslocal = $this->_resLocalInfo($res,$useinfo['uname'],$cyuserdata);
		$result = D('Resource')->increase($reslocal);
		if($result>0){
			$res_opr['resource_id'] = $result;
			$res_opr['operationtype'] = 4;
			$res_opr['dateline']=$reslocal['uploaddateline'];
			$res_opr['login_name'] =$reslocal['creator'];
			//添加资源操作信息（上传、下载、收藏等）
			$result1 = D('ResourceOperation')->saveOrUpdate($res_opr);
			return $result1 > 0;
		}else{
			return 0;
		}
	}
	
	/**
	 * 创建本地资源信息
	 * @param unknown_type $res
	 * @param unknown_type $cyuserdata
	 */
	private function _resLocalInfo($res,$uname,$cyuserdata){
		$reslocal = array();
		$reslocal['rid'] =$res->general->id;
		$reslocal['title'] = $res->general->title;
		$reslocal['description'] =$res->general->description;
		$reslocal['creator'] = $res->general->creator;
		$reslocal['username'] = $uname;
		$reslocal['uploaddateline'] = strtotime($res->date->uploadtime);
		$reslocal['format'] = $res->properties->type[0];
		$reslocal['suffix'] = strtolower($res->general->extension);
		$reslocal['type1'] = $res->properties->type[0];
		$reslocal['type2'] = count($res->properties->type)>=2?$res->properties->type[1]:$res->properties->type[0];
		$reslocal['restype'] = $res->properties->type[0];
		$reslocal['product_id'] = $res->general->productid;
		$reslocal['downloadtimes'] = 0;
		$reslocal['praisetimes'] =  0;
		$reslocal['negationtimes'] = 0;
		$reslocal['praiserate'] = 0;
		$reslocal['grade'] =  '';
		$reslocal['subject'] = '';
		$reslocal['size'] = $res->general->length;
		$reslocal['province'] = $cyuserdata['locations']['province']['id'];//省
		$reslocal['city'] = $cyuserdata['locations']['city']['id'];//市
		$reslocal['county'] = $cyuserdata['locations']['district']['id'];//区县
		//所在学校id
		$schools = $cyuserdata['orglist']['school'];
		$school = array_pop($schools);
		$reslocal['school_id'] = $school['id'];
		return $reslocal;
	}
	
	/**
	 * 获取网盘资源时转换问题白板的支持的对象
	 * @param array $res
	 */
	private function _transformResToWhiteBoard($res){
		$wbObj = new stdClass();
		$wbObj->author = $res['creator']?$res['creator']:'';
		$wbObj->title = $res['title'].'.'.$res['suffix'];
		$wbObj->id = $res['rid'];
		$wbObj->size =$res['size']? intval($res['size']):0;
		$wbObj->uploadtime = date('Y-m-d H:i:s',$res['uploaddateline']);
		$wbObj->filename = $this->guid().'.'.$res['suffix'];
		$wbObj->shared = '1';
		$wbObj->visualfolder = $res['creator'].':';
		$wbObj->resourcetype = $this->_transformToWhiteboardType($res['restype'], $res['type2']);
		$wbObj->description = $res['description']?$res['description']:'';
		$wbObj->keywords = $res['keywords']?$res['keywords']:'';
		$wbObj->subject = $res['subject'];
		$wbObj->publisher = $res['publisher']? $res['publisher']:'';
		$wbObj->grade = $res['grade'];
		$wbObj->volumn = $res['volumn']?$res['volumn']:'';
		$wbObj->bookcode = $res['bookcode']?$res['bookcode']:'';
		$wbObj->unit = $res['unit']?$res['unit']:'';
		$wbObj->course = $res['course']?$res['course']:'';
		return $wbObj;		
	}
	
    /**
     * 转换资源平台的数据结构到白板支持的数据类型
     * @param array $res30
     */
	private function _transformResToRes20($res30){
		$res20 = new stdClass();
		$res20->pinyintitle= $res30->general->pinyintitle;
		$res20->statistics = new stdClass();
		$res20->chunkSize = $res30->general->length;
		$res20->_closed = TRUE;
		$res20->filename = $res30->general->filename;
		$filemeta = new ResourceIndex();
		$filemeta->id = $res30->general->id;
		$filemeta->localname = $res30->general->id.'.'.$res30->general->extension;
		$filemeta->size = $res30->general->length;
		$filemeta->filename = $res30->general->filename;
		$filemeta->resourcefile = '';
		$book = new BookIndex();
		$book->grade = $res30->properties->grade?$res30->properties->grade[0]:'';
		$book->publisher = $res30->properties->publisher?$res30->properties->publisher[0]:'';
		$book->subject = $res30->properties->subject?$res30->properties->subject[0]:'';
		$book->volumn = $res30->properties->volumn?$res30->properties->volumn[0]:'';
		$book->code = $res30->properties->book?$res30->properties->book[0]:'';		
		$filemeta->book = $book;				
		$filemeta->unit = $res30->properties->unit?$res30->properties->unit[0]:'';
		$filemeta->course = $res30->properties->course?$res30->properties->course[0]:'';
		$filemeta->code = $res30->general->id;
		$filemeta->packagecode = '';
		$usage = $this->_transformToWhiteboardUsage($res30->properties->type[0]);
		$filemeta->usage = $usage;
		$filemeta->category = ResourceCategory::$Undefined;
		$type = $this->_transformToWhiteboardType($res30->properties->type[0], $res30->properties->type[1]);
		$filemeta->resourcetype = $type;
		$filemeta->belongtodirectory = '';
		$filemeta->advancedcardclass = '';
		$filemeta->version = $res30->lifecycle->curversion;
		$filemeta->createtime = $res30->date->createtime;
		$filemeta->uploadtime = $res30->date->uploadtime;
		$filemeta->lastmodified = $res30->date->lastmodify;
		$filemeta->keywords = implode(',', $res30->tags);
		$filemeta->title = $res30->general->title.'.'.$res30->general->extension;
		$filemeta->author = $res30->general->creator;
		$filemeta->thumbnail = '';
		$filemeta->resourcedescriptor = '';
		$filemeta->description = $res30->general->description;
		$filemeta->shared = $res30->lifecycle->curstatus;  //1代表未分享；2代表分享
		$filemeta->visualfolder = $res30->general->creator.':';
		$filemeta->status = $res30->lifecycle->auditstatus; //1代表审核通过 ;2代表未审核
		$res20->filemeta = $filemeta;
		$res20->length = $res30->general->length;
		$res20->uploadDate = $res30->date->uploadtime;
		$res20->md5 = $res30->general->md5;
		return $res20;
	}
	
	//创建guid
	private function guid(){
		if (function_exists('com_create_guid')){
			return trim(com_create_guid(),'{}');
		}else{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = substr($charid, 0, 8).$hyphen
			.substr($charid, 8, 4).$hyphen
			.substr($charid,12, 4).$hyphen
			.substr($charid,16, 4).$hyphen
			.substr($charid,20,12);
			return $uuid;
		}
	}	
}
?>