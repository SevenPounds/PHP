<?php
include_once dirname(__FILE__).'/CyBaseModel.class.php';
/**
 * 班级模型 
 * @author cheng
 *
 */
class CyClassModel extends CyBaseModel {

	
	/**
	 * 获取学校中的班级列表
	 * @param  $start
	 * @param  $perpage
	 * @param  $orgPath 路径  或者 id 
	 * @param  $withAllChildren
	 */
	public function list_class_by_school($schoolId,$skip, $limit){
		//if(empty($schoolId)) return array();
		$_result =   $this->client->listClassBySchool($schoolId,$skip, $limit);
		return $this->muti_class_convert($_result);;
	}
	
	/**
	 * 获取班级信息
	 * @param  $cid
	 */
	public function get_class_info_by_id($cid){
		if(!$cid){
			return null;
		}
		$_result = $this->client->getClass($cid);
		$_result = $this->class_convert($_result);
		$_result['type'] = 2;
		return $_result;
	}
	
	/**
	 * 通过多个ID获取多个的班级信息
	 * @param  $cid
	 */
	public function get_class_infos_by_ids($cids){
		$classes = array();
		foreach ($cids AS $cid){
			if(!$cid){
				continue;
			}
			$classInfo =  $this->client->getClass($cid);
			$classInfo =  $this->class_convert($classInfo);
			$classInfo['type'] = 2;
			$classes[$cid] = $classInfo;
		}
		return $classes;
	}
	
	
	/**
	 * 获取用户所在班级
	 * @param unknown_type $userId
	 * @param unknown_type $skip
	 * @param unknown_type $limit
	 */
	public function list_class_by_user($userId, $skip = 0, $limit = 100){
		if(empty($userId)) return array();		
		$_result =  $this->client->listClassByUser($userId, $skip, $limit);        
		return $this->muti_class_convert($_result);
	}
	
	/**
	 * 
	 * @param int $areaId
	 * @param int $skip
	 * @param int $limit
	 */
	public function list_class_by_area($areaId,$skip = 0, $limit = 100){
		if(empty($areaId)) return array();
		$_result =  $this->client->listClassByArea($areaId, $skip, $limit);
		return $this->muti_class_convert($_result);
	}
	
	/**
	 * 更新班级信息
	 * @param HashMap<String, Object> $orgMap 组织机构信息，key-value键值对json，参考@see Organization 对象定义
	 */
	public function update_class($orgMap){
		return $this->client->updateClass($orgMap);
	}
	
	/**
	 * 获得班级的信息(在动态中使用)
	 * @param int $cid
	 * @author yxxing
	 */
	public function get_class_info_for_feed($cid){
		$shoolInfo = S('class_info_id_feed'.$cid);
		if(empty($shoolInfo)){
			$org = $this->client->getClass($cid);
			$org = $this->class_convert($org);
			$campusAvatar = getCampuseAvatar($cid, $org['type']);
				
			$shoolInfo = array();
			$shoolInfo['cid'] = $org['id'];
			$shoolInfo['uname'] = $org['name'];
			$shoolInfo['type'] = $org['type'];
			$shoolInfo['space_url'] = getHomeUrl($org);
			$shoolInfo['space_link'] = "<a href='".$shoolInfo['space_url']."' class='name' event-node='face_card' orgType='".$shoolInfo['type']."' cid='".$shoolInfo['cid']."'>".$shoolInfo['uname']."</a>";
			$shoolInfo['avatar_small'] = $campusAvatar['avatar_small'];
			S('class_info_id_feed'.$cid, $shoolInfo, 3600);
		}
		return $shoolInfo;
	}
	
	
	public function list_class_by_school_grade($schoolId, $grade, $skip = 0, $limit = 100){
		if(empty($schoolId)) return array();
		$_result =   $this->client->listClassBySchoolGrade($schoolId, $grade,$skip, $limit);
		return $this->muti_class_convert($_result);
	}
	

	
	/**
	 * 获得学校的班级数量
	 * @param int $schoolId
	 * @return 班级数量
	 */
	public function count_school_class($schoolId){
		$_result = $this->client->countClassBySchool($schoolId);
		return $_result;
	}
	
}
?>