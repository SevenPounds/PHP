<?php
/**
 * 备课本
 * @example {:W('BeiKeBen')}
 * @version TS3.0 
 */
class BeiKeBenWidget extends Widget{

	/**
	 * 渲染空间数据统计模板
	 */
	public function render($var){
        
		try{
                        $bkDirId = D('Yunpan', 'yunpan')->getDirId($GLOBALS['ts']['cyuserdata']['user']['cyuid'], '0', '我的备课本');
			$books =  D('Beikeben', 'yunpan')->getBeikebens($GLOBALS['ts']['cyuserdata']['user']['cyuid'], $bkDirId, 0, 3);
			if(count($books)>2){
				$var['hasnext']=1;
			}else{
				$var['hasnext']=0;
			}
			$books=array($books[0],$books[1]);
			$var['books'] = $books;		
		}catch(Exception $e){
			$var['books'] = array();
		}		
		
		$content = $this->renderFile(dirname(__FILE__)."/beikeben.html",$var);
		return $content;
    } 
    
}
?>
