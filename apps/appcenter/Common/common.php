<?php
/**
 * @param $score 分数
 * @param bool $flag $score是不是要除以20
 * @return string
 */
function getStar($score,$flag = true){
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
		$html .= '<span class="star_all"></span>';
	}
	if($end_flag){
		$k++;
		$html .= '<span class="star_half"></span>';
	}
	for(;$k<5;$k++){
		$html .= '<span class="star_no"></span>';
	}
	return $html;
}

/**
 * 获取页面分页（学科资源首页特殊处理）
 * @param int $page 当前页码
 * @param int $count 资源总数
 * @param int $prepage 每页资源数目
 * @param string $fun 要调用的js异步方法名
 */
function getAppcenterPaging($page,$count,$prepage,$fun){
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
		$paging = $paging."<a href='javascript:void(0);' class='first pre' onclick='".$fun."(this);' data-page='".($page - 1)."'><</a>";
	}
	if(($page-3) > 1){
		$paging = $paging."<a href='javascript:void(0);' onclick='".$fun."(this);' data-page='1'>1..</a>";
	}else if(($page-3) == 1 && $totalpage == 4){
		$paging = $paging."<a href='javascript:void(0);' onclick='".$fun."(this);' data-page='1'>1</a>";
	}
	for($j = ($page - 2); $j < $page; $j++){
		if(($j) > 0){
			$paging = $paging."<a href='javascript:void(0);' onclick='".$fun."(this);' data-page='$j'>$j</a>";
		}
	}
	$paging = $paging."<a href='javascript:void(0);' class='current'>$page</a>";
	for($i = ($page + 1); $i < ($page + 3); $i++){
		if(($i) <= $totalpage){
			$paging = $paging."<a href='javascript:void(0);' onclick='".$fun."(this);' data-page='$i'>$i</a>";
		}
	}
	if(($page + 3) < $totalpage){
		$paging = $paging."<a href='javascript:void(0);' onclick='".$fun."(this);' data-page='$totalpage'>..$totalpage</a>";
	}else if(($page + 3) == $totalpage){
		$paging = $paging."<a href='javascript:void(0);' onclick='".$fun."(this);' data-page='$totalpage'>$totalpage</a>";
	}
	if($page < $totalpage){
		$paging = $paging."<a href='javascript:void(0);' class='last next' onclick='".$fun."(this);' data-page='".($page + 1)."'>></a>";
	}
	return $paging;
}
?>