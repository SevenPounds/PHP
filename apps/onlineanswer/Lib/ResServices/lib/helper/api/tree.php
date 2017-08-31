<?php

/**
 * PHP SDK For iFLYTEK
 *
 * @version         $Id$
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
 * @history
 *                  shenghe | 2013-4-13 0:15:40 | created
 */

require_once (dirname(dirname(__FILE__)) .'/base.php');

/**
 * 目录树操作
 * @package         Helper\API
 */
class Tree extends Base {
    /**
     * 构造函数
     * @param string $accesstoken 校验用accesstoken
     * @param array $header       http协议头
     */
    public function __construct($appkey, $accesstoken, $header) {
        parent::__construct($appkey, $accesstoken, $header);
    }


    /**
     * 查询目录树
     *
     * @param  string $treename  目录树名称
     * @param  array  $condition 查询条件，字典数组
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     * @example
     * 查询班班通中booklibrary目录树 <br/>
     * <code> get_tree('booklibrary', array('productid' => 'bbt')) </code>
     */
    public function get_tree($treename, $condition = array()) {
        if (!is_string($treename) || empty($treename) || !is_array($condition)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }
        $condition['treename'] = $treename;

        return $this->send('get', '/resourceservice/tree/', $condition);
    }

    /**
     * 创建目录树
     *
     * @param  string  $tree_index 目录树信息
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_tree($tree_index = array()) {
        if (!is_array($tree_index)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $params = array(
            "tree" => json_encode($tree_index)
            );
        return $this->send('put', '/resourceservice/tree/', $tree_index);
    }

    /**
     * 删除指定条件的目录树
     * @param  string $treename   目录树名称
     * @param  array  $condition  查询条件，字典数组
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function del_tree($treename, $productid) {
        if (!is_string($treename) || empty($treename) || !is_string($condition)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $params = array(
            "treename" => $treename,
            "productid" => $productid
            );

        return $this->send('del', '/resourceservice/tree/', $params);
    }

    /**
     * 查询分级目录下的数据
     * @param  string $treename  目录树名称
     * @param  array  $path      查找的路径,逗号分隔的字符串，例如:"01,02,03"
     * @param  array  $productid 目录树所属应用的Id
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_tree_children($treename, $path, $productid) {
        if (!is_string($treename) || empty($treename) || !is_string($path) || !is_string($productid)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }
        $params  = array(
            "productid" => $productid,
            'path' => $path
            );

        return $this->send('get', '/resourceservice/tree/'. $treename .'/children/', $params);
    }

    /**
     * 查询分级目录下的节点信息
     * @param  string $treename     目录树名称
     * @param  array  $tree_index   节点的属性信息格式参考Readme.txt中树形结构章节的介绍，"categoryvalue"值的结构
     * @param  array  $path         查找的路径,逗号分隔的字符串，例如:"01,02,03"
     * @param  array  $productid    目录树所属应用的Id
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_tree_children($treename, $tree_index, $path, $productid) {
        if (!is_string($treename) || empty($treename) || !is_array($tree_index) || !is_string($path) || !is_string($productid)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $params  = array(
            'categoryvalues' => json_encode($tree_index),
            "productid" => $productid,
            'path' => $path
            );

        return $this->send('post', '/resourceservice/tree/'. $treename .'/children/', $params);
    }

    /**
     * 删除分级目录下的节点信息
     * @param  string $treename  目录树名称
     * @param  array  $path      查找的路径,逗号分隔的字符串，例如:"01,02,03"
     * @param  array  $productid 目录树所属应用的Id
     * @return array|string      错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function del_tree_children($treename, $path, $productid) {
        if (!is_string($treename) || empty($treename) || !is_string($path) || !is_string($productid)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $params = array(
            "path" => $path,
            "productid" => $productid
            );
        return $this->send('del', '/resourceservice/tree/'. $treename .'/children/', $params);
    }

    /**
     * 参数查询目录树节点
     * 支持分页、排序
     *
     * @param  string $treename  目录树名称
     * @param  string $node      节点名称
     * @param  array  $condition 查询条件
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_tree_nodes($treename, $node, $condition = array()) {
        if (!is_string($treename) || !is_string($node) || !is_array($condition)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $condition['target'] = $node;

        return $this->send('get', '/resourceservice/tree/'. $treename .'/', $condition);
    }
}
?>
