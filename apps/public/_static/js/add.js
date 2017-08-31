var jcrop_api;
//选择的好友id数组
var select = new Array();
function initJcrop(){
  var boundx, boundy;
  
  $('#pathImgShow').Jcrop({
    onChange: updatePreview,
    onSelect: updatePreview,
    aspectRatio: 1.64,
    allowResize:true
  },function(){
    var bounds = this.getBounds();
    boundx = bounds[0];
    boundy = bounds[1];
    jcrop_api = this;
  });

  function updatePreview(c)
  {
    if (parseInt(c.w) > 0)
    {
  	  //度图片的大小进行隐藏设置
    $('#picX').val(c.x/boundx);
    $('#picY').val(c.y/boundy);
    $('#picW').val(c.w/boundx);
    $('#picH').val(c.h/boundy);
    var rx = 167 / c.w;
    var ry = 102 / c.h;
      $('#preview').css({
        width: Math.round(rx * boundx) + 'px',
        height: Math.round(ry * boundy) + 'px',
        marginLeft: '-' + Math.round(rx * c.x) + 'px',
        marginTop: '-' + Math.round(ry * c.y) + 'px'
      });
    }
  };

};
$(window).load(function() {
	$('#file_upload').uploadify({
		'auto'     : true,//关闭自动上传
		'removeTimeout' : 0,//文件队列上传完成0秒后删除
		'swf'      : './apps/public/_static/js/Utils/uploadify/uploadify.swf',
		'class':'photo',
		'cancelImg': './apps/public/_static/js/Utils/uploadify/uploadify-cancel.png',
		'uploader' : 'index.php?app=public&mod=Lesson&act=uploadify',
		'formData' : { 'session_id' : '<?php echo session_id();?>' }, 
	    'debug'         :   false, 
		'method'   : 'post',          //方法，服务端可以用$_POST数组获取数据
		'buttonText' : '浏览',//设置按钮文本
		'multi'    : true,//允许同时上传多张图片
		'uploadLimit' : 100,//一次最多只允许上传10张图片
		queueSizeLimit : 100,
		'fileTypeDesc' : 'Image Files',//只允许上传图像
		'fileTypeExts' : '*.jpeg; *.jpg; *.png',//限制允许上传的图片后缀
		'fileSizeLimit' : '15360KB',//限制上传的图片大小
		'queueID ' : 'uploadify-queue',
		'onUploadSuccess' : function(file, transport, response) { //每次成功上传后执行的回调函数，从服务端返回数据到前端
			transport = $.evalJSON(transport);
			if(transport.data.status == 200) {
				if(jcrop_api != undefined) jcrop_api.destroy();
				var data = transport.data.data;
				var filename=data.substring(14,data.length);
				
				
				var nameArr = file.name.split('.');
				var lastName = '';
				for(var i = 0;i<nameArr.length-1;i++){
					if(lastName == ''){
						lastName = nameArr[i] ;
					}else{
						lastName = lastName + '.' + nameArr[i] ;
					}
				}
				//保存上传的图片路径
				$("#previewParent").html("<img id='preview' width='100%' height='100%'/>");
				$('#pathImg').val(data);	
				$('#pathImgShow').show();
				$('#pathImgShow').attr("src",data);
				$('#preview').attr("src",data);
				$('#url_show').val(lastName);
				$('#showImg2').hide();
				initJcrop();
				//将预览图片的div显示出来
				parent.d.height(653);
				$('#whatYou').show();
			}
		},
		'onQueueComplete' : function(queueData) {             //上传队列全部完成后执行的回调函数
		}
	});

		//好友选择框
	$('#custom-headers').multiSelect({
		   selectableHeader: "<div class='custom-header'>好友</div><input type='text' class='search-input' autocomplete='off' placeholder='检索'>",
		  selectionHeader: "<div class='custom-header'>被邀请</div><input type='text' class='search-input' autocomplete='off' placeholder='检索'>",
		  afterInit: function(ms){
		    var that = this,
	        $selectableSearch = that.$selectableUl.prev(),
	        $selectionSearch = that.$selectionUl.prev(),
	        selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
	        selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

		    that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
		    .on('keydown', function(e){
		      if (e.which === 40){
		        that.$selectableUl.focus();
		        return false;
		      }
		    });

		    that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
		    .on('keydown', function(e){
		      if (e.which == 40){
		        that.$selectionUl.focus();
		        return false;
		      }
		    });
		  },
		  afterSelect: function(values){
			  select.push(values);
			  this.qs1.cache();
			  this.qs2.cache();
		  },
		  afterDeselect: function(values){
			  select.pop(values);
			  this.qs1.cache();
			  this.qs2.cache();
		  }
		});
});
//关闭选择好友弹出层
function closeUserPop(){
    if(select.toString() == '' || select.toString() == null){
		var selectNum = 0 ;
	}else{
		var selected = select.toString();
		var selectedArr = selected.split(",");
		selectNum = selectedArr.length ;
	}
	if($.trim($('#members').val())!=''){
		if($('#members').val() < selectNum){
			pop.alert('选择的听课人数超过上限');
			return ;
		}
	}else{
		if(20 < selectNum){
			pop.alert('选择的听课人数超过上限');
			return ;
		}
	}
	$('#pop_2').hide();
	$('#maskDiv').hide();
	if(select.toString() == '' || select.toString() == null){
		$('#ids').val('');
		$("#allRights").prop("checked",'checked');
		$("#partRights").prop("checked",null);
		pop.alert("听课权限的'对部分人开放'里没选择好友，将对所有人开放，你确定不选择任何好友吗？");
	}else{
		$('#ids').val(select.toString());
	}
	
}
//关闭选择好友弹出层
function closeUserPop2(id){
     if(select.toString() == '' || select.toString() == null){
		var selectNum = 0 ;
	}else{
		var selected = select.toString();
		var selectedArr = selected.split(",");
		selectNum = selectedArr.length ;
	}
	if($.trim($('#members').val())!=''){
		if($('#members').val() < selectNum){
			pop.alert('选择的听课人数超过上限');
			return ;
		}
	}else{
		if(20 < selectNum){
			pop.alert('选择的听课人数超过上限');
			return ;
		}
	}
	if(select.toString() == '' || select.toString() == null){
		$('#ids').val('');
		$('#pop_22').hide();
		$('#maskDiv2').hide();	
		parent.s.close();
	}else{
		var ids=select.toString();
		var addUser=$('#addUser').val();
		if(addUser==1||addUser=='1'){
			$.ajax({
				  type: "POST",
				  url: "index.php?app=public&mod=Lesson&act=inviteUsers",
				  data:{
					liveId:id,
					ids: ids 
				  },
				  success: function(data){
					 var result=eval("("+data+")");
					 var state=result.data.status;
	 				 var msg=result.data.msg;
	 				 if(state == 400){
	 					 pop.alert(msg);
	 				 }else{
	 					pop.alert(msg,function(){
	 						window.parent.location.reload();
	 					});				
	 				 }		     
				   }  
			});
		}
		$('#addUser').val(0);
	}	
}
function closePopUser(){
	$('#maskDiv').hide();
	$('#pop_2').hide();
}
function closePopUser2(){
	$('#maskDiv2').hide();
	$('#pop_22').hide();
	parent.s.close();
}
//通过学段筛选年级和学科
function getGradesByPhase(value,obj_1,obj_2){
	if(value == ''|| value == null){
		$('#'+obj_1+' > option:gt(0)').remove();
		$('#'+obj_2+' > option:gt(0)').remove();
		$('#publish > option:gt(0)').remove();
		return false;
	}
	$.post('index.php?app=public&mod=Lesson&act=getGradesAndSubjectByPhase', {'phase':value}, function(re){
		var data = $.evalJSON(re);
		var opt = '';
		for(var i=0; i < data['grades'].length; i++) {
			opt += '<option value="'+data['grades'][i]['code']+'" title="'+data['grades'][i]['name']+'">'+data['grades'][i]['name']+'</option>';
		}
		var opt_1 = '';
		for(var i=0; i < data['subjects'].length; i++) {
			opt_1 += '<option value="'+data['subjects'][i]['subCode']+'" title="'+data['subjects'][i]['subName']+'">'+data['subjects'][i]['subName']+'</option>';
		}
		$('#'+obj_1+' > option:gt(0)').remove();
		$('#'+obj_2+' > option:gt(0)').remove();
		$('#publish > option:gt(0)').remove();
		$('#'+obj_1).append(opt);
		$('#'+obj_2).append(opt_1);
	});
}
//通过学科和学段获取出版社信息
function getPublisherBySubject(value,obj_1,obj_2){
	if(value == ''|| value == null){
		$('#'+obj_1+' > option:gt(0)').remove();
		return false;
	}
	$.post('index.php?app=public&mod=Lesson&act=getPublisherBySubjectAndPhase', {'subject':value,'phase':$('#'+obj_2).val()}, function(re){
		var data = $.evalJSON(re);
		var opt = '';
		for(var i=0; i < data.length; i++) {
			opt += '<option value="'+data[i]['pubverCode']+'" title="'+data[i]['pubverName']+'">'+data[i]['pubverName']+'</option>';
		}
		$('#'+obj_1+' > option:gt(0)').remove();
		$('#'+obj_1).append(opt);
	});
}
var popSelect;
//单击选择权限按钮
function selectRadio(obj,type,select){
	if(type != ''){
		$("input[type=radio][value=0]").prop("checked",'checked');
		$('#pop_2').show();
		$('#maskDiv').show();
	}else{
		$("input[type=radio][value=1]").prop("checked",'checked');
		$('#ids').val('');
	}
} 
//已选的好友列表显示和隐藏
function listToggle(obj){
	$('#'+obj).toggle('slow');
}