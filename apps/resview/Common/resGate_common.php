<?php
/*
 * 访问资源网关 共用方法
 *
**/
/**
 * 获取category_value
 */
function getNameByCode($field,$code){
    $categoryService = new CategoryClient();
    $array = $categoryService->Category_GetCategoryValue($field);
	foreach ($array->data as $key=>$value){
		if($value->code == $code){
			$obj = $value;
			break;
		}
	}
	return $obj;
}
?>