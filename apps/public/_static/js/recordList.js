$(document).ready(function(){	
	/**
	 * 分页查询
	 */
	queryByPageCreate = function(obj){	
		var page=$(obj).attr('page');
		$.ajax({
		  type: "POST",
		  url: "index.php?app=public&mod=Lesson&act=recordListCreate",
		  data: {page:page},
		  success: function(msg){
			  $("#recordList").html(msg);
		   }  
		});
	};
	/**
	 * 分页查询
	 */
	queryByPageSign = function(obj){	
		var page=$(obj).attr('page');
		$.ajax({
		  type: "POST",
		  url: "index.php?app=public&mod=Lesson&act=recordListSign",
		  data: {page:page},
		  success: function(msg){
			  $("#recordList").html(msg);
		   }  
		});
	};
});