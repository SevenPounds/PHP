jQuery(function(){
	// 解决ie下不支持trim()的处理方法
    if(!String.prototype.trim) {
    	  String.prototype.trim = function () {
    	    return this.replace(/^\s+|\s+$/g,'');
    	  };
    }

	// 发布教育快讯
	jQuery("#publish").click(function(){
		
		var title = jQuery("#title").val().trim();
		
		//提交时编辑器需要先执行的方法
		E.sync();
		var content = jQuery("#content").val().trim();

		if(title == "" || content == ""){
			ui.error("标题或内容都不可为空!");
			return;
		}
		
		var attach_ids = Paper.attachments;
		
		jQuery.ajax({
			type:"POST",
			url:U('eduannounce/Education/add'),
			data:{
				title:title,
				content:content,
				attach_ids:attach_ids
			},
			dataType:"text",
			error:function(){
				ui.error("请检查网络连接....");
			},
			success:function(data){
				data = parseInt(data);
				if(data == 1){
					ui.success("发布成功!");
					setTimeout(function() {
						window.location.href= U('eduannounce/Education/index');
					      }, 1500);
					
				}else{
					ui.error("标题或内容都不可为空!");
				}
			}
		});
	});
});