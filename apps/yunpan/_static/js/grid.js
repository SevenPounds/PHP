/**
 * Created by xypan on 14-4-14.
 */
(function(ab,$){
    var _this = {};
    
    // 文件夹类型常量
    _this.constant  = {};
    
    _this.__APP__ = APP;
    
    // 普通文件夹
    _this.constant.NORMAL = '1000';
	//收藏夹
    _this.constant.FAVORITE = '1001';
	//备课本
    _this.constant.BEI_KE_BEN = '1002';
	//资源包
    _this.constant.PACKAGE = '1003';
	//书
    _this.constant.BOOK = '1004';
	//单元
    _this.constant.UNIT = '1005';
	//课
    _this.constant.COURSE = '1006';
    // 资源分类操作
    _this.constant.RESOUCECATEGORY = '2001';

    // 备课本名称
    _this.constant.BEI_KE_NAME = '我的备课本';

    

    // 设置导航条
    _this.setNav = function(fid,data){
        var newData = data;
        $('#yunpan_nav a:first').nextAll().remove();
        if(data.length > 5 && data[0].name != _this.constant.BEI_KE_NAME){
            $("#yunpan_nav").append('<span>......</span>');
            newData = data.slice(-5);
        }else if(data.length > 5 && data[0].name == _this.constant.BEI_KE_NAME){
            var temp = data.slice(0,3);
            $.each(temp,function(i,n){
                $("#yunpan_nav").append('<span>></span>');
                $("#yunpan_nav").append("<span>" +
                    "<a href='javascript:void(0);' class='yunpan_nav_a'   data-value='" + n.fid+"' title='"+ n.name+"'>"+ n.shortName+"</a>" +
                    "</span>");
            });

            temp = data.slice(3);
            if(temp.length > 2){
                $("#yunpan_nav").append('<span>......</span>');
                newData = temp.slice(-2);
            }
        }
        $.each(newData,function(i,n){
            if((i == 0 && data.length <= 5) || i > 0){
                $("#yunpan_nav").append('<span>></span>');
            }
            if(i+1==newData.length){
            	if (n.name.length>15){
            		n.shortName=n.name.substr(0,15)+'...'
            	}else{
            		n.shortName=n.name.substr(0,15)
            	}            	
            }
            $("#yunpan_nav").append("<span>" +
                "<a href='javascript:void(0);' class='yunpan_nav_a' data-value='" + n.fid+"' title='"+ n.name+"'>"+ n.shortName+"</a>" +
                "</span>");
        });
    }

    // 设置导航条后的按钮
    _this.setNavButton = function(fid,folderType){

        if($('#my_book_share_btn').length >0 ) $('#my_book_share_btn').remove();
        if($('#yunpan_upload_btn').length >0 ) $('#yunpan_upload_btn').remove();

        switch(folderType){
            case _this.constant.NORMAL:
            case _this.constant.UNIT:
            case _this.constant.COURSE:
                $("#yunpan_nav").after('<a class="yunfile_upload_btn opacityed" href="javascript:;"  id="yunpan_upload_btn" onclick="uploadBox.show(\''+fid+'\')">上传资源 </a>');
                break;
            case _this.constant.FAVORITE:
            case _this.constant.PACKAGE:
            case _this.constant.RESOUCECATEGORY:
                break;
            case  _this.constant.BOOK:
            case  _this.constant.BEI_KE_BEN:	
            	// 分享备课本
//        		$(".yunfile_r_logo").append("<a href='javascript:;' title='分享备课本' class='book_share_btn opacityed' id='my_book_share_btn'>分享备课本</a>");
            	break;
            default:
                $("#yunpan_nav").after('<a title="上传资源"  class="yunfile_upload_btn opacityed" href="javascript:;"  id="yunpan_upload_btn" onclick="uploadBox.show(\''+0+'\')">上传资源 </a>');
                break;
        }
    }

    // 设置table的th
    _this.setTableTh = function(folderType){
        switch(folderType){
        	case _this.constant.NORMAL:
            case  _this.constant.UNIT :
            case  _this.constant.COURSE :
            case _this.constant.RESOUCECATEGORY:
                var params = ab.getQueryString();
                if(typeof(params.type) != 'undefined' && params.type != '' &&  params.type != 'all'){
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" id="all_checked" name="resource_all_ck[]"/>'+
                        '资源名称</th><th width="80">来源</th><th width="164">操作</th><th width="100">目录</th><th width="60">'+
                        '状态</th><th width="114">时间</th></tr>');
                }else{
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" id="all_checked" name="resource_all_ck[]"/>'+
                        '资源名称</th><th width="80">来源</th><th width="164">操作</th><th width="60">'+
                        '状态</th><th width="114">时间</th></tr>');
                }
                $(".yunfile_r_cont_list").append('<tr id="new_folder_tr" style="display:none;">'+
                    '<td style="padding-left: 40px;">'+
                    '<a href="javascirpt:void(0);" title="新建文件夹">'+
                    '<img src="'+_this.__APP__ +'/images/yunfile_bkb.png" alt="新建文件夹"/></a>'+
                    '<input type="text" value="新建文件夹" name="new_folder" class="new_folder"/>'+
                    '<img alt="correct" src="'+_this.__APP__ +'/images/correct.png" class="rename_sign" id="create_correct" />'+
                    '<img src="'+_this.__APP__ +'/images/mistake.png" alt="mistake" class="rename_sign" id="create_mistake"/>'+
                    '<img src="'+_this.__APP__ +'/images/loading1.gif" class="rename_sign" id="create_now" style="display:none;"/>'+
                    '</td><td></td><td></td><td></td><td></td></tr>');
                break;
        	case _this.constant.BEI_KE_BEN:
                var params = ab.getQueryString();
                if(typeof(params.type) != 'undefined' && params.type != '' &&  params.type != 'all'){
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" id="all_checked" name="resource_all_ck[]"/>'+
                        '资源名称</th><th width="80"></th><th width="164"></th><th width="60">'+
                        '</th><th width="100">目录</th><th width="114">时间</th></tr>');
                }else{
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" id="all_checked" name="resource_all_ck[]"/>'+
                        '资源名称</th><th width="80"></th><th width="164"></th><th width="60">'+
                        '</th><th width="114">时间</th></tr>');
                }
            	break;
            case _this.constant.BOOK:
                var params = ab.getQueryString();
                if(typeof(params.type) != 'undefined' && params.type != '' &&  params.type != 'all'){
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" disabled="disabled"/>'+
                        '资源名称</th><th width="80"></th><th width="164"></th><th width="60">'+
                        '</th><th width="100">目录</th><th width="114">时间</th></tr>');
                }else{
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" disabled="disabled"/>'+
                        '资源名称</th><th width="80"></th><th width="164"></th><th width="60">'+
                        '</th><th width="114">时间</th></tr>');
                }
            	break;
            case _this.constant.FAVORITE:
            case _this.constant.PACKAGE:
                var params = ab.getQueryString();
                if(typeof(params.type) != 'undefined' && params.type != '' &&  params.type != 'all'){
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" id="all_checked" name="resource_all_ck[]"/>'+
                        '资源名称</th><th width="80">来源</th><th width="164">操作</th><th width="100">目录</th><th width="60">'+
                        '状态</th><th width="114">时间</th></tr>');
                }else{
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" id="all_checked" name="resource_all_ck[]"/>'+
                        '资源名称</th><th width="80">来源</th><th width="164">操作</th><th width="60">'+
                        '状态</th><th width="114">时间</th></tr>');
                }
                break;
            default :
                var params = ab.getQueryString();
                if(typeof(params.keyword) != 'undefined' && params.keyword != '' ){
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" id="all_checked" name="resource_all_ck[]"/>'+
                        '资源名称</th><th width="80">来源</th><th width="164">操作</th><th width="100">目录</th><th width="60">'+
                        '状态</th><th width="114">时间</th></tr>');
                }else{
                    $(".yunfile_r_cont_list").append('<tr><th><input type="checkbox" id="all_checked" name="resource_all_ck[]"/>'+
                        '资源名称</th><th width="80">来源</th><th width="164">操作</th><th width="60">'+
                        '状态</th><th width="114">时间</th></tr>');
                }
                break;
        }
    }

    // 设置列表的tbody
    _this.setListBody = function(data,folderType,parentfolders){
    	if(folderType == _this.constant.BOOK){
    		return;
    	}
        for(var i = 0; i < data.length; i ++){
            var checkbox ='';
            var shortName = data[i].shortName;
            var title = data[i].name;
            var href = "javascript:void(0)";
            var html = '';
            var source = '';
            var time = data[i].createtime;
            var status = '';
            var src = '';
            var schoolYear='';
            if(data[i].schoolYear!=null&&data[i].term!=null){
            	schoolYear='('+data[i].schoolYear+' '+data[i].term+')'
            }
            if(data[i].isdir){
                href = "javascript:clickFolder(\""+data[i].fid+"\")";
                src = _this.__APP__+"/images/yunfile_bkb.png";
            }else{
                var extension = data[i].extension;
                href = "javascript:previewBox(\""+extension+"\",\""+data[i].fid+"\",\""+data[i].name+"\",\""+data[i].uid+"\")";

                if(data[i].fromtype == 0){
                    source = '上传';
                    if(!data[i].isopen){
                        status = "<a href='javascript:;' class='status_setbtn'><span class='status status_pri'></span>私密</a>";
                    }else{
                        status = "<a href='javascript:;' class='status_setbtn'><span  class='status status_pub'></span>公开</a>";
                    }
                }else{
                    source = '收藏';
                }
                src = getShortImg(data[i].extension);
            }

            switch(data[i].foldertype){
	            case _this.constant.FAVORITE:
	            case _this.constant.BEI_KE_BEN:
	            case _this.constant.UNIT:
	            case _this.constant.COURSE:
                    checkbox = "<input type='checkbox' disabled='disabled'/>";
                    html = "<td></td>";
                    break;
	            case _this.constant.BOOK:
                    checkbox = "<input type='checkbox' parentfolder='"+data[i].parentfolder+"' data-isdir='"+data[i].isdir+"' data-value='"+data[i].fid+"' class='single_checked'/>";
                    html = "<td></td>";
                    break;
                default :
                    html = "<td><div class='op_box'>"+ _this.operation(data[i].isdir,data[i],folderType) + "</div></td>";
                    checkbox = "<input type='checkbox' parentfolder='"+data[i].parentfolder+"' data-isdir='"+data[i].isdir+"' data-value='"+data[i].fid+"' class='single_checked'/>";
                    break;
            }


            var tdHtml = "<tr><td>"+checkbox+
                "<a href='"+href+"' title='"+title+"'>"+
                "<img src='"+src+"' alt='"+title+"'/>"+
                "</a><a href='"+href+"' title='"+title+"'>"+
                shortName+"</a>"+
                "</td><td class='color_78'>"+source+"</td>"+
                html;

            var params = ab.getQueryString();
            if((typeof(params.type) != 'undefined' && params.type != '' &&  params.type != 'all') || (typeof(params.keyword) != 'undefined' && params.keyword != '' )){
                var parentName = ''
                var parentShortName = '';
                if(data[i].parentfolder == '0'){
                    parentName = '我的云盘';
                    parentShortName = '我的云盘';
                }else{
                    for(var j = 0; j< parentfolders.length; j++){
                        if(data[i].parentfolder == parentfolders[j].fid){
                            parentName = parentfolders[j].name;
                            parentShortName = parentfolders[j].shortName;
                        }
                    }
                }

                tdHtml = tdHtml + "<td><a href='javascript:clickFolder(\""+data[i].parentfolder+"\")' title=\""+parentName+"\">"+parentShortName+"</a></td>";
            }
            tdHtml = tdHtml + "<td class='color_78'>"+status+"</td>"+"<td>"+time+"</td></tr>"
            $(".yunfile_r_cont_list").append(tdHtml);
        }
    }

    // 文件夹和文件的操作
    _this.operation = function(type,data,folderType){
    	//修改下载之后资源格式问题
    	var fileNam=data.name+"."+data.extension;
        var html = "";
        if(type){
            switch(data.foldertype){           	
            	case _this.constant.PACKAGE:
                    html =
                         "<a href='javascript:void(0);' title='收录' data-isdir='"+data.isdir+"' data-value='"+data.fid+"' class='operation_btn collect_btn'>收录</a>"+
                         "<a href='javascript:void(0);' class='operation_btn delete_folder' parentfolder='"+data.parentfolder+"' data-isdir='"+data.isdir+"' data-value='"+data.fid+"' title='删除'>删除</a>";
                    break;
                default:
                    html =
                        "<a href='javascript:void(0);' title='移动' parentfolder='"+data.parentfolder+"'data-isdir='"+data.isdir+"' data-value='"+data.fid+"' class='move_btn'>移动</a>"+
                            "<a href='javascript:void(0);' title='更多' class='more_btn folder_more_btn'>更多</a>"+
                            "<ul class='more_list' style='display:none;'><li class='first-child'>"+
                            "<a href='javascript:void(0);' title='复制' data-isdir='"+data.isdir+"' data-value='"+data.fid+"' class='copy_btn'>复制</a></li>" +
                            "<li><a href='javascript:void(0);;' class='rename_folder' title='重命名'>重命名</a>"+
                            "</li><li>"+
                            "<a href='javascript:void(0);' class='delete_folder' parentfolder='"+data.parentfolder+"' data-isdir='"+data.isdir+"' data-value='"+data.fid+"' title='删除'>删除</a>"+
                            "</li></ul>";
            }
        }else{
            if(data.fromtype == 0){
                html += "<a href='javascript:void(0);' title='下载' class='dl_btn'  data-value='"+data.fid+"'  filename='"+fileNam +"' >下载</a>";
                if(folderType == _this.constant.COURSE || folderType == _this.constant.UNIT){
                    html += "<a href='javascript:void(0);' data-extension='"+data.extension+"'  title='公开' class='public_btn' book='true'  name='"+data.fid+"' data-name='"+data.name+"'  isopen='"+data.isopen+"'>公开</a>";
                }else{
                    html += "<a href='javascript:void(0);' data-extension='"+data.extension+"'  title='公开' class='public_btn'  name='"+data.fid+"' data-name='"+data.name+"'  isopen='"+data.isopen+"'>公开</a>";
                }
                html += "<a href='javascript:void(0);' title='移动' parentfolder='"+data.parentfolder+"' data-isdir='"+data.isdir+"' data-value='"+data.fid+"' class='move_btn'>移动</a>"+
                    "<a href='javascript:void(0);' title='更多' class='more_btn file_more_btn'>更多</a>"+
                    "<ul class='more_list' style='display:none;'><li class='first-child'>"+
                    "<a href='javascript:void(0);' title='复制' data-isdir='"+data.isdir+"'data-value='"+data.fid+"' class='copy_btn'"+">复制</a>" +
                    "</li><li>"+
                    "<a href='javascript:void(0);' class='rename_folder' title='重命名'>重命名</a>"+
                    "</li><li>"+
                    "<a href='javascript:void(0);' class='delete_folder' parentfolder='"+data.parentfolder+"' data-isdir='"+data.isdir+"' data-value='"+data.fid+"' title='删除'>删除</a>"+
                    "</li></ul>";
            }else{
                html = "<a href='javascript:void(0);' title='下载' class='dl_btn'  data-value='"+data.fid+"'  filename='"+fileNam +"' >下载</a>"+
                    "<a href='javascript:void(0);' title='移动' parentfolder='"+data.parentfolder+"' data-isdir='"+data.isdir+"' data-value='"+data.fid+"' class='move_btn'>移动</a>"+
                    "<a href='javascript:void(0);' title='更多' class='more_btn file_more_btn'>更多</a>"+
                    "<ul class='more_list' style='display:none;'><li class='first-child'>"+
                    "<a href='javascript:void(0);' title='复制' data-isdir='"+data.isdir+"'data-value='"+data.fid+"' class='copy_btn'"+">复制</a>" +
                    "</li><li>"+
                    "<a href='javascript:void(0);' class='rename_folder' title='重命名'>重命名</a>"+
                    "</li><li>"+
                    "<a href='javascript:void(0);' class='delete_folder' parentfolder='"+data.parentfolder+"' data-isdir='"+data.isdir+"' data-value='"+data.fid+"' title='删除'>删除</a>"+
                    "</li></ul>";
            }
        }
        return html;
    };

    // 渲染列表尾部左边的操作html
    _this._templeteFootLeft =function(folderType){
        $(".yunfile_r_fun").empty();
        $(".yunfile_r_fun").css("display","block");

        switch(folderType){  	
        	case _this.constant.NORMAL:
        	case _this.constant.UNIT:
        	case _this.constant.COURSE:
                $(".yunfile_r_fun").append('<a href="javascript:void(0);" class="yunfile_r_fun_btn yunfile_r_fun_btn_add opacityed" data-action="newfolder">新建文件夹</a>'+
                    //'<a href="javascript:void(0);" class="yunfile_r_fun_btn yunfile_r_fun_btn_copy opacityed" title="复制到"  data-action="copy">复制到</a>'+
                    //'<a href="javascript:void(0);" class="yunfile_r_fun_btn yunfile_r_fun_btn_move opacityed" title="移动到" data-action="move">移动到</a>'+
                    '<a href="javascript:void(0);" class="yunfile_r_fun_btn yunfile_r_fun_btn_del opacityed" data-action="del">删除</a>');
                break;
        	case _this.constant.BEI_KE_BEN:
        		$(".yunfile_r_fun").append(
						"<a href='javascript:void(0);' class='yunfile_r_fun_btn yunfile_r_fun_btn_add my_book_add opacityed  yunfile_add_btn' title='新建备课本'>新建备课本</a>"+
						"<a href='javascript:void(0);' class='yunfile_r_fun_btn yunfile_r_fun_btn_del opacityed' id='yunfile_r_fun_btn_del' title='删除' data-action='delbeikeben'>删除</a>");
        		break;
        	case _this.constant.BOOK:
        		break;
            default :
                $(".yunfile_r_fun").append('<a href="javascript:void(0);" class="yunfile_r_fun_btn yunfile_r_fun_btn_del opacityed" title="删除" data-action="del">删除</a>');
        }

        // 在资源分类里面屏蔽"新建文件夹"按钮
        var params = ab.getQueryString();
        if(typeof(params.type) != 'undefined' && params.type != '' &&  params.type != 'all'){
            $(".yunfile_r_fun").empty();
            $(".yunfile_r_fun").append('<a href="javascript:void(0);" class="yunfile_r_fun_btn yunfile_r_fun_btn_del opacityed" title="删除" data-action="del">删除</a>');
        }
    };

    // 渲染列表的分页html
    _this._templeteFootRight = function(pageHtml){
        if(!pageHtml){
            return;
        }
        $(".yunfile_r_fun").append('<div id="list_page" class="page_box fr clearfix"></div>');
        $("#list_page").append(pageHtml);

        $("#list_page a").attr("href","");

        var currentPage = $("#list_page .current").text();
        currentPage  = parseInt(currentPage);
        var prePage = currentPage - 1;
        var nextPage = currentPage + 1;
        $("#list_page .pre").attr("href","javascript:appBase.setQueryString('p',"+prePage+")");
        $("#list_page .next").attr("href","javascript:appBase.setQueryString('p',"+nextPage+")");

        $.each($("#list_page a").not(".pre,.next"),function(){
            var page = $(this).text();
            page = page.replace(/\./g,"");
            $(this).attr("href","javascript:appBase.setQueryString('p',"+page+")");
        });
    };

    // js渲染模板
    _this._templete = function(result){
        var folderType = result.folderType;
        var fid = result.fid;
        var data = result.list;
        var page = result.page;
        var bookFolderType = result.bookFolderType;
        var bookFid = result.bookFid;
        var parentId = result.parentId;
        var parentfolders = result.parentfolders;

        $(".yunfile_r_cont_list tr").remove();

        if(folderType !=  _this.constant.UNIT){
            $(".yunfile_tab_bkb").addClass('yunfile_tab');
            $(".yunfile_tab_bkb").removeClass('yunfile_tab_bkb');
        }
        
        // 选中备课本时不做渲染，等待默认选中第一课时渲染
        if(folderType != _this.constant.BOOK){
        	// 设置上传等按钮
            _this.setNavButton(fid,folderType);

            // 设置列表head
            _this.setTableTh(folderType);

            // 渲染列表body
            _this.setListBody(data,folderType,parentfolders);

            // 渲染列表尾部左边的操作html
            _this._templeteFootLeft(folderType);

            // 渲染列表的分页html
            _this._templeteFootRight(page);
        }
        
        //渲染左边栏
        _this._templeteLeft(bookFid,bookFolderType,parentId);

        // 显示推荐资源包
        if(folderType == _this.constant.COURSE || folderType == _this.constant.UNIT){
            _this._getRecomBag(fid);
        }
    };
    
    //渲染左边栏
    _this._templeteLeft = function(bookFid,bookFolderType,parentId){
    	switch(bookFolderType){
    	    case _this.constant.BOOK:
    	    	var bookdata = book.getUnits(bookFid);
    	    	var content = _this._templeteBook(bookdata);
                _this.changeBook(content);
                if(bookdata.units){
                    appBase.setQueryString('fid',bookdata.units[0].fid);
                    $.address.autoUpdate(true);
                }
                break;
    	    case _this.constant.UNIT:
    	    case _this.constant.COURSE:
                var bookdata = book.getUnits(parentId);
                var content = _this._templeteBook(bookdata);
                _this.changeBook(content);
    			//左边栏样式选中当前单元
    			$(".yunfile_book_map_list_tit_icon").each(function(){
    				if($(this).parent('div').children('a').attr('unit')==bookFid){
    					$(this).parent('div').children('a').css("color",'#229bd7'); 
    					$(this).removeClass('folded');
    				}
    			});
    	    	break;
    		default:
    			var books = book._init() ;
    			content = _this._templeteBooks(books);
    			_this.changeBook(content);    			
    	}
    };
    
    /**
     * 选择书本时
     * @param content
     */
    _this.changeBook =function(content){
        if($('.yunfile_list_cover').length > 0) $('.yunfile_list_cover').empty();
        if($('.yunfile_book_map').length > 0) $('.yunfile_book_map').remove();
        $('.yunfile_list_cover').append(content);
    };
    
    /**
     * 渲染多本备课本模版
     * @private
     */
    _this._templeteBooks = function(books){
        var html="";
        var li = "";

        //html+= '<div class="yunfile_list_cover"  >';
        html+= '<ul class="yunfile_list" id="cloudbook" ';

        if( null != books && ""!= books && books.books  &&  books.books.length > 2){
           // html +="style='height:360px;'"
        }
        html +='>';
        if(books.books){
            $.each(books.books,function(i,n){
                li +=  '<li book="'+n.fid+'">';
                li +='<div class="yunfile_book">';
                li += '<a href="javascript:;"  title="'+ n.name+'">';
                li += '<img  alt="'+ n.name+'" src="'+ n.thumbpath74 +'" /></a>';
                li += '<p class="yunfile_book_des1">';
                li +=  n.name.substring(n.name.length-13,0)+'</p>';
                li +=  '<p class="yunfile_book_des2">';
                li += n.schoolYear+ ' '+n.term;
                li +='</p><a href="javascript:;" title="删除" class="yunfile_del_btn"></a>';
                li +=  '</div></li>';
            })
        }
        html+= li;
        html+= '</ul>';
        return html;
    };
    
    /**
     * 渲染单本备课本模版
     * @private
     */
    _this._templeteBook  = function(book){
        var html="";
        var li = "";
        html+= ' <div class="yunfile_book_map" >';
        if(book.book){
        html+= ' <div class="yunfile_book_map_tit" book="'+book.book.fid+'">';
        html+= ' <img alt="'+book.book.name+'" src="'+book.book.thumbpath+'" />';
        html+= '<p class="yunfile_book_map_tit_des" style="text-align:left" title="'+book.book.name+'">';
        html+=   book.book.name.substring(0,10) +'<br/>';
        html+=  '<span class="yunfile_book_map_tit_more_des">';
        html+= book.book.schoolYear+book.book.term;
        html+= '</span></p>';
        html+= '<span class="slide_sign current"></span></div>';
        }

        html+= ' <div class="yunfile_book_map_list_box"  id="cloudbook">';
        html+= '  <ul class="yunfile_book_map_list">';
        if(book.units){
            $.each(book.units,function(i,n){
                li +=  '<li class="li_unit_'+n.fid+'">';
                li +='<div class="yunfile_book_map_list_tit" style="text-align:left">';
                li += '<span class="yunfile_book_map_list_tit_icon folded"></span>';

                li += '<a href="javascript:;" class="bookunit arrow_up"  unit="'+ n.fid+'" title="'+ n.name+'"';
                var attrstr = "";
                attrstr +=" course='10000' ";
                if('undefined'!= typeof(n.metadata)){
                    if('undefined'!=typeof(n.metadata.book) && null!=n.metadata.book  && ''!= n.metadata.book[0]){
                        attrstr +=" bookcode='" + n.metadata.book[0]+"' ";
                    }
                    if('undefined'!=typeof(n.metadata.unit) && null!=n.metadata.unit   && ''!= n.metadata.unit[0]){
                        attrstr +=" unitcode='" + n.metadata.unit[0]+"' ";
                    }
                    li += attrstr + '>';
                }else{
                    li += '>';
                }
                li += n.shortName;
                li += '</a></div></li>';

            })
        }
        html+= li;
        html+= '</ul></div></div>';
        return html;
    };
    
    // 获取顶部面包屑导航
    _this._getPath = function(fid){
        ab.flag = false;
        $.ajax({
            url:U('yunpan/Ajax/getPath'),
            type:"POST",
            data:{fid:fid},
            dataType:'json',
            success:function(res){
                _this.setNav(fid,res.data);
                ab.flag = true;
            },
            error:function(){
                ab.flag = true;
            }
        });
    };

    // 列表操作
    _this._getList = function(queryParams){
        ab.flag = false;
        $.ajax({
            url:'index.php?app=yunpan&mod=Ajax&act=getList',
            type:"POST",
            data:queryParams,
            dataType:'json',
            success:function(data){
                ab.flag = true;
                var list = data.list;
                var totalPages = data.totalPages;
                var params = ab.getQueryString();
                if(jQuery.isArray(list) && list.length == 0 && typeof(params.p) != "undefined" ){
                    appBase.setQueryString("p", totalPages);
                }else{
                    _this._templete(data);
                }
                // 点击备课时不去渲染顶部面包屑，等待默认选中第一课时渲染
                if(data.folderType != _this.constant.BOOK){
                    _this._getPath(queryParams.fid);
                }
            },
            error:function(){
                ui.error("网络连接错误......");
                ab.flag = true;
            }
        });
    };

    // 获取本课推荐资源包
    _this._getRecomBag = function(fid) {
        ab.flag = false;
        $.ajax({
            url : 'index.php?app=yunpan&mod=Ajax&act=getRecomBag',
            type : "POST",
            data : {fid:fid},
            dataType : 'json',
            success : function(data) {
                _this._templete_recomBag(data);
                ab.flag = true;
            },
            error : function() {
                ui.error("网络连接错误......");
                ab.flag = true;
            }
        });
    };

    // 渲染本课推荐资源包
    _this._templete_recomBag = function(data) {
        if ($.trim(data) == '') {
            return;
        }

        $('.yunfile_r_cont_list').parent().addClass('yunfile_tab_bkb');
        $(".yunfile_tab_bkb").removeClass('yunfile_tab');

        var str = '';
        // 返回的资源包和现在资源包对应的fid相同时不在重复渲染推荐资源包
        var fid = data.fid;
        if(fid == $(".yunfile_res_bag").attr('data-fid')){
        	return;
        }
        var viewUrl = data.viewUrl;
        var bookid = data.bookid;
        var bagName = data.bagInfo.general.title;
        var bagId = data.bagInfo.general.id;
        var score = Math.round((data.bagInfo.statistics.score * 10)/20) / 10;
        var scorecount = data.bagInfo.statistics.scorecount;
        var starStr = '';
        var i = 1;
        for (; i <= score; i++) {
            starStr += '<span class="star"></span>';
        }
        var f = score * 10;
        if (f % 10 != 0) {
            starStr += '<span class="star star_half"></span>';
            i++;
        }
        for (; i <= 5; i++) {
            starStr += '<span class="star star_gray"></span>';
        }

        // 资源包内资源列表
        var resList = data.bagRes;
        var res = '';
        for ( var i = 0; i < resList.length; i++) {
            res += '<li style="cursor:pointer">'
                + '<img style="width:16px;height:16px;" alt="'
                + resList[i].general.title + '" src="'
                + getShortImg(resList[i].general.extension) + '">'
                + '<a href="' + viewUrl + resList[i].general.id
                + '" target="_blank">' + resList[i].general.title
                + '</a></li>';
        }
        var pageStr = '';
        if(data.showPage){
        	pageStr ='<a id="bag_next_page" onclick="getBagResByPage(\'' + bagId + '\',this);" page="2" href="javascript:;" title="下一页" class="dir_btn_next"></a>'
        	+ '<a id="bag_pre_page" onclick="getBagResByPage(\'' + bagId + '\',this);" page="0" href="javascript:;" title="上 一页" class="dir_btn_prev disabled"></a>';
        }
        str += '<div class="book_detail_resource">'
            + '<div class="book_detail_resource_tit">'
            + pageStr
            + '<span class="title_icon"></span>为您推荐本课的资源包</div>'
            + '<div class="book_detail_resource_detail clearfix">'
            + '    <div class="book_package fl clearfix">'
            + '        <div style="" class="book_package_logo fl"></div>'
            + '        <div class="book_package_des fl">'
            + '            <div class="book_package_des_top">' + '<p>'
            + bagName
            + '</p>'
            + '<p class="clearfix star_box">'
            + starStr
            + '<span class="star_des fl"><span class="book_package_f18">'
            + score
            + '</span>'
            + '<span class="book_package_f12">('
            + scorecount
            + ')</span></span></p></div>'
            + '           <div class="book_package_des_bot">'
            + '<a href="javascript:;" onclick="collectBag(\''
            + bookid
            + '\',\''
            + bagId
            + '\');" title="收录" class="book_package_collect opacityed">收录</a>'
            + '<!-- <a href="javascript:;" title="下载" class="book_package_dl opacityed">下载</a> -->'
            + '           </div>'
            + '        </div>'
            + '    </div>'
            + '   <div class="resource_details fr">'
            + '       <div class="resource_details_tit">'
            + '           <p class="fl">包含以下资源</p>'
            + '           <!-- <a href="javascript:;" class="more_res_btn" id="more_res_btn" title="更多">更多'
            + '               <span class="more_tri"></span>'
            + '           </a> -->'
            + '       </div>'
            + '     <ul id="bag_res_list" class="resource_details_list clearfix">'
            + res
            + '</ul>' + '</div>' + '</div>' + '</div>';
        $(".yunfile_res_bag").attr("data-fid",fid);
        $(".yunfile_res_bag").html(str);
        $(".yunfile_res_bag").show();
    };

    // 初始化
    _this.init = function(params){
    	var fid = $('.yunfile_res_bag').attr("data-fid");
        if($('.yunfile_res_bag').length > 0 && fid != params.fid){
            $('.yunfile_res_bag').hide();
        }

        var keyword = params.keyword;

        if(typeof(keyword) != 'undefined'){
            keyword = decodeURIComponent(keyword);
            jQuery("#yunfile_search_input").val(keyword);
        }else{
            jQuery("#yunfile_search_input").val("");
        }

        //控制右边高度 by  frsun
		$(".yunfile_r_cont_list").parent().removeClass("yunfile_tab_mypub").removeClass("yunfile_tab_mydownload");

        var queryParams = {
            type:params.type,
            keyword:params.keyword,
            fid:params.fid,
            p:params.p
        };
        _this._getList(queryParams);
    };

    ab.grid = _this;
})(appBase,jQuery);