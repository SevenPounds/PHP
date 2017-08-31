(function($,win){
	/**
	 * 点击资源包预览资源分页
	 */
	win.queryPackageResByPage=function(obj){
		var queryParam={};
		queryParam.resid=$("#resid").val();
		queryParam.packageResPageNow=$(obj).attr("page");
		queryParam.packageResLimit=10;		
		 $.ajax({
			  type: "POST",
			  url: "index.php?app=changyan&mod=Rescenter&act=packageResList",
			  data: queryParam,
			  success: function(html){
				$("#packageResList").html(html);
			   },
			   error:function(data){
				   alert("网络连接错误...");
			   }
		  });	
	};
})(jQuery,window);