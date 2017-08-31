<?php
import(ADDON_PATH . '/library/common/resourceUtil.php');
import(ADDON_PATH . '/library/common/spaceUtil.php');

/**
 * 教学资源管理Action
 * @package msgroup\lib\Action\TeaResManageAction
 * @author sjzhao
 *
 */
class ResourceAction extends BaseAction{

	private $resClient;

    /**
     * 初始化
     */
	public function _initialize() {
		parent::_initialize();

		// 权限判断
		if ($this->level == 0) {
			$this->error("当前用户没有权限");
		}

		$this->resClient = D('CyCore')->Resource;
	}

	/**
	 * 教学资源管理首页
	 */
	public function index(){
		$this->display();
	}


	/**
	 * @api 编辑名师工作室资源
	 * @author yangli4	
	 * 
	 * @return string  {"status":1,"msg":"删除成功！"}:1,成功；0失败  	  
	 */
	public function editMSGroupRes(){	
		$rid =  isset($_REQUEST['rid'])?$_REQUEST['rid'] : "";
		if(!$rid){
			exit('{"status":0,"msg":"传入参数错误，rid为空！"}');
		}	
		$res_updatable = D('Resource','reslib')->where(array("rid"=>$rid))->find();
		if(!$res_updatable){
			exit('{"status":0,"msg":"该资源不属于您，无权修改！"}');
		}

		//获取资源修改信息
		$info = getRequestResParams();
		//修改网关信息
		$updateres = $this->resClient->Res_UpdateResource($rid, $info);
		if($updateres->statuscode != 200){
			//网关信息修改失败
			echo '{"status":0,"msg":"网关更新失败！"}';
			Log::write($this->login."更新网关资源失败:".$rid.",原因为：".json_encode($updateres->data), Log::ERR);
		}else{
			$res = initResinfoByGateinfo($rid);
			//修改数据库信息
			$update_result = D('Resource',"reslib")->where(array("rid"=>$rid))->save($res);
			if($update_result || $update_result !== false){
				echo '{"status":1,"msg":"资源更新成功！"}';
			}else{
				echo '{"status":0,"msg":"资源数据库更新失败！"}';
			}
		}
	}


	/**
	* @api 查询名师工作室资源
	* @author yangli4
	*
	* @param string $groupID   工作室id
	* @param string $resTitle  资源名称
	* @param string $resType   资源类型编号
	* @param string $orderby   排序字段
	* @param string $sort      排序方向
	* @param int    $start     起始位置
	* @param int    $limit     获取资源数量
	*
	* @return array 返回mysql数据库资源记录数组
	*/
	public function getMSGroupRes($groupID,$resTitle,$resType,$orderby = 'dateline', $sort = 'desc', $start = 0, $limit = 10 ){
		$conditions = array(
			'operationtype' => ResoperationType::MSGROUP_UPLOAD, 
			'keywords'=>$resTitle,
			'restype'=>$resType
			);
		return D('ResourceOperation','reslib')->getMSGroupRes($groupID,$conditions,$orderby, $sort, $start, $limit);
	}

	/**
	* @api 查询名师工作室资源
	* @author yangli4
	*
	* @param string $groupID   工作室id
	* @param string $resTitle  资源名称
	* @param string $resType   资源类型编号
	* @param string $orderby   排序字段
	* @param string $sort      排序方向
	* @param int    $pageSize  每页显示数量
	* @return array 返回mysql数据库资源记录数组
	*/
	public function getPageMSGroupRes($groupID,$resTitle,$resType,$orderby = 'dateline', $sort = 'desc', $pageSize = 10 ){
		$conditions = array(
			'operationtype' => ResoperationType::MSGROUP_UPLOAD, 
			'keywords'=>$resTitle,
			'restype'=>$resType
			);
		return D('ResourceOperation','reslib')->getPageMSGroupRes($groupID,$conditions,$orderby, $sort, $pageSize);
	}

	/**
	* @api 查询名师工作室资源数量
	* @author yangli4
	*
	* @param string $groupID   工作室id
	* @param string $resTitle  资源名称
	* @param string $resType   资源类型编号
	* @param string $orderby   排序字段
	* @param string $sort      排序方向
	* @param int    $start     起始位置
	* @param int    $limit     获取资源数量
	*
	* @return array 返回mysql数据库资源记录数组
	*/
	public function getMSGroupResCount($groupID,$resTitle,$resType){
		$conditions = array(
			'operationtype' => ResoperationType::MSGROUP_UPLOAD, 
			'keywords'=>$resTitle,
			'restype'=>$resType
			);
		return D('ResourceOperation','reslib')->getMSGroupResCount($groupID,$conditions);
	}

	/**
	 * @api 删除名师工作室资源
	 * @author yangli4	
	 * 
	 * @return string  {"status":1,"msg":"删除成功！"}:1,成功；0失败   
	 */
	public function delMSGroupRes(){		
		//资源id数组
		$resids = isset($_REQUEST['resids']) ? $_REQUEST['resids'] : "";
		if(!is_array($resids)){
			$resids = array($resids);
		}
		if(empty($resids)){
			exit('{"status":0,"msg":"传入参数错误！"}');
		}
		foreach ($resids as $value){
			$result = $this->_deleteResource($value);
			if(!$result){
				exit('{"status":0,"msg":"删除‘'.$value.'’失败！","data":"'.$result.'"}');
			}
		}
		exit('{"status":1,"msg":"删除成功！"}');
	}

	/**
	 * 从应用服务器删除资源信息
	 * @author yangli4
	 *
	 * @param string $rid          必须提供,资源id
	 *
	 * 成功返回1，失败返回false
	 */
	private function _deleteResource($rid){
		$data =array();
		$data['is_del'] = 1;
		$delete_result = D('Resource','reslib')->where(array("rid"=>$rid))->save($data);
		//删除网关资源
		$deleteres = $this->resClient->Res_DeleteResource($rid);
		if($deleteres->statuscode != 200){
			Log::write($login."删除网关资源".$rid."失败！", Log::ERR);
		}

		$resourceresult = D('Resource','reslib')->field(array('id','creator'))->where(array("rid"=>$rid))->select();//获取资源id与资源用户的cyuid
		$resourceresult = $resourceresult[0];
		$res = D('ResourceOperation','reslib')->deleteRes($resourceresult['id'],$resourceresult['creator'],ResoperationType::MSGROUP_UPLOAD);
		return $res;
	}
	/**
	 * 根据条件获取资源列表
	 * 
	 * @author sjzhao
	 * @param 
	 */
	public function getResList(){
		$login = $this->user['login'];
		if(!$login){
			exit('{"status":0,"msg":"非法操作"}');
		}
		$gid = $_REQUEST['gid'];
		$sort_type = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "";
		$pageindex =  isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
		$restype = isset($_REQUEST['restype']) ? $_REQUEST['restype'] : '';
		$keywords = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : '';
		$pagesize = 10;
		$result = $this->getPageMSGroupRes($gid, $keywords, $restype);
		$this->results = $result['data'];
		//ajax分页
		$p = new AjaxPage(array('total_rows'=>$result['totalRows'],
				'method'=>'ajax',
				'ajax_func_name'=>'Resource.requestData',
				'now_page'=>$pageindex,
				'list_rows'=>$pagesize
		));
		$this->pageindex = $pageindex;
		$page = $p->show ();
		$this->page =$page;
		$this->display();
	}
	/**
	 * 编辑资源
	 */
	public function showUpdate(){
		$rid = isset($_REQUEST["rid"]) ? $_REQUEST["rid"] : "";
		
		$restype_list = getRestype_ms(true);
		$resource = D("Resource","reslib")->where(array("rid"=>$rid))->field(array("rid","description","title","restype"))->find();
		
		$this->assign("res_list", $restype_list);
		$this->assign("rid", $resource['rid']);
		$this->assign("type", $resource['restype']);
		$this->assign("description", $resource['description']);
		$this->assign("title", $resource['title']);
		$this->display();
	}

}
?>
