<?php
/**
 * 精彩分享
 * @example  
 * @author yxxing
 */
class ExcellentShareWidget extends Widget{
	
	/**
	 * 没有参数
	 * @param unknown_type $data
	 * @return unknown
	 */
	public function render($data){
		$var = array();
		is_array($data) && $var = array_merge($var, $data);
		if(!isset($var['blogId'])||empty($var['blogId'])){
			$var['blogId'] =0;
		}
	    //渲染模版
        if($data['block']=='right'){
            $content = $this->renderFile(dirname(__FILE__)."/excellent_share_right.html", $var);
        }else{
            $content = $this->renderFile(dirname(__FILE__)."/excellent_share.html", $var);
        }
		unset($var, $data);
        //输出数据
		return $content;
    }
    
    /**
     * AJAX方式加载精彩分享
     */
    public function loadExcellentShare(){
    	$var = array();
    	$list= D("Blog", "blog")->getExcellentShare();
    	$blogs = array();
    	foreach($list as &$blog){
    		//去除blog内容中的tag和空格等
    		$blog['content'] = preg_replace("/\s/i", "", trim(strip_tags($blog['content'])));
    		if(mb_strlen($blog['content'], 'utf-8') > 45)
    			$blog['content'] = mb_substr($blog['content'], 0, 45, 'utf-8')."...";
    		else
    			$blog['content'] = $blog['content'];
    		//blog标题的长度截取
    		if(mb_strlen($blog['title'], "utf-8") > 11)
    			$blog['sub_title'] = mb_substr($blog['title'], 0, 11, "utf-8")."...";
    		else
    			$blog['sub_title'] = $blog['title'];
    	}
    	$list = array_chunk($list, 5);
    	$var['list'] = $list;
    	
    	//渲染模版
    	$content = $this->renderFile(dirname(__FILE__)."/ajax_excellent_share.html", $var);
    	
    	unset($var, $data);
    	//输出数据
    	return  json_encode($content);
    }
    
    /**
     * Ajax方式加载推荐日志和最新日志
     */
    public function loadExcellentShareForRight(){
    	$blogId =isset($_POST['blogId'])?$_POST['blogId']:0;
    	$var = array();
    	$reslist = S('resBlogList');
    	if (!$reslist){
    		$reslist= D("Blog", "blog")->getExcellentShare();
    		$blogs = array();
    		foreach($reslist as &$blog){
	    			//去除blog内容中的tag和空格等
	    			$blog['content'] = preg_replace("/\s/i", "", trim(strip_tags($blog['content'])));
	    			$blog['content'] =str_replace("&nbsp;",'',$blog['content']);
	    			if(mb_strlen($blog['content'], 'utf-8') > 45)
	    				$blog['content'] = mb_substr($blog['content'], 0, 45, 'utf-8')."...";
	    			else
	    				$blog['content'] = $blog['content'];
	    			$blog['sub_title'] = $blog['title'];
	    			$user = D('User')->getUserInfo($blog['uid']);
	    			$blog['userName'] = $user['uname'];
    		}
    		S('resBlogList',$reslist,3600*24);
    	}
    	if($blogId!=0){
    		foreach ($reslist as $key=>$val){
    			if($blogId==$val['id']){
    				unset($reslist[$key]);
    			}
    		}
    	}
    	$var['reslist'] = $reslist;
    	$newlist = S('newBlogList');
    	if (!$newlist){
    		$newlist= D("Blog", "blog")->getNewBlogs();
    		$blogs = array();
    		foreach($newlist as &$blog){
    			//去除blog内容中的tag和空格等
    			$blog['content'] = preg_replace("/\s/i", "", trim(strip_tags($blog['content'])));
    			$blog['content'] =str_replace("&nbsp;",'',$blog['content']);
    			if(mb_strlen($blog['content'], 'utf-8') > 45)
    				$blog['content'] = mb_substr($blog['content'], 0, 45, 'utf-8')."...";
    			else
    				$blog['content'] = $blog['content'];
    			$blog['sub_title'] = $blog['title'];
    			$user = D('User')->getUserInfo($blog['uid']);
    			$blog['userName'] = $user['uname'];
    		}
    		S('newBlogList',$newlist,60);
    	}
    	$var['newbloglist'] = $newlist;
    	//渲染模版
    	$content = $this->renderFile(dirname(__FILE__)."/ajax_excellent_share_right.html", $var);
    	//        dump($content);exit;
    
    	unset($var, $data);
    	//输出数据
    	return  json_encode($content);
    }
}