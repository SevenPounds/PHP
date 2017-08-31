<?php 
class GroupTagModel extends Model {
	var $tableName = 'group_tag';

	// 设置群组tag
	public function setGroupTag($tagname, $gid)	{
		$tagname = str_replace(' ', ',', $tagname);
		$tagname = str_replace('，', ',', $tagname);
		$tagInfo = $this->__addTags($tagname, 0);
		if ($tagInfo) {
			foreach ($tagInfo as $k=>$v) {
				$groupTagInfo = $this->where("gid=$gid AND tag_id=" . $v['tag_id'])->find();
				if (!$groupTagInfo) {
					$data['gid'] = $gid;
					$data['tag_id'] = $v['tag_id'];
					if ($v['group_tag_id'] = $this->add($data)) {
						$tagdata[] = $v;
						$tagids[]  = $v['tag_id'];
					}
				} else {
					$tagids[]  = $v['tag_id'];
				}
			}
			if ($tagids) {
				$delete_map['gid']    = $gid;
				$delete_map['tag_id'] = array('not in', $tagids);
				$this->where($delete_map)->delete();
		    	$return['code'] =  '1' ;
			} else {
				$return['code'] =  '0' ;
			}
		} else {
			$return['code'] =  '-1';
		}
		return $return['code'];
	}

	//添加全局tag
	private function __addTags($tagname, $nowcount)	{
		if(!$tagname) return false;
		$tagname = str_replace(' ', ',', $tagname);
		$tagname = str_replace('，', ',', $tagname);
		$tagname = explode(',', $tagname);
		foreach($tagname as $k=>$v){
			$v = preg_replace('/\s/i', '', $v);
			if( mb_strlen($v, 'UTF-8') > '10' || $v == '')continue;
			$result[] = $this->__addOneTag($v);
			$addcount = $addcount+1;
			if( $addcount+$nowcount >= 5 )break;
		}
		return $result;
	}

	private function __addOneTag($tagname){
		$map['name'] = t($tagname);
		if( $info = D('tag')->where($map)->find() ){
			return $info;
		}else{
			$map['tag_id'] = D('tag')->add($map);
			return $map;
		}
	}


	// 获取指定群组Tag列表
	public function getGroupTagList($gid){
		$base_cache_id = 'group_tag_';

		if (($res = model('Cache')->get($base_cache_id . $gid)) === false) {
			$this->setGroupTagObjectCache(array($gid));
			$res  = model('Cache')->get($base_cache_id . $gid);		
		}
		return $res;
	}

	public function setGroupTagObjectCache(array $gids){
		if (!is_numeric($gids[0]))
			return false;

		$base_cache_id = 'group_tag_';
		$gids = implode(',', $gids);		
		$res  = $this->field('a.*,b.name')
			 		 ->table("{$this->tablePrefix}{$this->tableName} AS a LEFT JOIN {$this->tablePrefix}tag AS b ON b.tag_id=a.tag_id")
			 		 ->where("a.gid IN ( {$gids} )")
			 		 ->order('a.group_tag_id ASC')
			 		 ->findAll();

		// 注: 每个群组最多含有5个标签
		$group_tags = array();
		foreach ($res as $v) {
			if (count($group_tags[$v['gid']]) >= 5)
				continue;
			else
				$group_tags[$v['gid']][] = $v;
		}
		
		foreach ($group_tags as $k => $v)

			model('Cache')->set($base_cache_id . $k, $v);
		
		return $res;
	}

	// 热门群组标签
	public function getHotTags($recommend = null, $limit = 8){
		if ('recommend' == $recommend) {
			$hot_tags = model('Xdata')->get('group:hotTags');
			$hot_tags = array_filter(array_unique(explode('|', $hot_tags)));
			return $hot_tags;
		} else {
			// 1小时锁缓存 
	    	if(!($cache = S('Cache_Hot_Tags'))){
	    		S('Cache_Hot_Tags_t',time()); //缓存未设置 先设置缓存设定时间	
	    	}else{
	    		if(!($cacheSetTime =  S('Cache_Hot_Tags_t')) || $cacheSetTime+3600 <= time()){
	    			S('Cache_Hot_Tags_t',time()); //缓存未设置 先设置缓存设定时间	
	    		}else{
	    			return $cache;
	    		}
	    	}	
    		// 缓存锁结束
			$cache = $this->field('a.tag_id,b.tag_name,count(a.tag_id) AS `count`')
			 			 ->table("{$this->tablePrefix}{$this->tableName} AS a LEFT JOIN {$this->tablePrefix}tag AS b ON b.tag_id=a.tag_id")
			 			 ->group('a.tag_id')
			 			 ->order('`count` DESC')
			 			 ->limit($limit)
			 			 ->findAll();
			 S('Cache_Hot_Tags',$cache);
			 return $cache;			 
		}
	}
}
?>