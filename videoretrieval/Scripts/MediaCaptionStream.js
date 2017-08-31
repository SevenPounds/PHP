/// <reference path="Common.js" />
/// <reference path="MediaCaption.js" />

/**
* @author xlzhou2
* 2013-04-10
*/

/*********************************************************************
   * description : 入口函数
   *********************************************************************/
var _meideaUrl;
//jQuery(function () {
//    //先创建DIV
//    //在DOC对象创建之前调用，
//    //CreateDiv.createDiv("test2", "http://localhost:1857/Resources/math.flv", Success, ErrorDataBack);
//    jQuery(ajax({
//        type: "GET", url: "Services/AppHandler.ashx", data: { action: "GetMediaDetail", id: "test2", keyWords: _keyWords }, timeout: 25000, //超时时间：10秒
//        error: function (XMLHttpRequest, textStatus, errorThrown) {
//            return;
//        },
//        success: function (data) {
//            if (data) {
//                showData(data);
//            } else
//                return;
//        }
//    });
//    //showData(data);
//})

function showData(data) {
    try {
        if (data) {
            resultData = eval('(' + data + ')');

            CreateDiv.createDiv("", resultData.MediaUrl, Success, ErrorDataBack);
        } else
            return -1;
    } catch (e) {
        return -2;
    }

}
function videoLoad() {
    jQuery("#jquery_jplayer_1").jPlayer("load");
    //VideoPlay("");
    //VideoPlay("http://localhost:1855/Resources/ocean.flv");
    VideoPlay(_meideaUrl);
    setTimeout(function () {
        jQuery("#jquery_jplayer_1").jPlayer("play");
        jQuery("#jquery_jplayer_1").jPlayer("pause");
        jQuery(".jp-video-play").show();
        playState = 0;
    }, 500);
}
/*********************************************************************
   * description : 数据获取成功后，后续操作
   *********************************************************************/
function SuccessDataBack() {
    if (_type == 0) {
        ShowContent.show_vd_left(resultData);
        ShowContent.showVideo();
        ShowContent.show_vd_right(resultData);
        videoLoad();

    } else
        ShowContent.show_rightContent(resultData);
}

/*********************************************************************
   * description : 全局异常
   *********************************************************************/
function ErrorDataBack() {
    //alert(Error);
    return;
}

/*********************************************************************
   * description :DIV创建成功后后续操作
   *********************************************************************/
function Success() {
    CreateDiv.appendDiv();
    SuccessDataBack();
}

/*********************************************************************
   * description : 创建DIV
   *********************************************************************/
var CreateDiv = {
    /*创建DIV
    *param: flvId 播放视频的ID
    *param: flvUrl 播放视频的地址
    *param:Success 回调成功方法名
    *param:ErrorDataBack 回调失败方法名
    */
    createDiv: function (flvId, flvUrl, Success, ErrorDataBack) {
        _videoId = flvId;
        _meideaUrl = flvUrl;
        try {
            var nodeDiv = document.createElement("div");
            nodeDiv.id = "video_page";
            var apendId = document.getElementById("video_page");
            apendId.appendChild(nodeDiv);
            Success();
        } catch (e) {
            ErrorDataBack(e.message.toString());
            return;
        }
    },
    //添加页面DIV框架
    appendDiv: function () {
        var htmlShow = [];
        htmlShow.push('<div id="vd_box"><div class="vd_left" id="videoLeft"></div><div class="vd_right" id="videoRight">');
        htmlShow.push(' <div class="vd_right_title" id="videoRightTitle"></div><div id="rolling"></div>');
        htmlShow.push('</div><div class="clear"></div></div>');
        jQuery("#video_page").html(htmlShow.join(''));
    }
}

