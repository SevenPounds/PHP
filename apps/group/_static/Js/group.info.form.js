group_info = function(){};
group_info.prototype = {
	$input_tags:'',
	init:function()
	{
		this.$input_tags = $('input[name="tags"]');
	},
	text_length:function(o, length)
	{
		$o = $(o);
		if (getLength($o.val()) > length) {
			$('#group_' + $o.attr('name') + '_tips').html('不能超过' + length + '个字');
		} else {
			$('#group_' + $o.attr('name') + '_tips').html('');
		}
	},
	add_tag:function(e)
	{
		var tag = $(e).html().replace(/\s/g, '');
		var tags = this.$input_tags.val();
		if (tags.indexOf(tag) == -1) {
			this.$input_tags.val((tags?(tags.replace(/,$/g, '') + ','):'') + tag);
			this.tag_num();
		}
	},
	tag_num:function()
	{
		var tags	= this.$input_tags.val().split(',');
		var tag_num = tags.length;
		var $tag_change = $('#tags_change');
		var i;
		var _tag_num;
		for (i = 0, _tag_num = 0; i < tag_num; i++) {
			if (tags[i] != '') {
				_tag_num++;
			}
		}
		if (_tag_num > 5) {
			$tag_change.html('添加标签最多可设置5个');
			this.$input_tags.focus();
		} else {
			$tag_change.html('');
		}
		return _tag_num;
	},
	change_verify:function()
	{
	    var date = new Date();
	    var ttime = date.getTime();
	    var url = SITE_URL+'/public/captcha.php';
		$('#verifyimg').attr('src',url+'?'+ttime);
	},
	check_form:function(v_form)
	{
		if(!limtAttach(v_form)){
			return false;
		}
		if (getLength(v_form.name.value) == 0) {
			ui.error("圈子名称不能为空");
			v_form.name.focus();
			return false;
		} else if (getLength(v_form.name.value) > 30) {
			ui.error("圈子名称不能超过30个字");
			v_form.name.focus();
			return false;
		} else if (v_form.cid0.value <= 0) {
			ui.error("请选择圈子分类");
			v_form.cid0.focus();
			return false;
		} else if (getLength(v_form.intro.value) > 200) {
			ui.error("圈子简介不能超过200个字");
			v_form.intro.focus();
			return false;
		} 
		$.post(U('group/Index/code'),{verify:v_form.verify.value},function(data){	 	
			if(data == 1) {
				v_form.submit();
			}
	    	
		});
		return false;	
	}
};
/**
 * 附件类型判断
 add by xmsheng 2014/8/5
 */
 //允许上传的文件类型
extArray = new Array("gif", "jpg", "png");
function limtAttach(form){
  var isAllow=false;
  var file=form.logo.value;
   if(file==""){
	   //没有更换头像，允许上传
	   isAllow=true;
   }else{
	   var lastIndex=file.lastIndexOf(".");
	   var ext=file.substring(lastIndex+1);
	   for(var index=0;index<extArray.length;index++){
		   if(extArray[index]==ext.toLowerCase()){
			   isAllow=true;
			   break
		   }   
	   }	  
   }
   if(!isAllow){
	   jQuery("#logo_waring").text("*请选择gif,jpg,png格式的图片");	 
   }
   return isAllow;
}


group_info = new group_info();