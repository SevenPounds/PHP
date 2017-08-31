<?php
/**
 * 班级作业模型
 * @author sjzhao<sjzhao@iflytek.com>
 * @version 1.0
 */
class ClassHomeworkModel extends Model{
	protected $tableName = 'class_homework';
	protected $fields =	array(0 => 'id',1=>'cid',2=>'cname',3=>'ctime',4=>'content',5=>'cuid',6=>'cuname');
	/**
	 * 根据班级id获取班级的作业内容
	 * @param int $cid
	 * @return 班级作业内容
	 * @author sjzhao
	 */
	public function getHomeworkByCid($cid,$pagesize,$order='ctime DESC'){
		$map['cid']=$cid;
		$data=$this->where($map)->order($order)->findPage($pagesize);
	    return $data;
	}
}
?>