<?php
/**
 * Solr空间服务
 * 
 * @author zqxiang@iflytek.com
 * @version 创建时间：2014-2-17 上午9:19:08
 */
class SolrZoneModel {
	
	/**
	 * 更新用户/名师工作室数据
	 * @param int $type 类别(前台传此参数的时候请使用已定义的常量, 参考ZoneTypeModel)
	 * @param int $id 用户uid或名师工作室gid
	 * 
	 * @return boolean
	 */
	public function update($type, $id) {
		$type = intval($type);
		if ($type == 0 || empty($id)) return false;
		
		/**
		 * 获取本地用户数据
		 * @var array
		 */
		if ($type == ZoneTypeModel::STUDIO) {
			$data = M('MSGroup', 'msgroup')->getMsgroupOnSolr($id);
		} else {
			$data = M('User')->getUserOnSolr($id);
		}
		if (empty($data)) return false;
		
		/**
		 * 生成数据
		 * @var array
		 */
		$data['id'] = $type . $data['zoneid'];
		$data['type'] = $type;
		if ($type == ZoneTypeModel::STUDIO) {
			$data['zonename'] = empty($data['zonename']) ? $data['username'] . '的名师工作室' : $data['zonename'];
		} else {
			$data['zonename'] = empty($data['zonename']) ? $data['username'] . '的工作室' : $data['zonename'];
		}
		/**
		 * 初始化
		 * @var Apache_Solr_Service
		 */
		$solr = new Solr('zone');
		if ($solr->ping() === false) return false;
		
		/**
		 * 执行更新
		 * @var mixed
		 */
		return $solr->add($data);
	}
	
	/**
	 * 删除用户或名师工作室
	 * @param int $type 类别(前台传此参数的时候请使用已定义的常量, 参考ZoneTypeModel)
	 * @param mixed $ids 用户id或名师工作室id(支持数组)
	 *
	 * @return boolean
	 */
	public function delete($type, $ids) {
		$type = intval($type);
		if ($type == 0 || empty($ids)) return false;
		
		/**
		 * 初始化
		 * @var Apache_Solr_Service
		 */
		$solr = new Solr('zone');
		if ($solr->ping() === false) return false;
		
		
		/**
		 * 合并删除条件
		 * @var Array
		 */
		if (is_array($ids)) {
			foreach ($ids as $key=>$id) {
				$ids[$key] = $type . $id;
			}
		} else {
			$ids = $type . $ids;
		}
		
		/**
		 * 执行删除
		 * @var mixed
		 */
		return $solr->delete($ids);
	}
	
	
	/**
	 * 更新用户微博数/名师工作室访问数
	 * @param int $type 类别(前台传此参数的时候请使用已定义的常量, 参考ZoneTypeModel)
	 * @param int $id 用户uid/名师工作室gid
	 * @param int $count 当前用户微博数/名师工作室访问数
	 * 
	 * @return boolean
	 */
	public function updateCount($type, $id, $count = 0) {
		$type = intval($type);
		if ($type == 0 || empty($id)) return false;
		
		
		/**
		 * 判断是否满足同步空间字段
		 * @var boolean
		 */
		if (C('SOLR_FIELD_UPDATE.ISOPEN') == true) {
			if (in_array($type, array(ZoneTypeModel::TEACHER, ZoneTypeModel::RESEARCHER))) {
				$p = C('SOLR_FIELD_UPDATE.USER');
			} else if ($type == ZoneTypeModel::STUDIO) {
				$p = C('SOLR_FIELD_UPDATE.MSGROUP');
			}
			krsort($p);
			$update = false;
			foreach ($p as $k=>$v) {
				if ($count >= $k && $count % $v == 0) {
					$update = true;
					break;
				}
			}
			if ($update == false) return false;
		}
		
		/**
		 * 获取本地用户数据
		 * @var array
		 */
		if ($type == ZoneTypeModel::STUDIO) {
			$user = M('MSGroup', 'msgroup')->getMsgroupOnSolr($id);
		} else {
			$user = M('User')->getUserOnSolr($id);
		}
		
		if (empty($user)) return false;
		
		/**
		 * 生成update数据
		 * @var array
		 */
		$user['id'] = $type . $user['zoneid'];
		$user['weibocount'] = $count;
		$user['type'] = $type;
		if ($type == ZoneTypeModel::STUDIO) {
			$data['zonename'] = empty($data['zonename']) ? $data['username'] . '的名师工作室' : $data['zonename'];
		} else {
			$data['zonename'] = empty($data['zonename']) ? $data['username'] . '的工作室' : $data['zonename'];
		}
		
		
		/**
		 * 初始化
		 * @var Apache_Solr_Service
		 */
		$solr = new Solr('zone');
		if ($solr->ping() === false) return false;
		
		/**
		 * 执行更新
		 * @var mixed
		 */
		return $solr->add($user);
	}


	/**
	 * 空间/名师工作室检索
	 * @param array $param 检索条件
	 * 									  array('keywords' =>, 搜索关键字  [必传]
	 * 											   'keywordsInResults' => '' 检索结果中检索的关键字 [非必须]
	 * 											   'type' =>, 空间类型(1：教师，2：教研员，3：家长，4：学生，5：名师工作室) [非必须]
	 * 											   'grade' =>,	年级/学段 [非必须]
	 * 											   'subject' =>, 学科 [非必须]
	 *  										   'province' =>, 省 [非必须]
	 *   										   'city' =>, 市 [非必须]
	 *    										   'county' =>, 区县 [非必须]
	 *     										   'school' =>, 学校 [非必须]
	 *      									   'level' =>, 级别 [非必须]
	 * 												)
	 * @param int $page 当前页 [非必须]
	 * @param int $limit [非必须]
	 * @param string $order 排序 (默认匹配度高的在前, 支持weibocount desc/asc) [非必须]
	 * 
	 * @return array
	 */
	public function search($param, $page = 1, $limit = 20,  $order = '') {
		/**
		 * 检索关键字验证
		 * @var String
		 */
		/* if (empty($param['keywords'])) return null; */
		
		/**
		 * 初始化
		 * @var Apache_Solr_Service
		 */
		$solr = new Solr('zone');
		if ($solr->ping() === false) return null;
		
		/**
		 * 设定模糊查询条件
		 * @var String
		 */
		$p = "*:*"; //默认为所有
		if (empty($param['keywordsInResults'])) {
			$p = !empty($param['keywords']) ? "zonename:{$param['keywords']} OR username:{$param['keywords']}" : '*:*';  //未使用"结果中搜索"功能
		} else {
			if (empty($param['keywords'])) {
				$p = "zonename:{$param['keywordsInResults']} OR username:{$param['keywordsInResults']}";  //支持检索关键字为空后， 一些人可能会直接在"结果中搜索"框中搜索
			} else {
				$p = "(zonename:{$param['keywords']} AND zonename:{$param['keywordsInResults']}) OR (username:{$param['keywords']} AND username:{$param['keywordsInResults']})";
			}
		}
		
		/**
		 * 根据类别[type]设定查询条件(1：教师，2：教研员，3：家长，4：学生，5：名师工作室) 
		 * @var String
		 */
		unset($param['keywords'], $param['keywordsInResults']);
		foreach ($param as $key=>$val) {
			$fq .= !empty($fq) ? ' AND ' : '';
			if ($key == 'subject' && strpos($val, ',') !== false) {
				$subject = explode(',', $val);
				$fq .= '(subject:' . implode(' OR subject:', $subject) . ')';
			} else {
				$fq .= "{$key}:{$val}";
			}
		}
		
		/**
		 * 合并查询条件
		 * @var mixed
		 */
		$offset = intval($page) > 1 ? (intval($page) - 1) * $limit : 0;
		$order = empty($order) ? 'weight desc' : $order . ',weight desc';//按权重排序	
		$config = array('fq' => $fq, 'sort' => $order, 'hl.fl' => 'zonename,username');
		
		/**
		 * 执行查询
		 * @var mixed
		 */
		return $solr->query($p, $offset, $limit, $config);
	}
	
}