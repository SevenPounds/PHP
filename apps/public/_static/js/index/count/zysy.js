(function(win){
	win.ResCount = win.ResCount || {};
	var serData ;
	/**
	 * 根据数据初始化柱状图
	 */
	ResCount.initChar = function (seriesData,title,subTitle){
		 $('#container').highcharts({
		        chart: {
		        	marginTop: 45,//图标距离四周的距离值
		        	marginBottom: 45,
		        	marginLeft: 80,
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
		        xAxis:{  
		        	 title: {
		 				text:'日期',
		 				align : 'high'
		             },
		             gridLineColor : '#ccc',
		             gridLineWidth: 1,
			  	     tickInterval : 5,
			  	     tickPixelInterval : 50,
			         max : 30,
			         labels: {
			        	 formatter:function() {
			        		 var vDate=new Date();
			        		 var newTime= vDate.getTime()-(30 - this.value)*24*60*60*1000;
			        		 var  new_time =new Date(newTime) ;
		           	         return (new_time.getMonth()+1)+"-"+new_time.getDate();  
			        	 }
           	        }	
		    	},
		        yAxis: { 
		        	//lineWidth : 1 ,
		            title: {
						text:'资源数量(个)',
						align : 'high',
						useHTML : true ,
						rotation : 0 ,
						x: 78,
						y: -15
		            },
		            labels: {
		                style: {
		             	   color: '#048ee9'
		                }
		            }
		        },
		        tooltip: {//鼠标移到图形上时显示的提示框  
		            formatter: function() {  
		            	 var vDate=new Date();
		        		 var newTime= vDate.getTime()-(30 - this.x)*24*60*60*1000;
		        		 var  new_time =new Date(newTime) ;
		                 return '<b>日期 : '+(new_time.getFullYear()+'-'+(new_time.getMonth()+1)+"-"+new_time.getDate())+'</b>'+'<br/><b>'+ this.series.name +' : '+this.y+'</b>';
		            }  
		        },  
		        legend: {
		        	enabled: false
		        },
		        series: [ {
		        	name: '资源数量（个）',
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
		//if($('#userRole').val() != 'teacher'){   //不是学校管理者的管理者身份才会初始化折线图
			$.ajax({
			   type: "POST",
			   url: "index.php?app=public&mod=Index&act=getInitCharData",
			   data: {'type':'zysy'},
			   success: function(data){
				   var d = jQuery.parseJSON(data);
				   console.log(d.series);
				   ResCount.initChar(d.series,d.title,d.subTitle);
			   }
			});
		//	}
	});
})(window);
