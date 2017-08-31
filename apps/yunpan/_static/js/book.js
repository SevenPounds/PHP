/**
 * Created by cheng on 14-4-12.
 */
var book=(function(){
    return {
            //初始化个人备课本
           _init:function(){
               var content = '',t = Math.random();
               var data ={widget_appname:'yunpan',t:t};
               $.ajax({
                   url:U('widget/CloudDiskMenu/getBooks'),
                   type:'post',
                   dataType:'json',
                   data:data,
                   async: false,
                   success:function(res){
                       content = res;
                   },
                   error:function(msg){

                   }
               });
               return content;
           },

           //初始化弹出层模版
           show:function(){
               this.showblackout();
               if($('#popup_addBook').length > 0) {
                   // TODO:???
               }else{
               var html = '<div class="popup popup_addBook none" id="popup_addBook" style="top:25%">';
                   html += '<div class="popup_tit" id="addboo_closBtn">';
                   html += '<a href="javascript:;" class="closeBtn hiddenText addbook_close" title="关闭" >关闭</a>新建备课本</div>';
                   html += '<div class="addBook_box">';
                   html += '<div class="addBook_input"><label for="phase"> 学段： </label>';
                   html += '<select id="book_phase"><option>请选择</option> </select></div>';
                   html += '<div class="addBook_input"><label for="subject"> 学科： </label>';
                   html += '<select id="book_subject"><option>请选择</option> </select></div>';
                   html += '<div class="addBook_input"><label for="stage"> 年级：</label>';
                   html += '<select id="book_stage"> <option>请选择</option> </select> </div>';
                   html += '<div class="addBook_input"><label for="edition">版本： </label>';
                   html += ' <select id="book_edition"> <option>请选择</option> </select></div>';
                   html += '<div id="addBook_loading_div"></div>';
                   html += '</div>';
                   html += '<div class="addBook_choice" id="addBook_Confirmation">';
                   html += '<a href="javascript:;" class="dropBtn opacityed  addbook_cancel" title="取消">取消</a>';
                   html += '<a href="javascript:;" class="enterBtn opacityed addbook_confirm"  title="确定">确定</a></div></div>';
                   $('body').prepend(html);
               }
               $('#popup_addBook').show();
               this.initCreateNew();
           },


            /**
             * 添加弹窗
             * @return void
             */
            showblackout: function() {
                if($('.book-modal-blackout').length > 0) {
                    // TODO:???
                } else {
                    var height = $('body').height() > $(window).height() ? $('body').height() : $(window).height();
                    height = height > 1100 ? height : 1100;
                    var divHtml = '<div class ="book-modal-blackout" ><iframe style="z-index:-1;position: absolute;visibility:inherit;width:'+$('body').width()+'px;height:'+height+'px;top:0;left:0;filter=\'progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)\'"'+
                        'src="about:blank"  border="0" frameborder="0"></iframe></div>';
                    $(divHtml).css({
                        height:height+'px',width:$('body').width()+'px',zIndex: 999, opacity: 0.5
                    }).appendTo(document.body);
                }
           },

          //关闭弹出层
          close:function(){
              $('.addBook_box').remove();
              $('#popup_addBook').remove();
              $('#beikeben_delBox').remove();
              $('.book-modal-blackout').remove();
          },

            //弹出显示创建
           initCreateNew:function(){
               var _this = this;
               $.ajax({
                    url:U('yunpan/CloudBook/select'),
                    type:'post',
                    success:function(res){
                        if($('.addBook_box').length >0 )  $('.addBook_box').remove();
                        if($('#addboo_closBtn').length >0 )  $('#addboo_closBtn').after(res);
                    },
                    error:function(msg){

                    }
               });
               //变更学段
               $('#book_phase').die().live('change',function(){
                   if( '0'!= $(this).val() )  {
                       var publisher = '<option value="0">请选择</option>';
                       var nodes = book.getSubject();
                       $.each(nodes,function(i,n){
                           publisher += '<option value="' + n.code + '" >' + n.name + '</option>';
                       });
                       $('#book_subject').html('').html(publisher);
                       $('#book_stage').html('').html('<option value="0">请先选择年级</option>');
                       $('#book_edition').html('').html('<option value="0">请先选择年级</option>');
                   }else{
                	   $('#book_subject').html('').html('<option value="0">请先选择年级</option>');
                	   $('#book_stage').html('').html('<option value="0">请先选择学科</option>');
                       $('#book_edition').html('').html('<option value="0">请先选择年级</option>');
                   }
               });
               
               //变更学科
               $('#book_subject').die().live('change',function(){
                   if( '0'!= $(this).val() )  {
                       var publisher = '<option value="0">请选择</option>';
                       var nodes = book.getStage();
                       $.each(nodes,function(i,n){
                           publisher += '<option value="' + n.code + '" >' + n.name + '</option>';
                       });
                       $('#book_stage').html('').html(publisher);
                       $('#book_edition').html('').html('<option value="0">请先选择年级</option>');
                   }else{
                	   $('#book_stage').html('').html('<option value="0">请先选择学科</option>');
                       $('#book_edition').html('').html('<option value="0">请先选择年级</option>');
                   }
               });
                //变更年级
               $('#book_stage').die().live('change',function(){
                   if('0'!= $(this).val() )  {
                       var publisher = '<option value="0">请选择</option>';
                       var nodes = book.getEdition();
                       $.each(nodes,function(i,n){
                           publisher += '<option value="' + n.code + '" >' + n.name + '</option>';
                       });
                       $('#book_edition').html('').html(publisher);
                   }else{
                	   $('#book_edition').html('').html('<option value="0">请先选择年级</option>');
                   }
               });

               //添加书本取消
               $('.addbook_cancel').die().live('click',function(){
                   _this.close();
               });

               //添加书本关闭
               $('.addbook_close').die().live('click',function(){
                   _this.close();
               });


               //添加书本确定
               $('.addbook_confirm').die().live('click',function(){
                   _this.createNew();
               });

           },

            //创建备课本
           createNew:function(){
               var _this = this;
               var data = {};
               data.phase= $('#book_phase option:selected').text();
               data.phaseCode = $('#book_phase').val();
               data.stage = $('#book_stage option:selected').text();
               data.stageCode = $('#book_stage').val();
               data.subject = $('#book_subject option:selected').text();
               data.subjectCode = $('#book_subject').val();
               data.edition = $('#book_edition option:selected').text();
               data.editionCode = $('#book_edition').val();
               
               if('0'==data.phaseCode||data.phaseCode==null){
                   ui.error('请选择学段!');
                   return;
               }               
               if('0'==data.subjectCode||data.subjectCode==null){
                   ui.error('请选择学科!');
                   return;
               }
               if('0'==data.stageCode||data.stageCode==null){
                   ui.error('请选择年级!');
                   return;
               }
               if('0'==data.editionCode||data.editionCode==null){
                   ui.error('请选择版本!');
                   return;
               }

               //TODO 后台加载
               //data.bkdirId= '' ;
                $.ajax({
                    url:U('yunpan/CloudBook/create'),
                    type:'post',
                    dataType:'json',
                    data:data,
                    beforeSend:function(){
                        $('#addBook_loading_div').show();
                        $('#addBook_Confirmation').hide();
                    },
                    complete:function(){
                        $('#addBook_loading_div').hide();
                    },
                    success:function(res){
                            if(res.status==1){
                                ui.success(res.info,2);
                                _this.close();
                                //TODO
                                _this.onCreateSuccess();
                            }else{
                                ui.error(res.info,2);
                                _this.close();
                                _this.onCreateFaild();
                            }
                    },
                    error:function(msg){
                        _this.onCreateFaild();
                    }
                })
            },

            useHistory:function(){

            },

           /**
           * @param param { fid:fids};
           */
            del:function(fids){
               var _this = this;
                $.ajax({
                    url:U('yunpan/Ajax/deleteBeikeben'),
                    type:"POST",
                    data:{fid:fids},
                    dataType:'json',
                    success:function(data){
                        data = $.parseJSON(data);
                        if(data.status == '200'){
                            ui.success(data.msg);
                            _this.close();
                            _this.onDelSuccess();
                        }else{
                            ui.error(data.msg);
                            _this.close();
                            _this.onDelFaild();
                        }
                    },
                    error:function(){
                        _this.onDelFaild();
                    }
                });
            },

            /**
             * 备课本删除展现
             * @param fids array()
             */
            showdel:function(fids){
                var _this = this;
                this.showblackout();
                if($('#beikeben_delBox').length > 0) {
                    // TODO:???
                }else{
                    var html='<div class="popup beikeben_del none" id="beikeben_delBox">';
                    var btnstyle = "";
                    html += '<div class="popup_tit">';
                    html += ' <a href="javascript:;" class="closeBtn hiddenText delbeikeben_cancel" title="关闭">关闭</a>';
                    html += '删除提醒';
                    html += '</div>';
                    html += '<div class="del_box">';
                    if(fids.length < 1){
                        html += '  请选择要删除的备课本!';
                        btnstyle = 'style="display:none;"'
                    }else{
                        html += '  确定删除此备课本？';
                        btnstyle = 'style="display:inline;"'
                    }

                    html += '</div>';
                    html += ' <div class="addBook_choice del_choice">';
                    html += ' <a href="javascript:void(0);" class="dropBtn opacityed  delbeikeben_cancel " title="取消">取消</a>';
                    html += ' <a href="javascript:void(0);" class="enterBtn opacityed" id="delbeikeben_confirm" title="确定"'+btnstyle+'>确定</a>';
                    html += ' </div></div>';

                    $('body').prepend(html);
                }
                $('#delbeikeben_confirm').live('click',function(){
                	$('#delbeikeben_confirm').die('click');
                	_this.del(fids);
                });
                $('.delbeikeben_cancel').live('click',function(){
                    _this.close();
                });

                $('#beikeben_delBox').show();
            },

            //获取课本单元信息
            getUnits:function(book){
                var content = '',t = Math.random();
                var data ={widget_appname:'yunpan',t:t ,book:book};
                $.ajax({
                    url:U('widget/CloudDiskMenu/getBookUnits'),
                    type:'post',
                    data:data,
                    dataType:'json',
                    async: false,
                    success:function(res){
                        content = res;
                    },
                    error:function(msg){

                    }
                });
                return content;
            },

            //获取课本单元详细内容
            getUnitLesson:function(unit){
                var content = '',t = Math.random();
                var data ={widget_appname:'yunpan',t:t ,unit:unit};
                $.ajax({
                    url:U('widget/CloudDiskMenu/getUnitLessons'),
                    type:'post',
                    data:data,
                    async: false,
                    success:function(res){
                        content = res;
                    },
                    error:function(msg){

                    }
                });
                return content;
            },

            showLesson:function(unit){
                var unitobj = $(".li_unit_"+unit);
                if(unitobj.find("ul").length > 0){

                }else{
                    var content = this.getUnitLesson(unit);
                    unitobj.find('div').after(content);
                    if($(".li_unit_"+unit+" a:first").hasClass('arrow_up')){
                        $(".li_unit_"+unit+" a:first").removeClass('arrow_up');
                        $(".li_unit_"+unit+" a:first").addClass('arrow_down');
                    }
                }
            },
            //获取学科信息信息
            getSubject:function(subject,grade){
                var content = '',t = Math.random();
                var data ={phase:$('#book_phase').val()};
                $.ajax({
                    url:U('yunpan/CloudBook/subject'),
                    type:'post',
                    dataType:'json',
                    data:data,
                    async: false,
                    success:function(res){
                        if(res.status==1){
                            content = res.data;
                        }
                    },
                    error:function(msg){

                    }
                });
                return content;
            },
            
            //获取版本 
            getEdition:function(){
            	 var content = '',t = Math.random();
                 var data ={phase:$('#book_phase').val(),subject:$('#book_subject').val(),stage:$('#book_stage').val()};
                 $.ajax({
                     url:U('yunpan/CloudBook/edition'),
                     type:'post',
                     dataType:'json',
                     data:data,
                     async: false,
                     success:function(res){
                         if(res.status==1){
                             content = res.data;
                         }
                     },
                     error:function(msg){

                     }
                 });
                 return content;
            
            },
            
            //获取出版社信息
            getStage:function(subject,grade){
                var content = '',t = Math.random();
                var data ={phase:$('#book_phase').val(),subject:$('#book_subject').val()};
                $.ajax({
                    url:U('yunpan/CloudBook/stage'),
                    type:'post',
                    dataType:'json',
                    data:data,
                    async: false,
                    success:function(res){
                        if(res.status==1){
                            content = res.data;
                        }
                    },
                    error:function(msg){

                    }
                });
                return content;
            },

            //创建备课本成功
            onCreateSuccess:function(){

            },

            //创建备课本失败
            onCreateFaild:function(){

            },

            //删除备课备成功
            onDelSuccess:function(){

            },

            //删除备课备失败
            onDelFaild:function(){

            }
    }
})();
