/** **************资源删除******************* */
jQuery(function() {
	
	  //二维码放开
	  $(".resource_ewm").mouseover(function(){
		 var id= $(this).attr('id');
		 id="wxsm"+id.substr(2,id.length);
     	 $("#"+id).css("display","block");
     	 
      });
	  //二位码隐藏
      $(".resource_ewm").mouseout (function(){
         var id= $(this).attr('id');
    	 id="wxsm"+id.substr(2,id.length);
     	 $("#"+id).css("display","none");
     	 
      });
	
});

