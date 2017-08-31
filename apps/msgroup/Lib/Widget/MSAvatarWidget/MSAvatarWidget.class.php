<?php
/**
 * 头像上传组件
 * @example 
 * $avatarData['url'] = 'widget/MSAvatar/doSaveClassAvatar';
 * $avatarData['widget_appname'] = 'msgroup';
 * $avatarData['rowid'] = $cid;
 * $avatarData['defaultImg'] = getCampuseAvatar($cid,2,'avatar_big');
 * {:W('MSAvatar',$avatarData)}
 * @version 
 */
class MSAvatarWidget extends Widget
{

    /**
     * @param array avatar 用户信息
     * @param string defaultImg 头像地址
     * @param string callback 回调方法
     */
    public function render($data)
    {
    	$params = array();
    	isset($data['widget_appname']) && $params['widget_appname'] = $data['widget_appname'];
    	isset($data['callback']) && $params['callback'] = $data['callback'];
    	$params['rowid'] = $data['rowid'];
        $var['action'] =urldecode(U($data['url'],$params));
        // 获取附件配置信息
        $attachConf = model('Xdata')->get('admin_Config:attach');
        $var['attach_max_size'] = $attachConf['attach_max_size'];

        is_array($data) && $var = array_merge($var, $data);
        if(strtolower($params['widget_appname']) == "msgroup"){
        	$content = $this->renderFile(dirname(__FILE__) . "/msgroup.html", $var);
        }else{
        	$content = $this->renderFile(dirname(__FILE__) . "/default.html", $var);
        }
		unset($params);
        return $content;
    }
    
    
    /**
	 * 保存学校的头像设置操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveSchoolAvatar() {
		$cid =$_GET['rowid'];
	
		$dAvatar = D('MSAvatar');
		$params['app'] = 'school';
		$params['rowid'] = $cid;
		$dAvatar->init($params); 			// 初始化Model用户id
		// 安全过滤
		$step = t($_GET['step']);
		if('upload' == $step) {
			$result = $dAvatar->upload();
		} else if('save' == $step) {
			$result = $dAvatar->dosave();
		}
		unset($params);
		$this->ajaxReturn($result['data'], $result['info'], $result['status']);
	}
	
	/**
	 * 保存班级的头像设置操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveClassAvatar() {
		$cid =$_GET['rowid'];
	
		$dAvatar = D('MSAvatar');
		$params['app'] = 'class';
		$params['rowid'] = $cid;
		$dAvatar->init($params); 			// 初始化Model用户id
		// 安全过滤
		$step = t($_GET['step']);
		if('upload' == $step) {
			$result = $dAvatar->upload();
		} else if('save' == $step) {
			$result = $dAvatar->dosave();
		}
		unset($params);
		$this->ajaxReturn($result['data'], $result['info'], $result['status']);
	}
	
	/**
	 * 保存名师的头像设置操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveMsGroupAvatar() {
		$cid =$_GET['rowid'];
	
		$dAvatar = D('MSAvatar');
		$params['app'] = 'msgroup';
		$params['rowid'] = intval($cid);
		$dAvatar->init($params); 			// 初始化Model用户id
		// 安全过滤
		$step = t($_GET['step']);
		if('upload' == $step) {
			$result = $dAvatar->upload();
		} else if('save' == $step) {
			$result = $dAvatar->dosave();
		}
		unset($params);
		$this->ajaxReturn($result['data'], $result['info'], $result['status']);
	}
	
	
}
