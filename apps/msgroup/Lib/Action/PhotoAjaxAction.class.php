<?php
/**
 * 荣誉图片ajax 请求处理
 * @author cheng
 *
 */
class PhotoAjaxAction extends Action{
	
	/**
	 * 删除单个图片
	 */
	public function delPhoto(){
		$gid = $_REQUEST['gid'];
		$photoId = $_REQUEST['photoId'];
		if(empty($gid) || empty($photoId)){
			$this->ajaxReturn('','参数异常',0);
		}
		$map['gid'] = $gid;
		$map['id'] = $photoId;
		$data = $map;
		$data['is_deleted'] = 1;
		$res = D('MSGroupPhoto')->where($map)->save($data);
		if($res){
			$info= '删除成功';
			$status= 1;
		}else{
			$info= '删除失败';
			$status= 0;
		}
		unset($map);
		unset($data);
		$this->ajaxReturn('',$info,$status);
	}
	
	/**
	 * 获取图片列表 
	 */
	public function getPhotos(){
		$gid = $_REQUEST['gid'];
		$pagenum= isset($_REQUEST['p'])?$_REQUEST['p']:1;
		$pagesize = 12;
		if(empty($gid)){
			$this->ajaxReturn('','参数异常',0);
		}
		$count = D('MSGroupPhoto')->where("`gid` = '{$gid}' and `is_deleted`= '0' ")->count();
		$start =  ($pagenum-1)*$pagesize;
		$limit = "{$start},{$pagesize}";
		
		$res = D('MSGroupPhoto')->getMSGPhoto($gid,$limit);
	
		$data ['total_rows'] = $count;
		$data ['list_rows'] = $pagesize;
		$data ['ajax_func_name'] = "photo.request";
		$data ['method'] = "ajax";
		$data ['now_page'] = $pagenum;
		$data ['parameter'] = $gid;
		
		$ajaxpage = new AjaxPage($data);
		$page = $ajaxpage->show();
		
		$photos['data'] = $res;
		$photos['html'] = $page;
		
		$photos['page'] = $pagenum;
		
		$data['photos'] = $photos;
		$data['gid'] = $gid;
		$content = fetch('_photolist', $data);

		unset($data);
		$this->ajaxReturn($content,'信息获取成功',1);
	}
	
	
	
}
