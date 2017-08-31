<?php
/**
 * 应用中心-应用模型
 * @author hhshi
 * @version 0.1
 */
class AppcenterAppModel extends Model{
	protected $tableName = 'appcenter_app';
	protected $error = '';
	protected $fields = array(
			0 => 'appid',
			1 => 'cid',
			2 => 'app_en_name',
			3 => 'app_zh_name',
			4 => 'status',
			5 => 'discription',
			6 => 'viewcount',
			7 => 'usercount',
			8 => 'downloadcount',
			9 => 'score',
			10 => 'app_icon',
			11 => 'is_default'
	);
	
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * 根据appId获取应用详细信息
	 * @param int $appId 应用id
	 */
	function getAppById($appId){
		$clientApps = C("CLIENT_APPS");
		if(preg_match("/^\d*$/", $appId)){
			$map = array('appid'=>$appId);
			$app = $this->where($map)->find();
			if($app){
				$category = D("AppcenterCategory","appcenter")->where(array('cid'=>$app['cid']))->find();
				$app['cname'] = $category ? $category['cname'] : '';
				
				if(in_array($app['app_en_name'],array_keys($clientApps))){
					$app['download_url'] = $clientApps[$app['app_en_name']]['download_url'];
				}
			}
			return $app ? $app : false;
		}else{
			return false;
		}
	}
	
	/**
	 * 获取推荐应用
	 * @param array $apps 配置中的推荐应用
	 * @return array 推荐应用数组
	 */
	function getRecommendApps($apps){
		$enNames = array_keys($apps);
		$in = "'" . implode("','",$enNames) . "'"; 
		$map = array(
				'app_en_name'=>array('in',$in)
		);
		$apps = $this->where($map)->select();
		return $apps ? $apps : array();
	}
	
	/**
	 * 获取指定条数热门应用
	 * @param int $limt 指定获取热门应用的条数
	 * @return array 返回热门应用
	 */
	function getHotApps($limt){
		$limt = $limt > 0 ? $limt : 5;
		$map = array();
		$map['status'] = '1';
		$apps = $this->where($map)->order('viewcount desc')->limit($limt)->select();
		return $apps ? $apps : array();
	}
	
	/**
	 * 根据分页数据和应用类型获取应用列表
	 * @param int $page 页码
	 * @param int $prepage 每页展示应用数，默认每页15个应用
	 * @param int $category 应用类别，默认为0，及不分类查询
	 * @return array('list'=>array(),'count'=>'210','currentpage'=>'1')
	 */
	function getAppsByCategory($page, $prepage = 15, $category = 0){
		// 检查参数
		$page = intval($page) ? $page : 1;
		$prepage = intval($prepage) ? $prepage : 15;
		$category = intval($category) ? $category : 0;
		
		// 组织查询条件
		$p = $page . ',' . $prepage;
		$map = array();
		$map['status'] = 1;
		if($category != 0){
			$map['cid'] = $category;
		}
		
		// 获取当前页列表和总数
		$apps = $this->where($map)->page($p)->select();
		$count = $this->where($map)->count();
		
		// 获取可用分类列表
		$categoryList = D("AppcenterCategory","appcenter")->where(array('status'=>1))->select();
		foreach($categoryList as $key=>$val){
			$categorys[$val['cid']] = $val['cname'];
		}
		
		// 设置类别名称
		foreach($apps as $key=>&$app){
			$app['cname'] = $categorys[$app['cid']];
		}
		
		$result['list'] = $apps ? $apps : array();
		$result['count'] = $count;
		$result['currentpage'] = $page;
		return $result;
	}
	
	/**
	 * 设置应用访问量和用户量
	 * @param string $appid 应用id
	 * @param string $type 增加积分的类型，如：viewcount、usercount、downloadcount
	 * @param int $count 增加的数量
	 * @return 受影响的行数
	 */
	function setAppCountInfo($appid, $type, $count){
		if(preg_match("/^\d*$/", $appid)){
			$app = $this->where(array('appid'=>$appid))->find();
		   //$sql = "select * from ts_appcenter_app where appid=".$appid;
			//$result=$this->execute($sql);
			//$Model = M('appcenter_app','ts_');
			//$result=$Model->query("select * from ts_appcenter_app where appid=".$appid);
			if($app){
				$result = $this->where(array('appid'=>$appid))->save(array($type => $count + $app[$type]));
				return $result;
			}else{
				return 1;
			}
		}else{
			return 0;
		}
	}
	function setAppCountInfoView($appid){
		if(preg_match("/^\d*$/", $appid)){
			$app = $this->where(array('appid'=>$appid))->find();
			if($app){
				$result1 = $this->where(array('appid'=>$appid))->save(array('viewcount' => 1 + $app['viewcount']));
				$result2 = $this->where(array('appid'=>$appid))->save(array('usercount' => 1 + $app['usercount']));
				return 1;
			}else{
				return 1;
			}
		}else{
			return 0;
		}
	}
	/**
	 * 设置应用积分
	 * @param 应用id $appid
	 * @param 积分 $score
	  *2014-9-28
	 */
	function updateAppScore($appid,$score){
		$condition=array('appid'=>$appid);
	   return  $this->where($condition)->setField('score',$score);	 
	}
}
?>