var Question = Question||{};
var isLoading = false;
var defaultTab;
var defaultChoose;
Question.nav = 1;//默认导航为我的问题
Question.choose =0;//默认最新答疑
Question.nowPage = 0;//记录当前页
Question.Subject  = '';
Question.Grade  = '';
Question.Status  = '';
Question.keyword = '';
Question.tagid = '';
/**
 * 切换导航
 */
Question.changeNav=function(node,nav){
	//当前tab刷新完毕后才可以点击其他tab
	if(isLoading){
		return false ;
	}
	//防止同一个tab重复点击刷新
	if(defaultTab == nav){
		return;
	}else{
		defaultTab=nav;
	}
	isLoading = true;
	Question.nav = nav;
	var parentNode = node.parentNode;
	jQuery('#'+parentNode.id+' li').each(function(){
		jQuery(this).removeClass('current');
	});
	jQuery('#'+node.id).addClass('current');
	isLoading = false;
	Question.initData();
};
/**
 * 最新，热门，精华，我关注的人的切换
 */
Question.changeChoose = function (node, nav) {

    //当前tab刷新完毕后才可以点击其他tab
    if (isLoading) {
        return false;
    }

    //防止同一个tab重复点击刷新
    if (defaultChoose == nav) {
        return;
    } else {
    	defaultChoose = nav;
    }

    isLoading = true;
    Question.choose = nav;
 
    jQuery(node).parent().find('li.current').removeClass('current');
    jQuery(node).addClass('current');
    jQuery('#searchkeyword').val('');
    Question.keyword = "";
    Question.Status = "";
    Question.tags = "";
    jQuery('#selected_tagid').val("");
	//重置tag
	var tagJquery = jQuery('#hottag_list p.card');
	tagJquery.removeClass('card');
	tagJquery.addClass('card_pre');

    jQuery('#ul_status li.current').removeClass('current');
    jQuery('#ul_status li').get(2).Class='current';

    isLoading = false;
    Question.initData();
};

/**
 * 答疑中心条件查询
 */
Question.changeCondition = function(node,type){
	jQuery('#searchkeyword').val('');
	Question.keyword="";
	switch(type){
		case 1:
			Question.Subject = node.getAttribute('code');
			break;
		case 2:
			Question.Grade = node.getAttribute('code');
			break;
		case 3:
			Question.Status = node.getAttribute('code');
			break;
	}
	var parentNode = node.parentNode;
	$('#'+parentNode.id+' li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#'+node.id).addClass('current');
	Question.initData();
};

/**
 * 异步加载问题列表
 */
Question.requestData=function(page){
	if(isLoading){
		return false ;
	}
	 jQuery.ajax({
		 type:'POST',
	 	 url:U('onlineanswer/Ajax/getQuestionList'),
	 	 data:{'nav':Question.nav,'p':page,'subject':Question.Subject,'grade':Question.Grade,'status':Question.Status,'keyword': Question.keyword,'tagid':Question.tagid,'choose':Question.choose},
	 	 beforeSend:function(){//发送ajax请求前加载信息提示
			isLoading = true;
				$("#container").html( '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>');
		 },
	 	 success:function(msg){
	 		jQuery('#container').html(msg);
	 		/*if(Question.Status === ''){
	 		   jQuery("#count_all").html('('+jQuery("#totalcount").val()+')');
	 		}*/
	 		isLoading = false;
	 	 },
	 	 error:function(msg){
	 		ui.error(msg);
	 		isLoading = false;
	 	 }
	 });
};
/**
 * 根据状态查询 -1全部、1进行中、0已结束
 */
Question.changeStatus = function(status){
	jQuery("#ul_status li").each(function(){
		jQuery(this).removeClass("current");
	});
	switch(status){
	    case  -1:
	    	Question.Status  = '';
	    	jQuery("#status_all").addClass("current");
	    	break;
	    case  0:
	    	Question.Status  = 0;
	    	jQuery("#status_ing").addClass("current");
	    	break;
	    case  1:
	    	Question.Status  = 1;
	    	jQuery("#status_end").addClass("current");
	    	break;
	}
	Question.initData();
} 
/**
 * 回车事件
 */
Question.enter=function(e){
	if(e.keyCode==13){
		Question.search();
	}
};

/**
 * 根据资源名称搜索
 */
 Question.search=function(){
	Question.initkey();
	Question.changeStatus(-1);
	Question.requestData(1);
}


/**
 * 初始化搜索关键字
 */
 Question.initkey=function(){
	 Question.keyword=jQuery.trim(jQuery('#searchkeyword').val());
};

Question.initData=function(){
	Question.requestData(1);
};  





