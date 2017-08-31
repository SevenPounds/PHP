<?php
/**
 * 个人桌面控制器
 * @author frsun
 * @version 1.0
 */
class PersonDeskAction extends Action {

	protected function _initialize() {    
		if(!in_array($this->roleEnName,array("teacher","student","instructor"))){
			U('public/Index/index','',true);
		}

	}


	public function index(){
		$epspSvc = new \EpspClient();
        $loginName=$GLOBALS['ts']['_user']['login'];
        $pageSize=8;
        $pageIndex=0;

        //取用户的基本信息
        $value  =$epspSvc->getUserResInfomation($loginName,$pageIndex,$pageSize);
        $value= json_decode($value , true);
        if($value ['Code']==0) {
            $resultData = $value['Data']['userInfo'];
            //云盘比例
            $scale=round($resultData['panUsedSize']/1024*10,2);
            $temp = array();
            for($i=0;$i<count($resultData['phase']);$i++){
                $temp_in = array();
                $temp_in[0] = $resultData['subject'][$i];
                $temp_in[1] = $resultData['phase'][$i];
                $temp[] = $temp_in;
            }
            $phaseSubjectList = $temp;
            $this->assign("list",$phaseSubjectList);
            $this->assign("scale",$scale);
            $this->assign("data",$resultData);
            //教案资源信息(包括资源总数，资源列表，资源下载量)
            $teachingPlanRes=$value['Data']['recommendInfo']['teachingPlanRes'];
            $this->assign('teachingPlanRes', $teachingPlanRes);

            //  课件资源信息(包括资源总数，资源列表，资源下载量)
            $coursewareRes=$value['Data']['recommendInfo']['coursewareRes'];
            $this->assign('coursewareRes', $coursewareRes);

            //习题资源信息(包括资源总数，资源列表，资源下载量)
            $exerciseRes=$value['Data']['recommendInfo']['exerciseRes'];
            $this->assign('exerciseRes', $exerciseRes);

            // 素材资源信息(包括资源总数，资源列表，资源下载量)
            $materialRes=$value['Data']['recommendInfo']['materialRes'];
            $this->assign('materialRes', $materialRes);

            //微课资源信息(包括资源总数，资源列表，资源下载量)
            $microClassRes=$value['Data']['recommendInfo']['microClassRes'];
            $this->assign('microClassRes', $microClassRes);

            //资源套餐资源信息(包括资源总数，资源列表，资源下载量)
            $resPlanRes=$value['Data']['recommendInfo']['resPlanRes'];
            $this->assign('resPlanRes', $resPlanRes);

            //获取热门网课信息(包括id-评课id, uid-用户id ,title-网课标题, uname-用户名称,discuss_count-评论数)
            $pingKeList=$value['Data']['recommendInfo']['pingKeList'];
            $this->assign('pingKeList', $pingKeList);

            //获取热门日志信息(包括id-日志id, uid-用户id ,title-日志标题, uname-用户名称, readCount-浏览数，content-日志内容)
            $blogList=$value['Data']['recommendInfo']['blogList'];
            $this->assign('blogList', $blogList);

            //获取热门关注信息(包括uname-用户名, uid-用户id, followCounts-关注数, userAvatarTiny-用户头像)
            $userList=$value['Data']['recommendInfo']['userList'];
            $this->assign('userList', $userList);

        };
        //应用分类
        $appType= $epspSvc->getAppcenterAppCategory(1,99);
        $appType= json_decode($appType , true);
        if($appType['Code']==0){
            $appTypes=$appType['Data'];
            $count=count($appTypes);
            $this->assign('num', $count);
            $this->assign('appsType', $appTypes);

        }
        $this->assign('user_info',$GLOBALS['ts']['_user']);
        $this->display();

	}
	
    function getAppList(){
        $epspSvc = new \EpspClient();
	    $type = isset($_POST['app_id'])?$_POST['app_id']:'';
        $loginName=$GLOBALS['ts']['_user']['login'];
        $appConfig = include_once('./apps/appcenter/Conf/app.config.php');
	//该用户某分类下的应用
      $apps= $epspSvc->getUserapps($loginName,$type,1,9);
      $apps= json_decode($apps , true);
      if($apps['Code']==0){
          $appes=$apps['Data'];
          $appesLen = count($appes);
          for($i=0;$i<$appesLen;$i++){
              if ($appes[$i]['appEnName'] == 'zhixuewang') {
                  $serviceUrl =$appConfig['apps'][$appes[$i]['appEnName']]['url'];
                  $domain =C('domain');
                  $appid =C('appid');
                  $expires = time();
                  $time=time();
                  $sessionkey=C('sessionkey');
                  $uid=$GLOBALS['ts']['user']['cyuid'];
                  $signStr = 'appid='.$appid.'domain='.$domain.'expires='.$expires.'iframe=1sessionkey='.$sessionkey.'time='.$time.'user='.$uid.'暂不定义，最好采用毫无意义的随机串';
                  $sign = md5($signStr);
                  $url = $serviceUrl . 'iframe=1&time='.$time.'&appid='.$appid.'&domain='.$domain.'&user='.$uid.'&sessionkey='.$sessionkey.'&expires='.$expires.'&sig=' . $sign;
                  $appes[$i]['url'] = $url;
              } else {
                  $appes[$i]['url']=$appConfig['apps'][$appes[$i]['appEnName']]['url'];
              }

          }
    
          $this->assign('app', $appes);
       }
	$this->display('_ajaxApps');
	}
}