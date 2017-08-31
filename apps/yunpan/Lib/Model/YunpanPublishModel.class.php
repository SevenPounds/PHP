<?php
/**
 * 云盘文件公开记录模型
 */
class YunpanPublishModel extends Model {
	protected $tableName = 'yunpan_publish';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'fid',
			2 => 'login_name',
			3 => 'dateline',
			4 => 'type',
			5 => 'open_position',
			6 => 'res_title',
			7 => 'is_del',
			8 => 'rid'
	);
	
	/**
	 * 云盘公开记录保存、更新
	 * @return 1:成功,0:失败,-1:参数不正确
	 * @param array $res_publish
	 * ("fid"=>资源id，"dateline"=>当前时间格式'Y-m-d H:i:s'，"login_name"=>登录用户名,
	 * "type"=>资源类型,"open_position"=>公开位置：01 个人主页 02 资源网关，"res_title"=>公开的资源标题)
	 */
	public function saveOrUpdate($res_publish) {
		if(!is_array($res_publish) || empty($res_publish)){
			return -1;
		}
		$result = $this->where(array("fid"=>$res_publish['fid'],"login_name"=>$res_publish['login_name'],'open_position'=>$res_publish['open_position']))->find();
		if(!$result){
			$result = $this->add($res_publish);
			return empty($result) ? 0 : 1;
		}else{
				$this->where(array('id'=>$result['id']))->save(array("dateline"=>$res_publish['dateline']));
				return 1;
		}
	}
	
	/**
	 * 获取用户公开记录列表
	 * @param string $login 用户登录名
	 * @param string $open_position 公开位置:01 个人主页 02 资源网关
	 * @param int $pageindex 页码
	 * @param int $pageSize 每页记录数
	 * @param string $sort 时间排序方式 DESC/ASC
	 * @param array $fields 返回字段 默认返回部分字段
	 * @return array 返回结果
	 */
	public function listPublish($login, $open_position, $pageindex = 1, $pageSize = 10, $sort = "DESC"){
		if(!$login){
			return array("false");
		}
		$order = "";
		if(strtoupper($sort) == "DESC"){
			$order = "dateline DESC";
		}else{
			$order = "dateline ASC";
		}
		$where = array("login_name"=>$login,"open_position"=>$open_position);
		$pageindex = intval($pageindex);
		if(!$pageSize || $pageindex <= 0){
			$pageindex = 1;
		}
		if(!$pageSize || $pageSize <=0 || $pageSize>=100){
			$pageSize = 10;
		}
		$start = ($pageindex - 1) * $pageSize;
		$list =  $this->where($where)
		->order($order)
		->limit("$start,$pageSize")
		->findAll();
		$result = array();
		foreach ($list as $d_res){
			$condition = array('include_deleted' => true);
			$file = D("CyCore")->Resource->Res_GetResIndex($d_res["rid"], $condition);//"8006628c91814fef85227601902ce8e6"
			$d_res["name"] = $d_res['res_title'];
			$d_res["extension"] = "";
			$d_res["previewurl"]=C("RS_SITE_URL").'/index.php?app=changyan&mod=Rescenter&act=detail&id='.$d_res["rid"];
			if($file->statuscode == 200){
				$d_res["resstatus"]=$file->data[0]->lifecycle->auditstatus;
				$d_res["name"] = $file->data[0]->general->title;
				$d_res["extension"] = $file->data[0]->general->extension;
			}
			array_push($result, $d_res);
		}
		return $result;
	}
	
	/**
	 * 获取用户公开记录列表总数
	 * @param string $login 登录用户名
	 * @param string $open_position 公开位置:01 个人主页 02 资源网关
	 * @return number
	 */
	public function getlistPublishCount($login, $open_position){
		if(!$login){
			return -1;
		}
		$where = array("login_name"=>$login,"open_position"=>$open_position,'is_del'=>0);
		return $this->where($where)->count();
	}
	
	/**
	 * 根据资源fid和用户登录名检查某个资源的分享情况
	 * @param string $fid 资源fid
	 * @param string $login 用户登录名
	 * @return array('per_page'=>0,'sys_res'=>0);返回分享位置数组，0表示未分享，1表示已分享
	 */
	public function checkSharePosition($fid,$login){
		$map = array('is_del'=>0,'fid'=>$fid,'login_name'=>$login);
		$list = $this->where($map)->select();
		$result = array('per_page'=>0,'sys_res'=>0,'class_share'=>0);
		foreach($list as $key=>$val){
			if($val['open_position'] == '01'){
				$result['per_page'] = 1;
			}else if($val['open_position'] == '02'){
				$result['sys_res'] = 1;
			}else if($val['open_position'] == '03'){
				$result['class_share'] = 1;
			}
		}
		return $result;
	}
	
	/**
	 * 根据fid数组和用户登录名删除个人主页分享记录
	 * @param array $fid 资源fid
	 * @param string $login 用户登录名
	 */
	public function deleteShare($fid,$login){
		$map = array('fid'=>$fid,'login_name'=>$login,'is_del'=>0,'open_position'=>'01');
		$result = $this->where($map)->select();
		foreach($result as $key=>$val){
			$r = $this->where(array('id'=>$val['id']))->save(array('is_del'=>'1'));
		}
	}
	
	/**
	 * 获取分享资源到学科资源排前几名的用户相关信息
	 * @param int $top 分享排前几名的
	 * @param int $limit 查询指定数量用户分享的记录
	 */
	public function getPublishTop($top = 3, $limit = 50){
		$openPosition = '02';
		$topUsers = $this->query("SELECT login_name,count(*) count FROM ts_yunpan_publish WHERE open_position = '$openPosition' GROUP BY login_name ORDER BY count(*) DESC");
		$topN = array_slice($topUsers,0,$top);

		// 查询每个用户指定数目的分享记录
		$map = array('open_position'=>$openPosition,'is_del'=>'0');
		foreach($topN as $key=>&$val){
			$map['login_name'] = $val['login_name'];
			$val['list'] = $this->where($map)->limit($limit)->order('dateline DESC')->select();
		}
		return $topN;
	}
}
?>