<?php
/**
 * 公告附件表
 * @version TS3.0
 */
class NoticeAttachModel extends Model{
		
	/**
	 * 表名
	 * @var string
	 */
	protected $tableName = 'notice_attach';
	protected $fields = array("id",
			"noticeId",
			"attachId",
			"attach_type");
	
}
?>