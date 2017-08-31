(function($){
    $(function(){
    	//删除文件全局变量
    	var g_params;
    	
        //点击备课本(左边栏渲染多本备课本)
        $('.yunfile_book a').die().live('click',function(){
            var $book= $(this).parent().parent().attr('book');
            if(!$(this).hasClass('yunfile_del_btn')){     	
            	$.address.autoUpdate(false);
                appBase.setQueryString('p','');
                appBase.setQueryString('type','');
                appBase.setQueryString('fid','');
                appBase.setQueryString('action','');
                appBase.setQueryString('keyword','');
                $.address.autoUpdate(true);
                appBase.setQueryString('fid',$book);
            }
        });
    	
        //点击备课本(左边栏渲染单本备课本)
        $('.yunfile_book_map_tit').die().live('click',function(){
        	var $book = $(this).attr('book');
        	$.address.autoUpdate(false);
        	appBase.setQueryString('p','');
        	appBase.setQueryString('type','');
        	appBase.setQueryString('fid','');
        	appBase.setQueryString('action','');
            appBase.setQueryString('keyword','');
            $.address.autoUpdate(true);
        	appBase.setQueryString('fid',$book);
        });
        
        //备课本分享
    	$('.book_share_btn').live('click',function(){
        	var checkedSize = $(".single_checked:checked").size();
            var data =$(this).attr('data-value');
        	if(data!=null){
        		 $('.del_box').text(" 确定分享此备课本?");
                 $("#share_button").css('display','inline');
        	}
        	else{
        		 if(checkedSize < 1){
                     $('.del_box').text("请选择要分享的备课本!");
                     $("#share_button").css('display','none');
                 }else{
                     $('.del_box').text(" 确定分享此备课本?");
                     $("#share_button").css('display','inline');
                 }
        	}
            popup($('#popup_share')); //弹出分享备课本
        	
        });   
        
        //关闭弹出层
        $('.closePop').live('click',function(){
            popout($('.closePop'));
            ui.box.close();
        });

        //弹出添加备课本
        $('.my_book_add_1').live('click',function(){
            //创建备课本完成刷新
           book.onCreateSuccess = function(){
        	   $.address.autoUpdate(false);
        	   appBase.setQueryString('p','');
               appBase.setQueryString('type','');
               appBase.setQueryString('fid','');
               appBase.setQueryString('action','');
               appBase.setQueryString('keyword','');
               $.address.autoUpdate(true);
               appBase.setQueryString("fid",appBase.bkDir);
            };
			book.show();
			return false;
        });
        
        //弹出添加备课本
        $('.yunfile_add_btn').live('click',function(){
            //创建备课本完成刷新
           book.onCreateSuccess = function(){
        	   var param=appBase.getQueryString();
               appBase.grid.init(param);
            };
			book.show();
			return false;
        });
        
        $('.my_beikeben_btn').live('click',function(){
        	window.location.href="index.php?app=yunpan&mod=Index&act=index#?fid="+appBase.bkDir;
        })


        //删除备课本
        $('.yunfile_del_btn').live('click',function(){
            var $book= $(this).parent().parent().attr('book');
            book.onDelSuccess = function(){
                var param=appBase.getQueryString();
                appBase.grid.init(param);
            };
            book.showdel($book);
        });

        //文件操作
        $('.yunfile_r_fun_btn').live('click',function(){
            var $action  = $(this).attr('data-action');       
            switch($action){
                case 'copy':
                    popup($('#popup_copy')); //弹出文件copy
                    break;
                case 'move':           
                    popup($('#popup_move'));//弹出文件move
                    break;
                case 'del':
                    deleteFolders();// 批量删除文件夹
                    break;
                case 'newfolder':
                    createFolder();
                    break;
                case 'upload':
                    popup($('#popup_upload')); //弹出上传文件
                    break;
                case 'delbeikeben':               
                	deleteBeikeben();//批量删除备课本
                	break;
            }
        });

        //文件上传
 /*       $('.yunfile_upload_btn').live('click',function(){
          //  popup($('#popup_upload')); //弹出上传文件
            uploadBox.show();
        });
*/
        // 列表的全选
        $("#all_checked").live('click',function(){
            var value = $(this).prop("checked");
            $(".single_checked").prop("checked",value);
        });

        // 列表的单选操作
        $(".single_checked").live('click',function(){
            var size = $(".single_checked").size();
            var checkedSize = $(".single_checked:checked").size();
            if(checkedSize < size){
                $("#all_checked").prop("checked",false);
            }else{
                $("#all_checked").prop("checked",true);
            }
        });


        // 文件夹选择更多操作
        $(".folder_more_btn").live('click',function(){
            $(".more_list").css('display','none');
            $(".more_list").parent(".op_box").css('z-index','auto');
            $(this).next(".more_list").css('display','block');
            $(this).parent(".op_box").css('z-index','200');
        });

        // 文件选择更多操作
        $(".file_more_btn").live('click',function(){
            $(".more_list").css('display','none');
            $(".more_list").parent(".op_box").css('z-index','auto');
            $(this).next(".more_list").css('display','block');
            $(this).parent(".op_box").css('z-index','200');
        });

        // 隐藏更多操作
        $(document).click(function (e) {
            var target = e.target;
            var fileArray = $(".file_more_btn").toArray();
            var folderArray = $(".folder_more_btn").toArray();
            var moreArray=$(".public_btn").toArray();
            //影藏状态按钮弹出框
            if( $.inArray(target,moreArray)==-1){
            	$(".status_radio_box").hide();
            }
            if(!($.inArray(target,fileArray) != -1 || $.inArray(target,folderArray) != -1)){
                $(".more_list").css('display','none');
                $(".more_list").parent(".op_box").css('z-index','auto');
            }
        });

        // 新建文件夹step1
        function createFolder(){
            var _display = $("#new_folder_tr").css('display');
            if(_display == 'none'){
                $(".new_folder").val("新建文件夹");
                if($.browser.msie && parseInt($.browser.version) <= 7.0){
                    $("#new_folder_tr").css('display','block');
                }else{
                    $("#new_folder_tr").css('display','table-row');
                }
            }
            $(".new_folder").focus();
            $(".new_folder").select();
        }


        // 新建文件夹step2(提交)
        $("#create_mistake").live('click',function(){
            $("#new_folder_tr").css('display','none');
        });
        $("#create_correct").live('click',function(){
            //$("#new_folder_tr").css('display','none');

            var name = $.trim($(".new_folder").val());

            if(name == ''){
                ui.error("文件夹名不能为空!");
                return;
            }

            var queryString = appBase.getQueryString();
            var fid=queryString.fid;

            var queryParams = {
                name:name,
                fid:fid
            };
            $.ajax({
                url:'index.php?app=yunpan&mod=Ajax&act=createFolder',
                type:"POST",
                data:queryParams,
                dataType:'json',
                beforeSend:function(){
                    $('#create_mistake').hide();
                    $('#create_correct').hide();
                    $('#create_now').show();
                },
                success:function(data){
                    data = $.parseJSON(data);
                    if(data.status == '500'){
                    	$('#create_mistake').show();
                        $('#create_correct').show();
                        $('#create_now').hide();
                        ui.error(data.msg);
                    }else{
                        var queryString = appBase.getQueryString();
                        appBase.grid.init(queryString);
                    }
                },
                error:function(){
                    ui.error("网络连接错误......");
                }
            });
        });


        // 导航条点击事件
        $(".yunpan_nav_a").live('click',function(){
            $(this).parent().nextAll().remove();
            var fid = $(this).attr('data-value');
            $.address.autoUpdate(false);
            appBase.setQueryString('p','');
            appBase.setQueryString('type','');
            appBase.setQueryString('fid','');
            appBase.setQueryString('keyword','');
            $.address.autoUpdate(true);
            appBase.setQueryString('fid',fid);
        });

        // 文件下载
        $(".dl_btn ").live('click',function(){
            var fileId = $(this).attr('data-value');
            var filename =  $(this).attr('filename');
            download(fileId,filename);
        });

        // 删除单个文件或文件夹
        $(".delete_folder").live('click',function(){
            var fid = $(this).attr("data-value");
            var isdir = $(this).attr("data-isdir");
            var parentfolder=$(this).attr("parentfolder");
            var queryParam = {
                fid:fid,
                isdir:isdir,
                parentfolder:parentfolder
            };
            deleteFile(queryParam);
        });
        
        $(".collect_btn").live('click',function(){
        	var fid = $(this).attr('data-value');
        	var param = {fid: fid};
        	collectResourcePackage(param);
        });
        
        // 验证删除的合法性
        function checkDeleteLegal(params){
        	var result = 'not empty';
            $.ajax({
                url:'index.php?app=yunpan&mod=Ajax&act=checkDeleteLegal',
                type:"POST",
                async: false,
                data:params,
                dataType:'json',
                success:function(data){
                    data = $.parseJSON(data);
                    if(data.status == '200'){
                    	result = 'not empty';
                    } else {
                    	result = 'is empty';
                    }
                },
                error:function(){
                    ui.error("网络连接错误......");
                }
            });
            return result;
        }
        
        
        //我的收藏夹中的资源包收录
        function collectResourcePackage(params){
            $.ajax({
                url:'index.php?app=yunpan&mod=Ajax&act=collectResoucePackage',
                type:"POST",
                data:params,
                dataType:'json',
                success:function(data){
                    if(data.status){
                        ui.success(data.message);
                    }else{
                        ui.error(data.message);
                    }
                },
                error:function(){
                    ui.error("网络连接错误......");
                }
            });
        }
        
        // 删除文件的ajax操作
        function deleteFolderAjax(params){
            $.ajax({
                url:'index.php?app=yunpan&mod=Ajax&act=deleteFolder',
                type:"POST",
                data:params,
                dataType:'json',
                success:function(data){
                    data = $.parseJSON(data);
                    if(data.status == '200'){
                        ui.success(data.msg);
                        var queryString = appBase.getQueryString();                    
                    	appBase.grid.init(queryString);
                    }else{
                        ui.error(data.msg);
                    }
                },
                error:function(){
                    ui.error("网络连接错误......");
                }
            });
        }
        
        //删除备课本的ajax操作
        function deleteBeikebenAjax(params){
        	 $.ajax({
                 url:'index.php?app=yunpan&mod=Ajax&act=deleteBeikeben',
                 type:"POST",
                 data:params,
                 dataType:'json',
                 success:function(data){
                     data = $.parseJSON(data);
                     if(data.status == '200'){
                    	 ui.success(data.msg);
                    	 var param=appBase.getQueryString();
                    	 appBase.grid.init(param);
                     }else{
                         ui.error(data.msg);
                     }
                 },
                 error:function(){
                	 ui.error("网络连接错误......");
                 }
             });
        }

        // 批量删除文件夹及文件
        function deleteFolders(){
            var checkedSize = $(".single_checked:checked").size();
            var queryParams = createFileQueryParams();
            if(checkedSize < 1){
                $('.del_box').text("请选择要删除的资源!");
                $("#del_button").css('display','none');
                popup($('#popup_del'));
                return;
            }else if(checkedSize==1){
                if(queryParams.isdir=='true' && 'not empty'==checkDeleteLegal(queryParams)){
                	g_params = queryParams;
                	$('.del_box').text("此文件夹有内容，是否确认删除？");
                    $("#del_button").css('display','inline');
                    popup($('#popup_del2')); //弹出文件删除
                	return;
                }
            }
            $('.del_box').text(" 确定删除此资源?");
            $("#del_button").css('display','inline');
            popup($('#popup_del')); //弹出文件删除
        }
        
        //删除单个文件或文件夹
        function deleteFile(param){
        	g_params = param;
            if(param.isdir=='true'){
            	if('not empty'==checkDeleteLegal(param)){
            		$("#popup_del2 .del_box").html('此文件夹有内容，是否确认删除？');
            		popup($('#popup_del2'));
            		return;
            	}
            } 
            $("#popup_del2 .del_box").html('确定删除此资源？');
            popup($('#popup_del2'));
        }


        // 批量删除备课本
        function deleteBeikeben(){
            var checkedSize = $(".single_checked:checked").size();      
            if(checkedSize < 1){
                $('.del_box').text("请选择要删除的备课本!");
                $("#delbeikeben_button").css('display','none');
            }else{
                $('.del_box').text(" 确定删除此备课本?");
                $("#delbeikeben_button").css('display','inline');
            }
            $('.del_choice').show();
            popup($('#beikeben_del')); //弹出文件删除
        }

        // 确认批量删除备课本
        $("#delbeikeben_button").live('click',function(){
            popout($('.closePop'));
            var values = $(".single_checked:checked");
            var arr = [];
            var arr1 = [];
            for(var i = 0; i < values.length; i++){
                arr[i] = $(values[i]).attr('data-value');
            }

            for(var i = 0; i < values.length; i++){
                arr1[i] = $(values[i]).attr('data-isdir');
            }

            var fids = arr.join(',');
            var isdirs =  arr1.join(',');

            var queryParams = {
                fid:fids,
                isdir:isdirs
            };
            deleteBeikebenAjax(queryParams);
        });
        
        // 确认批量删除
        $("#del_button").live('click',function(){
            popout($('.closePop'));
            var queryParams = createFileQueryParams();
            deleteFolderAjax(queryParams);
        });
        
        //文件夹不为空，确定删除
        $("#popup_del2 #ok").live('click', function(){
        	popout($('.closePop'));
        	deleteFolderAjax(g_params);
        });

        // 文件的重命名
        $(".rename_folder").live('click',function(){
                var $oldContent = $(this).parents('tr').children("td:first").children("a:last");
                var oldName = $oldContent.attr('title');
                $oldContent.css('display','none');
                oldName = oldName.replace(/\"/g,'&quot;');
                var html = '<input type="text" value="'+oldName+'" id="new_name_folder"/>'+
                '<img alt="correct" src="'+APP +'/images/correct.png" class="rename_sign" id="rename_correct" />'+
                '<img src="'+APP +'/images/mistake.png" alt="mistake" class="rename_sign" id="rename_mistake"/>'+
                '<img src="'+APP +'/images/loading1.gif" class="rename_sign" id="rename_now" style="display:none;"/>';
                $oldContent.after(html);
                $oldContent.nextAll('input').focus();
                $oldContent.nextAll('input').select();
        });
        $("#rename_correct").live('click',function(){
            var name = $.trim($("#new_name_folder").val());
            if(name == ''){
                ui.error('名字不能为空!');
                return;
            }
            var oldName = $("#new_name_folder").prev("a").attr('title');
            if(oldName == name){
                undoRenameFolder();
                return;
            }
            var fid = $("#new_name_folder").prevAll('input').attr('data-value');
            var isdir = $("#new_name_folder").prevAll('input').attr('data-isdir');
            var params = {
                name:name,
                fid:fid,
                isdir:isdir
            };
            $.ajax({
                url:'index.php?app=yunpan&mod=Ajax&act=renameFolder',
                type:"POST",
                data:params,
                dataType:'json',
                beforeSend:function(){
                    $('#rename_correct').hide();
                    $('#rename_mistake').hide();
                    $('#rename_now').show();
                },
                success:function(data){
                    data = $.parseJSON(data);
                    if(data.status == '200'){
                        ui.success(data.msg);
                        var queryString = appBase.getQueryString();
                        appBase.grid.init(queryString);
                    }else{
                        ui.error(data.msg);
                        undoRenameFolder();
                    }
                },
                error:function(){
                    ui.error("网络连接错误......");
                    undoRenameFolder();
                }
            });

        });
        $("#rename_mistake").live('click',undoRenameFolder);
        function undoRenameFolder(){
            $("#new_name_folder").nextAll('img').remove();
            $("#new_name_folder").css('display','none');
            $("#new_name_folder").prev('a').css('display','inline');
            $("#new_name_folder").remove();
        }

        // 移动文件夹及文件
        $(".move_btn").live('click',function(){
            var fid = $(this).attr('data-value');
            var isdir = $(this).attr('data-isdir');
            var parentfolder=$(this).attr("parentfolder");
            ui.box.load(U('yunpan/CloudDisk/moveTempl'),null,null,{'fid':fid,'isdir':isdir,'parentfolder':parentfolder});
        });

        // 复制文件夹及文件
        $(".copy_btn").live('click',function(){
            var fid = $(this).attr('data-value');
            var isdir = $(this).attr('data-isdir');

            // 检测容量是否足够
            var params = {
                fid:fid,
                isdir:isdir
            };
            $.ajax({
                url:'index.php?app=yunpan&mod=Ajax&act=judgeCapacity',
                type:"POST",
                data:params,
                dataType:'json',
                success:function(data){
                    data = $.parseJSON(data);
                    if(data.status == '200'){
                        ui.box.load(U('yunpan/CloudDisk/copyTempl'),null,null,{'fid':fid,'isdir':isdir});
                    }else{
                        ui.error(data.msg);
                    }
                },
                error:function(){
                    ui.error("网络连接错误......");
                }
            });
        });

        $('.copy_btn_beike').live('click',function(){
            var checkeds =  $('.my_book_details li input:checked');
            if(checkeds.length > 1 ){
                ui.error('只能选择一个文件！');
                return ;
            }

            var fid = $('.my_book_details li input:checked').attr('data-value');
            if('undefined'==typeof(fid) || ''==fid){
                    ui.error('请选择文件！');
                   return ;
            }
            var isdir = false;
            ui.box.load(U('yunpan/CloudDisk/copyTempl'),null,null,{'fid':fid,'isdir':isdir});
        });

        $('.move_btn_beike').live('click',function(){
            var checkeds =  $('.my_book_details li input:checked');
            if(checkeds.length > 1 ){
                ui.error('只能选择一个文件！');
                return ;
            }

            var fid = $('.my_book_details li input:checked').attr('data-value');
            if('undefined'==typeof(fid) || ''==fid){
                ui.error('请选择文件！');
                return ;
            }
            var isdir = false;
            ui.box.load(U('yunpan/CloudDisk/moveTempl'),null,null,{'fid':fid,'isdir':isdir});
        });

        //点击文件公开按钮
        $(".public_btn").live('click',function(e){     	        	
        	if('false'==$(this).attr('isopen')){     		
        		var isbook=$(this).attr("book");
            	var fid=$(this).attr("name"); 
            	var data_name=$(this).attr("data-name");
            	var extension=$(this).attr("data-extension");
            	openRs.show(fid,data_name,isbook,extension);
       	}else{
        		ui.success('已公开，请查阅您的公开记录');
        	}
        });
        
        //点击公开记录的公开位置按钮
        $('.record_btn').live('click',function(){
        	var name=$(this).attr('id');
        	 $('.record_btn').removeClass('current');
             $(this).addClass('current');
             if(name=='wdzy_1'){
            	 //点击‘我的主页’按钮
            	jQuery.address.autoUpdate(false);
           	    appBase.setQueryString('p','');
           	    jQuery.address.autoUpdate(true);
           	    appBase.setQueryString('open_position','01');
            	
             }else if(name=='xkzy_1'){
            	 //点击‘学科资源’按钮
            	 jQuery.address.autoUpdate(false);
            	  appBase.setQueryString('p','');
            	  jQuery.address.autoUpdate(true);
            	  appBase.setQueryString('open_position','02');
             }
            
        	
        });
        
        //选择备课本单元
        $('.bookunit').live('click',function(){
            	var $unit  = $(this).attr('unit');    	
            	$.address.autoUpdate(false);
                appBase.setQueryString('p','');
                appBase.setQueryString('type','');
                appBase.setQueryString('fid','');
                appBase.setQueryString('keyword','');
                $.address.autoUpdate(true);
                appBase.setQueryString('fid',$unit);       
        });

        //键盘监听,主要实现搜索
        $(document).keypress(function(e){
        	switch(e.which)    
            {    
                // Enter监听 
                case 13:   
                	var keyword=$("#yunfile_search_input").val();
                	if(keyword==null||keyword==''){
                		$("#yunfile_search_input").focus();
                	}else{
                		 appBase.gridBar.search();
                	}
                    break;  
                                
                         
            }    
        });
    });
})(jQuery);

