/**
 * 我的下载
 * xmsheng
 */

(function(ab,$){
	var _this = {};
	/**
	 * 初始化
	 */
	_this.init = function(param) {	
		_this.templeteDownloadBar();
		if(typeof(param.page)=="undefined"){
			param.page=1;
		}
		var queryparm={p:param.page};
		$('.yunfile_r_cont_list').html("");
		_this.getDownloadNotes(queryparm);
		//左边我的备课本初始化
		var books = book._init() ;
		content = appBase.grid._templeteBooks(books);
		appBase.grid.changeBook(content);
		
	}
	/**
	 * 获取公开列表
	 */
	_this.getDownloadNotes=function(queryparm){
		$.ajax({
			url : U("yunpan/Ajax/getDownload"),
			type : 'post',
			data : queryparm,
			datatype : 'json',
			success : function(result) {
				var res=eval('('+result+')');
				if(typeof(res.notes)!='undefined'){
					_this.templeteDownload(res.notes);
				}
				if(typeof(res.page)!='undefined'){	
					_this.templeteFoot(res.page)
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
	_this.templeteDownload = function(downloads) {
		//影藏'新建文件'，'删除'按钮所在的DIV
		$('.yunfile_r_fun').hide();
		var html = "<tr><th>资源名称 </th><th width='164'>资源评分</th><th width='164'>上传用户</th><th width='200'>下载时间</th></tr>";
		for ( var index = 0; index < downloads.length; index++) {
			html +="<tr>"
				  +"<td>" +
				  		 "<a title='"+downloads[index].name+"'target='_blank' href='"+downloads[index].previewurl+"'>" +
				  		 "<img src='"+getShortImg(downloads[index].extension)+"'/></a>" +
				  		 "<a title='"+downloads[index].name+"'target='_blank' href='"+downloads[index].previewurl+"'>";		
				  		if(downloads[index].name.length>10){
				  			downloads[index].name=downloads[index].name.substr(0,10)+"...";
						}				  		
			   	html +=  downloads[index].name +"</a>" +
				  	  "</td>" +
				   "<td>" ;
					var star=downloads[index].star;
						//全星
				       for(var starindex=0;starindex<star.allStar;starindex++){
				    	   html+="<span class='star'></span>";
				       }
				       //灰色星星的数量
				       var  grayStarNum=5;
				    	  //设置不全星的显示
				    	  if(star.endStar!=0){
				    		  html +="<span class='star star_half'></span>";
				    		  grayStarNum=(grayStarNum-1-star.allStar);
				    	  }else if(star.endStar==0){
				    		  grayStarNum=grayStarNum-star.allStar;
				    	  }
				    	  //设置灰色的星星
				    	  for(grayStarIndex=0;grayStarIndex<grayStarNum;grayStarIndex++){
				    		  html +="<span class='star star_gray'></span>" ;
				    	  }				   
				  	html+="<span class='book_package_f18'>"+star.fiveScore+"</span></td>"+
				  	     	"<td>";
				  					  	
				  	if(downloads[index].creator==null||downloads[index].creator==''){
				  		html+='匿名';
				  	}else{
				  		if('undefined'!=typeof(downloads[index].prerson_sns_url)){
				  			html+="<a href='"+downloads[index].prerson_sns_url+"'>"+downloads[index].creator+"</a>";
				  		}else{
				  			html+=downloads[index].creator;	
				  		}				  		
				  	}				 
				  	html+="</a></td>"+
				  	     	"<td>"+downloads[index].dateline+"</td>"+
				  	     "</tr>";  
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
				"javascript:appBase.setQueryString('page'," + (currentPage+1) + ")");
		$('#list_page .pre').attr("href",
				"javascript:appBase.setQueryString('page',"+(currentPage-1)+")")
		$.each($('#list_page a').not('.next,.pre'),function(){
				var pageIndex=parseInt($(this).text());
				$(this).attr("href",
				"javascript:;appBase.setQueryString('page',"+pageIndex+")");
		})
		
		$('.yunfile_r_fun').show();
	}
	
	/**
	 *初始化导航和工具栏
	 */
	_this.templeteDownloadBar = function() {
		//初始化导航栏
		// 清空上次面包屑数据
		$(".yunfile_r_logo a").not("#yunpan_nav a").hide();
		$("#yunpan_nav span").remove();
		var navHtml = "<span>></span><span>" + "<a href='javascript:;' title='我的下载记录'>我的下载记录</a></span>";
		$("#yunpan_nav").append(navHtml);

		 //by frsun 20140605
		$(".yunfile_r_cont_list").parent().removeClass("yunfile_tab_bkb").removeClass("yunfile_tab_mypub").addClass("yunfile_tab_mydownload");		
		$('.yunfile_res_bag').hide();

		//初始化工具栏
		var pos=ab.getQueryString("open_position");
		$("#nav_default").hide()
		$("#nav_public").hide();
		
	}
	ab.downloadgrid=_this;
	
})(appBase,jQuery)