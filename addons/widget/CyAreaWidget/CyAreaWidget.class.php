<?php
/**
 * 地区选择 widget
 * @example W('CyArea',array('province'=>1,'city'=>2,'district'=>3,'tpl'=>'selectArea','disable'=>false))
 * @author chengcheng3
 */

class CyAreaWidget extends Widget {

	/**
	 * 加载默认地区信息
	 * @param array $data
	 */
	public function render($data) {
		empty($data['tpl']) && $data['tpl'] = 'selectArea';
		$list['province'] = D('CyArea')->listAreaChildrenByCode('100000');
	
		isset($data['province']) &&  $list['city'] = D('CyArea')->listAreaChildrenByCode($data['province']);
		isset($data['city']) && $list['district'] = D('CyArea')->listAreaChildrenByCode($data['city']);

		$selected = array();
		$selected['province'] = $data['province'];
		$selected['city'] = $data['city'];
		$selected['district'] = $data['district'];
		$selected = implode(",",$selected);
		$data['list'] = json_encode($list);
		$data['selected'] = $selected;
		$content = $this->renderFile (dirname(__FILE__)."/".$data['tpl'].'.html', $data );
		return $content;
	}
	
	/**
	 * 地区信息请求
	 */
	public function area(){
		$areaCode = isset($_REQUEST['areaCode'])?trim($_REQUEST['areaCode']):'';
		$return = array();
		if(empty($areaCode)){
			$return['status'] = 0;
			$return['info'] = '无效的地区信息';
			exit(json_encode($return));
		}
		$areaChild = D('CyArea')->listAreaChildrenByCode($areaCode,false,0,100);
		$return['status'] = 1;
		$return['info'] = '成功获取信息';
		$return['data'] = $areaChild;
		exit(json_encode($return));
	}
}