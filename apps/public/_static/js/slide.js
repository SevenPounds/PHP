$(document).ready(function(){
	$("#slide_ul li").each(function(){
		$(this).click(function(){
			$("#slide_ul li").each(function(){
				$(this).removeClass('active');
			});
			$(this).addClass('active');
		});
	});
	$("#slide_ul li").each(function(){
		$(this).mouseenter(function(){
			$(this).find('a:not(:animated)').animate({'width':'100%'},400);		
		});
	});
	$("#slide_ul li").each(function(){
		$(this).mouseleave(function(){
			$(this).find('a').animate({'width':'2px'},100);
		});
	});
});
