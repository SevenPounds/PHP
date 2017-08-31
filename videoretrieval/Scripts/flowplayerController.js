///<reference path="flowplayer-3.2.11.js" />
/**
 * autho frsun 2014.2.13
 * flowplayer播放器封装
 */

var STREAM_SERVER = VOD_URL; //流媒体服务
var flowplayerCtrl = {	
	init:function(DIV,videoUrl,width,height){  //初始化视频
		 var strRegex = "^((https|http):\/\/)?"  
						 + "(([0-9]{1,3}\.){3}[0-9]{1,3}"
						 + "|"  
						 + "([0-9a-zA-Z\u4E00-\u9FA5\uF900-\uFA2D-]+[.]{1})+[a-zA-Z-]+)"  
						 + "(:[0-9]{1,4})?"
						 + "((/?)|(/[0-9a-zA-Z_!~*'().;?:@&=+$,%#-]+)+/?){1}";  
	     var re = new RegExp(strRegex);  
	     var url = videoUrl.replace(re, "");
	     var playerObj = document.getElementById(DIV);
	     if(width != undefined){
	    	 playerObj.style.width = width+"px";
	     }
	     if(height !=undefined){
	    	 playerObj.style.height = height+"px"; 
	     }	    
		 flowplayer(DIV, "apps/resource/_static/js/flowplayer/flowplayer-3.2.16.swf", {
			    clip: {	url: "flv:"+url, scaling: "fit", provider: "rtmp",autoPlay:false},
				plugins: {
					controls: {
						autoHide: false,
					    height: 41, //功能条高度
					    background:"#171717",
			       		url: "apps/resource/_static/js/flowplayer/flowplayer.controls-3.2.15.swf"
		        	},
					rtmp: {
						url: "apps/resource/_static/js/flowplayer/flowplayer.rtmp-3.2.12.swf",
						netConnectionUrl: STREAM_SERVER
					}
				}  
			});
	},
	load:function(callBackFun){//加载
		
	},
	play:function(){//播放
		flowplayer().play();
	},
	pause:function(){//暂停
		flowplayer().pause();
	},
	stop:function(){//停止
		flowplayer().stop();
	},
	seek:function(seconds){//定位播放
		//flowplayer().seek(seconds);
		
		var state = this.getState();
		if(state==3){
			flowplayer().seek(seconds);
		}else{
			flowplayer().play();
			var _timer = setInterval(function(){
				var _state = flowplayerCtrl.getState();
				if(_state==3){
					clearInterval(_timer);
					flowplayer().seek(seconds);
				}
			},100);
		}
		
	},
	getState:function(){//获取状态
		return flowplayer().getState();
	},
	getTime:function(){//获取时间
		return flowplayer().getTime();
	}	
}