<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 14-4-12
 * Time: 下午1:33
 * Description: 云盘操作
 */

class CloudDiskAction extends BaseCloudAction{

    /**
     * 移动的模板及文件
     */
    public function moveTempl(){

        $fid = $_REQUEST['fid'];
        $isdir = $_REQUEST['isdir'];
        $parentfolder = $_REQUEST['parentfolder'];
        $this->assign('fid',$fid);
        $this->assign('isdir',$isdir);
        $this->assign('parentfolder',$parentfolder);
        $this->display();
    }

    /**
     * 复制的模板及文件
     */
    public function copyTempl(){

        $fid = $_REQUEST['fid'];
        $isdir = $_REQUEST['isdir'];

        $this->assign('fid',$fid);
        $this->assign('isdir',$isdir);
        $this->display();
    }
    
    public function previewTempl(){
    	$extension = $_REQUEST['ext'];
    	$fid = $_REQUEST['fid'];
    	$uid = $this->cymid;    	
    	$filename = $_REQUEST['filename'];
    	$previewurl ="";    	
    	if(empty($uid) || empty($fid)){
    		return;
    	} 
    	
    	$result = D('YunpanFile')->getPreview($uid,$fid);
    	
    	if($result->hasError==false){
    		$previewurl = $result->obj->strVal;
    	}
    	$this->assign('fid',$fid);
    	$this->assign('filename',$filename);
    	$this->assign('extension',$extension);
    	$this->assign('previewurl',$previewurl);
    	$this->display();
    }

}