<?php
/**
 * 本地组织信息
 * @author cheng
 *
 */
class OrgInfoModel extends Model{
	protected $tableName = 'class';
	protected $fields = array (
			0 => 'fid',
			1 => 'uid',
			2 => 'cname',
			3 => 'intro',
			4 => 'logo',
			5 => 'announce',
			6 => 'membercount',
			7 => 'type',
			8 => 'ctime',
			9 => 'mtime',
			10 => 'is_del',
			11 => 'master',
			12 => 'contact',
			13 => 'phone',
			14 => 'id',
			'_pk' => 'id' 
	);
	
	public function updata_organizations($orgMap){
		$_sorgMap['id'] = intval($orgMap['fid']);
		if($orgMap['type']==1){
			$_sorgMap['address'] = $orgMap['contact'];
			$_sorgMap['mobile'] = $orgMap['phone'];
		}
		if($orgMap['type'] == 1){
			$res = D('CySchool')->update_school($_sorgMap);
		}else{
			$res = D('CyClass')->update_class($_sorgMap);
		}
		$type=$orgMap['type']==1?'Campus':'Class';
		$org = S( $type.'_'.$orgMap['fid']);  //替换缓存
		$org['name'] =  $orgMap['cname'];
		if($orgMap['type']==1){
			$org['address'] = $orgMap['contact'];
			$org['mobile'] = $orgMap['phone'];
		}
		S( $type.'_'.$orgMap['fid'],$org,null);
		
		if(!$res){
			return $res;
		}
		$orgMap['logo'] = "";
		$orgMap['announce'] = "";
		$orgMap['cname'] = "";
		$map = "fid = '{$orgMap['fid']}' AND is_del=0 AND type=".$orgMap['type'];
		$org =  $this->where($map)->find();//->table($this->tableName)
	   if($org){
			$this->where($map)->save($orgMap);
		}else{
			$this->add($orgMap);
		} 
		return true;
	}
	
	/**
	 * 获取本地组织信息 
	 * @param  $cid
	 * return array
	 */
	public function get_orginfo($cid,$type=1){
		if(!$cid){
			return ;	
		}
		$map = "fid = '{$cid}' AND is_del=0 AND type=".$type;
		$org =  $this->where($map)->find();
		return $org;
	}


}