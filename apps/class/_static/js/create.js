jQuery(function(){
	// 解决ie下不支持trim()的处理方法
    if(!String.prototype.trim) {
    	  String.prototype.trim = function () {
    	    return this.replace(/^\s+|\s+$/g,'');
    	  };
    }

	// 发布公告
	jQuery("#publish").click(function(){
		
		var title = jQuery("#title").val().trim();

		// 提交时编辑器需要先执行的方法
		editor.sync();
		var content = jQuery("#content").val().trim();
		
		var cid = jQuery("#hidden_cid").val();

		if(title == "" || content == ""){
			ui.error("标题或内容都不可为空!");
			return;
		}
		
		var attach_ids = jQuery("#attach_ids").val();
		
		jQuery.ajax({
			type:"POST",
			url:U('class/CampusHome/addNotice'),
			data:{
				title:title,
				content:content,
				cid:cid,
				attach_ids:attach_ids
			},
			dataType:"text",
			error:function(){
				ui.error("请检查网络连接....");
			},
			success:function(data){
				if(data == "1"){
					ui.success("发布成功!");
					setTimeout(function() {
						window.location.href= "index.php?app=class&mod=CampusHome&act=indexNotice&cid="+cid;
					      }, 1500);
					
				}else{
					ui.error("标题或内容都不可为空!");
				}
			}
		});
	});
});

/**
 * 取消按钮
 */
function closeBtn(){
	ui.box.close();
}