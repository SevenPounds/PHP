<?php
class UserLoginRoleModel extends Model{
	
	protected $connection = "SNS_DB";
	protected $tablePrefix      =   'ts_';
	protected $tableName        =   'user_login_role';
	
// 	public function __construct($name='',$tablePrefix='',$connection='') {
// 		$this->db(0,$this->connection,true);
// 	}
    
	/**
	 * 根据登录名获取当前用户所使用的角色
	 * @param string $loginName
	 * @param array $roles  eg:array('teacher','student')
	 */
    public function getUserCurrentRole($loginName='', $roles){ 
    	if(!isset($loginName) || !isset($roles)){
    		return null;
    	}
    	$roleCount = count($roles);
    	$roleEnName = null;
    	if($roleCount > 0){
    		if($roleCount > 1){
    			//多角色，从后台数据库取当前切换的角色    			
    			$roleEnName = $this->getCurrentRoleByLoginName($loginName);
    		}
    		if(!isset($roleEnName)){
    			//只有一个角色或多个角色时未登录过
    			if( is_array($roles[0])){
    				if(isset($roles[0]['name'])){
    					$roleEnName = $roles[0]['name'];
    				}else if(isset($roles[0]['enName'])){
    					$roleEnName = $roles[0]['enName'];
    				}
    			}else if(isset($roles[0]->enName)){
    				$roleEnName = $roles[0]->enName;
    			}else if(isset($roles[0]->name)){
    				$roleEnName = $roles[0]->name;
    			}
    		}
    	}else{
    		//无角色，$roleEnName为null
    	}
    	return $roleEnName;
    }

    public function getCurrentRoleByLoginName($loginName){
        header("Content-type: text/html; charset=utf-8");
        $record = $this->query("select login_role from ts_user_login_role where login_name = '{$loginName}'");
        if(!empty($record)){
        	return $record[0]["login_role"];
        }
        return null;
    }

    public function updateCurrentRoleByLoginName($loginName,$roleName){
    	if(!isset($loginName) || !isset($roleName)){
    		return false;
    	}
        header("Content-type: text/html; charset=utf-8");
        $record = $this->where("login_name = '{$loginName}'")->select();        
        $state = 0;
        if(!empty($record)){
            $state = $this->execute("update ts_user_login_role set login_role = '{$roleName}' where login_name = '{$loginName}'");    
        }else{
            $state = $this->execute("INSERT INTO ts_user_login_role (login_name,login_role)VALUES ('{$loginName}','{$roleName}')");
        }
        if($state>0){
            return true;
        }
        return false;
    }
    
    public function getCNRoleName($roleName){
    	$roles =array(
            'instructor'=>'教研员',
            'teacher'=>'老师',
            'student'=>'学生',
            'parent'=>'家长',
            'province'=>'省级教研员',
            'city'=>'市级教研员',
            'district'=>'区县级教研员',
            'eduadmin' =>'教育机构管理者',
            'member'=>'普通用户',
            'teammember'=>'团队成员',
            'edupersonnel'=>'机构用户',
            'ledcsuperadmin'=>'超级管理员',
            'deptLeader'=>'系统管理员',
            'ledcschoolMng'=>'学校管理员',
            'ledcdistrictMng'=>'区县管理员',
            'ledccityMng'=>'市级管理员',
            'ledcprovinceMng'=>'省级管理员',
    	);
    	return $roles[$roleName];
    }
    
}