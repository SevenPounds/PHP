var announce=announce || {};//资讯公告js by sjzhao
announce.param =announce.param || {};//资讯公告参数
(function(announce){
	announce.init = function(){//初始化
		this.requestdata();
	},
	announce.requestdata = function(){//请求数据
		jQuery.ajax({
			type:"post",
			url:U('msgroup/Ajax/getAnnounceList'),
			data:this.param,
			success:function(res){
				
			},
			error:function(){
				
			}
		});
	},
	announce.edit = function(){//编辑
		jQuery.ajax({
			type:"post",
			url:U('msgroup/Ajax/getAnnounceList'),
			data:this.param,
			success:function(res){
				
			},
			error:function(){
				
			}
		});
	},
	announce.del = function(){//删除
		jQuery.ajax({
			type:"post",
			url:U('msgroup/Ajax/getAnnounceList'),
			data:this.param,
			success:function(res){
				
			},
			error:function(){
				
			}
		});
	},
	announce.order = function(){//排序
		jQuery.ajax({
			type:"post",
			url:U('msgroup/Ajax/getAnnounceList'),
			data:this.param,
			success:function(res){
				
			},
			error:function(){
				
			}
		});
	}
})(announce);