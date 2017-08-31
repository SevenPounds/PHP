/****************资源删除********************/
jQuery(function (){
	jQuery("#select_all").change(function(){
		var checked = $(this).attr("checked");
		if(checked){
//			$(".online_tablebot_span").css("display", "block");
			$("[name='res_list']").attr("checked",'true');//全选  
		} else {
//			$(".online_tablebot_span").css("display", "none");
			$("[name='res_list']").removeAttr("checked");//取消全选  
		}
	})
 	$("[name='res_list']").each(function(){
		$(this).click(function(){
			var checked = $(this).attr("checked");
			if(checked){
				$(this).attr("checked",'true');//全选  
			} else {
				$(this).removeAttr("checked");//取消全选  
			}
			var to_delete = false;
			$("[name='res_list'][checked]").each(function(){  
				to_delete = true;
			}) 
//			if(to_delete){
//				$(".online_tablebot_span").css("display", "block");
//			} else {
//				$(".online_tablebot_span").css("display", "none");
//			}
		})
	})
	
	//删除资源
	$("#delete_res").click(function(){
		var _operation = $("#operation").val();
		var rids = new Array();
		var i = 0;
		$("[name='res_list'][checked]").each(function(){ 
			rids[i++] = $(this).val();
		})
		if(rids.length == 0){
			ui.error("未选中任何资源");	
			return;
		} 
		var _confirm_msg = "确认删除所选资源？";
		var _confirm_msg_temp = "";
		//如果是同步资源的话，需要先确认
		if(_operation == "5"){
			//确认该资源是否被评为省市区级优秀资源，给与用户提示
			for(var j = 0; j < rids.length; j++){
				if(typeof toAuditList[rids[j]] != undefined && toAuditList[rids[j]]){
					var  _resource = toAuditList[rids[j]];
					if(_resource.audit_level === 3){
						_resourceLevel = "省优资源";
					}else if(_resource.audit_level === 2){
						_resourceLevel = "市优资源";
					}else if(_resource.audit_level === 1){
						_resourceLevel = "区优资源";
					}
					if(_confirm_msg_temp === ""){
						_confirm_msg_temp = "“" + _resource.title + _resource.suffix + "”是" + _resourceLevel;
					}else{
						_confirm_msg_temp += "，“" + _resource.title + _resource.suffix + "”是" + _resourceLevel;
					}
				}
			}
			//确认该资源是否是文章附件中某一资源，给与用户提示
			$.ajax({
				type: "POST",
				url: U("reslib/Ajax/confirmRes"),
				data:{'opertionids':rids},
				dataType: "json",
				async:false,
				success:function(msg){
					if(msg.status == 1){
						if(_confirm_msg_temp == ""){
							_confirm_msg_temp = msg.msg
						}else{
							_confirm_msg_temp = _confirm_msg_temp + "，" + msg.msg;
						}
					}
				},
				error:function(msg){
					ui.error("删除失败，请稍后再试！");
					return;
				}
			});
		}
		if(_confirm_msg_temp !== ""){
			_confirm_msg = _confirm_msg_temp + "，" + _confirm_msg
		}
		ui.confirmBox("删除资源", _confirm_msg, function(){
				var operation = jQuery("#operation").val();
				$.ajax({
					type: "POST",
					url: U("reslib/Ajax/deleteRes"),
					data:{"uid":MyResLib.uid,'opertionids':rids,'operationtype':operation},
					dataType: "json",
					success:function(msg){
						if(msg.status== 1){
							ui.success("删除成功！");
							MyResLib.refresh();
						} else {
							ui.error("删除失败！");
						}
					},
					error:function(msg){
						ui.error("删除失败，请稍后再试！");
					}
				});
		});
	})
})