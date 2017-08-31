<?php
/**
 * 频道选择框
 * @example W('ShareToChannel') 
 * @author yudylaw
 * @version TS3.0
 */
class ShareToChannelWidget extends Widget{
	
	/**
     * @param integer sid 资源ID,如分享小名片就是对应用户的用户ID，分享微博就是微博的ID
     * @param string stable 资源所在的表，如小名片就是contact表，微博就是feed表
     * @param string appname 资源所在的应用
     * @param integer nums 该资源被分享的次数
     * @param string initHTML 默认的内容 
	 */
	public function render($data){
		$var = array();
		$var = array_merge($var, $data);
		//$var['data'] = $data = model('CategoryTree')->setTable('channel_category')->getCategoryList();
		$channelCategory=array();
		$channelCategory=model('CategoryTree')->setTable('channel_category')->getCategoryList();
		foreach ($channelCategory as $key=>$value){
			if($value['pid']==0){
				unset($channelCategory[$key]);
			}
		}
		$var['data']=$channelCategory;
		//渲染模版
	    $content = $this->renderFile(dirname(__FILE__)."/ShareToChannel.html",$var);
	
		
		unset($var,$data);
        //输出数据
		return $content;
    }
}