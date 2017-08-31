(function(win){
	win.Paper = win.Paper || {};
	Paper.attachments = new Array();
	//var attachments = Paper.attachments;
	// 文档夹在完后自动执行部分
	$(document).ready(function(){
		// 取消发表按钮点击事件
		$("#paper_cancel").click(Paper.cancelPublish);
		
		// 发表论文按钮点击事件
		$("#paper_submit").click(Paper.publishPaper);
		
		$("#body-bg").css("padding-top","26px");
	});
	
	/**
	 * 发表新论文事件
	 */
	Paper.publishPaper = function(){
		var paper = Paper.getPaperInfo();
		var check = Paper.checkPaper(paper);
		if(check != 1){
			ui.error(check);
		}else{
			// 禁用发表按钮
			$("#paper_submit").attr("disabled","disabled");
			$.ajax({
				url : 'index.php?app=paper&mod=Index&act=submitAddPaper',
				type : 'post',
				data : paper,
				success : function(result){
					//alert(result);
					result = eval('(' + result + ')');
					if(result.statuscode == '200'){
						//alert(result.result);
					    sendRec(result.result, paper.type);
						ui.success(result.data);
						setTimeout("Paper.cancelPublish()",2500);
						//location.href = "index.php?app=paper&mod=Index&act=index&type=" + $("#hide_category").val();
					}else{
						ui.error(result.data);
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown){
					//alert(XMLHttpRequest.status);
					ui.error("请检查网络链接...");
				}
			});
		}
	};
	
	/**
	 * 取消发表新论文事件
	 */
	Paper.cancelPublish = function(){
		location.href = "index.php?app=paper&mod=Index&act=index&type=" + $("#hide_category").val();
	};
	
	/**
	 * 获取页面中论文信息
	 */
	Paper.getPaperInfo = function(){
		var paper = {};
		
		paper.title = $("#paper_title").val();
		E.sync();
		paper.type = $("#hide_category").val();
		paper.content = $("#content").val();
		//添加附件 by zhaoliang 2013/11/5
		paper.attachments = Paper.attachments;
		//paper.friendid = $("#paper_recom").val();
		paper.privacyid = $("#paper_privacy").val();
		
		return paper;
	};
	
	/**
	 * 检查论文信息是否完整
	 * @param paper 论文信息对象
	 */
	Paper.checkPaper = function(paper){
		if(paper.title.length > 40){
			return "标题不能超过40字！";
		}
		if(!paper.title || paper.title == "" || $.trim(paper.title) == ""){
			return "标题不能为空！";
		}
		if(!paper.content || paper.content == "" || $.trim(paper.content.replace(/&nbsp;/ig," ")) == ""){
			return "内容不能为空！"
		}
		return 1;
	};
})(window);