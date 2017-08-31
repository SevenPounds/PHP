/**
 * 分享成果js
 * xmsheng
 * 2014/8/13
 */
(function($, win) {
	//分享对象
	win.SHAREFILES = {};
	//资源目录树配置
	win.setting = {
		async : {
			enable : true,
			url : getUrl
		},
		data : {
			key : {
				name : "shortName",
            	title : "name"
			},
			simpleData : {
				enable : true
			}
		},
		callback : {
			onClick : clickTree,
			onAsyncSuccess : onAsyncSuccess,
			onAsyncError : onAsyncError
		}
	};

	/**
	 * 点击树节点，刷新右侧文件里列表
	 */
	function clickTree(event, treeId, treeNode, clickFlag) {
		if ('undefined' != treeNode.fid) {
			loadFileList(treeNode.fid);
		}
	};

	/**
	 *获取后台请求url 
	 */
	function getUrl(treeId, treeNode) {
		var url = "index.php?app=public&mod=ShareResult&act=getDirs";
		if ('undefined' != typeof (treeNode)) {
			url =url+"&fid="+treeNode.fid;
		}
		return url;
	};

	/**
	 * 加载成功
	 */
	function onAsyncSuccess(event, treeId, treeNode, msg) {
		var res = $.parseJSON(msg);
		if(res.fid == wendangFid){
			//调用默认展开第一个结点 
			var zTree = $.fn.zTree.getZTreeObj(treeId);  
			var selectedNode = zTree.getSelectedNodes();                
			var nodes = zTree.getNodes();   
			zTree.expandNode(nodes[0], true); 
			loadFileList(res.fid);
		};
		//判断如果该文件夹目录下没有子目录，刷新右侧文件列表
		if (res.length == 0) {
			loadFileList(treeNode.fid);
		}
	};
	
	/**
	 * 加载异常
	 */
	function onAsyncError() {
		ui.error("请检查网络连接，稍后重试");
	};

	/**
	 * 加载右侧文件列表
	 * dirId文件夹Id
	 */
	function loadFileList(id) {
		$.ajax({   
		    url : "index.php?app=public&mod=ShareResult&act=getFiles&fid="+ id,
			dataType : "JSON",
			success : function(files) {
			  var html = "";
	          for ( var index = 0; index < files.length; index++) {
				  var aliasname = '';
				  if(files[index].aliasname.length > 10){
					  aliasname = files[index].aliasname.substring(0,10) + '...';
				  }else{
					  aliasname = files[index].aliasname;
				  }
				  var tmp_aliasname = files[index].aliasname;
				  tmp_aliasname = tmp_aliasname.replace(/'/g,"&#039;");
					html += "<li fileext='"+files[index].extension
				        +"' fileimg='" + files[index].shortImgUrl
						+ "' filename='" + tmp_aliasname
						+ "'fid=" + files[index].fid
						+ "><a href='javascript:;' title='"
						+ files[index].aliasname + "'><span>"
						+ aliasname
						+ "</span></a></li>";
					 }
				 $(".pop_resourceItem").html(html);
			   },
			error : function(res) {
				ui.error("请检查网络连接，稍后重试");
			  }
		});
      };
	
	/**
	 * 获取书本信息
	 */
	 function loadBook(bookid){		
		$.ajax({
			   url:"index.php?app=public&mod=ShareResult&act=getBookIndex&bookId="+bookid,
			   dataType:"JSON",
			   success:function(res){
				     var units=res.general.resourcedescriptor.units;
				     if(units.length>0){
					 var html="<option value='0'>请选择</option>";
					 for(var index=0;index<units.length;index++){
						 var name = '';
						 if(units[index].Name.length > 8){
							 name = units[index].Name.substr(0,8) + '...';
						 }else{
							 name = units[index].Name;
						 }
						 html +="<option value='"+units[index].Code+"' title='" + units[index].Name + "'>"+
						 	name+"</option>";
					   }
					 $("#unit1").html(html);
					 $("#unit1").show();
				  }
			   },
			   error:function(){
				   ui.error("请检查网络连接，稍后重试");
			  }
		});
	};
	
	/**
	 * 获取目录信息
	 */
	 function loadNodes(queryParam){
		$.ajax({
			   url:"index.php?app=public&mod=ShareResult&act=getNodes",
			   data:queryParam,
			   dataType:"JSON",
			   type:"POST",
			   success:function(res){
				   if(queryParam.node=="book"){
						  //如果是书本
						 $("#book").attr("book-id",res.id);
						 $("#book").attr("book-code",res.code);						
						 loadBook(res.id);
					 }else{
					     $("#"+queryParam.node).html('');
					     if(res.length>0){						  
					    	var html="<option value='0'>请选择</option>";
							for(var index=0;index<res.length;index++){
								var name = '';
								if(res[index].name.length>8){
									name = res[index].name.substr(0,8) + '...';
								}else{
									name = res[index].name;
								}
							    html +="<option value='"+res[index].code+"' title='" + res[index].name + "'>"
								     +name+"</option>"; 					  
							 }
							$("#"+queryParam.node).html(html);
							$("#"+queryParam.node).show();										   
						  }	   
					  }
			   		},
			   error:function(res){
				   ui.error("请检查网络连接，稍后重试");
			   }
		});
	};
	 
	/**
	 *  加载单元以及单元下的子目录
	 */
	 function loadUnits(queryParam){
		 $.ajax({
			   url:"index.php?app=public&mod=ShareResult&act=getUnit",
			   data:queryParam,
			   dataType:"JSON",
			   type:"POST",
			   success:function(res){
				 if(res.length>0){
					 var html="<option value='0'>请选择</option>";
					 for(var index=0;index<res.length;index++){
						 var name = '';
						 if(res[index].Name.length > 8){
							 name = res[index].Name.substr(0,8) + '...';
						 }else{
							 name = res[index].Name;
						 }
						 html +="<option value='"+res[index].Code+"' title='"+res[index].Name+"'>"+
						 	name+"</option>";
					 }
					 if(queryParam.node=="unit1"){
						 $("#unit2").html(html);
						 $("#unit2").show();
					 }else if(queryParam.node=="unit2"){
						 $("#unit3").html(html);
						 $("#unit3").show();
					 }
				 }
			   	},
			   error:function(res){
				   ui.error("请检查网络连接，稍后重试");
			   }				 
		 });
	};
	
	/**
	 * 点击学科
	 */
	$("#subject").live("change",function(){		
			if($(this).val()!='0'){
				cleanSelect("subject");
				var queryParam={};
				queryParam.node="edition";
				queryParam.subject=$(this).val();
				loadNodes(queryParam);
			}							
	});
	
	/**
	 * 点击版本
	 */
	$("#edition").live("change",function(){
		  if($(this).val()!='0'){
			 cleanSelect("edition");
			var queryParam={};
			queryParam.node="stage"; 
			queryParam.subject=$("#subject").val();
			queryParam.edition=$(this).val();
			loadNodes(queryParam);
		  }
	});
	
	/**
	 * 点击年级
	 */
	$("#stage").live("change",function(){
		if($(this).val!="0"){
			cleanSelect("stage");
			var queryParam={};
			queryParam.node="book"; 
			queryParam.subject=$("#subject").val();
			queryParam.edition=$("#edition").val();
			queryParam.stage=$("#stage").val();
			loadNodes(queryParam);
		}
	});
	
	/**
	 * 点击单元下的一层目录
	 */
	$("#unit1").live("change",function(){
		if($(this).val()!='0'){
			cleanSelect("unit1");
			var queryParam={};
			queryParam.node="unit1";
			queryParam.bookId=$("#book").attr("book-id");
			queryParam.unit1_code=$(this).val();
			loadUnits(queryParam);
		}
	});
	
	/**
	 * 点击单元下的二层目录
	 */
	$("#unit2").live("change",function(){
		if($(this).val()!='0'){
			cleanSelect("unit2");
			var queryParam={};
			queryParam.node="unit2";
			queryParam.bookId=$("#book").attr("book-id");
			queryParam.unit1_code=$("#unit1").val();
			queryParam.unit2_code=$(this).val();
			loadUnits(queryParam);
		}
	});
	 
	/**
	 * 点击选择云端按钮
	 */
	$("#choice_cloud_res").live("click", function() {
		//清空上次资源列表数据；
		$(".pop_resourceItem").html("");
		cancleRes();
		//初始化树
		$("#show_chooce_box").trigger("click");
		$.fn.zTree.init($("#myTree"), setting);		
	});

	/**
	 * 选中云端资源
	 */
	$("#pop_resourceItem li").live("click", function() {
		if ($(this).attr("class") == "current") {
			//该资源已被选中
			cancleRes();
			$(this).removeClass("current");			
		} else {
			//该资源尚未被选中
			$("#pop_resourceItem li").removeClass("current");
			SHAREFILES.fid=$(this).attr("fid");
			SHAREFILES.name=$(this).attr("filename");
			SHAREFILES.fileimg=$(this).attr("fileimg");
			SHAREFILES.extension=$(this).attr("fileext");
			$(this).addClass("current");
		}
	});

	/**
	 * 取消选中的某个资源
	 */
	$(".space_pop_process a").live("click", function() {
		var fid = $(this).attr("fid");
		cancleRes();
		$(this).parents("dt").remove();
	});

	/**
	 * 资源选择弹出框，确定按钮
	 */
	$("#sure_select_res").live("click",function() {
		resName=SHAREFILES.name;
		if(SHAREFILES.name.length>30){
			var resName = SHAREFILES.name.substr(0,30)+"...";	
		}		
		var html = "<dl class='space_pop_process'>";
			html += "<dt class='clearfix'>"
					+ "<img width='16px' height='15px' src='"
					+ SHAREFILES.fileimg + "'/>" + "<span>"
					+ resName + "</span>"
					+ "<p><a href=javascript:; fid="
					+ SHAREFILES.fid + ">取消</a></p>" + "</dt>";
			html += "</dl>";
		$("#share_res_list").html(html);
		closeBox("show_space_chooce");
	});

	/**
	 * 发布分享资源
	 */
	$("#space_sure").live("click", function() {
		//提交按钮控制，防止重复提交
		if($(this).attr("is_sub")=="true"){
			$(this).attr("is_sub","false");
		}else{
			return false;
		}
		//默认分享到个人主页
		SHAREFILES.per_page='1';
		//设置资源描述
		if($("#desc").val()=='描述一下您的资源吧'){
			SHAREFILES.desc='';
		}else{
			SHAREFILES.desc=$("#desc").val();	
		}
		
		if(SHAREFILES.sys_res == '1'){
			var result = getSubjectInfo();
			if(!result.status){
				ui.error(result.error);
				$("#space_sure").attr("is_sub","true");
				return false;
			}
		}
		
		if(SHAREFILES.class_share == '1'){
			var ids = getClassIds();
			if(ids == ''){
				ui.error("请选择班级！");
				return false;
			}
		}

//        if(SHAREFILES.class_share == '1'){
//            var classSubject = jQuery('#classSubject').find('select').val();
//            if(classSubject == ''){
//                ui.error("请选择资源所属学科！");
//                return false;
//            }
//            SHAREFILES.classSubject = classSubject;
//        }

		//提交数据校验
		if(!verifyShareData()){
			$(this).attr("is_sub","true");
			return false;
		}
		
		$.ajax({
			url : "index.php?app=public&mod=ShareResult&act=shareRes",
			data : SHAREFILES,
			type : "POST",
			dataType:"JSON",
			success : function(msg) {
				if(msg.status=='200'){
					if(typeof(msg.score)!='undefined'){
						ui.success(msg.msg+",积分增加"+msg.score);
					}else{
						ui.success(msg.msg);		
					}
					setTimeout('window.location.href="index.php?app=public&mod=Index&act=index";',3000);
				}else{				
					ui.error(msg.msg);
					$("#space_sure").attr("is_sub","true");
				}	
			},
			error : function(msg) {
				ui.error("请检查网络连接，稍后重试");
				$("#space_sure").attr("is_sub","true");
			}
		});
	});
	
	/**
	 * 获取班级id字符串
	 * @returns
	 */
	function getClassIds(){
		var classIds = [];
		$("#share_classes_area").children(".cur").each(function(){
			classIds.push($(this).attr("data-id"));
		});
		var ids = classIds.length > 0 ? classIds.join(',') : '';
		SHAREFILES.classids = ids;
		return ids;
	};
	
	/**
	 * 获取同步到同步资源栏目的相关信息
	 * @returns
	 */
	function getSubjectInfo(){
		var result = {};
		if($("#res_type").val()==null||$("#res_type").val()=='0'){
			result.error = '请选择资源类型';
			result.status = false;
			return result;
		}
		if($("#subject").val()==null){
			result.error = '请选择学科';
			result.status = false;
			return result;
		}
		if($("#edition").val()==null){
			result.error = '请选择版本';
			result.status = false;
			return result;
		}
		if($("#stage").val()==null){
			result.error = '请选择年级';
			result.status = false;
			return result;
		}
		if($("#unit1").val()==null){
			result.error = '请选择单元';
			result.status = false;
			return result;
		}
		win.SHAREFILES.bookid=$("#book").attr("book-id");
	    win.SHAREFILES.unit1=$("#unit1").val();
	    win.SHAREFILES.unit2=$("#unit2").val();
	    win.SHAREFILES.unit3=$("#unit3").val();
	    win.SHAREFILES.type=$("#res_type").val();
	    result.status = true;
		return result;
	};
	
	/**
	 * 点击分享到同步资源确定按钮
	 */
	$("#shareToSysRes").live("click",function(){
		if($("#res_type").val()==null||$("#res_type").val()=='0'){
			ui.error("请选择资源类型");
			return ;
		}
		if($("#subject").val()==null){
			ui.error("请选择学科");
			return ;
		}
		if($("#edition").val()==null){
			ui.error("请选择版本");
			return ;
		}
		if($("#stage").val()==null){
			ui.error("请选择年级");
			return ;
		}
		if($("#unit1").val()==null){
			ui.error("请选择单元");
			return ;
		}
		win.SHAREFILES.bookid=$("#book").attr("book-id");
	    win.SHAREFILES.unit1=$("#unit1").val();
	    win.SHAREFILES.unit2=$("#unit2").val();
	    win.SHAREFILES.unit3=$("#unit3").val();
	    win.SHAREFILES.type=$("#res_type").val();
		closeBox("show_space_chooce");
	});
	
	/**
	 * 选择分享位置
	 */
	$(".check_position").live("click", function() {
		var pos = $(this).attr("id");
		/*if (pos == "per_page") {
		    //分享到个人主页
			if($(this).is(":checked")){
				SHAREFILES.per_page='1';
			}else{
				SHAREFILES.per_page="";
			}
		}*/
		if (pos == "sys_res") {
			//清空历史同步资源目录选择遗留展示
			$("#subject").html("");
			$("#edition").html("");
		    $("#edition").hide();
			cleanSelect("subject");
			//分享到同步资源
			var queryParam={};	
			queryParam.node="subject";
			loadNodes(queryParam);
			if($(this).is(":checked")){
				SHAREFILES.sys_res='1';
				//加载资源类型
				$.ajax({
					url : "index.php?app=public&mod=ShareResult&act=getResourceTypes",
					type : "POST",
					dataType:"JSON",
					success : function(res) {
						if(res.length>0){
							var html="<option value='0'>请选择</option>";
							for(var index=0;index<res.length;index++){
								html +="<option value='"+res[index].code+"'>"+
								      res[index].name+"</option>";
							 }
							$("#res_type").html(html);
						}
						// 显示信息选择区域
						$("#subject_info_select").show();
					},
					error:function(res){
						ui.error("请检查网络连接，稍后重试");
					}	
				});
			}else{
				SHAREFILES.sys_res="";
				$("#subject_info_select").hide();
			}			
		}else if(pos == "class_share"){
			// 分享到班级
			if($(this).is(":checked")){
				SHAREFILES.class_share = 1;
				$(".pop_choice_class").show();
                $("#classSubject").show();
			}else{
				SHAREFILES.class_share = "";
				$(".pop_choice_class").hide();
				$("#classSubject").hide();
			}
		}
	});
	
	// 获取当前登录用户的班级列表
	function getUserClasses(){
		$.ajax({
			url : 'index.php?app=public&mod=ShareResult&act=getUserClasses',
			type : 'post',
			dataType : 'JSON',
			success : function(result){
				if(result.length <= 0){
					$("#class_share").attr('disabled',true);
					$("#choose_class_tip").html("您还没有班级，加入班级后才可以进行班级分享哦");
				}else{
					var classStr = '';
					for(var i = 0; i < result.length; i++){
						var className = result[i].className.length >=7 ? result[i].className.substring(0, 7)+"..." : result[i].className;
						classStr +=	'<li class="cur">'
								+ '<a href="javascript:void(0);">'
								+ '<img src="__APP__/images/choice_class.png">'
	                    		+ '<p>'
	                        	+ '<span title="' + result[i].className + '">' + className + '</span>'
	                        	+ '<font>' + result[i].classOrder + '班</font></p></a></li>';
					}
					$("#share_classes_area").html(classStr);
				}
			},
			error : function(msg){
				
			}
		});
	};
	
	/**
	 * 校验提交的数据
	 */
	function verifyShareData(){
		//校验是否选择了资源
		if(typeof(SHAREFILES.fid)=='undefined'){
			ui.error("请选择分享的资源");
			return false;
		}
		//校验如果分享到同步资源，是否选择了到课目录
		if($("#sys_res").is(":checked")){
			if(typeof(SHAREFILES.bookid)=='undefined'||typeof(SHAREFILES.unit1)=='undefined'){
				ui.error("请选择同步资源到课目录");
				return false;
			}
		}
		//校验是否选择了分享位置
		var isSelect=false;
		$(".check_position").each(function(){
			if($(this).is(":checked")){
				isSelect=true;
				return false;
			}
		});
		if(!isSelect){
			ui.error("请选择要分享的位置");			
			return false;
		}
		return true;
	}
	
	/**
	 * 清空选择框
	 */
	function cleanSelect(node){
		if(node=="subject"){		
		     $("#stage").html("");
		     $("#stage").hide();
		     $("#unit1").html("");
		     $("#unit1").hide();
		     $("#unit2").html("");
		     $("#unit2").hide();		    
		}else if(node=="edition"){	
			 $("#unit1").html("");
			 $("#unit1").hide();
			 $("#unit2").html("");
			 $("#unit2").hide();			
		}else if(node=="stage" || node == "unit1"){			
			 $("#unit2").html("");
			 $("#unit2").hide();			 
		}
		$("#unit3").html("");
		$("#unit3").hide();
	};
	
	/**
	 * 关闭弹出层
	 * id 保留的弹出成超链接id  1：关闭所有弹出层
	 */
	win.closeBox = function(id) {
		if (id == 1) {
			$("#shoose_where_share").css("display", "block");
			jQuery.fancybox.close();
		} else {
			jQuery.fancybox.close();
			jQuery("#" + id).trigger("click");
		}
	};

	/**
	 * 取消某一资源的分享
	 * fid 资源fid
	 */
	win.cancleRes = function() {
		SHAREFILES.fid=null;
		SHAREFILES.name=null;
		SHAREFILES.fileimg=null;
		SHAREFILES.extension=null;			
	};
})(jQuery, window);