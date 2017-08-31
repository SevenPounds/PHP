<?php
/**
 * 应用类型枚举类
 * @author hhshi
 * 创建日期：2013.9.16
 * 用于标记教学应用、教学反思、我的收获三类应用
 */
final class appInfo{
	const Paper = 1;		// 教学日志
	const Reflection = 2;	// 课后反思
	const Harvest = 3;		// 我的收获
	const MATERIAL_INTRO	 = 4;// 教材介绍
	const MATERIAL_TRAIN = 5;	// 教材培训
	const STAND_EXPLAIN = 6;	// 课标解读
	const TEACH_DESIGN = 7;		// 教学设计
	const TEACH_PAPER = 8;		// 教学论文
	const TEACH_WARE = 9;		// 教学课件
	const EXAM_INSTRUCTION = 10;// 考试指导
	const FILE_NOTICE = 11;//文件通知
	const EDU_NEWNOTICE = 12;//教育快讯
}

/**
 * 根据应用编码获取应用名称
 * @param int $typeCode 应用编码
 * @return Ambigous <string> 应用名称
 */
function getAppName($typeCode){
	$AppNames = array(
			'1'=>'教学日志',
			'2'=>'课后反思',
			'3'=>'我的收获', 
			'4'=>'教材解读',
			'5'=>'教材培训',
			'6'=>'课标解读',
			'7'=>'教学设计',
			'8'=>'教学论文', 
			'9'=>'教学课件',
			'10'=>'学科拓展',
			'11'=>'文件通知',
			'12'=>'教育快讯'
			);
	if(strtolower(C('PRODUCT_CODE'))==='anhui')
		$AppNames['10'] = '教学资料' ;
	return $AppNames[$typeCode];
}

/**
 * 根据应用编码获取应用类别
 * @param int $typeCode 应用编码
 * @return Ambigous <string> 应用类型
 */
function getAppCategory($typeCode){
	$AppTypes = array(
			'1'=>'教学日志',
			'2'=>'课后反思',
			'3'=>'教学收获',
			'4'=>'教材解读',
			'5'=>'教材培训',
			'6'=>'课标解读',
			'7'=>'教学设计',
			'8'=>'教学论文', 
			'9'=>'教学课件',
			'10'=>'学科拓展',
			'11'=>'文件通知',
			'12'=>'教育快讯'
			);
	if(strtolower(C('PRODUCT_CODE'))==='anhui')
		$AppTypes['10'] = '教学资料' ;
	return $AppTypes[$typeCode];
}
?>