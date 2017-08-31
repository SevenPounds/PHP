//发起&参与主题js
var _CAndFTopic = function(type){
	var _this = this;
	this.type = type;//请求发起主题还是参与主题
	this.loadData = function(page){
		var data = _this.getData();
		jQuery.ajax({
			type:"POST",
			url:U('/Index/getCreateFollowTopic'),
			data:{"data":data,"p":page},
			dataType:"json",
			beforeSend:function(XHR){
				jQuery("#cf_list").html( '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>');
			},
			success:function(msg){
				if(msg.status){
					jQuery("#cf_list").html("").html(msg.data);
				    if(data.status == -1){
				    	jQuery("#count_all").html("("+jQuery("#totalcount").val()+")");	
				    }
//					jQuery("#keyword_txt").val("");
				}else{
					jQuery("#cf_list").html("").html("<p style='padding:20px;'>加载失败，<a href='javascript:topic.init();'>请重试！</a></p>");
				}
			},
			error:function(msg){
				jQuery("#cf_list").html("").html("<p style='padding:20px;'>加载失败，<a href='javascript:topic.init();'>请重试！</a></p>");
			}
		});
	};
	this.init = function(){
		_this.loadData(1);
	};
	this.getData = function(){
		var data = {};
		data.type = _this.type;
		data.status = jQuery("#status_val").val();
		data.keyword = jQuery("#keyword_txt").val();
		return data;
	};
	this.search = function(){
		_this.changeStatus(-1);
		_this.loadData(1);
	};
	/**
	 * 回车事件
	 */
	this.enter=function(e){
		if(e.keyCode==13){
			_this.search();
		}
	};
	this.changeStatus = function(id){
		jQuery("#ul_status li").each(function(){
			jQuery(this).attr("class","");
		});
		switch(id){
			case -1:
				jQuery("#status_all").attr("class","current");
				jQuery("#status_val").val(-1);
				break;
			case 1:
				jQuery("#status_ing").attr("class","current");
				jQuery("#status_val").val(1);
				break;
			case 0:
				jQuery("#status_end").attr("class","current");
				jQuery("#status_val").val(0);
				break;
		}
		_this.loadData(1);
	};
};