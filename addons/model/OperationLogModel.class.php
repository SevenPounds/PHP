<?php
/**
 * Created by PhpStorm.
 * User: yfsong
 * Date: 2015/11/6
 * Time: 17:30
 */

class  OperationLogModel  extends Model{
    protected $tableName = 'operation_log';
    protected $fields =	array(0 => 'id',1=>'cyUserId',2=>'appName', 3=>'cTime',4=>'role',5=>'provinceId',
        6=>'cityId',7=>'districtId',8=>'schoolId',9=>'remark',10=>'actionName');

    /**
     * @param $operationLog 添加一条操作日志
     * @return array
     */
    public function addOperationLog($operationLog){
        $operationLog['cTime']=date('y-m-d h:i:s',time());
        $res=$this->add($operationLog);
       /* if($res) {
            $return = array('status'=>1);
        }*/
        return array('status'=>0);
    }
} 