jQuery(function(){
	// 注册提交修改按钮的点击事件
	jQuery("#editanswer").live('click',function(){
		var alter_content = jQuery.trim(jQuery("#txl_describe").val());
		var qid = jQuery("#answer_qid").val();
		var ansid = jQuery("#ansid").val();
		
		if(alter_content == ""){
			alert("请输入修改的内容!");
			return;
		}
		
		// 改成ajax提交 xypan 0905
		$.ajax({
			type:"post",
			url:"index.php",
			data:{app:"onlineanswer",
				  mod:"Index",
				  act:"alterAnswer",
				  qid:qid,
				  ansid:ansid,
				  content:alter_content},
			dataType:"text",
			success:function(data){
				data = eval("("+data+")");
				if(data.status == "200"){
					 ui.box.close();
					 ui.success("修改成功!");
					 setTimeout(function() {
						 window.location.href="index.php?app=onlineanswer&mod=Index&act=detail&qid="+qid;
		    		 }, 1500);
				}else{
					ui.error("网络异常,请检查网络状态!");
				}
			},
			error:function(msg){
				ui.error("网络异常,请检查网络状态!");
			}
		});
	});
});
var onLineAnswer = {};
onLineAnswer.adopt = function(qid,aid){
	$.ajax({
		type:"post",
		url:U('onlineanswer/Index/adoptAnswer'),
		data:{qid:qid,ansid:aid},
		dataType:"text",
		success:function(data){
			data = eval("("+data+")");
			if(data.status == "200"){
				 ui.success("采纳成功!");
				 setTimeout(function() {
					window.location.href="index.php?app=onlineanswer&mod=Index&act=detail&qid="+qid;
	    		 }, 1500);
			}else{
				ui.error("出错了，请重新操作!");
			}
		},
		error:function(msg){
			ui.error("网络异常,请检查网络状态!");
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
function wordLimit(jQuery_obj, length,cells) {
	var cell = !((typeof(cells) != undefined) && cells == true );
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
   	if(cell){
		// 改变显示的字符数的字体颜色
		if ((sum_chinese + sum_english) >= length && cell) {
			// 改变显示的长度
			jQuery_obj.next().find("b").text(length);
			jQuery_obj.next().find("b").css("color", "red");
		} else {
			// 在页面上显示的字符串的总长度(向上取整)
			var sum = sum_chinese + Math.ceil(sum_english);
			// 改变显示的长度
			jQuery_obj.next().find("b").text(sum);
			jQuery_obj.next().find("b").css("color", "#333");
		}
    }
	// 截取字符串
	if (cursor != 0 && cell) {
		jQuery_obj.val(str.substring(0, cursor));
	}
	if((sum_chinese + sum_english)>length){
		return false;
	}
	return true;
}