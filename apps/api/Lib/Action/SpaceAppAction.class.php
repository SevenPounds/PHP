<?php
class SpaceAppAction extends Action{
	private $questionD;
	private $researchD;
	private $userD;
	private $MSGroupMemberD;
	public function __construct() {
		parent::__construct();		
		$this->questionD = D('Question','onlineanswer');
		$this->researchD = D('Research','research');

		$this->userD = M('User');

		$this->MSGroupMemberD = D('MSGroupMember','msgroup');
	}


	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “在线答疑”
	 */
	public function getOnlineAnswers($start,$limit,$order){
		return $this->questionD->getQuestions($start,$limit,$order);
	}

	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “课题研究（主题讨论）”
	 */
	public function getResearchInfos($start,$limit,$order){
		return $this->researchD->getResearchInfos($start,$limit,$order);
	}


	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “优秀空间”
	 * $roleName 角色名称：	PROVINCE_RESAERCHER			//省级教研员
	 * 						CITY_RESAERCHER				//市级教研员
	 * 						COUNTY_RESAERCHER			//区县级教研员
	 * 						RESAERCHER					//教研员
	 * 						TEACHER						//教师
	 * 						STUDENT 					//学生
	 * 						PARENTS 					//家长
	 */
	public function getExcellentSpaces($start,$limit,$orderkey,$orderDir,$roleName){
		switch ($roleName) {
			case 'RESAERCHER':
				return $this->userD->getPersons(UserRoleTypeModel::RESAERCHER,$limit,$orderkey,$orderDir,$start);
				break;			
			case 'TEACHER':
				return $this->userD->getPersons(UserRoleTypeModel::TEACHER,$limit,$orderkey,$orderDir,$start);
				break;
			case 'PROVINCE_RESAERCHER':
				return $this->userD->getPersons(UserRoleTypeModel::PROVINCE_RESAERCHER,$limit,$orderkey,$orderDir,$start);
				break;
			case 'CITY_RESAERCHER':
				return $this->userD->getPersons(UserRoleTypeModel::CITY_RESAERCHER,$limit,$orderkey,$orderDir,$start);
				break;
			case 'COUNTY_RESAERCHER':
				return $this->userD->getPersons(UserRoleTypeModel::COUNTY_RESAERCHER,$limit,$orderkey,$orderDir,$start);
				break;
			case 'STUDENT':
				return $this->userD->getPersons(UserRoleTypeModel::STUDENT,$limit,$orderkey,$orderDir,$start);
				break;
			case 'PARENTS':
				return $this->userD->getPersons(UserRoleTypeModel::PARENTS,$limit,$orderkey,$orderDir,$start);
				break;
			default:
				return null;
				break;
		}
		

	}

	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “名师工作室”
	 *
	 * @param int    $start	起始位置
	 * @param int 	 $limit 最大数量
	 * @param string $order 排序字段
	 * @param string $orderDir 排序方向
	 */
	public function getMingshiSpaces($start,$limit,$order,$orderDir){
		return $this->MSGroupMemberD->getMemberRankingList($start,$limit,$order,$orderDir);
	}


}

?>