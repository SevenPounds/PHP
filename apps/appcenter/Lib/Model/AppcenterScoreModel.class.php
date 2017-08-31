<?php
/**
 * 应用中心-评分记录模型
 * @author xmsheng
 */
class AppcenterScoreModel extends Model{
	protected $tableName = 'appcenter_score';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'login',
			2 => 'appid',
			3 => 'ctime'
	);
	
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	
    /**
     * 评分
     * @param array $record=>('login'=>'','appid'=>'','score'=>'');
      *2014-9-28
     */
    public function addScoreRecord($record){
  	 if(empty($record['login'])||empty($record['appid'])){
  	 	return array('status'=>'500','message'=>'参数错误');
  	 }  	   	 
  	 $data=array('login'=>$record['login'],'appid'=>$record['appid']);
  	 $result=$this->where($data)->select();
  	 if(count($result)>0){
  	 	return array('status'=>'500','message'=>'资源已评分');
  	 }
     unset($record);
     $data['ctime']=date('Y-m-d H:i:s',time());
     $result= $this->add($data);
     return array('status'=>'200','message'=>'评分成功','result'=>$result);
    }
    
    /**
     * 获取app的评论记录数
     * @param 应用id $appid
      *2014-9-28
     */
    public function getCountByApp($appid){
    	if(empty($appid)){
    		return array('status'=>'500','message'=>'参数错误');
    	}
    	return $this->where(' appid = '.$appid)->count();
    }
    
    /**
     * 判断用户是否对该应用评论过
     * @param  $appid
     * @param  $login
     * @return 以评论返回true 否者false
      *2014-9-29
     */
    public function hasScore($appid,$login){
    	$data['appid']=$appid;
    	$data['login']=$login;
    	$count=$this->where($data)->count();
    	if($count>0){
    		return true;
    	}else{
    		return false;
    	}
    }
}
?>