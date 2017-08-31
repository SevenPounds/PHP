/**
 * @fileOverview jquery工具
 * @description 包含简单的ajax封装，select
 * @name jAjax
 * @extends jQuery1.9.1
 * @author <a href="mailto:kaizhang5@iflytek.com">kaizhang</a>
 * @date 2014-5-22
 */
(function(jQuery) {
	/**
 	 * See (<a href="http:http://jQuery.com/">http:http://jQuery.com/</a>).
 	 * @name jQuery
 	 * @class jQuery Library (http://jQuery.com/)
 	 */
 
 	/**
 	 * See (<a href="http://www.css88.com/jqapi-1.9/">http://www.css88.com/jqapi-1.9/</a>).
 	 * @name fn
 	 * @class jQuery Library (http://jQuery.com/)
 	 * @memberOf jQuery
 	 */
	
	/**
	 * @description ajax方式提交表单，可附加提交数据，支持回调
	 * @memberOf jQuery.fn
	 * @param {String|Object} [args] 提交参数
	 * @param {Function} [callBack] 回调函数，入参为ajax返回文本
	 * @example
	 * 1.$("#formId").ajaxSubmit(args, function(transport) {
	 * 	alert(transport);
	 * })
	 * 2.$("#formId").ajaxSubmit(function(transport) {
	 *  alert(transport); 
	 * })
	 */
	jQuery.fn.ajaxSubmit = function() {
		lock();
		var $this = $(this), data, callFunction;
		//为了调用方便和易读性，使用变长参数
		if(arguments.length == 2){
			data = arguments[0];
			if(!!data && jQuery.type(data) == "object") {//如果存在并且是对象，那么转换成查询字符串
				data = jQuery.param(data);
			}
			var formData = $this.serialize();
			data = !!data ? data + '&' + formData : formData;
			callFunction = arguments[1];
		}else {
			data = $this.serialize();
			callFunction = arguments[0];
		}
		this.each(function() {
					jQuery.ajax({
								url : $this.attr("action"),
								data : data,
								dataType : "text",
								processData : false,
								type : $this.attr("method") || "post",
								cache : false,
								success : function(responseText, textStatus, XMLHttpRequest) {
									unlock();
									try {
										callFunction(responseText);// 返回json或者其他格式文本，作为入参调用回调函数
									} catch (e) {
										alert(e.name + ':' + e.message);
									}
								},
								error : function(XMLHttpRequest, textStatus,
										errorThrown) {
									unlock();
									handleError(XMLHttpRequest, textStatus, errorThrown);
								}

			});

		});
	};

	/**
	 * @description ajax请求URL
	 * @memberOf jQuery
	 * @param {String} url 请求路径
	 * @param {String|Object} [data] 提交参数，支持查询字符串或json对象
	 * @param {Function} [callFunction] 回调函数 ajax返回文本为该回调入参
	 * @example
	 * jQuery.simpleAjaxSubmit(url, data, function(transport){
	 * alert(transport);
	 * })
	 */
	jQuery.simpleAjaxSubmit = function(url, data, callFunction) {
		lock();
		if(!!data && jQuery.type(data) == "object") {//如果参数是json对象，转为查询字符串
			data = jQuery.param(data);
		}
 		jQuery.ajax({
					url : url,
					data : data,
					dataType : "text",
					processData : false,
					type : 'post',
					cache : false,
					success : function(responseText, textStatus, XMLHttpRequest) {
						unlock();
						try {
							callFunction(responseText);// 返回json或者其他格式文本，作为入参调用回调函数
						} catch (e) {
							alert(e.name + ':' + e.message);
						}
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
						unlock();
						handleError(XMLHttpRequest, textStatus, errorThrown);
					}

		});
	};

	/**
	 * @description ajax提交表单并加载页面
	 * @memberOf jQuery.fn
	 * @param {String} showElementId 待加载页面的节点ID
	 * @param {String|Object} [data] 提交参数
	 * @param {Function} [callFunction] 回调方法
	 * @param {Array} [callParam] 回调方法参数
	 * @example
	 * $("formId").loadPage(divId, data, callFunction, callParam)
	 */
	jQuery.fn.loadPage = function(showElementId, data, callFunction, callParam) {
		lock();
		var $this = $(this);
		if(!!data && jQuery.type(data) == "object") {//如果参数是json对象，转为查询字符串
			data = jQuery.param(data);
		}
		
		this.each(function() {
			jQuery.ajax({
						url : $this.attr("action"),
						data : !!data ? $this.serialize() + '&' + data : $this.serialize(),
						dataType : "text",
						cache : false,
						processData : false,
						type : $this.attr("method") || "post",
						success : function(responseText, textStatus, XMLHttpRequest) {
							unlock();
							$("#" + showElementId).html(responseText);
							try {
								if(jQuery.type(callFunction) == "function") {
									if (callParam != undefined && callParam != null) {
										callFunction(callParam);
									} else {
										callFunction();
									}
								}
							} catch (e) {
								alert(e.name + ':' + e.message);
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							unlock();
							handleError(XMLHttpRequest, textStatus, errorThrown);
						}
			});
		});
	};

	/**
	 * @description ajax请求URL加载页面
	 * @memberOf jQuery.fn
	 * @param {String} pageUrl 请求URL
	 * @param {String|Object} [params] 请求参数
	 * @param {Function} [callFunction] 回调函数
	 * @param {Array} [paramArray] 回调函数参数
	 * @example
	 * $("#divId").simpleLoadPage(pageUrl, params, callFunction, paramArray);
	 */
	jQuery.fn.simpleLoadPage = function(pageUrl, data, callFunction, callParam) {
		lock();
		var $this = $(this);
		
		if(!!data && jQuery.type(data) == 'object') {
			data = jQuery.param(data);
		}
		this.each(function() {
					jQuery.ajax({
								url : pageUrl,
								data : data,
								processData : false,
								cache : false,
								type : "post",
								success : function(responseText, textStatus, XMLHttpRequest) {
									unlock();
									$this.html(responseText);
									try {
										if (jQuery.type(callFunction) == "function") {
											if (callParam != undefined && callParam != null) {
												callFunction(callParam);
											} else {
												callFunction();
											}
										}
									} catch (e) {
										alert(e.name + ':' + e.message);
									}
								},
								error : function(XMLHttpRequest, textStatus, errorThrown) {
									unlock();
									handleError(XMLHttpRequest, textStatus, errorThrown);
								}

			});

		});
	};
	
	/**
	 * @name handleError
	 * @function 异常处理方法
	 * @param {Object} XMLHttpRequest XHR对象
	 * @param {Number} textStatus http状态码
	 * @param {Error} errorThrown 异常对象
	 * @private
	 */
	function handleError(XMLHttpRequest, textStatus, errorThrown) {
		var status = XMLHttpRequest.status;
		var result = jQuery.parseJSON(XMLHttpRequest.responseText);
		switch(status) {
			case 409 : 
				alert(result.msg);break;
			case 412 : 
				alert(result.msg);break;
			case 501 : 
				alert(result.msg);break;
			case 503 : 
				alert(result.msg);break;
			case 507 : 
				alert(result.msg);break;
			default : 
				alert("系统异常，请联系管理员！");
		}
	}
})($);

/**
 * 对select封装
 */
(function(jQuery) {
	
	/**
	 * @memberOf jQuery.fn
	 * @description 动态生成下拉框
	 * @param {String|Array|Object} obj 下拉框内容，支持四种格式，详见demo
	 * @param {Boolean} [bool] 是否添加“请选择”项，不写则默认不添加
	 * @example
	 * 1.json对象 {"111":"哈哈","222":"呵呵","333":"哦哦"}
	 * 2.json格式字符串 "{'111':'哈哈','222':'呵呵','333':'哦哦'}"
	 * 3.json对象数组 [{"value":"111", "text":"哈哈", "title":"我是哈哈"},{"value":"888", "text":"嘎嘎", "title":"我是嘎嘎"}]
	 * 4.json格式字符串数组 ['{"value":"111", "text":"哈哈", "title":"我是哈哈"}','{"value":"888", "text":"嘎嘎", "title":"我是嘎嘎"}']
	 * 
	 * ID为selectId的下拉框<select id="selectId"></select>
	 * 生成不带“请选择”的下拉框$("#selectId").initOption(json);
	 * 生成带“请选择”的下拉框$("#selectId").initOption(json, true);
	 * 
	 */
	jQuery.fn.initOption = function(obj, bool) {
		var $this = $(this);
		bool = !!bool;
		$this.empty();// 清空
		if (bool) {// 添加请选择
			$this.append("<option value='' title='请选择'>请选择</option>");
		}
		obj = jQuery.type(obj) == "string" ? jQuery.parseJSON(obj) : obj;// 字符串转json对象

		if (jQuery.type(obj) == "object") {
			jQuery.each(obj, function(key, value) {
				$this.append("<option value='" + key + "' title='" + value
						+ "'>" + value + "</option>");
			});
		} else if (jQuery.type(obj) == "array") {
			jQuery.each(obj, function(index, value) {
				value = jQuery.type(value) == "string" ? jQuery.parseJSON(value) : value;// 字符串转json对象
				if (!value.title) {
					value.title = value.text;
				}
				$this.append("<option value='" + value.value + "' title='"
						+ value.title + "'>" + value.text + "</option>");
			});
		} else {
			throw new TypeError();// 未知类型异常
		}
	};

	/**
	 * @memberOf jQuery.fn
	 * @description 根据已被赋值的select控件，让其不能选择其他的option 可代替disable(),且可往后台传值
	 * @example
	 * 
	 * ID为selectId的下拉框<select id="selectId"><option>固定在当前值</option></select>
	 * $("#selectId").selectOneOption();
	 */
	jQuery.fn.selectOneOption = function() {
		for (var i = 0; i < this.options.length; i++) {
			if (this.options[i].selected) {
				var option = this.options[i];
				this.options.length = 0;
				this.options[0] = option;
				return;
			}
		}
	};

	/**
	 * @memberOf jQuery.fn
	 * @description 按照value选中select中option
	 * @param {String} value option的value值
	 * @example
	 * ID为selectId的下拉框<select id="selectId"><option value="003">选中当前值，value=003</option></select>
	 * $("#selectId").selectedByValue("003");
	 */
	jQuery.fn.selectedByValue = function(value) {
		this.find("option[value=" + value + "]").attr("selected", true);
		//如果IE6兼容性有问题，试试this.val(value)
	};

	/**
	 * @memberOf jQuery.fn
	 * @description 按照text选中select中option
	 * @param {String} text option的text值
	 * @example
	 * ID为selectId的下拉框<select id="selectId"><option value="003">中国</option></select>
	 * $("#selectId").selectedByValue("中国");
	 */
	jQuery.fn.selectedByText = function(text) {
		this.find("option[text=" + text + "]").attr("selected", true);
	};

	/**
	 * @memberOf jQuery.fn
	 * @description 对选择器匹配到的节点文本进行翻译
	 * @param {Object} text option的text值
	 * @example
	 * 要翻译的节点<span clsss="country">003</span><span class="country">004</span>
	 * 字典：var countryName = {"003" : "中国", "004" : "马来西亚"}
	 * $("span .country").showNameByJson(countryName);
	 */
	jQuery.fn.showNameByJson = function(obj) {
		this.each(function() {
			var $this = $(this);
			var key = $this.text();
			if (key) {
				$this.text(obj[key]);
			}
		});
	};
	
	/*
	 * 补全jquery1.9删除的浏览器判断方法，以支持旧的jquery插件
	 */
	/*jQuery.browser = {
		mozilla : /firefox/.test(navigator.userAgent.toLowerCase()),
		webkit : /webkit/.test(navigator.userAgent.toLowerCase()),
		opera : /opera/.test(navigator.userAgent.toLowerCase()),
		msie : /msie/.test(navigator.userAgent.toLowerCase())
	};*/
})($);

/**
 * 对artDialog的继续封装
 */
(function(jQuery) {
	
	/**
 	 * See (<a href="http://code.google.com/p/artdialog/">http://code.google.com/p/artdialog/</a>).
 	 * @name artDialog
 	 * @class artDialog Library (http://code.google.com/p/artdialog/)
 	 */
	if(!window.artDialog){
		return;
	}
	
	/**
	 * @description 跨框架数据共享接口
	 * @memberOf jQuery
	 * @see http://www.planeart.cn/?p=1554
	 * @param {String} [key] 存储的数据名
	 * @param {Object} [value] 将要存储的任意数据(无此项则返回被查询的数据)，它可以是任意的Javascript数据类型，包括Array 或者 Object
	 * @return {Object} 如果仅有key，返回对应的value；如果key和value都不传，返回全部的key-value键值对
	 * @example
	 * $.topData("foo", 52);
	 * $.topData("bar", { myType: "test", count: 40 });
	 * 
	 * $.topData("foo"); // 52
	 * $.topData(); // { foo: 52, bar: { myType: "test", count: 40 }, baz: [ 1, 2, 3 ] }
	 */
	jQuery.topData = function(key, value) {
		if (value) {
			artDialog.data(key, value);
		}else {
			return artDialog.data(key, value);
		}
	};
	
	/**
	 * @description 数据共享删除接口
	 * @memberOf jQuery
	 * @param {String} key 删除的数据名
	 * @example
	 * $.removeTopData("foo")
	 */
	jQuery.removeTopData = function(key) {
		artDialog.removeData(key);
	};
	
	/**
	 * @description 警告
	 * @memberOf jQuery
	 * @param {String} content 消息内容
	 * @param {Function} [callback] 确定按钮回调函数
	 * @example
	 * $.alert("这是一个提醒");
	 * $.alert("点击有惊喜", function() {//do something...	});
	 */
	jQuery.alert = function(content, callback) {
		artDialog.alert(content, callback);
	};
	
	/**
	 * @description 确认框
	 * @requires artDialog
	 * @memberOf jQuery
	 * @param {String} content 消息内容
	 * @param {Function} yes 确定按钮回调函数
	 * @param {Function} no 取消按钮回调函数
	 * @example
	 * jQuery.confirm("确定删除？", function(){//do something}, function() {//do something});
	 */
	jQuery.confirm = function(content, yes, no) {
		artDialog.confirm(content, yes, no);
	};
	
	/**
	 * @description 提问框
	 * @requires artDialog
	 * @memberOf jQuery
	 * @param {String} title 提问标题
	 * @param {String} content 提问内容
	 * @param {Function} yes 回调函数. 接收参数：输入值
	 * @param {String} [value] 回答框里的默认值
	 * @example
	 * jQuery.prompt("请您思考", "您现在在哪里上班?", function() {//do something}, "科大讯飞");
	 */
	jQuery.prompt = function (title, content, yes, value) {
		artDialog.prompt(title, content, yes, value);
	};
	
	/**
	 * @description 短暂提示
	 * @requires artDialog
	 * @memberOf jQuery
	 * @param {String} content 提示内容
	 * @param {Number} [time] 显示时间 (默认1.5秒)
	 * @example
	 * jQuery.tips("我就是我，是颜色不一样的烟火");
	 * jQuery.tips("我就是我，是颜色不一样的烟火", 50000);
	 */
	jQuery.tips = function (content, time) {
		artDialog.tips(content, time);
	};
	
	/**
	 * @description 空白DIV弹出框
	 * @requires artDialog
	 * @memberOf jQuery
	 * @param {String} id 弹出框唯一标识
	 * @param {String} content 弹出内容
	 * @example
	 * jQuery.simpleDiv("loadDiv", "hello world");
	 * jQuery.simpleDiv("simpleloadDiv", "<form>hello world</form>");
	 */
	jQuery.simpleDiv = function (content, id) {
		artDialog.simpleDiv(id, content);
	};
	
	/**
	 * @description 关闭DIV弹出框
	 * @requires artDialog
	 * @memberOf jQuery
	 * @param {String} id 弹出框唯一标识
	 * @example
	 * 关闭标识为simpleloadDiv的DIV弹出层
	 * $.closeDiv("simpleloadDiv");
	 */
	jQuery.closeDiv= function (id) {
		artDialog.closed(id || "Simple");
	};
	
	/**
	 * @description 弹出页面隐藏的内容
	 * @requires artDialog
	 * @memberOf jQuery
	 * @param {String} url ajax请求路径
	 * @param {String} [args] ajax提交参数，支持json对象或者查询字符串
	 * @param {Object} options artdialog参数,See<a href="http://www.planeart.cn/demo/artDialog/_doc/API.html">http://www.planeart.cn/demo/artDialog/_doc/API.html</a>
	 * @param {Boolean} cache 是否缓存
	 * @example
	 * 
	 */
	jQuery.showDiv = function (title, content, options) {
		artDialog.showDiv(title, content, options);
	};
	
	jQuery.hideDiv= function (id) {
		artDialog.closed(id || "Hide");
	};
	
	/**
	 * 弹出ajax加载
	 */
	jQuery.loadAjax = function(title, url, args, options, cache) {
		artDialog.load(title, url, args, options, cache);
	};	
	
	jQuery.closeAjax = function(id){
		artDialog.closed(id || "Ajax");
	};
	
	/**
	 * @description 开启iframe弹出层
	 * @requires artDialog
	 * @memberOf jQuery
	 * @param {String} url iframe地址
	 * @param {Object} options artdialog参数,See<a href="http://www.planeart.cn/demo/artDialog/_doc/API.html">http://www.planeart.cn/demo/artDialog/_doc/API.html</a>
	 * @param {Boolean} cache 是否缓存
	 * @example
	 * $.loadIframe("demo/demo_demoIframe.action");
	 */
	jQuery.loadIframe = function(title, url, options, cache) {
		artDialog.open(title, url, options, cache);
	};
	
	jQuery.closeIframe = function(id){
		artDialog.closed(id || "Iframe");
	};
	
	jQuery.refreshIframe = function(url){
		artDialog.refreshIframe(url);
	};
	
	/**
	 * @description 右下角滑动通知，默认显示5秒
	 * @requires artDialog
	 * @memberOf jQuery
	 * @param	{String} title 标题
	 * @param	{String} content 内容
	 * @param	{Number} 停留时间(秒)，默认五秒
	 * @example
	 * $.notice('万象网管', '尊敬的顾客朋友，您IQ卡余额不足10元，请及时充值');
	 * $.notice('万象网管', '尊敬的顾客朋友，您IQ卡余额不足10元，请及时充值', 10);
	 */
	jQuery.notice = function(title, content, time) {
		artDialog.notice({
		    title: title,
		    width: 220,// 必须指定一个像素宽度值或者百分比，否则浏览器窗口改变可能导致artDialog收缩
		    content: content,
		    icon: 'face-smile',
		    time: time||5
		});
	};
})(jQuery);


/**
 * @description 遮罩层的继续封装
 * @private
 */
var _ML;
$(document).ready(function(){
	if(window.maskLayer){
		_ML = maskLayer(false);
	}
});
$(window).load(function(){
	if(_ML){
		_ML = _ML.unlock();
	}
});
/**
 * @description 锁屏
 * @param {} config 
 * @example
 * lock();
 */
function lock(config){
	if(_ML){
		_ML = _ML.lock(config);
	}
}
/**
 * @description 解锁
 * @param {} times
 * @example
 * unlock();
 */
function unlock(times){
	if(_ML){
		_ML = _ML.unlock(times);
	}
}