<?php
include_once dirname(__FILE__).'/CyBaseModel.class.php';
/**
 * 校园模型 
 * @author cheng
 *
 */
class CySchoolModel extends CyBaseModel{

	/**
	 *  获取学校列表
	 * @param int $areaId 区域ID
	 * @param int $page 页码
	 * @param int $perpage 每页返回个数
	 * @return Ambigous <mixed, string, unknown>
	 */
	public function get_school($areaId=1125,$page=1,$perpage = 10){
		$cpList = S('list_school_'.$start.'_'.$perpage.'_'.$areaId);
		if(!$cpList){
			$cpList = $this->client->listSchoolByArea($areaId,true,($page-1)*$perpage,$perpage);
			$cpList = $this->muti_school_convert($cpList);
			S('list_school_'.$start.'_'.$perpage.'_'.$areaId, $cpList, null );
		}
		$unreal_page_count = $page*$perpage;
		if(!(count($cpList)-$perpage<0)){
			$npList = $this->client->listSchoolByArea($areaId,true,$page*$perpage,$perpage);
			$npList = $this->muti_school_convert($npList);
			$unreal_page_count = $unreal_page_count+count($npList);
		}  
		return $this->_get_pager($cpList,$perpage,$unreal_page_count);
	}
	
	/**
	 * 分页查询数据
	 * @param mixed $pageopt 分页参数
	 * @return mixed
	 */
	private function _get_pager($resultSet,$pageopt,$count){
		// 如果查询总数大于0
		if($count > 0&&count($resultSet)>0) {
			// 载入分页类
		//	import('ORG.Util.OrgPage');
			// 解析分页参数
			if( is_numeric($pageopt) ) {
				$pagesize	=	intval($pageopt);
			}else{
				$pagesize	=	intval(C('LIST_NUMBERS'));
			}
				
			$p	=	new Page($count,$pagesize);
		
			// 查询数据
			$options['limit']	=	$p->firstRow.','.$p->listRows;
		
			// 输出控制
			$output['count']		=	$count;
			$output['nowPage']		=	$p->nowPage;
			$output['html']			=	$p->show();
			$output['data']			=	$resultSet;
			unset($resultSet);
			unset($p);
			unset($count);
		}else{
			$output['count']		=	0;
			$output['totalRows']	=	0;
			$output['nowPage']		=	1;
			$output['html']			=	'';
			$output['data']			=	'';
		}
		// 输出数据
		return $output;
	}

	/**
	 * 获取学校信息
	 * @param  $cid
	 */
	public function get_school_info_by_id($sid){
		if(!$sid){
			return null;
		}
		$_result =  $this->client->getSchool($sid);
		return $this->school_convert($_result);
	}
	
	/**
	 * 通过多个ID获取多个的学校信息
	 * @param  $cid
	 */
	public function get_school_infos_by_ids($sids){
		$schoooles = array();
		foreach ($sids AS $sid){
			if(!$sid){
				continue;
			}
			$schoolInfo =  $this->client->getSchool($sid);
			$schoolInfo = $this->school_convert($schoolInfo);
			$schoooles[$sid] = $schoolInfo;
		}
		return $schoooles;
	}
	
	/**
	 * 获取组织列表   
	 * @param  $type
	 * @param  $key  比较关键字
	 * @param  $order
	 * @param  $limit
	 */
	public function get_orglist($type,$key,$order,$limit){
		$newOrgs = array();
		$orgs = D('ClassData','class')->getClassList($type,$key,$order,$limit);
		if(empty($orgs)){
			return null;
		}
		$fids = getSubByKey($orgs['data'],'fid');
		foreach($fids as $org_id){
			if(!$org_id){
				continue;
			}
			if($type == 1){
				$school =  $this->client->getSchool($org_id);
				$newOrgs[$org_id] = $this->school_convert($school);
			}else{
				$newOrgs[$org_id] = D("CyClass")->get_class_info_by_id($org_id);
			}
		}
		return $newOrgs;
	}
	
	/**
	 * 更新学校信息
	 * @param HashMap<String, Object> $orgMap 组织机构信息，key-value键值对json，参考@see Organization 对象定义
	 * `remarks` String '备注信息'
	 */
	public function update_school($orgMap){
		return $this->client->updateSchool($orgMap);
	}
	
	/**
	 * 获取用户所在学校
	 * @param unknown_type $userId
	 * @param unknown_type $skip
	 * @param unknown_type $limit
	 */
	public function list_school_by_user($userId, $skip = 0, $limit = 100){
		if(empty($userId)) return array();
        $_result = S('list_school_by_user_'.$userId."_".$skip."_".$limit);
        if(!$_result){
            $_result =  $this->client->listSchoolByUser($userId, $skip, $limit);
            $_result = $this->muti_school_convert($_result);
            S('list_school_by_user_'.$userId."_".$skip."_".$limit, $_result, 3600);
        }
		return $_result;
	}
	
	/**
	 * 获取学校列表 
	 * @param int $areaId 合肥市：1125
	 * @param boolean $withAllChildren
	 * @param int $start
	 * @param int $perpage
	 * @return void|Ambigous <boolean, unknown, multitype:Ambigous <multitype:, multitype:number NULL > >
	 */
	public function list_school_by_area($areaId=3173,$withAllChildren = false,$start=0,$perpage = 1000){
		if(!$areaId) return array();
		$_result = S('list_school_'.$start.'_'.$perpage.'_'.$areaId);
		if(!$_result){
			$_result = $this->client->listSchoolByArea($areaId,$withAllChildren, $start, $perpage);
			$_result = $this->muti_school_convert($_result);
			S('list_school_'.$start.'_'.$perpage.'_'.$areaId, $_result, null );
		}
		return $_result;
	}
	
	/**
	 * 获得学校的信息(在动态中使用)
	 * @param int $cid
	 * @author yxxing
	 */
	public function get_shool_info_for_feed($cid){
		$shoolInfo = S('school_info_id_feed_'.$cid);
		if(empty($shoolInfo)){
			$org = $this->get_school_info_by_id($cid);
			$campusAvatar = getCampuseAvatar($cid, $org['type']);
			
			$shoolInfo = array();
			$shoolInfo['cid'] = $org['id'];
			$shoolInfo['uname'] = $org['name'];
			$shoolInfo['type'] = $org['type'];
			$shoolInfo['space_url'] = getHomeUrl($org);
			$shoolInfo['space_link'] = "<a href='".$shoolInfo['space_url']."' class='name' event-node='face_card' orgType='".$shoolInfo['type']."' cid='".$shoolInfo['cid']."'>".$shoolInfo['uname']."</a>";
			$shoolInfo['avatar_small'] = $campusAvatar['avatar_small'];
			S('school_info_id_feed_'.$cid, $shoolInfo, 3600);
		}
		return $shoolInfo;
	}
	
	/**
	 * 获取学校列表
	 * @param int $skip
	 * @param int $limit
	 */
	public function list_school($skip = 0, $limit = 100){
		$_result = $this->client->listSchool($skip,$limit);
		return $this->muti_school_convert($_result);
	}
	
	
	
}
?>