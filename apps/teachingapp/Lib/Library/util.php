<?php

/**
 * 获取特定类型的资源
 * 
 * @param string $login 用户login
 * @param string $restype 资源类型编码
 * @param int $pageindex 当前页码
 * @param int $pagesize 每页大小
 * 
 * @return object $result 返回资源列表详情
 */
function getResListByType($login,$restype,$pageindex=1,$pagesize=16)
{
	$operationtype = "upload";
	$conditions  = array();
	$conditions['restype'] =$restype;
	$conditions['optype'] = ResoperationType::UPLOAD;
	
	$result=array();
	$result['operationtype'] = $operationtype;
	$result['currentpage'] = $pageindex;
	$data = null;
	$totalcount = 0;
	$totalcount = D('ResourceOperation','reslib')->getCount($login, $conditions);
	$result['totalrecords'] = $totalcount;
	$result['totalpage']=(int)($totalcount/$pagesize);
	if($totalcount % $pagesize != 0){
		$result['totalpage']+=1;
	}
	$data = D('ResourceOperation','reslib')->listResouce($login, $conditions, $pageindex, $pagesize);
	$datavalue =null;
	if($data)
	{
		$datavalue =array();

		$ids= array();
		foreach ($data as $vv){
			$ids[] = $vv["rid"];
		}

		$idStr=implode(',', $ids);
		
		//获取每个资源的播放量和缩略图
		$_resclient = D('CyCore')->Resource;
		$objs = $_resclient->Res_GetResources(array("general.id"=>$idStr,"with_url"=>true,"thumbsize"=>"164_123"),
														array("general","statistics","thumbnail_url"));
		unset($_resclient);

		$tempArr=array();
		if($objs->total>0){
			$datas = $objs->data;
			foreach ($datas as $value) {
				$tempArr[$value->general->id]= array('viewcount' =>$value->statistics->viewcount,
													"thumbnail_url"=>$value->thumbnail_url);
			}
		}

		foreach ($data as $vv){
			$res2 = array();
			foreach ($vv as $key => $value){
				if($key == 'uploaddateline'){
					$res2[$key] = date('Y-m-d', $value);
				}
				else if($key == "rid"){
					$res2["viewcount"] = isset($tempArr[$value]["viewcount"]) ? $tempArr[$value]["viewcount"] : 0;
					$res2["thumbnail_url"] = $tempArr[$value]["thumbnail_url"];
					$res2[$key]=$value;
				}
				else{
					$res2[$key]=$value;
				}
			}
			$datavalue[]= $res2;
		}
	}


	$result['data']=$datavalue;
	return $result;
}

/**
 * 获取资源的缩略图
 * 
 * @param string $$thumbnailurl 
 * @param string $size 缩略图大小
 * 
 * @return string $thumbnailurl 返回缩略图的路径
 * 												  若网关不存在，返回默认缩略图
 */
function getResThumbnail($thumbnailurl,$size="164_123"){
	/*
	 * zhaoliang 2013/10/22
	 * 缩略图暂未生成显示默认缩略图
	 */
	if(empty($thumbnailurl)){
		if($size=="120_90"){
			$thumbnailurl = '__APP__/images/default_h.jpg';
		}
		if($size=="120_160"){
			$thumbnailurl = '__APP__/images/default_s.jpg';
		}
		if($size=="164_123"){
			$thumbnailurl = '__APP__/images/default_h2.jpg';
		}
		if($size=="272_207"){
			$thumbnailurl = '__APP__/images/default_h3.jpg';
		}
	}
	echo $thumbnailurl;
}

/**
 * 获取分页控件
 * 
 * @param int $rescount 资源数量
 * @param int $pagesize 每页显示数量
 * 
 * @return string $page 返回分页控件的html
 */
function getPager($rescount,$pagesize=16){
	$p = new Page ( $rescount, $pagesize );
	$p->setConfig('prev',"上一页");
	$p->setConfig('next','下一页');
	$page = $p->show ();
	return $page;
}

/**
 * 模板展示
 * 
 * @param string $restype 资源类型编码
 * @param string $appname 资源类型名称
 * @param object $obj 调用时一定要使用$this
 */
function displayIndex($restype,$appname,$obj,$uid,$pagesize = 16){
	$current_page = isset($_GET['p'])?intval($_GET['p']):1;
	if(empty($uid)){
		  $uname=$GLOBALS['ts']['user']['login'];
	}else{
		$userInfo = model('User')->getUserInfo($uid);
		$uname =$userInfo['login'];
	}
	$result = getResListByType($uname,$restype,$current_page,$pagesize);
	$page = getPager($result['totalrecords'],$pagesize);
	//资源总数
    $obj->assign("totalrecords",$result['totalrecords']);
	$obj->assign("page",$page);
	$obj->assign("data",$result["data"]);
	$obj->assign("restype",$restype);
	$obj->assign("appname",$appname);
}
