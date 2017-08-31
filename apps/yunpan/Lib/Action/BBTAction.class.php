<?php
/**
 * date 2014.5.20 9:36:35
 * @author frsun
 *
 */
require_once (APP_LIB_PATH.'/BBTModel/ResourceIndex.php');
require_once(APP_LIB_PATH . '/BBTModel/resource_type.php');

class BBTAction extends Action{
	
	private $_resclient = null;
	
	
	public function __construct(){
		parent::__construct();
		date_default_timezone_set('PRC');
	}
	
	public function index(){
		
		var_dump($this->formatdate(1400654298705));
		var_dump($this->_transformWhiteboardType(42));
		$resid = "b72559a3-305d-433a-9e11-f5bdd3baf523";
	    $result = D ('YunpanFile')->getFileProps(IFlyBookModel::CY_UID,$resid,120,90);

	    return json_encode($result);
	}	
	
	/**
	 * 获取网盘资源aaaa
	 */
	public function getnetdiskresources(){		
		$pagesize = !empty($_REQUEST['rows'])?$_REQUEST['rows']:15;
		$pagenum = !empty($_REQUEST['page'])?$_REQUEST['page']:1;
		$usage=!empty($_REQUEST['usage'])?$_REQUEST['usage']:'';
		$sidx = !empty($_REQUEST['sidx'])?$_REQUEST['sidx']:'title';
		$sord = !empty($_REQUEST['sord'])?$_REQUEST['sord']:'asc';
		$_author = !empty($_REQUEST['username'])?$_REQUEST['username']:'';
		$arr_usage = explode(',', $usage);
		if($sidx=='title'){
			$sidx = 2;
		}elseif($sidx=='size'){
			$sidx = 4;
		}elseif($sidx=='uploadtime'){
			$sidx = 3;
		}elseif($sidx=='resourcetype'){
			$sidx = 1;
		}
		if($sord=='asc'){
			$isdesc = false;
		}else{
			$isdesc = true;
		}
		$type = array();
		if(count($arr_usage)!=3){
			foreach ($arr_usage as $val){
					$type = array_merge($type,array($this->_transformWhiteboardUsage($val)));
			}
			$typeStr = implode(',',$type);
			$type = array('type'=>$typeStr);
		}
		
		$cyUser = M("CyUser")->getUserByLoginName($_author);
		$cy_uid = $cyUser["cyuid"];
		
		$restParams = array();
		$restParams['method'] = 'pan.dirid.get';
		$restParams['uid'] = $cy_uid;
		$restParams['folderType'] = 'yun_wendang';
		$wendang_fid = Restful::sendGetRequest($restParams);
		
		$data = D('YunpanFile')->listFilesByType($cy_uid, $wendang_fid,$type, intval($pagenum), intval($pagesize), $sidx, $isdesc);
		if($data->total > 0){
			$total_pages = ceil($data->total/intval($pagesize));
			$data_new = array();
			foreach ($data->data as $val){
				$obj = $this->_transformResToWhiteBoard($val,$_author,$cy_uid);
				$data_new = array_merge($data_new,array($obj));
			}				
			$result = array('total'=>$total_pages,'rows'=>$data_new,'page'=>intval($pagenum),'records'=>$data->total);
		}else{
			$result = array('total'=>0,'rows'=>array(),'page'=>0,'records'=>0);
		}
		return json_encode($result);
	}
	
	/**
	 * 上传资源
	 */
	public function uploadpageresource(){
		$filemeta = $_REQUEST['filemeta']?$_REQUEST['filemeta']:$_REQUEST['file_index'];
		if(empty($_FILES) || empty($_FILES["file"])){
			return "-1";
		}
		
		Log::write("开始上传",Log::DEBUG);
		$tmpFile = $_FILES['file']['tmp_name'];
		$fileinfo = pathInfo($_FILES['file']['name']);
		$fileName = $fileinfo['filename'];
		// 处理Linux环境下后缀名大写问题
		if (isset($fileinfo['extension'])) {
			$fileinfo['extension'] = strtolower($fileinfo['extension']);
			$fileName = $fileinfo['filename'];
		}
		
		$targetFolder = UPLOAD_PATH;
		if(!is_dir($targetFolder)){  //如果不存在该文件夹
			mkdir($targetFolder, 0777);  //创建文件夹
		}
		chmod($targetFolder, 0777);  //改变文件模式
		$targetFile = rtrim($targetFolder,'/') . '/' . md5($fileinfo['filename'].$this->user['login'].mt_rand()) . '.' . $fileinfo['extension'];
		
		$flag = move_uploaded_file($tmpFile, $targetFile);
		Log::write("移动文件的结果：".$flag,Log::DEBUG);
		if(file_exists($targetFile)){
			Log::write("目标文件存在",Log::DEBUG);
		}else{
			Log::write("目标文件不存在",Log::DEBUG);
		}
		try{
			$res_original = str_replace('\\"', "\"", $filemeta);
			$res_original = json_decode($res_original);
			$cyUser = M("CyUser")->getUserByLoginName($res_original->Author);
			$cy_uid = $cyUser["cyuid"];
			
			$restParams = array();
			$restParams['method'] = 'pan.dirid.get';
			$restParams['uid'] = $cy_uid;
			$restParams['folderType'] = 'yun_wendang';
			$wendang_fid = Restful::sendGetRequest($restParams);
			
			$ret = D('YunpanFile')->uploadFile($cy_uid,$wendang_fid,$targetFile,$fileName);
			Log::write("上传到云盘返回的结果：".json_encode($ret),Log::DEBUG);
		}catch (Exception $e){
			Log::write("上传到云盘异常信息：".json_encode($e),Log::DEBUG);
		}
		@unlink($targetFile);
		
		if($ret['statuscode']==1){
			$data = json_decode($ret['data']);
			$fid = $data->fid;
			Log::write("云盘资源的fid：".$fid,Log::DEBUG);
			$type1 = $this->_transformWhiteboardUsage($res_original->Usage);
			$type2 = $this->_transformWhiteboardType($res_original->ResourceType);
			$restype = array('type'=>array($type1,$type2));
			$result = D('YunpanFile')->addFileProps($cy_uid,$fid,$restype);
			Log::write("云盘资源的设置属性：".json_encode($result),Log::DEBUG);
			return "1";
		}else{
			return "0";
			
		}
		Log::write("结束上传",Log::DEBUG);
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
		$cyUser = M("CyUser")->getUserByLoginName($author);
		$cy_uid = $cyUser["cyuid"];
		$resinfo = D ( 'YunpanFile' )->getFile( $cy_uid, $resid );		
		$result = new stdClass();
		$result->result = new stdClass();
		$result->result->msg = '';
		if($resinfo['statuscode']==1){
			$res30 = $resinfo["data"];
			$res20 = $this->_transformResToRes20($res30,$author,$cy_uid);
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
		$resid = $_REQUEST ['resid'];
		$filename = $_REQUEST ['filename'];
		if(empty($resid) || empty($filename)){
		    return ;
		}
		if (! strpos ( $_SERVER ["HTTP_USER_AGENT"], "Firefox" )) {
			$filename = rawurlencode ( $filename );
		}
		$res = D ( 'YunpanFile' )->getFileUrl ( IFlyBookModel::CY_UID, $resid);
		var_dump($res);
		if ($res ['statuscode'] == 1) {
			$_file = $res ['data']->obj->strVal;
			$filename = str_replace ( ',', ' ', $filename );
			$filename = rawurlencode ( $filename );
			$flag = strpos ( $_file, "?" );
			$location = '';
			if ($flag) {
				$location = $_file . '&filename=' . $filename;
			} else {
				$location = $_file . '?filename=' . $filename;
			}
			header ( "Location: $location" );
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
		if(empty($resid)||empty($type)){
			return 'no parameters';
		}
		if($type=="thumbnail"){
			$obj = D('YunpanFile')->getThumbnail(IFlyBookModel::CY_UID,$resid,120,90);
		}
		$result = $obj && $obj->hasError==false?$obj->obj->strVal:"";
		
		return $result;
	}
	
	
	/**
	 * 上传ebook文件资源
	 */
	public function uploadebookresource(){
 		Log::write('iflybook开始上传文件',"INFO");
		
		$creator = $_REQUEST['username']?$_REQUEST['username']:"";
		$bookCode = $_REQUEST['code']?$_REQUEST['code']:"";
		$unitCode = $_REQUEST['unit']?$_REQUEST['unit']:"";
		$courseCode = $_REQUEST['course']?$_REQUEST['course']:"";
		$filename = $_REQUEST['filename']?$_REQUEST['filename']:"";
		$restype = $_REQUEST['restype']?$_REQUEST['restype']:"";
		if(empty($_FILES)){
			return 'upload file failed';
		}
		
		$file = $_FILES['file']['tmp_name'];
		$fileinfo = pathinfo(iconv('gb2312','utf-8',$filename));
		// 处理Linux环境下后缀名大写问题
		if (isset($fileinfo['extension'])) {
			$fileinfo['extension'] = strtolower($fileinfo['extension']);
			$fileName = $fileinfo['filename'];
		}
		
		$targetFolder = UPLOAD_PATH;
		if(!is_dir($targetFolder)){  //如果不存在该文件夹
			mkdir($targetFolder, 0777);  //创建文件夹
		}
		chmod($targetFolder, 0777);  //改变文件模式
		$targetFile = rtrim($targetFolder,'/') . '/' . md5($fileinfo['filename'].$this->user['login'].mt_rand()) . '.' . $fileinfo['extension'];
		
		$flag = move_uploaded_file($file, $targetFile);
		Log::write("移动文件的结果：".$flag,Log::DEBUG);
		if(file_exists($targetFile)){
			Log::write("目标文件存在",Log::DEBUG);
		}else{
			Log::write("目标文件不存在",Log::DEBUG);
		}
		
		$props = array();
		$props['type'] = array('1400',$restype);
		$props['grade'] = substr($bookCode, 0,2);
		$props['subject'] = substr($bookCode, 2,2);
		$props['volumn'] = substr($bookCode, 4,2);
		$props['publisher'] = substr($bookCode, 6,2);
		$props['book'] = $bookCode;
		$props['unit'] = $unitCode;
		$courseCode = $courseCode=="-1"?"00":$courseCode;
		$props['course'] = $courseCode;
		
		try{
			$cyUser = M("CyUser")->getUserByLoginName($creator);
			$cy_uid = $cyUser["cyuid"];
			
			$restParams = array();
			$restParams['method'] = 'pan.dirid.get';
			$restParams['uid'] = $cy_uid;
			$restParams['folderType'] = 'yun_wendang';
			$wendang_fid = Restful::sendGetRequest($restParams);
			
			$ret = D('YunpanFile')->uploadFile($cy_uid,$wendang_fid,$targetFile,$fileName);
			Log::write("上传到云盘返回的结果：".json_encode($ret),Log::DEBUG);
		}catch (Exception $e){
			Log::write("上传到云盘异常信息：".json_encode($e),Log::DEBUG);
		}
		@unlink($targetFile);
		
		if($ret['statuscode']==1){
			$data = json_decode($ret['data']);
			$fid = $data->fid;
			Log::write("云盘资源的fid：".$fid,Log::DEBUG);
			$type1 = $this->_transformWhiteboardUsage($res_original->Usage);
			$type2 = $this->_transformWhiteboardType($res_original->ResourceType);
			$restype = array('type'=>array($type1,$type2));
			$result = D('YunpanFile')->addFileProps($cy_uid,$fid,$props);
			Log::write("云盘资源的设置属性：".json_encode($result),Log::DEBUG);
			return "1";
		}else{
			return "0";
			
		}
		Log::write("结束上传",Log::DEBUG);
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
	private function _transformResToWhiteBoard($res,$author,$cyuid){
		$propsObj = D('YunpanFile')->getFileProps($cyuid,$res->fid);
		$props = $propsObj['statuscode']==1?$propsObj->data:new stdClass();
		$wbObj = new stdClass();
		$wbObj->author = $author;
		$wbObj->title = $res->name.'.'.$res->extension;
		$wbObj->id = $res->fid;
		$wbObj->size = strval($res->length);
		$wbObj->uploadtime = $this->formatdate($res->createtime);
		$wbObj->filename = $this->guid().'.'.$res->extension;
		$wbObj->shared = '1';
		$wbObj->visualfolder = $this->author.':';
		$wbObj->resourcetype = $this->_transformToWhiteboardType($props->type[0], $props->type[1]);
		$wbObj->description = '';
		$wbObj->keywords = '';
		$wbObj->subject = $props->subject?$props->subject[0]:"";
		$wbObj->publisher = $props->publisher? $props->publisher[0]:'';
		$wbObj->grade = $props->grade?$props->grade[0]:"";
		$wbObj->volumn = $props->volumn?$props->volumn[0]:'';
		$wbObj->bookcode = $props->bookcode?$props->bookcode[0]:'';
		$wbObj->unit = $props->unit?$props->unit[0]:'';
		$wbObj->course = $props->course?$props->course[0]:'';
		return $wbObj;		
	}
	
    /**
     * 转换资源平台的数据结构到白板支持的数据类型
     * @param array $res30
     */
	private function _transformResToRes20($res30,$author,$cyuid){
		
		$propsObj = D('YunpanFile')->getFileProps($cyuid,$res30->fid);
		$props = $propsObj['statuscode']==1?$propsObj->data:new stdClass();
		
		$res20 = new stdClass();
		$res20->pinyintitle= '';
		$res20->statistics = new stdClass();
		$res20->chunkSize = strval($res30->length);
		$res20->_closed = TRUE;
		$res20->filename = $res30->fid.'.'.$res30->extension;
		$filemeta = new ResourceIndex();
		$filemeta->id = $res30->fid;
		$filemeta->localname = $res30->fid.'.'.$res30->extension;
		$filemeta->size = strval($res30->length);
		$filemeta->filename = $res30->fid.'.'.$res30->extension;
		$filemeta->resourcefile = '';
		$book = new BookIndex();
		$book->grade = $props->grade?$props->grade[0]:'';
		$book->publisher = $props->publisher?$props->publisher[0]:'';
		$book->subject = $props->subject?$props->subject[0]:'';
		$book->volumn = $props->volumn?$props->volumn[0]:'';
		$book->code = $props->book?$props->book[0]:'';		
		$filemeta->book = $book;				
		$filemeta->unit = $props->unit?$props->unit[0]:'';
		$filemeta->course = $props->course?$props->course[0]:'';
		$filemeta->code = $res30->fid;
		$filemeta->packagecode = '';
		$usage = $this->_transformToWhiteboardUsage($props->type[0]);
		$filemeta->usage = $usage;
		$filemeta->category = ResourceCategory::$Undefined;
		$type = $this->_transformToWhiteboardType($props->type[0], $props->type[1]);
		$filemeta->resourcetype = $type;
		$filemeta->belongtodirectory = '';
		$filemeta->advancedcardclass = '';
		$filemeta->version = $res30->version;
		
		$filemeta->createtime = $this->formatdate($res30->createtime);
		$filemeta->uploadtime = $this->formatdate($res30->modifytime);
		$filemeta->lastmodified = $this->formatdate($res30->modifytime);
		$filemeta->keywords = "";
		$filemeta->title = $res30->name.'.'.$res30->extension;
		$filemeta->author = $author;
		$filemeta->thumbnail = '';
		$filemeta->resourcedescriptor = '';
		$filemeta->description = '';
		$filemeta->shared = "1";  //1代表未分享；2代表分享
		$filemeta->visualfolder = $author.':';
		$filemeta->status = "0"; //1代表审核通过 ;2代表未审核
		$res20->filemeta = $filemeta;
		$res20->length = strval($res30->length);
		$res20->uploadDate = $this->formatdate($res30->createtime);
		$res20->md5 = $res30->storgeid;
		return $res20;
	}
	
	//创建guid
	private function guid(){
		if (function_exists('com_create_guid')){
			return trim(com_create_guid(),'{}');
		}else{
			mt_srand((double)microtime()*10000);
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

    /**
    *时间转换
    */
	private function formatdate($time){
        $temp = strval($time);
        $temp = substr($temp,0,(strlen($temp)-3));
        return date('Y-m-d H:i:s',$temp);
	}

}
?>