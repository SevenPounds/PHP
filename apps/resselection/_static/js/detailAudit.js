var ResourceSelector = ResourceSelector||{};

/**
 * 局部刷新按钮
 */
 ResourceSelector.requestBtn = function(){
	 jQuery.ajax({
			type: "POST",
			url: U('resselection/Index/detail_btn'),
			data:{"id":resourceDetail.id},
			success:function(msg){
				jQuery("#resbtn").html("").html(msg);
			},
			error:function(msg){
				ui.error(msg);
			}
		});
}

/**
 * 审核通过
 * type为1单个资源审核、type为2多个资源审核
 * defualt 为默认选择按钮 1为 精品 2为非精品 
 */
ResourceSelector.Pass=function(ids,type,defualt){
			ResourceSelector.ids = ids;
			ui.box.load(U('resselection/Ajax/auditpass')+"&type=1&optype="+defualt);
}

/**
 * 审核不通过
 * type为1单个资源审核、type为2多个资源审核
 */
ResourceSelector.NotPass=function(ids,type){
	ResourceSelector.ids = ids;
	ui.box.load(U('/Ajax/auditunpass')+"&type=1");
}

/**
 * 评优
 */
ResourceSelector.ShowRecommand =function(ids,optype,type){
	ResourceSelector.ids = ids;
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
	var t = Math.random();
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
    	$.ajax({
    		type: "POST",
    		url: U('/Ajax/rateResource'),
    		dataType:'json',
    		data:{'id':ResourceSelector.ids,'audit_status':1,'prolevel':prolevel,'t':t,'type':type},
    		success:function(content){
    			if(content.status==1){
    				ui.success(content.msg);
    			    ResourceSelector.requestBtn(); 
    			    ui.box.close();
    		    }else{
    		    	ui.success(content.msg);
    		    	ui.box.close();
    		    } 
    		},
    		error:function(content){
    		}
    	});
      	ResourceSelector.ids=[];//记录全选的资源id
    }
    auditPass();
    ui.box.close();
}

/**
 * 确认审核通过
 */
ResourceSelector.AuditPass= function(node,type){
	var bAudit = 1; //是否为审核操作 0 否 1 是 
	var t = Math.random();
    var prolevel = $('input[@name="rescheck_radio"][checked]').val();
    var cause = '';
    ResourceSelector.AuditResource (1,prolevel,type,cause);
}

/**
 * 审核资源 
 */
ResourceSelector.AuditResource = function(status,prolevel,type,cause){
	var t = Math.random();
	$.ajax({
		type: "POST",
		url: U('/Ajax/auditResource'),
		dataType:'json',
		data:{'id':ResourceSelector.ids,'audit_status':status,'prolevel':prolevel,'t':t,'type':type,'cause':cause},
		success:function(content){
			if(content.status==1){
				ui.success(content.msg);
			    ResourceSelector.requestBtn(); 
			    ui.box.close();
		    }else{
		    	ui.success(content.msg);
		    	ui.box.close();
		    } 
		},
		error:function(content){
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
	ResourceSelector.AuditResource (2,prolevel,type,cause);
	
}

/**
 * 取消审核 
 */
ResourceSelector.AuditCancle = function(){
	ResourceSelector.ids=[];//记录全选的资源id
	ui.box.close();
}

jQuery(function () {
	ResourceSelector.requestBtn();
});