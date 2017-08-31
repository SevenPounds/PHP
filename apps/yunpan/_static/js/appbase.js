//当前应用实例对象
var appBase = (function(){
	var _this = {};
	var addressEventList = {};

    // 标记初始化是否完成
    _this.flag = true;

    // 备课本文件夹id
    _this.bkDir = "";
    
    //收藏文件夹id
    _this.scDir="";
    
    //当前用户的是否是教师的角色
    _this.isTeacher = false;  

	//添加queryString改变事件处理程序
	_this.addAdressChangeEvent=function(key,fn){
		var name = fn?key:new Date().getMilliseconds().toString();
		fn = fn||key;
		addressEventList[name] = fn;		
	};

	//移除queryString改变事件处理程序
	_this.removeAdressChangeEvent=function(key){
		if(key){
			delete addressEventList.key;
		}
	};

	//右侧列表
	_this.grid = {};
	//列表工具栏
	_this.gridBar = {};
    //公开列表
	_this.publicgrid={};
	//下载列表
	_this.downloadgrid={};
	
	//设置或添加查询参数
	_this.setQueryString = function(key,value){
		jQuery.address.parameter(key,value);
	};

	//获取查询参数
	_this.getQueryString = function(key){
		var queryString = jQuery.address.queryString();
		queryString = queryString.split("&");
		var result = {};
		for(var i =0,len=queryString.length;i<len;i++){
			var item = queryString[i].split("=");
			var name = item[0];
			var value = item[1];
			result[name] = value;
		}
		return key&&result[key]||result;
	};

	//初始化应用
	_this.init = function(){
		jQuery.address.change(function(){			
			var queryString = appBase.getQueryString();			
			for(var item in addressEventList){
				addressEventList[item](queryString);
			}
		});

	};
	
	return _this;

})();


