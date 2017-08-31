var MyResLib = MyResLib ||{};
var isLoading = false;
var flag = false;
var defaultTab;
//用户信息初始化
MyResLib.uid = '';
MyResLib.login = '';

/********************显示上传按钮***********************/
MyResLib.ShowUploadWin = function(){
	ui.box.load(U('reslib/Index/showUpload'),'上传资源');
	//修复标题变为"#"的问题
	jQuery(".ico-close").attr("href","javascript:void(0);");
};
/****************修改属性*********************/
MyResLib.ShowUpdateResWin = function(rid){
	var url = U("reslib/Index/showUpdate")+'&rid='+rid;
	url = encodeURI(url);
	ui.box.load(url, "编辑资源");
}
/********************更新资源***********************/
MyResLib.updateResource = function(){
	var title = jQuery('#txt_title').val();
	if($.trim(title) == ""){
		ui.error("标题不能为空！");
		return;
	}
	var restype = jQuery('#update_type').val();
	if(restype == "0000"){
		ui.error("请选择资源类型！");
		return;
	}
	var description = jQuery('#txl_describe').val();
	var rid = jQuery('#txt_rid').val();
	$.ajax({
		type: "POST",
		url: U('reslib/Ajax/updateRes'),
		data: {"rid":rid, "title":title, "restype":restype, "description":description},
		dataType: "json",
		success:function(msg){
			if(msg.status == 1){
				ui.success("资源【"+title+"】更新成功！");
				MyResLib.clearUpdateResWin();
				MyResLib.refresh();
			} else{
				ui.error("资源【"+title+"】更新失败！");	
				MyResLib.clearUpdateResWin();
			}
		},
		error:function(msg){
			ui.error("资源【"+title+"】更新失败！");	
		}
	});
}
/********************刷新页面***********************/
MyResLib.refresh = function(){
	var page = jQuery("#pageNum").val();
	var operation = jQuery("#operation").val();
	if(page && operation){
		MyResLib.resPageList(page, operation);
	}
}
/********************选择排序方法***********************/
MyResLib.sort = function(id){
	var sort_type = jQuery("#sort_type"+id).attr('sort_type');
	jQuery("#sort").val(sort_type);
	MyResLib.refresh();
}
/********************切换tab样式***********************/
MyResLib.changeNav = function(node,page,operation){
	if(isLoading){
		return false ;
	}
	//防止同一个tab重复点击刷新
	if(defaultTab == operation){
		return;
	}else{
		defaultTab=operation;
	}
	isLoading = true;
	//当前tab刷新完毕后才可以点击其他tab
	
	var parentNode = node.parentNode.parentNode;
	if(operation==5 || operation==1){
		jQuery('#'+parentNode.id+' li').each(function(){
			jQuery(this).removeClass('current');
			jQuery(this).addClass('re_line');
		});
		jQuery('#'+node.parentNode.id).removeClass('re_line');
		jQuery('#'+node.parentNode.id).addClass('current');
	}
	if(operation!=5){
		jQuery('#'+parentNode.id+' li').each(function(){
			jQuery(this).removeClass('present');
			jQuery(this).addClass('normal');
		});
		jQuery('#'+node.parentNode.id).removeClass('normal');
		jQuery('#'+node.parentNode.id).addClass('present');
	}
	isLoading = false;
	MyResLib.resPageList(page,operation);
}
/********************资源库中的资源异步加载***********************/
MyResLib.resPageList = function(page, operation){
	var keywords = jQuery("#txf_search").val();
	var subject = jQuery("#subject_text").val();
	var grade = jQuery("#grade_text").val();
	var restype = jQuery("#restype_text").val();
	var sort = jQuery("#sort").val();
	jQuery("#pageNum").val(page);
	jQuery("#operation").val(operation);
	if(isLoading){
		return false ;
	}
	$.ajax({
		type: "POST",
		url: U('reslib/Ajax/getResList'),
		data:{"uid":MyResLib.uid,'g':grade,'s':subject,'t':restype,'p':page,'operation':operation,"keywords":keywords,"sort":sort},
		beforeSend:function(){//发送ajax请求前加载信息提示
			isLoading = true;
			if(operation==5){
				$(".res_div_up").html( '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>');
			}else{
				$(".res_div").html( '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>');
			}
		}, 
		success:function(msg){
			$(".res_div_up").html(msg);
			jQuery("#txf_search").val(keywords);
			isLoading = false;
		},
		error:function(msg){
			ui.error("加载失败，请重试！");
			isLoading = false;
		}
	});
}
/********************资源库中类型选择变化***********************/
MyResLib.changeSGT = function(code, operation,type){
	var keywords = jQuery("#txf_search").val();
	if("subject" == operation){
		jQuery("#subject_text").val(code);
	}else if("grade" == operation){
		jQuery("#grade_text").val(code);
	}else if("type" == operation){
		jQuery("#restype_text").val(code);
	}
	MyResLib.resPageList(1, type);
}
/********************隐藏编辑资源窗口***********************/
MyResLib.clearUpdateResWin = function()
{
	ui.box.close();
}
/********************相应Enter搜索事件***********************/
MyResLib.enterPress = function(e){
    var e = e || window.event;
    if(e.keyCode == 13){
    	MyResLib.refresh();
    }
}
