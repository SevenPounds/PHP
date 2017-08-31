(function(win){
	win.timeline = win.timeline || {};
	
	// 按月获取动态模版方法，不同地方调用时，各自获取渲染好的模版
	timeline.getTemplateByMonth;
	
	/**
	 * 初始化滚动事件
	 */
	timeline.initScroll = function(){
		$(window).scroll(function(){
			var scrollTop = $(this).scrollTop();
			if(scrollTop > 500){
				$("#date_tip").css("position","fixed");
			}else{
				$("#date_tip").css("position","static");
			}
			
			$(".month").each(function(){
				var offsetTop = $(this).offset().top;
				var divHeight = $(this).outerHeight(true);
				var height = offsetTop - scrollTop;
				if(scrollTop > offsetTop - 100 && scrollTop < (offsetTop + divHeight - 100)){
					var show = $(this).attr("data-date");
					// 月份选中状态切换
					$(".time_item li").removeClass("cur");
					$("#"+show).parent().addClass('cur');
					
					// 年份选中状态切换
					$(".time_item").removeClass("cur");
					$("#"+show).parent().parent().parent().addClass('cur');
					
					$(".time_item").each(function(){
						if(!$(this).hasClass("cur")){
							$(this).children("ul").slideUp();
						}else{
							$(this).children("ul").slideDown();
						}
					});
				}
			});
		});
	};
	
	$(document).ready(function(){
		var trendsHeight = $('.trends').height();
		if(trendsHeight>600){
			// 初始化滚动事件
			timeline.initScroll();
		}
		
		$("#body_page").css('background','none');
	});
})(window);