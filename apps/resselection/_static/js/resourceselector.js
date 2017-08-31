var ResourceSelector = ResourceSelector||{};

ResourceSelector.setting = {};
ResourceSelector.isselect = false;//记录全选与否
ResourceSelector.ids=[];//记录全选的资源id

ResourceSelector.init =function (option){
	this.setting.container= '#'+option.container ;//页面加载容器
	this.setting.currentpage= option.currentpage || 0;//记录当前页
	this.setting.locationId = option.locationId ||0;  //学校id 或区域 id 
	this.setting.subject = option.subject || 0; //学科
	this.setting.proLevel = option.proLevel ||0;  //默认0，精品1，非精品2
	this.setting.level =  parseInt(option.level) || 3;  //'{$level}'省、市、县级    精品等级   1，省  2，市 3，县
	this.setting.lock =option.lock || false;//请求发出后锁定，避免重复点击
	this.setting.keyword="";//搜索关键字
	
	this.setting.isloading = false;
	this.setting.preIndex = -1;
	
	if(this.setting.level > 0 && this.setting.level < 3){
		this.setting.audit_status = 1;//审核状态 0未审核，1已经审核
	}else{
		this.setting.audit_status = 0;//审核状态 0未审核，1已经审核
	}
	
	ResourceSelector.initData();
};





ResourceSelector.changeArea=function(node){
	jQuery("#searchkeyword").val("");
	ResourceSelector.setting.keyword="";
	ResourceSelector.setting.locationId = node.getAttribute('cid');
	var parentNode = node.parentNode;	//var childNodes = node.parentNode.childNodes;
	$('#'+parentNode.id+' li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#'+node.id).addClass('current');
	ResourceSelector.initData();
};

ResourceSelector.changeSubject=function(node){
	jQuery("#searchkeyword").val("");
	ResourceSelector.setting.keyword="";
	ResourceSelector.setting.subject= node.getAttribute('code');
	var parentNode = node.parentNode;
	$('#'+parentNode.id+' li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#'+node.id).addClass('current');
	ResourceSelector.initData();
};

/**
 * 切换遴选状态
 */
ResourceSelector.changeStatus=function(node,type){
	
	var index = $(node).index();
	
	if(ResourceSelector.setting.isloading){
		//ui.error('正在加载数据，请稍等……');
		return ;
	}
	
	if( ResourceSelector.setting.preIndex == index ){
		return ;
	}else {
		ResourceSelector.setting.preIndex = index;
	}
	
	
	jQuery("#searchkeyword").val("");
	ResourceSelector.setting.keyword="";
	ResourceSelector.setting.audit_status = type;
	ResourceSelector.setting.proLevel = 0;
	var parentNode = node.parentNode;
	$('#'+parentNode.id+' li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#'+node.id).addClass('current');
	ResourceSelector.initData();
};


/**
 * 切换精品类型
 */
ResourceSelector.changeProLevel=function(node,proLevel){
	
	var index = $(node).index();
	
	if(ResourceSelector.setting.isloading){
		//ui.error('正在加载数据，请稍等……');
		return ;
	}
	
	if( ResourceSelector.setting.preIndex == index ){
		return ;
	}else {
		ResourceSelector.setting.preIndex = index;
	}
	
	
	jQuery("#searchkeyword").val("");
	ResourceSelector.setting.keyword="";
	ResourceSelector.setting.proLevel = proLevel;
	var parentNode = node.parentNode;
	$('#'+parentNode.id+' li').each(function(){
		jQuery(this).removeClass('current');	
	});
	jQuery('#'+node.id).addClass('current');
	ResourceSelector.setting.audit_status = 1; 
	ResourceSelector.initData();
};

/**
 * 根据 区域id （学校 ，地区） ，学科code ,审核状态 ，精品类型 ，精品等级 
 */
ResourceSelector.initData = function(){
	ResourceSelector.requestData(1);
};


/**
 * 请求数据
 * @param page页码
 */
ResourceSelector.requestData = function(page){
	var postArgs ={};
	postArgs.lcid = ResourceSelector.setting.locationId;
	postArgs.p = page;
	postArgs.subcode = ResourceSelector.setting.subject;
	postArgs.status = ResourceSelector.setting.audit_status;
	postArgs.prolevel =ResourceSelector.setting.proLevel ;
	postArgs.keyword = ResourceSelector.setting.keyword;
	postArgs.t = Math.random();
	
	

	ResourceSelector.setting.isloading = true;
	var loadingHtml =  '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>';

	$(ResourceSelector.setting.container).html(loadingHtml);
	
	jQuery.ajax({
		type: "POST",
		url: U('resselection/Ajax/getResList'),
		data:postArgs,
		success:function(msg){
			//延时加载
			setTimeout(function(){
				$('#loadMore').remove();
				jQuery("#container").html(msg);
				jQuery("#showresnum").html(jQuery("#resnum").val());
				ResourceSelector.setting.isloading = false;
			},500);
		
			
		},
		error:function(msg){
			//ui.error(msg);
		}
	});
}
/**
 * 全选
 */
ResourceSelector.selectAll=function(){
	ResourceSelector.ids.length=0;
	if(ResourceSelector.isselect){
		jQuery("#content_tab input[type='checkbox']").each(function(){
			var _obj=jQuery(this);
			_obj.attr("checked",false);
		});
		ResourceSelector.isselect=false;
	}else{
		jQuery("#content_tab input[type='checkbox']").each(function(){
			var _obj=jQuery(this);
			_obj.attr("checked",true);
			
		});
		ResourceSelector.isselect=true;
	}
}
/**
 * 审核通过
 * type为1单个资源审核、type为2多个资源审核
 * defualt 为默认选择按钮 1为 精品 2为非精品 3为审核操作
 */
ResourceSelector.Pass=function(ids,type,defualt){
	switch(type){
		case 1:
			ResourceSelector.ids = ids;
			ui.box.load(U('resselection/Ajax/auditpass')+"&type=1&optype="+defualt);
			break;
		case 2:
			jQuery("#content_tab input[type='checkbox']").each(function(){
				var _obj=jQuery(this);
				if(_obj.attr("checked")){
					ResourceSelector.ids.push(this.value);
				}
			});
			if(ResourceSelector.ids.length==0){
				ui.error("没有选择任何资源");
				return;
			}
			ui.box.load(U('/Ajax/auditpass')+"&type=2&optype="+defualt);
			//alert("资源id为"+ids+"的资源审核通过");
			break;
	}
	
}

/**
 * 审核不通过
 * type为1单个资源审核、type为2多个资源审核
 */
ResourceSelector.NotPass=function(ids,type){
	switch(type){
		case 1:
			ResourceSelector.ids = ids;
			ui.box.load(U('/Ajax/auditunpass')+"&type=1");
			//alert("资源id为"+ids+"的资源审核不通过");
			break;
		case 2:
			jQuery("#content_tab input[type='checkbox']").each(function(){
				var _obj=jQuery(this);
				if(_obj.attr("checked")){
					ResourceSelector.ids.push(this.value);
				}
			});
			if(ResourceSelector.ids.length==0){
				ui.error("没有选择任何资源");
				return;
			}
			ui.box.load(U('/Ajax/auditunpass')+"&type=2");
			//alert("资源id为"+ids+"的资源审核不通过");
			break;
	}
		
}


/**
 * 评优
 */
ResourceSelector.ShowRecommand =function(ids,optype,type){
	ResourceSelector.ids = ids;
	if(type==2){
		jQuery("#content_tab input[type='checkbox']").each(function(){
			var _obj=jQuery(this);
			if(_obj.attr("checked")){
				ResourceSelector.ids.push(this.value);
			}
		});
		if(ResourceSelector.ids.length==0){
			ui.error("没有选择任何资源");
			return;
		}
	}
	switch(optype){
		case 1: //取消评优
			ui.box.load(U('/Ajax/cancelRecommand')+"&type="+type);
			break;
		case 2://评为精品
			ui.box.load(U('/Ajax/makeRecommand')+"&type="+type);
			break;
	}
}


/**
 * 取消评优
 */
ResourceSelector.OpratorRecommand =function(optype,node,type){
	var prolevel = 2;
	optype=parseInt(optype);
	switch(optype){
		case 1: //取消评优
			prolevel = 2;
			break;
		case 2://评为精品
			prolevel = 1;
			break;
	}
    var auditPass = function (){
    	var postArgs = {};
    	postArgs.id = ResourceSelector.ids;
    	postArgs.audit_status = 1;
    	postArgs.prolevel = prolevel;
    	postArgs.type = type;
    	postArgs.t = Math.random();
    	
    	$.ajax({
    		type: "POST",
    		url: U('/Ajax/rateResource'),
    		dataType:'json',
    		data:postArgs,
    		success:function(content){
    			if(content.status==1){
    				ui.success(content.msg);
    				ResourceSelector.updatepage();
    			    ResourceSelector.requestData(ResourceSelector.setting.currentpage); 
    			    ui.box.close();
    		    }else{
    		    	ui.error(content.msg);
    		    	ui.box.close();
    		    } 
    		},
    		error:function(content){
    			//ui.error();
    		}
    	});
      	ResourceSelector.ids=[];//记录全选的资源id
    }
    auditPass();
}


/**
 * optype
 *
 * 确认审核通过
 */
ResourceSelector.AuditPass= function(node,type){
	var bAudit = 1; //是否为审核操作 0 否 1 是 
   // var prolevel = parseInt($('input[@name="rescheck_radio"][checked]').val());
    var prolevel = 0;
    $('input[type="radio"][name="rescheck_radio"]').each(function(){
    	if($(this).attr('checked')=='checked'){
    		prolevel = $(this).val();
    	}
	});
    prolevel = parseInt(prolevel);
    var cause = '';
    ResourceSelector.AuditResource (1,prolevel,type,cause);
}


/**
 * 审核资源 
 */
ResourceSelector.AuditResource = function(status,prolevel,type,cause){
	var postArgs = {};
	postArgs.id = ResourceSelector.ids;
	postArgs.audit_status = status;
	postArgs.prolevel = prolevel;
	postArgs.type = type;
	postArgs.cause = cause;
	postArgs.t = Math.random();
	
	$.ajax({
		type: "POST",
		url: U('/Ajax/auditResource'),
		dataType:'json',
		data:postArgs,
		success:function(content){
			if(content.status==1){
				ui.success(content.msg);
				ResourceSelector.updatepage();
			    ResourceSelector.requestData(ResourceSelector.setting.currentpage); 
			    ui.box.close();
		    }else{
		    	ui.error(content.msg);
		    	ui.box.close();
		    }
			ResourceSelector.setting.lock=false;//解除避免重复点击的锁定
		},
		error:function(content){
			//ui.error();
		}
	});
	ResourceSelector.ids=[];//记录全选的资源id
}

/**
 * 确认审核不通过
 */
ResourceSelector.AuditUnPass= function(node,type){
	var bAudit = 1; //是否为审核操作 0 否 1 是
	var result = $("input[name='cause1']:checked").val();
	if(!result){
		ui.error("请选择理由！");
		return;	
	}
	var cause = $("#cause2").val();
	if(cause){
		result = result + "，理由为‘" + cause + "’";
	}
	var prolevel = 0;
	if(ResourceSelector.setting.lock){
		//ui.error("请勿重复点击");
		return;
	}else{
		ResourceSelector.setting.lock = true;
		ResourceSelector.AuditResource (2,prolevel,type,result);
	}
	/*var t=setTimeout(function(){
		
	},1500);*/
}


/**
 * 取消审核 
 */
ResourceSelector.AuditCancle = function(){
	ResourceSelector.ids=[];//记录全选的资源id
	ui.box.close();
}

/**
 * 根据资源名称搜索
 */
ResourceSelector.search=function(){
	ResourceSelector.initkey();
	ResourceSelector.requestData(1);
	/* if(keyword==""){暂时不支持关键字过滤
		ResourceSelector.requestData(1);
	}else{
		ResourceSelector.requestData(1);
	} */
}
/**
 * 初始化搜索关键字
 */
ResourceSelector.initkey=function(){
	ResourceSelector.setting.keyword=jQuery.trim(jQuery("#searchkeyword").val());
}
/*
 * 回车事件
 */
ResourceSelector.enter=function(e){
	if(e.keyCode==13){
		ResourceSelector.search();
	}
}
/**
 * 更新当前页码
 */
ResourceSelector.updatepage=function(){
	ResourceSelector.setting.currentpage=jQuery("#currentpage").val();
}
