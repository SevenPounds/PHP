<?php
/**
 * 资源包接口
 */
class BundleModel extends Model {
	private $bundleClient = null;

	function __construct() {
		parent::__construct();
		$this->bundleClient = new BundleClient();
	}
	
	/**
	 * 获取某课时下的资源列表
	 * @param array $condition
	 * @param array $fields
	 * @param int $skip
	 * @param int $limit
	 * @param string $order
	 * @return object
	 */
	public function get_resource_bundles($condition = array(), $fields = array(), $skip = 0, $limit = null, $order = null){
		return $this->bundleClient->get_resource_bundles($condition, $fields, $skip, $limit, $order);
	}
	
	/**
	 * 获取资源包内的资源列表
	 * @param string $bundle_id
	 * @param array $fields array('general', 'properties', ..)，传空数组表示全部属性
	 * @param int $skip
	 * @param int $limit 传null表示余下所有
	 * @param string $order
	 * @return object
	 */
	public function get_resources_in_bundle($bundle_id, $fields = array(), $skip = 0, $limit = null, $order = null){
		return $this->bundleClient->get_resources_in_bundle($bundle_id, $fields, $skip, $limit, $order);
	}
}

?>