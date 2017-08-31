<?php
	/**
	 * 名师工作室成员等级定义
	 */
	function msGroupLevel() {
		return array('1' => '成员', '2' => '管理员', '3' => '创建者');
	}
	
	/**
	 * 获取名师工作室等级名称
	 * @param int $key
	 * @return string
	 */
	function getMsGroupLevel($key) {
		$list = msGroupLevel();
		return $list[$key];
	}
	/**
	 * 获取资源分类
	 * @author yxxing
	 */
	function getRestype_ms($toSelect = false){
	
		$restype = array();
		if($toSelect)
			$restype['0000'] = array("code"=>"0000", "name"=>"请选择");
		$restype['0100'] = array("code"=>"0100", "name"=>"教学设计");
		$restype["0600"] = array("code"=>"0600", "name"=>"教学课件");
		$restype["0300"] = array("code"=>"0300", "name"=>"媒体素材");
	
		return $restype;
	}
	/**
	 * 返回类型名称
	 * @param string $code
	 * @return string
	 */
	function getResTypeNameByCode($code){
		switch($code){
			case "0100":
				return "教学设计";
				break;
			case "0600":
				return "教学课件";
				break;
			case "0300":
				return "媒体素材";
				break;
			default:
				return "其他";
				break;
		}
	}
?>