/**
 * 资源公开弹出框
 * 
 */
var openRs = (function() {
	//存放公开信息初始化是的单元
	var _unit=null;
	var _course=null;
    var _section = null;
	return {
		init:function(isbook,fid){	
			var _this=this;	
			$.ajax({
				url : U("yunpan/Ajax/getBookDetial&fid="+appBase.getQueryString('fid')),
				type : 'post',
				datatype : 'json',
				success : function(res) {
					var initdata = jQuery.parseJSON(res);
					var data={};
                    if(initdata.unit2){
                        _course = initdata.unit2;
                    }
                    if(initdata.unit3){
                        _section = initdata.unit3;
                    }
                    data.node='phase';
                    _this.bekbeninit(data,'phasr');

                    //初始化学科
					data.node='subject';
                    data.phase=initdata.phase[0];
					_this.bekbeninit(data,'subject');							
					//初始化版本
					data.node='edition';
					data.subject=initdata.subject[0];					
					_this.bekbeninit(data,'edition');						
					//初始化年级
					data.node='stage';
					data.edition=initdata.edition[0];
					_this.bekbeninit(data,'stage');				
					//初始化书本，初始化书本成功之后，通过书本的code，遍历获取书本的id,加载出该书本下的单元
					data.node='book';
					data.stage=initdata.stage[0];				
					data.code=initdata.book;
					_unit=initdata.unit;
					_this.bekbeninit(data,'book');

					//初始换资源类型
					//data.type=initdata.type[0];
				},
				error:function(msg){
					ui.error('请检查网络连接......');
				}
			});	
			
		},
			
		/**
		 * 初始化在备课本框点击本课本是的下拉信息
		 */
		bekbeninit:function(data,type){						
			var _this=this;		
			var bookid=null;
			var url='yunpan/Ajax/getTreeNodes';			
				$.ajax({
					url : U(url),
					type : 'post',
					data : data,
					datatype : 'json',
					success : function(res) {	
					var obj = eval('(' + res + ')');			
					 _this.templeteSelect(obj,type);
					 $("#phase").find("option[value='"+data.phase+"']").attr("selected",true);
					 $("#subject").find("option[value='"+data.subject+"']").attr("selected",true);
					 $("#edition").find("option[value='"+data.edition+"']").attr("selected",true);
					 $("#stage").find("option[value='"+data.stage+"']").attr("selected",true);	
					 $("#mulu").find("option[value='"+data.unit+"']").attr("selected",true);
					//$("#restype").find("option[value='"+data.type+"']").attr("selected",true);	
			    	//通过bookcode获取bookid
					  if(type=='book'){
						bookid=$("#book").find("option[name='"+data.code+"']").val();
					   $("#book").find("option[value='"+bookid+"']").attr("selected",true);					  
					 }
					},
					error : function(msg) {
						ui.error(msg.msg);
					}	
				});					
		},
		/**
		 * fid :文件id
		 * data_name:文件名
		 * initdata:初始化下拉列表选择数据
		 */
		show : function(fid,data_name,initdata,extension) {
			this.showblackout();
			var html = "<div class='popup popup_statusSet' id='poup_statusSet' style='top:15%'>";
			html += "<div class='popup_tit'>";
			html += "<a href='javascript:;' class='closeBtn hiddenText'  title='关闭'>关闭</a>请编辑公开信息</div>";
			html += "<div class='statusSet_box'><table><tr><td>公开位置 :</td><td>";
			html += "<div class='statusSet_input_box'>学科资源</div>"
			//html += "<div class='statusSet_input_box' ><input type='checkbox' name='my_home_page[]' />我的主页";
			//html += "<select name='sel_colomn' id='lanmu' style='display:none'> <option value='0'> 请选择栏目</option></select></div>";
			//html += "<div class='statusSet_input_box'><input type='checkbox' name='xk_res[]'/>学科资源</div>";
			html += "<div class='xk_res_list' id='xkzy' style='display:normal'>";
			html += "<div class='row'><label>学段</label><select id='phase'><option value=''>---请选择---</option></select></div>";
			html += "<div class='row'><label>学科</label><select id='subject'><option value=''>---请选择---</option></select></div>";
			html += "<div class='row'><label>版本</label><select id='edition'><option value=''>---请选择---</option></select></div>";
			html += "<div class='row'><label>年级</label><select id='stage'><option value=''>---请选择---</option></select></div>";
			html += "<div class='row' style='display:none'><label>书本</label><select id='book'><option value=''></option></select></div>";
			html += "<div class='row'><label>单元</label><select id='mulu'><option value=''>---请选择---</option></select></div>";
			html += "<div class='row' id='unit2_div' style='display:none'><label>课时</label><select id='unit2'><option value=''>---请选择---</option></select></div>";
            html += "<div class='row' id='unit3_div' style='display:none'><label>课时</label><select id='unit3'><option value=''>---请选择---</option></select></div>";
			html += "<div class='row'><label>资源类型</label><select id='restype'><option value=''>---请选择---</option></select></div>";
			html += "</div></td></tr>";
			html += "<tr><td>关键字(选填)： </td><td><input id='keywords' type='text' value=''/></td></tr>";
			html += "<tr><td>描述(选填)：</td><td><textarea rows='4' id='description' cols='10'></textarea></td></tr></table></div>";
			html += "<div id='public_choice_div' class='addBook_choice move_choice'>" ;
			html +=	"<a id='loading_div' style='display:none;float:right;padding-right:200px;margin-top:10px;'><img src='"+APP+"/images/loading1.gif'/>正在公开资源，请稍等....</a>" ;
			html += "<a href='javascript:;' id='cancel_public' class='dropBtn opacityed' title='取消'>取消</a>";
			html += "<a href='javascript:;' id='sure_public' class='enterBtn opacityed' title='确定'>确定</a></div>"+
			"<input type='hidden' id='fid' value='"+fid+"'/>" +
			"<input type='hidden' id='filename' value='"+data_name+"'/>" +
			"<input type='hidden' id='extension' value='"+extension+"'/></div>";
			$("body").append(html);
			$("#poup_statusSet").show();
			this.initSelect(null,'phase',initdata);	
			//如果是初始化，加载备课本资源的基本数据
				if(typeof(initdata)!='undefined'){
					this.init(initdata,fid);
				}		
			this.initCreateNew();			
		},

		/**
		 * 添加弹出窗口
		 */
		showblackout : function() {
			if ($('.book-modal-blackout').length > 0) {
				// TODO
			} else {
				var height = $('body').height() > $(window).height() ? $('body')
						.height()
						: $(window).height();
				height = height > 1100 ? height : 1100;
				var divHtml = '<div class ="book-modal-blackout" ><iframe style="z-index:-1;position: absolute;visibility:inherit;width:'
						+ $('body').width()
						+ 'px;height:'
						+ height
						+ 'px;top:0;left:0;filter=\'progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)\'"'
						+ 'src="about:blank"  border="0" frameborder="0"></iframe></div>';
				$(divHtml).css({
					height : height + 'px',
					width : $('body').width() + 'px',
					zIndex : 999,
					opacity : 0.5
				}).appendTo(document.body);
			}
		},

		/**
		 * 关闭资源公开弹出框
		 */
		close : function() {
			$('#poup_statusSet').remove();
			$('.book-modal-blackout').remove();
		},
		/**
		 * 初始化下来框数据
		 */
		initSelect : function(data,type,initdata) {
			var _url='yunpan/Ajax/getTreeNodes';
			var _this = this;
			//初始化科目下拉框
			if(data==null&&type=='phase'){
				data = {node:'phase'};
			}else if('mulu'==type){
				_url='yunpan/Ajax/getBookIndex';
			}
			$.ajax({
				url : U(_url),
				type : 'post',
				data : data,
				datatype : 'json',
				success : function(res) {
					var obj = eval('(' + res + ')');
					_this.templeteSelect(obj,type,initdata);			
				},
				error : function(msg) {
					ui.error(msg.msg);
				}
			});
		},

		/**
		 * 渲染弹出框数据
		 */
		templeteSelect : function(obj,type,initdata) {
			var dom="#"+type;
			var html = "";
			var firstOp="<option value=''>---请选择---</option>";		
			if('book'==type){
				if(typeof(obj.data)!='undefined'){						
				 html=	"<option  code="+obj.data[0].code+" selected = 'selected' value='"+obj.data[0].id + "'>"+obj.data[0].name +"</option>";			
				 $(dom).html(html);			
				 //初始化目录
				 var data={					 
						 id:$("#book").val()
					 };
				this.initSelect(data,'mulu');					
				return ;
				}
			}else if('mulu'==type){
				if(typeof(obj.data.general)!='undefined'&&typeof(obj.data.general.resourcedescriptor.units)!='undefined'){
					var data=obj.data.general.resourcedescriptor.units;
					html+=firstOp;
					for(var index=0;index<data.length;index++){
						html += "<option value='" + data[index].Code + "'>"
						+ data[index].Name + "</option>";
					}
				}			
			}else{				
				if(typeof(obj.data)!='undefined'){
					html+=firstOp;
					for ( var index = 0; index < obj.data.length; index++) {
						html += "<option value='" + obj.data[index].code + "'>"
								+ obj.data[index].name + "</option>";
					}				
			
				}				
				if(typeof(obj.rstype)!='undefined'&&$("#restype option").length==1){
					var rstHmtl=firstOp;
					for ( var index = 0; index < obj.rstype.length; index++) {
						rstHmtl += "<option value='" + obj.rstype[index].code + "'>"
								+ obj.rstype[index].name + "</option>";
					}	
					$("#restype").html(rstHmtl);
				}				
			}		
			$(dom).html(html);
			if(type=='mulu'){
				//选中默认目录
				if(_unit!=null){
					$("#mulu").find("option[value="+_unit+"]").attr("selected",true);
					_unit=null;
				}				
				//加载单元下的课程，选中默认课程
				this.loadCourse('unit2');
				this.loadCourse('unit3');
			}
			
		},
	
		/**
		 * 加载单元下的课程
		 */
	loadCourse : function(node){
		var param={
				    id:$("#book").val(),
				    unit:$("#mulu").val(),
                    unit2:$("#unit2").val(),
                    node:node
				   };
        if(_unit){
            param.unit = _unit;
        }
        if(_course){
            param.unit2 = _course;
        }

	  	$.ajax({
			url : U("yunpan/Ajax/getCource"),
			type : 'post',
			data : param,
			datatype : 'json',
			success:function(data){
				data = $.parseJSON(data);
				if(data.length!=0){
					var courserhmtl="<option value=''>---请选择---</option>";		
					for(var courseindex=0;courseindex<data.length;courseindex++){
						courserhmtl +="<option value="+data[courseindex].Code+">"+data[courseindex].Name+"</option>";
					}					
				    $("#"+node).html(courserhmtl);
				    if(_course!=null && node == 'unit2'){
                        $("#"+node).find("option[value="+_course+"]").attr("selected",true);
				    	_course=null;
				    }
                    if(_section!=null && node == 'unit3'){
                        $("#"+node).find("option[value="+_section+"]").attr("selected",true);
                        _section=null;
				    }

					$("#"+node+"_div").show();
				}else{
					$("#"+node+"_div").hide();
				}		
			}
		});
		},
      /**
       * 选择清空下一级别下拉框
       */
		cleanSelect:function(type){
			var html="<option value=''>---请选择---</option>";	
			if('phase'==type){
				$("#subejct").html();
				$("#edition").html(html);
				$("#stage").html(html);				
				$("#mulu").html(html);
				$("#unit2").html(html);
				$("#unit3").html(html);
			}else if('subject'==type){
				$("#edition").html(html);
				$("#stage").html(html);				
				$("#mulu").html(html);
				$("#unit2").html(html);
                $("#unit3").html(html);
			}else if('edition'==type){				
				$("#stage").html(html);				
				$("#mulu").html(html);
				$("#unit2").html(html);
                $("#unit3").html(html);
			}else if('stage'==type){		
				$("#mulu").html(html);
				$("#unit2").html(html);
                $("#unit3").html(html);
			}else if('mulu'==type){
                $("#unit2").html(html);
                $("#unit3").html(html);
            }
		},
		/**
		 *初始化弹出框事件 
		 */
		initCreateNew : function() {
			var _this = this;
		/**
		 * 选择学段下拉框
		 */
			$('#phase').die().live('change', function() {
					var data={
							node:'subject',
							phase:$("#phase").val()
					  	};
					_this.initSelect(data,'subject');
					_this.cleanSelect('phase');
		     });
		/**
		  * 选择学科下拉框
		 */
			$('#subject').die().live('change', function() {
				var data={
						node:'edition',
						phase:$("#phase").val(),
						subject:$("#subject").val()
				  	};
				_this.initSelect(data,'edition');
				_this.cleanSelect('subject');
			});
			/**
			 * 选择版本下拉列表
			 */
			$('#edition').die().live('change',function(){
				var data={
						node:'stage',
						phase:$("#phase").val(),
						subject:$("#subject").val(),
						edition:$("#edition").val()
				  	};
				_this.initSelect(data,'stage');
				_this.cleanSelect('edition');
				
			});
			/**
			 * 选择年级下拉列表
			 */
			$('#stage').die().live('change',function(){
				 var data={
					 node:'book',
					 phase:$("#phase").val(),
					 subject:$("#subject").val(),
					 edition:$("#edition").val(),
					 stage:$("#stage").val()
				 };
				 _this.initSelect(data,'book');				
				 _this.cleanSelect('stage');
			});			
			/**
			 * 选择单元按钮
			 */
			$("#mulu").die().live('change',function(){					
				_this.loadCourse('unit2');
                _this.cleanSelect('mulu');
			});
            /**
             * 选择课时按钮
             */
            $("#unit2").die().live('change',function(){
                _this.loadCourse('unit3');
            });

			/**
			 * 点击关闭图标关闭弹出框
			 */
			$(".closeBtn").die().live('click', function() {
				_this.close();
			});
			/**
			 * 点击取消按钮
			 */
			$("#cancel_public").die().live('click', function() {
				_this.close();
			});
			
			/**		
			 * 点击确定按钮事件
			 */
			$("#sure_public").die().live('click', function() {				
				var data={																
						bookid:$("#book").val(),
						unit:$("#mulu").val(),
						unit2:$("#unit2").val(),
						unit3:$("#unit3").val(),
						fid:$("#fid").val(),
						type:$("#restype").val(),
						filename:$("#filename").val(),
						keywords:$("#keywords").val(),
						description:$("#description").val(),
						extension:$("#extension").val()
				};
				//如果选中公开到学科资源
						
			//	if($("input[name='xk_res[]']").is(':checked')||$("input[name='my_home_page[]']").is(':checked'))
			//	{
				   if($("#phase").val()==''){
					   ui.error('请选择学段');
					     return;
					   
				   }				
					if($("#subject").val()==''){					
						     ui.error('请选择学科');
						     return;
					}
					if($("#edition").val()==''){
						   ui.error('请选择版本');
						   return;
					}
					if($("#stage").val()==''){
						   ui.error('请选择年级');
						   return;
					}				
					if($("#mulu").val()==''){
						   ui.error('请选择单元');
						   return;
					}
					if($("#restype").val()==''){
						   ui.error('请选择资源类型');
						   return;
					}				
					
			//	}else{
			//		ui.error('请选择公开的位置');
			//		return;
			//	}
				$('#cancel_public').hide();
				$('#sure_public').hide();
			    $('#loading_div').show();
				$.ajax({
					url : U("yunpan/Ajax/exportToGateway"),
					type : 'POST',
					data : data,
					datatype : 'json',
					success : function(res) {
						 var res=eval('('+res+')');
						if(res.status==1){
                            if(typeof(res.creditResult) != 'undefined'){
                                var alias = res.creditResult.alias;
                                var score = res.creditResult.score;
                                jQuery.jBox.tip("<span style='font-size:14px; color:#535353;'>"+alias+"成功,积分</span><span style='color:#ff6600;'>+"+score+"</span>", 'success');
                            }else{
                                ui.success('资源公开成功');
                            }
						    _this.close();
                            var param=appBase.getQueryString();
                            appBase.grid.init(param);
						}else if(res.status==2){
							$('#cancel_public').show();
							$('#sure_public').show();
						    $('#loading_div').hide();
							ui.error(res.data);	
						}else{
							$('#cancel_public').show();
							$('#sure_public').show();
						    $('#loading_div').hide();
							ui.error('资源公开失败');
						}
					},
					error : function(res) {
						$('#cancel_public').show();
						$('#sure_public').show();
					    $('#loading_div').hide();
						ui.error('请检查网络连接...');
					}
				});
			});
			
			
			/**
			 * 点击公开位置复选框
			 */
			$("input[type='checkbox']").die().live('click',function() {				
								var name = $(this).attr('name');						
								//公开到我的主页
								if ($(this).is(':checked')&& 'my_home_page[]' == name) {
									$("#lanmu").show();
								} else if ($(this).not(':checked') && 'my_home_page[]' == name) {
									$("#lanmu").hide();
								}
								//公开到我的学科资源
								if ($(this).is(':checked') && 'xk_res[]' == name) {
									$("#xkzy").show();
								} else if ($(this).not(':checked') && 'xk_res[]' == name) {
									$("#xkzy").hide();
								}
						})
			
		}		
	}
})();