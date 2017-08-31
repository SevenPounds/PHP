<?php
class ManageAction extends BaseAction {
	var $group;
	function _initialize() {
		parent::_initialize();
		if (!$this->isadmin)
			$this->error('您没有权限');

		$this->group = D('Group');
	}

	//基本设置  修改
	public function index()	{
		if(isset($_POST['editsubmit'])) {
			$group['name']  = h(t($_POST['name']));
			$group['intro'] = h(t($_POST['intro']));
			$group['cid0']  = intval($_POST['cid0']);
			$cid1 = D('Category')->_digCateNew($_POST);
			intval($cid1) > 0 && $group['cid1'] = intval($cid1);

			if (!$group['name'])
				$this->error('圈子名称不能为空');
			else if (get_str_length($group['name']) > 30)
				$this->error('圈子名称不能超过30个字');

			if (D('Category')->getField('id', 'name=' . $group['name'])) {
				$this->error('请选择圈子分类');
			}
			if (get_str_length($group['intro']) > 200) {
				$this->error('圈子简介请不要超过200个字');
			}

			$group['name_origin'] = $group["name"];
			$sensitiveWord = $this->sensitiveWord_svc;
			$nameReplace = $sensitiveWord->checkSensitiveWord($group['name']);
			$nameReplace = json_decode($nameReplace,true);
			if($nameReplace["Code"]!=0){
				return;
			}
			$group['name'] = $nameReplace['Data'];

			$group['intro_origin'] = $group["intro"];
			$introReplace = $sensitiveWord->checkSensitiveWord($group['intro']);
			$introReplace = json_decode($introReplace,true);
			if($introReplace["Code"]!=0){
				return;
			}
			$group["intro"] = $introReplace["Data"];

        	if($_FILES['logo']['size'] > 0 && is_image_file($_FILES['logo']['name']) ) {
				// 群组LOGO
				$options['allow_exts']	=	'jpg,gif,png,jpeg,bmp';
				$options['max_size'] = 2 * 1024 *1024;
				$options['attach_type'] = 'group_logo';
				$data['upload_type'] = 'image';
		        $info	=	model('Attach')->upload($data,$options);
			    if($info['status']) {
				    $group['logo'] = $info['info'][0]['save_path'] . $info['info'][0]['save_name'];
			    }
		    }

			$res = $this->group->where('id=' . $this->gid)->save($group);

			if ($res !== false) {
                $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在兴趣圈子修改了圈子“".$this->groupinfo["name_origin"]."”的基本信息";
                $logObj = $this->getLogObj(C("appType")["hdjl"]["code"],C("appId")["xqqz"]["code"],C("opType")["update"]['code'],$_POST["gid"],C("location")["panServer"]['code'],"","",$this->groupinfo["name_origin"],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
                Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

				D('Log')->writeLog($this->gid, $this->mid, '修改圈子基本信息', 'setting');

				$this->assign('jumUrl', U('group/Manage/index', array('gid'=>$this->gid)));
				$this->success('保存成功');
			}
			$this->error('保存失败');
		}

		$this->assign('current', 'basic');
		$this->display();
	}

	//访问权限
	public function privacy() {
		if (!iscreater($this->mid,$this->gid)) {
			$this->error('创建者才有的权限');  //创建者才可以修改配置
		}

		if(isset($_POST['editsubmit'])){
			$groupinfo['brower_level'] = ($_POST['brower_level'] == 1)?intval($_POST['brower_level']):-1;
			$groupinfo['type'] 		   = ($groupinfo['brower_level'] == 1)?'close':'open';
			$groupinfo['need_invite'] = ($_POST['need_invite'] == 1 || $_POST['need_invite'] == 2)?intval($_POST['need_invite']):0;
			$groupinfo['openWeibo'] = ($_POST['openWeibo'] == 0)?intval($_POST['openWeibo']):1;
			$groupinfo['openBlog'] = ($_POST['openBlog'] == 0)?intval($_POST['openBlog']):1;
			$groupinfo['openUploadFile'] = ($_POST['openUploadFile'] == 0)?intval($_POST['openUploadFile']):1;
			$groupinfo['whoUploadFile'] = ($_POST['whoUploadFile'] == 2)?intval($_POST['whoUploadFile']):3;
			$groupinfo['whoDownloadFile'] = ($_POST['whoDownloadFile'] == 0 || $_POST['whoDownloadFile'] == 2)?intval($_POST['whoDownloadFile']):3;
			$res = $this->group->where('id=' . $this->gid)->save($groupinfo);

			if ($res !== false) {
				D('Log')->writeLog($this->gid, $this->mid, '修改圈子访问权限', 'setting');

				$this->assign('jumUrl', U('group/Manage/privacy', array('gid'=>$this->gid)));
				$this->success('保存成功');
			} else {
				$this->error('保存失败');
			}
		}
		$this->assign('current','privacy');
		$this->display();
	}

	//成员管理
	public function membermanage()	{
		$type = (isset($_GET['type']) && in_array($_GET['type'], array('manage','apply')))?$_GET['type']:'';
		if ('' == $type || 'apply' == $type) {
			$memberlist = D('Member')->where("gid={$this->gid} AND level=0")->order('level ASC')->findPage();
			$type = 'apply';
		}
		if ('manage' == $type || (!$memberlist['data'] && 'apply' != $_GET['type'])) {
			$memberlist = D('Member')->where("gid={$this->gid} AND level>0")->order('level ASC')->findPage();
			$type = 'manage';
		}

		/*
    	 * 缓存当前页用户信息
    	 */
    	$ids = getSubByKey($memberlist['data'], 'uid');
		$this->assign('memberlist',$memberlist);
		$this->assign('iscreater',iscreater($this->mid,$this->gid));
		$this->assign('current','membermanage');
		$this->assign('type', $type);

		if('apply' == $type) {
			$this->display('memberapply');
			exit;
		}else{
			$this->display();
		}
	}

	//操作：设置成管理员，降级成为普通会员，剔除会员，允许成为会员
	public function memberaction() {
		$batch = false;
		$uidArr = explode(',', $_POST['uid']);
		if(is_array($uidArr)) {
			$batch = true;
		}

		if(!isset($_POST['op']) || !in_array($_POST['op'],array('admin','normal','out','allow'))) exit();

		switch ($_POST['op'])
		{
			case 'admin':  // 设置成管理员
				if (!iscreater($this->mid,$this->gid)) {
					$this->error('创建者才有的权限');  // 创建者才可以进行此操作
				}
				if($batch) {
					$uidStrLog = array();
					foreach($uidArr as $val) {
						$uidInfo = getUserSpace($val, 'fn', '_blank', '@' . getUserName($val));
						array_push($uidStrLog, $uidInfo);
					}
					$uidStr = implode(',', $uidStrLog);
					$content = '将用户 '.$uidStr.'提升为管理员 ';
					$res = D('Member')->where('gid=' . $this->gid . ' AND uid IN ('.$_POST['uid'].') AND level<>1')->setField('level', 2);   //3 普通用户
				} else {
					$content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '提升为管理员 ';
					$res = D('Member')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . ' AND level<>1')->setField('level', 2);   //3 普通用户
				}
				break;
			case 'normal':   // 降级成为普通会员
				if (!iscreater($this->mid,$this->gid)) {
					$this->error('创建者才有的权限');  // 创建者才可以进行此操作
				}
				$content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '降为普通会员 ';
				$res = D('Member')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . ' AND level=2')->setField('level', 3);   //3 普通用户
				break;
			case 'out':     // 剔除会员
				if (iscreater($this->mid, $this->gid)) {
					$level = ' AND level<>1';
				} else {
					$level = ' AND level<>1 AND level<>2';
				}
				if($batch) {
					$current_level = D('Member')->field('uid, level')->where('gid = '.$this->gid.' AND uid IN ('.$_POST['uid'].')'.$level)->findAll();
					$res = D('Member')->where('gid='.$this->gid.' AND uid IN ('.$_POST['uid'].')'.$level)->delete();
					if($res) {
						$count = count($current_level);
						$uidStrLog = array();
						foreach($current_level as $value) {
							$uidInfo = getUserSpace($value['uid'], 'fn', '_blank', '@' . getUserName($value['uid']));
							array_push($uidStrLog, $uidInfo);
							if($value['level'] > 0) {
								D('Group')->setDec('membercount', 'id=' . $this->gid);
								X('Credit')->setUserCredit($value['uid'], 'quit_group');
							}
						}
						$uidStr = implode(',', $uidStrLog);
						$content = '将用户 '.$uidStr. '踢出圈子 ';
					}
				} else {
					$current_level = D('Member')->getField('level', 'gid=' . $this->gid . ' AND uid=' . $this->uid . $level);
					$res = D('Member')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . $level)->delete();   //剔除用户
					if ($res) {
						$content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '踢出圈子 ';
						// 被拒绝加入不扣积分
						if (intval($current_level) > 0) {
							D('Group')->setDec('membercount', 'id=' . $this->gid);     //用户数量减少1
							X('Credit')->setUserCredit($this->uid, 'quit_group');
						}
					}
				}
				break;
			case 'allow':   // 批准成为会员
				$content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '批准成为会员 ';
				$res = D('Member')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . ' AND level=0')->setField('level', 3);   //level级别由0 变成 3
				if ($res) {
					D('Group')->setInc('membercount', 'id=' . $this->gid); //增加一个成员
					X('Credit')->setUserCredit($this->uid, 'join_group');
				}
				break;
		}

		if ($res) {
			D('Log')->writeLog($this->gid, $this->mid, $content, 'member');
		}
        $legalUrls = C('LEGAL_URL');
        $isLegal = false;
        $tourl = $_SERVER['HTTP_REFERER'];
        foreach($legalUrls as $legalUrl){
            if(strstr($tourl,$legalUrl)){
                $isLegal = true; break;
            }
        }
        if($isLegal){
            header('Location:'.$tourl);
        }
	}

	//群公告
	public function announce()	{
		if (isset($_POST['editsubmit'])) {
			$groupinfo['announce'] = t(getShort($_POST['announce'], 200));
			$groupinfo['announce_origin'] = $groupinfo["announce"];
			$sensitiveWord = $this->sensitiveWord_svc;
			$announceReplace = $sensitiveWord->checkSensitiveWord($groupinfo['announce']);
			$announceReplace = json_decode($announceReplace,true);
			if($announceReplace["Code"]!=0){
				return;
			}
			$groupinfo['announce'] = $announceReplace["Data"];

			$res = $this->group->where('id='.$this->gid)->save($groupinfo);

			if ($res !== false) {

                $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在兴趣圈子发布了“".$groupinfo['announce_origin']."”的公告";
                $logObj = $this->getLogObj(C("appType")["hdjl"]["code"],C("appId")["xqqz"]["code"],C("opType")["create"]['code'],C("location")["panServer"]['code'],"","","",$groupinfo['announce_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
                Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));


				$log = empty($groupinfo['announce']) ? '清除公告' : "发布公告: {$groupinfo['announce']}";
				D('Log')->writeLog($this->gid, $this->mid, $log, 'setting');
				$this->assign('jumUrl', U('group/Manage/announce', array('gid'=>$this->gid)));
				$this->success('保存成功');
			} else {
				$this->error('保存失败');
			}
		}
		$this->assign('current','announce');
		$this->display();
	}
	/**
	 * 检查圈子名称是否重复
	 */
	public function isRepeat(){
		$group_name =$_REQUEST['name'];
		if($this->groupinfo['name'] ==$group_name){
			$returns['status']=0;
		}else{			
		$counts =$this->group->where(array("name"=>$group_name,'is_del'=>0))->count();
		if($counts>0){
			$returns["status"]=1;
		}else{
			$returns["status"]=0;
		}}
		echo json_encode($returns);
		
	}
	
}