<?php

class IndexAction extends Action {
	
	private $_resclient = null;
	
	/**
	 * _initialize 模块初始化
	 *
	 * @return void
	 */
	protected function _initialize() {
		$this->_resclient = D('CyCore')->Resource;
	}
	
	/**
	 * 资源预览首页
	 */
	public function index(){		
		$resid = $_REQUEST['id'];
		$appCode = $_REQUEST['appcode'];
		$uid = !empty($_GET['uid'])?$_GET['uid']:$this->mid;
		
		if (empty($resid)) {
			$this->error("当前资源不存在！");
		} else {
			$obj = $this->_resclient->Res_GetResIndex($resid);
			$resourceInfo = $obj->data[0];
			if (empty($resourceInfo) || $resourceInfo->lifecycle->curstatus != "1") {
				$this->error("当前资源不存在或未被审核！");
			}

			//非本人预览，浏览量+1
			if ($uid != $this->mid) {
				$viewcount = $resourceInfo->statistics->viewcount;
				$viewcount = empty($viewcount) ? 0 : $viewcount;
				$viewcount = intval($viewcount) + 1;
				$this->_resclient->Res_UpdateStatistics($resid, 'viewcount', $viewcount);
			}
		}

		$resdetail = $resourceInfo->general;
		$date = $resourceInfo->date;
		$uploadtime = new DateTime($date->uploadtime);
		$date->uploadtime = date('Y-m-d H:i:s', strtotime($date->uploadtime));
	
		$audio = array("mp3","wma","wav","ogg","ape","mid","midi");
		$extension =$resdetail->extension;
		$preview_url = in_array($extension, $audio)?$resourceInfo->file_url:$resourceInfo->preview_url;
		
		
		$realTitle = $resdetail->title;
		$pathinfo = pathinfo($realTitle);
		if(strtolower($pathinfo['extension'])!=$extension){
			$realTitle = $realTitle.'.'.$extension;
		}

		$app= $this->getAppByCode($appCode,$uid);
		$this->assign("appname",$app['appname']);
		$this->assign("appurl",$app['url']);		
		$r = D("Resource", "reslib")->where("rid='".$resourceInfo->general->id."'")->find();
		$this->assign("ts_resource_id",$r['id']);
		
		$this->extension = $extension;
		$this->assign("resourceInfo",$resourceInfo);
		$this->assign("realTitle",$realTitle);
		$this->assign("preview_url",$preview_url);
		$this->assign("isUploadLimit",false);

		$uid = !empty($_GET['uid'])?$_GET['uid']:$this->mid;
		$this->assign("uid",$uid);
		$this->assign("mid",$this->mid);
		$this->assign("cyuid",$this->cymid);
		
		$this->display();
	}
	
	private function getAppByCode($appCode,$uid){
		$app=array();
		switch ($appCode){
			case '0100':
				$app['appname']='教学设计';
				$app['url']='index.php?app=teachingapp&mod=TeachingDesign&act=index&uid='.$uid;
				break;
			case '0200':
				$app['appname']='教学视频';
				$app['url']='index.php?app=teachingapp&mod=TeachingClass&act=index&uid='.$uid;
				break;
			case '0300':
				$app['appname']='媒体素材';
				$app['url']='index.php?app=teachingapp&mod=TeachingMaterial&act=index&uid='.$uid;
				break;
			case '0600':
				$app['appname']='教学课件';
				$app['url']='index.php?app=teachingapp&mod=TeachingWare&act=index&uid='.$uid;
				break;
		}
		
		return $app;
	}
	
	/**
	 * 获取隐私设置弹框信息
	 */
	public function privacysettings(){
		$this->display();
	}
	
	/**
	 * 提交隐私设置
	 */
	public function submitPrivacySet(){
		$resid = $_REQUEST['resid'];
		$privacycode = $_REQUEST['privacy'];
		
		echo $resid."-".$privacycode;
	}
}
