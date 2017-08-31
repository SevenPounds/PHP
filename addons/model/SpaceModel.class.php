<?php
/**
 * 空间服务
 * @author zhehuang
 * @since 2014-12-22
 */
use Home\Library\SpaceService;

class SpaceModel extends CyCoreModel{
	protected  $client;
	/* (non-PHPdoc)
	 * @see CyCoreModel::__construct()
	 */
	public function __construct() {
		try{
			$this->client =D('CyCore')->Space;
		}catch(Exception $e){
			throw_exception("网络连接异常，请稍后重试");
		}
	}
	//----顶级(市级)---  (来源：监管)
	/**
	 * 获取次一级地区(区县)空间活跃、开通趋势图
	 *@return { Array: pageInfo, Array: spaceOpenData{int: instructorActive, int: instructorOpen, int: teacherActive, int: teacherOpen} }
	 *@origin  TrackClient\SpaceService.class.php
	 */
	public function getSpaceGirdTable($code,$type){
		$client = new SpaceService();
		$list = $client->getTeacherSpace($code,$type);
		$list = json_decode($list);
		return $list;
		//$list = $this->client->getTeacherSpace($code,$type);
		//return $this->sortSpaceList($list);
	}
	/**
	 * 给查询的结果进行排序处理
	 * @param unknown $list
	 * @param string $type
	 * @return multitype:|unknown
	 */
	private function sortSpaceList($list,$type = 'desc'){
		$data = $list->spaceOpenData;
		if(empty($data)){
			return array();
		}
		if($type == ConstantsModel::DESC){  //倒叙
			for ($item = 0;$item < count($data) - 1;$item ++){
				for ($key = 0;$key <count($data) - $item - 1;$key ++){
					if($data[$key]->teacherOpen < $data[$key + 1]->teacherOpen){
						$tmp = $data[$key];
						$data[$key] = $data[$key + 1];
						$data[$key + 1] = $tmp;
					}
				}
			}
		}else if($type == ConstantsModel::ASC){ //正序
			for ($item = 0;$item < count($data) - 1;$item ++){
				for ($key = 0;$key <count($data) - $item - 1;$key ++){
					if($data[$key]->teacherOpen > $data[$key + 1]->teacherOpen){
						$tmp = $data[$key];
						$data[$key] = $data[$key + 1];
						$data[$key + 1] = $tmp;
					}
				}
			}
		}else { //默认的排序
			
		}
		return $data ;
	}
}