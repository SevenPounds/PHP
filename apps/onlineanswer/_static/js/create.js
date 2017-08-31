/**
 * 验证提交
 */
function check() {
	jQuery("#create_btn").click(function() {
		var title = jQuery.trim(jQuery("#title").val());
		
		//提交时编辑器需要先执行的方法
		E.sync();
		var content = jQuery.trim(jQuery("#content").val());
		var grade = jQuery("#classify_grade").val();
		var subject = jQuery("#classify_subject").val();
		var tag_ids = $("#ts_tag_search_value").val();//标签ids sjzhao
		var to_space;
	    $('input[name="to_space"]:checked').each(function(i){
	        to_space = 1;
	    });
	    var _gids = [];
	    $('input[name="gids"]:checked').each(function(i){
	        _gids[i] = $(this).val();
	    });
		var uid = jQuery("#hidden_uid").val();
		if (title == "") {
			ui.error("请输入问题!");
			return false;
		}

		// 标题总长度
		var sum = 0;

		for ( var i = 0; i < title.length; i++) {

			// 英文字母等数字的长度为0.5,汉字等字符的长度为1
			if ((title.charCodeAt(i) >= 0) && (title.charCodeAt(i) <= 255)) {

				sum = sum + 0.5;
			} else {

				sum = sum + 1;
			}
		}

		// 控制题目的最小长度,在个人中心显示更好看
		if (Math.ceil(sum) < 5) {
			ui.error("你输入的问题长度不够!");
			return false;
		}

		/*if (grade == "") {
			ui.error("请选择年级");
			return false;
		}
		if (subject == "") {
			ui.error("请选择科目");
			return false;
		}*/
		jQuery.ajax({
			type:"POST",
			url:U('onlineanswer/Index/addQuestion'),
			data:{
				title:title,
				content:content,
				uid:uid,
				grade:grade,
				subject:subject,
				gid:_gids,
				to_space:to_space,
				tag_ids:tag_ids
			},
			dataType:"text",
			error:function(){
				ui.error("请检查网络连接....");
			},
			success:function(data){
				data = eval("("+data+")");
				if(data.status == "200"){
					jQuery.ajax({
						type:"POST",
						url:U('onlineanswer/Index/syncQuestionToFeed'),
						data:{
							title:title,
							content:content,
							uid:uid,
							grade:grade,
							subject:subject,
							gid:_gids,
							to_space:to_space,
							tag_ids:tag_ids,
							r:data.data
						},
						dataType:"text",
						error:function(){
						},
						success:function(data){
						}
					});
					ui.success("创建成功!");
					setTimeout(function() {
						window.location.href= "index.php?app=onlineanswer&mod=Index&act=index&nav=1";
					      }, 500);
				}else{
					ui.error("请检查输入内容重新创建!");
				}
			}
		});

	});
}

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

jQuery(function() {

	check();

	jQuery("#title").keyup(function() {
		wordLimit(jQuery(this), 49);
	});

	jQuery("#title").keydown(function() {
		wordLimit(jQuery(this), 49);
	});
});