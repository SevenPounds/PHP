/**
 * @classDescription 个人账户添加学科信息 
 * @since 2014/3/24
 * @author chengcheng3
 */
var Xueke = (function (){
	var flag  = 1, num_limit = 5;
	
	return {
		more : function(obj) {
			if(num_limit == 1){
				return ;
			}
			var new_xueke = $($("li[name*='xueke_list']").get(0)).clone();
			var flag = $("li[name*='xueke_list']").size();
	
			if (num_limit <= flag) {
				$(obj).parent().hide();
				return;
			}
			
			new_xueke.find('select').get(0).selectedIndex=0;
			new_xueke.find('select').get(1).selectedIndex=0;
			new_xueke.show();
	
			$("li[name*='xueke_list']").last().after(new_xueke);
	
			this.removeNode();
			this.resort();
	
			flag++;
			if (num_limit <= flag) {
				$(obj).hide();
				return;
			}
	
		},
		
		resort : function() {
			var size = $("li[name*='xueke_list']").size();
			if (size <= 1) {
				//最少保留1个
				$('.xueke_delete').hide();
			} else {
				$('.xueke_delete').show();
			}
		},
		
		removeNode : function() {
			var self =this;
			$('.xueke_delete').live('click',function() {
				$(this).parent().remove();
				self.resort();
	
				var size = $("li[name*='xueke_list']").size();
				if (size < num_limit) {
					$('.form_row_addbtn').show();
				}
			});
            var size = $("li[name*='xueke_list']").size();
            if (size < num_limit) {
                $('.form_row_addbtn').show();
            }
		},
		
		/**
		 * 初始化学科数
		 * 默认为添加5个学科
		 */
		init : function(limit){
			num_limit = limit || 5;
			this.removeNode();
			this.resort();
		}
	}
})();