<?php
/**
 * cyore服务基础类 
 * @since 2014/3/31
 * @author chengcheng3
 *
 */
Class CyBaseModel{
	
	protected  $client;
	
	public function __construct() {
	    try{
			$this->client =D('CyCore')->CyCore;
		}catch(Exception $e){
			throw_exception("网络连接异常，请稍后重试");
		}
	}
	
	
	/**
	 * 获取subject信息
	 */
	public function getSubjectInfo() {
		$_result =  $this->client->getSubjectInfo();
		return $_result ;
	}
	
	/**
	 * 获取grade信息
	 */
	public function getGradeInfo() {
		$_result =  $this->client->getGradeInfo();
		return $_result ;
	}
	
	
	
	/**
	 * cyore  用户转换为 eduspace 用户
	 * @param object $cyuser
	 * return array
	 */
	protected function user_convert($cyuser){
		$user  = array();
		$user['cyuid'] = $cyuser->id;
		$user['uname'] =  $cyuser->userName;
		$user['login'] =  $cyuser->loginName;
		$user['phone'] = $cyuser->phone;
		$user['email'] = $cyuser->email;
		$user['pinyinName'] = $cyuser->pinyinName;
		$user['isdel'] = $cyuser->delFlag;
		$user['sex'] = $cyuser->gender == '0' ? 2 : $cyuser->gender;
		$user['updatetime'] = empty($cyuser->updateTime) ? date("Y-m-d H:i:s") : $cyuser->updateTime;
		$user['createtime'] = $cyuser->createTime;
		$user['intro'] = $cyuser->remark;
		return $user;
	}
	
	/**
	 * 批量cyore  用户转换为 eduspace 用户
	 * @param object $cyuser
	 * return array
	 */
	protected function muti_user_convert($cyusers){
		$users = array();
		foreach($cyusers as $cyuser){
			$users[$cyuser->id] = $this->user_convert($cyuser);
		}
		return $users;
	}
	
	
	/**
	 * 学校信息转换
	 * @param array $cyorgnization
	 * @return array
	 */
	protected function school_convert($cyorgnization){
		$orgnization =array();
		$orgnization['id'] = $cyorgnization->id;
		$orgnization['name'] = $cyorgnization->schoolName;
		$orgnization['type'] = 1;
		$orgnization['schoolCode'] = $cyorgnization->schoolCode;
		$orgnization['countryId'] = $cyorgnization->countryId;
		$orgnization['provinceId'] = $cyorgnization->provinceId;
		$orgnization['cityId'] = $cyorgnization->cityId;
		$orgnization['districtId'] = $cyorgnization->districtId;
		$orgnization['parentOrgId'] = $cyorgnization->parentOrgId;
		$orgnization['isdel'] = $cyorgnization->delFlag;
		$orgnization['phone'] = $cyorgnization->mobile;
		$orgnization['contact'] = $cyorgnization->address;
		return $orgnization;
	}
	
	/**
	 * 批量学校信息转换
	 * @param array $cyorgnizations
	 */
	protected function muti_school_convert($cyorgnizations){
		$orgnizations =array();
		foreach($cyorgnizations as $cyorgnization){
			$orgnizations[$cyorgnization->id] = $this->school_convert($cyorgnization);
		}
		return $orgnizations;
	}
	

	/**
	 * 班级信息转换
	 * @param array $cyorgnization
	 * @return array
	 */
	protected function class_convert($cyorgnization){
		$orgnization =array();
		$orgnization['id'] = $cyorgnization->id;
		$orgnization['name'] = $cyorgnization->className;
		$orgnization['type'] = 2;
		$orgnization['classCode'] = $cyorgnization->classCode;
		$orgnization['countryId'] = $cyorgnization->countryId;
		$orgnization['provinceId'] = $cyorgnization->provinceId;
		$orgnization['cityId'] = $cyorgnization->cityId;
		$orgnization['districtId'] = $cyorgnization->districtId;
		$orgnization['parentOrgId'] = $cyorgnization->parentOrgId;
		$orgnization['schoolId'] = $cyorgnization->schoolId;
		$orgnization['grade'] = $cyorgnization->grade;
		$orgnization['isdel'] = $cyorgnization->delFlag;
		return $orgnization;
	}
	
	/**
	 * 批量转换班级信息
	 * @param  $cyorgnizations
	 * @return array
	 */
	protected function muti_class_convert($cyorgnizations){
		$orgnizations =array();
		foreach($cyorgnizations as $cyorgnization){
			$orgnizations[$cyorgnization->id] = $this->class_convert($cyorgnization);
		}
		return $orgnizations;
	}
	
	
	/**
	 * cycore地区信息转换eduspace地区信息
	 * @param $cyarea object
	 */
	protected function area_convert($cyarea){
		$area = array();
		$area['id'] = $cyarea->id;
		$area['name'] = $cyarea->areaName;
		$area['code'] = $cyarea->areaCode;
		$area['type'] =  $cyarea->type;
		$area['countryId'] =  $cyarea->countryId;
		$area['provinceId'] =  $cyarea->provinceId;
		$area['cityId'] =  $cyarea->cityId;
		$area['districtId'] =  $cyarea->districtId;
		$area['isdel'] =  $cyarea->delFlag;
		return $area;
	}
	
	/**
	 * 批量cycore地区信息转换eduspace地区信息
	 * @param $cyarea array objects
	 */
	protected function muti_area_convert($cyareas){
		$areas = array();
		foreach($cyareas as $cyarea){
			$areas[$cyarea->id] = $this->area_convert($cyarea);
		}
		return $areas;
	}
	
	
	/**
	 * 教育组织信息转换
	 * @param int $cyorgnization
	 * @return  NULL or array
	 */
	protected function eduorg_convert($cyorgnization){
		$orgnization =array();
		$orgnization['id'] = $cyorgnization->id;
		$orgnization['name'] = $cyorgnization->eduorgName;
		$orgnization['type'] = 3;
		$orgnization['eduorgCode'] = $cyorgnization->eduorgCode;
		$orgnization['countryId'] = $cyorgnization->countryId;
		$orgnization['provinceId'] = $cyorgnization->provinceId;
		$orgnization['cityId'] = $cyorgnization->cityId;
		$orgnization['districtId'] = $cyorgnization->districtId;
		$orgnization['parentOrgId'] = $cyorgnization->parentOrgId;
		$orgnization['isdel'] = $cyorgnization->delFlag;
		return $orgnization;
	}
	
	/**
	 * 批量教育机构信息转换
	 * @param objects $cyorgnizations
	 * @return array
	 */
	protected function muti_eduorg_convert($cyorgnizations){
		$orgnizations =array();
		foreach($cyorgnizations as $cyorgnization){
			$orgnizations[$cyorgnization->id] = $this->eduorg_convert($cyorgnization);
		}
		return $orgnizations;
	}
	
	
	
}