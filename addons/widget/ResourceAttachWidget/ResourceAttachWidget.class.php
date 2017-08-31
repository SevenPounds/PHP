<?php
/**
 * 文件上传
 * 主要由core.uploadFile完成前端ajaxpost功能
 * 要自定义回调显示则需要自定义回调函数
 * @example {:W('ResourceAttach',array('to_action'=>'preview/edit','attachIds'=>array("attach_id"=>1172/resource_id,"attach_type"=>0/1)))}
 * @author yxxing#iflytek.com
 */

class ResourceAttachWidget extends Widget{
    private  static $rand = 1;

    /**
     * @param mixed attachIds 已有附件ID，可以为空
     */
	public function render($data){
		$var = array();
        $var['inputname'] = 'attach';
        $var['attachIds'] = '';
        $var['inForm'] = 1;
        $var['to_action'] = "preview";
        $var['app_name'] = "paper";
        $var['paper_id'] = "";
        
		!in_array($var['to_action'], array("preview","edit")) && $var['to_action'] = "preview";
        is_array($data) && $var = array_merge($var,$data);

        if(!empty($var['attachIds'])){
            !is_array($var['attachIds']) && $var['attachIds'] = explode(',', $var['attachIds']);
            foreach($var['attachIds'] as $k=>$v){
            	if(intval($v['attach_type']) == 0){
            		$attachInfo = model('Attach')->getAttachById($v['attach_id']);
            		$attachInfo['extension']  = strtolower($attachInfo['extension']);
            		$attachInfo['download_url'] = U('widget/Upload/down',array('attach_id'=>$v['attach_id']));
            	}elseif (intval($v['attach_type']) == 1){
            		$attachInfo = D('Resource', "reslib")->where(array("rid"=>$v['attach_id']))->field(array("rid","title","suffix","size"))->find();
            		$attachInfo['extension'] = strtolower($attachInfo['suffix']);
            		$attachInfo['size'] = strtolower($attachInfo['size']);
            		$attachInfo['name'] = strtolower($attachInfo['title'].".".$attachInfo['suffix']);
            		$attachInfo['download_url'] = U('reslib/Ajax/downloadResource', array("rid"=>$v['attach_id'],"filename"=>$attachInfo['title'].".".$attachInfo['suffix']));
            		$attachInfo['attach_id'] = $attachInfo['rid'];
            	}
            	$attachInfo['attach_type'] = $v['attach_type'];
            	$var['attachInfo'][] = $attachInfo;
            }
            $var['attachIds'] = implode('|', $var['attachIds']);
        }
		
        //渲染模版
        $content = $this->renderFile(dirname(__FILE__)."/".$var['to_action'].".html",$var);
        
        unset($var,$data);
        
        //输出数据
        return $content;
    }
    
	/*
	 * 删除文章和公告附件(适用于编辑文章/删除文章)
	 */
	public function deleteAttach(){
		$type = $_REQUEST["attach_type"];
		$attachId = $_REQUEST["attach_id"];
		$return = "0";
		$paper_id = $_REQUEST["paper_id"];
		if(!$paper_id){
			//如果这个附件不属于任何的文章
			if($type == 0){
				$return = D("Attach")->doEditAttach($attachId, "deleteAttach","");
				if($return["status"] == "1"){
					$return =  "1";
				}
			}
			$return = "1";
			exit($return) ;
		}else{
			//如果这个附件属于某个文章
			$appname = $_REQUEST["app_name"];
			//删除paper_attach或者notice_attach表中的记录
			switch($appname){
				case "paper":
					$r = D("PaperAttach","paper")->where(array("attach_id"=>$attachId,"paper_id"=>$paper_id))->delete();
					break;
				case "notice":
					$r = D("NoticeAttach")->where(array("attachId"=>$attachId,"noticeId"=>$paper_id))->delete();
					break;
				case "msgroup":
					$r = D("MSGroupAnnounceAttach","msgroup")->deleteAttachByID($attachId);
					break;
			}
			if($r){
				//普通附件，删除attach表中的记录
				if($type == 0){
					$return = D("Attach")->doEditAttach($attachId,"deleteAttach","");
					if($return["status"] == "1"){
						$return =  "1";
					}
				}
				$return = "1";
			}
			echo $return;
		}
	}
}
?>