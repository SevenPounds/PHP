<?php
/**
 * 根据用户获取角色信息
 * @param integer $uid
 *  @param bool $bReturn  返回多角色
 */
function getAuthority($mid,$roles){
	$map['uid'] = $mid;
		$map['roleid'] = array('in',implode(',',$roles));		
		$result = D('UserRole')->where($map)->find();
		if(!empty($result)){
			return true;
		}
		return false;
}

function getPreviewUrl($resid,$type='preview'){
	$resClient = D('CyCore')->Resource;
	$preview_obj = $resClient->Res_GetResIndex($resid,true);
	$resourceInfo = $preview_obj->data[0];
	$resdetail = $resourceInfo->general;
	$audio = array("mp3","wma","wav","ogg","ape","mid","midi");
	$extension =$resdetail->extension;
	$preview_url = in_array($extension, $audio)?$resourceInfo->file_url:$resourceInfo->preview_url;
	return $preview_url;
}

function getShortImg($extension,$isbig=false){
	$extension = strtolower($extension);
	if($isbig){
		$img = 'img_';
	}else{
		$img = 'icon_';
	}
	switch ($extension){
		case "doc":
		case "docx":
			$img.='doc';
			break;
		case "txt":
			$img.='txt';
			break;
		case "xls":
		case "xlsx":
			$img.='xls';
			break;
		case "mp3":
		case "wma":
		case "wav":
		case "ogg":
		case "ape":
		case "mid":
		case "midi":
			$img.='video';
			break;
		case "jpg":
		case "jpeg":
		case "bmp":
		case "png":
		case "gif":
			$img.='img';
			break;
		case "swf":
			$img.='swf';
			break;
		case "asf":
		case "avi":
		case "rmvb":
		case "mp4":
		case "mpeg":
		case "wmv":
		case "flv":
		case "3gp":
			$img.='movie';
			break;
		case "zip":
		case "rar":
			$img.='zip';
			break;
		case "card":
			$img.='card';
			break;
		case "ppt":
		case "pptx":
			$img.='ppt';
			break;
		case "pdf":
			$img.='pdf';
			break;
		case "rtf":
			$img.='rtf';
			break;
		default:
			$img.='default';
			break;
	}
	return ($img.'.png');
}

function stdClass_gene($code,$name){
	$stdClass_temp = new \stdClass();
	$stdClass_temp->code = $code;
	$stdClass_temp->name = $name;
	return $stdClass_temp;
}

/**
 * 获取录播课类型列表
 * @return multitype:
 */
function getRecordTypes() {
	$useType = array('1001'=>'通讲课','1002'=>'串讲课','1003'=>'习题课','1004'=>'拓展课','1005'=>'复习课','1006'=>'名师示范课');
	foreach($useType as $key=>$value){
		$list[] = stdClass_gene($key,$value);
	}
	return $list;
}


/**
 * 字符串英文单引号、双引号转换
 * @param string $oldstr
 * @param string or array $search
 * @param string or array $replace
 * @return string|mixed
 */
function escape($oldstr,$search =array('\'','"'),$replace=array('’','”')){
	if(!is_string($oldstr)){
		return '';
	}
	$newstr = str_replace($search, $replace, $oldstr);
	return $newstr;
}

/**
 * 发送手机短信方法
 * @param string $mobile 手机号
 * @param string $code 验证码
 * @return string 返回短信发送结果：0:321654 成功，1:XXX 失败
 */
function sendPhoneMessage($mobile,$code){
	// 配置短信服务相关参数
	$remote_server = C('MOBILE_SERVICE');
	$post_string = "apikey=" . C("MOBILE_API_KEY")
					. "&password=" . C("MOBILE_PASSWORD")
					. "&mobile=" . $mobile
					. "&templateid=" . C("MOBILE_TEMPLATE_ID") 
					. "&templateparams={mobile_key:" . urlencode($code) . "}";
	// POST请求基本设置
	$context = array(
			'http' => array(
					'method' => 'POST',
					'header' => 'Content-type: text/html; charset=utf-8' . "\r\n" . 'Content-length: ' . strlen($post_string) + 1,
					'content' => $post_string
			)
	);
	// 创建POST请求流
	$stream_context = stream_context_create($context);
	// 获取返回参数
	$result = file_get_contents($remote_server,FALSE,$stream_context);
	return $result;
}

/**
 * 读取excel内容
 * @param string $path Excel文件路径
 */
function excelIn($path = '') {
	//设定缓存模式为经gzip压缩后存入cache（PHPExcel导入导出及大量数据导入缓存方式的修改 ）
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
	$cacheSettings = array();
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
	$objPHPExcel = new PHPExcel();
	if (empty($path)) {
		//读入上传文件
		if($_POST){
			$objPHPExcel = PHPExcel_IOFactory::load($_FILES["inputExcel"]["tmp_name"]);
			//内容转换为数组
			$indata = $objPHPExcel->getSheet(0)->toArray();
		}
	} else {
		$objPHPExcel = PHPExcel_IOFactory::load($path);
		//内容转换为数组
		$indata = $objPHPExcel->getSheet(0)->toArray();
	}
	return $indata;
}
/**
 * 获取页面分页（学科资源首页特殊处理）
 * @param int $page 当前页码
 * @param int $count 资源总数
 * @param int $prepage 每页资源数目
 * @param string $fun 要调用的js异步方法名
 */
function getPaging($page,$count,$prepage,$fun){
	if($count%$prepage == 0){
		$totalpage = $count/$prepage;
	}else{
		$totalpage = intval($count/$prepage) + 1;
	}
	if($totalpage < 2){
		return "";
	}
	$paging = '';
	if($page > 1){
		$paging = $paging."<a class='pre' onclick='".$fun."(this);' page='".($page - 1)."'>上一页</a>";
	}
	if(($page-3) > 1){
		$paging = $paging."<a onclick='".$fun."(this);' page='1'>1..</a>";
	}else if(($page-3) == 1 && $totalpage == 4){
		$paging = $paging."<a onclick='".$fun."(this);' page='1'>1</a>";
	}
	for($j = ($page - 2); $j < $page; $j++){
		if(($j) > 0){
			$paging = $paging."<a onclick='".$fun."(this);' page='$j'>$j</a>";
		}
	}
	$paging = $paging."<a class='current' href='javascript:void(0);'>$page</a>";
	for($i = ($page + 1); $i < ($page + 3); $i++){
		if(($i) <= $totalpage){
			$paging = $paging."<a onclick='".$fun."(this);' page='$i'>$i</a>";
		}
	}
	if(($page + 3) < $totalpage){
		$paging = $paging."<a onclick='".$fun."(this);' page='$totalpage'>..$totalpage</a>";
	}else if(($page + 3) == $totalpage){
		$paging = $paging."<a onclick='".$fun."(this);' page='$totalpage'>$totalpage</a>";
	}
	if($page < $totalpage){
		$paging = $paging."<a class='next' onclick='".$fun."(this);' page='".($page + 1)."'>下一页</a>";
	}
	return $paging;
}
/**
 * @param $score 分数
 * @param bool $flag $score是不是要除以20
 * @return string
 */
function getStars($score,$flag = true){
	if($flag){
		$average = $score/20;
	}else{
		$average = $score;
	}
	$average = number_format($average,1);
	$average = strval($average);
	$lastChar = substr($average,-1);
	if($lastChar === '0'){
		$average = substr($average,0,(strlen-2));
		$end_flag = false;
	}else{
		$end_flag = true;
	}
	$end = intval($average);
	$k = 0;
	$html='';
	for(;$k<$end;$k++){
		$html .= '<em class="full_star"></em>';
	}
	if($end_flag){
		$k++;
		$html .= '<em class="half_star"></em>';
	}
	for(;$k<5;$k++){
		$html .= '<em class="no_star"></em>';
	}
	return $html;
}
?>