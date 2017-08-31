<?php

/**
 * PHP SDK For iFLYTEK
 *
 * @version         1.0.0.2
 * @author          heiyeluren
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
 * @history
 *                  1.0.0.0 | heiyeluren | 2009/12/13 04:43 | created <br/>
 *                  1.0.0.1 | heiyeluren | 2009/12/16 10:30 | 实现基本HTTP各种接口操作支持 <br/>
 *                  1.0.0.2 | shenghe    | 2013-04-09 14:02:24 | 修复单例模式导致的每次发uri链接,请求都一样的问题;完善注释;修复其他BUG <br/>
 */


/**
 * HTTP操作类
 *
 * 以工厂模式实现
 * @package         Helper
 * @example:
 * <pre>
 *  try {
 *      $http = Http::factory('http://192.168.18.21/provinces/get/', Http::TYPE_SOCK );
 *      echo $http->get();
 *      $http = Http::factory('http://192.168.18.21/provinces/get/', Http::TYPE_SOCK );
 *      echo $http->post('', array('user'=>'我们', 'nick'=>'ASSADF@#!32812989+-239%ASDF'), '', array('aa'=>'bb', 'cc'=>'dd'));
 *  } catch (Exception $e) {
 *      echo $e->getMessage();
 *  }
 *  </pre>
 */

class Http {
    /*
     * @var 使用 Curi
     */
    const TYPE_CURL = 1;


    /**
     * 保证对象不被clone
     */
    private function __clone(){
    }

    /**
     * 构造函数
     */
    private function __construct(){
    }


    /**
     * HTTP工厂操作方法
     *
     * @param string    $host           需要访问的host
     * @param int       $type           需要使用的HTTP类
     * @return object                   HTTP操作类实例
     */
    public static function factory($host = '', $type = self::TYPE_CURL){
        switch ($type) {
            case self::TYPE_CURL:
                if (!function_exists('curl_init')) {
                    throw new Exception(__CLASS__ . " PHP Curl extension not install");
                }
                $obj = new Http_CURL($host);
                break;
            default:
                throw new Exception("http access type $type not support");
        }
        return $obj;
    }


    /**
     * 生成一个供Cookie或HTTP GET Query的字符串
     *
     * @param array     $data       需要生产的数据数组，必须是 Name => Value 结构
     * @param string    $sep        两个变量值之间分割的字符，缺省是 &
     * @return string               返回生成好的Cookie查询字符串
     */
    public static function makeQuery($data, $sep = '&'){
        $encoded = '';
        while (list($k, $v) = each($data)) {
            $encoded .= ($encoded ? "$sep" : "");
            $encoded .= rawurlencode($k) . "=" . rawurlencode($v);
        }
        return $encoded;
    }
}


/**
 * 使用CURL 作为核心操作的HTTP访问类
 *
 * CURL 以稳定、高效、移植性强作为很重要的HTTP协议访问客户端，必须在PHP中安装 CURL 扩展才能使用本功能
 * @package         Helper
 */
class Http_CURL
{
    /**
     * @var string 需要发送的cookie信息
     */
    private $cookies = '';

    /**
     * @var array 需要发送的头信息
     */
    private $header = array();

    /**
     * 发送的服务器地址
     * @var string
     */
    private $host = '';

    /**
     * @var string 需要访问的uri path地址
     */
    private $uri = '';

    /**
     * @var array 需要发送的数据
     */
    private $vars = array();

    /**
     * 构造函数
     *
     * @param string $host       请求的host
     */
    public function __construct($host)
    {
        if (!stripos('://', $host)) {
            $host = 'http://'. $host;
        }
        $this->host = $host;
    }

    /**
     * 保证对象不被clone
     */
    private function __clone()
    {
    }

    /**
     * 发送HTTP GET请求
     *
     * @param string        $uri                如果初始化对象的时候没有设置或者要设置不同的访问uri，可以传本参数
     * @param array         $vars               需要单独返送的GET变量
     * @param array|string  $header = array()   需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *                                          或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string|array  $cookie             需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *                                          或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int           $timeout            连接对方服务器访问超时时间，单位为秒
     * @param array         $options            当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function get($uri = '', $vars = array(), $header = array(), $cookie = '', $timeout = 20, $options = array()){
        $this->seturi($uri);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('GET', $timeout);
    }


    /**
     * 发送HTTP POST请求
     *
     * @param string            $path           如果初始化对象的时候没有设置或者要设置不同的访问path，可以传本参数
     * @param array             $vars           需要单独返送的变量
     * @param array             $files          文件路径数组
     * @param array|string      $header         需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *                                          或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string|array      $cookie         需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *                                          或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int               $timeout        连接对方服务器访问超时时间，单位为秒
     * @param array             $options        当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function post($path = '', $vars = array(), $files = array(), $header = array(), $cookie = '', $timeout = 20, $options = array()){
        $this->seturi($path);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('POST', $timeout);
    }

    /**
     * 发送HTTP PUT请求
     *
     * @param string            $path           如果初始化对象的时候没有设置或者要设置不同的访问path，可以传本参数
     * @param array             $vars           需要单独返送的变量
     * @param array|string      $header         需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *                                          或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string|array      $cookie         需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *                                          或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int               $timeout        连接对方服务器访问超时时间，单位为秒
     * @param array             $options        当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function put($path = '', $vars = array(), $header = array(), $cookie = '', $timeout = 0, $options = array()){
        $this->seturi($path);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('PUT', $timeout);
    }

    /**
     * 发送HTTP DELETE请求
     *
     * @param string            $path            如果初始化对象的时候没有设置或者要设置不同的访问uri，可以传本参数
     * @param array             $vars           需要单独返送的变量
     * @param array|string      $header         需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *                                          或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string|array      $cookie         需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *                                          或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int               $timeout        连接对方服务器访问超时时间，单位为秒
     * @param array             $options        当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function del($path = '', $vars = array(),  $header = array(), $cookie = '', $timeout = 0, $options = array()){
        $this->seturi($path);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('DELETE', $timeout);
    }

    /**
     * 发送HTTP请求核心函数
     *
     * @param string        $method     使用GET还是POST方式访问
     * @param int           $timeout    连接对方服务器访问超时时间，单位为秒
     * @param array         $options    当前操作类一些特殊的属性设置
     * @return string                   返回服务器端读取的返回数据
     */
    private function send($method = 'GET', $timeout = 5, $options = array()){
    	
        //处理参数是否为空
        if ($this->uri == '') {
            throw new Exception(__CLASS__ . ": Access uri is empty");
        }

        //初始化Curi
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        //设置特殊属性
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->vars);

        } else if ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->vars);

        } else {
            if ($method == 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }

            if (!empty($this->vars)) {
                $query = Http::makeQuery($this->vars);
                $parse = parse_url($this->uri);
                $sep   = isset($parse['query']) ? '&' : '?';
                $this->uri .= $sep . $query;
            }
        }

        //设置cookie信息
        if (!empty($this->cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
        }

        //设置HTTP缺省头
        if (empty($this->header)) {
            $this->header = array(
                'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)'
            );
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);

        //发送请求读取输数据
        curl_setopt($ch, CURLOPT_URL, $this->uri);
        $data = curl_exec($ch);
        if (($err = curl_error($ch))) {
            curl_close($ch);
            throw new Exception(__CLASS__ . " error: " . $err);
        }
        curl_close($ch);
       
        return $data;
    }

    /**
     * 设置需要发送的HTTP头信息
     *
     * @param array|string     $header  需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *                                  或单一的一条类似于 'Host: example.com' 头信息字符串
     * @return void
     */
    public function setHeader($header){
        if (empty($header)) {
            return;
        }
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                $this->header[] = is_numeric($k) ? trim($v) : (trim($k) . ": " . trim($v));
            }
        } elseif (is_string($header)) {
            $this->header[] = $header;
        }
    }

    /**
     * 设置Cookie头信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param string|array $cookie     需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *                                 或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setCookie($cookie){
        if (empty($cookie)) {
            return;
        }
        if (is_array($cookie)) {
            $this->cookies = Http::makeQuery($cookie, ';');
        } elseif (is_string($cookie)) {
            $this->cookies = $cookie;
        }
    }

    /**
     * 设置要发送的数据信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param array $vars 设置需要发送的数据信息，一个类似于 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setVar($vars){
        if (empty($vars)) {
            return;
        }
        if (is_array($vars)) {
            $this->vars = $vars;
        }
    }

    /**
     * 设置要请求的uri地址
     *
     * @param string $path 需要设置的uri地址
     * @return void
     */
    public function seturi($path) {
        if (strpos($path, '/') != 0) {
            $path = '/'. $path;
        }
        $this->uri =  $this->host . $path;
    }
}

?>