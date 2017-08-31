/*
 * 文件名: center.js
 * 描述: 提供对网上评课中评课中心的信息获取
 * 
 * 作者: yxxing@iflytek.com
 * 日期：2013/12/06
 */
function LessionAssessment(){
	var _this = this;
	this._subject = "";
	this._province = "";
	this._city = "";
	this._district = "";
	this._keywords = "";
	this._status = "-1";
	this._page = "1";
	this._loading = false;
	this._nav =0; //默认最新评课, 1=>热门评课,2=>精华评课,3=>我关注的评课
	
	/**
	 * 初始化加载数据
	 */
	this.init = function(_province, _city, _district){
		_this._province = _province;
		_this._city = _city;
		_this._district = _district;
//		_this._getAreaList(_province,"city_list","city");
//		_this._getAreaList(_city,"district_list","county");
		var data = _this._getData();
		_this._requestData(data);
	}
	
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
		jQuery("#status_val").val(-1);
		jQuery("#keyword_txt").val('');
		//重置状态
		jQuery('#ul_status li.current').removeClass('current');
		jQuery('#ul_status li:first').addClass('current');
		_this._loading =false;
		_this._page =1;
		_this._reloadData();
	}
	
	/**
	 * 用户改变学科、省份、城市、区县时重新加载数据
	 * @param change_type 操作类型
	 * @param change_data 更新数据
	 */
	this.change = function(change_type, change_data){
		switch(change_type){
			case "subject":
				_this._subject = change_data;
				break;
			case "province":
				_this._province = change_data;
				_this._city = "";
				_this._district = "";
				_this._getAreaList(change_data,"city_list","city");
				break;
			case "city":
				_this._city = change_data;
				_this._district = "";
				_this._getAreaList(change_data,"district_list","county");
				break;
			case "district":
				_this._district = change_data;
				break;
		}
		_this._reloadData();
	}
	/**
	 * 查询时使用
	 */
	this.search = function () {
	    var _keywords = jQuery("#search_key").val();
	    _this._status = '-1';
	    _this._keywords = _keywords;
	    jQuery(".status_box ul li").each(function () {
	        $(this).removeClass('current');
	    });
	    jQuery(".status_box ul li:first").addClass('current');
	    this._page = "1";
	    _this._reloadData();
	}
	/**
	 * 分页时使用
	 */
	this.page = function(page){
		if(page == _this._page){
			return;
		}
		_this._page = page;
		_this._reloadData();
	}
	/**
	 * 按评课状态查找
	 * @param status 状态ID(-1:全部，0：结束，1：进行中)
	 */
	this.changeStatus = function(status){
		if(status == _this._status){
			return;
		}
		_this._status = status;
		jQuery("#status_"+status).parent().children().removeClass("current");
		jQuery("#status_" + status).addClass("current");
		this._page = "1";
		_this._keywords = jQuery("#search_key").val() ;
		_this._reloadData();
	}
	/**
	 * 响应Enter键搜索
	 * @param  Event e 事件 
	 */
    this.enterPress = function(e){ 
        var e = e || window.event;
        if(e.keyCode == 13){
             _this.search();
        }
    }

	this._getAreaList = function(areaId, toChange, area_type){
		var _html = "<option value='0'>请选择</option>";
		if(area_type == "city"){
			jQuery("#city_list").html(_html);
			jQuery("#district_list").html(_html);
		}
		if(area_type == "district"){
			jQuery("#district_list").html(_html);
		}
		if(!areaId){
			return;
		}
		jQuery.ajax({
			type:"POST",
			url:U('/Index/getAreaList'),
			data:{"areaId":areaId,"type":area_type},
			dataType:"json",
			success:function(msg){
				if(msg.status == 1){
					var _areaList = msg.data;
					if(area_type == "city"){
						jQuery("#district_list").html(_html);
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
	}
	this._reloadData = function(){
		var data = _this._getData();
		_this._requestData(data);
	}
	this._requestData = function(data){
		if(_this._loading == true){
			return;
		}
		_this._loading = true;
		$.ajax({
			type:"POST",
			url:U('/Index/getDataList'),
			data:data,
			dataType:"json",
			beforeSend:function(XHR){
				jQuery("#dataList").html( '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>');
			},
			success:function(msg){
				if(msg.status == 1){
					jQuery("#dataList").html(msg.data);
				}else{
					jQuery("#dataList").html("<p style='padding:20px;'>加载失败，<a href='javascript:pingke.init();'>请重试！</a></p>");
				}
			},
			error:function(msg){
				jQuery("#dataList").html("<p style='padding:20px;'>加载失败，<a href='javascript:pingke.init();'>请重试！</a></p>");
			},
			complete:function(XHR, TS){
				_this._loading = false;
			}
		});
	}
	this._getData = function(){
		var _data = {};
		//注释区域信息检索 by tkwang 2015/3/18
		//		_data.subject = typeof(_this._subject) == undefined ? "" : _this._subject;
		//		_data.province = typeof(_this._province) == undefined ? "" : _this._province;
		//		_data.city = typeof(_this._city) == undefined ? "" : _this._city;
		//		_data.district = typeof(_this._district) == undefined ? "" : _this._district;
		_data.keywords = typeof(_this._keywords) == undefined ? "" : _this._keywords;
		_data.status = typeof(_this._status) == undefined ? "" : _this._status;
		_data.p = _this._page;
		_data.nav = _this._nav;
		//		if(_data.province == false){
		//			_data.city = "";
		//			_data.district = "";
		//		}
		//		if(_data.city == false){
		//			_data.district = "";
		//		}
		return _data;
	}
}
