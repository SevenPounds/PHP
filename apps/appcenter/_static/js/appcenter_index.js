(function(win){
	win.Appcenter = win.Appcenter || {};
	
	$(document).ready(function(){
		$(".show-hide-operate").mouseover(function(){
			$(this).children().children(".app-ucount-score").hide();
			$(this).children().children(".app-operate").show();
		});
		
		$(".show-hide-operate").mouseout(function(){
			$(this).children().children(".app-operate").hide();
			$(this).children().children(".app-ucount-score").show();
		});
	});
	
	/**
	 * 应用分类变化事件
	 */
	Appcenter.changeCategory = function(obj){
		var cid = $(obj).attr("data-cid");
		var showid = $(obj).attr("data-show");
		$(obj).parent().children("a").css('color','#5e5e5e');
		$(obj).css('color','red');
		Appcenter.getAppsTemp(cid, 1, showid);
	};
	
	/**
	 * 应用分页点击事件
	 */
	Appcenter.changeApps = function(obj){
		var cid = $(obj).attr("data-cid");
		if(typeof(cid) == 'undefined'){
			cid = 0;
		}
		var page = $(obj).attr("data-page");
		var showid = 'show_app_list';
		Appcenter.getAppsTemp(cid, page, showid);
	};
	
	/**
	 * 获取应用列表选然后的模版
	 */
	Appcenter.getAppsTemp = function(cid, page, showid){
		$.ajax({
			url : 'index.php?app=appcenter&mod=Index&act=appList',
			type : 'post',
			data : {cid : cid, page : page},
			success : function(result){
				$("#" + showid).html(result);
				$("#app_list_paging a").attr("data-cid",cid);
				$("#app_list_paging a").attr("data-showid",showid);
			},
			error : function(msg){
				ui.error(msg);
			}
		});
	};
	
	/**
	 * 用户添加应用
	 */
	Appcenter.appOperate = function(obj, type){
		var appid = $(obj).attr("data-appid");
		var appEnName = $(obj).attr("data-app");
		var tip = '';
		if(type == 'add'){
			tip = '确认添加此应用？';
		}else{
			tip = '确认删除此应用？';
		}
		ui.confirmBox("提示", tip,function(){
			$.ajax({
				url : 'index.php?app=appcenter&mod=Index&act=appOperate',
				type : 'post',
				data : {appid : appid, type : type},
				dataType : 'json',
				success : function(result){
					if(result.statuscode){
						ui.success(result.message);
						if(type == 'add'){
							$(".appcenter-app-add-" + appEnName).hide();
							$(".appcenter-app-delete-" + appEnName).show();
						}
						if(type == 'delete'){
							$(".appcenter-app-delete-" + appEnName).hide();
							$(".appcenter-app-add-" + appEnName).show();
						}
					}else{
						ui.error(result.message);
					}
				},
				error : function(msg){
					ui.error(msg);
				}
			});
		});
	};
	
	/**
	 * 增加应用下载量
	 */
	Appcenter.addDownloadCount = function(obj){
		var appid = $(obj).attr("data-appid");
		ui.confirmBox("提示", "您确定要下载该应用吗？",function(){
			$.ajax({
				url : 'index.php?app=appcenter&mod=Index&act=addDownloadCount',
				type : 'post',
				data : {appid : appid},
				dataType : 'json',
				success : function(result){
					if(result.statuscode == '200'){
						var download_url = $(obj).attr("data-download");
						location.href = download_url;
					}else{
						ui.error(result.message);
					}
				},
				error : function(msg){
					//ui.error(msg);
				}
			});
		});
	};
	
	/**
	 * 增加用户量和访问量
	 */
	Appcenter.addAppCount = function(obj){
		var appid = $(obj).attr("data-appid");
		$.ajax({
			url : 'index.php?app=appcenter&mod=Index&act=addAppCount',
			type : 'post',
			data : {appid : appid},
			dataType : 'json',
			success : function(result){
				if(result.statuscode == '200'){
				}
				if(result.statuscode == '400'){
					ui.error(result.message);
				}
			},
			error : function(msg){
			}
		});
	};
	
	/**
	 * 展示应用操作部分按钮
	 */
	Appcenter.showOperate = function(obj){
		$(obj).find(".appcenter-app-info").hide();
        $(obj).find(".appcenter-app-operate").show();
	};
	
	/**
	 * 隐藏应用操作部分按钮
	 */
	Appcenter.hideOperate = function(obj){
		$(obj).find(".appcenter-app-operate").hide();
        $(obj).find(".appcenter-app-info").show();
	};
})(window);