<?php
/**
 * 用户积分模型
 * @author hhshi
 *
 */
class UserCreditModel extends Model{
	//用户初始积分
	private   $init_credit = 100;
	protected $tableName = 'user_credit';
	protected $fields = array (
		0=>'id',
		1=>'uid',
		2=>'username',
		3=>'credit'
	);
	
	/**
	 * 根据用户名查找该用户的积分,如没有，则初始化用户积分，并返回
	 * @param string $username
	 */
	public function getCreditByUsername($username){
		$map = array();
		$map['username'] = $username;
		$result = $this->table($this->tablePrefix.''.$this->tableName)->where($map)->select();
		//如没有，则初始化用户积分，并返回
		if(empty($result) ||count($result)<1){
			$result = $this->getUserByUname($username);
			$this->initCredit($username,$this->init_credit,$result["uid"]);
			$result = $this->table($this->tablePrefix.''.$this->tableName)->where($map)->select();
		}
		return $result[0];
	}

	/**
	 * 根据用户uid查找该用户的积分
	 * @param string $uid
	 */
	public function getCreditByUid($uid){
		$map = array();
		$map['uid'] = $uid;
		$result = $this->table($this->tablePrefix.''.$this->tableName)->where($map)->select();
		//如没有，则初始化用户积分，并返回
		if(empty($result) ||count($result)<1){
			$result = $this->getUserByUid($uid);
			$this->initCredit($result['login'],$this->init_credit,$uid);
			$result = $this->table($this->tablePrefix.''.$this->tableName)->where($map)->select();
		}
		return $result[0];
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
		$map = array();
		if(!empty($username)){
			$map['username'] = $username;
			// 查询username若查询不到则初始化改用户积分
			$result = $this->getCreditByUsername($username);
			
			// 增加积分信息
			if ($this->setInc('credit',$map,$credit)) {
				return array(
					"statuscode"=>"200",
					"data"=>$result['credit']+$credit
					);
			}else{
				return array(
					"statuscode"=>"500",
					"data"=>0
				);
			}
		}
	}

	/**
	 * 给用户增加积分
	 * @param string $uid 用户uid
	 * @param int $credit 增加的积分数
	 * @return  失败 array("statuscode"=>"500","data"=>0);
	 * 			成功 array("statuscode"=>"200","data"=>102);
	 */
	public function addCreditByUid($uid,$credit){
		$map = array();
		if(!empty($uid)){
			$map['uid'] = $uid;
			// 查询uid若查询不到则初始话改用户积分
			$result = $this->getCreditByUid($uid);
			
			// 增加积分数
			if ($this->setInc('credit',$map,$credit)) {
				return array(
						"statuscode"=>"200",
						"data"=>$result['credit']+$credit
				);
			}else{
				return array(
						"statuscode"=>"500",
						"data"=>0
				);
			}
		}
	}
	
	/**
	 * 给用户减少积分
	 * @param string $username 用户名
	 * @param int $credit 要扣除的积分数
	 * @return array 如 成功： $result = array( 'statuscode' => 200,'data' => '105')
	 * 					失败:  $result = array( 'statuscode' => 500,'data' => '1')
	 */
	public function minusCreditByUsername($username,$credit){
		$map = array();
		$resultMap=array();
		if(!empty($username)){
			$map['username'] = $username;
			
			// 检查用户剩余积分数是否大于要减少的积分数
			$result = $this->checkCreditByName($username,$credit);
			if ($result['statuscode']=="200") {
				if($this->setDec('credit',$map,$credit)==1)
				{
					$resultMap['statuscode']="200";
					$resultMap['data'] = $result['data'];
				}
				else{
					$resultMap['statuscode']="500";
					$resultMap['data']=$result['data']+$credit;
				}
				return $resultMap;
			}else{
				$resultMap['statuscode']="500";
				$resultMap['data']=$result['data'];
				return $resultMap;
			}
		}else{
			return null;
		}
	}

	
	/**
	 * 给用户减少积分
	 * @param string $uid 用户uid
	 * @param int $credit 要扣除的积分数
	 * @return array 如 成功： $result = array( 'statuscode' => 200,'data' => '105')
	 * 					失败:  $result = array( 'statuscode' => 500,'data' => '1')
	 */
	public function minusCreditByUid($uid,$credit){
		$map = array();
		$resultMap=array();
		if(!empty($uid)){
			$map['uid'] = $uid;
			
			// 检查用户剩余积分数是否大于要减少的积分数
			$result = $this->checkCreditByUid($uid,$credit);
			if ($result['statuscode']=="200") {
				if($this->setDec('credit',$map,$credit)==1)
				{
					$resultMap['statuscode']="200";
					$resultMap['data'] = $result['data'];
				}
				else{
					$resultMap['statuscode']="500";
					$resultMap['data']=$result['data']+$credit;
				}
				return $resultMap;
			}else{
				$resultMap['statuscode']="500";
				$resultMap['data']=$result['data'];
				return $resultMap;
			}
		}else{
			return null;
		}
	}

	/**
	 * 根据用户id校验用户积分是否够用
	 * @param int $uid 用户id
	 * @param int $credit 要扣除的积分
	 * @return array {'statuscode'=状态值,"data"=积分信息}
	 * <p>
	 * 	statuscode == 200 时够用
	 *  statuscode == 500 时不够用
	 * </p>
	 */
	private function checkCreditByUid($uid,$credit){
		// 根据用户id获取用户积分信息
		$result = $this->getCreditByUid($uid);
		$checkResult = array();
		if (($result['credit']-$credit)>=0) {
			$checkResult['statuscode'] = "200";
			$checkResult['data'] = $result['credit'];
			return $checkResult;
		}else {
			$checkResult['statuscode'] = "500";
			$checkResult['data'] = $result['credit'];
			return $checkResult;
		}
	}
	
	/**
	 * 根据用户名校验用户积分是否够用
	 * @param string $username 用户id
	 * @param int $credit 要扣除的积分
	 * @return array {'statuscode'=状态值,"data"=积分信息}
	 * <p>
	 * 	statuscode == 200 时够用
	 *  statuscode == 500 时不够用
	 * </p>
	 */
	private function checkCreditByName($uname,$credit){
		// 根据用户id获取用户积分信息
		$result = $this->getCreditByUsername($uname);
		$checkResult = array();
		
		// 判断用户积分数是否够用
		if (($result['credit']-$credit)>=0) {
			$checkResult['statuscode'] = "200";
			$checkResult['data'] = $result['credit'];
			return $checkResult;
		}else {
			$checkResult['statuscode'] = "500";
			$checkResult['data'] = $result['credit'];
			return $checkResult;
		}
	}

	/**
	 * 根据用户id查询用户信息
	 * @param int $uid 用户id
	 * @return array 用户信息
	 */
	private function getUserByUid($uid){
		if(!empty($uid)){
			$result = $this->table('ts_user')->where(array('uid' => $uid))->select();
			if (empty($result) || count($result)<1) {
				return null;
			}
			return $result[0];
		}
		return null;
	}
	
	/**
	 * 根据用户名查询用户信息
	 * @param string $username
	 * @return array 用户信息
	 */
	private function getUserByUname($username){
		if(!empty($username)){
			$result = $this->table('ts_user')->where(array('login' => $username))->select();
			if (empty($result) || count($result)<1) {
				return null;
			}
			return $result[0];
		}
		return null;
	}

	
	/**
	 * 初始化用户积分
	 * @param string $username 用户名
	 * @param int $credit 初始积分数,失败返回-1
	 */
	private function initCredit($username,$credit,$uid){
		$usercredit['uid'] = $uid;

		$usercredit['username'] = $username;
		$usercredit['credit'] = $credit;
		return $this->add($usercredit);
	}

}
?>