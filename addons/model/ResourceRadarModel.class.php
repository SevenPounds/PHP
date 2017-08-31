<?php
/**
 * 资源服务
 * @author zhehuang
 * @since 2014-12-22
 */
use Home\Library\ResourceRadarService;

use Home\Library\ResourceRankService;

class ResourceRadarModel extends CyCoreModel {
	protected  $client;
	/**
	 * g构造函数 
	 */
	public function __construct() {
		try{
			$this->client =D('CyCore')->ResourceRadar;
		}catch(Exception $e){
			throw_exception("网络连接异常，请稍后重试");
		}
	}
	/**
	 * 更新后的资源统计接口
	 * @return number
	 */
	public function getResourceAll(){
		$resService = new \ResourceClient();
		$condition = array("productid" => C('CLIENT_APP_NAME'));
		$obj =  $resService->Res_GetResCounts($condition,"");
		$total = $obj->statuscode == 200 ? $obj->data : 0;
		return $total ;
	}
	/**
	 * 获取学校管理者的统计数据
	 * @param unknown $areaId
	 */
	public function getSchoolCountById($schoolId){
		$client = new \Home\Library\ResourceRankService();
		$dataTotal = json_decode($client->getResourceById($schoolId,4));
		Log::write("school_master res :" . json_encode($dataTotal),Log::INFO);
// 		$olpModel = new OlapClient();
// 		$dataTotal = json_decode($olpModel->getSchoolCountById($schoolId));
		//设置缓存
		$data['resTotal'] = $dataTotal->uploadNum;  //本月资源上传
		$data['bookPre'] = $dataTotal->downNum;  //本月资源下载
		$data['subTitle'] = '('.date("Ymd", strtotime(' -'. 30 . 'day')).'-'.date('Ymd',time()).')' ;
		$data['title'] = '本校本月资源建设使用情况';
		$data['type'] = UserRoleTypeModel::TEACHER;
		return $data ;
	}
	/**
	 * 获取资源数量总数、平均每课资源数、覆盖教材数
	 *@return{ resourceAllNum 资源建设总量， bookAllNum  覆盖教材数量，avgSubjectNum  平均每个资源数， activeNum， bookNum }
	 *@origin  TrackClient\ResourceRadarService.class.php
	 */
	public function getResourceAllNum(){
		$client = new ResourceRadarService();
		$result = $client->getResourceAllNum();
		Log::write("resource all num result :" . json_encode($result),Log::INFO);
		return $result;
		//return $this->client->getResourceAllNum();
	}
	/**
	 * 获取资源科目总数、已建科目数、资源覆盖率
	 *@return { [资源科目总数,已建科目数,资源覆盖率] Array }
	 *@origin  TrackClient\ResourceRadarService.class.php
	 */
	public function getResourceInfo(){
		return $this->client->getResourceInfo();
	}
	/**
	 * 获取资源数量统计.资源建设情况趋势图
	 *@return { curData  累计建设总量Array， numData  覆盖教材数量Array }
	 *@origin  TrackClient\ResourceRadarService.class.php
	 */
	public function getResourceNum($county,$type,$start,$end){
		$client = new ResourceRadarService();
		$result = $client->getResourceNum($county,$type,$start,$end);
		Log::write("getResourceNum result :" . json_encode($result),Log::INFO);
		return $result;
		//return $this->client->getResourceNum($start,$end);
	}
	/**
	 * 区域内资源数量趋势图
	 *@param  $type{1:省，2:市，3: 区县}
	 *@return { numData  UGC上传总量Array，  curData 人均上传量Array， auditData 未审核数量 }
	 *@origin  TrackClient\ResourceRadarService.class.php
	 */
	public function getHomeUGCTrend($startDate,$endDate,$county,$type){
		$client = new ResourceRadarService();
		$result = $client->getHomeUGCTrend($startDate,$endDate,$county,$type);
		Log::write("Home UGC Trend result :" . json_encode($result),Log::INFO);
		return $result;
		//return $this->client->getHomeUGCTrend($startDate,$endDate,$county,$type);
	}
    
    /**
     * 通过区域code查询上传总量和人均上传量
     * @param  [type] $code  [区域code]
     * @param  [type] $level [区域级别 1：省，2：市，3：区县，4：学校]
     * @return [type]        [description]
     */
	public function getContributionById($code, $level){
		$client = new ResourceRadarService();
		$result = $client->getContributionById($code,$level);
		return $result;
	}
}