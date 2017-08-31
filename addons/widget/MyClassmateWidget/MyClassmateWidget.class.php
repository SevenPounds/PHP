<?php
/**
 * 我的同学Widget
 * @author qunli
 */
class MyClassmateWidget extends Widget {

	/**
	 * 渲染我的同学页面
	 * @param array $data 配置相关数据
	 * @return string 渲染页面的HTML
	 */
	public function render($data) {
		$var = $data;
		// 显示相关人数
		$var['classmate_limit'] = isset($data['classmate_limit']) ? intval($data['classmate_limit']) : 5;
		$var['teachers'] = $this->_getMyTeacher();
		$var['role'] = $this->roleEnName;
		$content = $this->renderFile(dirname(__FILE__)."/myClassmate.html", $var);
		return $content;
	}

	
	/**
	 * 获取我的同学相关数据
	 * @return array 显示所需数据
	 */
	private function _getMyTeacher() {
		
		$cyClient = new CyClient();
		$cyuid = $this->user['cyuid'];

		// 如果是家长，则获取孩子的学校和班级
		if($this->roleEnName == 'parent'){
			$children = $cyClient->listChildren($cyuid);
			$cyuid = $children[0]->id;
		}
		
		// 获取班级信息
		$classes = $cyClient->listClassByUser($cyuid);
		if($classes){
			$classId = $classes[0]->id;
		}
		if(!empty($classId)){
			//班级学生列表
			$studentList = $cyClient->listUserByClass($classId, "student", 0, 1000);
			//班级用户cyuid
			$cyuids=array();
			//去除自己
			foreach ($studentList as $tea){
				if($cyuid !=$tea->id){
				 $cyuids[]=$tea->id;
				}
			}
			//存在学生
			if(!empty($cyuids)){
				$userModel = model('User');
				//获取学生的uid列表
				$uids = $userModel->getUidsByCyuids($cyuids);
				//获取学生信息
				$studentInfos = $userModel->getUserInfoByUids($uids);
				//获取关注状态
// 				$userStates= model('Follow')->getFollowStateByFids($this->uid,$uids);
// 				foreach ($studentInfos as $key=>$stu){
// 					$sta=$studentInfos[$key]['uid'];
// 					$studentInfos[$key]['followState']=$userStates[$sta];
// 				}
				
				}
				//dump($studentInfos);
				//dump($teacherInfos);
			}
			
		return empty($studentInfos)?null:$studentInfos;
	}
	
}