<?php
/**
 * 我的老师Widget
 * @author qunli
 */
class MyTeacherWidget extends Widget {

	/**
	 * 渲染我的老师页面
	 * @param array $data 配置相关数据
	 * @return string 渲染页面的HTML
	 */
	public function render($data) {
		$var = $data;
		// 显示相关人数
		$var['teacher_limit'] = isset($data['teacher_limit']) ? intval($data['teacher_limit']) : 5;
		$var['teachers'] = $this->_getMyTeacher();
		$var['role'] = $this->roleEnName;
		$content = $this->renderFile(dirname(__FILE__)."/myTeacher.html", $var);
		return $content;
	}

	
	/**
	 * 获取我的老师相关数据
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
			//班级老师列表
			$teacherList = $cyClient->listUserByClass($classId, "teacher", 0, 1000);
			//班级用户cyuid
			$cyuids=array();
			foreach ($teacherList as $tea){
				$cyuids[]=$tea->id;
			}
			//存在老师
			if(!empty($cyuids)){
				$userModel = model('User');
				//获取老师的uid列表
				$uids = $userModel->getUidsByCyuids($cyuids);
				//获取老师信息
				$teacherInfos = $userModel->getUserInfoByUids($uids);
				//获取班主任
				$headTeacher = $cyClient->listTeacherByClassSubject ($classId, '00' );
				//存在班主任
				if(!empty($headTeacher)){
					//班主任cyuid
					$ht_cyuid=$headTeacher[0]->id;
				}
				
				$clazzInfo =$cyClient->getClass($classId);
				$grade = $this->getGradeByPhaseYear($clazzInfo->phase, $clazzInfo->year);
				
				//获取老师学科年级
				foreach ($teacherInfos as $k=>$ti){
					
					$teacherInfos[$k]['gradeName']=$grade['name'];
					$teacherInfos[$k]['subjectName']=$this->_getSubjectName($ti['subject']);
					
					if(!empty($ht_cyuid)){
						//找出班主任
						if($ti['cyuid']==$ht_cyuid){
							//标记班主任
							$ht_flag = $k;
						}
					}
				}
				//将班主任置顶
				if(!empty($ht_flag)){
					$header=$teacherInfos[$ht_flag];
					$header['ht']=1;
					unset($teacherInfos[$ht_flag]);
					array_unshift($teacherInfos,$header);
				}
				
				//dump($headTeacher);
				//dump($teacherInfos);
			}
			
		}
		
		return empty($teacherInfos)?null:$teacherInfos;
	}
	
	/**
	 * 获取用户的年级名称
	 * @param string $gradeCode 年级code
	 * @return string 年级名称
	 */
	private function _getGradeName($gradeCode){
		$gradeName='-';
		if(empty($gradeCode)){
			return $gradeName;
		}
		$gradeList = S('teacher_grade_list');
		if(empty($gradeList)){
			$cyClient = new CyClient();
			$gradeList = $cyClient->getGradeInfo();
			S('teacher_grade_list',$gradeList,3600);
		}
		
		foreach ($gradeList as $k=>$vo){
			if($gradeCode==$vo->code){
				$gradeName = $vo->name;
				break;
			}
		}
		return $gradeName;
	}
	
	/**
	 * 获取用户的学科名称
	 * @param string $subjectCode 学科code
	 * @return string 学科名称
	 */
	private function _getSubjectName($subjectCode){
		$subjectName='-';
		if(empty($subjectCode)){
			return $subjectName;
		}
		$subjectList = S('teacher_subject_list');
		if(empty($subjectList)){
			$cyClient = new CyClient();
			$subjectList = $cyClient->getSubjectInfo();
			S('teacher_subject_list',$subjectList,3600);
		}

		foreach ($subjectList as $k=>$vo){
			if($subjectCode==$vo->code){
				$subjectName = $vo->name;
				break;
			}
		}
		return $subjectName;
	}
	
	/**
	 * 通过学段和入学年份得到年级
	 * @param unknown $phase
	 * @param unknown $year
	 */
	private function getGradeByPhaseYear($phase,$year){
		$grades=array();
		//获取当前时间
		$time=getdate();
		//当前月
		$month=$time[mon];
		//当前日
		$day=$time[mday];
		//系统配置的月,日
		$tmp=split(',', C('CREATE_MONTH'));
		$i=$time[year]-$year;
		//判断当前是第几学期
		if ($month>$tmp[0]){
			$i+='1';
		}elseif ($month==$tmp[0]&&$day>$tmp[1]){
			$i+='1';
		}
		switch ($phase){
			case '03':
				switch ($i){
					case '1':
						$grades['code'] = '01';
						$grades['name'] = '一年级';
						break;
					case '2':
						$grades['code'] = '02';
						$grades['name'] = '二年级';
						break;
					case '3':
						$grades['code'] = '03';
						$grades['name'] = '三年级';
						break;
					case '4':
						$grades['code'] = '04';
						$grades['name'] = '四年级';
						break;
					case '5':
						$grades['code'] = '05';
						$grades['name'] = '五年级';
						break;
					case '6':
						$grades['code'] = '06';
						$grades['name'] = '六年级';
						break;
					case '0':
						$grades['code'] = '00';
						$grades['name'] = '零年级';
						break;
					default:$grades['name'] = '已毕业';
					break;
				}
				break;
			case '04':
				switch ($i){
					case '1':
						$grades['code'] = '07';
						$grades['name'] = '七年级';
						break;
					case '2':
						$grades['code'] = '08';
						$grades['name'] = '八年级';
						break;
					case '3':
						$grades['code'] = '09';
						$grades['name'] = '九年级';
						break;
					case '0':
						$grades['code'] = '00';
						$grades['name'] = '零年级';
						break;
					default:$grades['name'] = '已毕业';
					break;
				}break;
	
			case '05':
				switch ($i){
					case '1':
						$grades['code'] = '10';
						$grades['name'] = '高一';
						break;
					case '2':
						$grades['code'] = '11';
						$grades['name'] = '高二';
						break;
					case '3':
						$grades['code'] = '12';
						$grades['name'] = '高三';
						break;
					case '0':
						$grades['code'] = '00';
						$grades['name'] = '零年级';
						break;
					default:$grades['name'] = '已毕业';
					break;
				}
				break;
		}
		return $grades;
	}
}