<?php
/**
 * 应用模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class TeachCheckinModel extends BaseModel {
    /*
   *教师空间签到
   */
    public function teach_check_in($uid,$callback){
        $map['ctime'] = array ( 'gt' , strtotime( date('Ymd') ) );
        $map['uid'] = $uid;
        $ischeck = M('check_info','ts_')->where($map)->find();
        //清理缓存
        model( 'Cache' )->set('check_info_'.$uid.'_'.date('Ymd') , null);
        //是否重复签到
        if ( !$ischeck ){
            $map['ctime'] = array( 'lt' , strtotime( date('Ymd') ) );
            $last = M('check_info','ts_')->where($map)->order('ctime desc')->find();
            $data['uid'] = $uid;
            $data['ctime'] = $_SERVER['REQUEST_TIME'];
            //是否有签到记录
            if ( $last ){
                //是否是连续签到
                if ( $last['ctime'] > ( strtotime( date('Ymd') ) - 86400 ) ){
                    $data['con_num'] = $last['con_num'] + 1;
                } else {
                    $data['con_num'] = 1;
                }
                $data['total_num'] = $last['total_num'] + 1;
            } else {
                $data['con_num'] = 1;
                $data['total_num'] = 1;
            }

            if ( M('check_info','ts_')->add($data) ){

                /*********** 增加积分 begin by xypan 2014/5/28 *****************/
                $result = array();
                $result['creditResult'] = D('Credit')->setUserCredit($uid,'check_in');

                $arr = array();
                $arr['content'] = '每日签到';
                $arr['url'] = '';
                $arr['rule'] = array(
                    'alias' => $result['creditResult']['alias'],
                    'score' => $result['creditResult']['score']
                );

                M('CreditRecord')->addCreditRecord($this->mid, $this->user['login'], 'check_in', $arr);
                /*********** 增加积分 end by xypan 2014/5/28 *****************/

                //更新连续签到和累计签到的数据
                $connum = M('user_data','ts_')->where('uid='.$uid." and `key`='check_connum'")->find();
                if ( $connum ){
                    $connum = M('check_info','ts_')->where('uid='.$uid)->getField('max(con_num)');
                    M('user_data','ts_')->setField('value' , $connum , "`key`='check_connum' and uid=".$uid);
                    M('user_data','ts_')->setField('value' , $data['total_num'] , "`key`='check_totalnum' and uid=".$uid);

                } else {
                    $connumdata['uid'] = $uid;
                    $connumdata['value'] = $data['con_num'];
                    $connumdata['key'] = 'check_connum';
                    M('user_data','ts_')->add($connumdata);

                    $totalnumdata['uid'] = $uid;
                    $totalnumdata['value'] = $data['total_num'];
                    $totalnumdata['key'] = 'check_totalnum';
                    M('user_data','ts_')->add($totalnumdata);
                }

                $result['con_num'] = $data['con_num'];
                return $callback."(".json_encode($result).")";
            }else{
                $result = 'error';
                return $callback."(".json_encode($result).")";
            }
        }
    }
}