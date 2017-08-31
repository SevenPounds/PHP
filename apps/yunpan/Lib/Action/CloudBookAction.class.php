<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 14-4-12
 * Time: 下午1:39
 * Description: 备课本操作
 */

class CloudBookAction extends  BaseCloudAction{

    /**
     * 备课本首页
     */
    public function index(){
        $this->display();
    }

    /**
     * 备课本详细
     */
    public function detail(){
        $this->display();
    }

    /**
     * 课程详细
     */
    public function lesson(){
        $this->display();
    }

    /**
     * 创建备课本信息
     */
    public function create(){
        $bkbInfo = array();
        $return = array();
        
        $condition['stage'] = $_REQUEST['stageCode'] ? trim($_REQUEST['stageCode']) : "";
        $condition['subject'] = $_REQUEST['subjectCode'] ? trim($_REQUEST['subjectCode']) : "";
        $condition['edition'] = $_REQUEST['editionCode'] ? trim($_REQUEST['editionCode']) : "";
        $condition['phase']=$_REQUEST['phaseCode'] ? trim($_REQUEST['phaseCode']) : "";
        $book = getBookTreenodes($condition , 'book');
        if(empty($book)){
            $return['status'] = 0;
            $return['info'] = '暂无此备课本';
            exit(json_encode($return));
        }
        //TODO获取我的备课本ID
        $bkdirId = $_REQUEST['bkdirId'] ? trim($_REQUEST['bkdirId']) : $this->bkDirId;
        $bkbInfo['bookID'] = $book[0]->id;
        $bkbInfo['book'] = $book[0]->name;

        //TODO 验证创建书本信息  返回创建书本信息
        $res = D('Beikeben')->createBeikeben($this->cymid, $bkdirId, $bkbInfo);
        if ($res['statuscode'] == 1) {
            $return['status'] = 1;
            $return['info'] = "备课本创建完成";
            $return['data'] = array();
        } else {
            $return['status'] = 0;
            $return['info'] = $res['data'];
        }

        exit(json_encode($return));
    }

    /**
     * 获取创建备课本信息
     */
    public function select(){
        $this->assign('phase',array_slice (D('Node')->getXueduans(),1));
        $this->display();
    }
    /**
     * 获取学科信息
     */
    public function subject(){
        $phase = $_REQUEST['phase'] ? trim($_REQUEST['phase']):'';
        $condition =  array();
        $phase &&  $condition['phase'] = $phase;
        $nodeName = 'subject';
        $return  = array();
        $return['status'] = 1;
        $return['data'] = getBookTreenodes($condition , $nodeName);
        exit(json_encode($return));
    }
    
    /**
     * 获取年级信息
     */
    public function stage(){
    	$phase = $_REQUEST['phase'] ? trim($_REQUEST['phase']):'';
        $subject = $_REQUEST['subject'] ? trim($_REQUEST['subject']):'';
        $condition =  array();
        $phase &&  $condition['phase'] = $phase;
        $subject &&  $condition['subject'] = $subject;
        $nodeName = 'stage';
        $return  = array();
        $return['status'] = 1;
        $return['data'] = getBookTreenodes($condition , $nodeName);
        exit(json_encode($return));
    }
    
    public function edition(){
    	$phase = $_REQUEST['phase'] ? trim($_REQUEST['phase']):'';
        $subject = $_REQUEST['subject'] ? trim($_REQUEST['subject']):'';
        $stage = $_REQUEST['stage'] ? trim($_REQUEST['stage']):'';
        $condition =  array();
        $phase &&  $condition['phase'] = $phase;
        $subject &&  $condition['subject'] = $subject;
        $stage &&  $condition['stage'] = $staget;
        $nodeName = 'edition';
        $return  = array();
        $return['status'] = 1;
        $return['data'] = getBookTreenodes($condition , $nodeName);
        exit(json_encode($return));
    }

    /**
     * 获取出版社信息
     */
    public function pulisher(){
        $subject = $_REQUEST['subject'] ? trim($_REQUEST['subject']):'';
        $grade = $_REQUEST['grade'] ? trim($_REQUEST['grade']):'';
        $condition =  array();
        $subject && $condition['subject'] = $subject;
        $grade &&  $condition['grade'] = $grade;
        $nodeName = 'publisher';
        $return  = array();
        $return['status'] = 1;
        $return['data'] = getBookTreenodes($condition , $nodeName);
        exit(json_encode($return));
    }

    /**
     * 获取册别
     */
    public function volume(){
        $publihser = $_REQUEST['publisher'] ? trim($_REQUEST['publisher']):'';
        $subject = $_REQUEST['subject'] ? trim($_REQUEST['subject']):'';
        $grade = $_REQUEST['grade'] ? trim($_REQUEST['grade']):'';
        $condition =  array();
        $publihser && $condition['publisher'] = $publihser;
        $subject && $condition['subject'] = $subject;
        $grade &&  $condition['grade'] = $grade;
        $nodeName = 'book';
        $return  = array();
        $return['status'] = 1;
        $return['data'] = getBookTreenodes($condition , $nodeName);
        exit(json_encode($return));
    }
}
