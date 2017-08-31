var resourceDetail = resourceDetail ||{};
resourceDetail.id;
//收藏的权限标识
resourceDetail.limit = true;
resourceDetail.uid = 0;
resourceDetail.filename;
// 当前进行的操作名称
resourceDetail.operate = "";
// 一个jquery对象
resourceDetail.obj;

resourceDetail.requestData= function(url,data,succCallback,errorCallback){
	jQuery.ajax({
		type : "post",
		url : url,
		data : data,
		success : succCallback,
		error : errorCallback
	});
}
resourceDetail.succesCallback = function(successDate){
	var data  = eval('('+successDate+')');
	if(data.statuscode == 200){
		alert("操作成功!");
	}else if(data.statuscode != null){
		alert("操作失败!");
	}
}
resourceDetail.errorCallback = function(errorDate){
	alert(errorDate);
}
//预览
resourceDetail.preview = function(ext,url){	
	    ext = ext.toLowerCase();
	    if(ext!="" && url!=""){
	    	jQuery("#preview").css("display","block");
	    }else{
	    	jQuery("#preview").append("<img src='"+THEME_URL+"/image/no_preview.jpg'/>");
	    	return;
	    }
		switch(ext){
		  case "jpg":
		  case "png":
		  case "gif":
		  case "jpeg":
		  case "bmp":
			  resourceDetail.view_img(url);
		      break;
		  case "mp3":
		  case "wma":
		  case "wav":
		  case "ogg":
		  case "ape":
		  case "mid":
		  case "midi":
			  resourceDetail.view_audio(url);
			  break;
		  case "flv":
		  case "avi":
		  case "mp4":
		  case "wmv":
		  case "3gp":
		  case "mpg":
		  case "asf":
			  resourceDetail.view_flv(url);//上传后的视频都转换成flv的预览文件
			  break;
//			  resourceDetail.view_video(url,ext);
//			  break;
		  case "swf":
			  resourceDetail.view_flash(url);
			  break;
		  case "doc":
		  case "docx":
		  case "txt":
		  case "xlsx":
		  case "xls":
		  case "ppt":
		  case "pptx":
		  case "pdf":
			  resourceDetail.view_office(url);
			  break;
		   default :
			   jQuery("#preview").css("display","none");
			   break;
		}
	}
//预览图片文件
resourceDetail.view_img=function(fileurl){
	   var viewholder=document.getElementById("preview");
       viewholder.innerHTML="";
       viewholder.innerHTML="<img src='"+fileurl+"' style='max-height:506px;max-width:737px;_width:expression(this.width>737?\"737px\":true);_height:expression(this.height>506?\"506px\":true)'></img>";
}
//txt、html文件
resourceDetail.view_txtandhtml=function(fileurl){
       var viewholder=document.getElementById("preview");
       viewholder.innerHTML="";
	   var element=document.createElement("div");
	   element.innerHTML="<iframe src='"+fileurl+"' width=100% ></iframe>";
	   viewholder.appendChild(element);
}
//预览音频文件
resourceDetail.view_audio=function(fileurl){
		jQuery("#preview").append("<center><a id='media' href='"+fileurl+"'></a></center>");
	    jQuery.fn.media.mapFormat('mp3','winmedia');
	    jQuery.fn.media.mapFormat('wav','winmedia');
	    jQuery.fn.media.mapFormat('mid','winmedia');
	    jQuery("#media").media({width:680,height:64});
}
//预览视频文件
resourceDetail.view_video=function(fileurl,ext){
	   jQuery("#preview").append("<a class=\"media\" style='margin:auto' href='"+fileurl+"' ></a>");
	   jQuery.fn.media.mapFormat('mp4','winmedia');
	   jQuery.fn.media.mapFormat('mp4','winmedia');
	   jQuery.fn.media.mapFormat('flv','winmedia');
	   jQuery.fn.media.mapFormat('mpg','winmedia');
	   jQuery.fn.media.mapFormat('3gp','winmedia');
	   resourceDetail.checkplugin(ext);
}
 //预览FLV视频文件
resourceDetail.view_flv=function(fileurl){
	   jQuery("#preview").append("<a href="+fileurl+" style='display:block;width:736px;height:530px' id='player'></a>");
	   jQuery("#preview").append('\<script\>flowplayer("player", THEME_URL+"/js/preview/flowplayer/flowplayer-3.2.16.swf",{clip:{autoPlay: false,autoBuffering: true}});\<\/script\>');
}
//检测插件安装与否sjzhao
resourceDetail.checkplugin=function(extension){
	if(window.navigator.userAgent.indexOf("MSIE")!=-1){//如果是IE浏览器
	     if(resourceDetail.CheckMediaVersion()>11){
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
		if(resourceDetail.isFirefoxWMPPluginInstalled()){
			jQuery(".media").media({width:737,height:530});
			jQuery(".media").css({margin:"auto"});
		}
		else{
			jQuery("#preview").append("<strong>预览该文件需要media player plugin<a href='http://www.interoperabilitybridges.com/windows-media-player-firefox-plugin-download'><span style='color:red;font-size:20px'>点击安装</span></a>,并重启浏览器</strong>");
		}
    }
}
//火狐浏览器插件检测
resourceDetail.isFirefoxWMPPluginInstalled=function(){
	  var plugs = navigator.plugins;
	  for (var i = 0; i < plugs.length; i++) {
		var plugin = plugs[i];
		if (plugin['filename'] == 'np-mswmp.dll')
			return true;
	  }
	     return false;
}
//IE浏览器插件检测
resourceDetail.CheckMediaVersion=function(){
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
resourceDetail.view_office=function(url){
	jQuery("#preview").css("height","517px");
    jQuery("#preview").css("width","736px");
    var fp = new FlexPaperViewer(
	 THEME_URL+'/js/preview/flexpaper/FlexPaperViewer',
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
resourceDetail.view_flash=function(url){
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
     swfobject.embedSWF(url, "preview", "739", "530", "9.0.0", THEME_URL+"/js/preview/flexpaper/expressInstall.swf", flashvars, params, attributes);
}

// 下载
resourceDetail.event = function(){	
	jQuery("#download").click(function(){
		 resourceDetail.operate = "download";
		 location.href = "index.php?app=resselection&mod=Ajax&act=download&id="+resourceDetail.id+"&login="+resourceDetail.uname+"&uid="+resourceDetail.uid+"&filename="+resourceDetail.filename;
	  }); 
}
