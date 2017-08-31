function wordLimit(jQuery_obj,length){
			
			var str = jQuery_obj.val().trim();
			
			// 字符串中英文等字符的总长度
			var sum_english = 0;
			
			// 字符串中汉字等字符的总长度
			var sum_chinese = 0;
			
			// 在页面中输入的最后一个字符在字符串中的实际位置
			var cursor = 0;
			
			for ( var i = 0; i < str.length; i++) {
				
				// 英文字母等数字的长度为0.5,汉字等字符的长度为1
				if ((str.charCodeAt(i)>=0) && (str.charCodeAt(i)<=255)) {
					 
					sum_english = sum_english + 0.5;
				}else {
					
					sum_chinese = sum_chinese + 1;
				}
				
				if((sum_chinese + sum_english) > length && cursor == 0){
					
					cursor = i;
				}
			}
			
			if(cursor == 0){
				
				// 在页面上显示的字符串的总长度(向上取整)
		    	var sum = 120-(sum_chinese + Math.ceil(sum_english));
		    	
				// 改变显示的长度
		    	$("#num").text(sum);
			}
			
			// 改变显示的字符数的字体颜色
			if((sum_chinese + sum_english) >= length){
				jQuery_obj.next().find("#num").css("color","red");
			}else{
				jQuery_obj.next().find("#num").css("color","#333");
			}
			
			// 截取字符串
			if(cursor != 0){
					
				jQuery_obj.val(str.substring (0,cursor));
			}
		}

			jQuery(function(){
			// 解决ie下不支持trim()的处理方法
		    if(!String.prototype.trim) {
		    	  String.prototype.trim = function () {
		    	    return this.replace(/^\s+|\s+$/g,'');
		    	  };
		    }
		    wordLimit(jQuery("#intro"), 120);
			jQuery("#intro").keyup(function(){
				wordLimit(jQuery(this), 120);
			});
			
			jQuery("#intro").keydown(function(){
				wordLimit(jQuery(this), 120);
			});
			
		});