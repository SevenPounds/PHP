/**
 * 页面切换tab
 */
function changeTab(){
	jQuery(".tab li").click(function(){
		var index = jQuery(this).index();
		
		jQuery(".tab .present").addClass("nor3");
		jQuery(".tab .present").removeClass("present");
		
		jQuery(".tab .present2").addClass("nor2");
		jQuery(".tab .present2").removeClass("present2");
		
		var length = jQuery(".tab li").length;
		
		if(index != (length - 1)){
			jQuery(this).removeClass();
			jQuery(this).addClass("present");
		}else{
			jQuery(this).removeClass("nor2");
			jQuery(this).addClass("present2");
		}
		
		for ( var i = 0; i < length; i++) {
			jQuery(".content" + i).css("display","none");
		}
		
		jQuery(".content" + index).css("display","block");
	});
}


jQuery(function(){
	changeTab();
});