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
 * 目录操作接口
 * @package         Helper\API
 */
class Category extends Base {
    /**
     * 构造函数
     * @param string $accesstoken 校验用accesstoken
     * @param array $header       http协议头
     */
    public function __construct($appkey, $accesstoken, $header) {
        parent::__construct($appkey, $accesstoken, $header);
    }

    /**
     * 获取指定条件的目录属性
     * @param  array  $condition 条件，字典数组
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_category($condition = array()) {
        if (!is_array($condition)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        return $this->send('get', '/resourceservice/category/', $condition);
    }

    /**
     * 创建目录树属性
     *
     * 注意：<br/>
     * enabbr不可重复；<br/>
     * tree(s), child(ren), node(s) 作为保留关键字，不允许 category_enabbr 使用；<br/>
     *
     * @param  string $enabbr           分类的英文缩写
     * @param  string $name             取值的名称
     * @param  string $productid        指定目录树属性所属的应用Id
     * @param  array  $condition        查询条件，字典数组
     * @return array|string             错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_category($enabbr, $name, $productid, $condition = array()) {
        if (!is_string($enabbr) || !is_string($name) || !is_string($productid) || !is_array($condition)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $condition['category'] = json_encode(array('enabbr'=>$enabbr, 'name'=>$name, 'productid'=> $productid));

        return $this->send('put', '/resourceservice/category/', $condition);
    }

    /**
     * 删除指定条件的目录属性
     * @param  string   $enabbr           分类的英文缩写
     * @param  string   $productid        应用Id
     * @return array|string             错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function del_category($enabbr, $productid) {
        if (!is_string($enabbr) || !is_string($productid)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $params = array('productid'=>$productid, 'enabbr' => $enabbr);

        return $this->send('del', '/resourceservice/category/', $params);
    }

    /**
     * 参数查询目录树节点
     * @param  string $enabbr    为欲查分类的英文缩写
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     * @example
     *  查询班班通中语文包含了哪些年级 <br>
     *  <code> get_category_enabbr('grade') </code>
     */
    public function get_category_value($enabbr) {
        if (!is_string($enabbr) || empty($enabbr)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        return $this->send('get', '/resourceservice/category/'. $enabbr .'/');
    }

    /**
     * 使用create接口为某一资源分类新增一个或多个取值
     * @param  string $enabbr 分类的英文缩写
     * @param  string $name   新取值的名称
     * @param  string $code   新取值的code
     * @param  array  $other  新增的其他字段
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function create_category_value($enabbr, $name, $code, $other = array()) {
        if (!is_array($other)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $params = array(
          'category_value' => json_encode(array_merge(array('name' => $name, 'code' => $code), $other))
          );

        return $this->send('post', '/resourceservice/category/'. $enabbr .'/', $params);
    }

    /**
     * 删除某一资源分类中的一个取值
     *
     * @param  string $enabbr   分类的英文缩写
     * @param  string $code     分类的某个属性code号
     * @return array|string     错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function del_category_value($enabbr, $code) {
        if (!is_string($enabbr) || !is_string($code)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        return $this->send('del', '/resourceservice/category/'. $enabbr .'/'. $code .'/');
    }
}
?>
