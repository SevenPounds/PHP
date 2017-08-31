/**
 * 名师工作室 通知公告页面使用
 * @param gid 工作室ID
 * @param type 公告类型：1：通知公告，2：工作动态，3：研究成果，4：教学论文，5：教学日志
 * @param toChange 需要更新数据的Node ID
 * @author yxxing
 */
function MSAnnounce(gid,type,toChange){
	
	var _this = this;
	this._gid = gid;
	this._type = type;
	this._toChange = toChange;
	this._page = 1;
	this._announceIds = [];
	this._orderBy = "ctime desc";
	this._adding = false;
	//提交保存和修改时进行检查
	this._check = function(){
		E.sync();
		var _announceTitle = $("#announce_title").val();
		var _announceContent = $("#announce_content").val();
		if($.trim(_announceTitle) == ""){
			ui.error("标题不能为空");
			return false;
		}
		if($.trim(_announceTitle).length>50){
			ui.error("标题不能超过50个字符");
			return false;
		}
		if($.trim(_announceContent) == ""){
			ui.error("内容不能为空");
			return false;
		}
		var _data = {};
		_data.gid = _this._gid;
		_data.announceTitle = _announceTitle;
		_data.announceContent = _announceContent;
		_data.announceType = _this._type;
		return _data;
	}
    this.cancel = function(){
        if(_this._type == 1){
            location.href = U('/Announce/index',['gid=' + _this._gid]);
        }else if(_this._type == 2){
            location.href = U('/Article/dynamic',['gid=' + _this._gid]);
        }else if(_this._type == 3){
            location.href = U('/Article/research',['gid=' + _this._gid]);
        }else if(_this._type == 4){
            location.href = U('/Article/thesis',['gid=' + _this._gid]);
        }else if(_this._type == 5){
            location.href = U('/Article/blog',['gid=' + _this._gid]);
        }
    }
	/**
	 * 保存
	 */
	this.doAdd = function(){
		var _data = _this._check();
		if(!_data){
			return false;
		}
		if(_this._adding){
			return fasle;
		}
		_data.attachments = $("#attach_ids").val();
		$.ajax({
			type: "POST",
			url: U('/Ajax/doAdd'),
			data: _data,
			dataType: "json",
			beforeSend:function(){
				_this._adding = true;
			},
			success:function(msg){
				if(msg.status == 1){
					ui.success("发布成功！");
                    setTimeout(function(){
                        if(_this._type == 1){
                            location.href = U('/Announce/index',['gid=' + _this._gid]);
                        }else if(_this._type == 2){
                            location.href = U('/Article/dynamic',['gid=' + _this._gid]);
                        }else if(_this._type == 3){
                            location.href = U('/Article/research',['gid=' + _this._gid]);
                        }else if(_this._type == 4){
                            location.href = U('/Article/thesis',['gid=' + _this._gid]);
                        }else if(_this._type == 5){
                            location.href = U('/Article/blog',['gid=' + _this._gid]);
                        }
                    },2000);

				} else{
					ui.error("发布失败！");
				}
			},
			error:function(msg){
				ui.error("发布失败！");
			},
			complete:function(){
				_this._adding = false;
			}
		});
	}
	/**
	 * 保存
	 */
	this.doEdit = function(){
		var _data = _this._check();
		_data.announceId = $("#announce_id").val();
		if(!_data){
			return false;
		}
		if(_this._adding){
			return fasle;
		}
		_data.attachments = $("#attach_ids").val();
		$.ajax({
			type: "POST",
			url: U('/Ajax/doEdit'),
			data: _data,
			dataType: "json",
			beforeSend:function(){
				_this._adding = true;
			},
			success:function(msg){
				if(msg.status == 1){
					ui.success("修改成功！");
                    setTimeout(function(){
                        if(_this._type == 1){
                            location.href = U('/Announce/index',['gid=' + _this._gid]);
                        }else if(_this._type == 2){
                            location.href = U('/Article/dynamic',['gid=' + _this._gid]);
                        }else if(_this._type == 3){
                            location.href = U('/Article/research',['gid=' + _this._gid]);
                        }else if(_this._type == 4){
                            location.href = U('/Article/thesis',['gid=' + _this._gid]);
                        }else if(_this._type == 5){
                            location.href = U('/Article/blog',['gid=' + _this._gid]);
                        }
                    },2000);
				} else{
					ui.error("修改失败！");
				}
			},
			error:function(msg){
				ui.error("修改失败！");
			},
			complete:function(){
				_this._adding = false;
			}
		});
	}
	/**
	 * 删除
	 */
	this.deleteAnnounce = function(announceId){
		var _announceIds = [];
		var _data = {};
		_announceIds[0] = announceId;
		_data.announceIds = _announceIds;
		_this._deleteAnnounce(_data);
	}
	/**
	 * 删除全部
	 */
	this.ids = [];//记录多个删除的id数组
	this.deleteAll = function(){
		/*var _toDel = $("#delete_all").attr("checked");
		if(!(typeof _toDel != undefined && _toDel == "checked")){
			ui.error("请您选择文章！");
			return false;
		}
		var _announceIdString = $("#announceIds").val();
		var _announceIds = _announceIdString.split("|");*/
		_this.ids.length = 0;//清空数组
		jQuery("#restable td input").each(function(){
			if(this.checked){
				_this.ids.push(this.value);
			}
		});
		if(_this.ids.length <= 0){
			var tip = '';
			switch(_this._type){
				case 1:
					tip = '公告';
					break;
				case 2:
					tip = '动态';
					break;
				case 3:
					tip = '成果';
					break;
				case 4:
					tip = '论文';
					break;
				case 5:
					tip = '日志';
					break;
			}
			ui.error("未选择任何"+tip);
			return;
		}
		var _data = {};
		_data.announceIds = _this.ids;
		_this._deleteAnnounce(_data);
	}
	this._deleteAnnounce = function(_data){
		ui.confirmBox("删除文章", "确认删除？", function(){
			$.ajax({
				type: "POST",
				url: U('/Ajax/deleteAnnounce'),
				data: _data,
				dataType: "json",
				success:function(msg){
					if(msg.status == 1){
						ui.success("删除成功！");
						_this._reloadData();
					} else{
						ui.error("删除失败！");	
					}
				},
				error:function(msg){
					ui.error("删除失败！");	
				}
			})
		});

	}
	/**
	 * 初始化，加载第一页
	 */
	this.init = function(){
		_this._page = 1;
		_this._reloadData();
	}
	/**
	 * 分页时使用
	 */
	this.page = function(page){
		_this._page = page;
		_this._reloadData();
	}
	/**
	 * 改变排序方式时使用
	 */
	this.changeOrder = function(order){
		_this._page = 1;
		switch(order){
			case "ctime":
				if(_this._orderBy == "ctime desc"){
					_this._orderBy = "ctime asc";
				}else{
					_this._orderBy = "ctime desc";
				}
				break;
			case "view_count":
				if(_this._orderBy == "viewcount desc"){
					_this._orderBy = "viewcount asc";
				}else{
					_this._orderBy = "viewcount desc";
				}
				break;
		}
		_this._reloadData();
	}
	this._getData = function(){
		var _data = {};
		_data.page = _this._page;
		_data.type = _this._type;
		_data.gid = _this._gid;
		_data.orderBy = _this._orderBy;
		return _data;
	}
	this._reloadData = function(){
		var _data = _this._getData();
		$.ajax({
			type: "POST",
			url: U('/Ajax/getAnnouncesList'),
			data: _data,
			dataType: "json",
			beforeSend : function(){
				jQuery("#" + toChange).html( '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img style="display:inline;" src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>');
			},
			success:function(msg){
				if(msg.status == 1){
					jQuery("#" + toChange).html(msg.data);
				} else{
					ui.error("数据加载失败！");	
				}
			},
			error:function(msg){
				ui.error("更新失败！");	
			}
		});
	}
}