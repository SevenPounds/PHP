<?php
/**
 * 班级课程表
 * @author Dingyf
 *
 */
class ClassScheduleModel extends Model{ 
	protected $tableName = 'class_schedule';
	protected $fields = array(0=>'id',1=>'cid',2=>'course','_autoinc'=>true,'_pk'=>'id');
	
	public function updata_classSchedule($schMap){
		$map = "cid = '{$schMap['cid']}'";
		$org =  $this->where($map)->find();
		if($org){
			$this->save($schMap);
		}else{
			$this->add($schMap);
		}
		return true;
	}
	/**
	 * 获取课程表信息
	 * @param  $cid
	 * return array
	 */
	public function get_schinfo($cid){
		if(!$cid){
			return ;
		}
		$map = "cid = '{$cid}'";
		$sch =  $this->where($map)->find();
		return $sch;
	}
}
?>