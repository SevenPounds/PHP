<?php

/**
 * 积分兑换模型
 * @author frsun
 * 
 */
class CyGiftModel extends Model {
	
	protected $tableName = 'cy_gift';
	
	/**
	 * 礼物兑换接口
	 *
	 * @param int $uid        	
	 * @param string $giftId        	
	 * @param array $orderInfo(
	 *        	"login_name"=>"xx",
	 *        	"consignee" => "xx",
	 *        	"province" => "xx",
	 *        	"city" => "xx",
	 *        	"district" => "xx",
	 *        	"address" => "xx",
	 *        	"zipcode" => "xx",
	 *        	"telephone" => "xx")
	 * @return array(status='xx', msg='xx')
	 */
	public function exchangeGift($uid, $giftId, $orderInfo) {
		if (! isset ( $uid ) || ! isset ( $giftId ) || ! is_array ( $orderInfo )) {
			$result ["status"] = 0;
			$result ["msg"] = "参数信息错误";
			return $result;
		}
		//检查能否兑换
		$checkResult = $this->checkExchangeGift($uid, $giftId);
		if($checkResult['status']==0){
			return $checkResult;
		} else {
			// 获取礼物信息
			$giftInfo = $this->getGiftInfoById ( $giftId );
			// 兑换礼物处理
			if (! $this->exchangeGiftProcess ( $uid, $giftInfo, $orderInfo )) {
				$result ['status'] = 0;
				$result ['msg'] = "兑换时发生错误";
				return $result;
			} else {
				$result ['status'] = 1;
				$result ['msg'] = "您已成功兑换礼物,请留意注册邮箱的寄送通知!";
				$result ['gift_name'] = $giftInfo['gift_name'];
				return $result;
			}
		}
	}
	
	/**
	 * 检查是否能够兑换
	 * @param int $uid 用户uid
	 * @param array $giftId 礼物id
	 * @return array(status=>'xx', msg='xx')
	 */
	public function checkExchangeGift($uid, $giftId){
		// 获取用户积分
		model ( "Credit" )->cleanCache ( $uid );
		$credit = model ( "Credit" )->getUserCredit ( $uid );
		$score = $credit ['credit'] ['score'] ['value'];
		// 获取礼物信息
		$giftInfo = $this->getGiftInfoById ( $giftId );
		//检查是否能够兑换
		if(!$giftInfo){
			$result ["status"] = 0;
			$result ["msg"] = "不存在该礼物";
			return $result;
		}
		if (1 == $giftInfo ['is_delete']) {
			$result ["status"] = 0;
			$result ["msg"] = "礼物已下架，不能兑换";
			return $result;
		}
		if ($giftInfo ["num"] <= 0) {
			$result ["status"] = 0;
			$result ["msg"] = "礼物数目不足";
			return $result;
		}
		if ($score < $giftInfo ['score']) {
			$result ["status"] = 0;
			$result ["msg"] = "积分不足，再接再厉";
			return $result;
		}
		$result ["status"] = 1;
		$result ["msg"] = "可以兑换";
		return $result;
	}
	
	/**
	 * 兑换礼物处理
	 *
	 * @param int $uid        	
	 * @param array $giftInfo        	
	 * @return boolean 成功返回true
	 */
	private function exchangeGiftProcess($uid, $giftInfo, $orderInfo) {
		$giftId = $giftInfo ['id'];
		$this->startTrans ();
		// 礼物数目-1
		$r1 = $this->where("id=$giftId")->setDec('num', '', 1);
		// 用户减去相应的积分
		$r2 = model ( "Credit" )->setUserCredit ( $uid, array (
				"score" => - $giftInfo ['score'] 
		) );
		// 将订单插入数据库
		$orderInfo ['uid'] = $uid;
		$orderInfo ['gift_id'] = $giftId;
		$r3 = D("CyOrderInfo")->addOrderInfo($orderInfo);
		// 积分记录
		$data['content'] = '兑换了'.$giftInfo['gift_name'];
		$data['url'] = '';
		$data['rule'] = array (
			'alias' => '兑换礼物',
			'score' => -$giftInfo ['score'] 
		);
		$r4 = model ("CreditRecord")->addCreditRecord($uid, $orderInfo['login_name'], 'exchange_gift', $data);
		if ($r1 != 1 || $r2->info === false || $r3 == false || $r4 == false) {
			$this->rollback ();
			return false;
		} else {
			$this->commit ();
			return true;
		}
	}
	
	/**
	 * 根据礼物编号获取礼物信息
	 *
	 * @param string $giftId        	
	 * @return array
	 */
	private function getGiftInfoById($giftId) {
		$giftInfo = $this->where ( array (
				"id" => $giftId 
		) )->find ();
		return $giftInfo;
	}
	
	/**
	 * 获取礼物列表
	 * @param int $page
	 * @param int $pageSize
	 * @param string $order
	 * @return array
	 */
	public function getGiftList($page=1, $pageSize=5, $order='create_time DESC'){
		$condition = 'is_delete=0';
		$data = $this->where($condition)->order($order)->page("$page, $pageSize")->select();
		$count = $this->where($condition)->count();
		return array(
			'data' => $data,
			'count' => $count
		);
	}
        
        /**
         * 删除礼物
         * @return boolean
         */
        public function delGift($id){
             if(!$id){
			return false;
		}
		return $this->where("id=".$id)->save(array("is_delete"=>1));
        }
        
        /**
             * 编辑礼物
         * @param string $giftId
         * @param array $giftInfo
         * @return boolean
         */
        public function editGift($giftId,$giftInfo){
             if(empty($giftId)||empty($giftInfo)){
			return false;
		}
             $result=$this->where(array('id'=>$giftId))->save($giftInfo);

	     return $result;
        }
        
        /**
         * 创建礼物
         * @param array $giftInfo
         */
        public function createGift($giftInfo){
            //数据信息
            $giftInfo['is_delete'] = 0;
            $giftInfo['num'] = 0;
            $giftInfo['create_time'] = date('Y-m-d H:i:s',time());
            $result=$this->add($giftInfo);
            if($result){
                return true;
            }
            return false;
        }
}

?>