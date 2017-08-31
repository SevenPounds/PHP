<?php
/**
 * 热门资源Widget
 * example:{:W('HotResource'),array('tpl'=>'hot_resource')}
 * @author hhshi
 * @version TS3.0 2014.8.11
 */
class HotResourceWidget extends Widget{

	/**
	 * (non-PHPdoc)
	 * @see Widget::render()
	 */
	public function render($data) {
		$var = $data;
		$client = new ResourceClient();
		$conditions = array(
				'limit'=>C('HOT_RES_COUNT'),
				'skip'=>0,
				'rrtlevel1'=>'08',
				'auditstatus'=>1,
				'productid'=>'_WHOLE',
				'order'=>'-statistics.downloadcount'
		);
		
		if(C('ENABLE_SECURITY')==1){//是否启用安全监管监测的结果
			$conditions['lifecycle.securitystatus']='-(2 or 3 or 4)';
		}
		
		$fileds = array('general','date','statistics');
		$result = S("hot_resource");
		if(!$result){
			$result = $client->Res_GetResources($conditions,$fileds);
			S("hot_resource",$result,3600);
		}
		
		if($result->statuscode == '200'){
			$var['list'] = $result->data;
		}else{
			$var['list'] = array();
		}
		$content = $this->renderFile(dirname(__FILE__)."/".$var['tpl'].'.html', $var);
		return $content;
	}
}
?>