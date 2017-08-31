<?php

/**
 * 资源库控制器
 * @author yxxing
 * @version 1.0
 */
class IndexAction extends AccessAction {
	
	//初始化
	protected function _initialize() {
		$roleId = $GLOBALS['ts']['roleid'];
		//关闭session写入，避免堵塞
		session_write_close();
		$this->assign("cyuid", $this->cymid);
		$this->assign("login", $GLOBALS['ts']['user']['login']);
	}
	/**
	 * 资源库首页
	 * @return void
	 */
	public function index() {
		
		// 获取操作类型
		$operation = empty($_GET['type']) ? 1 : intval($_GET['type']);

		//默认为1：系统推荐页面；2：用户推荐；3：用户收藏；4：用户下载；5：同步资源。
		$this->pageNum = 1;
		$this->operation = $operation;
		$this->assign("RS_SITE_URL", C('RS_SITE_URL'));
		$this->setTitle( '资源库首页' );
		$this->setKeywords( '资源库首页' );
		$this->display();
	}
	
	/**
	 * 弹出资源上传页面
	 */
	public function showUpload(){
		//资源选择的类型是否限定
		$restype = isset($_REQUEST['restype']) ? $_REQUEST['restype'] : "";
		//资源选择的类型是否限定
		$msgid = isset($_REQUEST['msgid']) ? $_REQUEST['msgid'] : "";

		//资源上传成功后是否刷新当前页面
		$refresh = isset($_REQUEST['refresh']) ? $_REQUEST['refresh'] : "false";
		//是否将资源同步到学科资源
		$sync = isset($_REQUEST['sync']) ? $_REQUEST['sync'] : "false";
		if(empty($restype)){
			$restype_list = getRestype(true);
		} else{
			//指定资源类型展现（多类型以","分割）
			$_restype = getRestype(false);
			$restypeArr = explode(',', $restype);
			foreach ($restypeArr as $value) {
				if(!empty($value)){
					$restype_list[] = $_restype[$value];
				}
			}
			if(empty($restype_list))
				$restype_list = $_restype;
		}
		
		/**判断当前登录用户的资源库容量是否已经有记录 by xypan 10.9**/
		$loginName = $this->user['login'];
		$result = D("Yunpan", "yunpan")->getCapacityInfoByLogin($loginName);
		$this->usedCapacity = $result['usedCapacity'];
		$this->totalCapacity = $result['totalCapacity'];
		$upload_config = include(CONF_PATH.'/upload.inc.php');
		$file_extensions = implode(';',$upload_config['previewable_exts']);
		/*-------------------end-----------------------------*/
		$this->assign("res_list", $restype_list);
		$this->assign("refresh", $refresh);
		$this->assign("sync", $sync);
		$this->assign("_session_id", session_id());
		$this->assign("msgid", $msgid);
		$this->assign("file_extensions", $file_extensions);
		$this->display();
	}
	
	/**
	 * 评课资源上传页面
	 */
	public function pingkeUpload(){
		$this->assign("_session_id", session_id());
		$this->display();
	}
	
	
	/**
	 * 弹出资源上传页面
	 */
	public function upload(){
		//资源选择的类型是否限定
		$restype = isset($_REQUEST['restype']) ? $_REQUEST['restype'] : "";
		//资源上传成功后是否刷新当前页面
		$refresh = isset($_REQUEST['refresh']) ? $_REQUEST['refresh'] : "false";
		//是否将资源同步到学科资源
		$sync = isset($_REQUEST['sync']) ? $_REQUEST['sync'] : "false";
	
		if(isset($_REQUEST['sync'])){
			$roleEnNames  = getSubByKey($GLOBALS['ts']['cyuserdata']['rolelist'],'name');
			in_array(UserRoleTypeModel::TEACHER,$roleEnNames) && $roleEnName = UserRoleTypeModel::TEACHER;
			$this->checked = $roleEnName === UserRoleTypeModel::TEACHER ? 1 : 0;
		}
		
		if(empty($restype)){
			$restype_list = getRestype(true);
		} else{
			$_restype = getRestype(false);
			$restype_list[] = $_restype[$restype];
			if(empty($restype_list))
				$restype_list = $_restype;
		}
	
		/**判断当前登录用户的资源库容量是否已经有记录 by xypan 10.9**/
		$loginName = $this->user['login'];
		$result = D("Yunpan", "yunpan")->getCapacityInfoByLogin($loginName);
		$this->usedCapacity = $result['usedCapacity'];
		$this->totalCapacity = $result['totalCapacity'];
		/*-------------------end-----------------------------*/
		$this->assign("res_list", $restype_list);
		$this->assign("refresh", $refresh);
		$this->assign("sync", "true");
		$this->assign("_session_id", session_id());
		$this->display();
	}
	
	/**
	 * 弹出资源修改页面
	 */
	public function showUpdate(){
		
		$rid = isset($_REQUEST["rid"]) ? $_REQUEST["rid"] : "";
		
		$restype_list = getRestype(true);
		$resource = D("Resource")->where(array("rid"=>$rid))->field(array("rid","description","title","restype"))->find();
		
		$this->assign("res_list", $restype_list);
		$this->assign("rid", $resource['rid']);
		$this->assign("type", $resource['restype']);
		$this->assign("description", $resource['description']);
		$this->assign("title", $resource['title']);
		$this->display();
	}

    /**
     * 资源预览页面，根据资源类型，跳往资源平台的不同栏目预览
     */
    public function detail(){
        $rid = isset($_REQUEST["rid"]) ? $_REQUEST["rid"] : "";
        if(empty($rid)){
            $this->error("资源预览出错！");
        }
        $params = array('id'=>$rid);
        $fields = array(
            "rrtlevel1","rrtlevel2"
        );
        $client = D('CyCore')->Resource;
        $resourceInfo = $client->Res_GetResources($rid, $fields);
        $app = C('RESLIB_VIEW_APP');
        if(empty($app)) {
            $app = 'resource';
        }else{
            $app = strtolower($app);
        }
        $mod = 'Rescenter';
        if($resourceInfo->statuscode == 200){
            $resource = $resourceInfo->data[0]->properties->rrtlevel1;
            if(in_array('09', $resource)){
                $mod = 'Videocenter';
                $act = 'play';
            }elseif(in_array('10', $resource)){
                $mod = 'Specialclass';
            }elseif(in_array('11', $resource)){
                $mod = 'Resmarket';
            }elseif(in_array('12', $resource)){
                $rrtLevel2 = $resourceInfo->data[0]->properties->rrtlevel2;
                is_array($rrtLevel2) && $rrtLevel2 = $rrtLevel2[0];
                $mod = 'Homepage';
                $params['level2'] = $rrtLevel2;
            }
        }
        $url = Ures('/'.$mod.'/',$params );
        header("Location: ".$url);
    }
	
}
