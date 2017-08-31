<?php

/**
 * PHP SDK For iFLYTEK
 *
 * @version         $Id$
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
 * @history
 *                  shenghe | 2013-4-12 23:32:53 | created
 */
include(dirname(__DIR__) .'/http.php');

/**
 * 安全校验接口
 * @package         Helper\API
 */
class OAuth2 {
    /**
     * 提供此接口的服务器地址
     */
     private $host = '127.0.0.1';

    /**
     * 应用Id
     * @var string
     */
    private $appkey = '';


    /**
     * 构造函数
     * @param string $appkey 应用注册时的Id
     */
    public function __construct($appkey, $app_secret) {
        $this->appkey = $appkey;
        $this->app_secret = $app_secret;
    }

    /**
     * 获取校验用随机数
     *
     * @param  string $state        用于保持请求和回调的状态，在回调时，会在Query Parameter中回传该参数。
     *                              开发者可以用这个参数验证请求有效性，也可以记录用户请求授权页前的位置。
     *                              这个参数可用于防止跨站请求伪造（CSRF）攻击
     * @return string               出错返回空字符串，成功返回code
     */
    public function get_oauth_code($state = '') {
        if (!is_string($state)) {
            return '';
        }

        $_params = array(
            'response_type' => 'token',
            'client_id' => $this->appkey,
            'randsecret' => md5($this->appkey .'&'. $this->app_secret .'&'. $state),
            'state' => $state
            );

        $ret = json_decode($this->send("get", '/oauth2/code/', $_params));
        if (is_object($ret) && $ret->statuscode == 200) {
            if ($ret->data->state == $state) {
                return $ret->data->code;
            }
        }
        return '';
    }

    /**
      * 获取accesstoken
      *
      * @param      string          $code                  获取的随机数
      * @return     string                                 出错时返回空字符串，成功返回accesstoken
      */
    public function get_oauth_token($code) {
        $token = md5($this->appkey .'&'. $app_secret .'&'. $code);
        $_params = array(
            'grant_type' => 'authorization_token',
            'client_id' => $this->appkey,
            "client_secret" => $token,
            'code' => $code
            );

        $ret = json_decode($this->send("get", '/oauth2/token/', $_params));
        if (is_object($ret) && $ret->statuscode == 200) {
            return $ret->data->accesstoken;
        }

        return '';
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
    private function send($method, $path, $params = array()) {
        srand((double)microtime() * 1000000);
        $nonce = rand();
        if (strpos('/', $path) != 0) {
            $path = '/'. $path;
        }

        $http = Http::factory($this->host);
        $method = strtolower($method);
        return $http->$method($path, $params);
    }
}