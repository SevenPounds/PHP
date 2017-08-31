<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 14-4-12
 * Time: 下午1:54
 * Description: 云盘左侧菜单挂件
 */

class CloudDiskMenuWidget extends Widget{

    public function  render($data){
        $tpl = 'CloudDiskMenu';
        $var = array();
        // 渲染模版
        $content = $this->renderFile(dirname(__FILE__) . "/".$tpl.".html", $var);
        unset($var,$data);
        // 输出数据
        return $content;
    }

    /**
     * 获取备课本单元信息详细信息
     */
    public function getBookUnits(){
        $bookId = $_REQUEST['book'] ? trim( $_REQUEST['book']) :'';
        $var = array();
        if(!empty($bookId)){
            $var['book'] = D('Beikeben')->getBeikebenDetail($GLOBALS['ts']['cyuserdata']['user']['cyuid'],$bookId);
            $units = D('Beikeben')->getBookFileAndDirs($GLOBALS['ts']['cyuserdata']['user']['cyuid'],$bookId,0,1000);
            foreach($units as &$value){
                $value->shortName = getShort($value->name,10,'...');
                $temp = strval($value->createtime);
                $temp = substr($temp,0,(strlen($temp)-3));
                $value->createtime = date('Y/n/j',$temp);
            }
            $var['units']  = $units;
        }
        exit(json_encode($var));
    }

    /**
     * 获取备课本单元课程信息详细信息
     */
    public function getUnitLessons(){
        $unit = $_REQUEST['unit'] ? trim( $_REQUEST['unit']) :'';
        $var = array();
        if(!empty($unit)){
            $lessons= D('Beikeben')->getBookDirs($GLOBALS['ts']['cyuserdata']['user']['cyuid'],$unit,0,1000);
         //   $lessons[] = (object)array('fid'=>'11212','name'=>'测试');
            $var['lessons']  = $lessons;
        }
        $content = $this->renderFile(dirname(__FILE__) . "/BookLessons.html", $var);
        unset($var);
        exit($content);
    }


    /**
     * 获取我的备课本信息
     */
    public function getBooks(){
        $var = array();

        //TODO 获取当前用户我的备课 id
        $bkDirId = D('Yunpan', 'yunpan')->getDirId($GLOBALS['ts']['cyuserdata']['user']['cyuid'], '0', '我的备课本');

        try{
            $books = D('Beikeben')->getBeikebens($GLOBALS['ts']['cyuserdata']['user']['cyuid'], $bkDirId, 0, 100);

            foreach($books as &$value){
                $temp = strval($value->createtime);
                $temp = substr($temp,0,(strlen($temp)-3));
                $value->createtime = date('Y/n/j',$temp);
            }
            $var['books'] = $books ? $books: array() ;
        }catch(Exception $e){
            $var['books'] = array();
        }
        exit(json_encode($var));
    }

}
