<?php
require_once (dirname ( __FILE__ ) . '/baseserver.php');
class TreeServer extends BaseServer{
	private $treeClient = null;
	private static $_instance;
	
	public function __construct() {
		parent::__construct();
		$this->treeClient = $this->client->tree;
	}
	
	public static function getInstance() {
		if (! (self::$_instance instanceof self)) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	
	/**
	 * 查询目录树
	 * @param string $treename eg:"booklibrary"
	 * @param array $condition  eg:array("productid"=>"bbt")
	 * 
	 */
	public function get_tree($treename, $condition = array()){
		try{
			$json = $this->treeClient->get_tree($treename, $condition);
			$obj = $this->getResult($json);
			return $obj;
		}catch (Exception $e){
			$this->logger->error($e->getMessage());
		}
	}
	
	/**
	 *  创建目录树
	 * @param array $tree_index
	 */
	public function create_tree($tree_index = array()){
		try{
			$json = $this->treeClient->create_tree($tree_index);
			$obj = $this->getResult($json);
			return $obj;
		}catch (Exception $e){
			$this->logger->error($e->getMessage());
		}
	}
	
	/**
	 * 删除指定条件的目录树
	 * @param string $treename
	 * @param array $condition
	 */
	public function del_tree($treename, $condition = array()){
		try{
			$json = $this->treeClient->del_tree($treename, $condition);
			$obj = $this->getResult($json);
			return $obj;
		}catch (Exception $e){
			$this->logger->error($e->getMessage());
		}
	}
	
	/**
	 * 查询分级目录下的数据
	 * @param string $treename  eg:"booklibrary"
	 * @param string $path      eg:"01,02"
	 * @param string $productid  eg:"bbt"
	 */
	public function get_tree_children($treename, $path = array(), $productid){
		try{
			$json = $this->treeClient->get_tree_children($treename, $path, $productid);
			$obj = $this->getResult($json);
			return $obj;
		}catch (Exception $e){
			$this->logger->error($e->getMessage());
		}
	}
	
	/**
	 * 创建分级目录下的节点信息
	 * @param string $treename
	 * @param array $tree_index
	 * @param array $condition
	 */
	public function create_tree_children($treename, $tree_index = array(), $condition = array()){
		try{
			$json = $this->treeClient->create_tree_children($treename, $tree_index, $condition);
			$obj = $this->getResult($json);
			return $obj;
		}catch (Exception $e){
			$this->logger->error($e->getMessage());
		}
	}
	
	/**
	 *  删除分级目录下的节点信息
	 * @param string $treename  eg:"booklibrary"
	 * @param array $condition
	 */
	public function del_tree_children($treename, $condition = array()){
		try{
			$json = $this->treeClient->del_tree_children($treename, $condition);
			$obj = $this->getResult($json);
			return $obj;
		}catch (Exception $e){
			$this->logger->error($e->getMessage());
		}
	}
	
	/**
	 *参数查询目录树节点
     * 支持分页、排序
	 * @param string $treename  eg:"booklibrary"
	 * @param string $node      eg:"grade"
	 * @param array $condition  eg:array("subject"=>"01")
	 */
	public function get_tree_nodes($treename, $node, $condition = array()){
		try{
			$json = $this->treeClient->get_tree_nodes($treename, $node, $condition);
			$obj = $this->getResult($json);
			return $obj;
		}catch (Exception $e){
			$this->logger->error($e->getMessage());
		}
	}
}

?>