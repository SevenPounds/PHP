var _topic = function(){
	var _this = this;
	this._nav = 0; //默认为最新主题讨论
	this._loading =false;
	this.loadData = function(page){
		var data = _this.getData();
		jQuery.ajax({
			type:"POST",
			url:U('/Index/getResearchList'),
			data:{"data":data,"p":page},
			dataType:"json",
			beforeSend:function(XHR){
				jQuery("#topic_list").html( '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>');
			},
			success:function(msg){
				if(msg.status){
					jQuery("#topic_list").html("").html(msg.data);
//					jQuery("#keyword_txt").val("");
				}else{
					jQuery("#topic_list").html("").html("<p style='padding:20px;'>加载失败，<a href='javascript:topic.init();'>请重试！</a></p>");
				}
			},
			error:function(msg){
				jQuery("#topic_list").html("").html("<p style='padding:20px;'>加载失败，<a href='javascript:topic.init();'>请重试！</a></p>");
			}
		});
	};
	this.init = function(){
		_this.loadData(1);
	};
	this.getData = function(){
		var data = {};
		//根据创建者地区获取主题列表 现注释 by tkwang 2015/3/16
		//		data.subject = jQuery("#subject_list").val();
		//		data.province = jQuery("#province_list").val();
		//		data.city = jQuery("#city_list").val();
		//		data.district = jQuery("#district_list").val();
		data.nav = _this._nav;
		data.tag = jQuery("#selected_tagid").val();
		data.status = jQuery("#status_val").val();
		data.keyword = jQuery("#keyword_txt").val();
		return data;
	};
	//改变板块内容
	this.changeNav  =function(node,nav){
		if(_this._loading){
			return false;
		}
		//防止同一个tab重复点击刷新
		if(_this._nav == nav){
			return;
		}else{
			_this._nav=nav;
		}
		_this._loading =true;
		var parentNode = node.parentNode;
		jQuery('#'+parentNode.id+' li.current').removeClass('current');
		jQuery(node).addClass('current');
		//重置数据
		jQuery("#selected_tagid").val('');
		jQuery("#status_val").val(-1);
		jQuery("#keyword_txt").val('');
		//重置tag
		var tagJquery = jQuery('#hottag_list p.card');
		tagJquery.removeClass('card');
		tagJquery.addClass('card_pre');
		//重置状态
		jQuery('#ul_status li.current').removeClass('current');
		jQuery('#ul_status li:first').addClass('current');
		_this._loading =false;
		_this.loadData(1);
	}
	
	this.search = function(){
		_this.changeStatus(-1);
		_this.loadData(1);
	}
	/**
	 * 回车事件
	 */
	this.enter=function(e){
		if(e.keyCode==13){
			_this.search();
		}
	};
	this.change = function(change_type, change_data){
		switch(change_type){
			case "subject":
				break;
			case "province":
				_this._getAreaList(change_data,"city_list","city");
				break;
			case "city":
				_this._getAreaList(change_data,"district_list","county");
				break;
			case "district":
				break;
	    }
		_this.loadData(1);
	}
	this._getAreaList = function(areaCode, toChange, area_type){
		var _html = "<option value='0'>请选择</option>";
		jQuery.ajax({
			type:"POST",
			url:U('/Index/getAreaList'),
			data:{"areaCode":areaCode,"type":area_type},
			dataType:"json",
			success:function(msg){
				if(msg.status == 1){
					var _areaList = msg.data;
					if(area_type == "province"){
						jQuery("#province_list").html("").html(_html);
						jQuery("#city_list").html("").html(_html);
					}
					if(area_type == "city"){
						jQuery("#district_list").html("").html(_html);
					}
					for(var area in _areaList){
						_html +=  "<option value='" + _areaList[area].code + "'>" + _areaList[area].name + "</option>";
					}
					jQuery("#" + toChange).html(_html);
				}else{
					jQuery("#" + toChange).html(_html);
				}
			},
			error:function(msg){
				jQuery("#" + toChange).html(_html);
			}
		});
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
	}
};