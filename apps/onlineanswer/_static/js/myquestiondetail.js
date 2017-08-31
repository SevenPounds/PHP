jQuery(function(){
	jQuery(".sumit_btn").click(function(){
		var qid = jQuery(".hidden_qid").val();
		
		//提交时编辑器需要先执行的方法
		E.sync();
		var add_con = jQuery.trim(jQuery("#content").val());
		
		 $.ajax({
		     type: "POST",
		     url: "index.php?app=onlineanswer&mod=Index&act=alterQuestion",
		     data: {content:add_con,
		    	 	qid:qid},
		     success: function(msg){
		    	 msg = eval("("+msg+")");
		    	 if(msg.status == "200"){
		    		 ui.success("修改成功!");
		    		 setTimeout(function() {
		    			 window.location.href="index.php?app=onlineanswer&mod=Index&act=detail&qid="+qid;
		    		 }, 1500);
		    		
		    	 }else{
		    		 ui.error("修改出错,请重新输入!");
		    	 }
		     },
		     error: function(){
		    	 ui.error("请检查网络连接...");
			 }
		  });
		 jQuery(".add_content").css("display","none");
	});
	
	jQuery(".cancel_btn").click(function(){
		jQuery(".add_content").css("display","none");
	});
	
	jQuery("#add_question_later").click(function(){
		jQuery(".add_content").css("display","block");
	});
});