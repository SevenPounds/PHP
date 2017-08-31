<?php
/**
 * 应用中心-应用类型模型
 * @author hhshi
 * @version 0.1
 */
class AppcenterCategoryModel extends Model{
	protected $tableName = 'appcenter_category';
	protected $error = '';
	protected $fields = array(
			0 => 'cid',
			1 => 'cname',
			2 => 'status',
			3 => 'ctime',
			4 => 'discription'
	);
	
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * 获取所有应用分类
	 */
	public function getAllCategory(){
		$map = array('status'=>'1');
		$category = $this->where($map)->select();
		return $category ? $category : array();
	}
}
?>