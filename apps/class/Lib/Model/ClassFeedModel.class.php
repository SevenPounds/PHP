<?php
class ClassFeedModel extends Model{
	protected $tableName = 'class_follow';
	protected $fields = array(0=>'class_feed_id',1=>'feed_id',2=>'user_id',3=>'class_id',4=>'user_name'
			,5=>'class_name',6=>'school_id',7=>'school_name',8=>'publish_time','_autoinc'=>true,'_pk'=>'class_feed_id');
	
	
	/**
	 * 获取给定班级ID的最新的一条动态信息
	 * @param array $classid 班级ID
	 * @return array 给定微博ID的微博信息
	 */
	public function getClassFeed($classid,$limit=1,$where) {
		$table = "{$this->tablePrefix}class_feed AS cf LEFT JOIN {$this->tablePrefix}feed AS f ON cf.feed_id=f.feed_id AND 
		LEFT JOIN {$this->tablePrefix}feed_data AS fd ON f.feed_id=fd.feed_id AND cf.class_id ={$classid}";
		// 获取数据
		$feed = $this->table($table)->field('*')->order('cf.publish_time DESC')->findPage($limit);
		return $feed;	
	}
	
}
?>