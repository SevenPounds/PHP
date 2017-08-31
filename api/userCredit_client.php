<?php
/*
* 资源平台用户积分操作 相关服务的客户端
*/

/**
 * 
 * @author zmduan
 *
 *	测试例子
 * $client = new UserCredit_Client('http://localhost/ThinkSNS/api/userCredit.php');
 * echo $client->addCredit('huali',1000);
 */
class UserCredit_Client{
	private $client = null;
	
	/**
	 * 
	 * @param unknown_type $url 积分服务地址
	 */
	function __construct($url){
		$this->client = new PHPRPC_Client($url);
	}
	
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
	 * @param int $uid 用户名
	 */
	public function getExperienceByUsername($username){
		return $this->client->getExperienceByUsername($username);
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
	public function getExperienceByUid($uid){
		return $this->client->getExperienceByUid($uid);
	}
	
	/**
	 * 根据用户名查找该用户的积分
	 * @param string $username
	 */
	public function getCreditByUsername($username){
		return $this->client->getCreditByUsername($username);
	}
	
/**
	 * 根据用户uid查找该用户的积分
	 * @param string $uid
	 */
	public function getCreditByUid($uid){
		return $this->client->getCreditByUid($uid);
	}
	
	
	/**
	 * 给用户增加积分
	 * @param string $username 用户名
	 * @param int $credit 增加的积分数
	 * @return  失败 array("statuscode"=>"500","data"=>0);
	 * 			成功 array("statuscode"=>"200","data"=>102);
	 *
	 */
	public function addCreditByUname($username,$credit){
		return $this->client->addCreditByUname($username,$credit);
	}
	
	/**
	 * 给用户增加积分
	 * @param string $uid 用户uid
	 * @param int $credit 增加的积分数
	 * @return  失败 array("statuscode"=>"500","data"=>0);
	 * 			成功 array("statuscode"=>"200","data"=>102);
	 */
	public function addCreditByUid($uid,$credit){
		return $this->client->addCreditByUid($uid,$credit);
	}
	
	/**
	 * 给用户减少积分
	 * @param string $username 用户名
	 * @param int $credit 要扣除的积分数
	 * @return array 如 成功： $result = array( 'statuscode' => 200,'data' => '105')
	 * 					失败:  $result = array( 'statuscode' => 500,'data' => '1')
	 */
	public function minusCreditByUsername($username,$credit){
		return $this->client->minusCreditByUsername($username,$credit);
	}
	
	/**
	 * 给用户减少积分
	 * @param string $uid 用户uid
	 * @param int $credit 要扣除的积分数
	 * @return array 如 成功： $result = array( 'statuscode' => 200,'data' => '105')
	 * 					失败:  $result = array( 'statuscode' => 500,'data' => '1')
	 */
	public function minusCreditByUid($uid,$credit){
		return $this->client->minusCreditByUid($uid,$credit);
	}

        /**
         * 获取礼物列表
         */
        public function getGiftList($order,$page,$pageSize){
                return $this->client->getGiftList($order,$page,$pageSize);
        }
        
        /**
         * 删除礼物
         * @param string $id 礼物ID
         */
        public function delGift($id){
               return $this->client->delGift($id);
        }
        
        /**
         * 创建礼物
         * @param array $giftInfo
         * $giftInfo示例
         * <code>
         * $array(
         * 'gift_name' => "dingyf";礼物名
         * 'score' => 100;礼物兑换积分
         * 'total' => 200;礼物总数
         * 'img_path' => ''礼物图片地址
         * )
         * </code>
         */
        public function createGift($giftInfo){
                return $this->client->createGift($giftInfo);
        }
        
        /**
         * 编辑礼物
         * @param string $giftId
         * @param array $giftInfo
         * @return boolean
         */
        public function editGift($giftId,$giftInfo){
                return $this->client->editGift($giftId,$giftInfo);
        }
        
        /**
         * 获取兑换信息列表
         * $condition示例
         * <code>
         * $arrary(
         * 'login_name' => 'dingyf' 账号
         * 'consignee' => '丁亚飞' 收货人姓名
         * 'giftName' => 'ipad' 礼物名
         * 'order_time' => '' 兑换时间
         * 'timestart' => '开始时间
         * 'timeend'' => '结束时间'
         * )
         * </code>
         */
        public function getOrderList($condition,$order,$page,$pageSize){
                return $this->client->getOrderList($condition,$order,$page,$pageSize);
        }
        /**
         * 发货
         * @param string $orderId 订单ID
         * @param array $sendInfo 发货信息 
         */
        public function sendGift($orderId,$sendInfo){
                return $this->client->sendGift($orderId,$sendInfo);
        }
}

?>