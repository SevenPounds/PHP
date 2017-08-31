(function(win){
	win.Statistic = win.Statistic || {};
	/**
	 * 切换监管统计数据
	 */
	Statistic.changeRecomType = function(type){
		$.ajax({
			url : 'index.php?app=public&mod=Index&act=getCountData',
			type : 'post',
			data : {type : type},
			success : function(result){
				$("#chars-tab").html(result);
			},
			error : function(msg){
				ui.error("网络错误，请稍候再试！");
			}
		});
	};
	/**
	 * 文档加载完后自动初始化部分
	 */
	$(document).ready(function(){
		$("#sys_recom_nav_sub a").click(function(){
			$("#sys_recom_nav_sub a").removeClass("active");
			$(this).addClass("active");
			var type = $(this).attr("data-type");
			Statistic.changeRecomType(type);
		});
		
		//默认第一次加载资源统计数据
		Statistic.changeRecomType('jxjy');
	});
})(window);