var appDetail = (function($) {
 var _this={};
   /**
    * 提交评论
    */
  _this.commont=function(obj){
	  var param={};
	  var contentTemp=$("#text_comment").val();
	  param.content=$.trim(contentTemp);
	  param.appid=$("#appid").val();
	  if(param.content==''){
		  ui.error("评论内容不能为空");
		  return;
	  }
	  $.ajax({
		  url:'index.php?app=appcenter&mod=Index&act=comment',
		  data:param,
		  type:'POST',
		  success:function(data){
			  data=$.parseJSON(data);			  
			  if(data.status=='200'){
				  ui.success(data.message);
				  _this.getComments();
			  }else{
				  ui.error(data.message);
			  }
		  },
		  error:function(data){
			  ui.error("请检查网络连接...");
		  }		  
	  });	  
  }	
  
  /**
   * 获取当前应用的评论,默认加载第一页
   */
  _this.getComments=function(){
	 var param={};
	 param.appid=$("#appid").val();
	 param.page=1;
	 $.ajax({
		 url:'index.php?app=appcenter&mod=Index&act=appComments',
		  data:param,
		  type:'POST',
		  success:function(html){
			 $("#comment_list").html(html);	
			 $("#text_comment").val("");
		  },
		  error:function(data){
			  ui.error("请检查网络连接...");
		  }		    
	 });
  }
/**
 * 点击分页按钮获取
 */
  _this.getCommentsByPage=function(obj){
	  var param={};
	  param.appid=$("#appid").val();
	  param.page=$(obj).attr("data-page");
	  $.ajax({
			 url:'index.php?app=appcenter&mod=Index&act=appComments',
			  data:param,
			  type:'POST',
			  success:function(html){
				 $("#comment_list").html(html);				
			  },
			  error:function(data){
				  ui.error("请检查网络连接...");
			  }		    
		 });
  }
  
  /**
   * 点击回复评论按钮
   */
  _this.clickReplayComment=function(id){
	  if($("#"+id).is(":hidden")){
		  $("#"+id).show();
	  }else{
		  $("#"+id).hide(); 
	  }
  }
  
  /**
   * 提交回复内容
   */
  _this.postReplayComment=function(commentid,toLogin){
	  var replay={};
	  replay.commentId=commentid;
	  replay.toLogin=toLogin;
	  replay.appid=$("#appid").val();
	  replay.content=$.trim($("#"+commentid).find("input").val());
	  $.ajax({
			 url:'index.php?app=appcenter&mod=Index&act=comment',
			  data:replay,
			  type:'POST',
			  success:function(data){
				  data=$.parseJSON(data);				 
				  if(data.status=='200'){
					  ui.success(data.message);
					  _this.getComments();
				  }else{
					  ui.error(data.message);
				  }		
			  },
			  error:function(data){
				  ui.error("请检查网络连接...");
			  }		    
		 });
  }
  
  /**
   * 高亮星星
   */
_this.lightStar=function(obj){
	var index=$(obj).index();
	for(var row=0;row<=index;row++){
		$("#star_judge span").eq(row).removeClass("star_no");
		$("#star_judge span").eq(row).addClass("star_all");
	}
	var length=$("#star_judge span").length;
	for(var row=index+1;row<length;row++){
		$("#star_judge span").eq(row).removeClass("star_all");
		$("#star_judge span").eq(row).addClass("star_no");
	}
}

/**
 * 灰星星
 */
_this.grayStar=function(){
	 var size = $("#star_judge span").size();
     for(var i = 0; i < size; i++){
         $("#star_judge span").eq(i).removeClass("star_all");
         $("#star_judge span").eq(i).addClass("star_no");
     }
}

/**
 * 提交评分
 */
_this.postScore=function(obj){
	var index=$(obj).index();
	var score=index+1;
	var appid=$("#appid").val();
	$.ajax({
		 url:'index.php?app=appcenter&mod=Index&act=addScore',
		  data:{score:score,appid:appid},
		  type:'POST',
		  success:function(data){
			 data=$.parseJSON(data);
			 if(data.status=='200'){
				 ui.success(data.message);
				 window.location.reload();
			 }else if(data.status=='501'){
				 $('#login_popup').trigger('click');
			 }else{
				 ui.error(data.message);
			 }
		  },
		  error:function(data){
			  ui.error("请检查网络连接...");
		  }		    
	});
	
}
 return _this;
})(jQuery);