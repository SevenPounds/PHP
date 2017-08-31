<?php
include_once dirname(__FILE__).'/CyBaseModel.class.php';
/**
 * 教育组织机构
 * @author yxxing
 *
 */
class CyEduOrgModel extends CyBaseModel{
	
	
	/**
	 * 获取组织机构
	 * @param int $parentId 查询当前节点下机构时，$parentId为当前节点的id
	 * @param int $skip
	 * @param int $limit
	 *
	 * @return array
	 */
	public function listEduorgByParentId($parentId, $skip = 0, $limit = 100) {
	//	if(empty($parentId))  return array();
		$_result =  $this->client->listEduorgByParentId($parentId, $skip, $limit);
		return $this->muti_eduorg_convert($_result);
	}

    /**
     * 获取用户所在教育组织
     * @param int $userId
     * @param int $skip
     * @param int $limit
     * @return array|bool|string
     */
	public function listEduorgByUser($userId, $skip = 0, $limit = 100){
		if(empty($userId))  return array();
        $_result = S("listEduorgByUser_".$userId."_".$skip."_".$limit);
        if(!$_result){
            $_result =  $this->client->listEduorgByUser($userId, $skip, $limit);
            $_result = $this->muti_eduorg_convert($_result);
            S("listEduorgByUser_".$userId."_".$skip."_".$limit, $_result,3600);
        }
		return $_result;
	}
	

	
	
}
?>