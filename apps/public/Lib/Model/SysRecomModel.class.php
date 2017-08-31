<?php
/**
 * 系统推荐业务层
 * @author zhehuang
 * @since 2014-12-22
 */
class SysRecomModel extends BaseModel{
	/**
	 * 获取学校或者区域的监管统计资源数据 
	 */
	public function getResourceStatistical($id,$type){
		$userModel = D('CyUser');
		$userData = $GLOBALS['ts']['cyuserdata'];
		$aresInfo = D('CyArea','Model')->getArea($id);
		$resLogic = D("ResourceRadar","Model");
		$areaName = $aresInfo['name'];
		if($type == ConstantsModel::QY_TYPE_CITY) {  //机构管理员
			
			//判断教研员是否为市级			
			Log::write('params:['.json_encode(array(date("Y-m-d", strtotime(' -'. 30 . 'day'))),date('Y-m-d',time()),$id,ConstantsModel::QY_TYPE_CITY),Log::INFO);
			$rr = $resLogic->getResourceNum($aresInfo['id'],2,date("Y-m-d", strtotime(' -'. 30 . 'day')),date('Y-m-d',time()));
			$datas = $rr->curData;
			
			Log::write('刚出来的数据：'.json_encode($datas),Log::INFO);
			$series = array();
			$local = 1 ;
			for ($i=0;$i<count($datas);$i++){
				array_push($series, array($i,$datas[$i][1]));
				$local ++ ;
			}
			Log::write('处理一下刚出来的数据：'.json_encode($series),Log::INFO);
			$data['series'] = $series;
			$data['title'] = '本市资源数量增长趋势图';
			$data['subTitle'] = '('.date("Ymd", strtotime(' -'. 30 . 'day')).'-'.date('Ymd',time()).')' ;
			$data['type'] = ConstantsModel::CITY_TYPE;
			return $data ;
		}else if($type == ConstantsModel::QY_TYPE_DISTRICT){
			
			$resTotal = $resLogic->getHomeUGCTrend(date("Y-m-d", strtotime(' -'. 30 . 'day')),date('Y-m-d',time()),$areaName,ConstantsModel::QY_TYPE_DISTRICT);
			
			$datas = $resTotal->numData;
			$series = array();
			$local = 1 ;
			for ($i=0;$i<count($datas);$i++){
				array_push($series, array($i,$datas[$i][1]));
				$local ++ ;
			}
			$data['series'] = $series;
			$data['title'] = '本区域资源建设情况';
			$data['subTitle'] = '('.date("Ymd", strtotime(' -'. 30 . 'day')).'-'.date('Ymd',time()).')' ;
			$data['type'] = ConstantsModel::DISTRICT_TYPE;
			return $data ;
		}
	}
	/**
	 * 获取学校或者区域的监管统计空间数据 
	 * @param unknown $id
	 * @param unknown $type
	 * @return string
	 */
	public function getSpaceStatistical($id,$type){
		/* if($type == ConstantsModel::SCHOOL_TYPE){ //学校管理员
			$data['downNum'] = '';
			$data['upNum'] = '';
			$data['type'] = $type;
			return $data ;
		}else  */
		if($type == ConstantsModel::QY_TYPE_CITY) {  //机构管理员
			$userModel = D('CyUser');
			$userData = $GLOBALS['ts']['cyuserdata'];
			//判断教研员是否为市级
		/* 	$levelState = $userModel->isInstructorLevel($userData['user']['cyuid'],ConstantsModel::CITY_TYPE);
			if(!$levelState){ //判断教研员是否为区县级别
				$levelState = $userModel->isInstructorLevel($userData['user']['cyuid'],ConstantsModel::DISTRICT_TYPE);
				if($levelState){ //区县级教研员数据组装
					//区县级暂时对空间统计不做处理
				}
			}else {  *///市级教研员数据组装
				$spaceLogic = D("Space","Model");
				$data = $spaceLogic->getSpaceGirdTable($id,$type);
				//数据封装处理
				$cates = '' ;
				$ser = '' ;
				$total = count($data->cat);
				for($i = 0; $i < $total; $i++){
					$cates = empty($cates) ? $data->cat[$i] : $cates .','. $data->cat[$i];
					$ser = strlen($ser) == 0 ? $data->online[$i] : $ser .','. $data->online[$i];
				};
				$new_list = array('cates'=>$cates,'ser'=>$ser,'title'=>'本市各区县教师空间活跃度比较图','subTitle'=>'('.date("Ymd", strtotime(' -'. 30 . 'day')).'-'.date('Ymd',time()).')') ;
				return $new_list ;
		//	}
		}
	}
	/**
	 * 获取学校或者区域的的监管统计教学教研数据
	 */
	public function getResearchTeachingStatistical($id,$type,$flage){
		$time=getdate();
		$condition['method'] = 'getResearchTeachingStatistical' ;
		$condition['service'] = 'stats.serevice' ;
		$condition['id'] = $id ;
		$condition['type'] = $type ;
		$condition['year'] = $time[year] ;
		$condition['month'] = $time[mon] ;
		$condition['day'] = $time[mday] ;
		$serverUrl = C('JY_SITE_URL').'/rest/publicWs?';
		$obj = $this->sendGetRequest($serverUrl, $condition);
		if(empty($obj) || $obj->msg != 'success'){ //没有结果或者接口 异常
			return null ;
		}else { //需要的数据
			$areaIds = $obj->data ; //rpc接口返回到学校主键与教研活动数据  : json格式
			$catesData = '';
			$selDta = '';
			foreach ($areaIds as $val=>$key){
				if(!empty($val)){
					$catesData = empty($catesData) ? $catesData = $val : $catesData = $catesData.','.$val ;
					$selDta = empty($selDta) ? $selDta = $key : $selDta = $selDta.','.$key ;
				}
			}
			$datas['sel'] =  $selDta;
			$datas['subTitle'] = '('.date("Ymd", strtotime(' -'. 30 . 'day')).'-'.date('Ymd',time()).')' ;
			$datas['cate'] =  $catesData ; 
			$datas['xTitle'] = '区县' ;
			
			//1，市级
			if($flage == ConstantsModel::CITY_TYPE){
				$datas['title'] = '本市各区县教学教研活跃度比较图' ;
			}
			//2，区县级
			if($flage == ConstantsModel::DISTRICT_TYPE){
				$datas['title'] = '本区域教学教研活跃度比较图' ;
			}
			return $datas ;
		}		
	}
}