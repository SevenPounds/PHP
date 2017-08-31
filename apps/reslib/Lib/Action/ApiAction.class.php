<?php
import (SITE_PATH . '/vendor/rrt/resourceclient/src/gatewayInterface/commonFunction/function_util.php');

/**
 * Ajax调用控制器
 * @author 
 * @version TS3.0
 */
class ApiAction extends Action {
	
	private $_resclient = null;
	
	/**
	 * 设置默认时区 
	 */
	protected function _initialize(){
		date_default_timezone_set('PRC');
		$this->_resclient = D('CyCore')->Resource; /**初始化资源client chengcheng3**/
	}
	/**
	 * 入口
	 * @return void
	 */
	public function index() {
		$this->ajaxReturn('');
	}
	
	public function addUploadRecord($resid, $login, $restype, $product_id){		
		//保存上传信息到本地数据库
		$result = $this->uploadResource($resid, $login, $restype, $product_id);
		
		if(empty($result))
			return 0;
		
		return 1;
	}	
	
	/**************************************************************
	 * 下载资源
	* @param string $rid,必须提供， 资源id
	* @param string $login,必须提供， 下载者登录名
	*
	* 成功返回1，失败返回-1
	* yangli4
	*
	*************************************************************/
	public function addDownloadRecord($rid, $login,$product_id="other"){		
		
		//构建本地上传记录
		$res = $this->initResinfoByGateinfo($rid);
		$res['product_id'] = $product_id;
		$resoucr_id = D('Resource')->where(array("rid"=>$rid))->getField("id");
		if(!$resoucr_id){
			//如果资源信息表中没有这条资源的信息，则重新插入记录
			$resoucr_id = D('Resource')->increase($res);
			if(!$resoucr_id){
				return 0;
			}
		} else {
			//更新数据库信息
			$info = array();
			$info['downloadtimes'] = $res['downloadtimes'];
			$info['praisetimes'] = $res['praisetimes'];
			$info['negationtimes'] = $res['negationtimes'];
			$info['praiserate'] =$res['praiserate'];
			$info['score'] =$res['score'];
			$res_update = D('Resource')->updateRes($rid, $info);
		}
		$userdata = D('User')->getUserInfoByLogin($login);
		//保存下载记录
		$res_opr = array();
		$res_opr['resource_id'] = $resoucr_id;
		$res_opr['login_name'] = $login;
		$res_opr['operationtype'] = ResoperationType::DOWNLOAD;
		$res_opr['dateline'] = time();
		$result = D('ResourceOperation')->saveOrUpdate($res_opr);
		return $result > 0;
	}
	
	/**************************************************************
	 * 收藏资源
	* @param string $rid,必须提供， 资源id
	* @param string $login,必须提供， 收藏者登录名
	* @return {'status':0 or 1 , 'message':'xxx'},0:失败,1:成功
	*
	*************************************************************/
	public function collectResource($rid, $login,$product_id="other"){
		if(!$rid || !$login){
			return json_encode(array('status'=>1,'message'=>'资源ID或用户名为空!'));
		}
		//构建本地上传记录
		$res = $this->initResinfoByGateinfo($rid);
		if(empty($res)){
			return json_encode(array('status'=>0,'message'=>'该资源不存在!'));
		}
		$res['product_id'] = $product_id;
		$resoucr_id = D('Resource')->where(array("rid"=>$rid))->getField("id");
		if(!$resoucr_id){
			//如果资源信息表中没有这条资源的信息，则重新插入记录
			$resoucr_id = D('Resource')->increase($res);
			if(!$resoucr_id){
				return json_encode(array('status'=>0,'message'=>'资源同步失败!'));
			}
		} else {
			//更新数据库信息
			$info = array();
			$info['downloadtimes'] = $res['downloadtimes'];
			$info['praisetimes'] = $res['praisetimes'];
			$info['negationtimes'] = $res['negationtimes'];
			$info['praiserate'] =$res['praiserate'];
			$info['score'] =$res['score'];
			$res_update = D('Resource')->updateRes($rid, $info);
		}
		$userdata = D('User')->getUserInfoByLogin($login);
		//保存下载记录
		$res_opr = array();
		$res_opr['resource_id'] = $resoucr_id;
		$res_opr['login_name'] = $login;
		$res_opr['operationtype'] = ResoperationType::COLLECTION;
		$res_opr['dateline'] = time();
		$result = D('ResourceOperation')->saveOrUpdate($res_opr);
		switch($result){
			case 0:
				return json_encode(array('status'=>0,'message'=>'资源收藏失败!'));
			case 1:
				return json_encode(array('status'=>1,'message'=>'资源收藏成功!'));
			case -1:
				return json_encode(array('status'=>0,'message'=>'您已经收藏过该资源!'));
			default:
				return json_encode(array('status'=>0,'message'=>'资源收藏失败!'));
		}
	}
	
	/**
	 * 更新好评、差评、下载量和好评率
	 * @param string $resid
	 */
	public function updateResourceInfo($resid ){
		$rid = $resid;
		if(!$rid ){
			return 0;
		}
		$res=$this->initResinfoByGateinfo($rid);
		$reslocal = D('Resource')->fetchByRid($rid);
		if(!$reslocal){
			if(!(D('Resource')->increase($res)>0)){
				return 0;
			}
		}else {
			$info = array();
			$info['downloadtimes'] = $res['downloadtimes'];
			$info['praisetimes'] = $res['praisetimes'];
			$info['negationtimes'] = $res['negationtimes'];
			$info['praiserate'] =$res['praiserate'];
			$info['score'] =$res['score'];
			//修改数据库信息
			if(!(D('Resource')->updateRes($rid, $info))){
				return 0;
			}
		}
	}
	
	/**************************************************************
	 * 上传资源
	* $rid  	资源id
	* $login 上传者登录名
	*
	* 成功返回资源id，失败返回'-1'
	* yangli4
	*
	*************************************************************/
	private  function uploadResource($rid, $login, $restype, $product_id){
		if(!$login || !$rid){
			return false;
		}
		if($this->mid > 0){
			$cyuserdata = $this->cyuserdata;
		}else{
			$cyuserdata = D('CyUser')->getCyUserInfo($login);
		}
        $userInfo = D("User")->getUserInfoByLogin($login);
		if(empty($cyuserdata)){
			return false;
		}
		//构建本地上传记录
		$res = $this->initResinfoByGateinfo($rid);
		if(empty($res) || empty($res['rid'])){
			return false;
		}
		$res['province'] = $cyuserdata['locations']['province']['id'];//省
		$res['city'] = $cyuserdata['locations']['city']['id'];//市
		$res['county'] = $cyuserdata['locations']['district']['id'];//区县
		//所在学校id
		$schools = $cyuserdata['orglist']['school'];
		$school = array_pop($schools);
		$res['school_id'] = $school['id'];
		$res['username'] = $cyuserdata['user']['login'];
		$res['creator'] = $login;
		$res['restype'] = $restype;
		$res['product_id'] = $product_id;
		//在资源表中添加记录
		$result1 = D('Resource')->increase($res);
		if($result1){
			//保存上传记录
			$res_opr = array();
			$res_opr['resource_id'] = $result1;
			$res_opr['operationtype'] = ResoperationType::UPLOAD;
			$res_opr['dateline']=$res['uploaddateline'];
			$res_opr['login_name']=$login;
			//添加资源操作信息（上传、下载、收藏等）
			D('ResourceOperation')->saveOrUpdate($res_opr);
			if($userInfo['uid'] > 0){
				$feed = $this->syncToFeed($userInfo['uid'], $restype, $rid, $res['title']);
			}
		}
		return $result1;
	}
	
	/**
	 * 在资源上传时，发表动态
	 * @param int $uid 用户ID
	 * @param string $restype 资源类型
	 * @param string $rid 资源RID
	 * @return 添加失败返回false，成功返回新的微博ID
	 * @author yxxing
	 */
	private function syncToFeed($uid, $restype, $rid, $fileName){
		switch($restype){
			case '0100' : $type = "teaching_design";break;
			case '0200' : $type = "teaching_class";break;
			case '0300' : $type = "teaching_material";break;
			case '0600' : $type = "teaching_ware";break;
			default : $type = "post";
		}
		$data = array();
		$data['content'] = '';
		$data['body'] = '我上传了资源【'.$fileName.'】';
		$data['source_url'] = "[PREVIEW_SITE_URL]&id=".$rid;
		return D('Feed')->put($uid, $app = 'reslib', $type, $data);
	}
	/**************************************************************
	 * 通过网关资源信息初始化本地资源信息
	* 成功返回本地资源资源信息数组 对象
	* yangli4
	*
	*************************************************************/
	private  function getInitLocalResourceArray($res){
		//上传信息记录到本地
		$reslocal=array();
		if(!$res){
			return $reslocal;
		}
		else{
			$reslocal['rid'] =$res->general->id;
			if(strrpos($res->general->title, '.')>0){
				$reslocal['title'] =substr($res->general->title,0,strrpos($res->general->title, '.'));
			}
			else {
				$reslocal['title'] = $res->general->title;
			}
			if(is_array($res->tags)){
				$reslocal['keywords'] =$res->tags[0];
			}
			$reslocal['description'] =$res->general->description;
			$reslocal['username'] = $res->general->creator;
			$reslocal['creator'] = $res->general->creator;
			$reslocal['size'] = $res->general->length;
			$reslocal['uploaddateline'] = strtotime($res->date->uploadtime);
			$reslocal['suffix'] = strtolower($res->general->extension);
			$reslocal['type1'] = !empty($res->properties->type)?$res->properties->type[0]:'';
			$reslocal['type2'] = GetResType_Level2('.'.strtolower($res->general->extension));
			$reslocal['downloadtimes'] = !empty($res->statistics->downloadcount)?$res->statistics->downloadcount:0;
			$reslocal['praisetimes'] =  !empty($res->statistics->up)?$res->statistics->up:0;
			$reslocal['source'] = $res->general->source;
			//将平分转换为5分制
			$reslocal['score'] = round(($res->statistics->score ? $res->statistics->score : 0)*1.0/20, 1);
			$reslocal['negationtimes'] =  !empty($res->statistics->down)?$res->statistics->down:0;
			$reslocal['praiserate'] = round($res->statistics->reputablerate ? $res->statistics->reputablerate : 0);
			$reslocal['grade'] =  !empty($res->properties->grade)?$res->properties->grade[0]:'';
			$reslocal['subject'] =  !empty($res->properties->subject)?$res->properties->subject[0]:'';
			$reslocal['restype'] =  $reslocal['type1'];
			return $reslocal;
		}
	}
	
	
	
	
	/**************************************************************
	 * 通过网关资源信息初始化本地资源信息
	* @param string $rid,必须提供， 资源id
	* yangli4
	*
	*************************************************************/
	private  function initResinfoByGateinfo($rid){
		$gateresinfos =  $this->_resclient->Res_GetResIndex($rid);
		if($gateresinfos && $gateresinfos->statuscode == 200 && count($gateresinfos->data)>0){
			$data = $gateresinfos->data[0];
	
			$res=$this->getInitLocalResourceArray($data,0);
		}
		return $res;
	}
	
	/***
	 * 获取资源信息
	 */
	public function get_resource($resid){
		$fields = array(
				"id",
				"date",
				"general",
				"lifecycle",
				"properties",
				"statistics",
				"tags"
		);
		
		$gateresinfos=$this->_resclient->Res_GetResIndex($resid,true);
		$data = $gateresinfos->data[0];		
		if($data){
			$data->thumbnail_url=str_replace('THUMBNAIL_SIZE','128_96',$data->thumbnail_url);
		}
		return $gateresinfos;
	}

	/**
	 * 根据用户id获取用户的容量信息
	 * @param string $loginName 用户登录账号
	 * @return array 用户的容量信息
	 */
	public function getCapacityInfoByUid($loginName){
		$result = array();
		
		$res = D('resource_capacity')->where("login_name='$loginName'")->field('used_capacity,total_capacity')->find();
		
		// 判断当前用户在数据库中是否有容量信息的记录
		if($res){
			$result['usedCapacity'] = $res['used_capacity'];
			$result['totalCapacity'] = $res['total_capacity'];
		}else{
			// 如果当前用户在数据库中的没有容量记录就新建一条记录
			$data = array();
			// 用户资源库初始总容量
			$capacity = C('RESLIB_USER_TOTAL_CAPACITY');
			$capacity = intval($capacity)*1024*1024*1024;
			$data['login_name'] = $loginName;
			$data['used_capacity'] = 0;
			$data['total_capacity'] = $capacity;
			D('resource_capacity')->add($data);
				
			$result['usedCapacity'] = 0;
			$result['totalCapacity'] = $capacity;
		}
		return $result;
	}
	
	/**
	 * 用户上传文件成功时，增加已使用容量
	 * @param string $loginName 用户登录账号
	 * @param int $size 上传的文件大小
	 */
	public function addUsedCapacity($loginName,$size){
		
		D('resource_capacity')->setInc('used_capacity',array('login_name'=>$loginName),$size);
	}
	
	/**
	 * 查询资源
	 * 
	 * @param string $login 用户登录名
	 * @param array $condition 查询条件（restype：string/array，keywords：string，optype：int/array，suffix：string/array，resource部分字段）
	 * @param int $pageindex 页码
	 * @param int $pageSize 每页记录数
	 * @param string $orderBy 排序字段（dateline、uploddateline、uploddateline、praiserate、size、downloadtimes）
	 * @param string $sort 排序方式 DESC/ASC
	 * @param array $fields 返回字段 默认返回部分字段
	 * 
	 * @return array 返回结果
	 * 
	 * @author yxxing
	 */
	public function list_resource($login, $condition, $pageindex, $pageSize, $orderBy, $sort, $fields){
		$count = D('ResourceOperation')->getCount($login, $condition);
		$data = D('ResourceOperation')->listResouce($login, $condition, $pageindex, $pageSize, $orderBy, $sort, $fields);
		foreach ($data AS &$resource){
			$url = $this->_resclient->Res_GetResIndex($resource['rid'],true,'128_96');
			$resource['thumbnail_url'] = $url->data[0]->thumbnail_url;
			$resource['dateline'] = date("Y-m-d H:i:s", $resource['dateline']);
		}
		$result['count'] = $count;
		$result['data'] = $data;
		return $result;
	}

	/**
	 * 删除资源信息
	 * @param string $rid,必须提供,资源id
	 * @param int $uid,必须提供,用户id
	 * @param string $opertiontype,必须提供,操作类型
	 *
	 * 成功返回1，失败返回false
	 */
	public function deleteResource($rid, $login, $operationtype){
		if(!$rid || !$login || !$operationtype){
			return false;
		}
		$operationtype = strtolower($operationtype);
	
		switch ($operationtype){
			case 'collection':
				$optype = ResoperationType::COLLECTION;
				break;
			case 'upload':
				$optype = ResoperationType::UPLOAD;
				break;
			case 'download':
				$optype = ResoperationType::DOWNLOAD;
				break;
			case 'deliver':
				$optype = ResoperationType::DELIVER;
				break;
			default:
				return false;
		}
		if($optype == ResoperationType::UPLOAD){
			/**------查询出资源的大小 by xypan 10.9---------*/
			$fields = array("general");
			$resourceInfo=$this->_resclient->Res_GetResources(array("id"=>$rid),$fields);
			$fileSize = $resourceInfo->data[0]->general->length;
			$fileSize = intval($fileSize);
			/*------------------end-------------------*/
				
			$data =array();
			$data['is_del'] = 1;
			$delete_result = D('Resource')->where(array("rid"=>$rid, "creator"=>$login))->save($data);
			//删除网关资源
			$deleteres = $this->_resclient->Res_DeleteResource($rid);
			if($deleteres->statuscode != 200){

				Log::write($login."删除网关资源".$rid."失败！", Log::ERR);

			}
			
			/**判断当前登录用户的资源库容量是否已经有记录,修复使用资源库容量时资源库已有资源的BUG by xypan 10.12**/
			$result = D("ResourceCapacity")->getCapacityInfoByLogin($login);
			if(empty($result))
				return false;
			if($result['usedCapacity'] < $fileSize){
				D('ResourceCapacity')->where("login_name='$login'")->setField('used_capacity',0);
			}else{
				D('ResourceCapacity')->decUsedCapacity($login, $fileSize);
			}
			/*----------end--------------*/
		}
		$resource_id = D('Resource')->where(array("rid"=>$rid))->getField('id');
		$res = D('ResourceOperation')->deleteRes($resource_id, $login, $optype);

		return $res;
	}
	
}
?>
