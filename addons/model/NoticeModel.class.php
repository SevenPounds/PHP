<?php
/**
 * 公告model
 * @author xypan
 * @version TS3.0
 */
class NoticeModel extends Model{
		
	/**
	 * 表名
	 * @var string
	 */
	protected $tableName = 'notice';
	
	/**
	 * 新增公告
	 * @param array $data 要存入的数据
	 * @return int|boolean 如果数据非法或者查询错误则返回false 如果是自增主键 则返回主键值，否则返回1
	 */
	public function addNotice($data){
		
		// 创建时间
		$data['ctime'] = time();
		
		// 浏览次数初始化为0
		$data['viewcount'] = 0; 
		
		// 公告删除标记(默认为0,表示未删除)
		$data['isDeleted'] = 0;
		
		$res = $this->add($data);
		
		// 公告创建成功，则存入附件信息
		if($res && !empty($data['attach_ids'])){
			// 存入附件数据
			foreach ($data['attach_ids'] as $attach){
				D("NoticeAttach")->add(array("noticeId"=>$res,"attachId"=>$attach[0],"attach_type"=>$attach[2]));
			}
			
		}
		//更新统计的用户文章数
		D("UserData")->updateKey("article_count", 1, true, $this->uid);
		
		return $res;
	}
	
	/**
	 * 新增学校公告
	 * @param array $data 要存入的数据
	 * @return int|boolean 如果数据非法或者查询错误则返回false 如果是自增主键 则返回主键值，否则返回1
	 */
	public function addSchoolNotice($data){
		
		// 创建时间
		$data['ctime'] = time();
		
		// 浏览次数初始化为0
		$data['viewcount'] = 0; 
		
		// 公告删除标记(默认为0,表示未删除)
		$data['isDeleted'] = 0;
		
		$res = $this->add($data);
		
		// 公告创建成功，则存入附件信息
		if($res && !empty($data['attach_ids'])){

			$ids = explode('|', trim($data['attach_ids'], '|'));

			// 存入附件数据
			foreach ($ids as $id){
				D("NoticeAttach")->add(array("noticeId" => $res, "attachId" => $id, "attach_type" => 'attach_file'));
			}
			
		}
		//更新统计的用户文章数
		D("UserData")->updateKey("article_count", 1, true, $this->uid);
		
		return $res;
	}
	/**
	 * 
	 * @param int $page 请求页
	 * @param int $condition 查询条件
	 * @param int $num 每页记录数
	 * @param string $order 排序
	 * @return array 公告列表
	 */
	public function getNoticeLists($page,$condition,$num,$order){
		return $this->where($condition)->order("$order")->page("$page,$num")->select();
	}
	
	/**
	 * 查询指定条件的公告数
	 * @param array $condition 查询条件
	 * @return int 符合条件的公告数
	 */
	public function getNoticeCount($condition){
		return $this->where($condition)->count();
	}
	
	/**
	 * 查询指定的未删除的公告
	 * @param array $condition
	 * @return array 公告信息
	 */
	public function getNoticeDetail($condition){
		
		return $this->where($condition)->find();
	}
	
	/**
	 * 更新公告
	 * @param int $id 公告id
	 * @param array $data 新数据
	 * @return int|boolean 如果查询错误或者数据非法返回false 如果更新成功返回影响的记录数
	 */
	public function updateNotice($id,$data){
		
		$res = $this->where("id=$id")->save($data);
		
		// 排除只更新附件信息产生的BUG
		if($res !== false){
			$res = true;
		}
		
		// 公告信息更新成功，则存入附件信息
		if($res){
			if(!empty($data['attach_ids'])){
				foreach ($data['attach_ids'] as $attach){
					if(!isset($attach[3])){
						$r = D("NoticeAttach")->add(array("noticeId"=>$id,"attachId"=>$attach[0],"attach_type"=>$attach[2]));
						$_result = $r || $_result;
					}
				}
			}
		}
		
		return $res;
	}
	
	/**
	 * 删除指定的公告
	 * @param $id 公告id
	 * @return int|boolean 如果查询错误或者数据非法返回false 如果更新成功返回影响的记录数
	 */
	public function deleteNotice($id){
		//更新统计的用户文章数
		D("UserData")->updateKey("article_count", -1, true, $this->uid);
		return $this->where("id=$id")->setField('isDeleted',1);
		
	}
	/**
	 * 发布动态
	 * @param unknown_type $mid
	 * @param unknown_type $paperId
	 * @param unknown_type $title
	 * @param unknown_type $content
	 */
	public function syncToFeed($mid, $paperId, $title, $content, $type){
		$d['content'] = '';
		$d['source_url'] = " ".U('public/Workroom/teacher_preview', array('id'=>$paperId,"uid"=>$mid));
		switch($type){
			case 3:
				$d['body'] = '我发表了一篇教研动态【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
				break;
			case 4:
				$d['body'] = '我发表了一篇教育快讯【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
				break;
			default:
				$d['body'] = '我发表了一篇教研公告【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
		}
		$feed = model('Feed')->put($mid, 'eduannounce', 'eduannounce', $d);
		return $feed;
	}
}
?>
