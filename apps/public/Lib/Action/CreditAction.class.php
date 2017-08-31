<?php

/**
 * 积分中心
 * @author Dingyf
 *
 */
class CreditAction extends Action {

    function __construct() {
        parent::__construct();
        if (! CheckTaskSwitch()) {
            $this->error('该页面不存在！');
        }
        // 获取资源平台地址
        $resCloudUrl = C('RS_SITE_URL');
        $this->credit=D('Credit')->getUserCredit($this->uid);
        $this->resCloudUrl = $resCloudUrl;
    }
    
    public function index(){
        $uid = $this->uid;
        $page = $_REQUEST['p']?$_REQUEST['p']:1;
        $limit = $_REQUEST['limit']?$_REQUEST['limit']:10;
    	$result = D('CreditRecord')->getCreditRecordList($uid,$page,$limit);
        // 设置分页信息
        import("@.ORG.Page");
        $p = new Page ($result['total'], $limit);
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $paging = $p->show();
        $this->assign("list",$result['data']);
        $this->assign("paging",$paging);          
    	$this->display();
    }
    
    public function exchange(){  
        $page = $_REQUEST['p']?$_REQUEST['p']:1;
        $limit = 10;
        $result=D('CyGift')->getGiftList($page,$limit);
        // 设置分页信息
        import("@.ORG.Page");
        $p = new Page ($result['count'], $limit);
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $paging = $p->show();
        $this->assign("giftList",$result['data']);
        $this->assign("paging",$paging);
        $this->display();
    }
    public function exchangGift(){
        $orderInfo=array();
        $return=array();
        
        $giftsID=$_REQUEST['gifsId'] ? trim($_REQUEST['gifsId']) : "";
        
        //获取订单信息
        $orderInfo['consignee'] = $_REQUEST['umane'] ? trim($_REQUEST['umane']) : "";
        $orderInfo['province'] = $_REQUEST['province'] ? trim($_REQUEST['province']) : "";
        $orderInfo['city'] = $_REQUEST['city'] ? trim($_REQUEST['city']) : "";
        $orderInfo['district'] = $_REQUEST['area'] ? trim($_REQUEST['area']) : "";
        $orderInfo['address'] = $_REQUEST['street'] ? trim($_REQUEST['street']) : "";
        $orderInfo['telephone'] = $_REQUEST['phone'] ? trim($_REQUEST['phone']) : "";
        $orderInfo['login_name'] = $this->user['login'];

        $result=D('CyGift')->exchangeGift($this->mid,$giftsID,$orderInfo);
        if($result['status']==0){
            $return['status'] = 0;
            $return['info'] = $result['msg'];
            $return['data'] = array();
        }else{
            //发表动态
            $this->syncToFeed($this->mid,$result['gift_name']);
            $return['status'] = 1;
            $return['info'] = $result['msg'];
            $return['data'] = array();
        }    
              
        exit(json_encode($return));
        
    }


    /**
     * 兑换动态
     * @param int $uid
     * @param string $giftName  礼物名称
     * @return string 成功，返回微博动态，否则返回false
     */
    private function syncToFeed($uid, $giftName){
        $data = array();
        $data['content'] = '';
        $data['body'] = "我使用积分兑换了礼物【".$giftName."】";
        return model('Feed')->put($uid, 'public', 'post', $data);
    }
}
?>