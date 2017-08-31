<?php
class IndexAction extends AccessAction {
	private $diskClient = null;
	
	
	function __construct() {
		parent::__construct ();
		if(D('User')->where("uid={$this->uid} AND is_Del=0")->count()==0){
			$this->assign('jumpUrl', U('public/Index/index'));
			$this->error('用户不存在或已被删除！');
		}
		$this->diskClient = IflytekdiskClient::getInstance ();
	}
	
	
	/**
	 * 资源展示页
	 */
	public function index() {
		
		$uid = ! empty ( $_GET ['uid'] ) ? $_GET ['uid'] : $this->mid;
		$current_page = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : 1;
		$this->displayIndex1 ($uid ,$current_page);
		//$data = $this->displayIndex ($this,$uid ,$current_page);
		//$this->assign ( "dataInfo", $data );
		$this->display ();
	}
	
	/**
	 * 获取分页控件
	 *
	 * @param int $rescount
	 *        	资源数量
	 * @param int $pagesize
	 *        	每页显示数量
	 *        	
	 * @return string $page 返回分页控件的html
	 */
	function getPager($rescount, $pagesize = 16) {
		$p = new Page ( $rescount, $pagesize );
		$p->setConfig ( 'prev', "上一页" );
		$p->setConfig ( 'next', '下一页' );
		$page = $p->show ();
		return $page;
	}

	function displayIndex1($uid ,$current_page ,$pagesize = 16){
		if (empty ( $uid )) {
			$userInfo = $GLOBALS ['ts'] ['user'];
			$cyuser = $this->cymid;
		} else {
			$userInfo = $GLOBALS ['ts'] ['_user'];
			$cyuser = $this->cyuid;
		}		
		$params['method'] = 'pan.file.share.list';
    	$params['uid'] = $cyuser;
    	$params['to'] = 'homepage';
    	$params['page'] = $current_page;
    	$params['limit'] = $pagesize;
    	$obj = Restful::sendGetRequest($params);
    	$total = $obj->total;
		$list = $obj->data;
		$tmp = array();
		foreach ($list as $key => $value) {
			$resourceinfo = json_decode($value->resourceInfo);
			$tmp[$key]['resName'] = $resourceinfo->resName;
			$tmp[$key]['size'] = $resourceinfo->size;
			$tmp[$key]['extension'] = $resourceinfo->extension;
			$tmp[$key]['id'] = $resourceinfo->id;
			$tmp[$key]['downloadpath'] = $resourceinfo->downloadpath;
			$tmp[$key]['shareId'] = $value->id;
			$tmp[$key]['sharetime'] = $value->sharetime;
			$tmp[$key]['uid'] = $value->uid;
		}
		$page = $this->getPager ( $total, $pagesize );
		$this->assign ( "page", $page );
		$this->assign('dataInfo',$tmp);
		$this->assign ( 'userInfo', $userInfo );
		$this->assign ( "totalrecords", $total);

	}
	
	/**
	 * 模板展示
	 *
	 * @param object $obj
	 *        	调用时一定要使用$this
	 */
	function displayIndex($obj, $uid, $current_page, $pagesize = 16) {
		if (empty ( $uid )) {
			$userInfo = $GLOBALS ['ts'] ['user'];
			$cyuser = $this->cymid;
		} else {
			$userInfo = $GLOBALS ['ts'] ['_user'];
			$cyuser = $this->cyuid;
		}
		$map ['login_name'] = $userInfo ['login'];
		$map ['open_position'] = 1;
		$map ['is_del'] = 0;
		$start = ($current_page - 1) * $pagesize;
		$result ['fid'] = D ( 'YunpanPublish', 'yunpan' )->where ( $map )->limit ( "$start,$pagesize" )->order ( "dateline DESC" )->findAll ();
		$data = array ();
		foreach ( $result ['fid'] as $key ) {
			$rs = $this->diskClient->getFile ( $cyuser, $key ['fid'] );
			$d_res = null;
			if (! $rs->hasError) {
				$d_res ["extension"] = $rs->obj->fileInfoVal->extension;
				$d_res ["name"] = $rs->obj->fileInfoVal->aliasname;
				$d_res ["dateline"] = $key ['dateline'];
				$d_res ['uid'] = $uid;
				$d_res ['fid'] = $rs->obj->fileInfoVal->fid;
				$d_res ["length"] = $rs->obj->fileInfoVal->length;
			    $fileExtension = explode('.',$d_res ["name"]);
			    if(!($fileExtension[count($fileExtension)-1]==$d_res ["extension"])) {
				  $d_res ["name"]=$d_res ["name"].'.'.$d_res ["extension"] ;
			    }
				$download =  D('YunpanFile','yunpan')->getFileUrl ( $cyuser, $key ['fid']);
				$d_res ['downloadurl'] = $download['data']->obj->strVal;
			} else {
				$dirs = $this->diskClient->getDir ( $cyuser, $key ['fid']);
				if ($dirs->hasError) {
					$file = D ( "CyCore" )->Resource->Res_GetResIndex ( $key ['fid'] ); // "8006628c91814fef85227601902ce8e6"
					$d_res ['fid'] = $key ['fid'];
					$d_res ["extension"] = "";
					$d_res ["name"] = $file->data [0]->general->name;
					if(empty($d_res ["name"])){
						$d_res ["name"] =$key ['res_title'];
					}
					$d_res ["dateline"] = $key ['dateline'];
					$d_res ['uid'] = $uid;
					if ($file->statuscode == 200 || $file->statuscode == "200") {
						$d_res ["resstatus"] = $file->data [0]->lifecycle->auditstatus;
						$d_res ["extension"] = $file->data [0]->general->extension;
						$d_res ["length"] = $file->data [0]->general->length;
						$d_res ['downloadurl'] = $file->data [0]->file_url;
					} else {
						$d_res ["length"] = 0;
					}
					
					if (! empty ( $d_res ["extension"] )) {
						$d_res ["name"] = $d_res ["name"] . $d_res ["extension"];
					}
				}else{
					$d_res ['fid'] = $key ['fid'];
					$d_res ["extension"] = "zip";
					$d_res ["name"] = $key ['res_title'] ;
					$d_res ["dateline"] = $key ['dateline'];
					$d_res ['uid'] = $uid;
					$d_res ["length"] = 0;
				}
			}
			array_push ( $data, $d_res );
		}
		$result ['totalrecords'] = D ( 'YunpanPublish', 'yunpan' )->getlistPublishCount ( $map ['login_name'], 1 );
		
		$page = $this->getPager ( $result ['totalrecords'], $pagesize );
		// 资源总数
		$obj->assign ( "totalrecords", $result ['totalrecords'] );
		$obj->assign ( "page", $page );
		$obj->assign ( 'userInfo', $userInfo );
		
		return $data;
	}
	
	/**
	 * 获取二维码
	 *
	 * @return [type] [description]
	 */
	public function qrcode() {
		$dowloadurl = $_REQUEST ['url'];
		echo getQRcode ( $dowloadurl );
	}
}