<?php
/*
* 资源平台用户积分操作 相关服务
*/

define('SITE_PATH', dirname(dirname(__FILE__)));
$_GET['app'] = 'api';
$_GET['mod'] = 'Credit';
$_GET['act'] = 'index';


require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';

/**
 * 根据用户名获取用户经验信息
 * 返回积分值的数据结构
 * <code>
 * array(
 * 'score' =>array(
 * 'credit'=>'1',
 * 'alias' =>'积分',
 * ),
 * 'experience'=>array(
 * 'credit'=>'2',
 * 'alias' =>'经验',
 * ),
 * '类型' =>array(
 * 'credit'=>'值',
 * 'alias' =>'名称',
 * ),
 * )
 * </code>
 * @param int $uid 用户登录名
 */
function getExperienceByUsername($username){
	$syscredit=D("Credit");
	$userInfo=D("User");

	$userInfo = $userInfo->getUserInfoByLogin($username);		
	return $syscredit->getUserCredit($userInfo['uid']);
}

/**
 * 根据用户id获取用户经验信息
 * 返回积分值的数据结构
 * <code>
 * array(
 * 'score' =>array(
 * 'credit'=>'1',
 * 'alias' =>'积分',
 * ),
 * 'experience'=>array(
 * 'credit'=>'2',
 * 'alias' =>'经验',
 * ),
 * '类型' =>array(
 * 'credit'=>'值',
 * 'alias' =>'名称',
 * ),
 * )
 * </code>
 * @param int $uid 用户id
 */
function getExperienceByUid($uid){
	$syscreditD=D("Credit");

	return $syscreditD->getUserCredit($uid);
}

/**
 * 根据用户名查找该用户的积分
 * @param string $username
 */
function getCreditByUsername($username){
	$creditD = D("UserCredit","api");
	return $creditD->getCreditByUsername($username);
}

/**
 * 根据用户uid查找该用户的积分
 * @param string $uid
 */
function getCreditByUid($uid){
	$creditD = D("UserCredit","api");
	return $creditD->getCreditByUid($uid);
}


/**
 * 根据用户uid查找该用户的积分
 * @param string $uid
 */
function getUserCredit($uid){
	return model('Credit')->getUserCredit($uid);
}

/*
 * 给用户增加积分
 * @param string $username 用户名
 * @param int $credit 增加的积分数
 * @return  失败 array("statuscode"=>"500","data"=>0);
 * 			成功 array("statuscode"=>"200","data"=>102);
 */
 
function addCreditByUname($username,$credit){
	$creditD = D("UserCredit","api");
	return $creditD->addCreditByUname($username,$credit);
}

/**
 * 给用户增加积分
 * @param string $uid 用户uid
 * @param int $credit 增加的积分数
 * @return  失败 array("statuscode"=>"500","data"=>0);
 * 			成功 array("statuscode"=>"200","data"=>102);
 */
function addCreditByUid($uid,$credit){
	$creditD = D("UserCredit","api");
	return $creditD->addCreditByUid($uid,$credit);
}

/**
 * 给用户减少积分
 * @param string $username 用户名
 * @param int $credit 要扣除的积分数
 * @return array 如 成功： $result = array( 'statuscode' => 200,'data' => '105')
 * 					失败:  $result = array( 'statuscode' => 500,'data' => '1')
 */
function minusCreditByUsername($username,$credit){
	$creditD = D("UserCredit","api");
	return $creditD->minusCreditByUsername($username,$credit);
}

/**
 * 给用户减少积分
 * @param string $uid 用户uid
 * @param int $credit 要扣除的积分数
 * @return array 如 成功： $result = array( 'statuscode' => 200,'data' => '105')
 * 					失败:  $result = array( 'statuscode' => 500,'data' => '1')
 */
function minusCreditByUid($uid,$credit){
	$creditD = D("UserCredit","api");
	return $creditD->minusCreditByUid($uid,$credit);
}

/**
 * 获取礼物列表 
 * 返回数据结构类型
 * <code>
 * array(
 * 'data'=>array(
 * 'id'=>'1',
 * 'gift_name' =>'礼物名',
 * 'score'=>'兑换积分',
 * 'num' =>'礼物剩余数量',
 * 'is_delete'=>'0',
 * 'img_path' =>'礼物图片路径',
 * 'create_time' =>'创建时间',
 * ),
 * 'count'=>'礼物总数'
 * )
 * </code>
 */
function getGiftList($order,$page,$pageSize){
        $giftD = D('CyGift','public');
        return $giftD->getGiftList($order,$page,$pageSize);
}

/**
 * 通过礼物ID删除礼物
 * @param string $id 礼物ID
 */
function delGift($id){
        $giftD = D('CyGift','public');
        return $giftD->delGift($id);
}
/**
 * 添加礼物
 * @param array $giftInfo
 */
function createGift($giftInfo){
        $giftD = D('CyGift','public');
        return $giftD->createGift($giftInfo);
}
/**
 * 编辑礼物
 * @param array $giftInfo
 */
function editGift($giftId,$giftInfo){
        $giftD = D('CyGift','public');
        return $giftD->editGift($giftId,$giftInfo);
}

/**
 * 获取兑换信息列表
 */
function getOrderList($condition,$order,$page,$pageSize){
    $orderD = D('CyOrderInfo','public');
    return $orderD->getOrderList($condition,$order,$page,$pageSize);
}

/**
 * 
 * @param string $orderId 订单ID
 * @param array $sendInfo 发货信息
 * @return boolean
 */
function sendGift($orderId,$sendInfo){
    $orderD = D('CyOrderInfo','public');
    return $orderD->sendGift($orderId,$sendInfo);
}

$server = new PHPRPC_Server();
$server->add('getExperienceByUsername');
$server->add('getExperienceByUid');
$server->add('getCreditByUsername');
$server->add('getCreditByUid');
$server->add('addCreditByUname');
$server->add('addCreditByUid');
$server->add('minusCreditByUsername');
$server->add('minusCreditByUid');
$server->add('getUserCredit');
$server->start();
?>