(function($,win){	
	
	/**
	 * 关注好友
	 */
	$("#follow").die().live("click",function(){
		var data={
			type:$(this).attr("type"),
			tid:$(this).attr("data-tid")
		};
		var num=parseInt($("#followNum").text());
		$.ajax({
			type:"POST",
			url:"index.php?app=resview&mod=Resource&act=followOrUnfollowUser",
		    data:data,
		    success:function(res){
		    	res=$.parseJSON(res);
		    	if(res.state=="500"){
		    		//用户未登录
		    	 $("#login_popup").trigger('click');
		    	}else if(res.state=="502"){
		    		if(data.type==1){
		    			$("#follow").addClass("already");
		    			$("#follow").attr("type",2);
		    			$("#follow").html("<span>已关注</span>");
		    			$("#followNum").text((num+1));
		    			
		    		}else if(data.type==2){
		    			$("#follow").removeClass("already");
		    			$("#follow").attr("type",1);
		    			$("#follow").html("<span>+&nbsp;关注</span>");
		    			$("#followNum").text((num-1));
		    		}	    		
		    	}else if(res.state=="501"){
		    		alert(res.msg);
		    	}    	
		    },
		    error:function(){
		    	alert("网络连接出错...");
		    }
		});
	});
	
	/**
	 * 点击资源包预览资源分页
	 */
	win.queryPackageResByPage=function(obj){
		var queryParam={};
		queryParam.resid=$("#resid").val();
		queryParam.uid=$("#user_id").val();
		queryParam.packageResPageNow=$(obj).attr("page");
		queryParam.packageResLimit=10;
		 $.ajax({
			  type: "POST",
			  url: "index.php?app=resview&mod=Resource&act=packageResList",
			  data: queryParam,
			  success: function(html){
				$("#packageResList").html(html);
			   },
			   error:function(data){
				   alert("网络连接错误...");
			   }
		  });	
	};

    /**
     * 限制输入字数
     *
     * @param jQuery_obj
     *            jquery对象
     * @param length
     *            输入框限制的长度
     */
    function wordLimit(jQuery_obj, length) {

        var str = jQuery.trim(jQuery_obj.val());

        // 字符串中英文等字符的总长度
        var sum_english = 0;

        // 字符串中汉字等字符的总长度
        var sum_chinese = 0;

        // 在页面中输入的最后一个字符在字符串中的实际位置
        var cursor = 0;

        for ( var i = 0; i < str.length; i++) {

            // 英文字母等数字的长度为0.5,汉字等字符的长度为1
            if ((str.charCodeAt(i) >= 0) && (str.charCodeAt(i) <= 255)) {

                sum_english = sum_english + 0.5;
            } else {

                sum_chinese = sum_chinese + 1;
            }

            if ((sum_chinese + sum_english) > length && cursor == 0) {

                cursor = i;
            }
        }

        if (cursor == 0) {

            // 在页面上显示的字符串的总长度(向上取整)
            var sum = sum_chinese + Math.ceil(sum_english);

            // 改变显示的长度
            jQuery_obj.next().find("b").text(sum);
        }

        // 改变显示的字符数的字体颜色
        if ((sum_chinese + sum_english) >= length) {
            jQuery_obj.next().find("b").css("color", "red");
        } else {
            jQuery_obj.next().find("b").css("color", "#333");
        }

        // 截取字符串
        if (cursor != 0) {

            jQuery_obj.val(str.substring(0, cursor));
        }
    }

    $(function(){
    	 //二维码显示
   	     $(".resource_ewm").mouseover(function(){
    	    $(".wxsm").css("display","block");
    	 
         });
         $(".resource_ewm").mouseout (function(){
    	    $(".wxsm").css("display","none");
    	 
         });
        // 显示分享框
        $('#share_btn').click(function(){
            $.ajax({
                url:'index.php?app=resview&mod=Ajax&act=isLogin',
                type:"POST",
                dateType:"json",
                success:function(data){
                    data = $.parseJSON(data);
                    if(data.status == '500'){
                        $("#login_popup").trigger('click');
                    }else{
                        $('.share_box').show();
                    }
                },
                error:function(msg){
                    ui.error("网络连接错误...")
                }
            });
        });

        // 隐藏分享框
        $("#share_cancel").click(function(){
            $('.share_box').hide();
        });

        // 发布分享微博
        $("#share_publish").click(function(){
            var body = $.trim($("#share_body").val());
            if(body == ''){
                ui.error('请填写内容!');
                return false;
            }
            var rid = $("#hidden_id").val();
            var id =$("#user_id").val();
            $.ajax({
                url:'index.php?app=resview&mod=Ajax&act=share',
                type:"POST",
                dateType:"json",
                data:{body:body,rid:rid,id:id},
                success:function(data){
                    data = $.parseJSON(data);
                    if(data.status == '500'){
                        $("#login_popup").trigger('click');
                    }else if(data.status == '200'){
                        ui.success(data.message);
                        $('.share_box').hide();
                        $("#share_body").val("");
                    }else{
                        ui.error(data.message);
                    }
                }
            });
        });

        $("#share_body").keyup(function() {
            wordLimit(jQuery(this), 140);
        });

        $("#share_body").keydown(function() {
            wordLimit(jQuery(this), 140);
        });

    });
})(jQuery,window);