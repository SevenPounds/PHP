(function(win){
	win.Binding = win.Binding || {};
	
	$(document).ready(function(){
		// 展示邮箱更改区域按钮点击事件
		$("#show_change_email").click(function(){
			var display = $("#show_email_binding").css('display');
			if(display == 'none'){
				$("#show_email_binding").slideDown();
			}else{
				$("#show_email_binding").slideUp();
			}
		});
		
		// 展示手机号更改区域点击事件
		$("#show_change_mobile").click(function(){
			var display = $("#show_mobile_binding").css('display');
			if(display == 'none'){
				$("#show_mobile_binding").slideDown();
			}else{
				$("#show_mobile_binding").slideUp();
			}
		});
		
		// 更改邮箱按钮点击事件
		$("#change_email").click(function(){
			Binding.checkEmail();
		});
		
		// 更改手机号按钮点击事件
		$("#change_mobile").click(function(){
			Binding.checkMobile();
		});
		
		// 验证码输入后确认绑定
		$("#submit_email_binding").click(function(){
			var value = $.trim($("#email_new").val());
			var code = $.trim($("#binding_code").val());
			Binding.checkCode('email',value,code);
		});
		
		// 验证码输入后确认绑定
		$("#submit_mobile_binding").click(function(){
			var value = $.trim($("#mobile_new").val());
			var code = $.trim($("#binding_code_mobile").val());
			Binding.checkCode('mobile',value,code);
		});
		
		$("#cancel_change_email").click(function(){
			$("#show_email_binding").slideUp();
			$("#send_email_tip").slideUp();
			$("#show_code_input").slideUp();
			$("#email_new").val('');
			$("#binding_code").val('');
		});
		
		$("#cancel_change_mobile").click(function(){
			$("#show_mobile_binding").slideUp();
			$("#send_mobile_tip").slideUp();
			$("#show_code_input_mobile").slideUp();
			$("#mobile_new").val('');
			$("#binding_code_mobile").val('');
		});
	});
	
	/**
	 * 检查验证码是否正确
	 */
	Binding.checkCode = function(type,value,code){
		if(code == ''){
			ui.error('验证码不能为空！');
		}else if(code.length != 6){
			ui.error('验证码长度为6位！');
		}else{
			$.ajax({
				url : 'index.php?app=public&mod=Account&act=checkCode',
				type : 'post',
				dataType : 'json',
				data : {type : type, code : code, value : value},
				success : function(result){
					if(result.statuscode == '200'){
						ui.success(result.message);
						$(".info_editMail").hide();
						$(".info_wz").hide();
						setTimeout(function(){window.location.reload();},2500);
					}else{
						ui.error(result.message);
					}
				}
			});
		}
	};
	
	/**
	 * 检查邮箱格式和唯一性
	 */
	Binding.checkEmail = function(){
		var email_old = $.trim($("#email_old").html());
		var email_new = $.trim($("#email_new").val());
		if(email_new == ''){
			ui.error('请输入邮箱！');
			return;
		}
		if(email_old == email_new){
			ui.error('新老邮箱相同！');
			return;
		}
		//var reg = /^\w+([-\.]\w+)*@\w+([\.-]\w+)*\.\w{2,4}$/;
		var reg = /^[0-9a-z]([._]?[0-9a-z])+@([0-9a-z][-\w]*[0-9a-z]\.)+[a-z]{2,9}$/;
		var result = reg.test(email_new);
		if(!result){
			ui.error('邮箱格式错误！');
		}else{
			Binding.checkOnly('email', email_new);
		}
	};
	
	Binding.checkMobile = function(){
		var mobile_old = $.trim($("#mobile_old").html());
		var mobile_new = $.trim($("#mobile_new").val());
		if(mobile_new == ''){
			ui.error('请输入新的手机号！');
			return;
		}
		if(mobile_new == mobile_old){
			ui.error('新老手机号相同！');
			return;
		}
		//var mobile = /^(1[0-9])\d{9}$/;
		var mobile = /^1[0-9]{10}$/;
		var result = mobile.test(mobile_new);
		if(!result){
			ui.error('手机格式错误！');
		}else{
			Binding.checkOnly('mobile',mobile_new);
		}
	};
	
	/**
	 * 根据信息类型，检查唯一性
	 * $param type 信息类型：email、mobile
	 */
	Binding.checkOnly = function(type, value){
		$.ajax({
			url : 'index.php?app=public&mod=Account&act=checkOnly',
			type : 'post',
			data : {type : type, value : value},
			dataType : 'json',
			success : function(result){
				if(result.statuscode == '200'){
					if(type == "email"){
						$("#show_code_input").css('display','block');
						$("#binding_code").val('');
						Binding.sendBindingEmail(value);
					}
					if(type == "mobile"){
						$("#show_code_input_mobile").css('display','block');
						$("#binding_code_mobile").val('');
						Binding.sendBindingMobile(value);
					}
				}else{
					ui.error(result.message);
				}
			},
			error : function(msg){
				ui.error('唯一性检查错误！');
			}
		})
	};
	
	/**
	 * 发送绑定邮箱验证码
	 */
	Binding.sendBindingEmail = function(email){
		$.ajax({
			url : 'index.php?app=public&mod=Account&act=sendEmailCode',
			type : 'post',
			data : {email : email},
			dataType : 'json',
			success : function(result){
				if(result.statuscode == '1'){
					$("#show_code_input").css('display','block');
                    $("#binding_code").val('');
					$("#send_email_tip").html(result.message);
					$("#send_email_tip").css('display','block');
				}else{
					ui.error(result.message);
				}
			}
		});
	};
	
	/**
	 * 发送手机绑定验证码
	 */
	Binding.sendBindingMobile = function(mobile){
		$.ajax({
			url : 'index.php?app=public&mod=Account&act=sendMobileCode',
			type : 'post',
			data : {mobile : mobile},
			dataType : 'json',
			success : function(result){
				if(result.statuscode == '1'){
					$("#show_code_input_mobile").css('display','block');
                    $("#binding_code_mobile").val('');
					$("#send_mobile_tip").html(result.message);
					$("#send_mobile_tip").css('display','block');
				}else{
					ui.error(result.message);
				}
			}
		});
	};
})(window);