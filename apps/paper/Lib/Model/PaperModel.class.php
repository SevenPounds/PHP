<?php
require_once './apps/paper/Common/appInfo.php';
/**
 * 论文模型
 * @author hhshi
 */
class PaperModel extends Model {
	protected $tableName = 'paper';
	protected $error = '';
	protected $fields = array (
			0 => 'id',
			1 => 'uid',
			2 => 'class_id',
			3 => 'name',
			4 => 'title',
			5 => 'category',
			6 => 'category_title',
			7 => 'cover',
			8 => 'content',
			9 => 'readCount',
			10 => 'commentCount',
			11 => 'shareCount',
			12 => 'recommendCount',
			13 => 'tags',
			14 => 'cTime',
			15 => 'mTime',
			16 => 'rTime',
			17 => 'isHot',
			18 => 'type',
			19 => 'status',
			20 => 'private',
			21 => 'private_data',
			22 => 'hot',
			23 => 'canableComment',
			24 => 'attach',
			25 => 'feed_id' 
	);
	
	/**
	 * 添加新的论文
	 * @param array $paper 论文对象
	 */
	public function insertPaper($paper) {

		// 给新增文章一个默认类型：教学论文
		if(empty($paper['category'])){
			$paper['category'] = 1;
		}
		$return = $this->add($paper);
		if($return){
			//更新缓存
			S("researcher_last_fivepaper_uid_".$paper['uid']."_type_".$paper['category'], NULL);
			//更新统计的用户文章数
			D("UserData")->updateKey("article_count", 1, true, $this->uid);
		}
		return $return;	
	}
	
	/**
	 * 根据用户id和论文id生成动态
	 * @param int $mid
	 * @param int $paperId
	 * @param string $title
	 * @param string $content
	 * @param int $type 动态类型 (1.教学论文 2.教学反思 3.我的收获)
	 */
	public function syncToFeed($mid, $paperId, $title, $content, $type){
		$d['content'] = '';
		$d['source_url'] = " ".U('public/Workroom/summary_preview', array('uid'=>$mid, 'pid'=>$paperId, 'type'=>$type));
		$d['body'] = "我发表了一篇".getAppName($type)."【".getShort($title,30,'...')."】".getShort($content,40,'...');
		
		// 获取动态类型
		if($type == appInfo::Paper){
			$feed_type = "paper";
		}else if($type == appInfo::Reflection){
			$feed_type = "reflection";
		}else if($type == appInfo::Harvest){
			$feed_type = "harvest";
		}else{
			$d['source_url'] = " ".U('public/Workroom/instructionDetail', array('uid'=>$mid, 'paper_id'=>$paperId, 'type'=>$type));
			$feed_type = "guidances";
		}

		$feed = model('Feed')->put($mid, 'paper', $feed_type, $d);
		return $feed['feed_id'];
	}
	
	/**
	 * 根据论文id和用户id查询论文信息
	 * @param int $paperId 论文id
	 * @param int $uid 用户id
	 */
	public function selectPaperByIdAndUid($paperId, $uid){
		$map = array();
		$map['id'] = $paperId;
		$map['uid'] = $uid;
		return $this->table($this->tablePrefix.''.$this->tableName)->where($map)->select();
	}
	/**
	 * 根据论文id查询论文信息
	 * @param int $paperId 论文id
	 */
	public function selectPaperById($paperId){
		$map = array();
		$map['id'] = $paperId;
// 		dump($this->table($this->tablePrefix.''.$this->tableName)->where($map)->find());
// 		exit;
		return $this->table($this->tablePrefix.''.$this->tableName)->where($map)->find();
	}
	
	/**
	 * 根据用户id、论文发布时间和应用类型编码查询该论文的下一篇相关信息
	 * @param int $uid 用户id
	 * @param array $condition 查询条件
	 */
	public function selectNextPaper($uid, $condition){
		$map = array();
		$map['uid'] = $uid;
		$map['cTime'] = array('LT', $condition['cTime']);
		$map['category'] = $condition['category'];
		$map['private'] = array('IN', $condition['private']);
		return $this->table($this->tablePrefix.''.$this->tableName)->where($map)->order('cTime desc')->limit(1)->select();
	}
	
	/**
	 * 根据用户id、论文发布时间和应用类型编码查询该论文的上一篇相关信息
	 * @param int $uid 用户id
	 * @param array $condition 查询条件
	 */
	public function selectPrevPaper($uid, $condition){
		$map = array();
		$map['uid'] = $uid;
		$map['cTime'] = array('GT', $condition['cTime']);
		$map['category'] = $condition['category'];
		$map['private'] = array('IN', $condition['private']);
		return $this->table($this->tablePrefix.''.$this->tableName)->where($map)->order('cTime')->limit(1)->select();
	}
	
	/**
	 * 根据用户id查询用户论文集合
	 * @param int $uid 用户id
	 * @param array $condition 查询条件
	 */
	public function selectPapersByUid($uid, $condition){
		$map = array();
		$map['uid'] = $uid;
		$map['category'] = $condition['type'];
		$map['private'] = array('IN', $condition['private']);
		return $this->table($this->tablePrefix.''.$this->tableName)->where($map)->order('cTime desc')->select();
	}
	
	/**
	 * 根据用户id和分页条件查询用户论文集合
	 * @param int $uid 用户id
	 * @param array $condition 查询条件
	 */
	public function selectPapersByPage($uid, $condition,$order='cTime desc'){
		$map = array();
		$map['uid'] = $uid;
		$map['category'] = $condition['type'];
		$map['private'] = array('IN', $condition['private']);
		return $this->table($this->tablePrefix.''.$this->tableName)->where($map)->order($order)->page($condition['page'])->select();
	}
	
	/**
	 * 根据用户id和查询条件，获取该条件下论文总数
	 */
	public function getPaperCount($uid, $condition){
		$map = array();
		$map['uid'] = $uid;
		$map['category'] = $condition['type'];
		$map['private'] = array('IN', $condition['private']);
		return $this->table($this->tablePrefix.''.$this->tableName)->where($map)->count();
	}
	
	/**
	 * 根据论文id删除论文
	 * @param int $paperId 论文id
	 * @param int $mid 用户id
	 */
	public function deletePaperById($paperId, $mid){
		$map = array();
		$map['id'] = $paperId;
		$map['uid'] = $mid;
		$type = $this->where($map)->getField("category");
		$return = $this->where($map)->delete();
		if($return){
			//更新缓存
			S("researcher_last_fivepaper_uid_".$mid."_type_".$type, NULL);
			//更新统计的用户文章数
			D("UserData")->updateKey("article_count", -1, false, $this->uid);
		}
		return $return;
	}
	
	/**
	 * 根据论文id和用户id更新论文内容
	 * @param array $paper 论文对象
	 */
	public function updatePaperById($paper){
		$map = array();
		$map['id'] = $paper['id'];
		$map['uid'] = $paper['uid'];
		$return = $this->where($map)->data($paper)->save();
		if($return){
			$type = $this->where($map)->getField("category");
			S("researcher_last_fivepaper_uid_".$map['uid']."_type_".$type, NULL);
		}
		return $return;
	}
	
	/**
	 * 根据用户id和文章id浏览量加一
	 * @param int $uid 用户id
	 * @param int $pid 文章id
	 */
	public function viewCountAdd($uid, $pid){
		$map = array();
		$map['id'] = $pid;
		$map['uid'] = $uid;
		return $this->where($map)->setInc('readCount');
	}
	
	/**
	 * 根据论文id和用户id修改论文隐私状态
	 * @param int $id
	 * @param int $mid
	 * @param int $privacyId
	 */
	public function updatePaperPrivacyById($id, $mid, $privacyId){
		$map = array();
		$map['id'] = $id;
		$map['uid'] = $mid;
		$paper = array();
		$paper['private'] = $privacyId;
		$return = $this->where($map)->data($paper)->save();
		if($return){
			$type = $this->where($map)->getField("category");
			S("researcher_last_fivepaper_uid_".$map['uid']."_type_".$type, NULL);
		}
		return $return;
	}
	
	/**
	 * 获取某一用户的最新文章
	 * 只在limit是5时使用缓存
	 * @param int $type
	 * @param int $uid
	 */
	public function getLastPapers($uid, $type, $limit){
		require_once './apps/paper/Common/privacyInfo.php';
		$limit == 5 && $result = S("researcher_last_fivepaper_uid_".$uid."_type_".$type);
		if(!$result){
			$limit = intval($limit);
			$map = array();
			$map['category'] = $type;
			$map['uid'] = $uid;
			$map['private'] = privacyInfo::All;
			$result = $this->where($map)->field(array("title","category", "uid", "cTime", "id"))->limit("0,".$limit)->order("cTime DESC")->select();
			$limit == 5 && S("researcher_last_fivepaper_uid_".$uid."_type_".$type, $result, 36000);
		}
		return $result;
	}
	/**
	 * 获取指定条数的问题列表
	 * @param int $limit 取前多少条记录
	 * @return array 指定条数的问题列表
	 */
	public function getPaperList($uid,$num = 5,$condition){
		$map = array();
		$map['uid'] = $uid;
		$map['category'] = $condition['type'];
		$map['private'] = array('IN', $condition['private']);
		return  $this->where("uid='$uid' AND isDeleted=0")->where($map)->order('ctime desc')->page($condition['page'])->findpage($num);		 
		}

}
?>