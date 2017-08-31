var userList = userList || {};

var arr_tmp = userids!=""?userids.split('|'):new Array();
userList.selectTab = function(obj,name){
	$(obj).parent().children().removeClass("current");
	$(obj).addClass("current");
	$("#"+name+"list").parent().children().css("display","none");
	$("."+name+"list").css("display","block");
};

userList.selectUser=function(userid,username,userpic){
	if(jQuery.inArray((userid+""),arr_tmp)<0){
		arr_tmp.push(userid+"");
		userList.addUser(userid,username,userpic);
	}else{
		alert("该用户已经添加");
	}
};

userList.addUser = function(userid,username,userpic){
	temp = '<li id="user_'+userid+'"  onMouseover="userList.mouseenter(this)" onMouseOut="userList.mouseleave(this)">'+
		     '<p class="cr_pickpic"><img src="'+userpic+'" /></p>'+
		     '<p>'+username+'</p>'+
		     '<div class="delete_member"><img src="apps/research/_static/images/delete_icon.jpg" onclick="userList.deleteUser('+userid+')"/></div>'+
	      '</li>';
	
	$("#selectedlist").append(temp);
	$("#user_ids").val($("#user_ids").val()+userid+'|');
};

userList.mouseenter=function(obj){
	$(obj).addClass("current");
	$(obj).children(".delete_member").css("display","block");
};

userList.mouseleave=function(obj){
	$(obj).removeClass("current");
	$(obj).children(".delete_member").css("display","none");
};

userList.deleteUser = function(userid){	
	var index= jQuery.inArray((userid+""),arr_tmp)
	var ids = $("#user_ids").val();
	arr_tmp.splice(index,1);
	$("#user_"+userid).remove();	
	$("#user_ids").val(ids.replace('|'+userid+'|','|'));
};


jQuery(function(){
	jQuery("#search_btn").click(function(){
		var keywords = jQuery.trim(jQuery("#keywords").val());

		// 如果keywords为空，则不进行查询
		if(!keywords){
			ui.error("请输入查询关键字!");
			return false;
		}
		var data = {};
		data.widget_appname = 'research';
		data.keywords = keywords;

		$.ajax({
			type:"POST",
			url:U('widget/UserList/search'),
			data:data,
			dataType:"text",
			success:function(html){
				$(".sslist").remove();
				$(".cr_tab li").removeClass("current");
				$(".cr_roll").children().css("display","none");
				$(".cr_roll").append(html);
				$("#sslist_tab").addClass("current");
				jQuery("#keywords").val("");
			},
			error:function(msg){
				console.log(msg);
			}
		});
		
	});
});