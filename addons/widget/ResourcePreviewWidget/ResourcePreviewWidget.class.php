<?php
/**
 * 资源预览
 * @author yxxing
 *
 */
/**
 * 传入参数  rid:需要预览的资源ID（必须传入）；extension：资源的相关信息（可选择性传入）；
 *
 */

class ResourcePreviewWidget extends Widget{
	
	public function render($data) {
		
		$resourceId = $data['rid'];
		$var = array();
		$previewUrl = "";
        $previewObj = $this->apis_client->getConvert($resourceId);
       if(!isset($previewObj->convId)){
            $previewObj = $this->apis_client->getFile($resourceId);
            $previewUrl = $previewObj->url;
        }else{
           $fileInfo = json_decode($previewObj->results);
            $previewUrl = $fileInfo->url;
        }
        //$sds = $this->apis_client->getConvert()
	/*	$oldObj = AttachServer::getInstance()->getFileInfo($resourceId);*/
		//if($previewObj->status == 2){

			/*if(empty($previewObj['data']['conversion'])){
				$previewUrl = $previewObj['data']['url'];
			}else{
				$previewUrl = $previewObj['data']['conversion'][0]['data']['destination'];
			}*/
		//}
		$var['previewUrl'] = $previewUrl;
		$var['extension'] = substr(strrchr($previewUrl, '.'), 1);
		//测试用
// 		$var['previewUrl'] = "http://localhost/sns/data/upload/%E4%BA%8C%E5%8A%9B%E5%B9%B3%E8%A1%A1%E7%9A%84%E6%9D%A1%E4%BB%B6.avi";
// 		$var['extension'] = "avi";
		$var = array_merge($var, $data);
		
		$content = $this->renderFile(dirname(__FILE__)."/preview.html", $var);
		//http://download.cycore.cn/rrt/a13999d470cadf7e6df599b508db1bc4/2246592/565bae582a217.flv
		unset($data, $var);
		//输出数据
		return $content;
	}
}