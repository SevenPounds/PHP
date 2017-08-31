<?php
/**
 * @package msgroup\Lib\Model
 * @author zhaoliang <zhaoliang@iflytek.com>
 */
class MSGroupVisitorModel extends Model{
	protected $tableName = 'msgroup_visitor';
	protected $fields = array(1=>'id',
			2=>'gid',
			3=>'vuid',
			4=>'vtime');
	
	function  _initialize() {
		parent::_initialize();
	}
	
	/**
	 * 工作室新增访客
	 * @api
	 * @param int $gid <工作室ID>
	 * @param int $uid <访客ID>
	 */
	public function AddVisitor($gid, $uid){
		$visitor = array();
		$visitor['gid'] = $gid;
		$visitor['vuid'] = $uid;
		$visitor['vtime'] = time();
		$lastInsertId = $this->add($visitor);
		if ($lastInsertId > 0) {
			$visitorData = M('msgroup_data')->where(array('gid'=>$gid, 'key'=>'visitor_count'))->find();
			if (empty($visitorData)) {
				M('msgroup_data')->add(array('gid'=>$gid, 'key'=>'visitor_count', 'value'=>1, 'mtime'=>time()));
			} else {
				M('msgroup_data')->where(array('gid'=>$gid, 'key'=>'visitor_count'))->save(array('value' => array('exp', 'value+1'), 'mtime'=>time()));
			}
		}
		return $lastInsertId;
	}
	
	/**
	 * 获取工作室访客数量
	 * @api
	 * @param int $gid <工作室ID>
	 */
	public function GetVisitorCount($gid){
		$map = array('gid'=>$gid, 'key'=>'visitor_count');
		$count = M('msgroup_data')->where($map)->getField('value');
		return empty($count) ? 0 : $count;
	}
	
	/**
	 * 获取最近访客
	 * @api
	 * @param int $gid <工作室ID>
	 * @param int $limit <最近访客数量>
	 * @return array <返回访问信息(含访客uid)>
	 */
	public function GetVisitors($gid, $limit){
		$map = array("gid"=>$gid);
		return $this->where($map)->order("vtime desc")->limit($limit)->select();
	}
}
?>