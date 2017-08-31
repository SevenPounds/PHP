(function(win){
	win.SpaceCount = win.SpaceCount || {};
	var xData ;
	var serData ;
	/**
	 * 根据数据初始化柱状图
	 */
	SpaceCount.initChar = function (xDtas,seriesData,title,subTitle){
		$('#container').highcharts({
	         chart: {
	            type: 'column',
	            marginTop: 45,//图标距离四周的距离值
	        	marginBottom: 50,
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
	             categories: xDtas,
	             labels: {
	            	 style: {
	                     fontStyle : 'italic'//这个是控制斜放的
	                 },
	                 rotation: -90,  
	            	 formatter: function() {
		            	 //获取到刻度值
		            	 var labelVal = this.value;
		            	 //实际返回的刻度值
		            	 var reallyVal = labelVal;
		            	 //判断刻度值的长度
		            	 if(labelVal.length > 3){
			            	 //截取刻度值
			            	 reallyVal = labelVal.substr(0,3);
			            	 return labelVal;
		            	 }else{
		            		 return labelVal ;
		            	 }
	            	 }
	            },
	             
	             title: {
	                 text: '区县',
	              align : 'high'
	             },
	             max : 8
	         },
	         yAxis: {
	             min: 0,
	             title: {
	                 text: '活跃度（%）',
	              align : 'high'
	             }
	         },
	         tooltip: {//鼠标移到图形上时显示的提示框  
		            formatter: function() {  
		                    return '<b>区县 : '+this.x+'</b>'+'<br/><b>'+ this.series.name +' : '+this.y+'%</b>';
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
	             name: '活跃度',
	             data: seriesData
	         }]
	     });
	}
	/**
	 * 根据数据初始化折线图
	 */
	SpaceCount.initLineChar = function (seriesData,title,subTitle){
		 $('#container').highcharts({
			chart: {
	        	marginTop: 45,//图标距离四周的距离值
	        	marginBottom: 45,
	        	marginLeft: 70,
	        	marginRight: 30
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
	        xAxis:{  
	        	 title: {
	 				text:'时间',
	 				align : 'high'
	             },
	             gridLineColor : '#ccc',
	             gridLineWidth: 1,
		  	     tickInterval : 5,
		  	     tickPixelInterval : 50,
		         max : 30
	    	},
	        yAxis: { 
	        //	lineWidth : 1 ,
	            title: {
					text:'活跃度',
					align : 'high'
	            },
	            labels: {
	                style: {
	             	   color: '#048ee9'
	                }
	            }
	        },
	        tooltip: {//鼠标移到图形上时显示的提示框  
	            formatter: function() {  
	                    return '<b>时间 : '+this.x+'</b>'+'<br/><b>'+ this.series.name +' : '+this.y+'</b>';
	            }  
	        },  
	        legend: {
	        	enabled: false
	        },
	        series: [ {
	        	name: '活跃度',
	            color: '#048ee9',
	            type: 'line',
	            x: 1,
	            data: seriesData
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
		   data: {'type':'kjsy'},
		   success: function(data){
			   var d = jQuery.parseJSON(data);
			   xData = d.cates.split(',');
			   serData = d.ser.split(',');
			   for (var i = 0; i < serData.length; i++) {
				   serData[i] = parseInt(serData[i]); //转换数据
			   }
			   if(d.type == 'school'){ //学校管理者初始化折现图
				   SpaceCount.initLineChar(serData,d.title,d.subTitle);
			   }else{ //机构管理者初始化柱状图
				  
				   SpaceCount.initChar(xData,serData,d.title,d.subTitle);
			   }
		   }
		});
	});
})(window);
