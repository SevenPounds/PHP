core.search = {};
core.search._init = function(){
	this.searchKey = '';
	return true;
}
core.search.hideMenu=function(){
	if($('#search_menu').attr('ison') =="yes"){
		return false;
	}else{
		$('#search_menu').hide();
	}
}
core.search.dohide = function(){
	$('#search_menu').hide();
	$('#search_menu').attr('ison','no');
}
core.search.doshow = function(){
	$('#search_menu').show();
	$('#search_menu').attr('ison','yes');
}
core.search.showCurMenu = function(curArgs){
	$('#search_menu').find('li').each(function(){
		if($(this).attr('a') == curArgs.a && $(this).attr('t') == curArgs.t){
			$('#search_cur_menu').html($(this).attr('typename')+'<i class="ico-more"></i>');
		}
	})
}
core.search.doShowCurMenu = function(obj){
	$('#search_cur_menu').html($(obj).attr('typename')+'<i class="ico-more"></i>');
	$('#search_a').val($(obj).attr('a'));
	$('#search_t').val($(obj).attr('t'));
	this.dohide();
}
//初始化下拉项数据
core.search.searchInit = function(obj){
	var _this = this;
	if("undefined" == typeof(this.listdata)){
		$.post(U('public/Search/getSearchList'),{},function(data){
			_this.listdata = data;
		},'json');
	}
	$(obj).keyup(function(){
		core.search.displayList(obj);
	});
}
core.search.displayList = function(obj){
	var str = obj.value.replace(/(^\s*)|(\s*$)/g,"");
	str = str.replace(/<\/?[^>]*>/g,''); //去除HTML tag
    str = str.replace(/[ | ]*\n/g,'\n'); //去除行尾空白
    str = str.replace(/&nbsp;/ig,'');//去掉&nbsp;
    this.searchKey = str;
	if(getLength(this.searchKey)>0){
		var html = '<div class="search-box" id="search-box"><dd id="s_1" class="current" onclick="core.search.dosearch(\'public\',2);" onmouseover="$(this).addClass(\'current\');" onmouseout="$(this).removeClass(\'current\');">搜“<span>'+this.searchKey+'</span>”相关微博&raquo;</dd>'
					+'<dd id="s_2" onclick="core.search.dosearch(\'public\',1);" onmouseover="$(this).addClass(\'current\');" onmouseout="$(this).removeClass(\'current\');">搜“<span>'+this.searchKey+'</span>”相关用户&raquo;</dd>'
					+'<dd id="s_3" onclick="core.search.dosearch(\'public\',3);" onmouseover="$(this).addClass(\'current\');" onmouseout="$(this).removeClass(\'current\');">搜“<span>'+this.searchKey+'</span>”相关标签&raquo;</dd>'
					+'<dd id="s_4" onclick="core.search.dosearch(\'public\',4);" onmouseover="$(this).addClass(\'current\');" onmouseout="$(this).removeClass(\'current\');">搜“<span>'+this.searchKey+'</span>”相关资源&raquo;</dd>'
					+'</div>';
				//+'<dd class="more"><a href="#"" onclick="core.search.dosearch();">点击查看更多结果&raquo;</a></dd>';
	}else{
		var html = '';
	}
	//更新by yuliu2
	$(obj).parent().parent().find('.search-box').remove();
	$(html).appendTo($(obj).parent().parent());
}
//查找数据
core.search.dosearch = function(app,type){
	 var url = '';
	 if(type == 4){
		 //跳转到资源平台检索资源
		 url = Ures('search/SResource/index',new Array('kw='+this.searchKey));
		 window.open(url);
	 }else{
		 url = U('public/Search/index')+'&k='+this.searchKey;
		 if("undefined" != typeof(app)){
		 	url+='&a='+app;
		 }
		 if("undefined" != typeof(type)){
		 	url+='&t='+type;	
		 }
		 location.href = url;
	 }
}