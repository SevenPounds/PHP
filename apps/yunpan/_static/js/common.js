//定义一个让弹出层居中函数
function setPosition(sel) {
	var winWidth = $(window).width(), winHeight = $(window).height(), scrollTop = $(
			window).scrollTop(), popWidth = sel.width(), popHeight = sel
			.height(), x, y;
	x = (winWidth - popWidth) / 2;
	y = scrollTop + (winHeight - popHeight) / 2;

	return {
		'x' : x,
		'y' : y
	};
}
// 定义弹出层弹出函数
function popup(sel) {
	var position = setPosition(sel);
	$('#mask').height(Math.max($('body').height(), $(document).height()))
			.show();
	$('#mask').css({
		'opacity' : '0.6',
		'-ms-filter' : 'alpha(opacity=60)',
		'filter' : 'alpha(opacity=60)'
	});
	sel.css({
		'left' : position.x,
		'top' : position.y
	}).show();
}

// 关闭弹出层
function popout(sel) {
	$('#mask').fadeOut();
	$(sel).parent().parent().hide();
	return false;
}

// 点击文件夹
function clickFolder(fid) {
	if (appBase.flag) {
		jQuery.address.autoUpdate(false);
		appBase.setQueryString("keyword", '');
		appBase.setQueryString("p", '');
        appBase.setQueryString("type",'');
		jQuery.address.autoUpdate(true);
        var params = appBase.getQueryString();
        if(fid == params.fid){
            appBase.setQueryString("type",'all');
        }else{
            appBase.setQueryString("fid", fid);
        }
	}
}

function download(fileId, filename) {
	$.ajax({
		url : U('yunpan/Ajax/download'),
		type : "POST",
		data : {
			fileId : fileId
		},
		dataType : 'json',
		success : function(res) {
			if (res.status == 1) {
				window.location.href = res.data + "?filename="
						+ encodeURIComponent(filename);
			} else {
				ui.error(res.info);
			}
		},
		error : function() {
			ui.error('网络出现异常！');
		}
	});
}

/**
 * 获取文件的图标
 */
function getShortImg(extension) {
	var basePath = THEME_URL + "/image/yunpan/icon32X32/";

	switch (extension) {
	case "doc":
	case "docx":
		basePath = basePath + "word.png";
		break;
	case "txt":
		basePath = basePath + "txt.png";
		break;
	case "xls":
	case "xlsx":
		basePath = basePath + "excel.png";
		break;
	case "mp3":
	case "wma":
	case "wav":
	case "ogg":
	case "ape":
	case "mid":
	case "midi":
		basePath = basePath + "video.png";
		break;
	case "jpg":
	case "jpeg":
	case "bmp":
	case "png":
	case "gif":
		basePath = basePath + "img.png";
		break;
	case "swf":
		basePath = basePath + "swf.png";
		break;
	case "asf":
	case "avi":
	case "rmvb":
	case "mp4":
	case "mpeg":
	case "wmv":
	case "flv":
	case "3gp":
		basePath = basePath + "movie.png";
		break;
	case "zip":
	case "rar":
		basePath = basePath + "zip.png";
		break;
	case "card":
		basePath = basePath + "card.png";
		break;
	case "ppt":
	case "pptx":
		basePath = basePath + "ppt.png";
		break;
	case "pdf":
		basePath = basePath + "pdf.png";
		break;
	case "rtf":
		basePath = basePath + "rtf.png";
		break;
	case "page":
		basePath = basePath + "page.png";
		break;
	case "tebk":
	case "mebk":
		basePath = basePath + "ebook.png";
		break;
	default:
		basePath = basePath + "default2.png";
		break;
	}

	return basePath;
}

// 加载资源预览弹出框
function previewBox(ext, fid, filename, uid) {
	ui.box.load(U('yunpan/CloudDisk/previewTempl'), filename, null, {
		'ext' : ext,
		'fid' : fid,
		'filename' : filename,
		'uid' : uid,
		'callback' : function() {
			jQuery(".hd").hide();
		}
	});

}

//资源预览
function previewRes(ext,url){
    
    jQuery("#preview").removeClass("no-resource");
    
	switch(ext){
	  case "jpg":
	  case "png":
	  case "gif":
	  case "jpeg":
	  case "bmp":
		  if(url==""){
			  jQuery("#preview").addClass("no-resource");
		  }else{
		      previewImg(url);
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
			  jQuery("#preview").addClass("no-resource");
		  }else{
			  previewAudio(url);
		  }
		  break;
	  case "flv":
	  case "avi":
	  case "mp4":
	  case "wmv":
	  case "3gp":
	  case "mpg":
	  case "rmvb":
	  case "rm":
	  case "asf":
		  if(url==""){
			  jQuery("#preview").addClass("no-resource");
		  }else{
		      previewFlv(url);
		  }
		  break;
	  case "swf":
		  if(url==""){
			  jQuery("#preview").addClass("no-resource");
		  }else{
		      previewFlash(url);
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
			  jQuery("#preview").addClass("no-resource");
		  }else{
		      previewOffice(url);
		  }
		  break;
	  default :
		  jQuery("#preview").addClass("no-resource");
		  break;
	}
	switch (ext) {
	case "jpg":
	case "png":
	case "gif":
	case "jpeg":
	case "bmp":
		if (url == "") {

		} else {
			previewImg(url);
		}
		break;
	case "mp3":
	case "wma":
	case "wav":
	case "ogg":
	case "ape":
	case "mid":
	case "midi":
		if (url == "") {

		} else {
			previewAudio(url);
		}
		break;
	case "flv":
	case "avi":
	case "mp4":
	case "wmv":
	case "3gp":
	case "mpg":
	case "rmvb":
	case "rm":
	case "asf":
		if (url == "") {

		} else {
			previewFlv(url);
		}
		break;
	case "swf":
		if (url == "") {

		} else {
			previewFlash(url);
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
		if (url == "") {

		} else {
			previewOffice(url);
		}
		break;
	default:

		break;
	}
}

// 预览图片文件
function previewImg(fileurl) {
	var viewholder = document.getElementById("preview");
	viewholder.innerHTML = "";
	viewholder.innerHTML = "<img src='"
			+ fileurl
			+ "' style='max-height:410px;max-width:650px;_width:expression(this.width>650?\"650px\":true);_height:expression(this.height>410?\"410px\":true)'></img>";
}

// 预览音频
function previewAudio(fileurl) {
	jQuery("#preview").append(
			"<center><a id='media' href='" + fileurl + "'></a></center>");
	jQuery.fn.media.mapFormat('mp3', 'winmedia');
	jQuery.fn.media.mapFormat('wav', 'winmedia');
	jQuery.fn.media.mapFormat('mid', 'winmedia');
	jQuery("#media").media({
		width : 600,
		height : 64
	});
}

// 预览FLV视频文件
function previewFlv(fileurl) {
	$('#preview').css({
		'width' : '650px',
		'height' : '400px'
	});
	var strRegex = "^((https|http):\/\/)?" + "(([0-9]{1,3}\.){3}[0-9]{1,3}"
			+ "|"
			+ "([0-9a-zA-Z\u4E00-\u9FA5\uF900-\uFA2D-]+[.]{1})+[a-zA-Z-]+)"
			+ "(:[0-9]{1,4})?"
			+ "((/?)|(/[0-9a-zA-Z_!~*'().;?:@&=+$,%#-]+)+/?){1}";
	var re = new RegExp(strRegex);
	var url = fileurl.replace(re, "");
	$f("preview", "addons/theme/stv1/_static/js/preview/flowplayer/flowplayer-3.2.16.swf", {
		clip: {	url: "flv:"+url, scaling: "fit", provider: "rtmp",autoPlay:false},
		plugins: {
			controls: {
				autoHide: false, 
	       		url: "addons/theme/stv1/_static/js/preview/flowplayer/flowplayer.controls-3.2.15.swf"
        	},
			rtmp: {
				url: "addons/theme/stv1/_static/js/preview/flowplayer/flowplayer.rtmp-3.2.12.swf",
				netConnectionUrl: VOD_URL
			}
		}
	});
}

// 预览office文件
function previewOffice(url) {
	jQuery("#preview").css("height", "450px");
	jQuery("#preview").css("width", "650px");
	var fp = new FlexPaperViewer(
			'addons/theme/stv1/_static/js/preview/flexpaper/FlexPaperViewer',
			'preview', {
				config : {
					SwfFile : url,
					Scale : 1.0,
					ZoomTransition : 'easeOut',
					ZoomTime : 0.5,
					ZoomInterval : 0.2,
					FitPageOnLoad : false,
					FitWidthOnLoad : false,
					FullScreenAsMaxWindow : false,
					ProgressiveLoading : false,
					MinZoomSize : 0.2,
					MaxZoomSize : 5,
					SearchMatchAll : false,
					InitViewMode : 'Portrait',
					PrintPaperAsBitmap : false,
					ViewModeToolsVisible : true,
					ZoomToolsVisible : true,
					NavToolsVisible : true,
					CursorToolsVisible : true,
					SearchToolsVisible : false,
					PrintToolsVisible : false,
					localeChain : 'zh_CN'
				}
			});
	
}

// 预览flash动画
function previewFlash(url) {
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
	swfobject
			.embedSWF(
					url,
					"preview",
					"650",
					"410",
					"9.0.0",
					"addons/theme/stv1/_static/js/preview/flexpaper/expressInstall.swf",
					flashvars, params, attributes);
}

// 点击分类查询
function clickTypeSearch(type) {
	jQuery.address.autoUpdate(false);
	appBase.setQueryString('p', '');
	appBase.setQueryString('keyword', '');
	jQuery.address.autoUpdate(true);
	appBase.setQueryString('type', type);
}

//组装文件请求参数
function createFileQueryParams(){
	var values = $(".single_checked:checked");
    var arr = [];
    var arr1 = [];
    var arr2= [];
    for(var i = 0; i < values.length; i++){
        arr[i] = $(values[i]).attr('data-value');
    }

    for(var i = 0; i < values.length; i++){
        arr1[i] = $(values[i]).attr('data-isdir');
    }
     
    for(var i=0;i< values.length;i++){
    	arr2[i]=$(values[i]).attr("parentfolder");
    }
    var fids = arr.join(',');
    var isdirs =  arr1.join(',');
    var parentfolder=arr2.join(',');
    var queryParams = {
        fid:fids,
        isdir:isdirs,
        parentfolder:parentfolder
    };
    return queryParams;
}

/**
 * 资源状态名称转换
 */
function get_res_status_name(status){
	var status_name="";
	switch(status){		
	  case "2":
		  status_name="审核不通过";
		  break;
	  case "0":
	  case "3":
	  case "4":
		  status_name="待审核";
		  break;
	  case "1":
		  status_name="审核通过";
		  break;		
	  default :
		 status_name="已删除";
		  break;
	}
	return status_name;
}