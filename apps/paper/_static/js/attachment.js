(function(win){
	win.Paper = win.Paper || {};
	Paper.attachments = new Array();
	//上传附件 by zhaoliang 2013/11/5
	Paper.popup_uploadattach = function(){
		if(Paper.attachments.length >= 5){
			ui.error("超过最大可上传附件数量");
			return;
		}
		window.attachments = Paper.attachments;
		ui.box.load(U('reslib/Index/upload' +"&sync=true"),'上传资源',function(){
			$(".weibo-file-list").html("");
			//为了避免应用间相互影响，应用加载时清空attachments变量 by zhaoliang 2013/11/8
			Paper.attachments = attachments;
			if(Paper.attachments.length > 0){
				$("#attachlist").css("display","block");
			}
			for(var i = 0; i < Paper.attachments.length; i++){
				//$("#attachments").append('<a style="padding-right:10px;" href="{:U(\'reslib/Ajax/downloadResource\')}&rid='  + attachments[i][0] + '&filename=' + attachments[i][1] +'">' + attachments[i][1] + '</a>');
				var _title = Paper.attachments[i][1];
				var _extension = _title.substr(_title.lastIndexOf('.') + 1);
				var html = "<li id='" + Paper.attachments[i][0] + "'>";
				html += '<i class="ico-' + _extension + '-small"></i>';
				html = html + '<a href="javascript:void(0)" onclick="Paper.deleteattach(\'\',\'' + Paper.attachments[i][2] +  '\',\'' + Paper.attachments[i][0] + '\',\'upload\')" class="ico-close right"></a>';
				if(Paper.attachments[i][2] == "0"){
					downurl = U('widget/Upload/down') + "&attach_id=" + Paper.attachments[i][0];
				}
				else{
					downurl = U('reslib/Ajax/downloadResource') + "&rid=" + Paper.attachments[i][0] + '&filename=' + Paper.attachments[i][1];
				}
				html = html + '<a href = "' + downurl + '">' + Paper.attachments[i][1] + '</a>';
				html = html + "</li>";
				$(".weibo-file-list").append(html);
			}
		});
	}
	
	//删除添加的附件 by zhaoliang
	Paper.deleteattach = function(paperattachid, attachtype, attachid, deletetype){
		//发送ajax请求删除资源网关该资源
		ui.confirmBox("删除资源","确认删除所选资源？",function(){
			//删除paper_attach表的记录
			$.ajax({
				type:"POST",
				url:"index.php?app=paper&mod=Index&act=deleteAttach",
				data:{'uploadtype':attachtype,"id":paperattachid,"rid":attachid, "deletetype":deletetype},
				dataType:"json",
				success:function(msg){
					if(msg != "1"){
						ui.error("删除失败！");
					}
					else{
						$("#" + attachid).remove();
						//删除attament变量
						for(var i=0;i<Paper.attachments.length;i++){
							var attachment = Paper.attachments[i];
							console.log(attachment);
							if(attachment[0] == attachid && attachment[2] == attachtype){
								Paper.attachments.splice(i,1);
								break;
							}
						}
						//window.attachments = Paper.attachments;
						if(Paper.attachments.length == 0){
							$("#attachlist").css("display","none");
						}
					}
				},
				error:function(msg){
					ui.error("删除失败！");
				}
			})
		});
	}
})(window);