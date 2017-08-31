<?php
/**
 * 最新问题Widget
 * @author xypan
 * @version TS3.0
 */
class NewQuestionWidget extends Widget{
	
	/**
	 * @param array $data 配置相关数据
	 * @return string 渲染页面的HTML
	 */
	public function render($data){
		// 渲染模板
		$content = $this->renderFile(dirname(__FILE__)."/newQuestion.html", $var);
		
		// 输出html
		return $content;
	}
	/**
	 * @return 返回最新问题
	 * 
	 */
	public function getNewQuestion(){
		$list=array();
		$data = D("Question","onlineanswer")->getQuestionList('',5,3);
		$data = $data['data'];
		if($data){
			foreach($data as &$value){
				$value['url']=U('onlineanswer/Index/detail',array('qid'=>$value['qid']));
			}
			$list['status']=1;
			$list['data']=$data;
		}else{
			$list['status']=0;
		}
		echo json_encode($list);
	}
}
?>