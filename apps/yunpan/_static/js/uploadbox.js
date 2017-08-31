
/**
 * 上传资源弹出层
 */
var uploadBox =(function(){
        var _this = {};
        //文件上传
        $('.yunfile_upload_btn').live('click',function(){
            var folder = $(this).attr('data-folder');
          //  _this.show(folder);
        });

        _this.show = function(folder){
            ui.box.load(U('yunpan/Ajax/showUpload')+'&folder='+folder,'上传资源');
        }

        _this.close = function(){
            jQuery("#file_upload").uploadify('cancel','*');
            var swf_id = "SWFUpload_" + (SWFUpload.movieCount - 1);
            swfobject.removeSWF(swf_id);
            ui.box.close();
        }

        _this.callback = function(res){

        }

        _this.onUploadSuccess = function(res,fn){
        	if(files == '' || files == null || files == undefined){
        		alert("请先上传文件后操作!");
        		return ;
        	}
            var result = false;
            for(var i = 0; i < files.length; i++){
                if(files[i]['isadd'] != false){
                    result = true;
                }
            }
            if(result == true){
                alert("资源已上传至网盘！");
            }

            var back ='';
            if("undefined" != typeof(fn)){
                back = fn;
            }else if("undefined" != typeof(_this.callback)){
                back = this.callback;
            }
            if("function" == typeof(back)){
                back(res);
            }else{
                eval(back);
            }
            _this.close ();
        }

        return _this;
})();


