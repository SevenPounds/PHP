var bkOperater =(function(){
    var _this = {};
    _this.collectBagSuccess = function(){
        var param=appBase.getQueryString();
        appBase.grid.init(param);
    };
    _this.delResSuccess = function(){};
    return _this;
})();
// 备课本到单元资源列表收藏资源包
function collectBag(fid,bagid){
	$.ajax({
		url : 'index.php?app=yunpan&mod=Ajax&act=collectBag',
		type : 'post',
		data : {fid : fid, id : bagid},
		success : function(result){
			result = eval('(' + result + ')');
			if(result.statuscode){
				ui.success(result.data);
                bkOperater.collectBagSuccess();
			}else{
				ui.error(result.data);
			}
		}
	});
};

// 根据资源包id和分页参数获取当前页数据
function getBagResByPage(bagId, obj){
	var page = $(obj).attr('page');
	if(!$(obj).hasClass('disabled')){
		if(page > 0){
			$.ajax({
				url : 'index.php?app=yunpan&mod=Ajax&act=getBagResByPage',
				type : 'post',
				data : {bagId : bagId, page : page},
				success : function(result){
					result = eval('(' + result + ')');
					var list = '';
					var bagRes = result.bagRes;
					for(var i = 0; i < bagRes.length; i++){
						list += '\
							<li style="cursor:pointer">\
								<img style="width:16px;height:16px;" alt="' + bagRes[i].general.title + '" src="' + getShortImg(bagRes[i].general.extension) + '">\
								<a href="' + result.viewUrl + bagRes[i].general.id + '" target="_blank">\
									' + bagRes[i].general.title + '\
								</a>\
							</li>';
					}
					$("#bag_res_list").html(list);
					
					if(result.showNext){
						$("#bag_next_page").removeClass('disabled');
						$("#bag_next_page").attr('page',parseInt(page) + 1);
					}else{
						$("#bag_next_page").addClass('disabled');
					}
					
					if(result.showPre){
						$("#bag_pre_page").removeClass('disabled');
						$("#bag_pre_page").attr('page',parseInt(page) - 1);
					}else{
						$("#bag_pre_page").addClass('disabled');
					}
				},
				error : function(msg){
					
				}
			});
		}
	}
};