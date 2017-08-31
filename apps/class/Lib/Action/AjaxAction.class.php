<?php
class AjaxAction extends Action{
	/**
	 * 百宝箱文件上传
	 * @author sjzhao
	 */
	public function upload(){
		if (!empty($_FILES)) {
			$file = $_FILES['Filedata']['tmp_name'];
			$title = $_FILES['Filedata']['name'];
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			$creator = $_REQUEST['creator'];
			$uid = $_REQUEST['uid'];
			$keywords = $_REQUEST['keywords'];
			$description = $_REQUEST['description'];
			$restype = $_REQUEST['restype'];
			$targetFolder = './data/treasurebox';
			if(!is_dir($targetFolder)){  //如果不存在该文件夹
				mkdir($targetFolder, 0777);  //创建文件夹
				chmod($targetFolder, 0777);  //改变文件模式
			}
			$targetPath = $targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/' . md5($fileParts['filename']) . '.' . $fileParts['extension'];
	
			$des = $targetFile;
			if(file_exists($des)){
				@unlink($des);
			}
			$result=move_uploaded_file($file, $des);
			if($result){
				$resinfo=array();
				$resinfo['rid']=md5($fileParts['filename']);
				$resinfo['title']=$title;
				$resinfo['keywords']=$keywords;
				$resinfo['uid']=$uid;
				$resinfo['uname']=$creator;
				$resinfo['uploaddateline']=time();
				$resinfo['suffix']=$fileParts['extension'];
				$resinfo['downloadtimes']=0;
				$resinfo['restype']=$restype;
				$resinfo['description']=$description;
				$results=$this->saveUploadFile($resinfo,1);
			}
		}
	}
	/**
	 * 保存百宝箱上传文件信息
	 * @param array $info 文件信息数组
	 * @author sjzhao
	 * @return 上传文件的id
	 */
	public function saveUploadFile($info,$type){
		switch ($type){
			case 1:
				$result=M('Treasurebox')->saveFileInfo($info);
				return $result;
				break;
			case 2:
				$result=M('Treasurebox2')->saveFileInfo($info);
				return $result;
				break;
		}
		
			
	}
	/**
	 * 根据用户uid获取资源列表
	 * @param int $uid 用户id
	 * @author sjzhao
	 * @return 用户的资源列表
	 */
	public function getResList(){
		$cid=$_REQUEST['cid'];
		$pageindex =  isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
		$pagesize=1;
		$result=M('Treasurebox')->getResByUid($cid,$pageindex,$pagesize);
		$p = new AjaxPage(array('total_rows'=>$result['count'],
				'method'=>'ajax',
				'parameter'=>$cid,
				'ajax_func_name'=>'treasurebox.init',
				'now_page'=>$pageindex,
				'list_rows'=>$pagesize));
		$page = $p->show();
		$this->page =$page;
		$this->pageNum = $pageindex;
		$this->data=$result['data'];
		$this->display('getResList');
	}
	/**
	 * 保存百宝箱附件信息
	 */
	public function saveTreasureboxAttachInfo(){
		$attachids=$_POST['attachids'];
		$cid=$_POST['classid'];
		$resinfo=array();
		$resinfo['cid']=$cid;
		$resinfo['attachids']=$attachids;
		$results=$this->saveUploadFile($resinfo,2);
	}
	/**
	 * 根据班级cid获取班级资源
	 * @author sjzhao
	 * @return 班级资源
	 */
	public function getClassAttach(){
		$cid=$_REQUEST['cid'];
		$pageindex =  isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
		$pagesize=6;
		$map=array();
		$map['cid']=$cid;
		$order='ctime DESC';
		$result=M('Attach')->getAttachList($map,'',$order,$pagesize);
		$resnum=M('Attach')->getAttachNumByCid($cid);//资源数量
		$p = new AjaxPage(array('total_rows'=>$result['count'],
				'method'=>'ajax',
				'parameter'=>$cid,
				'ajax_func_name'=>'treasurebox.init',
				'now_page'=>$pageindex,
				'list_rows'=>$pagesize));
		$page = $p->show();
		$this->page =$page;
		$this->data=$result['data'];
		$this->resnum=$resnum;
		$this->display('getResList');
	}
	/**
	 * 更新下载次数
	 */
	public function UpdateDownloadTimes(){
		$aid=$_REQUEST['aid'];
		return model('Attach')->UpdatedownloadtimesById($aid);
	}
	/**
	 * 获取通知、作业、课程表
	 */
	public function getHNT(){//$type 1代表作业、2代表通知、3、代表课程表
		$num  = $_REQUEST['num']?$_REQUEST['num']:5;
		$cid  = 825;//暂时写死
		$type = $_REQUEST['type'];
		$result=array();
		switch($type){
			case 1:
				$homework=model('ClassHomework')->getHomeworkByCid($cid,$num);//作业
				foreach($homework as $key=>$value){
					$result['data'][$key]=$value;
				}
				echo json_encode($result);
				break;
			case 2:
				$notice=array(array('content'=>'下午一点在会议室开会'),array('content'=>'定于2日召开家长会'),array('content'=>'定于3日下午举行颁奖典礼'),array('content'=>'定于7日举行军训'),array('content'=>'定于下周末去巴厘岛旅游'));
				foreach ($notice as $key=>$value){
					$result['data'][$key]=$value;
				}
				echo json_encode($result);
				break;
		}
	}
	/**
	 * 获取班级作业
	 * @author sjzhao
	 */
	public function getClassHomework(){
		$cid=$_REQUEST['cid'];
		$pagesize=6;
		$result=model('ClassHomework')->getHomeworkByCid($cid,$pagesize);
		$p = new AjaxPage(array('total_rows'=>$result['count'],
				'method'=>'ajax',
				'parameter'=>$cid,
				'ajax_func_name'=>'Homework.init',
 				'now_page'=>$result['nowPage'],
				'list_rows'=>$pagesize));
		$page = $p->show ();
		$this->page =$page;
		$this->data=$result['data'];
		$this->display('getHomework');
	}
	
	
	/***
	 * 学校资料修改
	 */
	public function doSaveCampusProfile(){
		$_orgMaps = $_REQUEST['orginfo'];
		$orgMaps['fid'] = $_orgMaps['orgid'];
		$orgMaps['cname'] = $_orgMaps['orgname'];
		$orgMaps['contact'] = $_orgMaps['contact'];
		$orgMaps['phone'] = $_orgMaps['phone'];
		$orgMaps['intro'] = $_orgMaps['intro'];
		$orgMaps['uid'] =$this->mid;
		$orgMaps['type'] =1;
		$res = D('OrgInfo')->updata_organizations($orgMaps);
		if($res){
			$info = '资料修改成功';
			$status = 1;
		}else{
			$info = '资料修改失败';
			$status = 0;
		}
		$this->ajaxReturn('',$info,$status);
	}
	
	
	/***
	 * 班级 资料修改
	 */
	public function doSaveClassProfile(){
		$_orgMaps = $_REQUEST['orginfo'];
		$orgMaps['fid'] = $_orgMaps['orgid'];
		$orgMaps['cname'] = $_orgMaps['cname'];
		$orgMaps['intro'] = $_orgMaps['intro'];
		$orgMaps['uid'] =$this->mid;
		$orgMaps['type'] =2;
		$res = D('OrgInfo')->updata_organizations($orgMaps);
		if($res){
			$info = '资料修改成功';
			$status = 1;
		}else{
			$info = '资料修改失败';
			$status = 0;
		}
		$this->ajaxReturn('',$info,$status);
	}
	/***
	 * 课程表修改
	 */
	public function doSaveSchedule(){
		$_schMaps=$_REQUEST['schinfo'];
		$schMaps['cid']=$_REQUEST['cid'];
		$schMaps['course']=json_encode($_schMaps);
		$schMaps['id']=$_REQUEST['id'];
		$res = D('ClassSchedule')->updata_classSchedule($schMaps);
		if($res){
			$return = '课表修改成功';
		}else{
			$return = '课表修改失败';
		}
		exit($return);
	}

	/*
	 * 获取相册列表
	 * $type 0:上翻  1:下翻
	*/
	public function getAlbum(){
		$type =$_REQUEST['type'];
		$cid =$_REQUEST['cid'];
		$start =$_REQUEST['start'];
		$map['classId'] = $cid;
		$map['isDel'] = 0;
		if($type==1){
			$photo_data = D('Album', 'photo')->where($map)->order("mTime DESC")->limit("$start,3")->select();
			$photo_data[2]['name']=getShort($photo_data[2]['name'],12);
			$photo_data[2]['url']=get_classalbum_cover($photo_data[2]['id'],"",178,132);
			$photo_data[2]['mTime']=date('m月d日 H:i',$photo_data[2]['mTime']);
			exit(json_encode($photo_data[2]));
		}
		else if($type==0){
			$photo_data = D('Album', 'photo')->where($map)->order("mTime DESC")->limit("$start,3")->select();
			$photo_data[0]['name']=getShort($photo_data[0]['name'],12);
			$photo_data[0]['url']=get_classalbum_cover($photo_data[0]['id'],"",178,132);
			$photo_data[0]['mTime']=date('m月d日 H:i',$photo_data[0]['mTime']);
			exit(json_encode($photo_data[0]));
		}
	}
	
	/**
	 * @since 2014/3/31
	 * @author chengcheng3
	 */
	public function getClassBySchool(){
		$schoolId  = isset($_REQUEST['schoolId']) ? trim($_REQUEST['schoolId']): '';
		$gradeCode = isset($_REQUEST['gradeCode']) ? trim($_REQUEST['gradeCode']): '';
		$return = array();
		if(empty($schoolId)){
			$return['info'] = '学校信息不存在！';
			$return['status'] = 0;
			exit(json_encode($return));
		}
		if(empty($gradeCode)){
			$classes = D('CyClass')->list_class_by_school($schoolId,0,1000);
		}else{
			$classes = D('CyClass')->list_class_by_school_grade($schoolId,$gradeCode,0,1000);
		}
		$return['info'] = '成功获取班级信息';
		$return['status'] = 1;
		$return['data'] = $classes;
		exit(json_encode($return));
	}
	
}

?>