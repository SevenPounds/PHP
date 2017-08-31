(function(win){
	win.Index = win.Index || {};
	var flag = false;
	Index.casLoginImg=function(cyuid,url){
		
		jQuery.ajax({
			type: "get",
			url: url+"/index.php?app=public&mod=Avatar&act=index",
			data :{cyuid:cyuid},
			beforeSend: function(XMLHttpRequest){
				//ShowLoading();
			},
			success: function(data){
				jQuery("#img1").attr('src',data);
			},
			complete: function(XMLHttpRequest, textStatus){
				//HideLoading();
				
			},
			error: function(){
				//请求出错处理
				
			}
		});

	
}



Index.schoolName = function(school){
var shot_school =school.length>=5?school.substring(0,4)+"...":school;
jQuery("#school").html(shot_school);
} 

Index.loginThinkSNS = function(url){
	jQuery("#userin").click(function(){
		window.location.href = url+"/index.php?app=public&mod=Index&act=index";
	});
	} 
Index.loginHomePage = function(url){
	jQuery("#userhome").click(function(){
		window.location.href = url+"/index.php?app=public&mod=Profile&act=index";
	});
	}
Index.iealert=function iealert(){
	var browser=navigator.appName

	var b_version=navigator.appVersion

	var version=b_version.split(";");

	if(typeof(version[1]) != "undefined"){
		var trim_Version=version[1].replace(/[ ]/g,"");

		if(browser=="Microsoft Internet Explorer" && trim_Version=="MSIE6.0")
		{
		
			var html = '<div class="ie6_update_tooltip"><a href="http://www.microsoft.com/windows/ie/downloads/default.mspx" target="_blank"><p>当前浏览器版本(IE 6.0)过低，会影响网页浏览体验，请升级至更高版本。</p></a></div>';
			jQuery('#header').css("height","80px");
			jQuery('#header').css("background-color","rgb(83,82,78)");
			jQuery('#header').css("background-image","none");
			jQuery('#header').css("background-repeat","repeat");
			jQuery('#header').prepend(html);

		}
		else{return;}
		
		setTimeout(function(){
			jQuery('#header .ie6_update_tooltip').remove();
			jQuery('#header').css("height","40px");
		},10000);
	}
}

/**
 * 重置密码
 */
Index.reset = function reset(){
	if(flag){
		return false;
	}
	var postArgs = {};
	postArgs.userName = jQuery.trim(jQuery('#user_name').val());
	postArgs.idCard =  jQuery.trim(jQuery('#id_card').val());
	postArgs.email =  jQuery.trim(jQuery('#email').val());
	if(postArgs.userName == '' || postArgs.idCard == '' || postArgs.email == ''){
		jQuery("#error").html("请输入完整信息！");
		return false;
	}
	//身份证验证
	var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;  
	if(reg.test(postArgs.idCard) === false){  
		jQuery("#error").html("身份证输入不合法！");
		return  false;  
	}  
	//邮箱验证
	var re = /^([a-zA-Z0-9]+[_|\-|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\-|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/; 
	if(re.test(postArgs.email)==false){ 
		jQuery("#error").html("邮箱输入不合法！");
		return false;
	}
	flag = true;
	jQuery.ajax({
		type:"post",
		data:postArgs,
		url:"index.php?app=resource&mod=Index&act=reset",
		success: function(msg){
			result = eval('('+msg+')');
			if(result.status == '200'){
				jQuery("#error").html('');
				alert('密码已重置，请前往注册邮箱查看！');
				jQuery.fancybox.close();
				flag = false;
			}else	if(result.status == '400'){
				jQuery("#error").html(result.data);
				flag = false;
			}
		},
		error:function(){
			flag = false;
		}
	});
};

/**
 * 关闭重置密码框
 */
Index.cancel = function(){
	jQuery.fancybox.close();
	jQuery('#user_name').val('');
	jQuery('#id_card').val('');
	jQuery('#email').val('');
	jQuery("#error").html('');
}

/**
 * 限制输入字数
 * @param jQuery_obj  jquery对象
 * @param length  输入框限制的长度
 */
Index.wordLimit = function(jQuery_obj, length) {
	var str = jQuery.trim(jQuery_obj.val());
	// 字符串中英文等字符的总长度
	var sum = 0;
	// 在页面中输入的最后一个字符在字符串中的实际位置
	var cursor = 0;
	for ( var i = 0; i < str.length; i++) {
		// 英文字母等数字的长度为0.5,汉字等字符的长度为1
        if ((str.charCodeAt(i) >= 0) && (str.charCodeAt(i) <= 255)) {
                   sum = sum + 0.5;
       } else {
                   sum = sum + 1;
       }
		if (sum > length && cursor == 0) {
			cursor = i;
		}
	}
	// 截取字符串
	if (cursor != 0) {
		jQuery_obj.val(str.substring(0, cursor));
	}
}

/**
 * 限制字数
 * 注意：该字数限制和批量导入的设置是一样的
 */
Index.keyUpDown = function(){
	jQuery("#user_name").keyup(function() {
		Index.wordLimit(jQuery(this), 10);
	});
	jQuery("#user_name").keydown(function() {
		Index.wordLimit(jQuery(this), 10);
	});
	jQuery("#id_card").keyup(function() {
		Index.wordLimit(jQuery(this), 9);
	});
	jQuery("#id_card").keydown(function() {
		Index.wordLimit(jQuery(this), 9);
	});
	jQuery("#email").keyup(function() {
		Index.wordLimit(jQuery(this), 20);
	});
	jQuery("#email").keydown(function() {
		Index.wordLimit(jQuery(this), 20);
	});
}

})(window);
  
	
	
		

