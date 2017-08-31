/**
 * 扩展核心评论对象
 * @author jason <yangjs17@yeah.net>
 * @version TS3.0
 */
core.comment = {
	// 给工厂调用的接口
	_init:function(attrs) {
		if(attrs.length == 3) {
			core.comment.init(attrs[1], attrs[2]);
		} else {
			return false;
		}
	},
	// 初始化评论对象
	init: function(attrs, commentListObj) {	
		// 这些参数必须传入
	
		this.app_uid = attrs.app_uid,
		this.row_id  = attrs.row_id,
		this.to_comment_id = attrs.to_comment_id,
		this.to_uid = attrs.to_uid;
		this.app_row_id = attrs.app_row_id;//原文ID
		//by ylzhao
		table = attrs.table;//添加一个全局table，传给page函数
		
		this.app_row_table = attrs.app_row_table;
		this.addToEnd = "undefined" == typeof(attrs.addToEnd) ? 0 : attrs.addToEnd;
		this.canrepost = "undefined" == typeof(attrs.canrepost) ? 1 : attrs.canrepost;
		this.cancomment = "undefined" == typeof(attrs.cancomment) ?  1 : attrs.cancomment;
		this.cancomment_old = "undefined" == typeof(attrs.cancomment_old) ?  1 : attrs.cancomment_old;
		this.ajax_page = "undefined" == typeof(attrs.ajax_page) ?  0 : attrs.ajax_page;
		
		if("undefined" != typeof(attrs.app_name)) {
			this.app_name = attrs.app_name;
		} else {
			this.app_name = "public";	//默认应用
		}
		if("undefined" != typeof(attrs.table)) {
			this.table = attrs.table;
		} else {
			this.table = 'feed';	//默认表
		}
		if("undefined" != typeof(attrs.to_comment_uname)) {
			this.to_comment_uname = attrs.to_comment_uname;
		}
		if("undefined" != typeof(commentListObj)) {
			this.commentListObj = commentListObj;
		}
	},
	// 显示回复块
	display: function() {	
		var commentListObj = this.commentListObj;
		if("undefined" == typeof this.table) {
			this.table = 'feed';
		}
		if(commentListObj.style.display == 'none') {
			if(commentListObj.innerHTML !=''){
				commentListObj.style.display = 'block';
			}else{
				var rowid = this.row_id;
				var appname = this.app_name;
				var table = this.table;
				var cancomment = this.cancomment;
				var ajax_page = this.ajax_page;
				commentListObj.style.display = 'block';
				commentListObj.innerHTML = '<img src="'+THEME_URL+'/image/load.gif" style="text-align:center;display:block;margin:0 auto;"/>';
				$.post(U('widget/Comment/render'),{app_uid:this.app_uid,row_id:this.row_id,app_row_id:this.app_row_id,app_row_table:this.app_row_table,isAjax:1,showlist:0,
						cancomment:this.cancomment,cancomment_old:this.cancomment_old,app_name:this.app_name,table:this.table,
						canrepost:this.canrepost,ajax_page:this.ajax_page},function(html){
							if(html.status =='0'){
								commentListObj.style.display = 'none';
								ui.error(html.data)
							}else{
								commentListObj.innerHTML = html.data;
								$('#commentlist_'+rowid).html('<img src="'+THEME_URL+'/image/load.gif" style="text-align:center;display:block;margin:0 auto;"/>');
								$.post(U('widget/Comment/getCommentList'),{app_name:appname,table:table,row_id:rowid,cancomment:cancomment,ajax_page:ajax_page},function (res){
									$('#commentlist_'+rowid).html(res);
									M($('#commentlist_'+rowid).get(0));
								});
								M(commentListObj);
								//@评论框
								atWho($(commentListObj).find('textarea'));
								$(commentListObj).find('textarea').focus();
							}
				},'json');
			}
		}else{
			commentListObj.style.display = 'none';
		}
	},
	// 初始化回复操作
	initReply: function() {	
		this.comment_textarea = this.commentListObj.childModels['comment_textarea'][0];
		var mini_editor = this.comment_textarea.childModels['mini_editor'][0];
		var _textarea = $(mini_editor).find('textarea');
		var html = L('PUBLIC_RESAVE')+'@'+this.to_comment_uname+' ：';			
		//_textarea.focus();
		$('.comment_replay').html('回复');
		_textarea.val(''); // 防止出现多次的点击 回复，导致回复错乱的情况 --qiangjia
		_textarea.inputToEnd(html);
		_textarea.focus();
	},
	// 发表评论
	addComment:function(afterComment,obj) {
		var commentListObj = this.commentListObj;
		this.comment_textarea = commentListObj.childModels['comment_textarea'][0];
		var mini_editor = this.comment_textarea.childModels['mini_editor'][0];
		var _textarea = $(mini_editor).find('textarea').get(0);
		var strlen = core.getLength(_textarea.value);
		var leftnums = initNums - strlen;
		if(leftnums < 0 || leftnums == initNums) {
			flashTextarea(_textarea);
			return false;
		}
		
		// 如果转发到自己的微博
		if(this.canrepost == 1){
			// 由于屏蔽动态的转发到微博功能，暂时设置ischecked为false
			//var ischecked = $(this.comment_textarea).find("input[name='shareFeed']").get(0).checked;
			var ischecked = false;
			if(ischecked == true) {
				var ifShareFeed = 1;
			} else {
				var ifShareFeed = 0;
			}
		}else{
			var ifShareFeed = 0;
		}
		var isold = $(this.comment_textarea).find("input[name='comment']");
		var comment_old = 0;
		if( isold.get(0) != undefined) {
			if ( isold.get(0).checked == true  ){
				var comment_old = 1;
			}
		}
		var content = _textarea.value;	
		if(content == '') {
			ui.error(L('PUBLIC_CONCENT_TIPES'));
		}
		if("undefined" != typeof(this.addComment) && (this.addComment == true)) {
			return false;	//不要重复评论
		}
		var addcomment = this.addComment;
		var addToEnd = this.addToEnd;
		var _this = this;
		obj.innerHTML = '发送中..';
		$.post(U('widget/Comment/addcomment'),{
			app_name:this.app_name,
			table_name:this.table,
			app_uid:this.app_uid,
			row_id:this.row_id,
			to_comment_id:this.to_comment_id,
			to_uid:this.to_uid,
			app_row_id:this.app_row_id,
			app_row_table:this.app_row_table,
			content:content,
			ifShareFeed:ifShareFeed,
			comment_old:comment_old
			},function(msg){				
				//alert(msg);return false;
				if(msg.status == "0"){
					ui.error(msg.data);
					obj.innerHTML = '评论';
				}else{
					if("undefined" != typeof(commentListObj.childModels['comment_list']) ){
						if(addToEnd == 1){
							$(commentListObj).find(' .comment_lists').eq(0).prepend(msg.data);
						}else{
							$(msg.data).insertBefore($(commentListObj.childModels['comment_list'][0]));
						}
					}else{
						$(commentListObj).find('.comment_lists').eq(0).html(msg.data);
					}
					M(commentListObj);
					if ( obj != undefined ){
						obj.innerHTML = '评论';
					}
					//重置
					_textarea.value = '';
					_this.to_comment_id = 0;
					_this.to_uid = 0;
					if("function" == typeof(afterComment)){
						afterComment();
					}
                    //增加添加动态评论回复数实时刷新功能 by tkwang
                    var  _feedSpan =jQuery("#feed_span_"+obj.args.row_id);
                    if( _feedSpan.length >0 ){
                        _feedSpan.html((_feedSpan.text()!='' ? parseInt(_feedSpan.text()):0) + 1);
                        _feedSpan.parent().css('display','');
                    }
					//动态更新评论数量 by sjzhao
						var _obj = jQuery("#span_"+obj.args.row_id);
						_obj.html((_obj.text()!=''? parseInt(_obj.text()):0) + 1);
						//字符提示置为0
						jQuery("#comment_inputor").next().find("b").css("color", "#333").text(0);
				}
				addComment = false;
			//});
			},'json');
	},
	delComment:function(comment_id){
		var row_id = this.row_id;
		var tablename = this.table;
		$.post(U('widget/Comment/delcomment'),{comment_id:comment_id},function(msg){
			//什么也不做吧
			if(tablename == 'onlineanswer_answer'){
				var _obj = jQuery("#span_"+row_id);
				var num = parseInt(_obj.text());
				num >= 1 ? num-- : 0;  
				_obj.html(num);
			}
			//日志删除
			if(tablename == 'blog'){
				if(jQuery('.comment_list_'+comment_id).length>0){
					jQuery(jQuery('.comment_list_'+comment_id).get(0)).remove();
				}
				if(jQuery('#excellent_comment_'+comment_id).length>0){
					jQuery(jQuery('#excellent_comment_'+comment_id).get(0)).remove();
				}
			}
		});
	}
};
//by ylzhao
//ajax换页函数
function page(id,row_id){
	var id = id;
	   $.get(U('widget/Comment/getCommentList'), {'p':id,"table":table,'row_id':row_id,'app_name':'public','ajax_page':1}, function(data){  //用get方法发送信息到TestAction中的test方法
	    $('#ajax_div_'+row_id).replaceWith("<div id='ajax_div_'+row_id>"+data+"</div>");
	 });
}