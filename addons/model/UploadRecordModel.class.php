<?php
/**
 * 上传和公开记录次数
 * Created by PhpStorm.
 * User: xypan
 * Date: 14-7-3
 * Time: 上午11:31
 */
class UploadRecordModel extends Model{
    protected $tableName = 'upload_record';

    /**
     * 获取当天已经上传的资源数
     * @param $login string 登录名
     * @return bool|int 当天已经上传的资源数|查询失败返回false
     */
    public function getCuttentCounts($login){
        $result = $this->where("login='$login'")->find();
        if($result){
            // 当前的时间(用DateTime为了解决Y2K38 BUG)
            $nowTime = new DateTime();
            // 当天零点的时间
            $zeroTime = new DateTime($nowTime->format("Y-m-d"));
            // 记录的时间
            $recordTime = new DateTime($result['record_time']);

            if($zeroTime > $recordTime){
                $data['login'] = $login;
                $data['count'] = 0;
                $this->updateRecord($data);

                return $data['count'];
            }else{

                return $result['count'];
            }
        }else if($result !== false){
            $data['login'] = $login;
            $data['count'] = 0;
            $this->addRecord($data);

            return $data['count'];
        }else{
            return false;
        }
    }

    /**
     * 增加一条记录
     * @param $data array 记录内容
     * @return mixed 数据库插入操作新增的id|操作失败返回false
     */
    public function addRecord($data){
        $map['login'] = $data['login'];
        $map['count'] = $data['count'];
        $nowTime = new DateTime();
        $map['record_time'] = $nowTime->format("Y-m-d H:i:s");
        return $this->add($data);
    }

    /**
     * 更新一条记录
     * @param $data array 记录内容
     * @return mixed 数据库更新操作影响的行数|操作失败返回false
     */
    public function updateRecord($data){
        $map['count'] = $data['count'];
        $nowTime = new DateTime();
        $map['record_time'] = $nowTime->format("Y-m-d H:i:s");

        return $this->where("login='{$data['login']}'")->save($map);
    }
}