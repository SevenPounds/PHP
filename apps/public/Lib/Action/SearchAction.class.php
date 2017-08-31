<?php
/**
 * SearchAction 搜索模块
 * @version TS3.0
 */
class SearchAction extends Action {
	private $curApp  = '';
	private $curType = '';
	private $key 	 = '';
	private $tabkey  = '';
	private $tabvalue = '';
	private $searchModel = '';

	/**
	 * 模块初始化
	 * @return void
	 */
	public function _initialize() {
		$_GET 		= array_merge($_GET,$_POST);
		$this->curApp 	= $_GET['a'] ? strtolower(t($_GET['a'])):'public';
        //个人空间搜索好友页面,url后添加t=2跳转到不同的界面，此功能不再使用；
        //后台默认curType=0,即搜索好友页面update 2015.11.30
        $this->curType 	= 0;
		$this->key  	= str_replace('%','',t($_GET['k']));
		$this->tabkey	= t($_GET['tk']);
		$this->tabvalue = t($_GET['tv']);
		$this->searchModel = ucfirst($this->curApp).'Search';
		$this->assign('curApp',$this->curApp);
		$this->assign('curType',$this->curType);
		$this->assign('tabkey',$this->tabkey); 
		$this->assign('tabvalue',$this->tabvalue);
		$this->assign('keyword',$this->key);
		$this->assign('jsonKey',json_encode($this->key));
		
	}

	/**
	 * 根据关键字进行搜索
	 * @return void
	 */
	public function index() {
		if ( !CheckPermission('core_normal','search_info') ){
			$this->error('对不起，您没有权限进行该操作！');
		}
		$this->setTitle( '搜索'.$this->key );
		$this->setKeywords( '搜索'.$this->key );
		$this->setDescription( '搜索'.$this->key );

		if($this->curType == 2){     //搜索用户
			if($this->key != ""){
				if(t($_GET['Stime']) && t($_GET['Etime'])){
					$Stime = strtotime(t($_GET['Stime']));
					$Etime = strtotime(t($_GET['Etime']));
					$this->assign('Stime',t($_GET['Stime']));
					$this->assign('Etime',t($_GET['Etime']));
				}	
				//关键字匹配 采用搜索引擎兼容函数搜索 后期可能会扩展为搜索引擎
				$feed_type = !empty($_GET['feed_type']) ? t($_GET['feed_type']) : '';
				$list = model('Feed')->searchFeeds($this->key, $feed_type, 20, $Stime, $Etime);
				
				//赞功能
				$feed_ids = getSubByKey($list['data'],'feed_id');
				$diggArr = model('FeedDigg')->checkIsDigg($feed_ids, $GLOBALS['ts']['mid']);
				$this->assign('diggArr', $diggArr);
				
				$this->assign('feed_type',$feed_type);
				$this->assign('searchResult',$list);				 //搜索微博
				$weiboSet = model('Xdata')->get('admin_Config:feed');
				$this->assign('weibo_premission',$weiboSet['weibo_premission']);
			}
			$this->display('search_feed');

		}else{	
			if($this->key != ""){
				if($this->curType == 3){         //按标签搜索
					$data['name'] = $this->key;
					$tagid = D('tag')->where($data)->getField('tag_id');
					$maps['app'] = 'public';
					$maps['table'] = 'user';
					$maps['tag_id'] = $tagid;
					$user_ids = getSubByKey(D('app_tag')->where($maps)->field('row_id as uid')->order('row_id desc')->findAll(),'uid');
					$map['uid'] = array('in',$user_ids);
					$map['is_active'] = 1;
					$map['is_audit'] = 1;
					$map['is_init'] = 1;
					$userlist = D('user')->where($map)->field('uid')->findpage(10);
					foreach($userlist['data'] as &$v){
						$v = model('User')->getUserInfo($v['uid']);
						unset($v);
					}
				}else{
					$userlist = model('User')->searchUser($this->key, 0, 100, '' , '', 0, 10);
				}
				$uids = getSubByKey( $userlist['data'] , 'uid' );
				$usercounts = model('UserData')->getUserDataByUids( $uids );
				$userGids = model('UserGroupLink')->getUserGroup( $uids );
				$followstatus = model('Follow')->getFollowStateByFids($this->mid , $uids );
				
				$client = new CyClient();
				foreach($userlist['data'] as $k=>$v){
					$user = model('User')->getUser($v['uid']);
					$userlist['data'][$k]['userInfo'] = $user;
					$userlist['data'][$k]['usercount'] = $usercounts[$v['uid']];
					$userlist['data'][$k]['userTag'] = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags($v['uid']);
					// 获取用户用户组信息
					$userGroupData = model('UserGroup')->getUserGroupByGids($userGids[$v['uid']]);
					foreach($userGroupData as $key => $value) {
						if($value['user_group_icon'] == -1) {
							unset($userGroupData[$key]);
							continue;
						}
						$userGroupData[$key]['user_group_icon_url'] = THEME_PUBLIC_URL.'/image/usergroup/'.$value['user_group_icon'];
					}
					$userlist['data'][$k]['userGroupData'] = $userGroupData;
					//关注状态
					$userlist['data'][$k]['follow_state'] = $followstatus[ $v['uid'] ];
					
					// 获取用户积分信息
					$userlist['data'][$k]['userScore'] = model('Credit')->getUserCredit($v['uid']);
					// 获取相关的统计数目
					$userlist['data'][$k]['userData'] = model('UserData')->getUserData($v['uid']);
					//判断是否是学生或教师，若不是，则获取其机构名称
					$userRole = model('UserRole')->getUserRole($v['uid']);
					$userlist['data'][$k]['roleName'] = UserRoleTypeModel::getCNRoleName($userRole['rolename']);
					//判断是否是学生或者教师，如果不是，则查询其机构信息
					if($userlist['data'][$k]['roleName'] == "教师" || $userlist['data'][$k]['roleName'] == "学生"){
					}else{
							$eduorg = $client->listEduorgByUser($user['cyuid']);
							$userlist['data'][$k]['eduorgName'] = $eduorg[0]->eduorgName;
					}
				}
				$this->assign('searchResult',$userlist);
			}

			$this->display('search_user');
		}
	}

	/**
	 * 选择筛选时间
	 * @return void
	 */
	public function selectDate(){
		$this->assign('app',t($_GET['app']));
		$this->assign('mod',t($_GET['mod']));
		$this->assign('t',t($_GET['t']));
		$this->assign('a',t($_GET['a']));
		$this->assign('k',t($_GET['k']));
		$this->assign('feed_type',t($_GET['feed_type']));
		$this->display();
	}
	
	/**
	 * 模糊搜索标签
	 * @return mix 标签列表
	 */
	public function searchTag()	{
		$tagid = intval($_REQUEST['tagid']);
		$map['app'] = 'public';
		$map['table'] = 'user';
		$map['tag_id'] = $tagid;
		$userlist = D('app_tag')->where($map)->field('row_id')->order('row_id')->findpage(10);

		$where[] = "name LIKE '{$q}%'";
		$where = implode(' AND ', $where);
		$list = model('Tag')->getTagList($where, 'tag_id,tag_id as short,name,name as cn', null, $limit);
		if (!$list['data']) {
			$list['data'] = '';
		}
		exit(json_encode($list['data']));
	}

	/**
	 * 获取标签
	 * @return mix 标签信息
	 */
	public function getTag() {
		$data['name'] = t($_REQUEST['name']);
		$data['tag_id']   = model('Tag')->getTagId($data['name']);
		exit(json_encode($data));
	}
}