/**
 * 名师工作室 基础信息维护页面使用
 * @param gid 工作室ID
 * @author yxxing
 */
function MSConfig(gid){
	
	var _this = this;
	this._gid = gid;
	/**
	 * 响应保存操作
	 */
	this.save = function(){
		var _data = _this._check();
		if(!_data){
			return false;
		}
		$.ajax({
			type: "POST",
			url: U('/Ajax/saveConfig'),
			data: _data,
			dataType: "json",
			success:function(msg){
				if(msg.status == 1){
					ui.success("数据更新成功！");
					setTimeout('location.reload()',2000);
				} else{
					ui.error("更新失败！");	
				}
			},
			error:function(msg){
				ui.error("更新失败！");	
			}
		});
	}
	/**
	 * 响应取消操作
	 */
	this.cancel = function(){
		location.reload();
	}
	/**
	 * 检测输入框是否符合字数限制
	 * @param toCheck 需要检测的Node ID
	 * @param toChange 需要改变字数的Node ID
	 * @param numLimit 字数限制 numLimit
	 */
	this.checkNum = function(toCheck, toChange, numLimit){
		var str = jQuery.trim(jQuery("#" + toCheck).val());
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
			if ((sum_chinese + sum_english) > numLimit && cursor == 0) {
				cursor = i;
			}
		}
		if (cursor == 0) {
			// 在页面上显示的字符串的总长度(向上取整)
			var sum = sum_chinese + Math.ceil(sum_english);
			// 改变显示的长度
			jQuery("#" + toChange).text(sum);
		}
		// 改变显示的字符数的字体颜色
		if ((sum_chinese + sum_english) >= numLimit) {
			jQuery("#" + toChange).css("color", "red");
		} else {
			jQuery("#" + toChange).css("color", "#333");
		}
		// 截取字符串
		if (cursor != 0) {
			jQuery("#" + toCheck).val(str.substring(0, cursor));
		}
	}
	this._check = function(){
		var _name = jQuery.trim(jQuery("#ms_name").val());
		if(_name == ""){
			ui.error("工作室名称不能为空！");
			return false;
		}
        if(_name.length > 20){
            ui.error('工作室名称不能超过20个字！');
            return false;
        }
		var _description = jQuery.trim(jQuery("#description").val());
		if(_description == ""){
			ui.error("工作室描述不能为空！");
			return false;
		}
		
	    var _subject = jQuery.trim(jQuery("#ms_subject").val());
		if(_subject == "" || _subject == "00" || _subject == "0"){
			ui.error("请选择学科信息");
			return false;
		}
		var _data = {};
		_data.gid = gid;
		_data.name = _name;
		_data.description = _description;
		_data.subject = _subject;
		return _data;
	}
}