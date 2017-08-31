<?php
/**
 * Solr空间/资讯服务模型
 * 
 * @author zqxiang@iflytek.com
 * @version 创建时间：2014-2-13 上午11:48:19
 */

class Solr {
	
	/**
	 * solr连接器
	 * @var Apache_Solr_Service
	 */
	protected $conn = null;
	
	
	/**
	 * 初始化方法
	 * @param string $schema	查询的类别(如查询资讯为news, 查询空间为zone)
	 * @return void
	 */
	public function __construct($schema = 'news') {
		switch ($schema) {
			case 'zone':
				$path = C('SOLR_SERVER.PATH') . 'rrt_zone';
				break;
			default: 
				$path = C('SOLR_SERVER.PATH') . 'rrt_news';
		}
		$this->conn = new Apache_Solr_Service(C('SOLR_SERVER.HOST'), C('SOLR_SERVER.PORT'), $path);
	}
	
	/**
	 * solr网络状态
	 * @return boolean
	 */
	public function ping() {
		return $this->conn->ping(C('SOLR_CONNECT_TIMEOUT'));	//设置超时
	}
	
	
	/**
	 * solr查询
	 * @param string $queryString 模糊查询的字符串
	 * @param int $offset
	 * @param int $limit
	 * @param array $param solr其它查询条件(如fq, sort等)
	 * 
	 * @return array
	 */
	public function query($queryString, $offset = 0, $limit = 20, $param = array()) {
		try {
			/**
			 * 设定高亮
			 * @var String
			 */
			$param['hl'] = 'true'; //开启高亮
			$param['hl.simple.pre'] = '<font color="red">'; //高亮标签开始
			$param['hl.simple.post'] = '</font>'; //高亮标签结束
			$param['hl.usePhraseHighlighter'] = 'true';
			$param['hl.requireFieldMatch'] = 'true';
			$param['hl.highlightMultiTerm'] = 'true';
			
			/**
			 * 查询
			 * @var String
			 */
			$query = $this->conn->search($queryString, $offset, $limit, $param, 'POST');
			$r = json_decode($query->getRawResponse(), true);
			$hightlight_fields = explode(',', $param['hl.fl']);
			/**
			 * 处理高亮结果
			 * @var array
			 */
			if ($r['response']['numFound'] > 0) {
				foreach ($r['response']['docs'] as $k=>$v) {
					$highlighting = $r['highlighting'][$v['id']]; //定位到当前高亮部分数据
					//遍历高亮部分的所有匹配项
					foreach ($hightlight_fields as $field){
						if(isset($highlighting[$field])){
							$hl_content = $highlighting[$field][0];
						}else{
							//如果高亮字段没有匹配内容，直接截取内容填充 by yuliu2
							$hl_content = mStr($r['response']['docs'][$k][$field], 100, $charset="utf-8", $suffix=false);
						}
						$r['response']['docs'][$k][$field] = $hl_content;
					}
				}
			}
			
			/**
			 * 返回结果
			 * @var array
			 */
			return $r['response'];
			
		} catch (Exception $e) {
            Log::write($e->__toString(),Log::ERR);
		}
		return null;
	}
	
	
	/**
	 * solr数据添加/更新
	 * @param array $param
	 * 
	 * @return boolean
	 */
	public function add($param) {
		if (empty($param)) return false;
		
		try {
			$document = new Apache_Solr_Document();
			foreach ( $param as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $datum ) {
						$document->setMultiValue( $key, $datum );
					}
				} else {
					$document->$key = $value;
				}
			}
			$this->conn->addDocument($document);
			return true;
		}
		catch (Exception $e) {
            Log::write($e->__toString(),Log::ERR);
		}
		return false;
	}
	
	
	/**
	 * solr数据删除
	 * @param array $param
	 * 
	 * @return boolean
	 */
	public function delete($ids) {
		if (empty($ids)) return false;
	
		try {
			if (is_array($ids)) {
				$this->conn->deleteByMultipleIds($ids);
			} else {
				$this->conn->deleteById($ids);
			}
			// 	$solr->commit();
			return true;
		}
		catch (Exception $e) {
            Log::write($e->__toString(),Log::ERR);
		}
		return false;
	}
}