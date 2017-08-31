/**
 * 资源收藏功能
 * @param resId 资源id
 */
function collect(resId,uid){
	var click =$('.resource_btnV .save');
	if(click.attr('isclick')==0){
		click.attr('isclick',1);
	}else{
		return ;
	}
        jQuery.ajax({
            type : "post",
            url : "index.php?app="+APPNAME+"&mod=Ajax&act=collect",
            data : {"id":resId,"uid":uid},
            dateType:"json",
            async:false,
            success : function(data){
                data = jQuery.parseJSON(data);
                if(data.status == '501'){
                    jQuery("#login_popup").trigger('click');
                }else if(data.status == '504'){
                    ui.success(data.message);
                }else{
                	ui.error(data.message);
                }
            },
            error : function(msg){
                ui.error("收藏失败");
            }
        });
        click.attr('isclick',0);
};

/**
 * 资源下载功能
 * @param resId 资源id
 * @param filename 文件名称
 */
function download(resId,uid){
    	var url = "index.php?app="+APPNAME+"&mod=Ajax&act=download";
    	jQuery.ajax({
            url : url,
            type : 'post',
            async : false,
            data : {'resid' : resId,'uid':uid},
            success : function(result){
                var resultObj = jQuery.parseJSON(result);
                if(resultObj.status =="501"){
                    jQuery("#login_popup").trigger('click');
                }else if(resultObj.status){
                	location.href = resultObj.data;
                	window.location.href = resultObj.data;
                }else{
                	 ui.error(resultObj.info);
                }
            }
        });
    	
    	   
    }
