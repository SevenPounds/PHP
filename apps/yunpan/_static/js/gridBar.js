/**
 * Created by xypan on 14-4-14.
 */
 (function(ab,$){
        var _this = {};

        _this.config = ['all','audio','image','video','document'];


        // 关键字查询
        _this.search = function(){
            var keyword = jQuery.trim(jQuery("#yunfile_search_input").val());

            if(keyword != ""){
                keyword = encodeURIComponent(keyword);
                $.address.autoUpdate(false);
                ab.setQueryString('p','');
                ab.setQueryString('type','');
                ab.setQueryString('fid','');
                $.address.autoUpdate(true);
                ab.setQueryString("keyword",keyword);
            }
        }


        // 初始化
        _this.init = function(params){
        	
        	//影藏我的公开的bar
    		$("#nav_public").hide();
    		$("#nav_default").show()
        	
            $(".sort_btn").removeClass('current');
            var type = params.type;

            var types = _this.config;
            var flag = true;
            for(var i = 0;i < types.length; i++){
                if(types[i] == type){
                    jQuery(".sort_btn").eq(i).addClass('current');
                    jQuery(".it_btn").removeClass('it_btn_cur');
                    jQuery(".it_btn").eq(i).addClass('it_btn_cur');
                    flag = false;
                }
            }
            if(flag){
                $(".sort_btn").eq(0).addClass('current');
                jQuery(".it_btn").removeClass('it_btn_cur');
                jQuery(".it_btn").eq(0).addClass('it_btn_cur');
            }
        }


        ab.gridBar = _this;
})(appBase,jQuery);