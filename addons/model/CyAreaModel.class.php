<?php
include_once dirname(__FILE__).'/CyBaseModel.class.php';

class CyAreaModel extends CyBaseModel {

	/**
	 * 根据地区ID 获取地区信息
	 * @param integer $areaId
	 * @return array
	 */
	public function getArea($areaId) {
		$area = S('area_'.$areaId);
		if($area){
			return $area;
		}
		$_result = $this->client->getArea(intval($areaId));
		$area =  $this->area_convert($_result);
		S('area_'.$areaId,$area,3600);
		return $area;
	}
	
	
	/**
	 * 获取地区信息
	 * @param $areaId
	 */
	public function getFullArea($areaId){
		if(!$areaId){
			return null;
		}
		$area = $this->getArea($areaId);
		if(!$area['id']){
			return null;
		}
		$fullArea = S('Fullarea_'.$area['id']);
		if($fullArea){
			return $fullArea;
		}
		switch(intval($area['type'])){
			case 1:
				$fullArea['country'] = $area;
				$fullArea['province'] = null;
				$fullArea['city'] = null;
				$fullArea['district'] = null;
				break;
			case 2:
				$fullArea['country'] = $this->getArea($area['countryId']);
				$fullArea['province'] = $area;
				$fullArea['city'] =null;
				$fullArea['district'] = null;
				break;
			case 3:
				$fullArea['country'] = $this->getArea($area['countryId']);
				$fullArea['province'] = $this->getArea($area['provinceId']);
				$fullArea['city'] = $area;
				$fullArea['district'] = null;
				break;
			case 4:
				$fullArea['country'] = $this->getArea($area['countryId']);
				$fullArea['province'] = $this->getArea($area['provinceId']);
				$fullArea['city'] = $this->getArea($area['cityId']);
				$fullArea['district'] = $area;
				break;
			default :
				throw_exception("area address error!");
				break;
		}
		S('Fullarea_'.$area['id'],$fullArea,3600);
		return $fullArea;
	} 
	
	/**
	 * 获取地区信息列表
	 * @param unknown_type $areaCode
	 * @param unknown_type $type
	 * @param unknown_type $skip
	 * @param unknown_type $limit
	 */
	public function listAreaByCode($areaCode,$type, $skip = 0, $limit = 100){
		$_result =array();
		if(!$areaCode) return $_result;
		
		switch($type){
			case 'province':
				$_result =  $this->client->listProvince($areaCode, $skip, $limit);
				break;
			case 'city':
				$_result =  $this->client->listCityByCode($areaCode, $skip, $limit) ;
				break;
			case 'county':
				$_result =  $this->client->listDistrictByCode($areaCode, $skip, $limit);
				break;
			default:
				throw_exception("areaCode type args error");
				break;
		}
		return $this->muti_area_convert($_result);
	}
	
	
	/**
	 * 获取地区信息列表
	 * @param unknown_type $areaCode
	 * @param unknown_type $type
	 * @param unknown_type $skip
	 * @param unknown_type $limit
	 */
	public function listAreaById($areaId,$type, $skip = 0, $limit = 100){
		$_result =array();
		if(!$areaId) return $_result;
		
		switch($type){
			case 'province':
				$_result =  $this->client->listProvince(intval($areaId), $skip, $limit);
				break;
			case 'city':
				$_result =  $this->client->listCity(intval($areaId), $skip, $limit) ;
				break;
			case 'county':
				$_result =  $this->client->listDistrict(intval($areaId), $skip, $limit);
				break;
			default:
				throw_exception("areaId type args error");
				break;
		}
		return $this->muti_area_convert($_result);
	}
	
	
	
	
	/**
	 * 获取组织地区信息
	 * @param array $orgnization
	 * @return array
	 */
	public function getFullAreaByOrgnization($orgnization){
		$FullArea = Array();
		$FullArea['country'] =$orgnization['countryId']? $this->getArea($orgnization['countryId']):null;
		$FullArea['province'] =$orgnization['provinceId']? $this->getArea($orgnization['provinceId']):null;
		$FullArea['city'] = $orgnization['cityId']?$this->getArea($orgnization['cityId']):null;
		$FullArea['district'] = $orgnization['districtId']?$this->getArea($orgnization['districtId']):null;
		return $FullArea;
	}
	
		/**
	 * 根据地区code 获取地区信息
	 * @param string $areaCode
	 * @return array
	 */
	public function getAreaByCode($areaCode) {
		if(empty($areaCode)) return array();
		$area = S('areaCode_'.$areaCode);
		if($area){
			return $area;
		}
		$_result =  $this->client->getAreaByCode($areaCode);
		$area =  $this->area_convert($_result);
		S('areaCode_'.$areaCode,$area,3600);
		return  $area;
	}
	
	
	public function listAreaChildrenByCode($areaCode,$withAllChildren = false, $skip = 0, $limit = 100){
		if(empty($areaCode)) return array();
		$_result = $this->client->listAreaChildrenByCode($areaCode, $withAllChildren , $skip, $limit);
		return $this->muti_area_convert($_result);
	}
	
	
	
	
}
?>