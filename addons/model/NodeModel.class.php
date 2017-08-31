<?php

/**
 * 学科、年级、资源类型等节点信息
 * @author xypan 10.16
 * @version TS3.0 
 */
class NodeModel{
	
	private $_treeclient = null;
	private $_categoryclient = null;
	
	/**
	 * 学科
	 * @var array
	 */
	public $subjects = array();
	
	/**
	 * 年级
	 * @var array
	 */
	public $grades = array();

    /**
     * 用户服务获取的学科信息
     */
    public $cySubjects = array();

	/**
	 * 构造函数
	 */
	public function __construct(){
		$this->_treeclient = D('CyCore')->Tree;
		$this->_categoryclient = D('CyCore')->Category;
		$this->subjects = $this->getCySubjects();
		$this->grades = $this->getGrades();
	}
	
	/**
	 * 通过节点的编码获取对应的
	 * @param string $node 节点名称
	 * @param string $code 编码
	 */
	public function getNameByCode($node,$code){
		$code = strval($code);
		switch($node){
			case 'grade':
				foreach ($this->grades as $grade){
					if($code == $grade['code'] && $code){
						return $grade['name'];
					}
				}
				return "";
			case 'subject':
				foreach ($this->subjects as $subject){
					if($code == $subject['code'] && $code){
						return $subject['name'];
					}
				}
				return "";
		}
	}

	/**
	 * 所有的学科信息
	 */
	private function getSubjects(){
		$subjects = array();
		$subjects[] = array(code=>"", name=>"全部");
		$subjects[] = array(code=>"01", name=>"语文");
        $subjects[] = array(code=>"02", name=>"数学");
        $subjects[] = array(code=>"03", name=>"英语");
        $subjects[] = array(code=>"07", name=>"品德与生活");
        $subjects[] = array(code=>"10", name=>"品德与社会");
        $subjects[] = array(code=>"11", name=>"思想品德");
        $subjects[] = array(code=>"103", name=>"思想政治");
        $subjects[] = array(code=>"12", name=>"历史");
        $subjects[] = array(code=>"14", name=>"地理");
        $subjects[] = array(code=>"05", name=>"物理");
        $subjects[] = array(code=>"06", name=>"化学");
        $subjects[] = array(code=>"13", name=>"生物");
        $subjects[] = array(code=>"04", name=>"音乐");
        $subjects[] = array(code=>"09", name=>"美术");
        $subjects[] = array(code=>"25", name=>"体育与健康");
        $subjects[] = array(code=>"26", name=>"信息技术");
        $subjects[] = array(code=>"102", name=>"通用技术");
        $subjects[] = array(code=>"106", name=>"幼儿教育");
        $subjects[] = array(code=>"19", name=>"小学科学");
        $subjects[] = array(code=>"101", name=>"综合实践活动");
		$subjects[] = array(code=>"108", name=>"高中研究性学习");
		return $subjects;
	}
	
	/**
	 * 所有的年级信息
	 */
	private function getGrades(){
		$grades = array();
		$grades[] = array(code=>"", name=>"全部");
		$grades[] = array(code=>"14", name=>"小班");
		$grades[] = array(code=>"15", name=>"中班");
		$grades[] = array(code=>"16", name=>"大班");
		$grades[] = array(code=>"17", name=>"学前");
		$grades[] = array(code=>"01", name=>"一年级");
		$grades[] = array(code=>"02", name=>"二年级");
		$grades[] = array(code=>"03", name=>"三年级");
		$grades[] = array(code=>"04", name=>"四年级");
		$grades[] = array(code=>"05", name=>"五年级");
		$grades[] = array(code=>"06", name=>"六年级");
		$grades[] = array(code=>"07", name=>"七年级");
		$grades[] = array(code=>"08", name=>"八年级");
		$grades[] = array(code=>"09", name=>"九年级");
        $grades[] = array(code=>"10", name=>"高一");
        $grades[] = array(code=>"11", name=>"高二");
        $grades[] = array(code=>"12", name=>"高三");
		return $grades;
	}

	/**
     * 从网关获取学科列表
     * @return array
     */
    private function getAhSubjectList(){
		$subjectList = $this->_categoryclient->Category_GetCategoryValue("subject")->data;
        $result = array();
        $result[] = array('code' => '', 'name' => '全部');

        foreach ($subjectList AS $subject) {
			$result[] = array('code'=>$subject->code,'name'=>$subject->name);
        }
        return $result;
    }

    /**
     * 从网关获取学科列表
     * @return array
     */
    private function getSubjectList(){
		$subjectList = $this->_categoryclient->Category_GetCategoryValue("subject")->data;
        $result = array();
        $result[] = array('code' => '', 'name' => '全部');

        foreach ($subjectList AS $subject) {
			if ($subject->code != '00' && $subject->code != '17') {
	            $result[] = array('code'=>$subject->code,'name'=>$subject->name);
			}
        }
        return $result;
    }
    /**
     * 用户服务获取的学科信息
     */
    private function getCySubjects(){
        $subject = S('subject_list_'.C('PRODUCT_CODE'));
        if(!$subject){
            if(C('PRODUCT_CODE') == 'ANHUI'){
                $subject = $this->subjectSort($this->getAhSubjectList());
                S('subject_list_'.C('PRODUCT_CODE'), $subject, 3600);
            }else{
                $subject = $this->getSubjectList();
                S('subject_list_'.C('PRODUCT_CODE'), $subject, 3600);
            }
        }
        return $subject;
    }

    /**
     * 将学科数组按照指定的顺序排序
     * @param $subjects
     * @return array
     */
    private function subjectSort($subjects){
        $targets = array(
            '1'=>'语文','2'=>'数学','3'=>'英语','4'=>'品德与生活','5'=>'品德与社会',
            '6'=>'思想品德','7'=>'思想政治','8'=>'历史','9'=>'地理','10'=>'物理',
            '11'=>'化学','12'=>'生物','13'=>'音乐','14'=>'体育与健康','15'=>'美术',
            '16'=>'信息技术','17'=>'通用技术','18'=>'幼儿教育','19'=>'科学','20'=>'综合实践活动');
        foreach($subjects as $key=>$val){
            if(empty($val->name)){
                if(in_array($val['name'],$targets)){
                    $keys = array_keys($targets,$val['name']);
                    $newSubjects[$keys[0]] = $val;
                }
            }else{
                if(in_array($val->name,$targets)){
                    $keys = array_keys($targets,$val->name);
                    $newSubjects[$keys[0]] = $val;
                }
            }
        }
        $newSubjects[0] = array('code'=>"", 'name'=>"全部");
        ksort($newSubjects);
        $result = array();
        foreach($newSubjects as $key=>$val){
            $result[] = $val;
        }
        return $result;
    }
    
    /**
     * 设置学段
     * @return array():
     */
    public function getXueduans(){
    	$xueduans = array();
   // 	array_push($xueduans, array('code'=>'01','name'=>'幼儿园'));
      	array_push($xueduans, array('code'=>'02','name'=>'学前班'));
    	array_push($xueduans, array('code'=>'03','name'=>'小学'));
    	array_push($xueduans, array('code'=>'04','name'=>'初中'));
    	array_push($xueduans, array('code'=>'05','name'=>'高中'));
   // 	array_push($xueduans, array('code'=>'06','name'=>'大学'));
    	return $xueduans;
    }
    
    /**
     * 学段中文名
     */
    public function getXueduanCNName($code){
    	$xueduan = array(
    	//		'01' => '幼儿园',
    	    	'02' => '学前班',
    			'03' => '小学',
    			'04' => '初中',
    			'05' => '高中',
    	//		'06' => '大学'
    			);
    	return $xueduan[$code];
    }

}
?>
