(function(win){	
	win.rrt = win.rrt || {};
	rrt.uploadfile = {
			
		/**
		 * 移除文件接口
		 * @param string unid ID的字符串
		 * @param integer index 索引数
		 * @param integer attachId 附件ID
		 * @return void
		 */
		removeFile: function (unid, index, attachId) {
			
			// 移除附件ID数据
			rrt.uploadfile.upAttachVal('del', attachId);
			$('#player_' + unid + '_' + index + ' object').length > 0 && $('#player_' + unid + '_' + index + ' object').remove();
			// 移除图像
			$('#li_'+unid+'_'+index).remove();
			// 移除附件ID项
			($('#dl_'+unid).find('dd').length === 0) && $('#attach_ids').remove();
			// 动态设置数目
			rrt.uploadfile.upNumVal(unid, 'dec');
		},
		
		
		/**
		 * 更新附件表单值
		 * @return void
		 */
		upAttachVal: function (type, attachId) {
			var attachVal = $('#attach_ids').val();
			attachVal=attachVal.replace(/,/g,'|');
			var attachArr = attachVal.split('|');
			var newArr = [];
			type === 'add' &&  attachArr.push(attachId);
			for (var i in attachArr) {
				if (attachArr[i] !== '') {
					if(type ==='del' && attachArr[i] === attachId.toString())
								continue;
					newArr.push(attachArr[i]);
				}
			}
			$('#attach_ids').val('|' + newArr.join('|') + '|');
		},

		/**
		 * 更新附件表单值--contextId
		 * @return void
		 */
		upAttachValContextId: function (type, contextId) {
			var contextVal = $('#attach_ids').attr("contextVal",contextId);
			contextVal=contextVal.replace(/,/g,'|');
			var contextArr = contextVal.split('|');
			var newContextArr = [];
			type === 'add' &&  contextArr.push(contextId);
			for (var i in contextArr) {
				if (contextArr[i] !== '') {
					if(type ==='del' && contextArr[i] === contextId.toString())
						continue;
					newContextArr.push(contextArr[i]);
				}
			}
			var contextUse = '|' + newContextArr.join('|') + '|';
			$('#attach_ids').attr("contextVal",contextUse);
		},
		
		
		/**
		 * 更新上传显示数目
		 * @param string unid 唯一ID
		 * @param string type 更新类型，inc增加；dec减少
		 * @return void
		 */
		upNumVal: function (unid, type) {
			var $uploadNum = $('#upload_num_'+unid),
				$totalNum = $('#total_num_'+unid);
			switch (type) {
				case 'inc':
					// 动态设置数目 - 增加
					$uploadNum.html(parseInt($uploadNum.html()) + 1);
					$totalNum.html(parseInt($totalNum.html()) - 1);
					break;
				case 'dec':
					// 动态设置数目 - 减少
					$uploadNum.html(parseInt($uploadNum.html()) - 1);
					$totalNum.html(parseInt($totalNum.html()) + 1);
					break;
			}
		},
		/**
		 * 添加loading效果
		 * @param string unid 唯一ID
		 * @return void
		 */
		addLoading: function (unid) {
			var loadingHtml = '<dd id="loading_'+unid+'" class="load"><a><img src="'+THEME_URL+'/image/loading1.gif" style="margin-top:20px;"/></a></dd>';
			$('#btn_'+unid).after(loadingHtml);
		},
		/**
		 * 移除loading效果
		 * @param string unid 唯一ID
		 * @return void
		 */
		removeLoading: function (unid) {
			$('#loading_'+unid).remove();
		}
	}

})(window)