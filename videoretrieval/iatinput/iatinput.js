/// <reference path="../SpeechRecorder/swfobject.js" />
/// <reference path="../SpeechRecorder/Settings.js" />
/// <reference path="../SpeechRecorder/SpeechRecorder.js" />

/**
 * 文件名: iatinput.js
 * 描述: 听写input渲染js文件
 *
 * 功能说明：
 *
 * 版本: 1.0.1.4
 * 作者: yuwang
 * 日期：2013/4/9
 *
 * 变更记录：
 * 2013/04/16 1.0.0.3 修复浏览器兼容性Bug、 增加日志及debug配置、修复input重复渲染导致的bug
 * 2013/04/18 1.0.0.4 修复一个调用流程出错的Bug
 * 2013/04/23 1.0.0.6 增加input延时render功能, 修改终止识别方法名, 优化麦克风状态处理
 * 2013/04/27 1.0.0.7 增加input按钮鼠标滑动和录音样式
 * 2013/05/03 1.0.0.8 增加正在识别面板，增加录音按钮鼠标滑动样式，人性化flash设置框，不再强制用户允许；修复Chrome浏览器下的兼容性缺陷；
 * 2013/05/06 1.0.0.9 修复遮罩层不能覆盖全屏的缺陷，调整flash安全设置框的位置，要求页面input添加样式，解决页面延迟展示录音按钮的问题；
 * 2013/07/10 1.0.1.1 修改部分接口和回调名称
 * 2013/07/18 1.0.1.1 修复了IE浏览器中，文档模式为标准时input不输出的问题；同时重新调整flash安全设置框的位置，并修复遮罩层不能覆盖全屏的缺陷
 * 2013/11/06 1.0.1.2 暴露closeIat方法；添加_recorderId配置；增加录音面板在页面上方和页面下方的不同显示
 * 2014/1/21  1.0.1.3 修复双击听写按钮问题
 * 2014/2/14  1.0.1.4 暴露handleIatCompleteEvent方法
 */

(function (win) {

    var UNDEF = "undefined",
        FUNC = "function",
        IAT_INPUT_ATTRIBUTE = "iatinput",
        MIC_WIDTH = 20,
        MIC_HEIGHT = 20;

    //记录上一个输入框Id
    var _bakInput = null;
    var _flashState = null;
    var _recorder;
    var _recorderId = "iat-panel-" + new Date().getTime();

    var language = {
        zh: {
            recTip: "请开始说话",
            speakOver: "说完了",
            recognizing: "识别中",
            retry: "重试",
            cancel: "取消",
            settingMic: "麦克风设置"
        },
        en: {
            recTip: "Please speak now",
            speakOver: "speak end",
            recognizing: "recognising",
            retry: "Retry",
            cancel: "Cancel",
            settingMic: "Setting Microphone"
        }
    };

    var _language = language.zh;
    var _debug = false;
    var _browser = {},
        _iatPanel,
        _iatInputs = [],
        _iatDefaultParam = {
            "textMode": 'replace',      // 文本模式:replace | append
            "punctuation": false,       // 是否包含标点
            "vadSpeechTail": 500        // 后端点静音时长
        };

    var _curInputInfo = {
        id: ""
    };

    var _wrapedInputs = [];
    var _pendingWrapInputs = [];

    var _logUtil = (function () {

        return {
            debug: function (msg) {
                if (_debug && window.console) {
                    window.console.log(msg);
                }
            },
            info: function (msg) {
                if (_debug && window.console) {
                    window.console.info(msg);
                }
            },
            warn: function (msg) {
                if (_debug && window.console) {
                    window.console.warn(msg);
                }
            },
            error: function (msg) {
                if (_debug && window.console) {
                    window.console.error(msg);
                }
            }
        };
    })();


    var _util = new Util();

    /**
     * 帮助类
     *
     * @class Util
     * @constructor
     */
    function Util() {

        /**
         * 克隆对象
         *
         * @method clone
         * @param {Object} obj 要克隆的对象
         * @return {Object}
         **/
        function clone(obj) {

            if (obj === undefined) {
                return undefined;
            }

            var objClone;
            if (obj.constructor === Object) {
                objClone = new obj.constructor();
            } else {
                objClone = new obj.constructor(obj.valueOf());
            }

            for (var key in obj) {
                if (objClone[key] !== obj[key]) {
                    if (typeof obj[key] === 'object') {
                        objClone[key] = clone(obj[key]);
                    } else {
                        objClone[key] = obj[key];
                    }
                }
            }
            objClone.toString = obj.toString;
            objClone.valueOf = obj.valueOf;

            return objClone;
        }

        /**
         * 获取浏览器信息
         * More details: http://api.jquery.com/jQuery.browser
         *
         * @method getBrowserInfo
         * @return 浏览器信息
         */
        function getBrowserInfo() {
            var ua = navigator.userAgent.toLowerCase();

            var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
                /(webkit)[ \/]([\w.]+)/.exec(ua) ||
                /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
                /(msie) ([\w.]+)/.exec(ua) ||
                ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
                [];

            var matched = {
                browser: match[1] || "",
                version: match[2] || "0"
            };

            var browser = {};
            if (matched.browser) {
                browser[matched.browser] = true;
                browser.version = matched.version;
            }

            // Chrome is Webkit, but Webkit is also Safari.
            if (browser.chrome) {
                browser.webkit = true;
            } else if (browser.webkit) {
                browser.safari = true;
            }

            return browser;
        }

        /**
         * 为Html元素添加事件，并修改执行上下文
         *
         * @method addEvent
         * @param {DOM} ele html元素
         * @param {String} event 事件名称
         * @param {Function} handleFun 事件处理函数
         * @param {*} data 传递给事件处理函数的参数
         */
        function addEvent(ele, event, handleFun, data) {

            // 如果有参数要传递给事件处理函数，则创建一个闭包
            var func;
            if (data !== undefined) {
                func = (function (passData) {
                    return function () {
                        handleFun.call(ele, arguments[0], passData);
                    };
                } (data));
            } else {
                func = handleFun;
            }

            // Mozilla, Opera etc.
            if (document.addEventListener) {
                ele.addEventListener(event, function () {
                    func.apply(ele, arguments);
                }, false);
            }

            // IE
            else if (document.attachEvent) {
                ele.attachEvent("on" + event, function () {
                    arguments.data = data || "";
                    func.apply(ele, arguments);
                });
            }

            // Others
            else {
                ele['on' + event] = function () {
                    func.apply(ele, arguments);
                };
            }
        }

        /**
         * 获取DOM结点是否有某个属性值
         *
         * @method hasAttribute
         * @param {DOM} elem DOM元素
         * @param {String} attr 属性名称
         */
        function hasAttribute(elem, attr) {

            var nType = elem.nodeType;
            // don't get/set attributes on text, comment and attribute nodes
            if (!elem || !nType || nType === 3 || nType === 8 || nType === 2) {
                throw new Error("element not support attribute");
            }

            if (typeof attr !== "string" || attr.replace(" ").length < 1) {
                throw new Error("invalid attr name");
            }

            if (typeof elem.hasAttribute === FUNC) {
                return elem.hasAttribute(attr);

            }

            return elem.attributes[attr] !== undefined;
        }

        /**
         * 获取DOM结点属性值
         *
         * @method getAttribute
         * @param {DOM} elem DOM元素
         * @param {String} attr 属性名称
         */
        function getAttribute(elem, attr) {

            if (hasAttribute(elem, attr)) {
                if (typeof elem.getAttribute === FUNC) {
                    return elem.getAttribute(attr);
                }

                return elem.attributes[attr] ? elem.attributes[attr].value : undefined;
            }

            return undefined;
        }

        /**
         * 设置DOM结点属性值
         *
         * @method setAttribute
         * @param {Element} elem DOM元素
         * @param {String} attr 属性名称
         * @param {String} value 属性值
         */
        function setAttribute(elem, attr, value) {

            var nType = elem.nodeType;
            // don't get/set attributes on text, comment and attribute nodes
            if (!elem || !nType || nType === 3 || nType === 8 || nType === 2) {
                throw new Error("element not support attribute");
            }

            if (typeof elem.setAttribute === FUNC) {
                elem.setAttribute(attr, value);
            } else if (elem.attributes) {
                elem.attributes[attr].value = value;
            }
        }

        /**
         * 获取DOM元素绝对位置
         *
         * @method setInnerText
         * @param {Element} elem DOM元素
         * @param {String} text 文本内容
         */
        function setInnerText(panel, text) {
            // ie、chrome、safari
            text = (text === undefined ? "网络错误" : text);
            if (panel.innerText !== undefined) {
                panel.innerText = text;
            } else { // firefox
                panel.textContent = text;
            }
        }

        /**
         * 获取DOM元素绝对位置
         *
         * @method getPosition
         * @param {Element} elem DOM元素
         * @return {{x:number, y:number}}位置信息
         */
        function getPosition(elem) {

            var x = 0, y = 0;

            do {
                x += elem.offsetLeft;
                y += elem.offsetTop;
            } while (elem = elem.offsetParent);

            return {
                'x': x,
                'y': y
            };
        }

        return {
            clone: clone,
            getBrowserInfo: getBrowserInfo,
            addEvent: addEvent,
            hasAttribute: hasAttribute,
            getAttribute: getAttribute,
            setAttribute: setAttribute,
            setInnerText: setInnerText,
            getPosition: getPosition
        };
    };

    /**
     * 听写输入框
     *
     * @class IatInput
     * @constructor
     */
    function IatInput(input, iatPanel) {

        var _this = this;
        var _input = input;
        var _id = 'iatinput-' + _this.base.generateId();
        var _iatParam = _util.clone(_iatDefaultParam);

        /**
         * 包装普通文本框
         *
         * @method wrapInput
         * @private
         */
        function wrapInput() {

            _wrapedInputs.push(input);

            // 获取iatinput参数
            var params = _util.getAttribute(input, IAT_INPUT_ATTRIBUTE);
            _iatParam.textMode = getParam(params, "text-mode") === "append" ? "append" : "replace";
            _iatParam.punctuation = getParam(params, "punctuation") === "true" ? true : false;
            var vadTail = getParam(params, "vad-tail");
            if (typeof vadTail !== UNDEF) {
                _iatParam.vadSpeechTail = parseInt(vadTail);
            }
            //原则上，Html中input会设置样式，否则在这里添加相关样式，但是可能会导致页面延迟展示录音按钮
            if (_input.className.indexOf("iatinput") > -1) {
                // 已存在iatinput样式 
            }
            else {
                // 设置iatinput样式 
                _input.className += " iatinput";
                _input.style.paddingRight = MIC_WIDTH + "px";
            }

            //添加hover效果
            _input.onmouseover = function () {
                if (_input.className.indexOf("iatinput_rec") == -1 && _input.className.indexOf("iatinput_hover") == -1) {
                    _input.className = _input.className.replace("iatinput", "iatinput_hover");
                }
            };

            //添加hover效果
            _input.onmouseout = function () {
                _input.className = _input.className.replace("iatinput_hover", "iatinput");
            };

            // 注册事件
            _util.addEvent(_input, "click", function () {
                onIatInputClick.apply(_this, arguments);
            });

            _util.addEvent(input, "mousemove", onIatInputMousemove);
        }

        function onIatInputClick(e) {
            //            if (_flashState == false) {
            //                _iatPanel.showSecuritySettings();
            //            }
            //            console.log("_iatPanel.getRecordStatus: " + _iatPanel.getRecordStatus());
            // console.log("%c【chengyang】 onIatInputClick", "color:blue");
            //chengcheng3  2014/5/28 添加命中点击语音按钮触发语音控件
            if (isInMicArea(_input, e) && !_iatPanel.getRecordStatus()) {
                if (!_recorder || !_recorder.isReady) {
                    alert("组件加载中");
                    return;
                }

                if (_recorder.getMicrophoneState() == MicrophoneState.Denied) {
                    _iatPanel.showSecuritySettings();
                }

                if (isInMicArea(_input, e)) {
                    //console.log("%c【chengyang】 isInMicArea", "color:blue");

                    if (_bakInput != _input && _bakInput != null) {
                        _bakInput.className = "iatinput";
                    }
                    // 停止冒泡
                    if (e.stopPropagation) {
                        e.stopPropagation();
                    } else {
                        e.cancelBubble = true;
                    }

                    if (_iatParam.textMode === "replace") {
                        _input.select();
                    }

                    if (_browser.msie) {
                        _input.blur();
                    }
                    _bakInput = _input;
                    _curInputInfo.id = this.id;
                    iatPanel.show(this);
                };
            } else {
                if (e.stopPropagation) {
                    e.stopPropagation();
                } else {
                    e.cancelBubble = true;
                }
            }
        };

        function onIatInputMousemove(e) {
            //保存手型鼠标
            this.style.cursor = isInMicArea(this, e) ? "pointer" : "text";
        }


        //-------------------------------------------------------------------------------------------
        //                                  Public Method
        //-------------------------------------------------------------------------------------------

        /**
         * 添加文本
         *
         * @method appendText
         * @param {String} text 识别文本
         */
        function appendText(text) {

            // 判断文本是否被选中
            var textSelected;
            if (_input.document) {
                textSelected = _input.document.selection.createRange().text.length > 0;
            } else {
                textSelected = (_input.selectionEnd - _input.selectionStart) > 0;
            }
            if (textSelected) {
                _input.value = "";
            }

            // 判断是否显示标点
            if (!_iatParam.punctuation) {
                text = text.replace(/[，。！]/gi, "");
            }

            _input.value += text;
        }

        /**
         * 获取输入框在页面中的位置信息
         *
         * @method getRect
         * @return {{x:number, y:number, w:number, h:number}} 输入框在页面中的位置信息
         */
        function getRect() {
            var pos = _util.getPosition(_input);

            var w = _input.clientWidth;
            var h = _input.clientHeight;

            //            console.log("%c[chengyang] x:%d; y:%d; w:%d; h:%d ", "color:blue", pos.x, pos.y, w, h);
            return { x: pos.x, y: pos.y, w: w, h: h };

        }

        /**
         * 显示输入框状态
         *
         * @method showMicState
         * @param {boolean} isRecording 是否是录音状态
         */
        function showMicState(isRecording) {
            var className = _input.className;
            if (isRecording) {
                if (_input.className.indexOf("iatinput_rec") == -1) {
                    _input.className = className.replace("iatinput_hover", "iatinput_rec");
                }
            } else {
                _input.className = className.replace("iatinput_rec", "iatinput");
            }
        }


        wrapInput();

        //-------------------------------------------------------------------------------------------
        //                                  Export Property & Method
        //-------------------------------------------------------------------------------------------
        _this.id = _id;
        _this.input = _input;
        _this.iatParam = _iatParam;

        _this.appendText = appendText;
        _this.getRect = getRect;
        _this.showMicState = showMicState;
    };

    IatInput.prototype.base = (function () {

        var iatInputId = 0;

        return {
            generateId: function () {
                return iatInputId++;
            }
        };
    })();

    /**
     * 听写面板
     *
     * @class IatPanel
     * @constructor
     */
    function IatPanel() {

        var REC_PANEL_WIDTH = 103,      // 录音面板高度
            RETRY_PANEL_WIDTH = 185,    // 重试面板高度
            ARRAY_RIGHT = 7;           // 背景图片箭头靠右边界位置

        //-------------------------------------------------------------------------------------------
        //                                  Field
        //-------------------------------------------------------------------------------------------
        var _recPanel;
        var _retryPanel;
        var _settingLink;
        var _recognisePanel;
        var _energyBar;
        var _retryMsg;

        //        var _recorder;
        var _currIatInput;
        var _isRecording;

        //用户设置flash录音禁止次数标记
        var _flashAllowShowed = true;
        var _hasError;

        var _mask;                  // 遮罩层
        var _micState;              // 麦克风状态
        var _isDelayHandle = true;  // 是否延迟处理麦克风设置


        //-------------------------------------------------------------------------------------------
        //                                  Private Method
        //-------------------------------------------------------------------------------------------

        /**
         * 创建听写面板
         *
         * @method buildIatPanel
         * @private
         */
        function buildIatPanel() {

            buildRecordPanel();
            buildRetryPanel();
            buildRecognisePanel();

            _util.addEvent(document, "click", close);
        }

        function addBtnMouseover(btn) {
            _util.addEvent(btn, "mouseover", function () {
                this.style.backgroundPosition = "-115px -154px";
            });

            _util.addEvent(btn, "mouseout", function () {
                this.style.backgroundPosition = "-115px -130px";
            });
        }

        function buildRecordPanel() {

            // 录音面板
            _recPanel = document.createElement("div");
            _recPanel.className = "iatinput_pop iatinput_rec_panel_bottom";
            _recPanel.style.cssText = "display:none;position:absolute;height:73px;";

            // 为了修复IE7下，能量条margin-top失效的问题，加入高度为0的div，强制recPanel中的元素重新布局
            var pos = document.createElement("div");
            pos.style.height = "0px";
            _recPanel.appendChild(pos);

            _energyBar = document.createElement("div");
            _energyBar.className = "iatinput_energy";

            var recTip = document.createElement("div");
            recTip.className = "line iatinput_logo";
            recTip.style.display = "inline";
            _util.setInnerText(recTip, _language.recTip);

            var recEndBtn = document.createElement("input");
            recEndBtn.type = "button";
            recEndBtn.className = "iatinput_btn";
            recEndBtn.style.marginTop = "5px";
            recEndBtn.value = _language.speakOver;
            addBtnMouseover(recEndBtn);
            _util.addEvent(recEndBtn, "click", showRecognizePanel);

            var recCancelLink = document.createElement("a");
            recCancelLink.href = "javascript:void(0)";
            _util.setInnerText(recCancelLink, _language.cancel);
            _util.addEvent(recCancelLink, "click", close);


            _recPanel.appendChild(_energyBar);
            _recPanel.appendChild(recTip);
            _recPanel.appendChild(recEndBtn);

            _recPanel.appendChild(recCancelLink);

            _util.addEvent(_recPanel, "click", onPanelClick);

            document.body.appendChild(_recPanel);
        }

        //正在识别面板  lswang
        function buildRecognisePanel() {

            // 正在识别面板
            _recognisePanel = document.createElement("div");
            _recognisePanel.className = "iatinput_pop iatinput_dis_panel_bottom";
            _recognisePanel.style.cssText = "display:none;position:absolute;height:73px;";

            // 为了修复IE7下，能量条margin-top失效的问题，加入高度为0的div，强制recPanel中的元素重新布局
            var pos = document.createElement("div");
            pos.style.height = "0px";
            _recognisePanel.appendChild(pos);

            var _energyBarShow = document.createElement("div");
            _energyBarShow.className = "iatinput_energy";

            var recRecognizeTip = document.createElement("div");
            recRecognizeTip.className = "iatinput_recTiplogo";
            recRecognizeTip.style.marginTop = "15px";
            recRecognizeTip.style.display = "inline";
            _util.setInnerText(recRecognizeTip, _language.recognizing);

            var recTip = document.createElement("div");
            recTip.className = "iatinput_recognise_logo";
            recTip.style.display = "inline";

            var recCancelLink = document.createElement("a");
            recCancelLink.href = "javascript:void(0)";
            recCancelLink.style.marginTop = "8px";
            recCancelLink.className = "iatinput_cancel";
            _util.setInnerText(recCancelLink, _language.cancel);
            _util.addEvent(recCancelLink, "click", close);

            _recognisePanel.appendChild(_energyBarShow);
            _recognisePanel.appendChild(recTip);
            _recognisePanel.appendChild(recRecognizeTip);

            var posLine = document.createElement("p");
            posLine.style.height = "0px";
            _recognisePanel.appendChild(posLine);

            _recognisePanel.appendChild(recCancelLink);

            _util.addEvent(_recognisePanel, "click", onPanelClick);

            document.body.appendChild(_recognisePanel);
        }

        function buildRetryPanel() {
            // 重试面板
            _retryPanel = document.createElement("div");
            _retryPanel.className = "iatinput_pop iatinput_retry_panel_bottom";
            _retryPanel.style.cssText = "display:none;position:absolute;height:83px;";

            _retryMsg = document.createElement("div");
            _retryMsg.className = "line iatinput_logo";
            _retryMsg.style.display = "inline";
            _util.setInnerText(_retryMsg, "重试提示信息");

            var showSettingBtn = document.createElement("div");
            showSettingBtn.className = "line";
            _settingLink = document.createElement("a");
            _settingLink.href = "#";
            _util.setInnerText(_settingLink, _language.settingMic);
            _util.addEvent(_settingLink, "click", showSecuritySettings);

            showSettingBtn.appendChild(_settingLink);

            var retryBtn = document.createElement("input");
            retryBtn.type = "button";
            retryBtn.className = "iatinput_btn";
            retryBtn.value = _language.retry;
            addBtnMouseover(retryBtn);
            _util.addEvent(retryBtn, "click", showRecPanel);

            var retryCancelBtn = document.createElement("input");
            retryCancelBtn.type = "button";
            retryCancelBtn.className = "iatinput_btn";
            retryCancelBtn.value = _language.cancel;
            addBtnMouseover(retryCancelBtn);
            _util.addEvent(retryCancelBtn, "click", close);

            _retryPanel.appendChild(_retryMsg);
            _retryPanel.appendChild(showSettingBtn);
            _retryPanel.appendChild(retryBtn);
            _retryPanel.appendChild(retryCancelBtn);
            _util.addEvent(_retryPanel, "click", onPanelClick);
            document.body.appendChild(_retryPanel);
        }

        function onPanelClick(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            } else {
                e.cancelBubble = true;
            }
        }

        /**
         * 创建录音器
         *
         * @method createRecorder
         * @param {String} recorderId 录音器ID
         * @private
         */
        function createRecorder(recorderId) {

            var recorderPanel = document.createElement("div");
            recorderPanel.id = recorderId;
            //document.body.appendChild(recorderPanel);

            var firstEle = document.body.firstChild;
            document.body.insertBefore(recorderPanel, firstEle);

            var swfUrl = SpeechRecorderSettings.Root + "SpeechRecorderIAT.swf";

            if (SpeechRecorderSettings.IsDebug === true) {
                swfUrl += "?r=" + Math.random();
            }

            var recordOptios = {
                swf: swfUrl,
                width: "1px",
                height: "0px",
                expressInstall: SpeechRecorderSettings.Root + "expressInstall.swf",
                flashVars: {
                    loadSkin: false,
                    recorderId: recorderId,
                    enableWebLog: true,
                    mspServer: SpeechRecorderSettings.MspServer,
                    logLvl: SpeechRecorderSettings.LogLvl,
                    mscLogLvl: SpeechRecorderSettings.MscLogLvl,
                    skin: SpeechRecorderSettings.Root + "Skins/sound.swf"
                }
            };

            _recorder = new SpeechRecorder(recorderId, recordOptios);
            var playerState = _recorder.getFlashPlayerState();

            if (playerState.state === FlashPlayerState.Normal) {
                _recorder.addEventListener(SpeechRecorderEvent.READY, onReady);
                _recorder.addEventListener(SpeechRecorderEvent.ERROR, onError);
                _recorder.addEventListener(SpeechRecorderEvent.RECORDING, onRecording);
                _recorder.addEventListener(SpeechRecorderEvent.MICROPHONE_STATE, onMicrophoneState);
                _recorder.addEventListener(SpeechRecorderEvent.IAT_RESULT, onIATResult);
                _recorder.addEventListener(SpeechRecorderEvent.IAT_COMPLETE, onIATComplete);
            }
            else {
                alert(playerState.msg);
            }
        }

        //-------------------------------------------------------------------------------------------
        //                                  Recorder Callback
        //-------------------------------------------------------------------------------------------

        function onReady(e) {
            _logUtil.info("SpeechRecorder is ready:" + e.version);
            setTimeout(function () {
                _recorder.enableIAT();
                _isRecording = false;
                _recorder.isReady = true;
            }, 200);
        }

        function onError(e) {

            _logUtil.error("iatinpt::onError, ErrorCode:" + e.errorCode + ", Msg:" + e.msg);
            _isRecording = false;
            _hasError = true;
            showRetryPanel(e);
        }

        function onRecording(e) {
            setEnergy(e.energy);
        }

        function onMicrophoneState(state) {
            _micState = state;
            _logUtil.debug("iatinput::onMicphoneState, _micState:" + _micState);

            if (!_isDelayHandle) {
                handleMicSetting();
            } else {
                _isDelayHandle = true;
                hideSecuritySettings();
            }
        }

        function handleMicSetting() {
            _isDelayHandle = false;
            _flashState = false;


            _micState = _recorder.getMicrophoneState();
            // 没有检测到麦克风
            if (_micState === MicrophoneState.NotFound) {
                showRetryPanel({ ErrorMsg: "未发现麦克风" });
                hideSecuritySettings();
            }
            // 用户拒绝对麦克风访问
            else if (_micState === MicrophoneState.Denied) {
                if (_flashAllowShowed) {
                    showSecuritySettings();
                    _flashAllowShowed = false;
                } else {
                    hideSecuritySettings();
                }
            }
            // 用户允许对麦克风访问
            else if (_micState === MicrophoneState.Allowed) {
                hideSecuritySettings();
                _flashState = true;
            }
            else if (_micState == MicrophoneState.NoEnoughSize) {
                _logUtil.error("没有足够空间显示安全设置面板");
            }
        }

        function onIATResult(e) {
            _logUtil.info("iatinpt::onIATResult - " + e.result);

            if (_currIatInput) {
                _currIatInput.appendText(e.result);
            }
        }

        function onIATComplete() {
            _isRecording = false;

            if (!_hasError) {
                hide();
            }

            var iatCompleteEvent = exportEvent.iatCompleteEvent;
            if (iatCompleteEvent !== null && typeof iatCompleteEvent === "function") {
                iatCompleteEvent();
            }
        }

        /**
         * 显示flash安全设置
         *
         * @method showSecuritySettings
         * @private
         */
        function showSecuritySettings() {

            var recorderEle = getRecorder();
            var cssText = 'z-index: 9999; \
                            width: 215px; \
                            height: 138px; \
                            position: absolute;';
            //            cssText += " \
            //                            top: -" + (document.body.offsetHeight - 200) + "px;";
            cssText += " \
                             top: " + (document.documentElement.scrollTop + 100) + "px;";
            cssText += " \
                             left: " + ((document.body.offsetWidth - 200) / 2) + "px;";

            recorderEle.style.cssText = cssText;

            // 创建遮罩层
            if (!_mask) {
                _mask = document.createElement("div");
                cssText = ' z-index: 1000;  \
                            border: none; \
                            margin: 0px; \
                            padding: 0px; \
                            top: 0px; \
                            left: 0px; \
                            background-color: rgb(0, 0, 0); \
                            opacity: 0.6; \
                            cursor: default; \
                            position: absolute;\
                            filter:  alpha(opacity=60);';

                cssText += " \
                             height: " + (document.body.offsetHeight) + "px;";
                cssText += " \
                             width: " + (document.body.offsetWidth) + "px;";

                _mask.style.cssText = cssText;
                document.body.appendChild(_mask);
                /* window.onresize =function () {
                 // / <summary>窗口大小改变事件</summary>
                 _mask.style.width = window.screen.availWidth;
                 _mask.style.height = window.screen.availHeight;
                 };*/

            }

            _mask.style.display = "";
            _flashAllowShowed = true;
            setTimeout(function () {
                _recorder.showSecuritySettings();
                onResize();
            }, 0);
        }

        /**
         * 页面重新布局事件
         *
         * @method showSecuritySettings
         * @private
         */
        function onResize() {
            if (_mask.style.display !== "none") {
                _mask.style.height = document.body.offsetHeight + "px";
                setTimeout(onResize, 100);
            }
        }

        /**
         * 隐藏flash安全设置
         *
         * @method showSecuritySettings
         * @private
         */
        function hideSecuritySettings() {
            var recorderEle = getRecorder();
            recorderEle.style.width = "1px";
            recorderEle.style.height = "0px";
            if (_mask) {
                _mask.style.display = "none";
            }
        }

        /**
         * 设置录音能量条
         *
         * @method setEnergy
         * @param {Number} energy 音频能量值(0-100)
         * @private
         */
        function setEnergy(energy) {
            var level;
            if (energy === 0) {
                level = 0;
            } else {
                level = Math.round(energy / 9);
            }

            if (level < 0) level = 0;
            else if (level > 11) level = 11;

            _energyBar.style.backgroundPosition = "0px " + (level * -10) + "px";
        }

        createRecorder(_recorderId);
        buildIatPanel();

        /**
         * 显示听写录音框
         *
         * @method showRecPanel
         * @private
         */
        function showRecPanel() {

            _logUtil.debug("iatinpt::showRecPanel | enter");

            if (_currIatInput && _recorder.isReady) {

                _retryPanel.style.display = "none";

                //by frsun 2014.1.9
                _recPanel.style.zIndex = 1001;

                // 计算并设置录音框位置
                showPanel(_recPanel);

                _hasError = false;
                var recogParam = { vadSpeechTail: _currIatInput.iatParam.vadSpeechTail };

                _isRecording = true;
                _recorder.beginRecognize(recogParam);
                var iatBeginEvent = exportEvent.iatBeginEvent;
                if (iatBeginEvent !== null && typeof iatBeginEvent === "function") {
                    iatBeginEvent();
                }
                _currIatInput.showMicState(true);

                _logUtil.debug("iatinpt::showRecPanel | leave ok");
            }
            else {
                _logUtil.warn("iatinpt::showRecPanel | leave, _currIatInput:" + (typeof _currIatInput) + ", _recorder.sReady:" + _recorder.isReady);
            }
        }

        /**
         * 显示听写录音框
         *
         * @method showRecognizePanel
         * @private
         */
        function showRecognizePanel() {

            _logUtil.debug("iatinpt::showRecognizePanel | enter");

            _retryPanel.style.display = "none";
            _recPanel.display = "none";

            // 计算并设置录音框位置
            showPanel(_recognisePanel);

            if (_recorder.isReady && _isRecording) {
                // 终止听写流程
                //console.log("%cshowRecognizePanel,endRecognize","color");
                _recorder.endRecognize();
                _isRecording = false; //chengcheng3 2014/5/29 修复语音重复点击事件一直处理识别中 bug
            }
            _logUtil.debug("iatinpt::showRecognizePanel | leave ok");

        }

        /**
         * 显示听写重试框
         *
         * @method showRetryPanel
         * @param {JSON} error 错误信息
         * @private
         */
        function showRetryPanel(error) {
            _logUtil.debug("iatinpt::showRetryPanel | enter");

            _recPanel.style.display = "none";

            // 计算并设置重试框的位置
            showPanel(_retryPanel);

            _util.setInnerText(_retryMsg, error.ErrorMsg);
            if (error.ErrorMsg && error.ErrorMsg === "未发现麦克风") {
                _retryPanel.style.paddingTop = "25px";
                _retryPanel.style.paddingBottom = "0px";
                _util.setInnerText(_settingLink, "");
            } else {
                _retryPanel.style.paddingTop = "15px";
                _util.setInnerText(_settingLink, _language.settingMic);
            }
        }

        /*
         * 显示录音框、重试框
         *
         * @method showPanel
         * @param panelType : String 显示框类型
         * @private
         */
        function showPanel(panelType) {
            var panelWidth = (panelType === _retryPanel ? RETRY_PANEL_WIDTH : REC_PANEL_WIDTH);

            var rect = _currIatInput.getRect();
            var panelLeft = (rect.x + rect.w) - (panelWidth + ARRAY_RIGHT)-4;
            var panelTop = 0;
            var height = 0;

            if (rect.y > window.screen.availHeight * 0.7) {
                switch (panelType) {
                    case _recPanel:
                        _recPanel.className = "iatinput_pop iatinput_rec_panel_bottom";
                        height = parseInt(document.getElementsByClassName('iatinput_rec_panel_bottom')[0].style.height.replace(/\D\D/, ''));
                        break;
                    case _recognisePanel:
                        _recognisePanel.className = "iatinput_pop iatinput_dis_panel_bottom";
                        height = parseInt(document.getElementsByClassName('iatinput_dis_panel_bottom')[0].style.height.replace(/\D\D/, ''));
                        break;
                    case _retryPanel:
                        _retryPanel.className = "iatinput_pop iatinput_retry_panel_bottom";
                        height = parseInt(document.getElementsByClassName('iatinput_retry_panel_bottom')[0].style.height.replace(/\D\D/, ''));
                        break;
                    default:
                        break;
                }
                panelTop = rect.y - height - MIC_HEIGHT * 1.5;
            } else {
                _recPanel.className = "iatinput_pop iatinput_rec_panel";
                _recognisePanel.className = "iatinput_pop iatinput_dis_panel";
                _retryPanel.className = "iatinput_pop iatinput_retry_panel";
                panelTop = rect.y + (rect.h + MIC_HEIGHT) / 2;
            }
            panelType.style.left = panelLeft + "px";
            panelType.style.top = panelTop + "px";
            panelType.style.display = "";
        }

        /**
         * 隐藏听写框
         *
         * @method hide
         * @private
         */
        function hide() {
            _logUtil.debug("iatinpt::hide | enter");

            _recPanel.style.display = "none";
            _retryPanel.style.display = "none";
            _recognisePanel.style.display = "none";
            setEnergy(0);

            if (_currIatInput) {
                _currIatInput.showMicState(false);
            }

            _currIatInput = undefined;
        }

        //-------------------------------------------------------------------------------------------
        //                                  Public Method
        //-------------------------------------------------------------------------------------------

        /**
         * 显示听写框
         *
         * @method show
         * @param {IatInput} iatinput 关联的IatInput对象
         */
        function show(iatinput) {

            if (_currIatInput !== undefined) {
                if (_currIatInput.id === iatinput.id) {
                    if (_isRecording) {
                        return;
                    }
                }
            }

            _currIatInput = iatinput;

            if (_micState === MicrophoneState.Allowed) {

                if (_isRecording) {
                    close();
                }
                showRecPanel();
            } else {
                handleMicSetting();
            }
        }

        /**
         * 关闭听写框
         *
         * @method close
         */
        function close() {
            //console.log("%c【chengyang】close", "color:blue");
            hide();

            if (_recorder.isReady && _isRecording) {
                // 终止听写流程
                //console.log("%cclose,abortRecognize","color:blue");
                _isRecording = false;
                _recorder.abortRecognize();
            }
        }

        /**
         * 获取录音器DOM
         *
         * @method getRecorderDom
         * @return 录音器DOM
         */
        function getRecorderDom() {
            return document.getElementById(_recorderId);
        }

        function getRecordStatus() {
            return _isRecording;
        }

        return {
            show: show,
            close: close,
            getRecorderDom: getRecorderDom,
            showSecuritySettings: showSecuritySettings,
            getRecordStatus: getRecordStatus
        };
    }

    //-------------------------------------------------------------------------------------------
    //                                  iatinput Private Method
    //-------------------------------------------------------------------------------------------

    function hasWraped(input) {
        for (var i = 0; i < _wrapedInputs.length; i++) {
            if (input === _wrapedInputs[i]) {
                return true;
            }
        }
        return false;
    }

    function renderIatInputs(inputs, iatPanel) {

        _logUtil.info("iatinput renderIatInputs, length:" + inputs.length);

        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            if (!hasWraped(input)) {
                _iatInputs.push(new IatInput(input, iatPanel));
            } else {
                _logUtil.warn("input has wraped");
            }
        }
    };

    function isInMicArea(input, event) {
        var offsetX = event.offsetX || event.layerX;
        var offsetY = event.offsetY || event.layerY;

        return input.clientWidth - offsetX < MIC_WIDTH
            && (input.clientHeight < MIC_HEIGHT ||
            (offsetY > MIC_HEIGHT / 2 && offsetY < input.clientHeight - MIC_HEIGHT / 2));
    }

    function getParam(params, name) {
        params = params.replace(" ", "");
        var matched = new RegExp(name + ':([^:;]*)[;$]?', 'gi').exec(params);
        return matched ? matched[1] : undefined;
    }

    function getMarkedInputs(inputs) {
        if (!inputs) {
            inputs = document.getElementsByTagName("input");
        }
        var markedInputs = [];
        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            var isIatInput = _util.hasAttribute(input, IAT_INPUT_ATTRIBUTE);
            if (isIatInput) {
                markedInputs.push(input);
            }
        }
        return markedInputs;
    }

    function init() {
        _browser = _util.getBrowserInfo();
        _util.addEvent(win, "load", function () {

            _logUtil.info("iatinput window onload");

            _iatPanel = new IatPanel();

            // render pending wrap inputs
            if (_pendingWrapInputs.length > 0) {
                renderIatInputs(_pendingWrapInputs, _iatPanel);
            }

            // 
            var markedInputs = getMarkedInputs();
            renderIatInputs(markedInputs, _iatPanel);
        });
    }

    init();

    //-------------------------------------------------------------------------------------------
    //                                  iatinput Public Method
    //-------------------------------------------------------------------------------------------

    /**
     * 获取录音器DOM
     *
     * @method getRecorder
     * @return 录音器DOM
     */
    function getRecorder() {
        return _iatPanel.getRecorderDom();
    }

    /*
     *关闭面板
     *@method closeIat
     */
    function closeIat() {
        _iatPanel.close();
    }

    /**
     * 渲染文本输入框
     *
     * @method renderInputs
     * @param {Array} inputs 文本输入框
     */
    function renderInputs(inputs) {
        if (inputs) {
            if (!(inputs instanceof Array)) {
                inputs = [inputs];
            }

            var markedInputs = getMarkedInputs(inputs);

            if (!_iatPanel) {
                for (var i = 0; i < markedInputs.length; i++) {
                    _pendingWrapInputs.push(markedInputs[i]);
                }
                _logUtil.info("iatPanel not initialized, pendingWrapInputs, length:" + _pendingWrapInputs.length);

                return;
            }

            renderIatInputs(markedInputs, _iatPanel);
        }
    }

    /**
     * iatinput全局配置
     *
     * @method config
     * @param {JSON} configs 配置参数
     */
    function config(configs) {
        if (configs && configs.language === "en") {
            _language = language.en;
        }

        if (configs && configs.debug === true) {
            _debug = true;
        }

        if (configs && configs.id) {
            _recorderId = configs.id;
        }
    }

    var exportEvent = {
        iatCompleteEvent: null,
        iatBeginEvent: null
    };
    /**
     * 外部处理onIatComplete事件
     *
     * @method handleExportEvent
     * @param {function} iatBeginEvent 外部处理iatBeginEvent事件函数
     * @param {function} iatCompleteEvent 外部处理iatCompleteEvent事件函数
     */
    function handleExportEvent(iatBeginEvent, iatCompleteEvent) {
        exportEvent.iatBeginEvent = iatBeginEvent;
        exportEvent.iatCompleteEvent = iatCompleteEvent;
    }

    //-------------------------------------------------------------------------------------------
    //                                  Export iatinput
    //-------------------------------------------------------------------------------------------
    // isInMicArea: 提供外部检测判断语音按钮 chengcheng3 2014/5/28
    win.iatinput = {
        getRecorder: getRecorder,
        renderInputs: renderInputs,
        closeIat: closeIat,
        config: config,
        curInput: _curInputInfo,
        handleExportEvent: handleExportEvent,
        isInMicArea:isInMicArea
    };

})(window, document);