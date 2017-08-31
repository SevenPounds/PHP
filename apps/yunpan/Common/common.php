<?php
	/**
	 * 获得学年信息
	 * @author yangli4
	 *
 	 * @param int $year 		$year为基准年，如$year为空，则以当前年为准
 	 * @param int $offset 		基准年$year，前后偏移年数
 	 * @param string $connector 年份连接符
 	 *
 	 * @return array 			返回$year前后$offset年的学年数组
	 */
	function getSchoolYears($year, $offset = 3, $connector='-'){
		if(empty($year) ){
			$year = date('Y');
		}
		//合法性检查
		if(!is_numeric($year) || !is_numeric($offset)){
			return null;
		}
		if($year < 0 || $offset <= 0){
			return null;
		}

		$schoolYears = array();
		$cy = $year;
		$os = -$offset;
		while($os < $offset){
			$cy = $year + $os;
			$schoolYears[] = $cy.$connector.($cy + 1);
			$os++;
		}

		return $schoolYears;
	}


	/**
	 * 获得学期信息
	 * @author yangli4
 	 *
 	 * @return array 			返回学期数组
	 */
	function getTerms(){
		$terms =array();
		$terms[] = '上学期';
		$terms[] = '下学期';
		return $terms;
	}

	/**
	 * 获取课本树上节点数组
	 * @author yangli4
	 * 
 	 * @param array $conditionArr 	查询节点的条件，如：array('grade'=>'01','subject'=>'03')
 	 * @param int $nodeName 		节点名称，可选节点名称有:
	 * 														grade,年级
	 *														subject,科目
	 *														publisher,出版社
	 *														volumn,册别
	 *														
	 */
	function getBookTreenodes($conditionArr, $nodeName){
		$nname = strtolower($nodeName);
		$condition = array();
		$key = "res_service_nodes".$nodeName;
		if(!empty($conditionArr['phase'])){
			$condition = array_merge($condition,array('phase'=>$conditionArr['phase']));
			$key = $key ."_". $conditionArr['phase'];
		}
		if(!empty($conditionArr['subject'])){
			$condition = array_merge($condition,array('subject'=>$conditionArr['subject']));
			$key = $key ."_". $conditionArr['subject'];
		}
		if(!empty($conditionArr['edition'])){
			$condition = array_merge($condition,array('edition'=>$conditionArr['edition']));
			$key = $key ."_". $conditionArr['edition'];
		}
		if(!empty($conditionArr['stage'])){
			$condition = array_merge($condition,array('stage'=>$conditionArr['stage']));
			$key = $key ."_". $conditionArr['stage'];
		}
			//添加缓存
		$categoroys = S($key);	  
		if(empty($categoroys)){
			$_treeclient = D('CyCore')->Tree;
			$obj = $_treeclient->Tree_getTreeNodes('booklibrary2', $nname, $condition);
			if($obj->statuscode == 200){
				$categoroys = $obj->data;
			} else{
				$categoroys = array();
			}
			S($key, $categoroys, 36000);
		}
		return $categoroys;
	}
	
	/**
	 * 敏感词检测公共方法
	 * @param string $content 要检测的字符串
	 * @return json 如：{"status": "0", "data": []}
	 */
	function wordFilter($content){
		$url = C('TEXT_FILTER_SERVER').'?content='.rawurlencode($content);
		$html = file_get_contents($url);
		return $html;
	}
	
	/**
	 * 获取登记星星的信息，将百分之转换成5分值
	 * @param int $score
	 * @return string
	 */
	function get_resStart($score){
		$star=array();
		$average = $score/20;
		$average = number_format($average,1);
		$average = strval($average);
		$lastChar = substr($average,-1);	
    	$end = intval($average);
    	$star['fiveScore']=$average;
        $star['allStar']=$end;
		$star['endStar']=$lastChar;
		return $star;
	}
	
	/**
	 * 特殊字符串过滤
	 * 文件夹禁止字符串\/:*?"<>|
	 * 特殊字符串会被替换成一个空字符
	 * @param string $fileName
	 * @return 过滤后的合法字符串
	 */
	function filterFileName($fileName){
		$notAllowedChars = '\\\/:*?"<>|'; 
		$pattern = '{['.$notAllowedChars.']}';
		$fileName = preg_replace($pattern, ' ', $fileName);
		return $fileName;
	}
	
?>