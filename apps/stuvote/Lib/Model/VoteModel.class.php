<?php
class VoteModel extends BaseModel{
    // 表名
    protected $tableName = 'stuvote';

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * addVote
     * 添加投票
     * @param mixed $data
     * @param mixed $opt
     * @param mixed $tags 标签id数组
     * @param array $gids 发布到名师工作室的id数组
     * @access public
     * @return void
     */
    public function addVote($data, $opt, $tags, $gids){
        $deadline = $data['deadline'];
        $invitees = $data['invitees'];//被邀请者id
        unset($data['invitees']);

        if($deadline < time()) {
            throw new ThinkException('投票截止时间不能早于发起投票的时间！');
        }
        //检测选项是否重复
        $opt_test = array_filter($opt);
        foreach($opt as $value){
            if(get_str_length($value) >200){
                throw new ThinkException("投票选项不能超过200个字符");
            }
        }

        $opt_test_count = count(array_unique( $opt_test ));
        if( $opt_test_count < count($opt_test) ) throw new ThinkException( '投票不允许有重复项' );

        $vote_id = $this->add($data);
		
        if ($vote_id) {
			//增加标签信息
			$tagobj = M("Tag");
			$tagobj->setAppName('vote');
			$tagobj->setAppTable('vote');
			$tagobj->setAppTags($vote_id, array(), 9, $tags);
        	
        	//add by yxxing 产生网络投票动态
        	$viewUrl = U("stuvote/Index/detail",array("id"=>$vote_id));

            //同步到名师工作室
            D('MSGroupTeachingApp','msgroup')->addTeachingAppInfo($gids,$vote_id,'vote');
			$this->_syncToFeed($data['uid'], $viewUrl, $data['title'], $data['explain'], $gids,$data['to_space']);
        	
            //添加用户的常用用户
            D('UserFavorite')->inviteUser($data['uid'], $invitees, 'vote');
            //邀请他人，参与投票
            D( "VoteUser" )->inviteUser($invitees,$vote_id);

			//发送消息
			if($invitees){
				foreach($invitees as $k=>$v){
					addVoteMessage($data['uid'],$v,$data['title'],$vote_id,'vote');
				}
			}
			
				
            //选项表
            $optDao = D("VoteOpt");
            foreach($_POST["opt"] as $v) {
               // if(!$v) continue;
                $data["vote_id"]    =    $vote_id;
                $data["name"]       =    t($v);
                $add = $optDao->add($data);
            }
        }
        return $vote_id;

    }

    /**
     * getVoteList
     * 通过userId获取到用户列表
     * @param array|string|int $userId
     * @param array|object $options 查询参数
     * @access public
     * @return object|array
     */
    public function getVoteList($map = null,$field=null,$order = null,$limit = 20) {
        //处理where条件
        $map = $this->merge( $map );
        //连贯查询.获得数据集
        $result  = $this->where( $map )->field( $field )->order( $order )->findPage($limit) ;
        return $result;
    }

    /**
     * 获取指定条数的投票列表
     * @param int $limit 取前多少条记录
     * @return type 类型
     * 1：我发起的
     * 2：我参与的
     */
    public function getVotesBycondition($type=0,$condition,$order = 'id DESC',$limit = 20){
        switch ($type) {
            case 1:
                if(!empty($condition['keyword'])){
                    $condition['title'] = array('like','%'.$value.'%');
                    unset($condition['keyword']);
                }
                return $this->where($condition)->order($order)->findPage($limit);
                break;
            case 2:
                $map=array();
                $uid = $condition['uid'];
                unset($condition['uid']);
                foreach ($condition as $key => $value) {
                    if($key!='keyword'){
                        $map['v.'.$key] = $value;
                    }
                    else{
                        $map['v.title'] = array('like','%'.$value.'%');
                    }
                }
                $map['vu.uid']=$uid;

                $join=' LEFT JOIN '.$this->tablePrefix.'stuvote_user vu ON vu.vote_id = v.id';
                $tables = $this->tablePrefix.$this->tableName.' v ';
				$fields = array('v.*,vu.is_new');
                return $this->field($fields)->table($tables)->where($map)->join($join)->order('v.'.$order)->findPage($limit);
                break;
            default:
                break;
        }
    }

    /**
     * 根据条件获取网络投票列表
     * 注：供后台使用
     * @author ylzhao
     */
    public function getVotes($conditions, $page=1, $limit=10, $order){
    	$map = array();
    	if(isset($conditions['isHot'])){
    		$map['isHot'] = $conditions['isHot'];
    	}
    	if(isset($conditions['accessType'])){
    		$map['accessType'] = $conditions['accessType'];
    	}
    	if(isset($conditions['title'])){
    		$map['title'] = array('like', "%".$conditions['title']."%");
    	}
    	$fields = 'v.*,u.`uname`';
    	$tables = $this->tablePrefix.$this->tableName.' v ';
    	$join = 'LEFT JOIN '.$this->tablePrefix.'user u ON v.`uid` = u.`uid`';
    	$order = empty($order) ? "cTime DESC" : $order;
    	$start = ($page - 1) * $limit;
    	$result['data'] = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->limit("$start, $limit")->select();
    	$result['count'] = $total = $this->table($tables)->where($map)->join($join)->count();
    	return $result;
    }
    
    /**
     * 投票中心，查询操作
     * 
     * 最新投票：$order = 'cTime DESC'
     * 最热投票：$order = 'vote_num DESC'
     *
     */
    public function voteCenterSearch($condition,$order = 'cTime DESC',$limit = 20){
        if(!empty($condition['tagid'])){
                $map=array();
                foreach ($condition as $key => $value) {
                    if($key=='keyword'){
                        $map['v.title'] = array('like','%'.$value.'%');
                    }
                    if($key=='tagid'){
                        $map['at.app'] = 'vote';
                        $map['at.table'] = 'vote';
                        $map['at.tag_id'] = $value;

                    }
                    else{
                        $map['v.'.$key] = $value;
                    }
                }

                $join=' LEFT JOIN '.$this->tablePrefix.'app_tag at ON v.id = at.row_id';
                $tables = $this->tablePrefix.$this->tableName.' v ';
                $fields = array('v.*');
                return $this->field($fields)->table($tables)->where($map)->join($join)->order('v.'.$order)->findPage($limit);
                break;
        }
        else{
            if(!empty($condition['keyword'])){
                $condition['title'] = array('like','%'.$value.'%');
                unset($condition['keyword']);
            }
            return $this->where($condition)->order($order)->findPage($limit);
        }
    }

    public function merge ( $map = null ){
        if( isset( $map ) ){
            $map = array_merge( $this->data,$map );
        }else{
            $map = $this->data;
        }
        if(!empty($map['keyword'])){
            $map['title'] = array('like','%'.$value.'%');
            unset($map['keyword']);
        }

        return $map;
    }

    public function doDeleteVote($id){
        $voteUser        = D( 'VoteUser' );
        $voteOpt         = D( 'VoteOpt' );

        $map2['vote_id'] = $map1['id'] = $id;


        //删除投票
        $result1 = $this->where( $map1 )->delete();

        //删除投票选项库
        $result2 = $voteOpt->where( $map2 )->delete();

        //删除投票参与人员库
        $result3 = $voteUser->where( $map2 )->delete();

        if( $result1){
            return true;
        }else{
            return false;
        }

    }

         /**
         * DateToTimeStemp
         * 时间换算成时间戳返回
         * @param mixed $stime
         * @param mixed $etime
         * @access public
         * @return void
         */
        public function DateToTimeStemp( $stime,$etime ) {
            $stime = strval( $stime );
            $etime = strval( $etime );

           //如果输入时间是YYMMDD格式。直接换算成时间戳
            if( isset( $stime[7] ) && isset( $etime[7] ) ){
                //开始时间
                $syear  = substr( $stime,0,4 );
                $smonth = substr( $stime,4,2 );
                $sday   = substr( $stime,6,2 );
                $stime  = mktime( 0, 0, 0, $smonth,$sday,$syear );

                //结束时间
                $eyear  = substr( $etime,0,4 );
                $emonth = substr( $etime,4,2 );
                $eday   = substr( $etime,6,2 );
                $etime  = mktime( 0, 0, 0, $emonth,$eday,$eyear );

                return array( 'between',array( $stime,$etime ) );
            }

            //如果输入时间是YYYYMM格式
            $start_temp   = $this->paramData( $stime );
            $end_temp     = $this->paramData( $etime );
            $start        = $start_temp[0];
            $end          = $end_temp[1];

            return array( 'between',array( $start,$end ) );
        }


        /**
         * paramData
         * 处理归档查询的时间格式
         * @param string $findTime 200903这样格式的参数
         * @static
         * @access protected
         * @return void
         */
        protected function paramData( $findTime ){
            //处理年份
            $year = $findTime[0].$findTime[1].$findTime[2].$findTime[3];
            //处理月份
            $month_temp = explode( $year,$findTime);
            $month = $month_temp[1];
            //归档查询
            if ( !empty( $month ) ){

                //判断时间.处理结束日期
                switch (true) {
                    case ( in_array( $month,array( 1,3,5,7,8,10,12 ) ) ):
                        $day = 31;
                        break;
                    case ( 2 == $month ):
                        if( 0 != $year % 4 ){
                            $day = 28;
                        }else{
                            $day = 29;
                        }
                        break;
                    default:
                        $day = 30;
                        break;
                }
                //被查询区段开始时期的时间戳
                $start = mktime( 0, 0, 0 ,$month,1,$year  );

                //被查询区段的结束时期时间戳
                $end   = mktime( 24, 0, 0 ,$month,$day,$year  );

                //反之,某一年的归档
            }elseif( isset( $year[4] ) ){
                $start = mktime( 0, 0, 0, 1, 1, $year );
                $end = mktime( 24, 0, 0, 12,31, $year  );
            }else{
                //其它操作
            }

            //fd( array( friendlyDate($start),friendlyDate($end) ) );
            return array( $start,$end );

        }

        /**
         * doIsHot
         * 设置推荐
         * @param mixed $model
         * @access protected
         * @return void
         */
		 public function doIsHot( $map,$act ) {
			if( empty($map) ) {
				throw new ThinkException( "不允许空条件操作数据库" );
			}
			switch( $act ) {
				case "recommend":   //推荐
					$field = array( 'isHot','rTime' );
					$val = array( 1,time() );
					$result = $this->setField( $field,$val,$map );
					break;
				case "togreat":   //精华
					$field = array( 'isHot','rTime' );
					$val = array( 2,time() );
					$result = $this->setField( $field,$val,$map );
					break;
				case "cancel":   //取消
					$field = array( 'isHot','rTime' );
					$val = array( 0,0 );
					$result = $this->setField( $field,$val,$map );
					break;

			}
			return $result;
		 }
        /**
         * getConfig
         * 获取配置
         * @param mixed $index
         * @access public
         * @return void
         */
/*        public function getConfig( $index ){
            $config = $this->config->$index;
            return $config;
        }*/
		 
		 /**
		  * 根据isHot,评论数，浏览量和时间排序最火网络投票
		  * return array
		  */
		 public function geAppHotVote(){
		 	//网络投票列表
		 	$hot_list =$this->field("id,uid,title,`explain`,commentCount")->where(array('isHot'=>1,'accessType'=>0))->order('commentCount desc, vote_num desc, cTime desc ')->limit("0,5")->findAll();
		 	$result =array();
		 	foreach ($hot_list as $key){
		 		$map=array();
			 	$map['row_id']=$key['id'];
	        	$map['app']='vote';
	        	$map['table'] ='vote';
	        	$map['to_uid'] =0;
	        	$map['is_del']=0;
	        	//网络投票精彩评论获取
	        	$comment =D('Comment')->where($map)->order('digg_count desc, comment_count desc, ctime desc')->find();
	        	//网络投票发表者信息
	        	$blogUser =D('User')->getUserInfo($key['uid']);
			 	if(!empty($comment)){
			 		//网络投票评论者信息
		 			$commentUser =D('User')->getUserInfo($comment['uid']);
		 		}
		 		$blogInfo['hotUser']=$blogUser;
		 		$blogInfo['commentUser']=$commentUser;
		 		$blogInfo['id']=$key['id'];
		 		$blogInfo['content'] =filter_tag($key['explain']);
		 		$blogInfo['title']=filter_tag($key['title']);
		 		$blogInfo['discuss_count']=$key['commentCount'];
		 		$blogInfo['comment_content']=parse_html($comment['content']);
		 		$blogInfo['comment_count']=filter_tag($comment['digg_count']);
		 		$blogInfo['source_url']=U('vote/Index/detail',array('id'=>$key['id']));
		 		unset($commentUser);
		 		unset($comment);
		 		array_push($result,$blogInfo);
		 	}
		 	return $result;
		 }
		 

		 /**
		  * 发表网络投票动态
		  * @param int $uid 用户id
		  * @param string $viewUrl 预览地址
		  * @param int $title 标题
		  * @param int $content 内容
		  * @param array $gids 名师工作室ID
		  * @param int $toSpace 是否同步到我的工作室
          *
		  * @author yxxing
		  */
		 private function _syncToFeed($uid, $viewUrl, $title, $content, $gids, $toSpace=1) {
		 
		 	$d['content'] = '';
		 	$d['source_url'] = $viewUrl;
             $d['title'] = $title;
            //if(intval($toSpace) == 1){ 发布到工作室与否都发动态
		 		$d['body'] = '我发起了在线投票【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
		 		$feed = model('Feed')->put($uid, 'stuvote', "stuvote", $d);
		 	//}
             if(!empty($gids)){
                foreach ($gids as $gid) {
                    if(!empty($gid)){
        		 		$d['gid'] = $gid;
        		 		$d['body'] = "@" . $GLOBALS['ts']['user']['uname'] . ' 我发起了在线投票【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
        		 		$feed = model('Feed')->put($uid, 'msgroup', "msgroup", $d);
                    }
                }
		 	}
		 }
}
?>
