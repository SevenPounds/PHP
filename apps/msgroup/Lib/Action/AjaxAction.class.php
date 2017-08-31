<?php
/**
 * 名师工作室ajax处理页面
 * @author sjzhao
 */
class AjaxAction extends Action{
	/**
	 * 获取工作室前几条资讯
	 * @author sjzhao
	 * @param int $type 资讯类型 int $gid 工作室id int $num 获取的条数
	 */
	public function getTopNAnnounce(){
		$gid = $_POST['gid'];
		if(empty($gid)){
			echo "{'status':0,'msg':'工作室id不能为空'}";
			return;
		}
		$type = $_POST['type'];
		$num  = $_POST['num']?$_POST['num']:10;
		$ret = D('MSGroupAnnounce')->GetNotices(array('gid'=>$gid,'type'=>$type),$num);
		if($ret){
			echo "{'status':1,'data':$ret}";
		}
	}
	/**
	 * 获取资讯文章列表
	 * @author sjzhao
	 * @param int $gid 工作室id int $type 资讯类型  int $limit 资讯限制条数 string $order 排序规则 int  $page 页码
	 */
	public function getAnnounceList(){
	    $gid = $_POST['gid'];
		if(empty($gid)){
			echo "{'status':0,'msg':'工作室id不能为空'}";
			return;
		}
		$type = $_POST['type'];
		$limit  = $_POST['limit']?$_POST['limit']:10;
		$order = $_POST['order']?$_POST['order']:'ctime desc';
		$page = $_POST['page']?$_POST['page']:1;
		$ret = D('MSGroupAnnounce')->GetNoticesByGID($gid, $type, $limit, $order, $page);
		$result=json_encode($ret);
		if($ret){
			echo "{'status':1,'data':$result}";
		}
	}
	/**
	 * 编辑资讯文章
	 * @author sjzhao
	 * @param int $id 文章资讯的id string $title 文章资讯的标题 string  $content 文章资讯的内容
	 */
	public function announceEdit(){
		$id = $_POST['id'];
		if(empty($id)){
			echo "{'status':0,'msg':'资讯文章id不能为空'}";
			return;
		}
		$title = $_POST['title'];
		if(empty($title)){
			echo "{'status':0,'msg':'文章资讯标题不能为空'}";
		    return;
		}
		$content = $_POST['content'];
		if(empty($content)){
			echo "{'status':0,'msg':'内容不能为空'}";
			return;
		}
		$attachments = array();//附件数组，附件id作为数组index,附件类型作为数组键值
		$ret = D('MSGroupAnnounce')->Edit($id, $title, $content, $attachments);
		$result = $ret['data'];
		if($ret){
			echo "{'status':0,'data':$result}";
		}
		
	}
    /**
     * 根据id增加文章资讯的浏览量
     * @author sjzhao
     * @param int $id 文章资讯的id
     */
	public function addViewCountById(){
		$id = $_POST['id'];
		if(empty($id)){
			echo "{'status':0,'msg':'文章资讯的id不能为空'}";
			return;
		}
		$ret = D('MSGroupAnnounce')->AddViewCount($id);
		$result = $ret['data'];
		if($ret){
			echo "{'status':0,'data':$result}";
		}
	}
	/**
	 * 发布文章资讯
	 * @author sjzhao
	 * @param int $uid 用户id int $gid 工作室id string $uname 用户名string  $title 资讯标题 string $content资讯内容
	 */
	public function announcePublish(){
		$uid = $_POST['uid'];
		if(empty($uid)){
			echo "{'status':0,'msg':'uid不能为空'}";
			return;
		}
		$gid = $_POST['gid']; 
		if(empty($gid)){
			echo "{'status':0,'msg':'工作室id不能为空'}";
			return;
		}
		$uname = $_POST['uname'];
		if(empty($uname)){
			echo "{'status':0,'msg':'用户名不能为空'}";
			return;
		}
		$title = $_POST['title'];
		if(empty($title)){
			echo "{'status':0,'msg':'文章资讯标题不能为空'}";
			return;
		}
		$content = $_POST['content'];
		if(empty($content)){
			echo "{'status':0,'msg':'文章资讯内容为空'}";
			return;
		}
		$attachments = array();
		$type =$_POST['type'];
		$ret = D('MSGroupAnnounce')->Publish($uid, $gid, $uname, $title, $content, $attachments, $type);
		$result = $ret['data'];
		if($ret){
			echo "{'status':0,'data':$result,'msg':'发表成功'}";
		}
		
	}
	/**
	 * 根据id删除文章资讯
	 * @author sjzhao
	 * @param int $id 文章资讯id
	 */
	public function deleteAnnounceById(){
		$id = $_POST['id'];
		if(empty($id)){
			echo "{'status':0,'msg':'资讯id不能为空'}";
		}
		$ret = D('MSGroupAnnounce')->Delete($id);
		if($ret){
			echo "{'status':0,'msg':'资讯删除成功'}";
		}
	}
	/**
	 * 根据ids批量删除资讯
	 * @param array $ids 待删除的资讯id
	 * @author sjzhao
	 */
	public function deleteAnnouncesByIds(){
		$ids = $_POST['ids'];
		if(empty($ids)){
			echo "{'status':0,'msg':'资讯id不能为空'}";
		}
		$ret = D('MSGroupAnnounce')->BatchDelete($ids);
		if($ret){
			echo "{'status':0,'msg':'批量删除成功'}";
		}
	}
	/**
	 * 名师工作室基础信息保存
	 */
	public function saveConfig(){
		
		$gid = $_POST['gid'];
		$name = $_POST['name'];
		$subject = $_POST['subject'];
		$description = $_POST['description'];
		
		$_r = D('MSGroup')->update($gid, $name, $description,$subject);
		
		$result = new stdClass();
		if($_r === false){
			$result->status = 0;
			$result->data = "";
		}else{
			$result->status = 1;
			$result->data = "";
		}
		exit(json_encode($result));
	}
	
	/**
	 * 获取通知公告及资讯文章列表
	 */
	public function getAnnouncesList(){
		$gid = $_POST['gid'];
		if(empty($gid)){
			echo "{'status':0,'msg':'工作室id不能为空'}";
			return;
		}
		$type = $_POST['type'];
		$limit  = $_POST['limit'] ? $_POST['limit'] : 10;
		$orderBy = $_POST['orderBy'] ? $_POST['orderBy'] : 'ctime desc';
		$page = $_POST['page'] ? $_POST['page'] : 1;
		
		$announceList = D('MSGroupAnnounce')->GetNoticesByGID($gid, $type, $limit, $orderBy, $page);
		$announceCount = D('MSGroupAnnounce')->GetCountByGID($gid, $type);
		//ajax分页
		$ajaxPage = new AjaxPage(array('total_rows'=>$announceCount,
							'method'=>'ajax',
							'ajax_func_name'=>'msAnnounce.page',
							'now_page'=>$page,
							'list_rows'=>$limit
						));
		$pageInfo = $ajaxPage->show();
		$announceIds = array();
		foreach ($announceList AS $key=>$announce){
			if($announce['id']){
				$announceIds[] = $announce['id'];
			}
			if($announce['type'] == 1){
				$announceList[$key]['editurl'] = U('msgroup/Announce/edit',array('gid'=>$gid,'id'=>$announce['id'],'type'=>$announce['type']));
				$announceList[$key]['detailurl'] = U('msgroup/Announce/detail',array('gid'=>$gid,'id'=>$announce['id'],'type'=>$type));
			}else{
				$announceList[$key]['editurl'] = U('msgroup/Article/edit',array('gid'=>$gid,'id'=>$announce['id'],'type'=>$announce['type']));
				$announceList[$key]['detailurl'] = U('msgroup/Article/detail',array('gid'=>$gid,'id'=>$announce['id'],'type'=>$type));
			}
		}
		$var = array();
		$var['announceList'] = $announceList;
		$var['pageInfo'] = $pageInfo;
		$var['orderBy'] = $orderBy;
        $var['j'] = $limit * ($page - 1);
		$content = fetch("announceList", $var);
		$result = new stdClass();
		$result->status = 1;
		$result->data = $content;
		if($content){
			exit(json_encode($result));
		}
	}
	/**
	 * 删除通知公告或资讯文章
	 */
	public function deleteAnnounce(){
		$announceIds = $_POST['announceIds'];
		$_r = false;
		if(is_array($announceIds)){
			$_r = D('MSGroupAnnounce')->BatchDelete($announceIds);
		}else{
			$_r = D('MSGroupAnnounce')->Delete($announceIds);
		}
		$result = new stdClass();
		if($_r){
			$result->status = 1;
			$result->data = $_r;
		}else{
			$result->status = 0;
			$result->data = $_r;
		}
		exit(json_encode($result));
	}
	/**
	 * 保存通知公告或资讯文章
	 */
	public function doAdd(){
		$gid = $_POST['gid'];
		if(empty($gid)){
			exit("{'status':0,'msg':'工作室id不能为空'}");
		}
		$announceTitle = isset($_POST['announceTitle']) ? $_POST['announceTitle'] : "";
		if($announceTitle==""){
			exit("{'status':0,'msg':'标题不能为空'}");
		}
		$announceContent = isset($_POST['announceContent']) ? $_POST['announceContent'] : "";
		if($announceContent==""){
			exit("{'status':0,'msg':'内容不能为空'}");
		}
		$announceType = isset($_POST['announceType']) ? $_POST['announceType'] : 1;
		$announceAttachments = isset($_POST['attachments']) ? $_POST['attachments'] : "";
		$announceAttachments = array_filter(explode("|", $announceAttachments));
		$attachmentList = array();
		foreach ($announceAttachments AS $attachment){
			$attachmentList[$attachment] = 0;
		}
		$_r = D('MSGroupAnnounce')->Publish($this->uid, $gid, $this->user['uname'], $announceTitle, $announceContent, $attachmentList, $announceType);
		$result = new stdClass();
		$result->status = 0;
		if($_r){
			$result->status = 1;
			$result->msg = "保存成功！";
			$result->data = $_r;
		}
		exit(json_encode($result));
	}
	/**
	 * 保存通知公告或资讯文章
	 */
	public function doEdit(){
		$gid = $_POST['gid'];
		if(empty($gid)){
			exit("{'status':0,'msg':'工作室id不能为空'}");
		}
		$announceId = intval($_POST['announceId']) ? intval($_POST['announceId']) : 0;
		if(empty($announceId)){
			exit("{'status':0,'msg':'公告id不能为空'}");
		}
		$announceTitle = isset($_POST['announceTitle']) ? $_POST['announceTitle'] : "";
		if($announceTitle==""){
			exit("{'status':0,'msg':'标题不能为空'}");
		}
		$announceContent = isset($_POST['announceContent']) ? $_POST['announceContent'] : "";
		if($announceContent==""){
			exit("{'status':0,'msg':'内容不能为空'}");
		}
		$announceType = isset($_POST['announceType']) ? $_POST['announceType'] : 1;
		$announceAttachments = isset($_POST['attachments']) ? $_POST['attachments'] : array();
		$announceAttachments = array_filter(explode("|", $announceAttachments));
		$attachmentList = array();
		foreach ($announceAttachments AS $attachment){
			$attachmentList[$attachment] = 0;
		}
		$_r = D('MSGroupAnnounce')->Edit($announceId, $announceTitle, $announceContent, $attachmentList);
		$result = new stdClass();
		$result->status = 0;
		$result->msg = "修改失败！";
		if($_r){
			$result->status = 1;
			$result->msg = "修改成功！";
			$result->data = $_r;
		}
		exit(json_encode($result));
	}
	/**
	 * 获取相册列表  $type 0:上翻  1:下翻
	 */
	public function getphoto(){
		$type =$_REQUEST['type'];
		$gid =$_REQUEST['gid'];
		$start =$_REQUEST['start'];
		if($type==1){
			$photo_data = D('MSGroupPhoto')->where("`gid` = '{$gid}' and `is_deleted`= '0' ")->order("upload_time DESC")->limit("$start,4")->select();
			$photo_data[3]['savepath']=getImageUrl($photo_data[3]['savepath'],100,100);
			$photo_data[3]['photo_name']=mStr($photo_data[3]['photo_name'],8);
			$photo_data[3]['url']=U('msgroup/Photo/preview',array('gid'=>$gid,'pid'=>$photo_data[3]['id']));
			exit(json_encode($photo_data[3]));
		}
		else if($type==0){
			$photo_data = D('MSGroupPhoto')->where("`gid` = '{$gid}' and `is_deleted`= '0' ")->order("upload_time DESC")->limit("$start,4")->select();
			$photo_data[0]['savepath']=getImageUrl($photo_data[0]['savepath'],100,100);
			$photo_data[0]['photo_name']=mStr($photo_data[0]['photo_name'],8);
			$photo_data[0]['url']=U('msgroup/Photo/preview',array('gid'=>$gid,'pid'=>$photo_data[0]['id']));
			exit(json_encode($photo_data[0]));
		}
	}


}
?>
