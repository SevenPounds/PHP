var Center = Center || {};
var isLoading = false;
var defaultTab=1;
Center.nav = 0;//默认导航为最新问题
Center.nowPage = 0;//记录当前页
Center.Subject  = '';
Center.Grade  = '';
Center.Status  = '';

/**
 * 切换导航
 */
Center.changeNav = function(node, nav) {
	jQuery('#keyword').val('');
	Center.keyword="";
	//当前tab刷新完毕后才可以点击其他tab
	if (isLoading) {
		return false;
	}
	//防止同一个tab重复点击刷新
	if (defaultTab == nav) {
		return;
	} else {
		defaultTab = nav;
	}

	isLoading = true;
	Center.nav = nav;
	var parentNode = node.parentNode;
	jQuery('#'+parentNode.id+' li').each(function() {
		jQuery(this).removeClass('current');
		jQuery(this).addClass('re_line');
	});
	jQuery('#'+node.id).removeClass('re_line');
	jQuery('#'+node.id).addClass('current');
	isLoading = false;
	Center.initData();
};

/**
 * 答疑中心条件查询
 */
Center.changeCondition = function(node,type){
	jQuery('#keyword').val('');
	Center.keyword="";
	switch(type){
		case 1:
			Center.Subject = node.getAttribute('code');
			break;
		case 2:
			Center.Grade = node.getAttribute('code');
			break;
		case 3:
			Center.Status = node.getAttribute('code');
			break;
	}
	var parentNode = node.parentNode;
	$('#'+parentNode.id+' li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#'+node.id).addClass('current');
	Center.initData();
};

/**
 * 异步加载问题列表
 */
Center.requestData = function(page) {
	if (isLoading) {
		return false;
	}

	jQuery.ajax({
		type : 'POST',
		url : U('vote/Ajax/getCenterList'),
		data : {'nav' : Center.nav, 'p' : page, 'keyword' : Center.keyword},
		beforeSend : function() {//发送ajax请求前加载信息提示
			isLoading = true;
			$("#container").html('<div class="loading" id="loadMore">' + L('PUBLIC_LOADING') + '<img src="' + THEME_URL + '/image/icon_waiting.gif" class="load"></div>');
		},
		success : function(msg) {
			jQuery('#container').html(msg);
			isLoading = false;
		},
		error : function(msg) {
			ui.error(msg);
			isLoading = false;
		}
	});
};

/**
 * 回车事件
 */
Center.enter=function(e){
	if(e.keyCode==13){
		Center.search();
	}
};

/**
 * 根据资源名称搜索
 */
Center.search=function(){
	Center.initkey();
	jQuery('#nav_list li').each(function() {
		jQuery(this).removeClass();
		jQuery(this).addClass('re_line');
	});
	jQuery("#vote_search").removeClass('re_line');
	jQuery("#vote_search").addClass('current');
	Center.requestData(1);
}


/**
 * 初始化搜索关键字
 */
Center.initkey=function(){
	Center.keyword=jQuery.trim(jQuery('#keyword').val());
};

Center.initData=function(){
	Center.requestData(1);
}; 

jQuery(function(){
	Center.initData();
}); 





