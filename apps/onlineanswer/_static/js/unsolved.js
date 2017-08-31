jQuery(window).load(function(){
	$('#answer_content').keyup(function(){
		wordLimit($(this),140);
	});
	getAnswersByQid(1,1);
	getJoinMember(1);
	var flag = false;
	jQuery(".refer_btnnor").click(function(){
		//防止多次点击提交按钮
		if(flag){
			ui.error("回答正在提交...");
			return;
		}
		flag = true;
		var qid = jQuery("#hidden_qid").val();
		var content = jQuery.trim(jQuery(".add_qustion2 textarea").val());
		if(content == ""){
			ui.error("请输入内容!");
			return;
		}
		
		// 改成ajax提交 xypan 0905
		$.ajax({
			type:"post",
			url:"index.php",
			data:{app:"onlineanswer",
				  mod:"Index",
				  act:"answer",
				  qid:qid,
				  content:content},
			dataType:"text",
			success:function(data){
				data = eval("("+data+")");
				if(data.status == "200"){
					 ui.success("回答成功!");
					 setTimeout(function() {
						 window.location.href="index.php?app=onlineanswer&mod=Index&act=detail&qid="+qid;
		    		 }, 1500);
				}else{
					flag = false;
					ui.error("网络出错,请重新回答!");
				}
			},
			error:function(msg){
				flag = false;
				ui.error("网络异常,请检查网络状态!");
			}
		});
	});
	jQuery("#publish_ans").click(function(){
		//防止多次点击提交按钮
		if(flag){
			ui.error("回答正在提交...");
			return;
		}
		if(!wordLimit($('#answer_content'),140,true)){
			ui.confirmBox('字数限制','回复字数超过140个字，是否截取后发布？',function(){
				wordLimit($('#answer_content'),140);
				_createPost();
			},function(){
				return ;
			});
		}else{
			_createPost();
		}
		
		
	});
	
	
	function _createPost(){
		var qid = jQuery("#hidden_qid").val();
		var content = jQuery.trim(jQuery("#answer_content").val());
		var record_id = jQuery("#talk").attr("tag");
        var online_name = jQuery("p.online_name").html();
		if(content == ""){
			ui.error("请输入内容!");
			return;
		}
		flag = true;
		// 改成ajax提交 xypan 0905
		$.ajax({
			type:"post",
			url:"index.php",
			data:{app:"onlineanswer",
				mod:"Index",
				act:"answer",
				qid:qid,
				content:content,
				record_id:record_id,
                online_name:online_name
			},
			dataType:"text",
			success:function(data){
				data = eval("("+data+")");
				if(data.status == "200"){
					 ui.success("回答成功!");
					 setTimeout(function() {
						 window.location.href="index.php?app=onlineanswer&mod=Index&act=detail&qid="+qid;
		    		 }, 1500);
				}else{
					flag = false;
					ui.error("网络出错,请重新回答!");
				}
			},
			error:function(msg){
				flag = false;
				ui.error("网络异常,请检查网络状态!");
			}
		});
	}
});
