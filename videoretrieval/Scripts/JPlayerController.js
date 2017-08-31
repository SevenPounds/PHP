/// <reference path="VideoRetrieval.js" />
/// <reference path="MediaCaption.js" />

var playState = 0;
//指定时间点的播放
function JumpToTime(seconds) {
    //seconds = 12;
	if(seconds == 0){
		seconds=1;
	}

    //_playstatus = 1;
    //jQuery(".jp-video-play").hide();
    //jQuery("#jquery_jplayer_1").jPlayer("play", seconds); // Begins playing 42 seconds into the media.
    JP_play(seconds);
    //setTimeout(function () {
    //    jQuery("#jquery_jplayer_1").jPlayer("play", seconds);
    //}, 200);
    //jQuery(".jp-video-play").hide();
    //_playstatus = 1;
    //clearInterval(interTimer);
    //setTimeout(function () {
    //    _playstatus = 1;
    //    jQuery(".jp-video-play").hide();
    //    fn_setrelatedtextcolor();
    //    interTimer = setInterval(function () {
    //        fn_setrelatedtextcolor();
    //    }, 300);
    //}, 250)
}


function VideoPlay(videoPath) {
    jQuery("#jquery_jplayer_1").jPlayer({
        ready: function () {
            jQuery(this).jPlayer("setMedia", {
                flv: videoPath
            })
        },
        ended: function () { // The jQuery.jPlayer.event.ended event  
            jQuery(this).jPlayer("stop"); // Repeat the media 
            //结束事件
            //   stopEvent();
        },

        swfPath: "videoretrieval/Jplayer/js",
        supplied: "flv",
        //autohide: {
        //    full: false
        //},
        size: {
            width: "400px",
            height: "300px",
            cssClass: "jp-video-360p",
            solution: "flash, html"
        }
    });
}

function SetMediaPath(path) {
    jQuery("#jquery_jplayer_1").jPlayer("setMedia", { flv: path });
}

function StopMedia() {
    jQuery("#jquery_jplayer_1").jPlayer("stop");
}

function JP_play(times) {
    setTimeout(function () {
        playState = 1;
        jQuery(".jp-video-play").hide();
        if (times)
            jQuery("#jquery_jplayer_1").jPlayer("play", times);
        else
            jQuery("#jquery_jplayer_1").jPlayer("play");
    }, 200);
}

function JP_pause() {
    setTimeout(function () {
        playState = 0;
        jQuery("#jquery_jplayer_1").jPlayer("pause");
    }, 200);
}
