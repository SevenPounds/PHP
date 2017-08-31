//------------------------------------------------  LogUtil  ---------------------------------------------
(function (options) {

    var _width = options.width;
    var _left = options.left;
    var _top = options.top;

    var _height = options.height;
    var _showPanel = options.showPanel === true;
    var _showConsole = options.showConsole === true;
    var _logPanel = document.createElement("div");

    if (document.body) {
        appendLogPanel();
    } else {
        addLoadEvent(appendLogPanel);
    }

    /**
    * 为document添加onload事件
    *
    * @method addLoadEvent
    * @param {Function} fn onload事件处理方法
    * @private
    */
    function addLoadEvent(fn) {

        // Mozilla, Opera etc.
        if (window.addEventListener) {
            window.addEventListener("load", fn, false);
        }
            // IE
        else if (document.attachEvent) {
            window.attachEvent("onload", fn);
        }
            // Others
        else {
            window['on' + event] = fn;
        }
    }

    /**
    * 向页面上添加日志容器
    *
    * @method appendLogPanel
    * @private
    */
    function appendLogPanel() {
        var css = "width:" + _width + "px; \
                    height:" + _height + "px; \
                    position:absolute; \
                    border:1px solid #ccc; \
                    background-color:#fff; \
                    left:" + _left + "px; \
                    top:" + _top + "px; \
                    word-wrap: break-word; \
                    overflow-y:scroll; \
                    display:" + (_showPanel ? "" : "none");

        _logPanel.style.cssText = css;
        document.body.appendChild(_logPanel);
    }

    /**
    * 格式化时间
    *
    * @method formatDate
    * @private
    * @param {String} pattern 样式名模式(例如: yyyy-MM-dd hh:mm:ss)
    * @return {String} 格式化后的时间字符串
    */
    function formatDate(pattern) {

        function fs(num) {
            return num < 10 ? "0" + num : num.toString();
        }

        var s = pattern.replace(/yyyy/g, this.getFullYear());
        s = s.replace(/MM/g, fs(this.getMonth() + 1));
        s = s.replace(/dd/g, fs(this.getDate()));
        s = s.replace(/hh/ig, fs(this.getHours()));
        s = s.replace(/mm/g, fs(this.getMinutes()));
        s = s.replace(/ss/g, fs(this.getSeconds()));
        var milliseconds = this.getMilliseconds();
        s = s.replace(/fff/g, milliseconds < 10 ? "00" + milliseconds : milliseconds < 100 ? "0" + milliseconds : milliseconds);

        return s;
    }

    /**
    * 显示消息
    *
    * @method showMessage
    * @private
    * @param {String} msg       消息内容
    * @param {String} perfix    消息前缀
    * @param {String} color     消息显示颜色
    **/
    function showMessage(msg, perfix, color) {
        if (_showPanel) {
            msg = formatDate.call(new Date(), "yyyy-MM-dd HH:mm:ss.fff") + " " + perfix + " : " + msg;
            var msgPanel = document.createElement("span");
            msgPanel.style.color = color;

            // ie, chrome, safari
            if (msgPanel.innerText !== undefined) {
                msgPanel.innerText = msg;
            } else { // firefox
                msgPanel.textContent = msg;
            }
            _logPanel.appendChild(msgPanel);
            _logPanel.appendChild(document.createElement("br"));

            _logPanel.scrollTop = _logPanel.scrollHeight;
        }
    }

    var LogCatLevel = {
        DEBUG: 3,
        INFO: 4,
        WARN: 5,
        ERROR: 6
    };

    /**
    * 显示LogCat消息, Android平台下使用(同时Andorid中要实现相应的日志适配器)
    *
    * @method showLogCat
    * @private
    * @param {Int} level      消息级别(参考LogCatLevel)
    * @param {String} msg     消息内容
    * @param {String} tag     消息标签
    **/
    function showLogCat(level, msg, tag) {
        if (window.LogCatInterface) {
            window.LogCatInterface.log(level, msg, tag || "SpeechJS Log");
        }
    }

    /**
    * 重新加载日志属性
    * 
    * LogUtil.reload({
    *    width: 300, 
    *    height: 450,
    *    left: 630,
    *    top: 10, 
    *    showPanel: true,           // 显示日志容器日志
    *    showConsole: true          // 显示控制台日志 
    * });
    *
    * @method reload
    * @param {JSON} option    配置参数
    **/
    function reload(option) {
        if (option && _logPanel) {
            if (typeof option.width === "number") {
                _width = option.width;
                _logPanel.style.width = _width + "px";
            }

            if (typeof option.height === "number") {
                _height = option.height;
                _logPanel.style.height = _height + "px";
            }

            if (typeof option.left === "number") {
                _left = option.left;
                _logPanel.style.left = _left + "px";
            }

            if (typeof option.top === "number") {
                _top = option.top;
                _logPanel.style.top = _top + "px";
            }

            if (typeof option.showPanel === "boolean") {
                _showPanel = option.showPanel;
                if (_showPanel) {
                    _logPanel.style.display = "";
                } else {
                    _logPanel.style.display = "none";
                }
            }

            if (typeof option.showConsole === "boolean") {
                _showConsole = option.showConsole;
            }
        }
    }

    window.LogUtil = {

        debug: function (msg, tag) {
            showMessage(msg, "DEBUG", "gray");
            if (_showConsole && window.console && window.console.log) {
                window.console.log(msg);
            }

            showLogCat(LogCatLevel.DEBUG, msg, tag);
        },
        info: function (msg, tag) {
            showMessage(msg, "INFO", "green");
            if (_showConsole && window.console && window.console.info) {
                window.console.info(msg);
            }

            showLogCat(LogCatLevel.INFO, msg, tag);
        },
        warn: function (msg, tag) {
            showMessage(msg, "WARN", "orange");
            if (_showConsole && window.console && window.console.warn) {
                window.console.warn(msg);
            }

            showLogCat(LogCatLevel.WARN, msg, tag);
        },
        error: function (msg, tag) {
            showMessage(msg, "ERROR", "red");
            if (_showConsole && window.console && window.console.error) {
                window.console.error(msg);
            }

            showLogCat(LogCatLevel.ERROR, msg, tag);
        },
        reload: reload,
        clear: function () {
            _logPanel.innerHTML = "";
        }
    };

})({ width: 300, height: 450, left: 630, top: 10, showPanel: false, showConsole: false });


//字符串的连接
String.format = function () {
    if (arguments.length == 0)
        return null;
    var str = arguments[0];
    for (var i = 1; i < arguments.length; i++) {
        var re = new RegExp('\\{' + (i - 1) + '\\}', 'gm');
        str = str.replace(re, arguments[i]);
    }
    return str;
};