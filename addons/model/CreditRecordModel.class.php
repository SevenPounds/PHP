<?php
/**
 * 积分记录模型
 * @author frsun <[email]>
 * @date(2014.5.28)
 */

class CreditRecordModel extends Model {


	protected $tableName = 'credit_record';

	/**
	 * 获取积分记录列表
	 * @param  [int] $uid   [用户id]
	 * @param  [int] $page  [页码]
	 * @param  [int] $limit [每页大小]
	 * @param  string $order [排序字段]
	 * @return [array]        [返回数组]
	 */
	public function getCreditRecordList($uid,$page,$limit=10,$order='create_time DESC'){
            $map = array();
            $map['uid'] = $uid;
            $count = $this->where($map)->count();
            $list = $this->where($map)->page("$page,$limit")->order($order)->select();
            foreach ($list as $key => $value) {
            	$data = json_decode($value['data']);
            	$list[$key]['action'] = $data->rule->alias;
            	$list[$key]['description'] = $data->content;
            	$list[$key]['score'] = $data->rule->score;
            }
            $result = array("total"=>intval($count),"data"=>$list);
            return $result;
	}

	/**
	 * 添加积分记录 
	 * @param  [int] $uid               [用户id]
	 * @param  [str] $login             [用户登录名]
	 * @param  [int] $action            [积分规则id]
	 * @param  [str] $data = array()    [记录数据]
     *               $data['content'] = 'xx';
     *               $data['url'] = 'xx';
     *               $data['rule'] = array();
	 * @return [array]                  [返回数组]
	 */
    
	public function addCreditRecord($uid, $login, $action, $data){
            $map = array();
            $map['uid'] = $uid;
            $map['login_name'] = $login;
            $map['action'] = $action;
            $map['data'] = json_encode($data);
            $map['ip'] = get_client_ip();
            $map['create_time'] = date('Y-m-d H:i:s');

            return $this->add($map);
	}

	
	
	/**
	 * 获取用户当上传和分享的次数
	 * record_type  1:上传; 2:分享
	 * @param unknown $login
	 * @return Ambigous <multitype:, boolean, mixed, multitype:unknown >
	 */
	public function getCuttentCounts($login){
		// 当天时间
		$current =date("Y-m-d");
		//分类获取当前的各种分类
		$sql = "SELECT  r.`action` , COUNT(*) AS count FROM ts_credit_record r WHERE r.`login_name` ='".$login."' AND  r.`create_time` > '".$current."' GROUP BY ACTION ";
		$result =$this->query($sql);
		foreach($result as $key => $value){
			if($value['action']=="upload_resource"){
				$return['share'] =$value['count'];
			}
		}
		return  $return ;
		 
	}

	
	/**
	 * 增加一条记录
	 * @param $data array 记录内容
	 *   type :'upload_resource' 积分添加类型
	 *   url  : 分享资源地址
	 *   content:  添加积分的内容
	 *   login  :  用户登录名
	 */
	public function addRecordAndScore($data){
		$user= D('User')->getUserInfoByLogin($data['login']);
		$result = array();
		$result['creditResult'] = D('Credit')->setUserCredit($user['uid'],$data['type']);
		$arr = array();
		$arr['content'] = $data['content'];
		$arr['url'] = $data['url'];
		$arr['rule'] = array(
				'alias' => $result['creditResult']['alias'],
				'score' => $result['creditResult']['score']
		);
		$return  =$this->addCreditRecord($user['uid'], $data['login'], $data['type'], $arr);
		//分享次数增加
		$count =D('UploadRecord')->getCuttentCounts($data['login']);
		if($count==false&&$count!=0){
			D('UploadRecord')->addRecord(array('login'=>$data['login'],'count'=>0));
		}else{
			D('UploadRecord')->updateRecord(array('login'=>$data['login'],'count'=>$count+1));
			
		}
		return  $return;
	}
	/**
	 * 获取积分达人榜数据
	 * @param $limit 获取数量
	 * 
	 *    根据上周获取积分数量的逆序排列
	 */
	public function getCreditMasterList($limit){
		//上周一时间
		$date = date('Y-m-d',strtotime('last Monday'));
		$sql =" SELECT tcr.login_name as login_name ,tcr.create_time as create_time , tcr.uid as uid ,SUM(tcs.score) as score "
 			  ."FROM `ts_credit_record`  tcr left join `ts_credit_setting` tcs  on tcr.action =tcs.`name` where  DATEDIFF(create_time ,'".$date
 			  ."') BETWEEN -7 and 0 GROUP BY uid order by score DESC limit 0,".$limit.";";
		
		$list = $this->query($sql);
		$count = count($list);
		//如果积分达人榜不足5个，则按照总的积分数量排行补足
		if($count<5){
			$addlList=M ( 'credit_user' )->field('uid')->order("`score` DESC ")->limit('0,5')->select();
			$uidArr =getSubByKey($list,'uid');
			foreach($addlList as $val){
				if(!in_array($val['uid'], $uidArr)){
					array_push($list, array('uid'=>$val['uid']));
					$count++;
					if($count >=5){
						return $list;
					}
				}
			}
		}
		return $list;
	}

    public function getDalyScore($login,$action=''){
        // 检查今天总积分
        $start = date('Y-m-d');
        $end = date('Y-m-d',strtotime('+1 day'));
        if(isset($action)){
            $action='AND sr.action ='.$action;
        }
        $sql="SELECT SUM(se.score) AS sumScore  FROM ts_credit_record  sr LEFT JOIN ts_credit_setting se ON se.`name`=sr.action WHERE sr.login_name=.$login AND sr.create_time .$action. BETWEEN $start,$end ";
        return $this->query($sql);
    }
}

?>
