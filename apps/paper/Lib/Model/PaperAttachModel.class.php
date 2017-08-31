<?php
/**
 * 文章附件
 */
class PaperAttachModel extends Model {
	protected $tableName = 'paper_attach';
	protected $error = '';
	protected $fields = array (
			0 => 'id',
			1 => 'paper_id',
			2 => 'attach_id',
			3 => 'attach_type'
	);
	
	/**
	 * 添加附件
	 * @param unknown_type $paper_attach
	 * @return 插入结果
	 */
	public function addAttach($paper_id,$attach_id,$attach_type){
		return $this->add(array("paper_id"=>$paper_id,"attach_id"=>$attach_id,"attach_type"=>$attach_type));
	}
	
	public function deleteAttachByID($attach_id){
		$map = array();
		$map["id"] = $attach_id;
		return $this->where($map)->delete();
	}
	
	public function deleteAttach($paper_attach){
		$map = $paper_attach;
		return $this->where($map)->delete();
	}
	
	public function selectAttachByPaperID($paper_id){
		$map = array();
		$map['paper_id'] = $paper_id;
		return $this->table($this->tablePrefix.''.$this->tableName)->where($map)->select();
	}
}
?>