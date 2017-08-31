<?php

/**
 * PHP SDK For iFLYTEK
 *
 * @version         $Id$
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
 * @history
 *                  shenghe | 2013-04-22 08:44:23 | created
 */

include_once(dirname(__FILE__) .'/http.php');

/**
 * 基类，所有的接口实现都应该继承此类
 * @package         Helper
 */
class Base {
    /**
     * 提供此接口的服务器地址
     * @var string
     */
    protected  $host = '127.0.0.1';

    /**
     * http协议头
     * @var array
     */
    private $header = array();

    /**
     * 注册得到的账号Id
     * @var string
     */
    private $appkey = '';

    /**
     * 校验用accesstoken
     * @var string
     */
    private $accesstoken = '';

    /**
     * 构造函数
     * @param string $accesstoken 校验用accesstoken
     * @param array $header       http协议头
     */
    public function __construct($appkey, $accesstoken, $header){
        $this->appkey = $appkey;
        $this->accesstoken = $accesstoken;
        $this->header = $header;
    }

    /**
     * 设置api所在服务器地址,默认是127.0.0.1
     *
     * @param string $host API所在的地址，格式为：[http(s)://]127.0.0.1[:80]/resourceservice
     * @return  void
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
    public function send($method, $path, $params = array()) {
        srand((double)microtime() * 1000000);
        $nonce = rand();
        if (strpos('/', $path) != 0) {
            $path = '/'. $path;
        }

        $this->header['Authorization'] = 'Bearer '. md5($path .'?accesstoken='. $this->accesstoken .'&nonce='. $nonce);
        $this->header['NONCE'] = $nonce;

        $http = Http::factory($this->host);
        $http->setHeader($this->header);
        $method = strtolower($method);
        return $http->$method($path, $params);
    }
}