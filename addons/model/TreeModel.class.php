<?php 

class TreeModel{
	
	public $treeClient ;
	
	public  function __construct(){
		$this->treeClient=new  TreeClient();
	}
	

	/**
	 * 获取课本树上节点数组
	 * @author yangli4
	 *
	 * @param array $conditionArr 	查询节点的条件，如：array('phase'=>'01','subject'=>'03')
	 * @param int $nodeName 		节点名称，可选节点名称有:
	 * 		phase,学段
	 *		subject,学科
	 *		edition,版本
	 *		stage,年级
	 *
	 */
	public function getTreenodes($conditionArr, $nodeName){
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
			$obj = $this->treeClient->Tree_getTreeNodes('booklibrary2', $nname, $condition);
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
	 * 获取书本下的节点信息，
	 * @param bookid
	 * @return NULL
	  *2014-8-18
	 */
	public function getBookIndex($bookid){
		if(empty($bookid)){
			return null;
		}
		$bookinfo=S("'s_book_'.$bookid");
		if(empty($bookinfo)){
			$obj=$this->treeClient->Tree_getBookindex($bookid);
			$bookinfo = $obj->statuscode == 200 ? $obj->data : array();
			S('s_book_'.$bookid,$bookinfo,3600);
		}	
		return $bookinfo;
	}
	
}