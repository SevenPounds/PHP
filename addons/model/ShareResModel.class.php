<?php
/**
 * 资源分享模型
 * @author xmsheng
 *
 */
class ShareResModel {
	
	/**
	 * @param array $condition=array(
	 * 						 "cyuid"=>"",
	 * 						  "uid"=>"",
	 *                        "fid"=>"",
	 *                        "filename"=>"",
	 *                        "desc"=>"",
	 *                        "login"=>"");
	  *2014-9-11
	  * $msg['status']=500 分享失败
	  * $msg['status']=200 分享成功
	 */
	public function  shareResToHome($condition){
		$msg=array();
		$pro = array (
				"fid" => $condition['fid'],
				"login_name" => $condition["login"],
				"dateline" => date ('Y-m-d H:i:s'),
				"open_position" => '01',
				"res_title" => $condition["filename"]
		);
		$pub_res=D("YunpanPublish","yunpan")->saveOrUpdate($pro);
		if ($pub_res != 1) {
			$msg['status']="500";
			return $msg;
		} else {
			$properties=array("description"=>$condition["desc"]);
			D("YunpanFile","yunpan")->setFileProperties($condition["cyuid"],$condition["fid"],$properties);
			$resUrl = U("resview/Resource/index",array("id"=>$condition["fid"],"uid"=>$condition["uid"]));
			D('YunpanFile','yunpan')->syncToFeed($condition["uid"],$condition["filename"],$resUrl,2);
		}
		$msg['status']="200";
		return $msg;
	}
}

?>