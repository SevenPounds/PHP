<?php
class IndexAction extends AccessAction {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $_GET['nav'] = isset($_GET['nav'])?$_GET['nav']:0;
        $this->assign('nav',$_GET['nav']);
        $this->setTitle( '在线答疑' );
        $this->display ("index_new");
    }

    public function follows() {
        $_GET['nav'] = isset($_GET['nav'])?$_GET['nav']:0;
        $this->assign('nav',$_GET['nav']);
        $this->setTitle( '在线答疑' );
        $this->display ();
    }

    /**
     * 问题详情页面
     */
    public function detail(){
        // 当前登录用户的id
        $uid = $this->mid;
        // 获取问题编号
        $qid = $_GET['qid'];

        //在线答疑统计
        $this->operationLog["actionName"]="onlinelistshow";
        $this->operationLog["remark"]="在线答疑";
        model("OperationLog")->addOperationLog($this->operationLog);

        // 实例化QuestionModel
        $Question = D('Question');
        //判断该问题是否已删除
        $isdel = $Question->getIsDelete($qid);
        if($isdel['isDeleted'] == 1){
            $this->error("该问题已删除！");
        }

        //计算评论数
        $Answer = D('Answer');
        $commentcount = $Answer->getCommentCount();

        // 问题详细信息
        $list = $Question->questionDetail($qid);
        $quid = $list['uid'];
        $quid != $uid && $Question->updateQuestionViewcount($qid);//非提出者浏览增加浏览次数

        //提问者信息
        $cyInfo = D("User")->getUserInfo($list['uid']);
        $orgInfo = D("CyUser")->getCyUserInfo($cyInfo['login']);
        if(isset($orgInfo['orglist']['school'])){
            foreach($orgInfo['orglist']['school'] AS $school){
                $cyInfo['orgName'] = $school['name'];
                break;
            }
        }
        $author = D('User')->getUserInfo($quid);
        $orglist = D('CyUser')->getCyUserInfo($author['login']);
        $list['locations'] = array();
        if(!empty($orglist['locations'])){
            foreach($orglist['locations'] as $v){
                array_push($list['locations'],$v['name']);
            }
        }
        $list['school'] = $orglist['orglist']['school'];
        // 将问题的年级和学科code转换成name
        $node = model('Node');
        //将年级和学科的code转换成name
        $list['grade'] = $node->getNameByCode('grade',$list['grade']);
        $list['subject'] = $node->getNameByCode('subject',$list['subject']);
        //获取AppTags---by zhaoliang 2014/1/16
        $tagresult = M("Tag")->setAppName("onlineanswer")->setAppTable("onlineanswer")->getAppTags(array($qid));
        $list['tags'] = $tagresult[$qid];
        $this->question = $list;
        $this->qid = $qid;
        $this->cyInfo=$cyInfo;
        $this->setTitle( '问题详情' );
        $this->status = $list['status'];
        $this->isself = $uid == $quid;//登录者为提问者
        recordVisitor($quid,$this->mid);
        // 如果问题已解答
        $answers = $Answer->answers($qid);
        $answers = $answers['data'];
        foreach($answers as &$answer){
            $answer = array_merge ( $answer, model ( 'Avatar' )->init ( $answer['uid'] )->getUserPhotoFromCyCore ($answer['uid'],"uid",C("ONLINEANSWER_APP_ID")) );
        }
        if(!empty($answers)){
            // 创建一个 存储所有回答id的数组
            $ansids = array();
            // 遍历回答数组
            foreach($answers as $value){
                array_push($ansids,$value['ansid']);
            }
            // 模板变量
            $this->agreeArray = D('Agree')->checkIsAgreed($ansids,$uid);
        }
        if($list['status'] == '1'){
            for($i=0;$i<count($answers);$i++){
                if($answers[$i]['is_best'] == '1'){
                    $this->bestanswer = $answers[$i];
                    unset($answers[$i]);
                }
            }
        }
        // 模板变量
        $this->answers = $answers;
        // 判断当前登录用户是否是投票发起者
        if($list['uid'] == $this->mid){
            $isCreator = TRUE;
        }else{
            $isCreator = FALSE;
        }
        $this->isCreator = $isCreator;
        $this->display("unsolved_new");
    }

    /**
     * 回答问题
     */
    public function answer(){
        // 回答的问题id
        $qid = $_POST['qid'];

        // 回答的内容
        $content = safe($_POST['content']);

        // 文语双显音频id
        $record_id = $_POST['record_id'];

        // 当前登录的用户
        $user = $this->user;

        // 把值存入$data数组
        $data = array();
        $data['qid'] = $qid;
        $data['content_origin'] = $content;
        //敏感词检测
        $resultData = $this->sensitiveWord_svc->checkSensitiveWord($content);
        $resultData = json_decode($resultData, true);
        if ($resultData["Code"] != 0) {
            return;
        }
        $data['content'] = $resultData["Data"];
        $data['record_id'] = $record_id;
        $data['uid'] = $user['uid'];
        $data['uname'] = $user['uname'];

        // 实例化AnswerModel
        $Answer = D('Answer');
        // 把回答的信息插入数据库
        $res = $Answer->insertAnswer($data);
        if($res){
            $AnswerInfo = D('Question')->questionDetail($qid);

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在在线答疑评论了“".$AnswerInfo['title_origin']."”的在线答疑！";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["zxdy"]["code"],C("opType")["reply"]['code'],$data["qid"],C("location")["localServer"]["code"],C("location")["panServer"]['code'],"",$AnswerInfo['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

            //在线答疑  评论消息 记录
            $conmment = $this->user['uname'].'评论了您发起的在线答疑“'.$AnswerInfo['title'].'”';
            $map =array(
                "userid" => $AnswerInfo['cyuid'], //$AnswerInfo['loginName']
                "type" => "4",
                "content" => $conmment,
                "url" => C("SPACE").'index.php?app=onlineanswer&mod=Index&act=detail&qid='.$qid
            ) ;
            $list = array($map);
            $message = json_encode($list);
            $data = array(
                "timestamp"=>time(),
                "appId" => C("ONLINEANSWER_APP_ID"),
                "message" => $message
            );
            Restful::sendMessage($data);


            echo("{status:'200',data:$res}");
        }else{
            echo("{status:'400',data:$res}");
        }
    }

    /**
     * 修改回答
     */
    public function alterAnswer(){
        // 回答的问题id
        $qid = $_POST['qid'];
        // 回答id
        $ansid = $_POST['ansid'];
        // 用户id
        $uid = $this->mid;
        // 回答的内容
        $content = safe($_POST['content']);
        // 把数据存入数组
        $data = array();
        $data['ansid'] = $ansid;
        $data['uid'] = $uid;
        $data['content'] = $content;
        $data['qid'] = $qid;

        // 实例化AnswerModel
        $Answer = D('Answer');
        $res = $Answer->alterAnswerContent($data);

        if($res !== false){

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在在线答疑修改了“".$data['title_origin']."”回答";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["zxdy"]["code"],C("opType")["update"]['code'],$data["qid"],C("location")["panServer"]['code'],"","",$data['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj,JSON_UNESCAPED_UNICODE),3,SITE_PATH.C("LOGRECORD_URL"));

            echo("{status:'200',data:$res}");
        }else{
            echo("{status:'400',data:$res}");
        }
    }

    /**
     * 显示创建问题页面
     */
    public function create() {
        $node = model('Node');
        $subjects = $node->subjects;
        $grades = $node->grades;
        array_shift($subjects);
        array_unshift ($subjects,array(name=>"学科"));
        array_shift($grades);
        array_unshift ($grades,array(name=>"年级"));
        $this->assign("subjects",$subjects);
        $this->assign("grades",$grades);
        $this->setTitle( '创建答疑' );
        $this->assign("roleEnName",$this->roleEnName);
        // 当前登录用户相关的名师工作室
        $loginName = $this->user['login'];
        $MSGroups = D("MSGroup","msgroup")->getMsGroupByLoginName($loginName);
        $this->MSGroups = $MSGroups;
        $this->display ();
    }

    /**
     * 把问题存入数据库
     */
    public function addQuestion() {
        // 创建一个数组
        $data = array ();
        // 把表单输入值存入$data数组
        $data ['uid'] = $_POST ['uid'];
        $data ['content'] = $_POST ['content'];

        $titleData = $this->sensitiveWord_svc->checkSensitiveWord(trim(htmlspecialchars($_POST ['title'])));
        $titleData = json_decode($titleData, true);
        if ($titleData["Code"] != 0) {
           return;
        }
        $data['title'] = $titleData["Data"];
        $data['title'] = trim(htmlspecialchars($_POST ['title']));
        $data['title_origin'] = trim(htmlspecialchars($_POST ['title']));

        $contentData = $this->sensitiveWord_svc->checkSensitiveWord($_POST ['content']);
        $contentData = json_decode($contentData, true);
        if ($contentData["Code"] != 0) {
            return;
        }
        $data['content'] = $contentData["Data"];
        $data['content_origin'] = $_POST ['content'];

        $data ['grade'] = $_POST ['grade'];
        $data ['subject'] = $_POST ['subject'];
        $data ['is_public'] = $_POST ['is_public'] == null? 1:$_POST ['is_public'] ;
        $data ['is_private'] = 0;
        $data ['to_space'] = intval($_POST['to_space']);
        $gidsArr = $_POST['gid'];
        $tagIds = empty($_POST['tag_ids']) ? array() : explode(',',$_POST['tag_ids']);
        // 实例化QuestionModel
        $Question = D('Question');
        // 插入数据库
        $r = $Question->insertQuestion ($data,$gidsArr);
        if ($r) {
        	$conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在在线答疑创建了“".$data['title_origin']."”的在线答疑";
        	$logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["zxdy"]["code"],C("opType")["create"]['code'],$r,C("location")["localServer"]["code"],C("location")["panServer"]['code'],"",$data['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
        	Log::writeLog(json_encode($logObj,JSON_UNESCAPED_UNICODE),3,SITE_PATH.C("LOGRECORD_URL"));
        	//20170116 改造性能问题，异步调用syncQuestionToFeed发表问题动态
			echo ("{status:'200',data:$r}");
		} else {
			echo ("{status:'400',data:$r}");
		}

    }
    
    /**
     * 发表问题动态
     */
    public function syncQuestionToFeed() {
    	$r = $_POST ['r'];
    	if ($r) {
			// 创建一个数组
			$data = array ();
			// 把表单输入值存入$data数组
			$data ['uid'] = $_POST ['uid'];
			$data ['content'] = $_POST ['content'];
			
			$titleData = $this->sensitiveWord_svc->checkSensitiveWord ( trim ( htmlspecialchars ( $_POST ['title'] ) ) );
			$titleData = json_decode ( $titleData, true );
			if ($titleData ["Code"] != 0) {
				return;
			}
			$data ['title'] = $titleData ["Data"];
			$data ['title'] = trim ( htmlspecialchars ( $_POST ['title'] ) );
			$data ['title_origin'] = trim ( htmlspecialchars ( $_POST ['title'] ) );
			
			$contentData = $this->sensitiveWord_svc->checkSensitiveWord ( $_POST ['content'] );
			$contentData = json_decode ( $contentData, true );
			if ($contentData ["Code"] != 0) {
				return;
			}
			$data ['content'] = $contentData ["Data"];
			$data ['content_origin'] = $_POST ['content'];
			
			$data ['grade'] = $_POST ['grade'];
			$data ['subject'] = $_POST ['subject'];
			$data ['is_public'] = $_POST ['is_public'] == null ? 1 : $_POST ['is_public'];
			$data ['is_private'] = 0;
			$data ['to_space'] = intval ( $_POST ['to_space'] );
			$gidsArr = $_POST ['gid'];
			$tagIds = empty ( $_POST ['tag_ids'] ) ? array () : explode ( ',', $_POST ['tag_ids'] );
			// 实例化QuestionModel
			$Question = D ( 'Question' );
			// 增加标签信息
			$tagobj = M ( "Tag" );
			$tagobj->setAppName ( "onlineanswer" );
			$tagobj->setAppTable ( "onlineanswer" );
			$tagobj->setAppTags ( $r, array (), 9, $tagIds );
			$feed = $Question->syncToFeed ( $data ['uid'], $r, $data ['title'], $data ['content'], $gidsArr );
			echo ("{status:" . $feed . "}");
		}else{
			echo ("{status:-1}");
		}
    }

    /**
     * 修改问题
     */
    public function alterQuestion(){
        // 问题id
        $qid = $_POST['qid'];

        // 用户id
        $uid = $this->mid;

        // 标题
        $title = $_POST['title'];

        $titleData = $this->sensitiveWord_svc->checkSensitiveWord($_POST ['title']);
        $titleData = json_decode($titleData, true);
        if ($titleData["Code"] != 0) {
            return;
        }
        $title = $titleData["Data"];
        
        $data['title_origin'] = $_POST['title'];

        $contentData = $this->sensitiveWord_svc->checkSensitiveWord($_POST ['content']);
        $contentData = json_decode($contentData, true);
        if ($contentData["Code"] != 0) {
            return;
        }
        $content = $contentData["Data"];
        $data['content'] = $_POST ['content'];
        $data['content_origin'] = $_POST ['content'];

        // 把数据存入数组
        $data = array();
        $data['qid'] = $qid;
        $data['uid'] = $uid;
        $data['content'] = $content;
        $data['content_origin'] = $_POST ['content'];
        $data['title'] = $title;
        $data['title_origin'] = $_POST['title'];
        $data ['to_space'] = $_POST['to_space'];
        $gidsArr = $_POST['gid'];
        $tagIds = empty($_POST['tag_ids']) ? array() : explode(',',$_POST['tag_ids']);
        $data['grade'] = $_POST['grade'];
        $data['subject'] = $_POST['subject'];
        // 实例化QuestionModel
        $Question = D('Question');
        $question = D('Question')->questionDetail($qid);
        $res = $Question->alterQuestionContent($data,$gidsArr);
        if($res !== false){
            //增加标签信息
            $tagobj = M("Tag");
            $tagobj->setAppName("onlineanswer");
            $tagobj->setAppTable("onlineanswer");
            $tagobj->setAppTags($qid,array(),9,$tagIds);

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在在线答疑修改了“".$question['title_origin']."”的在线答疑";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["zxdy"]["code"],C("opType")["update"]['code'],$data['qid'],C("location")["localServer"]["code"],"","",$question['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj,JSON_UNESCAPED_UNICODE),3,SITE_PATH.C("LOGRECORD_URL"));

            echo("{status:'200',data:$res}");
        }else{
            echo("{status:'400',data:$res}");
        }
    }

    /**
     * 采纳答案
     */
    public function adoptAnswer(){
        // 回答的问题id
        $qid = $_POST['qid'];

        // 实例化QuestionModel
        $Question = D('Question');
        $result = $Question->questionStatusAndUid($qid);
        if($result['uid'] != $this->mid){
            echo("{status:'400',data:'用户不正确!'}");
            return;
        }
        // 回答id
        $ansid = $_POST['ansid'];

        // 实例化AnswerModel
        $Answer = D('Answer');
        $res = $Answer->alterAnswerBset($ansid);
        if($res){
            $Question->alterQuestionStatus($qid);
            // 获取回答者id
            $noticedid = model('Answer')->where("ansid=$ansid")->field("uid,content")->find();
            // 发送消息通知提问者
            addMessage($this->mid, $noticedid['uid'], '',$noticedid['content'],$qid,'adoptAnswer');
            echo("{status:'200',data:$res}");
        }else{
            echo("{status:'400',data:$res}");
        }
    }

    /**
     * 答疑中心
     */
    public function center(){
        $this->st = isset($_GET['st'])?$_GET['st']:2;
        $node = model('Node');
        $this->subjects = $node->subjects;
        $this->grades = $node->grades;
        $this->status = $this->getStatusArray();
        $this->setTitle( '答疑中心' );
        $this->display("center_new");
    }

    private function getStatusArray(){
        $status = array();
        $status[] = array(code=>"", name=>"全部");
        $status[] = array(code=>"1", name=>"已解决");
        $status[] = array(code=>"0", name=>"未解决");
        return $status;
    }

    /**
     * 增加赞
     * @author xypan
     */
    public function addAgree(){
        $ansid = intval($_POST['ansid']);
        $res = model('Agree')->addAgree($ansid, $this->mid);
        if($res){
            echo("{status:'200',data:$res}");
        }else{
            echo("{status:'400',data:$res}");
        }
    }

    /**
     * 删除我的问题
     */
    public function delete() {
        $qid = intval($_POST['qid']);
        $uid = $this->mid;

        // 实例化NoticeModel
        $Question = D('Question');
        // 插入数据库
        $res = $Question->deleteQuestion($qid,$uid);
        if($res){
            echo("{status:'200',data:$res}");
        }else{
            echo("{status:'400',data:$res}");
        }
    }

    /**
     * 把问题列表中的学科、年级从code变成name
     * @param array $list 问题列表
     * @return array 新的问题列表
     */
    public function codeToName($list){
        $node = model('Node');
        foreach ($list as &$value){
            $value['grade'] = $node->getNameByCode('grade',$value['grade']);
            $value['subject'] = $node->getNameByCode('subject',$value['subject']);
        }
        return $list;
    }
    /**
     * 问题补充页面
     */
    public function addMore(){
        // 当前登录用户的id
        $uid = $this->mid;
        // 获取问题编号
        $qid = $_GET['qid'];

        // 实例化QuestionModel
        $Question = D('Question');
        //判断该问题是否已删除
        $isdel = $Question->getIsDelete($qid);
        if($isdel['isDeleted'] == 1){
            $this->error("该问题已删除！");
        }

        // 实例化AnswerModel
        $Answer = D('Answer');
        // 查询出该问题的所有回答列表
        $answers = $Answer->answers($qid);
        foreach($answers as &$answer){
            $answer = array_merge ( $answer, model ( 'Avatar' )->init ( $answer['uid'] )->getUserPhotoFromCyCore ($answer['uid'],"uid",C("ONLINEANSWER_APP_ID")) );
        }
        //计算评论数
        $commentcount = $Answer->getCommentCount();

        // 模板变量
        $this->answers = $answers;
        // 如果回答列表不为空
        if(!empty($answers)){
            // 创建一个 存储所有回答id的数组
            $ansids = array();
            // 遍历回答数组
            foreach($answers as $value){
                array_push($ansids,$value['ansid']);
            }
            // 实例化AgreeModel
            $Agree = D('Agree');
            // 模板变量
            $this->agreeArray = $Agree->checkIsAgreed($ansids,$uid);
        }
        // 查出问题的提出者和当前状态
        $questionStatusAndUid = $Question->questionStatusAndUid($qid);
        // 判断当前的登录用户是否是提问者
        if($uid != $questionStatusAndUid['uid']){
            //当前用户不是提问者,浏览次数加1
            $Question->updateQuestionViewcount($qid);
        }
        // 问题详细信息
        $list = $Question->questionDetail($qid);
        $quid = $list['uid'];
        $author = D('User')->getUserInfo($quid);
        $orglist = D('CyUser')->getCyUserInfo($author['login']);
        $list['locations'] = array();
        if(!empty($orglist['locations'])){
            foreach($orglist['locations'] as $v){
                array_push($list['locations'],$v['name']);
            }
        }
        $list['school'] = $orglist['orglist']['school'];

        // 如果问题已解答
        if($questionStatusAndUid['status'] == '1'){
            if($uid==$questionStatusAndUid['uid']){
                $a = 1;
            }else{
                $a = 0;
            }
            $this->assign('questionuid',$a);
            // 跳转到已解答页面
            $this->display("unsolved");
            //结束程序
            return;
        }

        if($uid == $questionStatusAndUid['uid']){

        }else{
            // 当前登录用户不是提问者，默认没有回答过该问题
            $flag = true;
            for ($i = 0 ; $i < count($answers); $i++){
                if($uid == $answers[$i]['uid']){
                    // 当前登录用户已经回答过该问题
                    $flag = false;
                }
            }
            $this->flag = $flag;
        }

        // 学科年级数据
        $node = model('Node');
        $subjects = $node->subjects;
        $grades = $node->grades;
        array_shift($subjects);
        array_unshift ($subjects,array(name=>"学科"));
        array_shift($grades);
        array_unshift ($grades,array(name=>"年级"));
        $this->assign("subjects",$subjects);
        $this->assign("grades",$grades);
        $this->to_space = $list['to_space'];
        $this->assign("roleEnName",$this->roleEnName);
        // 当前登录用户相关的名师工作室
        $loginName = $this->user['login'];
        $MSGroups = D("MSGroup","msgroup")->getMsGroupByLoginName($loginName);
        $this->MSGroups = $MSGroups;
        $existgids = D('MSGroupTeachingApp','msgroup')->where(array('app_type'=>'onlineanswer_question','appid'=>$qid))->field('gid')->findAll();
        $this->existgids = getSubByKey($existgids,'gid');
        // 答疑详细数据
        $this->assign('question', $list);
        $this->assign('qid', $qid);

        $this->setTitle( '编辑提问' );
        $this->display();
    }
    /**
     * 弹出编辑答案页面
     */
    public function showUpdate(){
        $qid = $_REQUEST['qid'];
        $aid = $_REQUEST['aid'];
        $this->ansid = $aid;
        $this->qid = $qid;
        $answer = D("Answer")->answers($qid);
        $this->content = $answer[0]['content'];
        $this->display();
    }
}
?>
