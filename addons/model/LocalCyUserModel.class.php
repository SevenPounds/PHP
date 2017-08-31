<?php
/**
 * 本地用户中心用户数据 - 数据对象模型
 * @author mingtao 
 */
class LocalCyUserModel extends Model {
    protected $tablePrefix  =   'uc_';
    protected $tableName = 'user';
    /**
     * 根据身份证号获取用户名（判断是否存在）
     */
	public function getUser($userIdcards){
		if(empty($userIdcards)){
			return array();
		}
		$condition['method'] = 'user.check.idcards';
		$condition['idCards'] = $userIdcards;
		$str_params = '';
		foreach ($condition as $key => $value) {
			$str_params .= "&$key=".$value;
		}
		$result = $this->getResByUrl(C('ESCHOOL')."api.php?".$str_params);
		return json_decode($result,true);
	}
	
	/**
	 * 获取绑定后台的账号
	 * @param unknown $userId
	 * @return multitype:|Ambigous <mixed, boolean, multitype:, multitype:multitype: >
	 */
	public function getBindAdminByUser($userId){
		if(empty($userId)){
			return array();
		}
		$condition['method'] = 'user.get.bindadmin';
		$condition['userId'] = $userId;
		$str_params = '';
		foreach ($condition as $key => $value) {
			$str_params .= "&$key=".$value;
		}
		$result = $this->getResByUrl(C('ESCHOOL')."api.php?".$str_params);
		return json_decode($result,true);
	}
	
	
	private function getResByUrl($url){
		$ch = curl_init();
		$timeout = 5;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch); 
		//$file_contents = file_get_contents($url);
		return $file_contents;
	}
	
}