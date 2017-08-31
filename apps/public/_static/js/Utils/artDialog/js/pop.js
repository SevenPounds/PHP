var pop = pop||{};
(function(pop, dialog) {
	/**
	 * 警告框
	 */
	pop.alert = function(content, callBack) {
		if(callBack == undefined) callBack = new Function();
		var d = dialog({
			id : Math.random(),
		    title: '提示',
		    content: content,
		    cancel: false,
		    modal : true,
		    okValue: '确定',
		    ok: callBack
		});
		d.show();
	};
	
	pop.confirm = function(content, yes, no) {
		if(no == undefined) no = new Function();
		var d = dialog({
		    title: '提示',
		    content: content,
		    modal : true,
		    okValue: '确定',
		    ok: function () {
		        yes();
		    },
		    cancelValue: '取消',
		    cancel: no
		});
		d.show();
	};
	
	pop.tips = function(content) {
		var d = dialog({
		    content: content,
		    modal : true
		});
		d.show();
		setTimeout(function () {
		    d.close().remove();
		}, 2000);

	};
	
	pop.lock = function() {
		var d = dialog({
		    modal : true
		});
		d.show();
		setTimeout(function () {
		    d.close().remove();
		}, 2000);

	};
})(pop, top.dialog);