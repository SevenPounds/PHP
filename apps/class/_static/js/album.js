function create_classalbum_tab (cid, isRefresh) {
	isRefresh = (typeof isRefresh === 'undefined') ? 0 : isRefresh;
	ui.box.load(U('class/ClassAlbum/create_classalbum_tab') + '&cid=' + cid + '&isRefresh=' + isRefresh, '创建相册');
};
/**
 * 执行创建班级专辑操作
 * @param boolean isRefresh 是否刷新，默认为false
 * @return void
 */
function do_create_classalbum (isRefresh,cid) {
	isRefresh = (typeof isRefresh === 'undefined') ? 0 : isRefresh;
	var name = $('#name').val().replace(/\s+/g,"");
	if (!name) { 
		ui.error('名称不能为空');
		return false;
	} else if (name.length > 12) { 
		ui.error('名称不能超过12个字');
		return false;
	}
	$.post(U('class/ClassAlbum/do_create_classalbum'), {name:name,cid:cid}, function(res) {
		if (res.status == -1) {
			ui.error('该相册名已存在');
		} else if (res.status == 1) {
			if (isRefresh) {
				location.reload();
			} else {
				parent.setAlbumOption(res.data);
			}
			ui.box.close();
			ui.success('创建成功');
		} else if (res.status == 0) {
			ui.box.close();
			ui.error('创建失败');
		}
	}, 'json');
};
/**
 * 添加专辑下拉菜单
 * @param object data 专辑名称与专辑ID
 */
function setAlbumOption (data) {
	var albumId = data.albumId,
		albumName = data.albumName;
	$('#albumlist').append('<option value="' + albumId + '" selected="selected" style="background-color:yellow">' + albumName + '</option>');
};

/**
 * 删除单张图片
 * @param integer albumId 相册ID
 * @param integer photoId 图片ID
 * @return void
 */
function delphoto (albumId, photoId,uid,cid) {
	if (confirm('你确定要删除这张图片么？')) {
		$.post(U('photo/Manage/delete_photo'), {id:photoId, albumId:albumId}, function(data) {
			if (data == 1) {
				location.href = U('class/ClassAlbum/classalbum') + '&id=' + albumId + '&uid=' + uid +"&cid=" + cid;
				return false;
			} else {
				ui.error('删除失败！');
			}
		});
	}
};
/**
 * 设置封面操作
 * @param integer albumId 相册ID
 * @param integer photoId 图片ID
 * @return void
 */
function setcover (albumId, photoId) {
	if(confirm('你要将这张图片设置为封面么？')) {
		$.post(U('photo/Manage/set_cover'), {photoId:photoId,albumId:albumId}, function(data) {
			if (data == 1) {
				ui.success('封面设置成功！');
			} else if (data == -1) {
				ui.error('该图片不存在！');
			} else {
				ui.error('当前封面已是该图片，或设置失败！');
			}
		});
	}
};
/**
 * 编辑班级图片弹窗
 * @param integer albumId 相册ID
 * @param integer photoId 图片ID
 * @return void
 */
function editClassPhotoTab (albumId, photoId, classId, nextId) {
	ui.box.load(U('class/ClassAlbum/edit_photo_tab') + '&aid=' + albumId + '&pid=' + photoId + '&cid=' + classId+ '&nextid=' + nextId, '编辑图片');
};