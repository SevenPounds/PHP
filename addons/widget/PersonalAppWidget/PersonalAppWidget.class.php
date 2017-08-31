<?php
/**
 * 个人应用widget
 * @author dingyf
 * @version TS3.0
 */

class PersonalAppWidget extends Widget {
    /**
     * @param array 传递参数 
     * @param string 页面html
     */
    public function render($data) {
        $appConfig = include_once('./apps/appcenter/Conf/app.config.php');
        // 根据角色获取我的应用
        $app_mine = $appConfig[$this->roleEnName]['app_mine'];
        $var['app_mine'] = $app_mine;
        $bindUser = array();
        if($this->roleEnName == UserRoleTypeModel::RESAERCHER || $this->roleEnName == UserRoleTypeModel::TEACHER){
            $userId = $this->user['cyuid'];
            $bindUser = D('LocalCyUser')->getBindAdminByUser($userId);
            if($bindUser){
                $var['app_mine']['htgl'] = $appConfig['htgl'];
            }
        }
        
        $app_space = $appConfig[$this->roleEnName]['app_space'];
        $var['app_space'] = $app_space;
        
        if($this->roleEnName == UserRoleTypeModel::TEACHER){
            
            $app_jx = $appConfig[$this->roleEnName]['app_jx'];
            $var['app_jx'] = $app_jx;
            $app_jy = $appConfig[$this->roleEnName]['app_jy'];
            $var['app_jy'] = $app_jy;
        }
        if($this->roleEnName == UserRoleTypeModel::STUDENT){
                
            $app_xx = $appConfig[$this->roleEnName]['app_xx'];
            $var['app_xx'] = $app_xx;
        }
        if($this->roleEnName == UserRoleTypeModel::PARENTS){
                
            $app_jz = $appConfig[$this->roleEnName]['app_jz'];
            $var['app_jz'] = $app_jz;
        }
        
        /* $role_apps = $appConfig[$this->roleEnName]['app_space'];
        $app_space = D("AppcenterUserapps","appcenter")->getUserApps($GLOBALS['ts']['user']['login']);
        foreach($app_space as $key=>&$app){
            $app['url'] = $role_apps[$app['app_en_name']]['url'];
            $app['target'] = $role_apps[$app['app_en_name']]['target'] ? true : false;
        }
        $var['app_space'] = $app_space; */
        $is_SP = Model('CyUser')->hasRole(UserRoleTypeModel::STUDENT,$GLOBALS['ts']['cyuserdata']['user']['cyuid'])||Model('CyUser')->hasRole(UserRoleTypeModel::PARENTS,$GLOBALS['ts']['cyuserdata']['user']['cyuid']);
        $tpl = $is_SP ? '/student_applist.html':'/applist.html';
        if(!$is_SP){
            // 获取名师工作室
            $client = new PHPRPC_Client(C("WORKROOM_SITE_URL") . "/index.php?m=Home&c=Server");
            $ms = $client->getMsgroupByUniqueInfo("loginName",$GLOBALS['ts']['user']['login']);
            $var['msGroups'] = $ms;
            $var['roleEnName'] = $this->roleEnName;
        }
        //增加应用能手大赛开关控制
        if(C('YYNSDS_STATE')=='OFF'){
            unset($var['app_mine']['yynsds']);
        }
        $content = $this->renderFile(dirname(__FILE__) . $tpl, $var);
        return $content;
    }
}
