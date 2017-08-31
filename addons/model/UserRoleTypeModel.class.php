<?php
/**
 * 用户角色类型
 * @author cheng
 *
 */
class UserRoleTypeModel {
	
	 const PROVINCE_RESAERCHER = 'province'; //省级教研员
	 const CITY_RESAERCHER = 'city'; //市级教研员
	 const COUNTY_RESAERCHER = 'district'; //区县级教研员
	 const RESAERCHER = "instructor"; //教研员
	 const TEACHER = "teacher"; //教师
	 const STUDENT= "student"; //学生
	 const PARENTS= "parent"; //家长
	 const EDUADMIN = "eduadmin";//教育机构管理者
	 const MENBER = 'member'; //普通用户
	 const TEAMMEMBER = 'teammember'; //团队成员
	 const EDUPERSONNEL = 'edupersonnel';//机构用户
	 const LEDCSUPERADMIN = 'ledcsuperadmin';//超级管理员
	 const DEPTLEADER = 'deptLeader';//系统管理员
	 const LEDCSCHOOLMNG = 'ledcschoolMng';//学校管理员
	 const LEDCDISTRICTMNG = 'ledcdistrictMng';//区县管理员
	 const LEDCCITYMNG = 'ledccityMng';//市级管理员
	 const LEDCPROVINCEMNG = 'ledcprovinceMng';//省级管理员

	 /**
	  * 获取用户角色中文名
	  * @param string $roleName
	  */
	 public static function getCNRoleName($roleName){
	 	$roles =array(
	 			'instructor'=>'教研员',
	 			'teacher'=>'教师',
	 			'student'=>'学生',
	 			'parent'=>'家长',
	 			'province'=>'省级教研员',
	 			'city'=>'市级教研员',
	 			'district'=>'区县级教研员',
	 			'eduadmin' =>'教育机构管理者',
				'member'=>'普通用户',
				'teammember'=>'团队成员',
				'edupersonnel'=>'机构用户',
				'ledcsuperadmin'=>'超级管理员',
				'deptLeader'=>'系统管理员',
				'ledcschoolMng'=>'学校管理员',
				'ledcdistrictMng'=>'区县管理员',
				'ledccityMng'=>'市级管理员',
				'ledcprovinceMng'=>'省级管理员',
	 			);
	 	return $roles[$roleName];
	 }







	 /**
	  * 获取第一角色
	  * 角色优先级：老师、教研员、学生、家长
	  * 任何具有多角色的账号，只以第一优先级角色登录空间
	  * @param $cyRolelist array() cycore角色列表
	  * @return array 返回第一RRT角色
	  */

	 public static function getFirstRole($cyRolelist){
	 	$roles = array(
	 		self::TEACHER,
			self::RESAERCHER,
			self::STUDENT,
			self::PARENTS,
			self::EDUADMIN,
			self::MENBER,
			self::TEAMMEMBER,
			self::EDUPERSONNEL,
			self::LEDCSUPERADMIN,
			self::DEPTLEADER,
			self::LEDCSCHOOLMNG,
			self::LEDCDISTRICTMNG,
			self::LEDCCITYMNG,
			self::LEDCPROVINCEMNG,

		);
	 	$rrt_role = array();
	 	foreach ($roles as $role){
		 	foreach($cyRolelist as $cy_role){
		 		if($role == $cy_role->enName){
		 			$r = array();
		 			$r['id'] = $cy_role->id;
		 			$r['name'] = $cy_role->enName;
		 			$rrt_role[] = $r;
		 			break;
		 		}
		 	}
	 	}
	 	return $rrt_role;	
	 }
	 
	 /**
	  * 判断是否有遴选权限，即判断是否是三级教研员
	  * @param string $instructor_level 教研员级别标志
	  * @return bool
	  */
	 public static function isHasSelectionRight($instructor_level){
	 	return in_array($instructor_level,array(self::PROVINCE_RESAERCHER,self::CITY_RESAERCHER,self::COUNTY_RESAERCHER));
	 }
}
?>
