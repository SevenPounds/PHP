/**
 * 名师工作室 成员管理页面使用
 * @param gid 工作室ID
 * @param toChange 需要更新数据的Node ID
 * @author xrding
 */
function MSMember(gid, toChange) {
	var _this = this;

	this._gid = gid;
	this._toChange = toChange;
	this._page = 1;
	this._keywords = "";
	this._memberIds = [];
	this.initMemberIds = function(memberIds) {
		_this._memberIds = memberIds;
	}
	
	/**
	 * 删除
	 */
	this.deleteOne = function(memberId) {
		var _memberIds = [];
		var _data = {};

		_memberIds[0] = memberId;

		_data.memberIds = _memberIds;
		_data.gid = _this._gid;

		_this._deleteMember(_data);
	}

	/**
	 * 删除全部
	 */
	this.ids = [];//记录多个删除的id数组
	this.deleteAll = function() {
		/*var _toDel = $("#delete_all").attr("checked");
		if(!(typeof _toDel != undefined && _toDel == "checked")){
			ui.error("请选择用户");
			return false;
		}

		var _memberIdString = $("#memberIds").val();
		var _memberIds = _memberIdString.split("|");*/
		_this.ids.length = 0;//清空数组
		jQuery("#restable td input").each(function(){
			if(this.checked){
				_this.ids.push(this.value);
			}
		});
		if(_this.ids.length <=0){
			ui.error("未选择任何成员");
			return;
		}
		var _data = {};
		_data.memberIds = _this.ids;
		_data.gid = _this._gid;
		_this._deleteMember(_data);
	}
	this.enter = function(e){
		var e = e || window.event;
		if(e.keyCode == 13){
			_this.search();
		}
	}
	this._deleteMember = function(_data) {
		ui.confirmBox("删除成员", "确认删除？", function(){
			$.ajax({
				type : "POST",
				url : U('/Member/deleteMember'),
				data : _data,
				dataType : "json",
				success : function(msg) {
					if (msg.status == 1) {
						ui.success("删除成功");
						_this._reloadData();
					} else {
						ui.error("删除失败！");	
					}
				},
				error : function(msg) {
					ui.error("删除失败");	
				}
			});
		});
	}

	/**
	 * 初始化，加载第一页
	 */
	this.init = function() {
		_this._page = 1;
		_this._reloadData();
	}

	/**
	 * 分页时使用
	 */
	this.page = function(page) {
		_this._page = page;
		_this._reloadData();
	}
	this.search = function(){
		_this._keywords = $("#keywords").val();
		_this._page = 1;
		_this._reloadData();
	}
	this._getData = function() {
		var _data = {};
		
		_data.keywords = _this._keywords;
		_data.page = _this._page;
		_data.gid = _this._gid;

		return _data;
	}

	this._reloadData = function() {
		var _data = _this._getData();
		$.ajax({
			type : "POST",
			url : U('/Member/getMemberList'),
			data : _data,
			dataType : "json",
			beforeSend : function(){
				jQuery("#" + toChange).html( '<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img style="display:inline;" src="'+THEME_URL+'/image/icon_waiting.gif" class="load"></div>');
			},
			success : function(msg) {
				if (msg.status == 1) {
					jQuery("#" + toChange).html(msg.data);
				} else {
					ui.error("数据加载失败");	
				}
			},
			error : function(msg) {
				ui.error("更新失败");	
			}
		});
	}
	this.showAdd = function(){
		ui.box.load(U('/Member/showAdd', ['gid=' + _this._gid]), "添加成员");
	}
	this.addMember = function(){
		var _userIds = $("#user_ids").val();
		if(_userIds === "||"){
			ui.error("成员不能为空！");
			return false;
		}
		var _data = {};
		_data.userIds = _userIds;
		_data.gid = _this._gid;
		$.ajax({
			type : "POST",
			url : U('/Member/addMember'),
			data : _data,
			dataType : "json",
			success : function(msg) {
				if (msg.status == 1) {
					ui.success("添加成功！");
					ui.box.close();
					_this._page = 1;
					_this._reloadData();
				} else {
					ui.error("成员添加失败");	
				}
			},
			error : function(msg) {
				ui.error("更新失败");	
			}
		});
	}
}
