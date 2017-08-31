<?php
/**
 * Created by PhpStorm.
 * User: xypan
 * Date: 14-6-24
 * Time: 下午1:18
 */
class ResourceCommonModel{

    protected $treeService = null;

    public function __construct(){
        $this->treeService = new TreeClient();
    }

    /**
     * 获取节点信息(phase|subject|edition|book)
     * @param $data array 传入的条件
     * @param $node string 要查询的节点
     * @param string $treeName 要查询的节点树
     * @return array
     */
    public function getNodes($data,$node,$treeName = 'booklibrary2'){
        $condition = array();
        $condition = empty($data['phase'])?$condition:array_merge($condition,array('phase'=>$data['phase']));
        $condition = empty($data['subject'])?$condition:array_merge($condition,array('subject'=>$data['subject']));
        $condition = empty($data['edition'])?$condition:array_merge($condition,array('edition'=>$data['edition']));
        $condition = empty($data['stage'])?$condition:array_merge($condition,array('stage'=>$data['stage']));

        $result = $this->treeService->Tree_GetTreeNodes($treeName,$node, $condition);
        $result = $result->statuscode == 200 ? $result->data:array();

        // 去除目录节点中的"其他"和"演示"等无用信息
        if($node == "subject"){
            foreach ($result as &$value){
                if(in_array($value->code,array('00','17'))){
                      $value = null;
                }
            }
            $result = array_filter($result);
        }

        return $result;
    }


    /**
     * 根据书本Id获取单元和课时信息
     * @param $bookId 书本Id
     * @return array
     */
    public function getUnitsByBookId($bookId){

        $book = $this->treeService->Tree_getBookindex($bookId);

        if($book->statuscode == 200){
            $book = $book->data;
            return $book->general->resourcedescriptor->units;
        }else{
            return array();
        }
    }
}