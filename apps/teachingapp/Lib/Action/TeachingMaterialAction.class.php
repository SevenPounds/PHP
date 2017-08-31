<?php
require "./apps/teachingapp/Lib/Library/util.php";

class TeachingMaterialAction extends AccessAction{
	public function index(){
		//资源类型编码,资源类型名称，最后一个参数是$this,详细见require__↑
		displayIndex("0300","媒体素材",$this);
		$uid = !empty($_GET['uid'])?$_GET['uid']:$this->mid;
		$this->assign('uid',$uid);
		$this->assign('mid',$this->mid);
		$this->display("./apps/teachingapp/Tpl/default/index.html");
	}
}