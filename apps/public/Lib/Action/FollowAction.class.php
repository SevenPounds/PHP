<?php
/**
 * 关注控制器
 * @author chenweichuan <chenweichuan@zhishisoft.com>
 * @version TS3.0
 */
class FollowAction extends Action {

	private $_follow_model = null;         // 关注模型对象字段

    /**
     * 初始化控制器，实例化关注模型对象
     */
	protected function _initialize() {
		$this->_follow_model = model('Follow');
    }

    /**
     * 添加关注操作
     * @return json 返回操作后的JSON信息数据
     */
    public function doFollow() {
        // 安全过滤
        $fid = t($_POST['fid']);
        $type = t($_REQUEST['ftype'])?t($_REQUEST['ftype']):0;
        if($type){
        	$res = $this->_follow_model->doFollowCampus($this->mid, intval($fid),intval($type));
        }else{
        	$res = $this->_follow_model->doFollow($this->mid, intval($fid));
        }
        $targetUser =  M("User")->getUserInfo($fid);
        //关注
        $conmment = '有新粉丝关注';
        $map =array(
            "userid" => $targetUser['cyuid'], //$targetUser['login']
            "type" => "4",
            "content" => $conmment,
            "url" => C("SPACE").'index.php?app=public&mod=Profile&act=follower&uid='.$fid
        ) ;
        $list = array($map);
        $message = json_encode($list);
        $sendMsg = array(
            "timestamp"=>time(),
            "appId" => C("SNS_APP_ID"),
            "message" => $message
        );

        Restful::sendMessage($sendMsg);

    	$this->ajaxReturn($res, $this->_follow_model->getError(), false !== $res);
    }

    /**
     * 取消关注操作
     * @return json 返回操作后的JSON信息数据
     */
    public function unFollow() {
        // 安全过滤
        $fid = t($_POST['fid']);
        $type = t($_REQUEST['ftype'])?t($_REQUEST['ftype']):0;
        if($type){
        	$res = $this->_follow_model->unFollowCampus($this->mid, intval($fid),intval($type));
        }else{
        	$res = $this->_follow_model->unFollow($this->mid, intval($fid));
        }
    	$this->ajaxReturn($res, $this->_follow_model->getError(), false !== $res);
    }

    /**
     * 批量添加关注操作
     * @return json 返回操作后的JSON信息数据
     */    
    public function bulkDoFollow() {
        // 安全过滤
    	$res = $this->_follow_model->bulkDoFollow($this->mid, t($_POST['fids']));
    	$this->ajaxReturn($res, $this->_follow_model->getError(), false !== $res);
    }
}