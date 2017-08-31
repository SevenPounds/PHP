<?php
/**
 * @package msgroup\Lib\Model
 */
class MSGroupAnnounceAttachModel extends Model{
	protected $tableName = 'msgroup_notice_attach';
	protected $fields = array(1=>'id',
			2=>'notice_id',
			3=>'attach_id',
			4=>'attach_type');
	
	function  _initialize()	{
		parent::_initialize();
	}
	
	/**
	 * 获取资讯文章类的所有附件
	 * @param int $notice_id <资讯文章ID>
	 * @return array <附件列表>
	 */
	public function GetAttachs($notice_id){
		$map = array('notice_id'=>$notice_id);
		return $this->where($map)->select();
	}
	
	/**
	 * 为资讯文章类添加单个附件
	 * @param int $notice_id <资讯文章ID>
	 * @param int $attach_id <附件ID>
	 * @param int $attach_type <附件类型(0：普通附件  1：网关资源附件)>
	 * @return int <返回刚刚添加的附件ID>
	 */
	public function AddAttach($notice_id, $attach_id, $attach_type){
		return $this->add(array("notice_id"=>$notice_id,"attach_id"=>$attach_id,"attach_type"=>$attach_type));
	}
	
	/**
	 * 为资讯文章类添加多个附件
	 * @param int $notice_id <资讯文章ID>
	 * @param array $attach_ids <附件ID数组>
	 * @param int $attach_type <附件类型(0：普通附件  1：网关资源附件)>
	 */
	public function AddAttachs($notice_id,$attachments){
		foreach($attachments as $attach_id=>$attach_type){
			$this->add(array("notice_id"=>$notice_id,"attach_id"=>$attach_id,"attach_type"=>$attach_type));
		}
		return true;
	}
	
	/**
	 * 删除资讯文章的附件
	 * @param int $attach_id <附件ID>
	 * @return <返回受影响的行数，出错返回false>
	 */
	public function deleteAttachByID($attach_id){
		$map = array();
		$map["attach_id"] = $attach_id;
		//获取附件属性，若为普通附件，彻底删除，否则只删除数据库记录
		$attach = $this->where($map)->find();
		if($attach){
			$attach_type = $attach['attach_type'];
			if($attach_type == 0){
				D('Attach')->doEditAttach($attach_id, 'delAttach', '');
			}
		}
		return $this->where($map)->delete();
	}
	
	/**
	 * 批量删除附件
	 * @param array $attachments 
	 */
	public function deleteAttachs($attachments){
		foreach($attachments as $attach_id=>$attach_type){
			$this->deleteAttachByID($attach_id);
		}
	}
}
?>