<?php
/**
 * 
 * @author sjzhao
 * @version TS 3.0
 */
class CommunityWidget extends Widget{
	/**
	 *  
	 * @param array $data
	 */
	public function render($data){
		 $list=array();
// 		 $list[]=array("id"=>1,"appname"=>"说说", 
// 				 		"url"=>U('public/Profile/index',array('uid'=>$GLOBALS['ts']['uid'])),
// 		 				'img'=>"__THEME__/image/app_ss_large.png");
		 $list[]=array("id"=>2,"appname"=>"相册", "url"=>U('photo/Index/albums',array('uid'=>$GLOBALS['ts']['uid'])),'img'=>"__THEME__/image/app_photo_large.png");
		 $list[]=array("id"=>3,"appname"=>"日志", "url"=>U('blog/Index/index',array('uid'=>$GLOBALS['ts']['uid'],'type'=>'blog')),'img'=>"__THEME__/image/app_blog_large.png");
		 $list[]=array("id"=>4,"appname"=>"关注/粉丝", "url"=>U('public/Index/following'),'img'=>"__THEME__/image/app_group_large.png");
		 /* $list[]=array("id"=>5,"appname"=>"留言板","url"=>"javascript:void(0)","img"=>"__THEME__/image/app_ly_large.png"); */
		 $list[]=array("id"=>6,"appname"=>"个人资料","url"=>U('public/Account/index',array('uid'=>$GLOBALS['ts']['uid'])),'img'=>"__THEME__/image/app_qyh_large.png");
		 $var['list'] = $list;
		 $content = $this->renderFile(dirname(__FILE__)."/communityuse.html", $var);
		 return $content;
	}
}
?>