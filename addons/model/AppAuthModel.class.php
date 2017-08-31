<?php
/**
 * 应用授权模型
 * 
 * 模拟OAuth授权认证协议
 * 
 * 通过发放authcode给第三方服务接口，第三方服务接口通过authcode获取token
 * 验证成功后第三方请求需要带上token验证有效性
 * 32位authcode 与 32位token 一一对应，且与用户相关联
 * @author yuliu2@iflytek.com
 */
class AppAuthModel extends Model {

	protected $tableName = 'app_auth';
	
	const AUTHCODE_MINUTE = 5;//authcode 失效时间,单位分钟
	
	const TOKEN_MINUTE = 300;//token 失效时间，单位分钟
	
	const AUTHCODE_KEY = 'FJDabcasd2329fasdjk230*&^%$^';//28 个加密 salt字符
	
	const TOKEN_KEY = '5FDS!4fas23#@fsad-2faskjdf-2';//28 个加密 salt字符
	
	const TOKEN_CACHE_PREFIX = 'TOKEN_';
	
	const AUTHCODE_CACHE_PREFIX = 'AUTHCODE_';
		
	/**
	 * 获取authcode
	 * 配对的token会被缓存直到失效
	 * @param string $appName 应用name
	 * @param string $loginName 用户名
	 * @return string $authcode 授权码
	 */
	public function getAuthcode($appName, $loginName){
		
		$key = self::AUTHCODE_CACHE_PREFIX . $appName . $loginName;
		$value = S($key);
		if(!empty($value)){
			return $value;
		}
		
		$now = time();
		$authcode = md5(self::AUTHCODE_KEY . $now . $appName . $loginName);
		$token = md5(self::TOKEN_KEY . $now . $appName . $loginName);
		
		//authcode缓存
		S($key, $authcode, self::AUTHCODE_MINUTE * 60);
		
		//token加密缓存，cache expire = token expire
		$key = self::TOKEN_CACHE_PREFIX . $appName . $loginName;
		$safeToken = desencrypt($token, self::TOKEN_KEY);//DES加密
		
		S($key, $safeToken, self::TOKEN_MINUTE * 60);
		
		$data = array('login_name' => $loginName,
				'app' => $appName, 
				'authcode' => $authcode, 
				'token' => $token, 
				'ctime' => date('Y-m-d H:i:s'));
		
		$result = $this->add($data);
		return $result ? $authcode : '';
	}
	
	/**
	 * 获取缓存中的token
	 * @param string $appName 应用name
	 * @param string $loginName 用户名
	 * @return string $token 令牌
	 */
	public function getCacheToken($appName, $loginName){
		$key = self::TOKEN_CACHE_PREFIX . $appName . $loginName;
		$value = S($key);
		if(!empty($value)){
			return desdecrypt($value, self::TOKEN_KEY);//解密
		}else{
			//缓存不存在，则去数据库查
			$offset = '-' . self::AUTHCODE_MINUTE . ' minute';
			$time = date('Y-m-d H:i:s',strtotime($offset));
			$authInfo = $this->where(array('app' => $appName, 'login_name' => $loginName, 'ctime' => array('GT', $time)))->find();
			return $authInfo['token'];
		}
	}
	
	/**
	 * 通过授权码获取Token
	 * @param string $appName 应用name
	 * @param string $authcode 授权码
	 * @return string $token 令牌
	 */
	public function getToken($appName, $authcode){
		$offset = '-' . self::AUTHCODE_MINUTE . ' minute';
		$time = date('Y-m-d H:i:s',strtotime($offset));
		$authInfo = $this->where(array('app' => $appName, 'authcode' => $authcode, 'ctime' => array('GT', $time)))->find();
		return $authInfo['token'];
	}
	
	/**
	 * 通过token获取用户
	 * @param string $appName 应用name
	 * @param string $token 令牌
	 * @return array $cyuser 用户信息
	 */
	public function getUser($appName, $token){
		$offset = '-' . self::TOKEN_MINUTE . ' minute';
		$time = date('Y-m-d H:i:s',strtotime($offset));
		$authInfo = $this->where(array('app' => $appName, 'token' => $token, 'ctime' => array('GT', $time)))->find();
		$cyuser = array();
		if(!empty($authInfo)){
			$cyuser = model('CyUser')->getCyUserInfo($authInfo['login_name']);
		}
		return $cyuser;
	}
	
	/**
	 * 验证令牌是否有效
	 * @param string $appName 应用name
	 * @param string $token 令牌
	 * @return bool true|false 有效|无效
	 */
	public function validateToken($appName, $token){
		$offset = '-' . self::TOKEN_MINUTE . ' minute';
		$time = date('Y-m-d H:i:s',strtotime($offset));
		$token = $this->where(array('app' => $appName, 'token' => $token, 'ctime' => array('GT', $time)))->find();
		return !empty($token);
	}
}
