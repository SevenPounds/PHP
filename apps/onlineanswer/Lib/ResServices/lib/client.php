<?php
/**
 * PHP SDK For iFLYTEK
 *
 * @version         $Id$
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
 * @history
 *                  jhzhang | 2013-03-27 09:57:35 | created <br/>
 *                  shenghe | 2013-03-28 16:06:58 | 添加安全相关的函数,完善资源操作和目录操作接口 <br/>
 *                  shenghe | 2013-04-12 16:26:13 | 更改API调用方式,添加权限相关接口 <br/>
 */



/**
 * 资源服务操作接口
 *
 * @package         Default
 * @example
 * <pre>
 *     function get_content($commentid) {
 *         $client = new ResourceServiceClient('bbt');
 *         if (!isset($_SESSION['accesstoken'])) {
 *             $code = $client->oauth2->get_oauth_code('http://127.0.0.1/helper/callback.php');//callback.php会设置$_Session['accesstoken']=xxx
 *             $accesstoken = $auth->get_accesstoken('1234567');//对第三方应用, 这一步需要用户授权;对内置应用, 不需要用户授权
 *             if ($accesstoken == '') {
 *                 //header('Location:'. $auth->get_authorizeurl());
 *                 //exit();
 *                 return '';
 *             }
 *             $_SESSION['accesstoken'] = $accesstoken;
 *         }
 *         $client->set_accesstoken($_SESSION['accesstoken']);
 *         $client->set_host('127.0.0.1');
 *         return $client->comment->get_comment('xxxxx');
 *     }
 * </pre>
 */
class ResourceServiceClient
{
    /**
     * 客户端SDK版本
     */
    const VERSION = '1.0';

    /**
     * http协议头，一般携带校验信息
     * @var array
     */
    private $header = array(
        'User-Agent' => 'iFlyTEK/1.0 (PHP)',
        'Accept' => 'application/json; version=1.0',
        'APPKEY' => ''
    );

    /**
     * 获取的accesstoken
     * @var string
     */
    private $accesstoken = '';

    /**
     * 接口服务器地址
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * Auth类实例
     * @var object
     */
    private $auth_instance = null;

    /**
     * 接口类字典数组
     * @var array
     */
    private $ins = array();

    /**
     * 初始化
     *
     * @param  string        $appkey                应用注册时得到的appkey
     * @param  string        $mimetype              数据返回的格式,以minetype格式发送,支持:application/json, application/yaml, text/html,application/xml
     * @param  string        $version               判断是否是指定版本的SDK,防止使用错误的SDK
     */
    public function __construct($appkey, $mimetype = 'application/json', $version = '1.0')
    {
        if ($version != ResourceServiceClient::VERSION) {
            throw new Exception('It\'s not right ResourceServiceClient!');
        }
        $this->header['APPKEY'] = $appkey;
        $this->header['Accept'] = $mimetype .'; version='. ResourceServiceClient::VERSION;
    }

    /**
     * 设置accesstoken
     * @param string $accesstoken accesstoken
     */
    public function set_accesstoken($accesstoken) {
        $this->accesstoken = $accesstoken;
    }

    /**
     * 设置api所在服务器地址,默认是127.0.0.1
     *
     * @param string $host API所在的地址，格式为：[http(s)://]127.0.0.1[:80]/resourceservice
     * @return  void
     */
    public function set_host($host) {
      $this->host = $host;
    }

    /**
     * 魔法函数，生成不存在的属性
     * @param  string $property 属性名称
     * @return object           类实例
     */
    public function __get($property) {
        if (isset($this->$property)) return $this->$property;
        if (isset($this->ins[$property])) return $this->ins[$property];
        include_once(dirname(__FILE__).'/helper/api/'. $property .'.php');

        if ($property == 'oauth2') {
            $this->ins[$property] = new OAuth2($this->header['APPKEY']);
        } else {
            $_property = ucfirst($property);
            $this->ins[$property] = new $_property($this->header['APPKEY'], $this->accesstoken, $this->header);
            $this->ins[$property]->set_host($this->host);
        }
        return $this->ins[$property];
    }
}
?>