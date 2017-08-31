var photo= photo || {};//名师工作室图片

photo.setting = {};
photo.formData = {};

/**
 * 初始化配置相关数据
 * $param object option 
 * @return void
 */
photo.init = function(option) {
	this.setting.gid = option.gid || 0;//设置工作室id 	
//	this.setting.fileSizeLimit = option.fileSizeLimit || '2048';//默认2M 		
//	this.setting.fileTypeExts = option.fileTypeExts || '*.jpeg;*.gif;*.png;*.jpg';//默认上传图片格式	
//	this.setting.uploader = option.upload_url || U('msgroup/Photo/uploadReputationPhoto');	
};


photo.delphoto = function(gid,photoId){
	var	page = jQuery("#photoPage").val();
	if(gid==0 && photoId==0){
		return ;
	}
	ui.confirmBox('删除荣誉','确认删除该荣誉吗？',function(){
		jQuery.ajax({
			type: "POST",
			url:U('msgroup/PhotoAjax/delPhoto'),
			dataType:'json',
			data:{gid:gid,photoId:photoId},
			success:function(res){
				if(res.status == 1){
					//alert(res.info);
					//window.location.reload();
					photo.request(page,gid);
				}
			},
			error:function(){
				
			}
		});
	});

}


photo.reload  = function(gid,page){
	jQuery.ajax({
		type: "POST",
		url:U('msgroup/PhotoAjax/getPhotos'),
		dataType:'json',
		data:{gid:gid,p:page},
		success:function(res){
			if(res.status == 1){
				jQuery('.teaWS_rgt .teaWSR_con').html(res.data);
			}
		},
		error:function(){
			
		}
	});
}

photo.request =function(page,gid){
	photo.reload(gid,page);
}


photo.showUpload =function(gid){
	ui.box.load(U('msgroup/Photo/showUpload')+"&gid="+gid,"上传图片");
	jQuery(".ico-close").attr("href","javascript:void(0);");
}

photo.closeDialog = function(){
	jQuery("#file_upload").uploadify('cancel','*');
	var swf_id = "SWFUpload_" + (SWFUpload.movieCount - 1); 
	swfobject.removeSWF(swf_id);
	this.formData.gid = 0;
	this.formData.phototitle =  "";
	this.formData.description = "";
	ui.box.close();
}


photo.startUpload = function(){
	this.formData.gid = this.setting.gid;
	this.formData.phototitle = jQuery('#phototitle').val();
	this.formData.description = jQuery('#description').val();
	jQuery("#file_upload").uploadify("settings","formData",photo.formData);
	jQuery("#file_upload").uploadify('upload');
}



