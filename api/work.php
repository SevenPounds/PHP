<?php
/**
 * Created by PhpStorm.
 * 提供给作业平台的服务接口
 * User: xypan
 * Date: 14-6-10
 * Time: 上午11:19
 */
define('SITE_PATH', dirname(dirname(__FILE__)));


require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';

date_default_timezone_set('PRC');

/**
 * cyore  用户转换为 eduspace 用户
 * @param object $cyuser
 * return array
 */
function user_convert($cyuser){
    $user  = array();
    $user['cyuid'] = $cyuser->id;
    $user['uname'] =  $cyuser->userName;
    $user['login'] =  $cyuser->loginName;
    $user['phone'] = $cyuser->phone;
    $user['email'] = $cyuser->email;
    $user['pinyinName'] = $cyuser->pinyinName;
    $user['isdel'] = $cyuser->delFlag;
    $user['sex'] = $cyuser->gender==1 ? $cyuser->gender:2;
    $user['updatetime'] = empty($cyuser->updateTime) ? date("Y-m-d H:i:s") : $cyuser->updateTime;
    $user['createtime'] = $cyuser->createTime;
    return $user;
}

/**
 * 通过授权码获取Token
 * @param string $appName 应用name
 * @param string $authcode 授权码
 * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
 */
function getToken($appName, $authcode){
    $token = D("AppAuth")->getToken($appName, $authcode);

    if(empty($token)){
        $result['status'] = '500';
        $result['message'] = '令牌获取失败';
        $result['data'] = '';
    }else{
        $result['status'] = '200';
        $result['message'] = '令牌获取成功';
        $result['data'] = $token;
    }

    return json_encode($result);
}

/**
 * 通过token获取用户
 * @param string $appName 应用name
 * @param string $token 令牌
 * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
 */
function getUser($appName, $token){
    $flag = D("AppAuth")->validateToken($appName, $token);
    if(!$flag){
        $result['status'] = '500';
        $result['message'] = '令牌失效';
        $result['data'] = '';
    }

    $user = D("AppAuth")->getUser($appName, $token);
    if(empty($user)){
        $result['status'] = '500';
        $result['message'] = '用户信息获取失败';
        $result['data'] = '';
    }else{
        $rolelist = $user['rolelist'];
        unset($user['rolelist']);
      /*   foreach($rolelist as $value){
            if(in_array($value['name'],array('teacher','student','parent'))){
                $user['user']['role'] = $value['name'];
                break;
            }
        } */
        //改为多角色下，取当前用户使用的角色
        $roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $rolelist);
        if(in_array($roleEnName,array('teacher','student','parent','instructor','eduadmin'))){
        	$user['user']['role'] = $roleEnName;
        }
        if(!isset($user['user']['role'])){
            $user['user']['role'] = '';
        }

        foreach($user['orglist']['school'] as $value){
            $school = $value;
            break;
        }
        unset($user['orglist']['school']);
        $user['orglist']['school'] = $school;

        unset($user['orglist']['class']);

        $cyClient = D('CyCore')->CyCore;
        $class = $cyClient->getClassByUserId($user['user']['cyuid']);

        if(count($class) == 1 && $user['user']['role'] != 'teacher'){
            $class = $class[0];
        }

        $user['orglist']['class'] = $class;

        $result['status'] = '200';
        $result['message'] = '用户信息获取成功';
        $result['data'] = $user;
    }

    return json_encode($result);
}

/**
 * 通过用户ID查找学校信息
 * @param $cyuid 用户id
 * @param string $appName 应用name
 * @param string $token 令牌
 * @return string {"status":(返回状态), "message":(结果信息), "data":(学校信息)}
 */
function getSchoolByUserId($cyuid,$appName, $token){
    $result = array();
    if(empty($cyuid)){
        $result['status'] = '500';
        $result['message'] = '查询的用户Id不能为空';
        $result['data'] = '';

        return json_encode($result);
    }

    $flag = D("AppAuth")->validateToken($appName, $token);
    if(!$flag){
        $result['status'] = '500';
        $result['message'] = '令牌失效';
        $result['data'] = '';
    }

    $cyClient = D('CyCore')->CyCore;
    $userInfo = $cyClient->getUserDetailById($cyuid);
    if($userInfo){
        $result['status'] = '200';
        $result['message'] = '查询成功';
        $result['data'] = $userInfo->schoolData;
    }else{
        $result['status'] = '404';
        $result['message'] = '查询的用户不存在';
        $result['data'] = '';
    }
    return json_encode($result);
}

/**
 * 通过学生ID查找班级信息
 * @param $cyuid 用户id
 * @param string $appName 应用name
 * @param string $token 令牌
 * @return string {"status":(返回状态), "message":(结果信息), "data":(班级信息)}
 */
function getClassByUserId($cyuid,$appName, $token){
    $result = array();
    if(empty($cyuid)){
        $result['status'] = '500';
        $result['message'] = '查询的用户Id不能为空';
        $result['data'] = '';

        return json_encode($result);
    }

    $flag = D("AppAuth")->validateToken($appName, $token);
    if(!$flag){
        $result['status'] = '500';
        $result['message'] = '令牌失效';
        $result['data'] = '';
    }

    $cyClient = D('CyCore')->CyCore;
    $class = $cyClient->getClassByUserId($cyuid);
    if($class){
        $result['status'] = '200';
        $result['message'] = '查询成功';
        $result['data'] = $class;
    }else{
        $result['status'] = '404';
        $result['message'] = '查询的用户不存在';
        $result['data'] = '';
    }
    return json_encode($result);
}

/**
 * 通过班级ID查找老师信息
 * @param $classid 班级id
 * @param string $appName 应用name
 * @param string $token 令牌
 * @return string {"status":(返回状态), "message":(结果信息), "data":(老师信息)}
 */
function getTeachersByClassId($classid,$appName, $token){
    $result = array();
    if(empty($classid)){
        $result['status'] = '500';
        $result['message'] = '查询的班级Id不能为空';
        $result['data'] = '';

        return json_encode($result);
    }

    $flag = D("AppAuth")->validateToken($appName, $token);
    if(!$flag){
        $result['status'] = '500';
        $result['message'] = '令牌失效';
        $result['data'] = '';
    }

    $cyClient = D('CyCore')->CyCore;
    $teachers = $cyClient->listUserByClass($classid,'teacher',0,200);
    if($teachers){
        foreach($teachers as $key=>$value){
            $teachers[$key] = user_convert($value);
        }
        $result['status'] = '200';
        $result['message'] = '查询成功';
        $result['data'] = $teachers;
    }else{
        $result['status'] = '404';
        $result['message'] = '查询的班级不存在';
        $result['data'] = '';
    }
    return json_encode($result);
}

/**
 * 通过班级ID查找所有学生信息
 * @param $classid 班级id
 * @param string $appName 应用name
 * @param string $token 令牌
 * @return string {"status":(返回状态), "message":(结果信息), "data":(学生信息)}
 */
function getStudentsByClassId($classid,$appName, $token){
    $result = array();
    if(empty($classid)){
        $result['status'] = '500';
        $result['message'] = '查询的班级Id不能为空';
        $result['data'] = '';

        return json_encode($result);
    }

    $flag = D("AppAuth")->validateToken($appName, $token);
    if(!$flag){
        $result['status'] = '500';
        $result['message'] = '令牌失效';
        $result['data'] = '';
    }

    $cyClient = D('CyCore')->CyCore;
    $students = $cyClient->listUserByClass($classid,'student',0,200);
    if($students){
        foreach($students as $key=>$value){
            $students[$key] = user_convert($value);
        }
        $result['status'] = '200';
        $result['message'] = '查询成功';
        $result['data'] = $students;
    }else{
        $result['status'] = '404';
        $result['message'] = '查询的班级不存在';
        $result['data'] = '';
    }
    return json_encode($result);
}

/**
 * 通过学校ID查找到所有老师信息
 * @param $schoolid 学校id
 * @param string $appName 应用name
 * @param string $token 令牌
 * @return string {"status":(返回状态), "message":(结果信息), "data":(老师信息)}
 */
function getTeachersBySchoolId($schoolid,$appName, $token){
    $result = array();
    if(empty($schoolid)){
        $result['status'] = '500';
        $result['message'] = '查询的学校Id不能为空';
        $result['data'] = '';

        return json_encode($result);
    }

    $flag = D("AppAuth")->validateToken($appName, $token);
    if(!$flag){
        $result['status'] = '500';
        $result['message'] = '令牌失效';
        $result['data'] = '';
    }

    $cyClient = D('CyCore')->CyCore;
    $teachers = $cyClient->listUserBySchool($schoolid,'teacher',0,500);
    if($teachers){
        foreach($teachers as $key=>$value){
            $teachers[$key] = user_convert($value);
        }
        $result['status'] = '200';
        $result['message'] = '查询成功';
        $result['data'] = $teachers;
    }else{
        $result['status'] = '404';
        $result['message'] = '查询的学校不存在';
        $result['data'] = '';
    }
    return json_encode($result);
}


/**
 * 通过家长ID查找到学生ID
 * @param $cyuid 用户id
 * @param string $appName 应用name
 * @param string $token 令牌
 * @return string {"status":(返回状态), "message":(结果信息), "data":(孩子信息)}
 */
function getChildrenByParentId($cyuid,$appName, $token){
    $result = array();
    if(empty($cyuid)){
        $result['status'] = '500';
        $result['message'] = '查询的用户Id不能为空';
        $result['data'] = '';

        return json_encode($result);
    }

    $flag = D("AppAuth")->validateToken($appName, $token);
    if(!$flag){
        $result['status'] = '500';
        $result['message'] = '令牌失效';
        $result['data'] = '';
    }

    $cyClient = D('CyCore')->CyCore;
    $children = $cyClient->listChildren($cyuid);
    if($children){

        foreach($children as $key=>$value){
            $temp = $value;
            $children[$key] = array();
            $children[$key]['user'] = user_convert($temp);

            $cyClient = D('CyCore')->CyCore;

            // 以前获取用户的角色
            $role = $cyClient->listRoleByUser($children[$key]['user']['cyuid']);
            // 获取用户身份映射到角色
//            $role = array();
//            $client = new \CyClient();
//            $userTypeInfo = $client->getListUserReviewByUserId($cyuid);
//            $roleTypeArr = array(
//                "001"=>"orger",
//                "002"=>"teacher",
//                "003"=>"student",
//                "004"=>"parent"
//            );
//            // 将身份映射为角色名
//            $userTypeInfoLen = count($userTypeInfo);
//            for($i=0;$i<$userTypeInfoLen;$i++){
//                if($userTypeInfo[$i]->reviewStatus=='110002'||$userTypeInfo[$i]->reviewStatus==null){
//                    $everyRole = new \stdClass();
//                    $everyRole->enName = $roleTypeArr[$userTypeInfo[$i]->userType];
//                    $role[] = $everyRole;
//                }
//            }


            if(empty($role)){
                $role = '';
            }else{
            	//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
            	$role = D("UserLoginRole")->getUserCurrentRole($children[$key]['user']['login'], $role);
            }
            $children[$key]['user']['role'] = $role;

            $class = $cyClient->getClassByUserId($children[$key]['user']['cyuid']);

            if(count($class) == 1){
                $class = $class[0];
            }
            $children[$key]['orglist']['class'] = $class;

            $school = $cyClient->listSchoolByUser($children[$key]['user']['cyuid']);
            if(!empty($school)){
                $school = $school[0];
            }
            $children[$key]['orglist']['school'] = $school;

        }

        $result['status'] = '200';
        $result['message'] = '查询成功';
        $result['data'] = $children;
    }else{
        $result['status'] = '404';
        $result['message'] = '查询的用户不存在';
        $result['data'] = '';
    }
    return json_encode($result);
}

$server = new PHPRPC_Server();
$server->add('getToken');
$server->add('getUser');
$server->add('getSchoolByUserId');
$server->add('getClassByUserId');
$server->add('getTeachersByClassId');
$server->add('getStudentsByClassId');
$server->add('getTeachersBySchoolId');
$server->add('getChildrenByParentId');
$server->start();