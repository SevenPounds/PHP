<?php

/**
 * WebIM-for-ThinkSNS
 *
 * @author      Ery Lee <ery.lee@gmail.com>
 * @copyright   2014 NexTalk.IM
 * @link        http://github.com/webim/webim-for-thinksns
 * @license     MIT LICENSE
 * @version     5.5
 * @package     WebIM-for-ThinkSNS
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Webim-for-ThinkSNS Plugin
 */
class webim_plugin_thinksns extends webim_plugin {

	/*
	 * constructor
	 */
    public function __construct() { 
        parent::__construct();
    }

	/*
	 * old constructor
	 */
    function webim_plugin_thinksns() {
        $this->__construct();
    }

    /**
     * API: current user
     *
     * @return object current user
     */
    public function user() {
        global $_SESSION;
        $uid = $_SESSION['mid'];
        if(!$uid) return null;
        $user = model('User')->getUserInfo($uid);
        if(!$user) return null;
		return (object) array (
			'id'		=> (string)$uid,
			'nick'		=> $user['uname'],
			'avatar'	=> $user['avatar_small'],
			'url'		=> $user['space_url'],
			'status'	=> $user['intro'],
            'role'      => (isset($user['admin_level']) && $user['admin_level']) ? 'admin' : 'user',
		);

    }


	/*
	 * API: Buddies of current user.
     *
     * @param string $uid current uid
	 *
     * @return array Buddy list
     *
	 * Buddy:
	 *
	 * 	id:         uid
	 * 	uid:        uid
	 *	nick:       nick
	 *	avatar:     url of photo
     *	presence:   online | offline
	 *	show:       available | unavailable | away | busy | hidden
	 *  url:        url of home page of buddy 
	 *  status:     buddy status information
	 *  group:      group of buddy
	 *
	 */
	public function buddies($uid) {
		global $IMC;
		//根据当前用户uid获取双向follow的好友id列表
		$follows = model('Follow')->getFriendsData($uid);
		if(!$follows) $follows = array();
		$fids = array_map(array($this, '_fid'), $follows);
		//获取好友信息列表
		$friends = model('User')->getUserInfoByUids($fids);
		if(!$friends) $friends = array();
		//获取管理员信息列表
        $admin_uids = array();
        foreach(explode(",", $IMC['admin_uids']) as $uid) {
            if(! (in_array($uid, $fids) or $uid == $uid) ) {
                $admin_uids[] = $uid;
            }
        }
		$admins = model('User')->getUserInfoByUids($admin_uids);
		if(!$admins) $admins = array();
		//转换为Webim Buddy对象.
		return $this->_to_buddies(array_merge($admins, $friends));
    }

    private function _fid($follow) {
        return $follow['fid'];
    }

	/*
	 * API: buddies by ids
	 *
     * @param array $ids buddy id array
     *
     * @return array Buddy list
     *
	 * Buddy
	 */
	public function buddies_by_ids($uid, $ids) {
        if( count($ids) === 0 ) return array();
		//根据id列表获取好友列表
		$friends = model('User')->getUserInfoByUids($ids);
        if(!$friends) $friends = array();
        return $this->_to_buddies($friends);
    }

	/*
	 * User对象转化为Buddy对象
	 */
	private function _to_buddies($users, $group = "friend") {
		$buddies = array();
		foreach($users as $user) {
			$buddies[] = (object)array(
				'uid'		=> (string)$user['uid'],
				'id'		=> (string)$user['uid'],
				'group'		=> $group,
				'nick'		=> $user['uname'],
				'avatar' 	=> $user['avatar_small'],
				'url'		=> $user['space_url'],
				'status'	=> $user['intro'],
			);
		}
		return $buddies;
	}

	/*
	 * API：rooms of current user
     * 
     * @param string $uid 
     *
     * @return array rooms
     *
	 * Room:
	 *
	 *	id:		    Room ID,
	 *	nick:	    Room Nick
	 *	url:	    Home page of room
	 *	avatar:     Pic of Room
	 *	status:     Room status 
	 *	count:      count of online members
	 *	all_count:  count of all members
	 *	blocked:    true | false
	 */
	public function rooms($uid) {
		return array( );	
    }

	/*
	 * API: rooms by ids
     *
     * @param array id array
     *
     * @return array rooms
	 *
	 * Room
     *
	 */
	public function rooms_by_ids($uid, $ids) {
		return array( );	
    }

    /**
     * API: members of room
     *
     * $param $room string roomid
     * 
     */
    public function members($room) {
		return array( );	
    }


	/*
	 * API: notifications of current user
	 *
     * @return array  notification list
     *
	 * Notification:
	 *
	 * 	text: text
	 * 	link: link
	 */	
	public function notifications($uid) {
		$notices = array();
		$userCount = model('UserCount')->getUnreadCount($uid);
		if(!$userCount) $userCount = array();
		if ($userCount['unread_notify']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount['unread_notify'] . '</strong> 个系统消息'), 
				"link" => SITE_URL . "/index.php?app=public&mod=Message&act=notify");
		}
		if ($userCount['unread_message']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount["unread_message"] . '</strong> 个站内短消息'), 
				"link" => SITE_URL . "/index.php?app=public&mod=Message&act=index");
		}
		if ($userCount['unread_atme']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount["unread_atme"] . '</strong> 个好友@了你'),
				"link" => SITE_URL . "/index.php?app=public&mod=Mention&act=index");
		}
		if ($userCount['unread_comment']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount["unread_comment"] . '</strong> 评论'), 
				"link" => SITE_URL . "/index.php?app=public&mod=Comment&act=index&type=receive");
		}
		if($userCount['new_folower_count']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount['new_folower_count'] . '</strong>位新粉丝'),
				"link" => SITE_URL . "/index.php?app=public&mod=Index&act=follower&uid=" . $uid);	

		}
		return $notices;
    }

    /**
     * API: menu
     *
     * @return array menu list
     *
     * Menu:
     *
     * icon
     * text
     * link
     */
    public function menu($uid) {
		$apps = model('App')->getUserApp($uid);
		if(!$apps) $apps = array();
		$menu = array();
		foreach($apps as $app) {
			$menu[] = (object)array(
				'title' => $app['app_alias'],
				'icon' => $app['icon_url'],
				'link' => SITE_URL . "/index.php?app=" . $app['app_name'],
			);
		}
		return $menu;
    }

}

?>
