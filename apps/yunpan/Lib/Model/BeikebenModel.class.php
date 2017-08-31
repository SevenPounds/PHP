<?php
/**
 * 被课本模型
 * yangli4@iflytek.com
 */

class BeikebenModel	extends	Model {
	private $diskClient = null;

	private $bookClient = null;

	//单元文件夹重名后缀
	private $dirSuffix = 'IFLY_YP' ;

	//默认书本封面名称
	private $defaultBookName = 'defaultBook';

	function __construct() {
		parent::__construct();
		$this->diskClient = IflytekdiskClient::getInstance();
		$this->bookClient = new BooksClient();
	}

	/**
	 * 创建备课本
	 * @author yangli4
	 * 
	 * @param array $cyuid 			用户cyuid
	 * @param array $pDirID			我的备课本云盘文件夹id
	 * @param array $bkbInfo 		备课本信息
	 * @return array 				array('status'=>1, 	// 创建成功:1; 创建失败:0;
	 *						 			  'data'=>xx)	// 如成功返回备课本相关信息
	 */
	public function createBeikeben($cyuid,$pDirID,$bkbInfo){
		$result = array();

		//参数信息合法检查
		if (!$this->checkNewBeikeben($cyuid, $pDirID, $bkbInfo)) {
			 $result['statuscode'] = 0;
			 $result['data'] = '参数信息错误';
		} else {
			// 创建云盘文件夹
			$res = $this->addBeikeben($cyuid, $pDirID, $bkbInfo);			
			if ($res['statuscode'] == 1) {
				$dir_id = $res['data']->fid;

				//云盘课本文件夹id
				$result['statuscode'] = 1;
				$result['data'] = $res['data'] ;
			} else {
				//为用户添加备课本失败
				$result['statuscode'] = 0;
				$result['data'] = $res['data'];
				$result['yunpanRes'] = $res['yunpanRes'];
				$result['param'] = $res['param'];
			}
		}
		return $result;
	}

	/**
	 * 删除备课本
	 * @author yangli4
	 * 
	 * @param int $cyuid 			用户cyuid
	 * @param array  $dirIDs     	待删除的目录ID数组
     * @param  bool  $isDirectly 	是否直接删除(默认删除到回收站)
	 * @return int 					删除成功:1，删除失败:0
	 */
	public function deleteBeikeben($cyuid,$dirIDs,$isDirectly){	
		//参数信息合法检查
		if(empty($cyuid) || empty($dirIDs)){
			return 0;
		}

		//云盘删除
        return D('Yunpan')->deleteYunpanDirs($cyuid, $dirIDs, $isDirectly);
	}


	/**
	 * 获取备课本文件夹列表信息
     * @param  string $cyuid      文件夹所属用户cyuid(与用户服务ID一致)
     * @param  string $pDirID     父目录ID, 只获取此目录下的一级子目录列表
     * @param  int $page          第几页
     * @param  int $limit         每页显示记录数
     * @param  string $ordertype  记录排序方式
     * @return {[type]}         [description]
     */
  	public function getBeikebens($cyuid, $pDirID, $page = 0, $limit = 10, $ordertype = 1){
		// 参数信息合法检查  		
		if (!isset($cyuid) || !isset($pDirID)) {
			return array();
		}

		$res = $this->diskClient->getDirs($cyuid, $pDirID, $page, $limit, $ordertype);
		$res = $res->data;

		if (!empty($res)) {
			foreach ($res as $key => $value) {

                // 学年学期默认为空
                $res[$key]->schoolYear = '';
                $res[$key]->term = '';

				// 获取文件夹元数据
                $detail = $this->diskClient->getDirProps($cyuid, $value->fid);
                if (!empty($detail)) {
                    if ($detail->volumn[0] == '01') {
                        // 上册
                        $res[$key]->term = '学年';

                        // 根据创建时间生成学年
                        $createtime = substr($value->createtime,
                            0, (strlen($value->createtime) - 3)); // 处理时间戳毫秒
                        $startYear = date('Y', $createtime);
                        $endYear = $startYear + 1;
                        $res[$key]->schoolYear = "{$startYear}-{$endYear}";
                    } else if ($detail->volumn[0] == '02') {
                        // 下册
                        $res[$key]->term = '学年';

                        // 根据创建时间生成学年
                        $createtime = substr($value->createtime,
                            0, (strlen($value->createtime) - 3)); // 处理时间戳毫秒
                        $endYear = date('Y', $createtime);
                        $startYear = $endYear - 1;
                        $res[$key]->schoolYear = "{$startYear}-{$endYear}";
                    } else {
                        // 全一册
                        $res[$key]->term = '学年';

                        // 根据创建时间生成学年
                        $createtime = substr($value->createtime,
                            0, (strlen($value->createtime) - 3)); // 处理时间戳毫秒
                        $startYear = date('Y', $createtime);
                        $endYear = $startYear + 1;
                        $res[$key]->schoolYear = "{$startYear}-{$endYear}";
                    }
                }

                // 封面缩略图相对地址
			    $tempPath = SITE_URL . '/apps/yunpan/_static/images/' . $this->defaultBookName;

                // 默认封面
                $res[$key]->thumbpath = $tempPath . '-35.jpg';
                $res[$key]->thumbpath74 = $tempPath . '-74.jpg';
                $res[$key]->thumbpath80 = $tempPath . '-80.jpg';

                // 从资源网关获取备课本对应的书本信息
                $bookinfo = $this->bookClient->getCover($detail->book[0]);
                if ($bookinfo->statuscode == 200) {
				    // 使用网关书本封面 
                    $res[$key]->thumbpath   = $bookinfo->data->cover_35_46;
                    $res[$key]->thumbpath74 = $bookinfo->data->cover_74_100;
                    $res[$key]->thumbpath80 = $bookinfo->data->cover_80_108;
                }
			}
		}

		return $res;
  	}


	/**
	 * 获取书本文件夹和文件列表信息
     * @param  string $cyuid      文件夹所属用户cyuid(与用户服务ID一致)
     * @param  string $pDirID     父目录ID, 只获取此目录下的一级子目录列表
     * @param  int $page          第几页
     * @param  int $limit         每页显示记录数
     * @param  string $ordertype  记录排序方式
     * @return {[type]}         [description]
     */  	
  	public function getBookFileAndDirs($cyuid, $pDirID, $page = 0, $limit = 0){
  		$result = array();
		//参数信息合法检查  		
		if(!isset($cyuid) || !isset($pDirID)){
			return $result;
		}
		$files = $this->diskClient->listFilesAndDirs($cyuid, $pDirID, $page, $limit, 3, false);
		foreach ($files as $value) {
			//过滤文件夹后缀
			$tempName = $value->name;
			$n = strpos($tempName,$this->dirSuffix);//寻找位置
			if ($n > 0) {
				$value->name = substr($tempName,0,$n);//删除后面
			}
			$result[] = $value;
		}
		return $result;
  	}

	/**
	 * 获取书本文件夹表信息
     * @param  string $cyuid      文件夹所属用户cyuid(与用户服务ID一致)
     * @param  string $pDirID     父目录ID, 只获取此目录下的一级子目录列表
     * @param  int $page          第几页
     * @param  int $limit         每页显示记录数
     * @param  string $ordertype  记录排序方式
     * @return {[type]}         [description]
     */  	
  	public function getBookDirs($cyuid, $pDirID, $page = 0, $limit = 0){
		//参数信息合法检查  		
		if(!isset($cyuid) || !isset($pDirID)){
			return null;
		}
		$dirs = $this->diskClient->getDirs($cyuid, $pDirID, $page, $limit, 3, false);
		$result = array();
		foreach ($dirs->data as $value) {
			//过滤文件夹后缀
			$tempName = $value->name;
			$n = strpos($tempName,$this->dirSuffix);//寻找位置
			if ($n > 0) {
				$value->name = substr($tempName,0,$n);//删除后面
			}
			$result[] = $value;
		}
		return $result;
  	}

    /**
     * 获取单个备课本文件夹信息
     * @param  string $cyuid        文件夹所属用户ID(与用户服务ID一致)
     * @param  stirng $dirID      目录ID
     * @return {[type]}        [description]
     */
    public function getBeikebenDetail($cyuid, $dirID){
    	//参数信息合法检查  		
		if (!isset($cyuid) || !isset($dirID)) {
			return null;
		}

		$res = $this->diskClient->getDir($cyuid, $dirID);
		if (!$res->hasError) {
			$res = $res->obj->dirInfoVal;

			//过滤文件夹后缀
			$tempName = $res->name;
			$n = strpos($tempName, $this->dirSuffix); //寻找位置
			if ($n) {
				$res->name = substr($tempName, 0, $n); //删除后面
			}

            // 学年年学期默认为空
            $res->schoolYear = '';
            $res->term = '';
            
            // 获取文件夹元数据
            $detail = $this->diskClient->getDirProps($cyuid, $dirID);
            if (!empty($detail)) {
                if ($detail->volumn[0] == '01') {
                    // 上册
                    $res->term = '学年';

                    // 根据创建时间生成学年
                    $createtime = substr($res->createtime,
                        0, (strlen($res->createtime) - 3)); // 处理时间戳毫秒
                    $startYear = date('Y', $createtime);
                    $endYear = $startYear + 1;
                    $res->schoolYear = "{$startYear}-{$endYear}";
                } else if ($detail->volumn[0] == '02') {
                    // 下册
                    $res->term = '学年';

                    // 根据创建时间生成学年
                    $createtime = substr($res->createtime,
                        0, (strlen($res->createtime) - 3)); // 处理时间戳毫秒
                    $endYear = date('Y', $createtime);
                    $startYear = $endYear - 1;
                    $res->schoolYear = "{$startYear}-{$endYear}";
                } else {
                    // 全一册
                    $res->term = '学年';

                    // 根据创建时间生成学年
                    $createtime = substr($res->createtime,
                        0, (strlen($res->createtime) - 3)); // 处理时间戳毫秒
                    $startYear = date('Y', $createtime);
                    $endYear = $startYear + 1;
                    $res->schoolYear = "{$startYear}-{$endYear}";
                }
            }

            //封面缩略图相对地址
			$tempPath = SITE_URL . '/apps/yunpan/_static/images/' . $this->defaultBookName;

			//默认封面
			$res->thumbpath = $tempPath . '-35.jpg';
			$res->thumbpath74 = $tempPath . '-74.jpg';
			$res->thumbpath80 = $tempPath . '-80.jpg';

            // 从资源网关获取备课本对应的书本信息
            $bookinfo = $this->bookClient->getCover($detail->book[0]);
            if ($bookinfo->statuscode == 200) {
				// 使用网关书本封面 
                $res->thumbpath   = $bookinfo->data->cover_35_46;
                $res->thumbpath74 = $bookinfo->data->cover_74_100;
                $res->thumbpath80 = $bookinfo->data->cover_80_108;
            }
		}

		return $res;
    }

	/**
	 * 创建备课本、课目录
	 * @author yuliu2@iflytek.com
	 * 
	 * @param string $cyuid cycore 用户id
	 * @param string $pDirID 备课本fid
	 * @param array $bookInfo 书本信息
	 * @return array('statuscode' => 1|0, 'data' => 'xxx') 返回结果
	 */
	private function addBeikeben($cyuid,$pDirID, $bookInfo){
		$result = array('statuscode' => 0);
		// 课本单元信息
		$book = $this->getBookinfo ( $bookInfo ["bookID"] );
		if (empty ( $book )) {
			$result ['data'] = '课本数据获取失败';
			return $result;
		}
		$units = $book->general->resourcedescriptor->units;
		//获取所有课目录
		$list = $this->getCourseList($units);
		if(empty($list)){
			$result['data'] = '该书未正确标记到课信息';
			return $result;
		}
		// book,grade,subject,publisher,volumn,type,course,unit,[phase]
		$metadata = array ();
		$metadata ['book'] = $book->properties->book;
		$metadata ['grade'] = $book->properties->grade;
		$metadata ['subject'] = $book->properties->subject;
		$metadata ['volumn'] = $book->properties->volumn;
		$metadata ['type'] = $book->properties->type;
		$metadata ['publisher'] = $book->properties->publisher;
		$metadata ['stage'] = $book->properties->stage;
		$metadata ['edition'] = $book->properties->edition;
		$metadata ['phase'] = $book->properties->phase;
		
		if ($metadata ['volumn'] [0] == '01') {
			$startYear = date ( 'Y', time () );
			$endYear = $startYear + 1;
			$bookInfo ["book"] = $bookInfo ["book"] . ' (' . $startYear . '-' . $endYear . '学年' . ')';
		} else if ($metadata ['volumn'] [0] == '02') {
			$startYear = date ( 'Y', time () );
			$endYear = $startYear - 1;
			$bookInfo ["book"] = $bookInfo ["book"] . ' (' . $endYear . '-' . $startYear . '学年' . ')';
		} else {
			// 全一册
			$startYear = date ( 'Y', time () );
			$endYear = $startYear + 1;
			$bookInfo ["book"] = $bookInfo ["book"] . ' (' . $startYear . '-' . $endYear . '学年' . ')';
		}
                
		// 创建课本文件夹
		$dataBook = $this->diskClient->mkdirAndSetProps($cyuid, $pDirID, $bookInfo["book"], FolderTypeModel::BOOK, $metadata, false);
		if($dataBook->hasError){
			$errorInfo = $dataBook->obj->errorInfo;
			Log::write($cyuid.', pDirID '.$pDirID.', '.$errorInfo->errorcode.', '.$errorInfo->msg);
			$result['data'] = '备课本已经存在';
			return $result;
		}
		$bookDirID = $dataBook->obj->dirInfoVal->fid;
		
		$hasError = false;
		foreach ($list as $course){
			$metadata['course'] = $course->Code;
			$course->Name = filterFileName($course->Name);
			
			$mkdir_result = $this->diskClient->mkdirAndSetProps($cyuid, $bookDirID, $course->Name, FolderTypeModel::COURSE, $metadata, false);
			if($mkdir_result->hasError){
				$errorInfo = $mkdir_result->obj->errorInfo;
				//课时名称重复
				if($errorInfo->errorcode == 40003){
					$randomName = $course->Name . '(' . rand(1000,2000) . ')';
					$rs = $this->diskClient->mkdirAndSetProps($cyuid, $bookDirID, $randomName, FolderTypeModel::COURSE, $metadata, false);
				}else{
					Log::write($cyuid.','.$course->Name.','.$errorInfo->errorcode.', '.$errorInfo->msg);
					$result['data'] = 'Course目录创建失败' . ' ' . $errorInfo->msg;
					$hasError = true;
					break;
				}
			}
		}

		if($hasError){
			//回滚删除备课本
			D('Yunpan')->deleteYunpanDir($cyuid, $bookDirID, true);
			return $result;
		}
	
		$result['statuscode'] = 1;
		$result['data'] =$dataBook->obj->dirInfoVal;
		return $result;
	}

	//引号转换
	public function convertQuotes($str){
		for($i=0; $i<strlen($str); $i++){
			if($str[$i]=="'"){
				echo $i;
				$str = substr_replace($str, "‘", $i, 1);
			}else if($str[$i]=="\""){
				$str = substr_replace($str, "“", $i, 1);
			}
		}
		return $str;
	}
		
	/**
	 * 网关获取课本信息
	 * @author yangli4
	 * @param array $bookid 		课本id
	 * @return array				单元信息数组
	 */
	public function getBookinfo($bookid){
		if (empty($bookid)) {
			return null;
		} else {
			$resClient = D('CyCore')->Tree;
			$obj = $resClient->Tree_getBookindex($bookid);
			if ($obj->statuscode == 200) {
				return $obj->data;
			} else {
				return null;
			}
		}
	}


    /**
     * 为文件夹添加业务属性
     * @param string $uid             文件夹所属用户ID(与用户服务ID一致)
     * @param string $dirID           文件夹ID
     * @param array $props            属性键值对
     */
    public function addDirProps($cyuid, $dirID, $props){
      return $this->diskClient->addDirProps($cyuid, $dirID, $props);
    }

	/**
	 * 检查参数
	 * @param  $cyuid
	 * @param  $pDirID
	 * @param  $bkbInfo
	 * @return boolean 正确：false，错误： true
	 */
	private function checkNewBeikeben($cyuid, $pDirID, $bkbInfo){
		return (isset($cyuid) && isset($pDirID) &&
            !empty($bkbInfo['book']) && //书本名称
            !empty($bkbInfo['bookID'])); //书本id
	}
	/**
	 * 递归获取书本所有课
	 * @author yuliu2@iflytek.com
	 * 
	 * @param array $units
	 * @param string $unitCode 传递父级code
	 * @return array() 课列表
	 */
	private function getCourseList($units, $unitCode = null){
		$list = array();
		foreach ($units as $unit){
			if($unit->Hide || empty($unit->Name) || empty($unit->Code)){
				continue;
			}
			if($unitCode){
				//合并多级Code，便于查询到课
				$unit->Code = $unitCode . '-' . $unit->Code;
			}
			if(!empty($unit->Courses)){
				//递归调用
				$inner_list = $this->getCourseList($unit->Courses, $unit->Code);
				$list = array_merge($list, $inner_list);
			} else if(in_array($unit->Type[0], array('section', 'course'))){
				array_push($list, $unit);
			}
		}
		return $list;
	}
}
?>
