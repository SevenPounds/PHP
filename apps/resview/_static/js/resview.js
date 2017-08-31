
//文档加载完后初始化的事件
$(document).ready(function(){
	// 初始化页面中的事件
	resview.initPage();
});

function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	var expires = new Date();
	if(cookieValue == '' || seconds < 0) {
		cookieValue = '';
		seconds = -2592000;
	}
	expires.setTime(expires.getTime() + seconds * 1000);
	domain = '';
	path = '/';
	document.cookie = escape(cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

function getcookie(name, nounescape) {
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	if(cookie_start == -1) {
		return '';
	} else {
		var v = document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length));
		return !nounescape ? unescape(v) : v;
	}
}

var jiathis_config = {
    		 url:location.href,
    	     title:"#安徽基础教育资源服务平台#",
    	     summary:""
    };
var resview = resview ||{};


/**
 * 页面初始化方法
 */
resview.initPage = function(){
	// 删除论文点击事件
	$(".delete-res").click(resview.deleteRes);
	
	// 隐私设置点击事件
	$(".privacy-set").click(resview.privacySet);
	
	// 分享按钮点击事件
	$(".share-res").toggle(resview.showShare, resview.hideShare);
};

/**
 * 提交隐私设置
 */
resview.submitPrivacySet = function(){
	var privacy_code = $("#privacy_settings input[type='radio']:checked").val();
	var data = {};
	data.resid = "test";
	data.privacy = privacy_code;
	$.ajax({
		url : 'index.php?app=resview&mod=Index&act=submitPrivacySet',
		type : 'POST',
		data : data,
		success : function(result){
			ui.box.close();
			if(result){
				ui.success("保存成功！");
			}
		},
		error : function(msg){
			ui.box.close();
		}
	});
};

/**
 * 隐私设置弹窗取消事件
 */
resview.cancelPrivacySet = function(){
	ui.box.close();
};

resview.id;

//收藏的权限标识
resview.limit = true;

resview.uid = 0;
resview.appurl = "";

resview.filename;

// 当前进行的操作名称
resview.operate = "";

// 是否已经评论过的标识
resview.commentFlag = true;

// 一个jquery对象
resview.obj;

resview.requestData= function(url,data,succCallback,errorCallback){
	jQuery.ajax({
		type : "post",
		url : url,
		data : data,
		success : succCallback,
		error : errorCallback
	});
}

resview.succesCallback = function(successDate){
	var data  = eval('('+successDate+')');
	
	if(data.statuscode == 200){
		alert("操作成功!");
		if(resview.operate == "comment"){
			 // 改变页面上up或者down的次数
			 var t = resview.obj.parent().next().text();
			 var i = parseInt(t) + 1;
			 resview.obj.parent().next().text(i);
			 resview.commentFlag = false;
		 }	
	}else if(data.statuscode != null){
		alert("操作失败!");
	}
}


resview.errorCallback = function(errorDate){
	alert(errorDate);
}

	//预览
resview.preview = function(ext,url){	
	    ext = ext.toLowerCase();
	    if(ext!="" && url!=""){
	    	jQuery("#preview").css("display","block");
	    }else{
	    	jQuery("#preview").css("display","none");
	    	jQuery("#no_preview").css("display","block");
	    	return;
	    }
		switch(ext){
		  case "jpg":
		  case "png":
		  case "gif":
		  case "jpeg":
		  case "bmp":
			  resview.view_img(url);
		      break;
		  case "mp3":
		  case "wma":
		  case "wav":
		  case "ogg":
		  case "ape":
		  case "mid":
		  case "midi":
			  resview.view_audio(url);
			  break;
		  case "flv":
			  resview.view_flv(url);
			  break;
		  case "avi":
		  case "mp4":
		  case "wmv":
		  case "3gp":
		  case "mpg":
		  case "asf":
			  resview.view_flv(url,ext);
			  break;
		  case "swf":
			  resview.view_flash(url);
			  break;
		  case "doc":
		  case "docx":
		  case "txt":
		  case "xlsx":
		  case "xls":
		  case "ppt":
		  case "pptx":
		  case "pdf":
			  resview.view_office(url);
			  break;
		   default :
			   jQuery("#preview").css("display","none");
		   	   jQuery("#no_preview").css("display","block");
			   break;
		}
	}
	
//预览图片文件
resview.view_img=function(fileurl){
	   var viewholder=document.getElementById("preview");
       viewholder.innerHTML="";
       viewholder.innerHTML="<img src='"+fileurl+"' style='max-height:506px;max-width:737px;_width:expression(this.width>737?\"737px\":true);_height:expression(this.height>506?\"506px\":true)'></img>";
}
	
//txt、html文件
resview.view_txtandhtml=function(fileurl){
       var viewholder=document.getElementById("preview");
       viewholder.innerHTML="";
	   var element=document.createElement("div");
	   element.innerHTML="<iframe src='"+fileurl+"' width=100% ></iframe>";
	   viewholder.appendChild(element);
}
	
//预览音频文件
resview.view_audio=function(fileurl){
		jQuery("#preview").append("<center><a id='media' href='"+fileurl+"'></a></center>");
	    jQuery.fn.media.mapFormat('mp3','winmedia');
	    jQuery.fn.media.mapFormat('wav','winmedia');
	    jQuery.fn.media.mapFormat('mid','winmedia');
	    jQuery("#media").media({width:680,height:64});
}
//预览视频文件
resview.view_video=function(fileurl,ext){
//	   jQuery("#preview").append("<a class=\"media\" style='margin:auto' href='"+fileurl+"' ></a>");
//	   jQuery.fn.media.mapFormat('mp4','winmedia');
//	   jQuery.fn.media.mapFormat('mp4','winmedia');
//	   jQuery.fn.media.mapFormat('flv','winmedia');
//	   jQuery.fn.media.mapFormat('mpg','winmedia');
//	   jQuery.fn.media.mapFormat('3gp','winmedia');
//	   resview.checkplugin(ext);
	   jQuery("#preview").append("<a href="+fileurl+" style='display:block;width:736px;height:530px' id='player'></a>");
	   jQuery("#preview").append('\<script\>flowplayer("player", "apps/resview/_static/js/flowplayer/flowplayer-3.2.16.swf",{clip:{autoPlay: false,autoBuffering: true}});\<\/script\>');
}
 //预览FLV视频文件
resview.view_flv=function(fileurl){
//	   jQuery("#preview").append("<a href="+fileurl+" style='display:block;width:736px;height:530px' id='player'></a>");
//	   jQuery("#preview").append('\<script\>flowplayer("player", "apps/resview/_static/js/flowplayer/flowplayer-3.2.16.swf",{clip:{autoPlay: false,autoBuffering: true}});\<\/script\>');
	   
	$('#preview').css({'width':'750px', 'height':'530px'}).removeClass("pre_font");
	var strRegex = "^((https|http):\/\/)?"  
			 + "(([0-9]{1,3}\.){3}[0-9]{1,3}"
			 + "|"  
			 + "([0-9a-zA-Z\u4E00-\u9FA5\uF900-\uFA2D-]+[.]{1})+[a-zA-Z-]+)"  
			 + "(:[0-9]{1,4})?"
			 + "((/?)|(/[0-9a-zA-Z_!~*'().;?:@&=+$,%#-]+)+/?){1}";  
	var re = new RegExp(strRegex);  
	var url = fileurl.replace(re, "");
	$f("preview", "apps/resview/_static/js/flowplayer/flowplayer-3.2.16.swf", {
		clip: {	url: "flv:"+url, scaling: "fit", provider: "rtmp",autoPlay:false},
		plugins: {			
			controls: {
	            autoHide:'always', 
	       		url: "apps/resview/_static/js/flowplayer/flowplayer.controls-3.2.15.swf"
        	},
			rtmp: {
				url: "apps/resview/_static/js/flowplayer/flowplayer.rtmp-3.2.12.swf",
				netConnectionUrl: VOD_URL
			}
		}
	});
}
//检测插件安装与否sjzhao
resview.isFirefoxWMPPluginInstalled=function(){
	  var plugs = navigator.plugins;
	  for (var i = 0; i < plugs.length; i++) {
		var plugin = plugs[i];
		if (plugin['filename'] == 'np-mswmp.dll')
			return true;
	  }
	     return false;
 }
resview.checkplugin=function(extension){
   //以下2012.12.14修改
	if(window.navigator.userAgent.indexOf("MSIE")!=-1){//如果是IE浏览器
	     if(resview.CheckMediaVersion()>11){
	    	     jQuery(".media").media({width:737,height:530,params:{wmode:"opaque"}});
		         jQuery(".media").css({margin:"auto"});
	     }else{
	    	 if(extension=="asf"||extension=="wmv"){
	    		 jQuery(".media").media({width:737,height:530,params:{wmode:"opaque"}});
			     jQuery(".media").css({margin:"auto"}); 
	    	 }else{
	    		 alert("系统检测到您的MediaPlayer版本过低,无法播放该文件");
	    	 }
	     }
		 
    }
	else{//非IE浏览器
		if(resview.isFirefoxWMPPluginInstalled()){
			jQuery(".media").media({width:737,height:530});
			jQuery(".media").css({margin:"auto"});
		}
		else{
			jQuery("#preview").append("<strong>预览该文件需要media player plugin<a href='http://www.interoperabilitybridges.com/windows-media-player-firefox-plugin-download'><span style='color:red;font-size:20px'>点击安装</span></a>,并重启浏览器</strong>");
		}
    }
   //以上2012.12.14修改
   
}
resview.CheckMediaVersion=function(){
	var flash=""; 
    WMPVersion= oClientCaps.getComponentVersion("{22D6F312-B0F6-11D0-94AB-0080C74C7E95}","ComponentID");  
    if (WMPVersion != "") { 
    flash = ""; 
    var version = WMPVersion.split(","); 
    var i; 
    for (i = 0; i < version.length; i++) { 
      if (i != 0) 
       flash += "."; 
       flash += version[i]; 
    } 
    return flash.substring(0,2);
}
}
//预览office文件
resview.view_office=function(url){
	jQuery("#preview").css("height","517px");
    jQuery("#preview").css("width","736px");
    var fp = new FlexPaperViewer(
	 'apps/resview/_static/js/flexpaper/FlexPaperViewer',
	 'preview', { config: {
	     SwfFile: url,
	     Scale: 1.0,
	     ZoomTransition: 'easeOut',
	     ZoomTime: 0.5,
	     ZoomInterval: 0.2,
	     FitPageOnLoad: false,
	     FitWidthOnLoad: false,
	     FullScreenAsMaxWindow: false, 
	     ProgressiveLoading: false,
	     MinZoomSize: 0.2,
	     MaxZoomSize: 5,
	     SearchMatchAll: false,
	     InitViewMode: 'Portrait',
	     PrintPaperAsBitmap: false,
	     ViewModeToolsVisible: true,
	     ZoomToolsVisible: true,
	     NavToolsVisible: true,
	     CursorToolsVisible: true,
	     SearchToolsVisible: false,
	     PrintToolsVisible : false,
	     localeChain: 'zh_CN'
		}
	});
}
//预览flash动画
resview.view_flash=function(url){
	 var flashvars = {};
     var params = {};
     params.bgcolor = "#FFFFFF";
     params.menu = "false";
     params.quality = "high";
     params.play = "true";
     params.loop = "true";
     params.scale = "1";
     params.wmode = "Opaque";
     params.devicefont = "false";
     params.allowfullscreen = "true";
     params.allowscriptaccess = "always";
     params.align = "middle";
     var attributes = {};
     swfobject.embedSWF(url, "preview", "739", "530", "9.0.0", "apps/resview/_static/js/flexpaper/expressInstall.swf", flashvars, params, attributes);
}
// 判断用户是否登录
resview.judgeLogin = function(){
	
	// 判断是否是登录用户
	if (resview.uid != 0 && resview.uid != null) {
		
		// 登录用户返回true
		return true;
	}else {

		// 未登录用户跳转到登陆页面
		location = "index.php";
		
		// 返回false
		return false;
	}
}
resview.init=function(title){
	 jiathis_config.url=location.href;
	 jiathis_config.summary="我在安徽基础教育资源服务平台发现了一个很不错的文件：“"+title+"”，快来看看吧~";
}


resview.upload=function(islimit){
	 if (resview.uid  && resview.uid !='' && resview.uid != 0) {
			if(islimit==true){
				jQuery('#res_popup').fancybox({
					'width' : '75%',
					'height' : '75%',
					'padding' : 0,
					'margin' : 0,
					'scrolling' : false,
					'autoScale' : false,
					'modal' : true
				});
			}else{
				alert("操作无法完成，仅教师、骨干教师、教研员可以上传资源");
			}
		} else {
			location.href = "index.php";
		}
}


resview.event = function(){	
	 // 评论   update by frsun 2013.07.23
	 jQuery(".comment_img").click(function(){
	
		 // 判断当前的操作是up还是down
		 var index = jQuery(this).parent().parent().index();
		 var operate = "";
		 if(index == 0){
			 operate = "up";
		 }else if(index == 1){
			 operate = "down";
		 }
		 
		 // 判断是否是重复评论
		 if(getcookie("res"+resview.id)!=resview.id){
			 resview.operate = "comment";
			 resview.obj = jQuery(this);
			 
			 var url = "index.php?app=resview&mod=Ajax&act=comment";
			 var data = {'id':resview.id,'operate':operate};
			 setcookie("res"+resview.id,resview.id,3600);
			 resview.requestData(url,data,resview.succesCallback,resview.errorCallback);
		 }else{
			 alert("请勿重复评论!");
			 } 
		 }); 
	   
	 // 转载 
	 jQuery("#reproduced").click(function(){
		 
		 // 判断是否是登录用户
		 if(!(resview.judgeLogin())){
			 
			 // 结束当前js
			 return;
		 }
		 resview.operate = "reproduced";
		 
		 var url = "index.php?app=resview&mod=Ajax&act=reproduct";
		 var data = {'id':resview.id,'uid':resview.uid};
		 resview.requestData(url,data,resview.succesCallback,resview.errorCallback);
	  });
	 
	// 收藏 
	jQuery("#collect").click(function(){
		
		 // 判断是否是登录用户
		 if(!(resview.judgeLogin())){
			 
			 // 结束当前js
			 return;
		 }
		 
		// 判断用户是否具有收藏权限
		if(resview.limit){
			 resview.operate = "collect";
			 
			 var url = "index.php?app=resview&mod=Ajax&act=collect";
			 var data = {'id':resview.id,'uid':resview.uid};
			 resview.requestData(url,data,resview.succesCallback,resview.errorCallback);
		 
		}else{
			alert("操作无法完成，仅教师、骨干教师、教研员可以收藏资源");
		}
	 });
	 
	// 下载
	jQuery("#download").click(function(){
		
		 // 判断是否是登录用户
		 if(!(resview.judgeLogin())){
			 
			 // 结束当前js
			 return;
		 }	 
		 resview.operate = "download";
		 
		 location.href = "index.php?app=resview&mod=Ajax&act=download&id="+resview.id+"&uid="+resview.uid+"&filename="+resview.filename;
	  }); 
}


/**
 * 删除点击提示事件
 */
resview.deleteRes = function(){
	ui.confirmBox("删除提示","确定删除该资源？",resview.deleteResAjax);
	$(".hd").css({'background':'#73BFEE','color':'white'});
	$(".btn-green-small").css('background','#73BFEE');
};


/**
 * 隐私设置点击事件，弹出设置弹出层
 */
resview.privacySet = function(){
	ui.box.load(U('resview/Index/privacysettings'), '隐私设置',function (){});
	$(".hd").css({'background':'#73BFEE','color':'white'});
};

/**
 * 点击显示分享区域
 */
resview.showShare = function(){
	var top = $(this).offset().top;
	var left = $(this).offset().left;
	var share = $(".jiathis_style_24x24");
	share.css({'position': 'absolute', 'top': (top + 105) + 'px','left': (left + 90) + 'px'});
	share.show();
};

/**
 * 点击隐藏分享区域
 */
resview.hideShare = function(){
	var share = $(".jiathis_style_24x24");
	share.hide();
};


//资源删除
//cyuid,用户cyuid
//rid,资源id
//jumpURL,删除成功后跳转URL
resview.deleteResAjax = function(){
	var rids= new Array();
	rids[0]=resview.id;
	
	$.ajax({
		type: "POST",
		url: U("reslib/Ajax/deleteRes"),
		data:{"uid":resview.uid,'opertionids':rids,'operationtype':5},
		success:function(msg){
			location.href = resview.appurl;
		},
		error:function(msg){
			ui.error("删除失败，请稍后再试！");
		}
	});
}
