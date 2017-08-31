<?php
/**
 * 系统模型 - 处理SNS中使用yun_开头的部分表操作，不采用配置中的ts前缀
 * @author dmhu 2015.11.26
 * @version TS3.0
 */
class YunFileShareModel extends Model
{
    // 数据表前缀
    protected $tablePrefix  =   '';


    public function __construct($name='')
    {
        parent::__construct();
        // 设置表前缀
        $this->tablePrefix = '';
    }
}