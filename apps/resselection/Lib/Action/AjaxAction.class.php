<?php
import('api/userCredit_client.php');
class AjaxAction extends BaseAction{
	
	
	private $jjyrole;
	private $jjylocation;
	private $resClient;
	/**
	 * 初始化 个人信息角色
	 */
	public function _initialize(){
		parent::_initialize();
	    $this->jjylocation = $this->cyuserdata['locations'];
	    $this->jjyrole = $this->cyuserdata['instructor_level'];
	    $this->resClient = D('CyCore')->Resource;
	}
	
	
	/**
	 * 根据条件获取资源列表
	 * @param int $uid 用户id
	 * @author sjzhao
	 * @return 用户的资源列表
	 */
	public function getResList(){
		include_once './reslib/Common/common.php';
		$loid=isset($_POST['lcid'])?$_POST['lcid']:'';
		$subcode=isset($_POST['subcode'])?$_POST['subcode']:'';
		$status=isset($_POST['status'])?$_POST['status']:0;
		$prelevel=isset($_POST['prolevel'])?$_POST['prolevel']:0;
		$pageindex =  isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
		$pagesize=10;
		$uid=$this->mid;
		$keyword=$_POST['keyword']?$_POST['keyword']:'';
		
		if(!in_array($prelevel,array(0,1,2))){
			$content['status'] = 0;
			$content['msg'] = '操作异常！';
			exit(json_encode($content));
		}
		
		$role =  $this->jjyrole;
		$userlocation = $this->jjylocation;
		
		if(!UserRoleTypeModel::isHasSelectionRight($GLOBALS['ts']['cyuserdata']['instructor_level'])){
			exit("<div style='width:100%;text-align:center;font-size:15px;padding-top:10px;color:#229bd7;'> 对不起,您无此项操作权限！</div>");
		}
		if(!$userlocation){
			exit("<div style='width:100%;text-align:center;font-size:15px;padding-top:10px;color:#229bd7;'> 地区信息获取失败，请完善信息后重试！</div>");
		}
		
		
		switch ($role){
			case UserRoleTypeModel::PROVINCE_RESAERCHER:
				$order = $prelevel>0 ?'provinceratedate DESC':'cityratedate ASC';
				break;
			case UserRoleTypeModel::CITY_RESAERCHER:
				$order =  $prelevel>0 ?'cityratedate DESC':'countyratedate ASC';
				break;
			case UserRoleTypeModel::COUNTY_RESAERCHER:
				$order = $status > 0 ? 'countyratedate DESC':'uploaddateline ASC';
				break;
		}
		
		$result=D('Resource','reslib')->getResListByCondition($loid,$subcode,$status,$prelevel,$role,$pageindex,$pagesize,$keyword,$userlocation,0,$order);
		$nores=false;//该变量标记有没有当前登录者可以操作的资源
		$data=$result['data'];
		foreach ($data as &$value){
			switch ($role){
				case UserRoleTypeModel::PROVINCE_RESAERCHER:
			        if($value['province_auditor']==$uid){
					   $nores=false;//表示至少有一个资源登录者可以操作
				    }
				    $value['isupper']=0;//0代表没被上级审核,1表示已经被上级审核过,下级不能再次审核,其中省级没有更高等级
				    $this->level =1;
					break;
				case UserRoleTypeModel::CITY_RESAERCHER:
					if($value['city_auditor']==$uid){
						$nores=false;//表示至少有一个资源登录者可以操作
					}
					$value['isupper']=intval($value['province_level']!=0);
					$this->level =2;
					break;
				case UserRoleTypeModel::COUNTY_RESAERCHER:
					if($value['county_auditor']==$uid){
						$nores=false;//表示至少有一个资源登录者可以操作
					}
					$value['isupper']=intval($value['province_level']!=0 || $value['city_level']!=0);
					$this->level =3;
					break;
			}
		}
		$result['data']=$data;
		$rnum=0;
		foreach($data as $val){//如果一批资源全被上级审核过则隐藏批量按钮
			if($val['isupper']==1){
				$rnum++;
			}
		}
		$rnum==$result['count']?$nores=true:$nores=false;//如果改批资源全被上级遴选过或者全被别人遴选过,则关闭批量按钮
		//ajax分页
		$ajaxpage = new AjaxPage(array('total_rows'=>$result['count'],
				'method'=>'ajax',
				'parameter'=>'a=1',
				'ajax_func_name'=>'ResourceSelector.requestData',
				'now_page'=>$pageindex,
				'list_rows'=>$pagesize));
		$page = $ajaxpage->show();
		$this->page = $page;
		$this->pageNum = $pageindex;
		$this->resnum=$result['count'];
		$this->nores=intval($nores);//php中 false没有输出，所以转换为0
		$this->data=$result['data'];
		$this->prelevel=$prelevel;
		$this->uid=$uid;
		
		switch($status){
			case 0:
				$template = 'getResList';
				break;
			case 1:
				if($prelevel==0){
					$template = 'getNoSelect';
				}else{
					$template = 'getAlreadySelect';
				}
				break;
			case 2:$template = 'getNotPass';
				break;
		}
		$this->display($template);
	}
	
	/**
	 * 发送审核或者遴选结果消息
	 * 
	 * @param int or array $rid 资源id
	 * @param unknown_type $node  audit_pass已经审核通过，audit_un_pass审核未通过 ，rete_resource遴选结果
	 * @param unknown_type $cause 原因
	 * 
	 * @return null
	 */
	private function _sendMsg($rid, $node, $cause){
		if(!is_array($rid)){
			$rids[] = $rid;
		} else{
			$rids = $rid;
		}
		//发送消息
		if(!empty($rids)){
			foreach($rids as $v){
				$resource = D('Resource', 'reslib')->where(array("rid"=>$v))->field(array('rid,creator,title'))->find();
				if(!$resource['creator']){
					continue;
				}
				//将cyuid转换为uid
				$toUid = D('User')->where(array('login'=>$resource['creator']))->getField('uid');
				addMessage($this->mid, $GLOBALS['ts']['user']['uname'], $toUid, $resource['title'], $node, $v, $cause);
			}
		}
	}
	
	/**
	 * 审核资源操作
	 */
	public function auditResource(){
		$rid = $_REQUEST['id'];
		is_array($rid)?'':$rid=array($rid);
		$cause = $_REQUEST['cause'];
		$audit_status = intval($_REQUEST['audit_status']);
		$type=$_REQUEST['type'];
		$prolevel = intval($_REQUEST['prolevel'])?intval($_REQUEST['prolevel']):0;
		$uid = $this->mid;
		
		
		$userlocation =  $this->jjylocation;
		
		if(!in_array($prolevel,array(0,1,2))){
			$content['status'] = 0;
			$content['msg'] = '操作异常！';
			exit(json_encode($content));
		}
		if(!in_array($audit_status,array(0,1,2))){
			$content['status'] = 0;
			$content['msg'] = '操作异常！';
			exit(json_encode($content));
		}
		
		$auditRes = D('Resource','reslib')->fetchByRid($rid);
		foreach($auditRes as $val){
			if($userlocation['district']['id']!=$val['county']){
				$content['status'] = 0;
				$content['msg'] = '您不是该区县的区县教研员，无法进行该操作';
				exit(json_encode($content));
			}
			
			if($val['is_del']==1){
				$content['status'] = 0;
				$content['msg'] = '资源【'.$val['title'].'】资源已被删除';
				exit(json_encode($content));
			}
			
			if($val['audit_status']==1){
				$content['status'] = 0;
				$content['msg'] = '资源【'.$val['title'].'】为已审核资源';
				exit(json_encode($content));
			}
			if($prolevel&&$val['county_level']==$prolevel){
				switch($prolevel){
					case 1:
						$content['status'] = 0;
						$content['msg'] = '资源【'.$val['title'].'】已审核为县级精品资源';
						exit(json_encode($content));
						break;
					case 2:
						$content['status'] = 0;
						$content['msg'] = '资源【'.$val['title'].'】已审核为县级非精品资源';
						exit(json_encode($content));
						break;
					default:
						$content['status'] = 0;
						$content['msg'] = '您的操作出错了';
						exit(json_encode($content));
						break;
				}
			}
		}
		if($prolevel){
			$info['county_auditor'] = $uid;
		}
		$info['countyratedate'] = strtotime(date("Y-m-d H:i:s"));
		if(!$GLOBALS['ts']['cyuserdata']['instructor_level'] == UserRoleTypeModel::COUNTY_RESAERCHER){
			$content['status'] = 0;
			$content['msg'] = '您不是县级教研员无法进行该操作';
			exit(json_encode($content));
		}
		$info['audit_uid'] = $uid;
		$info['audit_status']=$audit_status;
		$info['county_level']= $prolevel;
		
	
		$res = D('Resource','reslib')->updateRes($rid,$info);//TODO更新接口有BUG
	
		if($res){
			//发送消息
			if($prolevel){
				//遴选状态同步更新到网关sjzhao 12.19
				$gateinfo = array();
				if($prolevel == 1){
					$cause = "县级 精品资源";
					$gateinfo['quality'] = '1';//区县精品
				}elseif($prolevel == 2){
					$cause = "县级 非精品资源";
					$gateinfo['quality'] = '11';//非精品资源
				}
				foreach ($rid as $value){
				    $updateres = $this->resClient->Res_UpdateResource($value, $gateinfo);
				}
				$this->_sendMsg($rid, "rate_resource", $cause);
			}else{
				if($audit_status == 1){
					$node = "audit_pass";
				}elseif($audit_status == 2){
					$node = "audit_un_pass";
				}
				$this->_sendMsg($rid, $node, $cause);
			}
			$content['status'] = 1;
			$content['msg'] = '审核结束';
			exit(json_encode($content));
		}else{
			$content['status'] = 0;
			$content['msg'] = '审核出错';
			exit(json_encode($content));
		}
		
	}

	/**
	 * 遴选资源操作
	 */
	public function rateResource(){
		$rid = $_REQUEST['id'];
		is_array($rid)?'':$rid=array($rid);
		$type=$_REQUEST['type'];
		$prolevel = intval($_REQUEST['prolevel'])?intval($_REQUEST['prolevel']):0;
		$uid = $this->mid;
		$auditRes = D('Resource','reslib')->fetchByRid($rid);
		$userlocation =  $this->jjylocation;
		
		if(!in_array($prolevel,array(0,1,2))){
			$content['status'] = 0;
			$content['msg'] = '操作异常！';
			exit(json_encode($content));
		}
		
		foreach($auditRes as $value){
				if($value['is_del']==1){
					$content['status'] = 0;
					$content['msg'] = '资源【'.$value['title'].'】已被删除';
					exit(json_encode($content));
				}
					
				if($value['audit_status']==0){
					$content['status'] = 0;
					$content['msg'] = '资源【'.$value['title'].'】未审核';
					exit(json_encode($content));
				}
		}
		$role =  $this->jjyrole;
		switch ($role){
				//省级
				case UserRoleTypeModel::PROVINCE_RESAERCHER:
					foreach($auditRes as $value){
							if($userlocation['province']['id']!=$value['province']){
								$content['status'] = 0;
								$content['msg'] = '您不是该省份的省级教研员，无法对资源【'.$value['title'].'】操作';
								exit(json_encode($content));
							}
							if($value['city_level']==2){
								$content['status'] = 0;
								$content['msg'] = '资源【'.$value['title'].'】已经被审核为市级非精品资源';
								exit(json_encode($content));
							}
					}
					$area_level = 'province_level';
					$area_level_name = '省级';
					$info['province_level']=$prolevel;
					if($prolevel){
						$info['province_auditor'] = $uid;
						$info['provinceratedate'] = strtotime(date("Y-m-d H:i:s"));
					}
					break;
					//市级
				case UserRoleTypeModel::CITY_RESAERCHER:
					$area_level = 'city_level';
					$area_level_name = '市级';
					$info['city_level']=$prolevel;
					if($prolevel){
						$info['city_auditor'] = $uid;
						$info['cityratedate'] = strtotime(date("Y-m-d H:i:s"));
					}
					foreach ($auditRes as $value){
							if($userlocation['city']['id']!=$value['city']){
								$content['status'] = 0;
								$content['msg'] = '您不是该市的市级教研员，无法对资源【'.$value['title'].'】操作';
								exit(json_encode($content));
							}
							if($value['province_level']){
								$content['status'] = 0;
								$content['msg'] = '资源【'.$value['title'].'】已被审核为省级资源';
								exit(json_encode($content));
							}
							if($value['county_level']==2){
								$content['status'] = 0;
								$content['msg'] = '资源【'.$value['title'].'】已经被审核为县级非精品资源';
								exit(json_encode($content));
							}
					}
					break;
					//县级
				case UserRoleTypeModel::COUNTY_RESAERCHER:
					$area_level = 'county_level';
					$area_level_name = '县级';
					$info['county_level']= $prolevel;
					if($prolevel){
						$info['county_auditor'] = $uid;
						$info['countyratedate'] = strtotime(date("Y-m-d H:i:s"));
					}
					foreach($auditRes as $value){
							if($userlocation['district']['id']!=$value['county']){
								$content['status'] = 0;
								$content['msg'] = '您不是该区县的区县教研员，无法对资源【'.$value['title'].'】操作';
								exit(json_encode($content));
							}
							if($value['city_level']){
								$content['status'] = 0;
								$content['msg'] = '资源【'.$value['title'].'】已被审核为市级资源';
								exit(json_encode($content));
							}
					}
					break;
				default:
					$content['status'] = 0;
					$content['msg'] = '对不起，您无权限操作！';
					exit(json_encode($content));
					break;
		}
		foreach ($auditRes as $val){
			if($val[$area_level] == $prolevel){
				switch($prolevel){
					case 1:
						$content['status'] = 0;
						$content['msg'] = '资源【'.$val['title'].'】已审核为'.$area_level_name.'精品资源';
						exit(json_encode($content));
						break;
					case 2:
						$content['status'] = 0;
						$content['msg'] = '资源【'.$val['title'].'】已审核为'.$area_level_name.'非精品资源';
						exit(json_encode($content));
						break;
					default:
						$content['status'] = 0;
						$content['msg'] = '您的操作出错了';
						exit(json_encode($content));
						break;
				}
			}
		}
		$res = D('Resource','reslib')->updateRes($rid,$info);//TODO更新接口有BUG
		if($res){
			//发送消息
			if($prolevel == 1){
				$cause = $area_level_name.'精品资源';
				//遴选状态同步更新到网关sjzhao 12.19
				$gateinfo = array();
				switch($area_level_name){
					case '省级':
						$gateinfo['quality']='3';//省优
						break;
					case '市级':
						$gateinfo['quality']='2';//市优
						break;
					case '县级':
						$gateinfo['quality']='1';//区优
						break;
					default:
						$gateinfo['quality']='0';//待遴选
				}
			    foreach ($rid as $value){
  			            $updateres = $this->resClient->Res_UpdateResource($value, $gateinfo);
			    }
			}else if($prolevel == 2){
				$cause = $area_level_name.'非精品资源';
			}
			$this->_sendMsg($rid, "rate_resource", $cause);
			$content['status'] = 1;
			$content['msg'] = '遴选结束';
			exit(json_encode($content));
		}else{
			$content['status'] = 0;
			$content['msg'] = '遴选出错';
			exit(json_encode($content));
		}
	}

	
	
	/**
	 * 审核通过资源
	 */
	public function auditpass(){
		$this->type=$_GET['type'];//1代表单个、2代表多个
		$optype = $_GET['optype']?$_GET['optype']:0;//默认操作按钮值
		$this->optype = $optype;
		$this->display('auditPassBox');
	}
	
	
	/**
	 * 审核不通过资源
	 */
	public function auditunpass(){
		$this->type=$_GET['type'];//1代表单个、2代表多个
		$this->display('auditUnPassBox');
	}
	
	/**
	 * 取消评优
	 */
	public function cancelRecommand(){
		$this->type=$_GET['type'];//1单个资源2、批量
		$this->assign('resoptype',1); //取消操作类型为1 
		$this->display('auditResultBox');
	}
	
	/**
	 * 确认评优
	 */
	public function makeRecommand(){
		$this->type=$_GET['type'];//1单个资源、2批量
		$this->assign('resoptype',2);//评优操作类型为2 
		$this->display('auditResultBox');
	}
	
	/**
	 * 下载资源
	 */
	public function download() {
		set_time_limit(0);
		session_write_close();
		$resid = $_REQUEST['id'];
		$uid = $_REQUEST['uid'];
		$login = $_REQUEST['login'];
		$filename = $_REQUEST['filename'];
		if (empty($resid)) {
			exit();
		}
		if(!empty($login)){
			$credit_client = new UserCredit_Client(C('bbs_space').'/api/userCredit.php');
			$credit_client->minusCreditByUsername($login,1);
		}
		//解决中文乱码
		if (!strpos($_SERVER["HTTP_USER_AGENT"], "Firefox")) {
			$filename = iconv("UTF-8", "GB2312", $filename);
		}
		// 	下载网关资源
		$downloadres = $this->resClient->Res_GetResIndex($resid,true);
		if ($downloadres->statuscode == "200" && $downloadres->data[0] != "") {
			$_file = $downloadres->data[0]->file_url;
			$_file = str_replace("\\\\", "/", $_file);
			$_file = str_replace("\"", "", $_file);
			if (!$filename) {
				$_filename = basename($_file);
			}
			$file_data = get_headers($_file, true);
			ob_end_clean();
			ob_start();
			ob_clean();
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.$filename);//不能使用urlencode会导致空格变'+'
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . $file_data['Content-Length']);
			
			$this->readfile_chunked($_file);
			$client_think = new Resource_Client(C('bbs_space') . '/api/resource.php');
			$client_think->addDownloadRecord($resid, $login);
		} else {
			exit();
		}
		exit();
	}
	
	private function readfile_chunked($filename,$retbytes=true) {
		$chunksize = 2*(1024*1024); // how many bytes per chunk
		$buffer = '';
		$cnt =0;
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			ob_flush();
			flush();
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt; // return num. bytes delivered like readfile() does.
		}
		return $status;
	
	}
	
}
/**
 * 改变资源类型名称
 * @param int $type资源类型
 * @return资源名称
 * @author sjzhao
 */
function changeResName($type){
	switch($type){
		case "0100":
			return "教学设计";
			break;
		case "0600":
			return "教学课件";
			break;
		case "0200":
			return "教学视频";
			break;
		case "1400":
			return "电子教材";
			break;
		case "0300":
			return "媒体素材";
			break;
		case "0400":
			return "习题";
			break;
		case "1600":
			return "实验设计";
			break;
		case "1700":
			return "拓展视野";
			break;
		case "0500":
			return "试卷";
			break;
		default:
			return "其他";
			break;
	}
}
/**
 * 将uid转为uname
 * @param int $uid
 * @return string uname
 * @author sjzhao
 */
function convertUidToUname($uid){
	$data=model("User")->getUserInfo($uid);
	return $data['uname'];
}
?>