(function(win){
	win.TeacCount = win.TeacCount || {};
	var selData ;
	var othData ;
	/**
	 * 根据数据初始化柱状图
	 */
	TeacCount.initChar = function (cates,data_1,title,subTitle,xTitle){
		$('#container').highcharts({
            chart: {
                type: 'column',
                marginTop: 45,//图标距离四周的距离值
	        	marginBottom: 45,
	        	marginLeft: 60,
	        	marginRight: 30,
	        	backgroundColor: '#f4f5f7'
            },
            credits : {
            	enabled : false 
            },
            title: {
                text: title,
                style : {
	                'fontSize' : '12px'
	            }
            },
            subtitle: {
                text: subTitle,
                style : {
	                'fontSize' : '11px'
	            }
            },
            xAxis: {
                categories: cates,
                title: {
                    text: xTitle,
	                align : 'high'
                },
                max : 4
            },
            yAxis: {
                min: 0,
                title: {
                    text: '活动次数',
	                align : 'high'
                }
            },
            tooltip: {//鼠标移到图形上时显示的提示框  
	            formatter: function() {  
	                    return '<b> '+this.series.name+': '+this.x+'</b>'+'<br/><b>活动次数 : '+this.y+'</b>';
	            }  
	        },  
	        legend: {
	        	enabled: false
	        },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                name: xTitle,
                data:data_1
    
            }]
        });
	}
	/**
	 * 文档加载结束就进行柱状图数据加载
	 */
	$(document).ready(function(){
		//加载横坐标的前10名用户 和相应的数据
		$.ajax({
		   type: "POST",
		   url: "index.php?app=public&mod=Index&act=getInitCharData",
		   data: {'type':'jxjy'},
		   success: function(data){
			   var d = jQuery.parseJSON(data);
			   selData = d.sel.split(',');
			   for (var i = 0; i < selData.length; i++) {
				   selData[i] = parseInt(selData[i]); //转换数据
			   }
			   TeacCount.initChar(d.cate.split(','),selData,d.title,d.subTitle,d.xTitle);
		   }
		});
	});
})(window);
