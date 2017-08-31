<?php

/**
 * 资源遴选控制器
 * @author sjzhao
 * @version 1.0
 */
class IndexAction extends BaseAction{
	
	/**
	 * 初始化函数
	 */
	public function _initialize(){
		parent::_initialize();
	 	if(!UserRoleTypeModel::isHasSelectionRight($GLOBALS['ts']['cyuserdata']['instructor_level'])){
			$this->error('对不起，您无此权限操作 !');
		}
	}
	
	/**
	 * 资源遴选首页
	 * @return void
	 */
	public function index(){
		$this->_init_subjects();
		$this->_init_quxian();
		$this->display();
	}
	/**
	 * 初始化学科信息
	 */
	private function _init_subjects(){
		$tempCategoroys =Model('Node')->subjects;
		$useDetail = D('CyUser')->getUserDetail($this->cymid);
		$categoroys = array();
		$subjectCodes = explode(',',$useDetail['subject']);
		foreach ($tempCategoroys as $value) {
			if($value['code'] == ''){
				$categoroys = array_merge($categoroys,array($value));
			}
			foreach ($subjectCodes as $scode) {
				if($scode!='' && $value['code'] == $scode ){
					$categoroys = array_merge($categoroys,array($value));
				}
			}
		}
		$this->assign("subject_self",$subjectCodes[0]);
		$this->assign('categoroys',$categoroys);
	}
	
	/**
	 * 初始化学校信息 
	 */
	private function _init_schools($areaid){
		$result = D("CySchool")->list_school_by_area($areaid,false,0,100);
		array_unshift($result,array('id'=>0,'name'=>'全部'));
		$this->assign('locationData',$result);
	}
	
	/**
	 * 初始化城市信息
	 */
	private function _init_city($areaid){
		$result =Model('CyArea')->listAreaById($areaid,'county',0,100); 
		array_unshift($result,array('id'=>0,'name'=>'全部'));
		$this->assign('locationData',$result);
	}
	
	/**
	 * 初始化省信息
	*/
	private function _init_province($areaid){
		$result = Model('CyArea')->listAreaById($areaid,'city',0,100); 
		array_unshift($result,array('id'=>0,'name'=>'全部'));
		$this->assign('locationData',$result);
	}
	
	/**
	 * 初始化是区还是县信息
	 */
	private function _init_quxian(){
		$cyuserdata = $this->cyuserdata;
		switch($GLOBALS['ts']['cyuserdata']['instructor_level']){
			case UserRoleTypeModel::PROVINCE_RESAERCHER:
				$this->_init_province($cyuserdata['locations']['province']['id']);
				$areaid = $cyuserdata['locations']['province']['id']?$cyuserdata['locations']['province']['id']:'';
				$this->_init_province($areaid);
				$this->assign('level',1);
				break;
			case UserRoleTypeModel::CITY_RESAERCHER:
				$areaid = $cyuserdata['locations']['city']['id']?$cyuserdata['locations']['city']['id']:'';
				$this->_init_city($areaid);
				$this->assign('level',2);
				break;
			case UserRoleTypeModel::COUNTY_RESAERCHER:
				$areaid = $cyuserdata['locations']['district']['id']?$cyuserdata['locations']['district']['id']:'';
				$this->_init_schools($areaid);
				$this->assign('level',3);
				break;
		}
	}
	
	
	/**
	 * 资源遴选详情页
	 * @author ylzhao
	 */
	public function detail(){
		include_once './reslib/Common/common.php';
		switch($GLOBALS['ts']['cyuserdata']['instructor_level']){
			case  UserRoleTypeModel::PROVINCE_RESAERCHER:
					    $level =1;
				        break;
			case  UserRoleTypeModel::CITY_RESAERCHER:
					   $level = 2;
					   break;
			case  UserRoleTypeModel::COUNTY_RESAERCHER:
						$level=3;
						break;
		}
		$resid = $_GET['id'];
		if(empty($resid)){
				$this->error("当前资源不存在！");
			}else{
				$resClient = D('CyCore')->Resource;
				$resourceInfo = $resClient->Res_GetResIndex($resid,true);
				//资源预览地址
				$resourceInfo = $resourceInfo->data[0];
				$preview_url = $resourceInfo->preview_url;
 				if(empty($resourceInfo) || $resourceInfo->lifecycle->curstatus !="1"){
					$this->error("当前资源不存在或未被审核！");
				}
	    }
		//从资源库获取资源
		$resource = D('Resource', 'reslib')->where(array("rid"=>$resid))->find();
		//获取上传者学校信息
		$location = Model('CyArea')->getFullArea($resource['county']);
		$locationText = $location['city']['name'].' '.$location['district']['name'];
		$school = D('CySchool')->get_school_info_by_id($resource['school_id']);
		//从资源网关获取视频后缀
		$extension =$resource['suffix'];
		//判断资源是否已删除
		if($resource['is_del'] == 1){
			$this->error("该资源已删除！");
		}
		//防止跨级遴选和遴选非精品资源
		if(($level == 2 && $resource['county_level']!=1)||($level == 1 && $resource['city_level']!=1)){
			$this->error("无法遴选该资源！");
		}
		//获取资源学科名
		$resource['subject'] = D('Node')->getNameByCode('subject',$resource['subject']);
		$this->assign("resource",$resource);
		$this->assign("level",$level);
		$this->assign("preview_url",$preview_url);
		$this->assign("isUploadLimit",false);
		$this->assign("uploadtime",$resource['uploaddateline']);
		$this->assign("extension",$extension);
		$this->assign("location",$locationText);
		$this->assign("school",$school['name']);
		$this->assign("role",$role);
		$this->display();
	}
	
	/**
	 * 资源遴选详情按钮
	 * @author ylzhao
	 */
	public function detail_btn(){
		$this->_init_quxian();
		$resid = $_POST['id'];
		//从资源库获取资源
		$resource = D('Resource', 'reslib')->where(array("rid"=>$resid))->find();
		//获取遴选人名字
		$uname = array();
		if(!empty($resource['county_auditor'])){
			$uname['county'] = M('User')->getUserInfo($resource['county_auditor']);
		}
		if(!empty($resource['city_auditor'])){
			$uname['city'] = M('User')->getUserInfo($resource['city_auditor']);
		}
		if(!empty($resource['province_auditor'])){
			$uname['province'] = M('User')->getUserInfo($resource['province_auditor']);
		}
		$this->assign("resource",$resource);
		$this->assign("uname",$uname);
		$this->display("detail_btn");
	}


	/**
	* 通过用户cyuid，查询其下辖区域
	* @param string $cyuid 用户cyuid
	* return  array
	*/
	private function getAreaByCyuid($cyuid){
		$res = array();
		$tempRes = D('CyUser')->getAreaByCyuid($cyuid);
		foreach ($tempRes as $value) {
			$tt = D('CyArea')->getFullArea($value->id);
			$res = array_merge($res,array($tt));
		}

		return $res;
	}

}
?>