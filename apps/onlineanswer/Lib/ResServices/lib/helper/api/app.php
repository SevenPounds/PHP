<?php

/**
 * PHP SDK For iFLYTEK
 *
 * @version         $Id$
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
 * @history
 *                  shenghe | 2013-4-12 23:13:57 | created
 */

include(dirname(__DIR__) .'/http.php');

/**
 * 校验接口
 * @package         Helper\API
 */
class App {
    /**
     * 提供此接口的服务器地址
     */
    private $host = '127.0.0.1';

    /**
     * http协议头
     * @var array
     */
    private $header = array();

    /**
     * 构造函数
     * @param array $header       http协议头
     */
    public function __construct($header) {
        $this->header = $header;
    }

    /**
     * 获取应用信息(不对外开放)
     *
     * @param      string        $productid                            应用名称(应用ID)
     * @return     array|string                                错误码数组信息|Json字符串(由用户进行反序列化)
     */
    public function get_app($productid) {
        if (!is_string($productid) || empty($productid)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        return $this->send('get', '/app/'. $productid .'/');
    }

    /**
      * 创建应用(不对外开放)
      *
      * @param  string        $name                         应用名称(应用ID), 每个应用都不会重复
      * @param  string        $description                  应用描述
      * @param  string        $redirect                     验证的时候，回调地址
      * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
      */
    public function create_app($name, $description = "", $redirect = '') {
        if (
            !is_string($name) ||
            empty($name) ||
            !is_string($description) ||
            !is_string($redirect) ||
            !is_array($scope)
            ) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        $_params = array(
            "name" => $name,
            "description" => $description,
            'redirect' => $redirect
            );

        return $this->send('put', '/app/', $_params);
    }

    /**
      * 删除应用(不对外开放)
      *
      * @param      string        $productid                    应用名称(应用ID)
      * @return     array|string                            错误码数组信息|Json字符串(由用户进行反序列化)
      */
    public function del_app($productid) {
        if (!is_string($productid) || empty($productid)) {
            return array('total' => 1, 'statuscode' => 400, 'data' => "invalid parameters");
        }

        return $this->send('del', '/app/'. $productid .'/');
    }

    /**
     * 设置api所在服务器地址,默认是127.0.0.1
     * @param string $host [description]
     */
    public function set_host($host) {
      $host = str_replace('http://', '', $host);
      $this->host = str_replace('https://', '', $host);
    }

    /**
     * 发送指定协议的请求
     *
     * @param  string $method 协议名称，可以为：get,post,del,put
     * @param  string $path   请求的路径
     * @param  array  $params 请求参数
     * @return string         指定格式的字符串
     */
    private function send($method, $path, $params = array()) {
        srand((double)microtime() * 1000000);
        $nonce = rand();
        if (strpos('/', $path) != 0) {
            $path = '/'. $path;
        }

        $http = Http::factory($this->host);
        $http->setHeader($this->header);
        $method = strtolower($method);
        return $http->$method($path, $params);
    }
}