core.dropnotify = {
	_init:function(attrs){
			if(attrs.length == 1){
				return false; // 意思是执行插件 只是为了加载此文件
			} 
			this.init(attrs[1],attrs[2]);
	},
	init:function(dropclass,parentObjId){

		this.dropclass = dropclass;
		this.parentObjId = parentObjId;

		this.close = false;
		var _this = this;

		this.count();
		return false;
	},
	//显示父对象
	dispayParentObj:function(){
		if(this.close == false){
			 $('#'+this.parentObjId).show();
			 $('.'+this.dropclass).show();
		}
	},
	//隐藏
	hideParentObj:function(){
		if("undefined" != typeof(this.parentObjId)){
			$('#'+this.parentObjId).hide();
		}else{
			if($('#'+this.parentObjId).length > 0){
				$('#'+this.parentObjId).hide();	
			}
		}
	},
	//关闭 不在循环显示
	closeParentObj:function(){
		this.close = true;
		this.hideParentObj();
	},
	count:function(){
		var _this = this;

		var noticeTipsText = {
				unread_praise:  '条新赞',
                unread_notify:  L('PUBLIC_SYSTEM_MAIL'),
                unread_atme:    L('PUBLIC_SYSTEM_TAME'),
                unread_comment: L('PUBLIC_SYSTEM_CONCENT'),
                unread_message: L('PUBLIC_SYSTEM_PRIVATE_MAIL'),
                new_folower_count: L('PUBLIC_SYSTEM_FOLLOWING'),
                unread_group_atme: '条群聊@提到我',
                unread_group_comment: '条群组评论'
        };
        var loopCount = '';
		var getCount = function() {
 			
			$.get( U( "widget/UserCount/getUnreadCount" ), function( msg ) {	
				if("undefined" == typeof(msg.data) || msg.status != 1){
					return false;
				}else{
					var txt =msg.data;
					//新粉丝数气泡显示
					if($("#new_follower_total").length>0){
						var follow_count=txt.new_folower_count;
						var follow_class ="one";
						if(follow_count>99){
							follow_count ="99+";
							follow_class ="three";
						}else if(follow_count>9){
							follow_class ="two";
						}
						if(follow_count==0){
							$("#new_follower_total").html('').attr('class','');
						}else{
							$("#new_follower_total").html(follow_count).addClass(follow_class);
						}						
					}
					if(txt.unread_total <= 0){
						_this.hideParentObj();
						return false;
					}else{
						if(txt['new_folower_count']>0 && txt['new_folower_count'] == txt['unread_total']){
							 
						}else{
							_this.dispayParentObj();
						}
						
					}
					$('.'+_this.dropclass).each(function(){
						$(this).find('li').each(function(){
							var name = $(this).attr('rel');
							var num  =  txt[name] ;
							if(num > 0){
								$(this).find('span').html(num +noticeTipsText[name]);
								$(this).show();
							}else{
								$(this).hide();
							}
						});
					});
					$('.'+_this.dropclass+'_system').each(function(){
						$(this).find('li').each(function(){
							var name = $(this).attr('rel');
							var num  =  txt[name] ;
							if(num>0){
								if(num>99){
									num ="99+";
								}
								$(this).find('strong.circle_tip').css('display','inline-block');
								$(this).find('strong.circle_tip font').html(num);
							}else{
								$(this).find('strong.circle_tip').css('display','none');
							}
						});
					});
				}
			},'json');
		};
		// loopCount = setInterval( getCount, 60000 );
		
		getCount();

       
	}
};