var PeopleSelector = PeopleSelector||{};

/*
PeopleSelector.Role = 15;  //用户角色
PeopleSelector.Subject = 0; //学科
PeopleSelector.Grade = 0;  //年级
PeopleSelector.province = 340000;
PeopleSelector.city = 0;
PeopleSelector.area = 0;
PeopleSelector.OrderField = 0;
PeopleSelector.OrderDirector = 0;
PeopleSelector.Keywords = '';
*/

PeopleSelector.setting = {};

/**
 * 初始化
 * $param object option 用户配置相关数据
 * @return void
 */
PeopleSelector.init = function(option) {
	this.setting.role = option.role ||"";				//用户角色
	this.setting.subject = option.subject || 0;				 //学科
	this.setting.grade = option.grade || 0;				//年级
	this.setting.province = option.province || 0;					 
	this.setting.city = option.city || 0;					
	this.setting.area = option.area || 0;			
	this.setting.orderfield = option.orderfield || 0;				
	this.setting.orderdirector = option.orderdirector||0;									
	this.setting.keywords = '';									
	this.setting.school = option.schoolid || 0;						
	PeopleSelector.requestData(1);
};



PeopleSelector.requestData = function (page){
	PeopleSelector.setting.province = $('#province_selected').val();
	PeopleSelector.setting.city = $('#city_selected').val();
	PeopleSelector.setting.area = $('#area_selected').val();
	PeopleSelector.setting.keywords = $('#searchName_text').val();
	
	if(PeopleSelector.setting.role == "teacher"){
		PeopleSelector.setting.school = $('#school_selected').val();
	}
	var postArgs = {};
	postArgs.p = page;
	postArgs.grade = PeopleSelector.setting.grade;
	postArgs.roleid = PeopleSelector.setting.role;
	postArgs.subject = PeopleSelector.setting.subject;
	postArgs.province = PeopleSelector.setting.province;
	postArgs.city = PeopleSelector.setting.city;
	postArgs.area = PeopleSelector.setting.area;
	postArgs.orderfield = PeopleSelector.setting.orderfield;
	postArgs.order = PeopleSelector.setting.orderdirector;
	postArgs.keywords = PeopleSelector.setting.keywords;
	postArgs.school = PeopleSelector.setting.school;
	if(postArgs.keywords == "根据名称搜索"){
		postArgs.keywords = "";
	}
	//满足12月10日版本中教师研栏目将“政治与生活”和“政治与社会”合并成“政治与生活（社会）”的需求
	postArgs.type = $(".ts_tab li:eq(1)").attr("class");
	postArgs.t = Math.random();
	$.post(U('/Ajax/getUserList'), postArgs, function(content) {
		$('#userlist_div').html(content);
		M($('#userlist_div')[0]);
	});
}


/**
 * 切换年纪学段
 */
PeopleSelector.changeGrade=function(node){
	PeopleSelector.setting.grade= node.getAttribute('code');
	$('#gradelist li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#'+node.id).addClass('current');
};


/**
 * 切换学科
 */
PeopleSelector.changeSubject=function(node){
	PeopleSelector.setting.subject= node.getAttribute('code');
	$('#subjectlist li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#'+node.id).addClass('current');
};

/**
 * 切换教师级别
 */
PeopleSelector.changeLevel =function(node){
	PeopleSelector.setting.role  = $('#teacher_level').val();
	switch(PeopleSelector.setting.role){
	
		case 0:
			$("#city_selected").get(0).selectedIndex=0;
			$("#area_selected").get(0).selectedIndex=0;
			if($('.sr_con01 p[name="location"]').css('display')=='none'){
				$('.sr_con01 p[name="location"]').css('display','block');
			}
			if($('.sr_con01 p[name="province"]').css('display')=='none'){
				$('.sr_con01 p[name="province"]').css('display','block');
			}
			if($('.sr_con01 p[name="city"]').css('display')=='none'){
				$('.sr_con01 p[name="city"]').css('display','block');
			}
			if($('.sr_con01 p[name="county"]').css('display')=='none'){
				$('.sr_con01 p[name="county"]').css('display','block');
			}
			break;
		case 'district':
			//$("#city_selected").get(0).selectedIndex=0;
			//$("#area_selected").get(0).selectedIndex=0;
			if($('.sr_con01 p[name="location"]').css('display')=='none'){
				$('.sr_con01 p[name="location"]').css('display','block');
			}
			if($('.sr_con01 p[name="province"]').css('display')=='none'){
				$('.sr_con01 p[name="province"]').css('display','block');
			}
			if($('.sr_con01 p[name="city"]').css('display')=='none'){
				$('.sr_con01 p[name="city"]').css('display','block');
			}
			if($('.sr_con01 p[name="county"]').css('display')=='none'){
				$('.sr_con01 p[name="county"]').css('display','block');
			}
			break;
		case 'city':
			$("#area_selected").get(0).selectedIndex=0;
			if($('.sr_con01 p[name="location"]').css('display')=='none'){
				$('.sr_con01 p[name="location"]').css('display','block');
			}
			if($('.sr_con01 p[name="province"]').css('display')=='none'){
				$('.sr_con01 p[name="province"]').css('display','block');
			}
			if($('.sr_con01 p[name="city"]').css('display')=='none'){
				$('.sr_con01 p[name="city"]').css('display','block');
			}
			if($('.sr_con01 p[name="county"]').css('display')=='block'){
				$('.sr_con01 p[name="county"]').css('display','none');
			}
			break;
		case 'province':
			$("#city_selected").get(0).selectedIndex=0;
			$("#area_selected").get(0).selectedIndex=0;
//			if($('.sr_con01 p[name="location"]').css('display')=='block'){
//				$('.sr_con01 p[name="location"]').css('display','none');
//			}
			if($('.sr_con01 p[name="province"]').css('display')=='none'){
				$('.sr_con01 p[name="province"]').css('display','block');
			}
			if($('.sr_con01 p[name="city"]').css('display')=='block'){
				$('.sr_con01 p[name="city"]').css('display','none');
			}
			if($('.sr_con01 p[name="county"]').css('display')=='block'){
				$('.sr_con01 p[name="county"]').css('display','none');
			}
			break;
		default:
			//ui.error('');
			break;
	}
}

/**
 * 切换省份
 */
PeopleSelector.changeAreaProvince=function(node){
	var provincecode = $('#province_selected').val();
	var t= Math.random();
	$.ajax({
			url:U('/Ajax/getCityList'),
			type:'get',
			data:{provincecode:provincecode,t:t},
			dataType:'json',
			success:function(content){
				if(content.status==1){
					$("#city_selected").html("<option value ='0' >请选择</option>");
					$("#area_selected").html("<option value ='0' >请选择</option>");
					$("#school_selected").html("<option value ='0' >请选择</option>");
					for(var x in content['data']){
		        			$("#city_selected").append("<option value ="+content['data'][x].code+"   areaid="+content['data'][x].id+" >"+content['data'][x].name+"</option>");
		           	}
				}else{
					ui.error(content.msg);
				}
			},
			error:function(content){
				
			}
	});
};

/**
 * 切换城市
 */
PeopleSelector.changeAreaCity=function(node){
	var citycode = $('#city_selected').val();
//	$('.sr_con01 p[name="county"]').css('display','block');
	
	
	var t= Math.random();
	$.ajax({
			url:U('/Ajax/getCountyList'),
			type:'get',
			data:{citycode:citycode,t:t},
			dataType:'json',
			success:function(content){
				if(content.status==1){
					$("#area_selected").html("<option value ='0' >请选择</option>");
					$("#school_selected").html("<option value ='0' >请选择</option>");
					for(var x in content['data']){
		        			$("#area_selected").append("<option value ="+content['data'][x].code+"   areaid="+content['data'][x].id+" >"+content['data'][x].name+"</option>");
		           	}
				}else{
					ui.error(content.msg);
				}
			},
			error:function(content){
				
			}
	});
};

/**
 * 切换区县
 */
PeopleSelector.changeAreaCounty=function(node){
	var countyid = $('#area_selected option:selected').attr('areaid');

	/*if(countyid == 0 ||countyid < 0 || typeof countyid =='undefined'){
		return ;
	}*/
	var t= Math.random();
	$.ajax({
		url:U('/Ajax/getSchoolList'),
		type:'get',
		data:{countyid:countyid,t:t},
		dataType:'json',
		success:function(content){
			if(content.status==1){
				$("#school_selected").html("<option value ='0' >请选择</option>");
				for(var x in content['data']){
	        			$("#school_selected").append("<option value ="+content['data'][x].id+">"+content['data'][x].name+"</option>");
	           	}
			}else{
				//ui.error(content.msg);
			}
		},
		error:function(content){
			
		}
	});
};



/**
 * 切换排序
 */
PeopleSelector.FieldOrder=function(node){
	var field= node.getAttribute('field');
	if(field!=PeopleSelector.setting.orderfield){
		PeopleSelector.setting.orderdirector = 'desc';
	}else{
		PeopleSelector.setting.orderdirector = node.getAttribute('order');
	}
	PeopleSelector.setting.orderfield = field;
	PeopleSelector.requestData(1);
};


PeopleSelector.search = function (){
	PeopleSelector.requestData(1);
}


PeopleSelector.searchName = function(page){
	PeopleSelector.setting.orderfield = 0;
	PeopleSelector.setting.orderdirector = 0;
	PeopleSelector.setting.subject = 0; //学科
	PeopleSelector.setting.grade = 0;  //年级
	PeopleSelector.setting.province = 0;
	PeopleSelector.setting.city = 0;
	PeopleSelector.setting.area = 0;
	
	if(PeopleSelector.setting.role!="teacher"){
		if(PeopleSelector.setting.role=="famteacher"){
			PeopleSelector.setting.role = "famteacher";
		}else{
			PeopleSelector.setting.role = "";
		}
	}
	PeopleSelector.setting.keywords = $('#searchName_text').val();
	$('#gradelist li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#gradelist li:first').addClass('current');	
	
	
	$('#subjectlist li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#subjectlist li:first').addClass('current');	
	
	if($('.sr_con01 p[name="location"]').css('display')=='none'&&PeopleSelector.setting.role != "famteacher"){
		$('.sr_con01 p[name="location"]').css('display','block');
	}
	if($('.sr_con01 p[name="province"]').css('display')=='none'&&PeopleSelector.setting.role != "famteacher"){
		$('.sr_con01 p[name="province"]').css('display','block');
	}
	if($('.sr_con01 p[name="city"]').css('display')=='none'&&PeopleSelector.setting.role != "famteacher"){
		$('.sr_con01 p[name="city"]').css('display','block');
	}
	if($('.sr_con01 p[name="county"]').css('display')=='none'&&PeopleSelector.setting.role != "famteacher"){
		$('.sr_con01 p[name="county"]').css('display','block');
	}
	
	$("#province_selected").get(0).selectedIndex=0;
	$("#city_selected").get(0).selectedIndex=0;
	$("#area_selected").get(0).selectedIndex=0;
	
	
	if($("#teacher_level").length > 0 ){
		$("#teacher_level").get(0).selectedIndex=0;
	}
	if($("#school_selected").length > 0 ){
		$("#school_selected").get(0).selectedIndex=0;
	}

	
	PeopleSelector.requestData(page);

}
