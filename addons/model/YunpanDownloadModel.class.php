<?php

/*
 * 云盘下载记录模型
 */

class YunpanDownloadModel extends Model {
	protected $tableName = 'yunpan_download';
	protected $error = '';
	protected $fields = array(
			0 =>'id',
			1=>'fid',
			2=>'dateline',
			3=>'login_name',
			4=>'type',
			5=>'download_source'
	);

	/**
	 * 获取最新下载记录列表
	 * @param string $login 用户登录名
	 * @param array $downloadSource 下载来源:01 资源网关02 其他
	 * @param int $pageindex 页码
	 * @param int $pageSize 每页记录数
	 * @param string $sort 时间排序方式 DESC/ASC
	 * @param array $fields 返回字段 默认返回部分字段
	 * @return array 返回结果
	 *
	 */
	public function getNewListDownload( $downloadSource= '01', $pageindex = 1, $pageSize = 10, $sort = "DESC"){
		$order = "";
		if(strtoupper($sort) == "DESC"){
			$order = "dateline DESC";
		}else{
			$order = "dateline ASC";
		}
		$where['download_source']=array('in',$downloadSource);
		$pageindex = intval($pageindex);
		if(!$pageSize || $pageindex <= 0){
			$pageindex = 1;
		}
		if(!$pageSize || $pageSize <=0 || $pageSize>=100){
			$pageSize = 10;
		}
		$start = ($pageindex - 1) * $pageSize;
		$list =  $this->where($where)
				->order($order)
				->limit("$start,$pageSize")
				->findAll();
		$result = array();
		foreach ($list as $d_res){
			$condition = array('include_deleted' => true);
			$file = D("CyCore")->Resource->getResource($d_res["fid"], $condition);//"8006628c91814fef85227601902ce8e6"
			$d_res["name"] = "";
			$d_res["creator"] = "";
			$d_res["score"] = "";
			$d_res["extension"] = "";
			if($file->statuscode == 200){
				$d_res["name"] = $file->data[0]->general->title;
				$d_res["creator"] = $file->data[0]->general->creator;
				$d_res["score"] = $file->data[0]->statistics->score;
				$d_res["extension"] = $file->data[0]->general->extension;
				$d_res["previewurl"]=C("RS_SITE_URL").'/index.php?app=changyan&mod=Rescenter&act=detail&id='.$d_res["fid"];
			}
			array_push($result, $d_res);

		}

		return $result;
	}
}

?>
