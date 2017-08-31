var resourceDetail = resourceDetail ||{};

//预览
resourceDetail.preview = function(ext,url){	
	    ext = ext.toLowerCase();
	    if(url!=""){
	    	jQuery("#preview").css("display","block");
	    }
		switch(ext){
		  case "jpg":
		  case "png":
		  case "gif":
		  case "jpeg":
		  case "bmp":
              if(url==""){
                  resourceDetail.nopreview();
              }else{
                  resourceDetail.view_img(url);
              }
		      break;
		  case "mp3":
		  case "wma":
		  case "wav":
		  case "ogg":
		  case "ape":
		  case "mid":
		  case "midi":
              if(url==""){
                  resourceDetail.nopreview();
              }else{
			      resourceDetail.view_audio(url);
              }
			  break;
		  case "flv":
              if(url==""){
                  resourceDetail.nopreview();
              }else{
			      resourceDetail.view_flv(url);
              }
			  break;
		  case "avi":
		  case "mp4":
		  case "wmv":
		  case "3gp":
		  case "mpg":
		  case "rmvb":
		  case "rm":
		  case "asf":
              if(url==""){
                  resourceDetail.nopreview();
              }else{
			    resourceDetail.view_flv(url);
              }
			  break;
		  case "swf":
              if(url==""){
                  resourceDetail.nopreview();
              }else{
			      resourceDetail.view_flash(url);
              }
              break;
		  case "doc":
		  case "docx":
		  case "txt":
		  case "xlsx":
		  case "xls":
		  case "ppt":
		  case "pptx":
		  case "pdf":
              if(url==""){
                  resourceDetail.nopreview();
              }else{
			    resourceDetail.view_office(url);
              }
              break;
		   default :
               jQuery("#preview").css("display","none");
               jQuery("#no_preview").css("display","block");
               jQuery("#no_preview span").html('该格式资源不支持在线预览，请下载后查看');
               break;
		}
	}

resourceDetail.nopreview = function(){
    jQuery("#preview").css("display","none");
    jQuery("#no_preview").css("display","block");
    jQuery("#no_preview span").html('在线预览生成中，您可以下载后查看');
}
	
//预览图片文件
resourceDetail.view_img=function(fileurl){
	   var viewholder=document.getElementById("preview");
       viewholder.innerHTML="";
       viewholder.innerHTML="<img src='"+fileurl+"' style='max-height:500px;max-width:660px;_width:expression(this.width>700?\"700px\":true);_height:expression(this.height>500?\"500px\":true)'></img>";
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
	jQuery('#preview').css({
	        'width' : '600px',
	        'height' : '200px'
	    });
	jQuery("#preview").html('');
	    var url = fileurl;
	    jQuery.getScript("apps/resview/_static/js/flowplayer/flowplayer-3.2.11.min.js",function(){
	        $f("preview", "apps/resview/_static/js/flowplayer/flowplayer-3.2.16.swf", {
	            clip: {	url: url, provider: "audio",coverImage: { url: "apps/resview/_static/js/flowplayer/music.png",
	                scaling: 'orig' }},
	            plugins: {
	                audio:{
	                    url: "apps/resview/_static/js/flowplayer/flowplayer.audio-3.2.11.swf"
	                },
	                controls:{
	                    autoHide:false
	                }
	            }
	        });
	    });
}
//预览视频文件
resourceDetail.view_video=function(fileurl){
	   jQuery("#preview").append("<a href="+fileurl+" style='display:block;width:736px;height:530px' id='player'></a>");
	   jQuery("#preview").append('\<script\>flowplayer("player", "apps/resview/_static/js/flowplayer/flowplayer-3.2.16.swf",{clip:{autoPlay: false,autoBuffering: true}});\<\/script\>');
}
 //预览FLV视频文件
resourceDetail.view_flv=function(fileurl){
	   
	$('#preview').css({ 'height':'530px'}).removeClass("pre_font");
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
resourceDetail.isFirefoxWMPPluginInstalled=function(){
	  var plugs = navigator.plugins;
	  for (var i = 0; i < plugs.length; i++) {
		var plugin = plugs[i];
		if (plugin['filename'] == 'np-mswmp.dll')
			return true;
	  }
	     return false;
 }

resourceDetail.checkplugin=function(extension){
   //以下2012.12.14修改
	if(window.navigator.userAgent.indexOf("MSIE")!=-1){//如果是IE浏览器
	     if(resourceDetail.CheckMediaVersion()>11){
	    	     jQuery(".media").media({width:650,height:450,params:{wmode:"opaque"}});
		         jQuery(".media").css({margin:"auto"});
	     }else{
	    	 if(extension=="asf"||extension=="wmv"){
	    		 jQuery(".media").media({width:650,height:450,params:{wmode:"opaque"}});
			     jQuery(".media").css({margin:"auto"}); 
	    	 }else{
	    		 ui.error("系统检测到您的MediaPlayer版本过低,无法播放该文件");
	    	 }
	     }
		 
    }
	else{//非IE浏览器
		if(resourceDetail.isFirefoxWMPPluginInstalled()){
			jQuery(".media").media({width:650,height:450});
			jQuery(".media").css({margin:"auto"});
		}
		else{
			jQuery("#preview").append("<strong>预览该文件需要media player plugin<a href='http://www.interoperabilitybridges.com/windows-media-player-firefox-plugin-download'><span style='color:red;font-size:20px'>点击安装</span></a>,并重启浏览器</strong>");
		}
    }
   //以上2012.12.14修改
}

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
//	
//    jQuery("#preview").css("height","500px");
//    jQuery("#preview").css("width","700px");
    var fp = new FlexPaperViewer(
	 'apps/'+APPNAME+'/_static/js/flexpaper/FlexPaperViewer',
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
	jQuery("#preview").children().each(function(){
		jQuery(this).css("height","530px");
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
     swfobject.embedSWF(url, "preview", "679", "530", "9.0.0", "apps/resview/_static/js/flexpaper/expressInstall.swf", flashvars, params, attributes);
}