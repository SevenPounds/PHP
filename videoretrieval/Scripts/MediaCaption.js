/// <reference path="Common.js" />

/**
* @author xlzhou2
* 2013-04-10
*/

/*********************************************************************
全局变量定义块
*********************************************************************/
var _videoId = '';
var _keyWords = '';
var resultData;
var _type = 0;//标记是否点击了搜索 如果没有为0 ，否则不为0
var _fullscreen = false;//标记是否已经全屏
var _playstatus = 0;//标记播放状态,0表示暂停，1表示已播放
var interTimer;//延时器
var _STime;//开始时间
var _ETime;//结束时间
var _paraId;//标记的DOM对象ID
var _dataList;//数据list
var _timmer;//延时器
var autoPlayTime = '';
/*********************************************************************
全局变量定义块 —— end
*********************************************************************/


var ShowContent = {
    //页面左边数据展示
    show_vd_left: function (data) {
        var name = data.MediaName;
        var introduction = data.Introduction;
        var belong = data.Belong;
        if (!name)
            name = "";
        if (!introduction)
            introduction = "";
        if (!belong)
            belong = "";
        var htmlShow = [];
        htmlShow.push(String.format('<div class="vd_left_title">{0}</div><div class="vd_left_vd" id="videoContain"></div>', name));
        //htmlShow.push(String.format('<div class="vd_left_under" ><h2>视频简介：</h2><p>{0}</p><h2>所属学科：</h2><p>{1}</p></div>', introduction, belong));
        htmlShow.push(String.format('<div class="vd_left_under" ><h2 style="font-weight:bold;">视频简介：</h2><p style="text-indent: 2em;">{0}</p></div>', introduction));
        jQuery("#videoLeft").html(htmlShow.join(''));
    },
    //页面右边数据展示
    show_vd_right: function (data) {
        this.show_rightTitle(data);
        this.show_rightContent(data);
    },
    //页面右边头部展示
    show_rightTitle: function () {
        var htmlShow = [];
        htmlShow.push('<div class="vd_right_title"><div class="search_box"><div class="search_kuang"><div class="search_sr">');
        htmlShow.push('<input type="text" id="inputSearch" style="outline:none" class="iatinput" iatinput="" onkeydown="BindEnter(event)" /></div></div>');
        htmlShow.push(' <div class="btngray"><input onclick="ClickEvents.clickSearch()" name="搜索" type="button" class="btn_gray01" onmouseover="this.className=\'btn_gray02\'" onmouseout="this.className=\'btn_gray01\'" value="在视频中搜索" style="cursor:pointer" />');
        htmlShow.push('</div><div class="clear"></div></div>');
        htmlShow.push('<div class="vd_right_text">字体：<span ><span class="at"  id="font_1" onclick="ClickEvents.clickFont(this)">大</span></span>&nbsp;');
        htmlShow.push('<span ><span id="font_2"  onclick="ClickEvents.clickFont(this)">中</span></span>&nbsp;');
        htmlShow.push('<span><span id="font_3" onclick="ClickEvents.clickFont(this)">小</span></span></div>');
        htmlShow.push('');
        jQuery("#videoRightTitle").html(htmlShow.join(''));
        setTimeout(function () {
            iatinput.renderInputs(document.getElementById("inputSearch"));
        }, 200);
    },
    //页面右边正文展示
    show_rightContent: function (data) {
        PageShow.small_ListShow(data);
        var id = jQuery(".at").attr("id");
        if (id == "font_1") {
            jQuery(".v_list ul li .text p").css("font-size", 16);
        } else if (id == "font_2") {
            jQuery(".v_list ul li .text p").css("font-size", 14);
        } else if (id == "font_3") {
            jQuery(".v_list ul li .text p").css("font-size", 12);
        }
        _dataList = data.MediaKeyWordsDetailList;
        CallBack();
    },
    //页面右边正文展示 20130503 backup
    show_rightContent2: function () {
        var contentList = data.MediaKeyWordsDetailList;
        var listLength = contentList.length;
        var changeWord;
        var htmlShow = [];
        htmlShow.push('<div class="vd_right_cent">');
        for (var i = 0; i < listLength; i++) {
            htmlShow.push(String.format('<div class="vd_t"><div class="vd_tl blue">{0}</div>', data.MediaKeyWordsDetailList[i].StartTime));
            if (_type == 0) {
                htmlShow.push(String.format('<div class="vd_tr" style="cursor:pointer" onclick="ClickEvents.clickContent({1},\'{2}\',\'{3}\',\'playedTime{1}\')" id="playedTime{1}"><a>{0}</a></div></div>', contentList[i].KeyWordsContent, contentList[i].StartSecondTime, contentList[i].StartTime, contentList[i].EndTime));
                // htmlShow.push(String.format('<p style="cursor:pointer" onclick="ClickEvents.clickContent({1},\'{2}\',\'{3}\',\'playedTime{1}\')" id="playedTime{1}"><a >{0}</a></p>', contentList[i].KeyWordsContent, contentList[i].StartSecondTime, contentList[i].StartTime, contentList[i].EndTime));
            }
            else {
                changeWord = contentList[i].KeyWordsContent.replace(new RegExp(_keyWords, 'g'), String.format('<span class="redcolor">{0}</span>', _keyWords));
                htmlShow.push(String.format('<div class="vd_tr" style="cursor:pointer" onclick="ClickEvents.clickContent({1},\'{2}\',\'{3}\',\'playedTime{1}\')" id="playedTime{1}"><a>{0}</a></div></div>', changeWord, contentList[i].StartSecondTime, contentList[i].StartTime, contentList[i].EndTime));
            }
        }

        htmlShow.push('</div>');
        jQuery("#rolling").html(htmlShow.join(''));
        var id = jQuery(".at").attr("id");
        if (id == "font_1") {
            jQuery(".vd_right_cent").css("font-size", 16);
        } else if (id == "font_2") {
            jQuery(".vd_right_cent").css("font-size", 14);
        } else if (id == "font_3") {
            jQuery(".vd_right_cent").css("font-size", 12);
        }
        _dataList = data.MediaKeyWordsDetailList;
        CallBack();
    },
    //页面右边正文展示 20130428 backup
    show_rightContent1: function (data) {
        var contentList = data.MediaKeyWordsDetailList;
        var listLength = contentList.length;
        var changeWord;
        var htmlShow = [];
        htmlShow.push('<div class="vd_right_cent">');
        for (var i = 0; i < listLength; i++) {
            if (_type == 0)
                htmlShow.push(String.format('<p style="cursor:pointer" onclick="ClickEvents.clickContent({1},\'{2}\',\'{3}\',\'playedTime{1}\')" id="playedTime{1}"><a >{0}</a></p>', contentList[i].KeyWordsContent, contentList[i].StartSecondTime, contentList[i].StartTime, contentList[i].EndTime));
            else {
                changeWord = contentList[i].KeyWordsContent.replace(new RegExp(_keyWords, 'g'), String.format('<span class="redcolor">{0}</span>', _keyWords));
                htmlShow.push(String.format('<p style="cursor:pointer" onclick="ClickEvents.clickContent({1},\'{2}\',\'{3}\',\'playedTime{1}\')" id="playedTime{1}"><a >{0}</a></p>', changeWord, contentList[i].StartSecondTime, contentList[i].StartTime, contentList[i].EndTime));
            }
        }
        htmlShow.push('</div>');
        jQuery("#rolling").html(htmlShow.join(''));
        var id = jQuery(".at").attr("id");
        if (id == "font_1") {
            jQuery(".vd_right_cent").css("font-size", 16);
        } else if (id == "font_2") {
            jQuery(".vd_right_cent").css("font-size", 14);
        } else if (id == "font_3") {
            jQuery(".vd_right_cent").css("font-size", 12);
        }
        _dataList = data.MediaKeyWordsDetailList;
        CallBack();
    },

    //展示播放器
    showVideo: function () {
        var htmlShow = [];
        htmlShow.push('<div id="mask_layer" onclick="fn_clickMaskLayer()"></div><div id="jp_container_1" class="jp-video jp-video-360p"><div class="jp-type-playlist"><div id="jquery_jplayer_1" class="jp-jplayer"></div>');
        htmlShow.push('<div class="jp-gui"><div class="jp-video-play"><a href="javascript:;" class="jp-video-play-icon" tabindex="1" onclick="fn_startpaly()">play</a></div>');
        htmlShow.push('<div class="jp-interface"><div class="jp-progress" style="margin-top: 14px"><div class="jp-seek-bar"><div class="jp-play-bar"></div></div>');
        htmlShow.push(' </div><div class="jp-controls-holder" style="margin-top: -20px;"><ul class="jp-controls" style="margin-left: 5px;">');
        htmlShow.push('<li><a href="javascript:;" class="jp-play" tabindex="1" onclick="fn_startpaly()">play</a></li>');
        htmlShow.push(' <li><a href="javascript:;" class="jp-pause" tabindex="1" onclick="fn_playerpause()">pause</a></li>');
        htmlShow.push(' <li><a href="javascript:;" class="jp-stop" onclick="fn_playerstop()" tabindex="1">stop</a></li>');
        htmlShow.push(' <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>');
        htmlShow.push('</ul>');
        htmlShow.push('<div class="jp-volume-bar"><div class="jp-volume-bar-value"></div></div><ul class="jp-toggles" >');
        htmlShow.push(' <li><a style="cursor: pointer" class="jp-full-screen" tabindex="1" title="full screen" onclick="fn_fullScreen()">full screen</a></li>');
        htmlShow.push('<li><a style="cursor: pointer" class="jp-restore-screen" tabindex="1" title="restore screen" onclick="fn_restoreScreen()">restore screen</a></li>');
        htmlShow.push(' </ul>');
        htmlShow.push('    </div></div></div></div></div>');
        jQuery("#videoContain").html(htmlShow.join(''));
    }
}


var ClickEvents = {
    keywords: "",
    fontid: '',
    /*********************************************************************
      * description :点击文本段落事件
      * param :playTime   : 播放起始时间 
      *********************************************************************/
    clickContent: function (playTime, STime, ETime, paraId) {
        clearInterval(_timmer);
        JumpToTime(playTime);
        fn_startpaly();
        _STime = TimeToInt(STime);
        _ETime = TimeToInt(ETime);
        _paraId = paraId;
        //fn_setrelatedtextcolor();
        //CallBack();
    },
    /*********************************************************************
     * description :点击字体大小改变事件
     *********************************************************************/
    clickFont: function (obj) {
        //  jQuery(obj)
        if (this.fontid == obj.id)
            return;
        if (obj.id == "font_1") {
            jQuery("#font_2").removeClass("at");
            jQuery("#font_3").removeClass("at");
            jQuery("#font_1").attr("class", "at");
            jQuery("..v_list ul li .text p").css("font-size", 16);
        } else if (obj.id == "font_2") {
            jQuery("#font_1").removeClass("at");
            jQuery("#font_3").removeClass("at");
            jQuery("#font_2").attr("class", "at");
            jQuery("..v_list ul li .text p").css("font-size", 14);
        } else if (obj.id == "font_3") {
            jQuery("#font_2").removeClass("at");
            jQuery("#font_1").removeClass("at");
            jQuery("#font_3").attr("class", "at");
            jQuery("..v_list ul li .text p").css("font-size", 12);
        }
        this.fontid = obj.id;
    },
    /*********************************************************************
    * description :点击搜索事件
    *********************************************************************/
    clickSearch: function () {

        _keyWords = jQuery("#inputSearch")[0].value;
        if (this.keywords == _keyWords)
            return;
        this.keywords = _keyWords;
        if (_keyWords) {
            _type++;
            SuccessDataBack();
            //GetData.contentData(SuccessDataBack, ErrorDataBack);
        } else {
            //SuccessDataBack();
            ShowContent.show_rightContent(resultData);
        }
    }

}

/*********************************************************************
* description : Enter 键触发事件
*********************************************************************/
function BindEnter(obj) {
    if (obj.keyCode == 13) {
        ClickEvents.clickSearch();
        obj.returnValue = false;
    }
}

/*********************************************************************
* description : 全屏事件
*********************************************************************/
function fn_fullScreen() {
    var width = document.body.clientWidth;
    var height = window.screen.height;
    var progressWidth = document.body.clientWidth - 180;
    //jQuery(".jp-gui").show();
    jQuery(".jp-controls-holder").css({ width: width, "margin-top": "-25px" });
    jQuery(".jp-progress").css({ width: progressWidth, "margin-top": "15px", "z-index": "2000" });
    jQuery(".jp-volume-max").css({ top: "15px" });
    jQuery(".jp-volume-bar").css({ "margin-top": "17px" });
    jQuery(".jp-controls").css({ "margin-top": "10px" });
    jQuery("#fullscreen_masklayer").css({ display: "block", width: width, height: height });
    jQuery(".jp-video-play").css({ height: "100%" });
    jQuery(".jp-video-play-icon").css({ left: "45%", top: "40%" });
    jQuery("html").css({ overflow: "hidden" });
    _fullscreen = true;
}

function fn_restoreScreen() {
    jQuery(".jp-controls-holder").css({ width: 400, "margin-top": "-20px" });
    jQuery(".jp-progress").css({ width: 240, "margin-top": "14px", "z-index": "1000" });
    jQuery(".jp-volume-max").css({ top: "10px" });
    jQuery(".jp-volume-bar").css({ "margin-top": "12px" });
    jQuery(".jp-controls").css({ "margin-top": "5px" });
    jQuery("#fullscreen_masklayer").css({ display: "none" });
    jQuery(".jp-video-play").css({ height: "300px" });
    jQuery(".jp-video-play-icon").css({ left: "30%", top: "30%" });
    jQuery("html").css({ overflow: "auto" });

    setTimeout(function () { jQuery(".jp-progress").css("position", "none") }, 10);
    setTimeout(function () { jQuery(".jp-progress").css("position", "absolute") }, 20);

    _fullscreen = false;
}

//退出全屏后的后续操作
function fn_restoryPlayOrPause() {
    if (_playstatus == 0) {
        setTimeout(function () { fn_startpaly(); }, 20);
        setTimeout(function () {
            fn_playerpause();
        }, 40);
    } else if (_playstatus == 1) {
        fn_startpaly();
        setTimeout(function () { fn_playerpause(); }, 20);
        setTimeout(function () {
            fn_startpaly();
        }, 40);
    }
}

/*********************************************************************
* description : 监视窗体的变化
*********************************************************************/
jQuery(window).resize(function () {
    if (_fullscreen) {
        fn_fullScreen();
    }
})

/*********************************************************************
* description : 点击遮罩层事件
*********************************************************************/
function fn_clickMaskLayer() {
    if (_playstatus == 1) {
        fn_playerpause();
        _playstatus = 0;
    }
    else {
        fn_startpaly();
        _playstatus = 1;
    }
}

/*********************************************************************
* description : 播放器的播放、暂停和停止方法
*********************************************************************/
//开始播放
function fn_startpaly() {
    JP_play();
    //setTimeout(function () {
    //    jQuery("#jquery_jplayer_1").jPlayer("play")
    //}, 200);
   // _playstatus = 1;
   // jQuery(".jp-video-play").hide();
    //clearInterval(interTimer);
    //fn_setrelatedtextcolor();
    //interTimer = setInterval(function () {
    //    fn_setrelatedtextcolor();
    //}, 300);
}

//暂停播放
function fn_playerpause() {
    setTimeout(function () {
        jQuery("#jquery_jplayer_1").jPlayer("pause");
    }, 200);
    _playstatus = 0;
    jQuery(".jp-video-play").show();
    //fn_setrelatedtextcolor();
    //clearInterval(interTimer);
}

//停止播放
function fn_playerstop() {
    setTimeout(function () {
        jQuery("#jquery_jplayer_1").jPlayer("stop");
    }, 200);
    _playstatus = 0;
    jQuery(".jp-video-play").show();
    jQuery(".yellowcolor").each(function () {
        jQuery(this).attr({ "class": "" });
    });
    //if (interTimer) {
    //    clearInterval(interTimer);
    //}
}

/*********************************************************************
* description : 全屏时的播放、暂停
*********************************************************************/
function fn_fullscreenclick() {
    if (_playstatus == 1) {
        fn_playerpause();
        _playstatus = 0;
    }
    else {
        fn_startpaly();
        fn_showbutPlay();
        _playstatus = 1;
    }
}

//获取当前播放时间
function getCurrentTime() {
    var config = jQuery("#jquery_jplayer_1").data("jPlayer.config");//整个取出来
    var playedTime = config.status.currentTime;//还有多少时间才结束
    return playedTime;
}

/*********************************************************************
* description : 根据播放器的当前播放时间，设置文本的高亮显示
*********************************************************************/
var _currenthideId = null;
function fn_setrelatedtextcolor() {
    try {
        var hideId = _paraId.substring(5, _paraId.length);

        if (_currenthideId == hideId)
            return;
        _currenthideId = hideId;
        var fatherClass = jQuery(".v_list");
        var fatherClassLength = fatherClass.length;

        for (var i = 0; i < fatherClassLength; i++) {
            if (fatherClass[i].id.indexOf("hide_") >= 0) {
                jQuery("#" + fatherClass[i].id).hide();
            }
            else if (fatherClass[i].id.indexOf("show_") >= 0) {
                jQuery("#" + fatherClass[i].id).show();
            }
        }
        jQuery("#show_" + hideId).hide();
        jQuery("#" + _paraId).show();
    } catch (e) { }
}


function CallBack(data) {
	
    data = _dataList;
    var dataLength = data.length;
    var lastdataLength = dataLength - 1;

    clearInterval(_timmer);
    _timmer = setInterval(function () {
        var currentPlayTiem = getCurrentTime();

        for (var i = 0; i < dataLength; i++) {
            if (currentPlayTiem <= TimeToInt(data[i].EndTime)) {
                _STime = TimeToInt(data[i].StartTime);
                _ETime = TimeToInt(data[i].EndTime);
                // if (currentPlayTiem >= _STime && currentPlayTiem <= _ETime)
                try {
                    // if (i == 0) {
                    _paraId = 'hide_' + data[i].StartSecondTime;
                    // }
                    // else
                    //     _paraId = 'hide_' + data[i - 1].StartSecondTime;

                } catch (e) {
                    //_paraId = 'hide_' + data[i].StartSecondTime;
                }
                break;
            }
            //else if (currentPlayTiem >= data[dataLength - 1].StartSecondTime) {
            //    _STime = TimeToInt(data[dataLength - 1].StartTime);
            //    _ETime = TimeToInt(data[dataLength - 1].EndTime);
            //    _paraId = 'hide_' + data[dataLength - 1].StartSecondTime;
            //}
        }
    }, 100);

    clearInterval(interTimer);
    //fn_setrelatedtextcolor();
    interTimer = setInterval(function () {
        fn_setrelatedtextcolor();
    }, 300);
}

//截获时间
function TimeToInt(time) {
    time = time.replace("：", ":");
    if (time.indexOf(':') > 0) {
        var timesplit = time.split(':');
        var hours = 0;
        var minute = 0;
        var seconds = 0;
 
        if (time.indexOf(':') === time.lastIndexOf(':')) {//格式为00:00           
            if (parseInt(timesplit[0]) > 0) {
                minute = parseInt(timesplit[0]);
            }
            seconds = parseInt(timesplit[1]);
        } else {//格式为00:00:00
            if (parseInt(timesplit[0]) > 0)
                hours = parseInt(timesplit[0]);
 
            if (parseInt(timesplit[1]) > 0)
                minute = parseInt(timesplit[1]);
            seconds = parseInt(timesplit[2]);
        }
        return hours * 3600 + minute * 60 + seconds;
    } else
        return 0;
}


//截获时间
function TimeToInt2(time) {
    time = time.replace("：", ":");
    //var timeList
}


var PageShow = {
    clickObj: '',
    clickId: '',
    //展示二次搜索后的列表
    small_ListShow: function (data) {
        // jQuery("#small_searchList").html('');
        var htmlShow = [];
        // var tipShow = [];
        try {

            if (data.MediaKeyWordsDetailList == null)
                return;
            var dataLength = data.MediaKeyWordsDetailList.length;
            for (var i = 0; i < dataLength; i++) {
                var detailTemp = data.MediaKeyWordsDetailList[i];
                //正常展示
                if (autoPlayTime == detailTemp.StartSecondTime) {
                    if (detailTemp.KeyWordsContent)
                        htmlShow.push(String.format('<div class="v_list" id="show_{0}" style="display:none"', detailTemp.StartSecondTime));
                    else
                        htmlShow.push(String.format('<div class="v_list" id="show_{0}" style="height:70px;display:none" ', detailTemp.StartSecondTime));
                    this.clickId = i;
                } else {
                    if (detailTemp.KeyWordsContent)
                        htmlShow.push(String.format('<div class="v_list" id="show_{0}" ', detailTemp.StartSecondTime));
                    else
                        htmlShow.push(String.format('<div class="v_list" id="show_{0}" style="height:70px;" ', detailTemp.StartSecondTime));
                }
                htmlShow.push(String.format(' onclick="PageShow.rightVideoClick({0},this)" ucObj=1>', detailTemp.StartSecondTime));
                // htmlShow.push('<ul><li class="v_list_normal" onmouseover="PageShow.onmouseoverEvent(this)" onmouseout="PageShow.onmouseoutEvent(this)" ');
                htmlShow.push(String.format('<ul onmouseover="PageShow.onmouseoverEvent(\'{0}\',\'{1}\',\'{2}\',\'{3}\',\'{4}\')" onmouseout="PageShow.outEvent()">', detailTemp.KeyWordsTipContent, detailTemp.StartTime, detailTemp.EndTime, detailTemp.StartTimePercent - 5, i + 1));
                if (detailTemp.KeyWordsContent) {
                    htmlShow.push('<li class="v_list_normal" onmouseover="this.className=\'v_list_hover\'" onmouseout="this.className=\'v_list_normal\'" >');

                    htmlShow.push(String.format('<div class="numeral">{1}</div><div class="text">{0}', detailTemp.TitleContent, i + 1));
                    if (_type === 0) {
                        htmlShow.push(String.format('<p>{0}</p>', detailTemp.KeyWordsContent));
                    } else {
                        hangeWord = detailTemp.KeyWordsContent.replace(new RegExp(_keyWords, 'g'), String.format('<span class="redcolor">{0}</span>', _keyWords));

                        htmlShow.push(String.format('<p>{0}</p>', hangeWord));
                    }
                }
                else {
                    htmlShow.push('<li style="height:55px;" class="v_list_normal_1" onmouseover="this.className=\'v_list_hover_1\'" onmouseout="this.className=\'v_list_normal_1\'" >');
                    htmlShow.push(String.format('<div class="numeral" style="margin-top:10px;">{1}</div><div class="text">{0}', detailTemp.TitleContent, i + 1));
                }
                // if (detailTemp.KeyWordsContent)

                htmlShow.push(String.format('<p style="color: #aaaaaa;">{0}-{1}</p>', detailTemp.StartTime, detailTemp.EndTime));
                htmlShow.push(' </div></li></ul></div>');

                //隐藏内容
                if (autoPlayTime == detailTemp.StartSecondTime) {
                    if (detailTemp.KeyWordsContent)
                        htmlShow.push(String.format('<div class="v_list" id="hide_{0}" ', detailTemp.StartSecondTime));
                    else
                        htmlShow.push(String.format('<div class="v_list" id="hide_{0}" style="height:70px;" ', detailTemp.StartSecondTime));
                    this.clickId = i;
                } else {
                    if (detailTemp.KeyWordsContent)
                        htmlShow.push(String.format('<div class="v_list" id="hide_{0}" style="display:none" ', detailTemp.StartSecondTime));
                    else
                        htmlShow.push(String.format('<div class="v_list" id="hide_{0}" style="display:none;height:70px;" ', detailTemp.StartSecondTime));
                }
                htmlShow.push(String.format(' onclick="PageShow.rightVideoClick({0},this)" ucObj=1>', detailTemp.StartSecondTime));
                htmlShow.push(String.format('<ul onmouseover="PageShow.onmouseoverEvent(\'{0}\',\'{1}\',\'{2}\',\'{3}\',\'{4}\')" onmouseout="PageShow.outEvent()">', detailTemp.KeyWordsTipContent, detailTemp.StartTime, detailTemp.EndTime, detailTemp.StartTimePercent - 5, i + 1));
                if (detailTemp.KeyWordsContent) {
                    htmlShow.push('<li class="v_list_active" >');
                    htmlShow.push(String.format('<div class="numeral">{1}</div><div class="text">{0}', detailTemp.TitleContent, i + 1));
                    if (_type === 0) {
                        htmlShow.push(String.format('<p>{0}</p>', detailTemp.KeyWordsContent));
                    } else {
                        hangeWord = detailTemp.KeyWordsContent.replace(new RegExp(_keyWords, 'g'), String.format('<span class="redcolor">{0}</span>', _keyWords));

                        htmlShow.push(String.format('<p>{0}</p>', hangeWord));
                    }
                }
                else {
                    htmlShow.push('<li class="v_list_active_1" style="height:55px;" >');
                    htmlShow.push(String.format('<div class="numeral" style="margin-top:10px;">{1}</div><div class="text">{0}', detailTemp.TitleContent, i + 1));
                }
                htmlShow.push(String.format('<p style="color: #aaaaaa;">{0}-{1}</p>', detailTemp.StartTime, detailTemp.EndTime));
                htmlShow.push(' </div></li></ul></div>');
            }

            jQuery("#rolling").html(htmlShow.join(''));

        } catch (e) {
            alert(e.message);
        }
    },
    rightVideoClick: function (time, obj) {
        JumpToTime(time);
        this.changeCssStyle(obj);
    },

    changeCssStyle: function (obj) {
        var id = obj.id.toString();
        var idNum = id.substring(id.indexOf("_") + 1, id.length);
        if (this.clickId) {
            if (this.clickId == idNum) {
                return;
            } else {
                jQuery("#show_" + this.clickId).show();
                jQuery("#hide_" + this.clickId).hide();

                jQuery("#show_" + idNum).hide();
                jQuery("#hide_" + idNum).show();

                this.clickId = idNum;
            }
        } else {
            this.clickId = idNum;
            jQuery("#show_" + this.clickId).hide();
            jQuery("#hide_" + this.clickId).show();
        }
    },
    overEvent: function (content, startTime, endTime, posation, num) {
        var tipTop = "-90px";
        if (content.length < 34)
            tipTop = "-73px";

        jQuery("#tipDetail").show();
        jQuery("#tipDetail").css({
            "margin-top": tipTop,
            "margin-left": posation + "%"
        });

        var htmlShow = [];
        htmlShow.push('<div class="tip" ><div class="tip_top"><div class="t_left"></div><div class="t_cont"></div><div class="t_right"></div></div>');
        htmlShow.push(String.format('<div class="tip_content"><span style="font-size:14px">{3}、</span>{0}<p>{1}-{2}</p></div>', content, startTime, endTime, num));
        htmlShow.push('<div class="tip_bottom"><div class="b_left"></div><div class="b_cont"></div><div class="b_right"></div></div>');
        htmlShow.push('<div class="tip_arrow" id="tipPopBottom"></div></div>');
        jQuery("#tipDetail").html(htmlShow.join(''));
        jQuery("#tipPopBottom").css({
            "left": posation + "%"
        });
        // ChangeStyle.tipStyle(parseFloat(posation) + 5);
    },
    outEvent: function () {
        jQuery("#tipDetail").html("");
    },
    onmouseoverEvent: function (content, startTime, endTime, posation, num) {
        PageShow.overEvent(content, startTime, endTime, posation, num);
    },
    onmouseoutEvent: function (obj, type) {
        if (!_isClickEnd) {
            return;
        }
        jQuery(obj).removeClass();
        jQuery(obj).addClass("v_list_normal");
    }
}