<?php
/**
 * Created by PhpStorm.
 * 提供给作业平台的服务接口
 * User: xypan
 * Date: 14-6-10
 * Time: 上午11:19
 * example:$client = new Work_Client('http://localhost/Space/api/work.php');
 */
class Work_Client{
    private $client = null;

    /**
     *
     * @param unknown_type $url 空间应用服务地址
     */
    function __construct($url){
        $this->client = new PHPRPC_Client($url);
    }

    /**
     * 通过授权码获取Token
     * @param string $appName 应用name
     * @param string $authcode 授权码
     * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
     */
    function getToken($appName, $authcode){
        return $this->client->getToken($appName, $authcode);
    }

    /**
     * 通过token获取用户
     * @param string $appName 应用name
     * @param string $token 令牌
     * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
     */
    function getUser($appName, $token){
        return $this->client->getUser($appName, $token);
    }

    /**
     * 通过用户ID查找学校信息
     * @param $cyuid 用户id
     * @param string $appName 应用name
     * @param string $token 令牌
     * @return string {"status":(返回状态), "message":(结果信息), "data":(学校信息)}
     */
    function getSchoolByUserId($cyuid,$appName, $token){
        return $this->client->getSchoolByUserId($cyuid,$appName, $token);
    }

    /**
     * 通过学生ID查找班级信息
     * @param $cyuid 用户id
     * @param string $appName 应用name
     * @param string $token 令牌
     * @return string {"status":(返回状态), "message":(结果信息), "data":(班级信息)}
     */
    function getClassByUserId($cyuid,$appName, $token){
        return $this->client->getClassByUserId($cyuid,$appName, $token);
    }

    /**
     * 通过班级ID查找老师信息
     * @param $classid 班级id
     * @param string $appName 应用name
     * @param string $token 令牌
     * @return string {"status":(返回状态), "message":(结果信息), "data":(老师信息)}
     */
    function getTeachersByClassId($classid,$appName, $token){
        return $this->client->getTeachersByClassId($classid,$appName, $token);
    }

    /**
     * 通过班级ID查找所有学生信息
     * @param $classid 班级id
     * @param string $appName 应用name
     * @param string $token 令牌
     * @return string {"status":(返回状态), "message":(结果信息), "data":(学生信息)}
     */
    function getStudentsByClassId($classid,$appName, $token){
        return $this->client->getStudentsByClassId($classid,$appName, $token);
    }

    /**
     * 通过学校ID查找到所有老师信息
     * @param $schoolid 学校id
     * @param string $appName 应用name
     * @param string $token 令牌
     * @return string {"status":(返回状态), "message":(结果信息), "data":(老师信息)}
     */
    function getTeachersBySchoolId($schoolid,$appName, $token){
        return $this->client->getTeachersBySchoolId($schoolid,$appName, $token);
    }

    /*
     * 通过家长ID查找到学生ID
     * @param $cyuid 用户id
     * @param string $appName 应用name
     * @param string $token 令牌
     * @return string {"status":(返回状态), "message":(结果信息), "data":(孩子信息)}
     */
    function getChildrenByParentId($parentid,$appName, $token){
        return $this->client->getChildrenByParentId($parentid,$appName, $token);
    }
}

define('SITE_PATH', dirname(dirname(__FILE__)));
require_once SITE_PATH . '/core/core.php';

$client = new Work_Client('http://localhost/sns/api/work.php');

//$authcode = D('AppAuth')->getAuthcode('grmh','4587412546321456982_p');
//echo $authcode;

//$cyClient = D('CyCore')->CyCore;
//$result = $cyClient->getUserByUniqueInfo('login_name','4587412546321456982');

//$result = $client->getUser('grmh','144ca7785d1f002350a886e1fe397545');
//
//$result = $client->getClassByUserId('2000000017000004118','grmh', 'fa88f06140056f48c43efc399697683a');
$result = $client->getChildrenByParentId('2000000017000004124','grmh','20f1bca3240993bedcafbed4be49c59c');
//$result = $client->getTeachersBySchoolId('2000000017000000245','grmh','20f1bca3240993bedcafbed4be49c59c');
echo $result;