/**
 * 我的公开
 * 
 */

(function(ab, $) {
	var _this = {};
	/**
	 * 初始化
	 */
	_this.init = function(params) {		
		//渲染导航和工具栏
		_this.templetePublicBar(params);
		//当前页数，默认为1页
		if('undefined'==typeof(params.p)){
			params.p=1;
		}
		//当前公开位置类型,默认为‘我的主页’
		//01 	我的主页
		//02	学科资源
		//注：由于公开到"我的主页"功能暂未做，先默认'学科资源'，改回是只需要将'02'改成'01'
		if('undefined'==typeof(params.open_position)){
			params.open_position='02';			
		}
		queryparm={
				p:params.p,
				open_position:params.open_position
		};
		$('.yunfile_r_cont_list').html("");
		_this.getPublicNotes(queryparm);	
		
		//左边我的备课本初始化
		var books = book._init() ;
		content = appBase.grid._templeteBooks(books);
		appBase.grid.changeBook(content);
	}
	/**
	 * 获取公开列表
	 */
	_this.getPublicNotes=function(queryparm){
		$.ajax({
			url : U("yunpan/Ajax/getPublic"),
			type : 'post',
			data : queryparm,
			datatype : 'json',
			success : function(result) {
				var res=eval('('+result+')')
				//渲染公开列表数据
				if(res!=null&&'undefined'!=typeof(res.notes)){
					_this.templetePublic(res.notes);
				}
				//渲染分页
				if(res!=null&&'undefined'!=typeof(res.page)){
					_this.templeteFoot(res.page);
				}
			},
			error : function(res) {
				ui.error('请检查网络连接...');
			}
		});
	}

	/**
	 * 渲染我的公开列表
	 */
	_this.templetePublic = function(pubs) {
		//影藏'新建文件'，'删除'按钮所在的DIV
		$('.yunfile_r_fun').hide();		
		var html = "<tr><th>资源名称</th><th width='164'>资源类型</th><th width='164'>审核状态</th><th width='200'>公开时间</th></tr>";
		for ( var index = 0; index < pubs.length; index++) {
			html += "<tr>" 
					+ "<td><a title='"+pubs[index].name+"'target='_blank' href='"+pubs[index].previewurl+"'>"
					+ "<img  src='"+getShortImg(pubs[index].extension)
					+ "'/></a>"
					+ "<a title='"+pubs[index].name+"'target='_blank' href='"+pubs[index].previewurl+"'>";
					if(pubs[index].name.length>12){
						pubs[index].name=pubs[index].name.substr(0,12)+"...";
					}
			html += pubs[index].name +"</a>"
					+ "</td>" + " <td>"+pubs[index].resTypeName+"</td>" 
					+ "<td>"+get_res_status_name(pubs[index].resstatus)+"</td>"
					+ "<td>"+pubs[index].dateline+"</td>"
					+ "</tr>"
		}
		$('.yunfile_r_cont_list').html(html);
		$('.yunfile_r_cont_list').addClass('yunfile_r_cont_list_record');

	}

	/**
	 * 渲染分页
	 */
	_this.templeteFoot = function(page) {	
		var page_html = "<div id='list_page'  class='page_box fr clearfix'></div>";				
		$('.yunfile_r_fun').html(page_html);
		$('.page_box').html(page);
		$('.page_box a').attr('href','javascript:;')
		var currentPage=parseInt($('.page_box').find('.current').text());
		$("#list_page .next").attr("href",
				"javascript:appBase.setQueryString('p'," + (currentPage+1) + ")");
		$('#list_page .pre').attr("href",
				"javascript:appBase.setQueryString('p',"+(currentPage-1)+")")
		$.each($('#list_page a').not('.next,.pre'),function(){
				var pageIndex=parseInt($(this).text());
				$(this).attr("href",
				"javascript:;appBase.setQueryString('p',"+pageIndex+")");
		})
		
		$('.yunfile_r_fun').show();
	}
	
	/**
	 *初始化导航和工具栏
	 */
	_this.templetePublicBar = function(params) {
		//初始化导航栏
		// 清空上次面包屑数据
		$(".yunfile_r_logo a").not("#yunpan_nav a").hide();
		$("#yunpan_nav span").remove();
		var navHtml = "<span>></span><span>" + "<a href='javascript:;' title='我的公开记录'>我的公开记录</a></span>";
		$("#yunpan_nav").append(navHtml);
		//初始化工具栏,以及按钮选中样式,由于公开到'我的主页'功能未做，先将公开到’我的主页‘记录注销
//		$('.record_btn').removeClass('current');
//		if('undefined'==typeof(params.open_position)||params.open_position=='01'){
//			$("#wdzy_1").addClass('current');
//		}else if(params.open_position=='02'){
//			$("#xkzy_1").addClass('current');
//			}
		$("#nav_default").hide();
		$("#nav_public").show();
         
         //by frsun 20140605
		$(".yunfile_r_cont_list").parent().removeClass("yunfile_tab_bkb").removeClass("yunfile_tab_mydownload").addClass("yunfile_tab_mypub");
		$('.yunfile_res_bag').hide();
	}
	ab.publicgrid = _this;
})(appBase, jQuery)