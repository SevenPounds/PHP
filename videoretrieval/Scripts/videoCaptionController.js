///
/**
 * author frsun 2014.2.13
 * 视频检索类库
 */


var videoCaptionCtrl = videoCaptionCtrl || {};
videoCaptionCtrl.data = [];  //视频相关信息
videoCaptionCtrl.keywords = ''; //视频检索关键字
videoCaptionCtrl.interval = null; //timerID
videoCaptionCtrl.retInfo = [];

/**
 * 初始化界面
 * @param divId 框架显示的位置定位DOM的id
 * @param data 数据信息
 */
videoCaptionCtrl.init = function(divId,data){	
	this.data = data;
	this.keywords = jQuery.trim(this.data['keywords']);
	this.initFrame(divId);
	this.showLeftContent();
	this.showRightContent();
	if(this.data['originalText'] ==""){
		jQuery(".button_right").hide();
		jQuery("#left_div").css("width","702px");
		jQuery("#player").css("width","702px");
		jQuery("#right_div").hide();
	}else{
		if(this.keywords==""){
			jQuery(".play_movbox").hide();
			jQuery("#tips").show();
		}
	}
}

/**
 * 初始化框架
 * @param divId 框架的id
 */
videoCaptionCtrl.initFrame = function(divId){
	var title=this.data['title'];
	 var tmp='<div class="pt10 play_box" style="margin-top: 10px;">'
			   +'<h3>{title}</h3>'
			   +'<div class="pt10">'
			     +'<div class="play_movie">'
			       +'<div class="left widebox02" id="left_div">'
			       +'</div>'
			       +'<div class="right narrowbox02 bl_solid" id="right_div">'
			       +'</div>'
			       +'<div class="clear"></div>'
			     +'</div>'
			   +'</div>'
			 +'</div>';
     tmp = tmp.replace(/{title}/, title);
     jQuery("#"+divId).html(tmp);
}

videoCaptionCtrl.initLeftContent = function(){	
    var description = this.data['description'];
    var tmp = ' <div class="movie_con" id="player">'
	            +'</div>'
				+'<div class="button_right button_right02" onclick="videoCaptionCtrl.zoomClick();">'
				   +'<span></span>'
				+'</div>'
				+'<div class="clear"></div>';
    jQuery("#left_div").html(tmp);
}

videoCaptionCtrl.initPlayer = function(){
	var videoUrl =this.data['videoUrl'];
	flowplayerCtrl.init("player",videoUrl,null,null);
}

videoCaptionCtrl.showLeftContent = function(){
	this.initLeftContent();
	this.initPlayer();	
}

videoCaptionCtrl.initRightContent = function(){
	var tmp ='<div class="play_tit">'				
				+'<div class="viode_search">'
				  +'<div class="viode_search_left"></div>'
					+'<div class="viode_search_middle">'
						+'<input type="text" id="inputSearch" style="outline:none" class="iatinput" iatinput="">'
						+'<button onclick="videoCaptionCtrl.search();">在视频内搜索</button>'
					+'</div>'
					+'<div class="viode_search_right"></div>'
					+'<div class="clear"></div>'
				+'</div>'
				+'<div class="clear"></div>'
			+'</div>'
			+'<div class="ser_tips" id="tips" style="display:none">请输入关键字进行视频搜索！</div>'
			+'<div class="play_movbox" style="overflow-x:hidden;overflow-y:auto;height:235px;position:relative">'
				+'<ul style="display: none;" id="segments_div">'
				+'</ul>'
				+'<div class="text_box" id="text_div" style="display: none;">'
				+'</div>'
				+'<div class="clear"></div>'
			+'</div>'
			+'<div class="result_li" style="display:none;">'
			 +'<button id="show_btn" tag="text" class="show_button"	onmouseover="this.className=\'show_button show_button_hover\'" onmouseout="this.className=\'show_button\'" onclick="videoCaptionCtrl.showClick();">显示全文</button>'
			 +'<div id="search_num"></div>'
			 +'</div>';
	jQuery("#right_div").html(tmp);
	setTimeout(function () {
        iatinput.renderInputs(document.getElementById("inputSearch"));
    }, 200);
}


videoCaptionCtrl.showSearchResult = function(){
	var keywords = jQuery.trim(jQuery("#inputSearch").val());
	if(keywords == ""){
		alert("请输入关键词");
		return ;
	}
	this.keywords = keywords;
	jQuery(".play_movbox").show();
	jQuery("#tips").hide();
	jQuery("#segments_div").show();
	jQuery("#text_div").hide();
	this.getSearchContent();
}

videoCaptionCtrl.getSearchContent = function(){
	jQuery("#segments_div").show();
	jQuery("#text_div").hide();
	this.retInfo = [];
	var segments = this.data['segments']
	var len = segments?segments.length:0;
	var regExp = new RegExp(this.keywords, 'gi');
	var htmlShow = [];
	var num = 0;
	for(var i = 0; i < len; i++){
		var content = segments[i]['content'];
		var starttime = segments[i]['starttime'];
		var endtime = segments[i]['endtime'];
		var tmp='<li id="seg_{num}" starttime={starttime} endtime={endtime} onclick="videoCaptionCtrl.playClick(this);" onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'text\'">'
			    	+'<a class="voide_li" href="javascript:;"></a>'
			    	+'<span class="time">{starttime}</span>'
			    	+'{content}'
			    +'</li>';
		if(content.match(regExp)){
			content = content.replace(new RegExp(this.keywords, 'g'),'<span class="redcolor">'+this.keywords+'</span>');
			tmp = tmp.replace(/{num}/g,num);
			tmp = tmp.replace(/{starttime}/g,starttime);
			tmp = tmp.replace(/{endtime}/g,endtime);
			tmp = tmp.replace(/{content}/,content);
		    htmlShow.push(tmp);
		    num++;
		    this.retInfo.push(segments[i]);
		}
	}
	var num = this.getKeywordsNum();
	jQuery("#segments_div").html(htmlShow.join(''));
	jQuery("#search_num").html('共搜索到<span>'+num+'</span>个结果');
}

videoCaptionCtrl.showOriginalText = function(){
	jQuery("#segments_div").hide();
	jQuery("#text_div").show();
	var originalText = this.data['originalText'];
	
	if(this.keywords!=""){
		originalText = originalText.replace(new RegExp(this.keywords, 'g'),'<span class="redcolor">'+this.keywords+'</span>');
	}
	jQuery("#text_div").html('<p>&nbsp;&nbsp;&nbsp;&nbsp;'+originalText+'</p>');
}

videoCaptionCtrl.getKeywordsNum = function(){
	var originalText = this.data['originalText'];
	var keywords = this.keywords;
    var regex= new RegExp(keywords,'gi');
    var arrMatches = originalText.match(regex);
    return arrMatches != undefined ?arrMatches.length:0;   
}


videoCaptionCtrl.showRightContent = function(){	
	this.initRightContent();
	if(this.keywords!=undefined  && this.keywords!=""){
		jQuery("#show_btn").text("显示全文").attr("tag","text");
		this.getSearchContent();
	}else{
		this.showOriginalText();
	}
	
}

//缩放视频宽度
videoCaptionCtrl.zoomPlayer = function(){
	var obj = jQuery(".button_right");
	if(obj.hasClass("button_right02")){
		obj.removeClass("button_right02");
		jQuery("#left_div").css("width","1000px");
		jQuery("#player").css("width","987px");
		jQuery("#right_div").hide();
	}else{		
		jQuery("#left_div").css("width","712px");
		jQuery("#player").css("width","700px");
		obj.addClass("button_right02");
		jQuery("#right_div").show();
	}
}

/**
 * 视频定位文本高亮显示
 */
videoCaptionCtrl.highLightDisplay = function(){
	var currentTime = flowplayerCtrl.getTime();	
	var infoArr = this.retInfo;
	var len = infoArr.length;
	for(var i=0; i<len;i++){		
		var starttime = this.TimeToInt(infoArr[i]['starttime']);
		var endtime = this.TimeToInt(infoArr[i]['endtime']);
		if(currentTime>=starttime && currentTime<=endtime){
			jQuery("#segments_div").children().each(function(){
				jQuery(this).removeClass("selected");	
			});
			//console.log("i:"+i);
		    jQuery("#seg_"+i).addClass("selected");
			return ;
		}else{
			//jQuery("#segments_div").children().each(function(){
			//	jQuery(this).removeClass("selected");	
			//});
		}
	}
}

videoCaptionCtrl.TimeToInt =function(time) {
    time = time.replace("：", ":");
    if (time.indexOf(':') > 0) {
        var timesplit = time.split(':');
        var hours = 0;
        var minute = 0;
        var seconds = 0;
        if (time.indexOf(':') === time.lastIndexOf(':')) {//格式为00:00           
            if (parseInt(timesplit[0]) > 0) {
                minute = parseInt(timesplit[0]);
            }
            seconds = parseInt(timesplit[1]);
        } else {//格式为00:00:00
            if (parseInt(timesplit[0]) > 0)
                hours = parseInt(timesplit[0]);
            if (parseInt(timesplit[1]) > 0)
                minute = parseInt(timesplit[1]);
            seconds = parseInt(timesplit[2]);
        }
        return hours * 3600 + minute * 60 + seconds;
    } else
        return 0;
}



/*------------------events start-----------------------*/
videoCaptionCtrl.search  = function(){
	    //flowplayerCtrl.stop();
	    //clearInterval(this.interval);
		this.showSearchResult();
		//this.interval = setInterval(function(){
		//	videoCaptionCtrl.highLightDisplay();
		//},300);
}

videoCaptionCtrl.showClick = function(){
	jQuery(".play_movbox").show();
	jQuery("#tips").hide();
	var btn_obj = jQuery("#show_btn");
	var mode = btn_obj.attr("tag");
	if(mode=='text' && this.keywords!=""){
		btn_obj.text("显示搜索结果").attr("tag","segment");		
		this.showOriginalText();
	}else if(mode=='segment'){
		btn_obj.text("显示全文").attr("tag","text");
		this.getSearchContent();
	}
}

videoCaptionCtrl.playClick = function(obj){
	var jobj = jQuery(obj);	
	var starttime = jobj.attr("starttime");
	var endtime = jobj.attr("endtime");
	var seconds = this.TimeToInt(starttime);
	//alert(seconds);
	//console.log(starttime);
	flowplayerCtrl.seek(seconds+2);
	clearInterval(this.interval);
	this.interval = setInterval(function(){
		videoCaptionCtrl.highLightDisplay();
	},100);
}

//视频缩放事件
videoCaptionCtrl.zoomClick = function(){
	this.zoomPlayer();
}




/*------------------events end-----------------------*/


videoCaptionCtrl.show = function(divId,dataJson){
	var data = eval('('+dataJson+')');
	this.init(divId,data);
}



