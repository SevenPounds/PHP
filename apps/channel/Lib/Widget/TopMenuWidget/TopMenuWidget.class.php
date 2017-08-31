<?php
/**
 * 频道顶部菜单Widget
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class TopMenuWidget extends Widget
{
	/**
	 * 模板渲染
	 * @param array $data 相关数据
	 * @return string 频道内容渲染入口
	 */
	public function render($data)
	{
		// 设置频道模板
		$template = 'menu';
		// 频道分类ID
		$var['cid'] = intval($data['cid']);
		// 频道名称
		$var['title'] = t($data['title']);
		// 频道分类数据
		$var['channelCategory'] = $data['channelCategory'];
		// 获取频道的关注数目
		$var['followingCount'] = D('ChannelFollow', 'channel')->getFollowingCount($var['cid']);
		// 获取频道的记录数目
		$var['channelCount'] = D('Channel', 'channel')->getChannelCount($var['cid']);
		// 获取用户与频道分类的关注状态
		$var['followStatus'] = model('ChannelFollow')->getFollowStatus($GLOBALS['ts']['mid'], $var['cid']);
		$content = $this->renderFile(dirname(__FILE__)."/".$template.".html", $var);
		return $content;
    }

	/**
	 * 频道关注状态修改接口
	 * @return json 处理后返回的数据
	 */
	public function upFollowStatus()
	{
		$uid = intval($_POST['uid']);
		$cid = intval($_POST['cid']);
		$type = t($_POST['type']);
		$res = model('ChannelFollow')->upFollow($uid, $cid, $type);
		$result = array();
		if($res) {
			$result['status'] = 1;
			$result['info'] = '';
		} else {
			$result['status'] = 0;
			$result['info'] = '';
		}

		exit(json_encode($result));
	}
	/**
	 * 实时获取用户关注数据
	 * @return json 返回关注数据
	 */
	public function getFollowDatas(){
		$uid = intval($_POST['uid']);
		$fda = model('ChannelFollow')->getFollowList($uid);
		$result = array();
		if($fda){
			$result['status'] = 1;
			$result['data'] = $fda;
		}else{
			$result['status'] = 0;
			$result['data'] = '';
		}
		exit(json_encode($result));
	}
	/**
	 * 实时获取用户关注数据
	 * @return json 返回关注数据
	 */
	public function getFollowIds(){
		$uid = $GLOBALS['ts']['mid'];
		$fda = model('ChannelFollow')->getFollowChannelId($uid);
		$result = array();
		if($fda){
			$result['status'] = 1;
			$result['data'] = $fda;
		}else{
			$result['status'] = 0;
			$result['data'] = '';
		}
		exit(json_encode($result));
	}
	/**
	 * 频道关注状态修改接口
	 * @return json 处理后返回的数据
	 */
	public function upFollowStatus2()
	{
		$uid = intval($_POST['uid']);
		$cid = $_POST['cid'];
		$type=$_POST['type'];
		$res = model('ChannelFollow')->upFollow2($uid, $cid,$type);
		$result = array();
		if($res) {
			$result['status'] = 1;
			$result['info'] = '';
		} else {
			$result['status'] = 0;
			$result['info'] = '';
		}
	
		exit(json_encode($result));
	}
}