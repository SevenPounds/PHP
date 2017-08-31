var Vote = Vote || {};
var isLoading = false;
var defaultTab;
Vote.nav = 0;//默认导航为最新问题    0:最新;   1:热门;    2:精华;   3:我关注的;
Vote.nowPage = 0;//记录当前页
Vote.Subject  = '';
Vote.Grade  = '';
Vote.Status  = '';
Vote.type = ''; //作为中心页面与我发起调研，参与的调研区分
Vote.tags = '';

/**
 * 切换导航
 */
Vote.changeNav = function (node, nav) {

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
    Vote.nav = nav;
 
    jQuery(node).parent().find('li.current').removeClass('current');
    jQuery(node).addClass('current');
    jQuery('#searchkeyword').val('');
    Vote.keyword = "";
    Vote.Status = "";
    Vote.tags = "";
    jQuery('#selected_tagid').val("");
    jQuery('#hottag_list p').each(function () {
        jQuery(this).removeClass('card');
        jQuery(this).addClass('card_pre');
    });

    jQuery('#ul_status li').each(function () {
        jQuery(this).removeClass('current');
    });
    jQuery('#ul_status li:first').addClass('current');

    isLoading = false;
    Vote.initData();
};

/**
 * 答疑中心条件查询
 */
Vote.changeCondition = function (node, type) {
   // jQuery('#searchkeyword').val('');
   // Vote.keyword = "";
    switch (type) {
        case 1:
            Vote.Subject = node.getAttribute('code');
            break;
        case 2:
            Vote.Grade = node.getAttribute('code');
            break;
        case 3:
            Vote.Status = node.getAttribute('code');
            break;
    }
    var parentNode = node.parentNode;
    var _this = node;
    $('#' + parentNode.id + ' li').each(function () {
        jQuery(this).removeClass('current');
    });
    jQuery(_this).addClass('current');
    Vote.initData();
};

/**
 * 异步加载问题列表
 */
Vote.requestData = function (page) {
    if (isLoading) {
        return false;
    }
    if (Vote.type === 'center') {
        var requrl = U('vote/Ajax/getCenterList');
    } else {
        var requrl = U('vote/Ajax/getVoteList');
    }
    jQuery.ajax({
        type: 'POST',
        url: requrl,
        data: { 'nav': Vote.nav, 'p': page, 'keyword': Vote.keyword, 'status': Vote.Status ,'tags': Vote.tags },
        beforeSend: function () {//发送ajax请求前加载信息提示
            isLoading = true;
            $("#container").html('<div class="loading" id="loadMore">' + L('PUBLIC_LOADING') + '<img src="' + THEME_URL + '/image/icon_waiting.gif" class="load"></div>');
        },
        success: function (msg) {
            jQuery('#container').html(msg);
            isLoading = false;
        },
        error: function (msg) {
            ui.error("网络异常，请稍等...");
            isLoading = false;
        }
    });
};

/**
 * 回车事件
 */
Vote.enter=function(e){
	if(e.keyCode==13){
		Vote.search();
	}
};

/**
 * 根据资源名称搜索
 */
Vote.search = function () {
    Vote.Status = '';
    jQuery('#ul_status li').each(function () {
        $(this).removeClass('current');
    });
    jQuery('#ul_status li:first').addClass('current');
    Vote.initkey();
    Vote.requestData(1);
}


/**
 * 初始化搜索关键字
 */
Vote.initkey=function(){
	Vote.keyword=jQuery.trim(jQuery('#searchkeyword').val());
};

Vote.initData=function(){
	Vote.requestData(1);
}; 

/**
* 标签变化搜索
*/
Vote.changTags = function () {
    Vote.tags = jQuery('#selected_tagid').val();
    Vote.initkey();
    Vote.requestData(1);
}

jQuery(function(){
	Vote.initData();
}); 





