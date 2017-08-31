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
	 * 云盘下载记录保存、更新
	 * @return 1:成功,0:失败,-1:参数不正确
	 * @param array $res_download
	 * ("fid"=>资源id，"dateline"=>当前时间格式'Y-m-d H:i:s'，"login_name"=>登录用户名,
	 * "type"=>资源类型,"download_source"=>下载来源:01 资源网关02 其他)
	 */
	public function saveOrUpdate($res_download) {
		if (!is_array($res_download) || empty($res_download)) {
			return -1;
		}

        $map = array(
            'fid' => $res_download['fid'],
            'login_name' => $res_download['login_name']
        );

		$result = $this->where($map)->find();
		if (!$result) {
            // 第一次下载资源，添加下载记录
			$result = $this->add($res_download);

            // 第一次下载资源，为资源上传者添加积分
            $res_info = D('CyCore')->Resource->Res_GetResIndex($res_download['fid']);

            // 下载者不为上传者时添加积分
            if ($res_info->statuscode == 200 && !empty($res_info->data[0]->general->creator)
                && $res_info->data[0]->general->creator != $res_download['login_name']) {
                // 获取上传用户数据
                $upload_user_info = M('User')->getUserInfoByLogin($res_info->data[0]->general->creator);
                if ($upload_user_info) {
                    // 增加下载资源积分
                    $credit_result = M('Credit')->setUserCredit($upload_user_info['uid'], 'download_resource');
                    if ($credit_result !== false) {
                        // 增加积分成功，添加积分日志
                        $data = array(
                            'content' => '资源被下载:' . $res_info->data[0]->general->title,
                            'url' => '',
                            'rule' => $credit_result
                        );

                        M('CreditRecord')->addCreditRecord($upload_user_info['uid'],
                            $upload_user_info['login'], 'download_resource', $data);
                    }
                }
            }

			return empty($result) ? 0 : 1;
		} else {
            // 更新下载时间
		    $this->where(array('id' => $result['id']))->save(array('dateline' => $res_download['dateline']));
			return 1;
		}
	}
	
	/**
	 * 获取用户下载记录列表
	 * @param string $login 用户登录名
	 * @param array $downloadSource 下载来源:01 资源网关02 其他
	 * @param int $pageindex 页码
	 * @param int $pageSize 每页记录数
	 * @param string $sort 时间排序方式 DESC/ASC
	 * @param array $fields 返回字段 默认返回部分字段
	 * @return array 返回结果
	 *
	 */
	public function listDownload($login, $downloadSource, $pageindex = 1, $pageSize = 10, $sort = "DESC"){
		if(!$login){
			return array("false");
		}
		$order = "";
		if(strtoupper($sort) == "DESC"){
			$order = "dateline DESC";
		}else{
			$order = "dateline ASC";
		}
		$where = array("login_name"=>$login);
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
	
	/**
	 * 获取用户下载记录列表总数
	 * @param string $login 登录用户名
	 * @param array $downloadSource 下载来源:01 资源网关02 其他
	 * @return number
	 */
	public function getlistDownloadCount($login, $downloadSource){
		if(!$login){
			return -1;
		}
		$where = array("login_name"=>$login,"download_source"=>array('in',$downloadSource));
		return $this->where($where)->count();
	}
}

?>
