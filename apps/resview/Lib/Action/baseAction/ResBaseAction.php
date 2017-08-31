<?php
/*
 * 资源类Acton的父类
 */
import ( APP_COMMON_PATH . '/resGate_common.php' );
class ResBaseAction extends Action {
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 *
	 * @param $val 魔术方法        	
	 * @return CategoryClient ResCommentClient ResourceClient TreeClient
	 */
	public function __get($val) {
		switch ($val) {
			// 资源服务对象
			case 'resService' :
				return new ResourceClient ();
			// 目录树服务对象
			case 'treeService' :
				return new TreeClient ();
			// 资源分类服务对象
			case 'categoryService' :
				return new CategoryClient ();
			// Cycore
			case 'CyCore' :
				return new CyClient ();
			default :break;
		}
	}
}