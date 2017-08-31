<?php
/**
 * 评论模型
 */
class AppcenterCommentModel extends Model{
    // 表名
    protected  $tableName = 'sns_comment';

    /**
     * 评论
     * @param string $data
     * @return mixed|Model
     */
    public function comment($data){
        $nowTime = new DateTime();
        $data['ctime'] = $nowTime->format("Y-m-d H:i:s");
        return $this->add($data);
    }

    /**
     * 获取评论分页
     * @param unknown_type $condition
     * @param unknown_type $page
     * @param unknown_type $limit
     * @param unknown_type $order
     * @return multitype:|unknown
      *2014-9-29
     */
    public function getComments($condition,$page,$limit,$order = 'comment_id desc'){
        $condition['is_del'] = 0;
        $result = $this->where($condition)->order($order)->page("$page,$limit")->select();

        if($result === false){
            return array();
        }else{
            foreach($result as &$value){
                $user =  D('User')->getUserInfoByLogin($value['login']);
                $value['uid'] = $user['uid'];
                $value['uname'] = $user['uname'];
                $value['avatar'] = $user['avatar_small'];
                unset($user);
                if(!empty($value['to_login'])){
                    $toUser =  D('User')->getUserInfoByLogin($value['to_login']);
                    $value['to_uid'] = $toUser['uid'];
                    $value['to_uname'] = $toUser['uname'];
                    unset($toUser);
                }
            }
            return $result;
        }
    }

    /**
     * 获取评论条数
     * @param unknown_type $condition
      *2014-9-29
     */
    public function getCommentCount($condition){
        $condition['is_del'] = 0;
        return $this->where($condition)->count();
    }
}