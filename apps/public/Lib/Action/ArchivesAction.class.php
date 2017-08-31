<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2015/12/16
 * Time: 13:28
 */

class ArchivesAction extends Action {

    public function _initialize() {

    }

    public  function  index(){
        if(empty($this->user['uname'])) {
            redirect(C('LOGIN_URL'));
        }
        $roleName=$this->roleEnName;
        if($roleName=='teacher'){//老师通过汇聚也打开学生的档案
            $studentId = $_REQUEST['studentId'];
        }else if($roleName=='student'){//学生打开自己的档案  lt2---1000000001000000002     lt3---2000000001000000003
            $studentId = $this->user['cyuid'];
        }
        $domain=C("CENTRE_DOMAIN");
        $archives_url=C('ARCHIVES_URL');
        $iframeUrl = $this->getCenterIframeUrl($archives_url,'changyanyun', 'rrt', $domain,$this->user['login']);
        $iframeUrl .= '&studentId='. $studentId;
        $this->assign("url", $iframeUrl);
        $this->display();
    }

    /**
     * 工具类 获取token和url
     */
    public function getCenterIframeUrl($iframe_url, $accessToken, $appkey,$domain, $userName){
        $param = "&access_token=$accessToken&appkey=$appkey&timestamp=".time()."&domain=$domain&userName=$userName&from=rrt";
        $gen_url = C('CENTRE_GEN_URL').$param;
        //获取中心化token
        $sign = \Httpful\Request::get($gen_url)->send()->body;
        $param .= '&sign='.$sign;
        if(strpos($iframe_url, "?")){
            $iframe_url .= $param;
        }else{
            $iframe_url .= '?' . $param;
        }
        return $iframe_url;
    }
} 