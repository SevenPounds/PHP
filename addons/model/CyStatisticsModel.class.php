<?php
include_once dirname(__FILE__).'/CyBaseModel.class.php';

/**
 * 用户服务统计
 * @author cheng
 *
 */
class CyStatisticsModel extends CyBaseModel{
	
	
	/**
	 * 统计学校中班级的数目
	 * @param int $schoolId
	 * @return int
	 */
	public function count_class_by_school($schoolId){
		if(empty($schoolId)) return 0;
		$_result = $this->client->countClassBySchool($schoolId);
		return $_result;
	}
	
	/**
	 * 统计班级中学生的数目
	 * @param int $schoolId
	 * @return int
	 */
	public function count_student_by_class($classId){
		if(empty($classId)) return 0;
		$_result = $this->client->countStudentByClass($classId);
		return $_result;
	}
	/**
	 * 统计学校中学生的数目
	 * @param int $schoolId
	 * @return int
	 */
	public function count_student_by_school($schoolId){
		if(empty($schoolId)) return 0;
		$_result = $this->client->countStudentBySchool($schoolId);
		return $_result;
	}
	/**
	 * 统计班级中教师的数目
	 * @param int $schoolId
	 * @return int
	 */
	public function count_teacher_by_class($classId){
		if(empty($classId)) return 0;
		$_result = $this->client->countTeacherByClass($classId);
		return $_result;
	}
	/**
	 * 统计学校中教师的数目
	 * @param int $schoolId
	 * @return int
	 */
	public function count_teacher_by_school($schoolId){
		if(empty($schoolId)) return 0;
		$_result = $this->client->countTeacherBySchool($schoolId);
		return $_result;
	}
	
	/**
	 * 统计班级家长的数目
	 * @param int $classId
	 * @return int
	 */
	public function count_parents_by_class($classId){
		return 0;
	}
	
}
?>