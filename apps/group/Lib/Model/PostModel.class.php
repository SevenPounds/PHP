<?php

class PostModel extends Model {
	var $tableName = 'group_post';
	protected $fields = array(0=>'id',1=>'gid',2=>'uid',3=>'tid',4=>'content',5=>'content_origin',6=>'ip',7=>'istopic',
		8=>'ctime',9=>'status',10=>'quote',11=>'is_del',12=>'attach',13=>'is_pub');
	 // 获取文件
	  /**
      * getGroupList 
    
     */
     public function getPostList($html=1,$map = null,$fields=null,$order = null,$limit = null,$isDel=0) {
            //处理where条件
            if(!$isDel)$map[] = 'is_del=0';
            else $map[] = 'is_del=1';
            
            $map[] = 'istopic=0';
   			$map = implode(' AND ',$map);
            //连贯查询.获得数据集
            $result         = $this->where( $map )->field( $fields )->order( $order )->findPage($limit) ;
          
            if($html) return $result;
            return $result['data'];

     }
     
     // 回收站
     function remove($id){
     	$id = is_array($id) ? '('.implode(',',$id).')' : '('.$id.')';  //判读是不是数组回收
     	$uids = D('Post')->field('uid')->where('id IN' . $id)->findAll();
     	$res  = D('Post')->setField('is_del', 1, 'id IN' . $id); //回复
     	if ($res) {
     		// 积分
     		foreach ($uids as $vo) {
     			X('Credit')->setUserCredit($vo['uid'], 'group_reply_topic', -1);
     		}
     	}
     	return $res;
     }
     
      // 删除
     function del($id) {
     	$id = in_array($id) ? '('.implode(',',$id).')' : '('.$id.')';  //判读是不是数组回收
     	return D('Post')->where('id IN'.$id)->delete(); //删除回复
     }
}

?>