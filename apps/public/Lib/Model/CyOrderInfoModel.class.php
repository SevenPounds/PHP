<?php
class CyOrderInfoModel extends Model {
	
	protected $tableName = 'cy_order_info';
	
	/**
	 * 插入礼物订单
	 * @param unknown $order
	 */
	public function addOrderInfo($orderInfo){
		$orderInfo ['order_sn'] = $this->createOrderSN ($orderInfo['uid']);
		$orderInfo ['order_status'] = 0; // 订单状态，默认未发货
		$orderInfo ['order_time'] = date ('Y-m-d H:i:s');
		return $this->data($orderInfo)->add();
	}
        
        /**
         * 获取兑换信息列表
         * @param type $condition
         * @param type $page
         * @param type $pageSize
         * @param type $order
         * @return type
         */
        public function getOrderList($condition, $order='order_time DESC', $page=1, $pageSize=10){
            if(!empty($condition['giftName'])){
                $giftId=M('cy_gift')->where(array('gift_name'=>$condition['giftName']))-> field('id')->find(); 
                $condition['gift_id']=$giftId['id'];
                unset($condition['giftName']); 
            }
            $data = $this->where($condition)->order($order)->page("$page, $pageSize")->select();  
	    $count = $this->where($condition)->count();
            return array(
			'data' => $data,
			'count' => $count
            );
        }
	
	/**
	 * 创建订单号
	 *
	 * @param unknown $uid
	 * @return string
	 */
	private function createOrderSN($uid) {
		return date ( 'YmdHis', time () ) . $uid . rand ( 1000, 9999 );
	}
	
}