<?php
/**
 * 群聊 控制类
 * @author Stream
 *
 */
class GroupFeedAction extends BaseAction{
	/**
	 * 3.0发布微博操作，用于AJAX
	 * @return json 发布微博后的结果信息JSON数据
	 */
	public function PostFeed()	{
		if ( !$this->ismember ){
			$return = array('status'=>0,'data'=>'抱歉，您不是该圈子成员');
			exit(json_encode($return));
		}
		// 返回数据格式
		$return = array('status'=>1, 'data'=>'');
		//群组ID
		$gid = intval($_POST['gid']);
		// 用户发送内容
		$d['content'] = isset($_POST['content']) ? filter_keyword(h($_POST['content'])) : '';
		$d['gid'] = $gid;
		// 原始数据内容
		$d['body'] = filter_keyword(h($_POST['body']));
		$d['source_url'] = urldecode($_POST['source_url']);  //应用分享到微博，原资源链接
		// 滤掉话题两端的空白
		$d['body'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$d['body']);
		// 附件信息
		$d['attach_id'] = trim(t($_POST['attach_id']), "|");
		!empty($d['attach_id']) && $d['attach_id'] = explode('|', $d['attach_id']);
		// 发送微博的类型
		$type = t($_POST['type']);
		// 所属应用名称
		$app = 'group';

		if($data = D('GroupFeed')->put($this->uid, $app, $type, $d)) {
			// 微博来源设置
			$data['from'] = getFromClient($data['from'], 'public');
			$this->assign($data);
			//微博配置
			$weiboSet = model('Xdata')->get('admin_Config:feed');
			$this->assign('weibo_premission', $weiboSet['weibo_premission']);
			$return['data'] = $this->fetch();
		} else {
			$return = array('status'=>0,'data'=>model('Feed')->getError());
		}
	
		exit(json_encode($return));
	}
}