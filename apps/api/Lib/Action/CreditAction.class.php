<?php
class CreditAction extends Action{
	private $credit;
	private $syscredit;
	private $userInfo;
	public function __construct() {
		parent::__construct();
		
		$this->credit = D("UserCredit","user_credit");
		$this->syscredit=D("Credit");
		$this->userInfo=D("User");
	}

	public function index(){
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
	 * @param string $username 用户登录名
	 */
	public function getExperienceByUsername($username){
		$userInfo = $this->userInfo->getUserInfoByLogin($username);
		
		return $this->syscredit->getUserCredit($userInfo['uid']);
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
		return $this->syscredit->getUserCredit($uid);
	}
	
	/**
	 * 根据用户名查找该用户的积分
	 * @param string $username
	 */
	public function getCreditByUsername($username){
		return $this->credit->getCreditByUsername($username);
	}
	
	/**
	 * 根据用户uid查找该用户的积分
	 * @param string $uid
	 */
	public function getCreditByUid($uid){
		return $this->credit->getCreditByUid($uid);
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
		return $this->credit->addCreditByUname($username,$credit);
	}
	
	/**
	 * 给用户增加积分
	 * @param string $uid 用户uid
	 * @param int $credit 增加的积分数
	 * @return  失败 array("statuscode"=>"500","data"=>0);
	 * 			成功 array("statuscode"=>"200","data"=>102);
	 */
	public function addCreditByUid($uid,$credit){
		return $this->credit->addCreditByUid($uid,$credit);
	}
	
	/**
	 * 给用户减少积分
	 * @param string $username 用户名
	 * @param int $credit 要扣除的积分数
	 * @return array 如 成功： $result = array( 'statuscode' => 200,'data' => '105')
	 * 					失败:  $result = array( 'statuscode' => 500,'data' => '1')
	 */
	public function minusCreditByUsername($username,$credit){
		return $this->credit->minusCreditByUsername($username,$credit);
	}
	
	/**
	 * 给用户减少积分
	 * @param string $uid 用户uid
	 * @param int $credit 要扣除的积分数
	 * @return array 如 成功： $result = array( 'statuscode' => 200,'data' => '105')
	 * 					失败:  $result = array( 'statuscode' => 500,'data' => '1')
	 */
	public function minusCreditByUid($uid,$credit){
		return $this->credit->minusCreditByUid($uid,$credit);
	}
}
?>