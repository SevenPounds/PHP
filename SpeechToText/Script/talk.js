/// <reference path="../speechJS/LogUitl.js" />
/// <reference path="talkAchieve.js" />

var helpMsg = "";
//var recordServer = "http://gz.resource.openspeech.cn/audiodown.htm?key=";
var recorderIsReady;
var _intervalTime;
var _curentId;
var _playerState = 0; //播放器状态，0为初始状态，1为放音状态，2为录音状态，3为录音结束(读取结果中),4为播放器出错
var _microphoneState = true;//是否存在麦克风，默认存在
var _audioNameList = [];

var _recordStateType = false;
var _clickPosition;//点击的位置
var _flashAllowShowed = true;//

var RecogState =
{
    Normal: 1,
    Recording: 2,
    Recognizing: 3
};
var _speecher;
var _state = RecogState.Normal;
var _audioLength = 0;//音频总时长

function showData(data) {
    var content = " id=" + data.id + ",content=" + data.content + ",audioUrlId=" + data.audioUrlId;
    //alert(content);
    jQuery("#text_" + data.id).val(jQuery("#text_" + data.id).val() + data.content);
}

/*
*入口函数，调用内部方法即可，调用后注释该函数
*/
//jQuery(document).ready(function () {
//    LogUtil.reload({ showPanel: false, showConsole: true });
//    loadSTT(5000, "talk");
//    loadSTT(5000, "Div1");
//    loadSTT(5000, "Div2");
//    loadSTT(5000, "Div3");
//    loadSTT(5000, "Div4");
//    loadSTT(5000, "Div5");
//    STTRecordPlay.SetRecordEndEvent(showData);
//    AudioPlayer.init();
//});

var _speechDoc;

function loadSTT(IntervalTime, containId) {
    if (_speechDoc == undefined) {
        _speechDoc = new SpeechLoadDoc(IntervalTime, containId);
    }
    jQuery("#text_" + containId).val(helpMsg);
    ShowTalk.showContent(containId);

}

/*
*判断录音状态
*/
function setState(state, speechResultId) {
    /// <param name="speechResultId" type="String">结果text id</param>
    if (state == RecogState.Normal) {
        CilckEvents.talkType = 0;
        jQuery("#content_Img" + speechResultId).html('点击麦克风开始说话');
        setEnergy(0);
    }
    //else if (state == RecogState.Recording) {
    //    jQuery("#text_" + speechResultId).val("");
    //}
}


/*
*设置容器初始化
*/
function Setinitial(containId) {
    jQuery(".content_Img").html('Recorder is ready...');
    if (_microphoneState) {
        jQuery(".content_Img").html('点击麦克风开始说话');
    }
    else {
        jQuery(".content_Img").html("没有检测到麦克风！");
    }

};

/**
* 设置能量条
* @param {Number} 音频能量值(0-100)
*/
function setEnergy(energy) {
    if (energy < 0)
        energy = 0;
    if (energy > 100)
        energy = 100;
    //setTimeout(function () {
    jQuery(".voice_box_02").css("width", energy + "%");
    //}, 20);
}

/**
* 初始化文语转写,需在页面加载的load函数里面添加（页面的入口函数）
* @param {IntervalTime} 间隔时间毫秒（500-30000）
* @param {containId} 引擎返回后的结果写入容器（500-30000）
* @param {fnName} 提供外部函数参，默认不传
*/
function SpeechLoadDoc(IntervalTime, containId) {

    var _currentSpeechResultId;
    var _AudioName;
    var _key;
    _intervalTime = IntervalTime;
    LogUtil.reload({ top: 20, left: 790, showPanel: false, showConsole: true });

    // 不加载插件
    if (SpeechRecorderSettings && SpeechRecorderSettings.Plugins) {
        delete SpeechRecorderSettings.Plugins;
    }

    var swfUrl = SpeechRecorderSettings.Root + "SpeechRecorderIAT.swf";
    if (SpeechRecorderSettings.IsDebug === true) {
        swfUrl += "?r=" + Math.random();
    }
    var recordId = "recorder-panel";
    var recordOptios = {
        swf: swfUrl,
        width: "100%",
        height: "100%",
        expressInstall: SpeechRecorderSettings.Root + "expressInstall.swf",
        flashVars: {
            skin: SpeechRecorderSettings.Root + "Skins/sound.swf",
            //loadType: LoadType.HTML,
            recorderId: recordId,
            enableWebLog: SpeechRecorderSettings.EnableWebLog,
            mspServer: SpeechRecorderSettings.MspServer,
            logLvl: SpeechRecorderSettings.LogLvl,
            mscLogLvl: SpeechRecorderSettings.MscLogLvl
        }
    };

    _recorder = new SpeechRecorder(recordId, recordOptios);
    _recorder.addEventListener(SpeechRecorderEvent.READY, onReady);
    _recorder.addEventListener(SpeechRecorderEvent.ERROR, onError);
    _recorder.addEventListener(SpeechRecorderEvent.RECORDING, onRecording);
    _recorder.addEventListener(SpeechRecorderEvent.MICROPHONE_STATE, onMicphoneState);
    _recorder.addEventListener(SpeechRecorderEvent.IAT_COMPLETE, onIATComplete);
    _recorder.addEventListener(SpeechRecorderEvent.IAT_RESULT, onIATResult);


    _recorder.addEventListener(SpeechRecorderEvent.DOWNLOAD_STATE, onDownloadState);
    _recorder.addEventListener(SpeechRecorderEvent.AUDIO_LOADED, onAudioLoaded);
    _recorder.addEventListener(SpeechRecorderEvent.PLAY_STATE, onPlayState);
    _recorder.addEventListener(SpeechRecorderEvent.PLAYING, onPlaying);

    function onReady() {
        setTimeout(function () {
            /// 
            //vadSpeechTail(IntervalTime);
            //  enableIAT(_intervalTime);
            _recorder.enableIAT(_intervalTime);
            recorderIsReady = true;
            _playerState = 0;//设置播放器状态为初始状态
            Setinitial(containId);
            LogUtil.info("Recorder is ready...");
        }, 1000);
    }
    function onError(e) {
        LogUtil.error("onError:" + jQuery.toJSON(e));
        if (_currentSpeechResultId) {
            CilckEvents.hideTalk(_currentSpeechResultId);
        }
        _playerState = 4;
    };

    function onRecording(e) {
        LogUtil.debug("onRecording:" + jQuery.toJSON(e));
        setEnergy(e.energy);
        _playerState = 2;
    }

   
    function onIATResult(e) {
        LogUtil.info("onIATResult:" + jQuery.toJSON(e));
        var recordRes = [];
        recordRes["id"] = _currentSpeechResultId;
        recordRes["content"] = e.result;
        recordRes["audioUrlId"] = _AudioName + ".wav";
        STTRecordPlay.RecordEnd(recordRes);
        _playerState = 3;
    }

    function onIATComplete(e) {
        LogUtil.info("onIATComplete:" + jQuery.toJSON(e));
        //_key = e.key;
		//解析后台传来的json
		var tempKey = e.key;
		tempKey = tempKey.replace(/\"/g,"\"");
		if(tempKey.indexOf("code") != -1){
			tempKey = jQuery.parseJSON(tempKey);
			if(1 == tempKey.code){
				_key = tempKey.data;
				STTRecordPlay.CompleteEnd(_key);   //by frsun 2014.2.12
			}
			
		}
        setState(RecogState.Normal, _currentSpeechResultId);
        _playerState = 0;
    };

    function onMicphoneState(status) {
        //if (_recordStateType == false)
        //    return;
        // 没有检测到麦克风

        //前面已经展示了一次安全提示框，所有没有必要再次展示 xlzhou2 2013-7-18 10:02:41
        //if (status === MicrophoneState.Denied) {
        //    _flashAllowShowed = false;
        //}

        if (status === MicrophoneState.NotFound) {
            LogUtil.warn("没有检测到麦克风...");
            _microphoneState = false;
            hideSecuritySettings();
//            alert("没有检测到麦克风...");
        }
            // 用户拒绝对麦克风访问
            //modify by xlzhou2 2013-7-18 10:07:05
        else if (status === MicrophoneState.Denied) {
            LogUtil.warn("请允许使用麦克风！");
            if (_flashAllowShowed) {
                //showSecuritySettings();
            	hideSecuritySettings();  // modify by frsun 2014.1.8
                _flashAllowShowed = false;
                _recordStateType = true;
            } else {
                hideSecuritySettings();
                _recordStateType = false;
            }
        }
            // 用户允许对麦克风访问
        else if (status === MicrophoneState.Allowed) {
            LogUtil.info("allow micphone access...");
            _recordStateType = true;
            hideSecuritySettings();
        }
        else if (status == MicrophoneState.NoEnoughSize) {
            LogUtil.error("没有足够空间显示安全设置面板");
        }
    }

    function onDownloadState(e) {
        if (e.ErrorCode == 0) {
            LogUtil.debug("onDownloadState:" + jQuery.toJSON(e));
        } else if (e.ErrorCode == 9999) {
            LogUtil.error("onDownloadState:" + jQuery.toJSON(e));
            alert("音频资源加载失败，请刷新后重试！");
        }
        else {
            LogUtil.error("onDownloadState:" + jQuery.toJSON(e));
        }
    }

    function onAudioLoaded(e) {
        LogUtil.debug("onAudioLoaded:" + jQuery.toJSON(e));
        //获取音频总长度
        _audioLength = e.audioLength;
    }

    function onPlaying(e) {
        LogUtil.debug("onAudioLoaded:" + jQuery.toJSON(e));

        //获取当前进度
        percent = e.position / _audioLength;
        AudioPlayer.playProgress(e.position, _audioLength, _currentSpeechResultId);
    }

    function onPlayState(e) {
        if (e.newstate === "PLAYING" && e.oldstate !== "PLAYING") {
            LogUtil.info("Play Begin");
            _playerState = 1;
        } else if (e.newstate === "PAUSE" && e.oldstate === "PLAYING") {
            LogUtil.info("Play Pause");
            AudioPlayer.pause();
        } else if (e.newstate === "IDLE" && e.oldstate !== "IDLE") {
            LogUtil.info("Play Stop");
            _playerState = 0;
            AudioPlayer.playEnd();
        }
    }


    function showSecuritySettings() {
    	  _flashAllowShowed = true;
          var top = getSecurityPosition();
        jQuery("#recorder-outter-panel").css({
            width: '215px',
            height: '138px',
            position: 'absolute',
            left: '180px',
            top: top + 20,
            "z-index": 9999
        });

        jQuery("#mask").show();

        setTimeout(function () {
            _recorder.showSecuritySettings();
        }, 0);
    }

    //add by xlzhou2 2013-7-18 10:06:22 
    function getSecurityPosition() {
        ///<summary>获取弹出安全提示框的位置</summary>
        var height = window.screenTop;
        var scrollTop = document.documentElement.scrollTop
        var cli = window.screen.availHeight;//可见区域高度
        var top = 0;
        if (height == undefined)
            top = scrollTop + 30;
        else
            top = height + scrollTop + 30;
        return top;
    }
    
    function hideSecuritySettings() {
        jQuery("#recorder-outter-panel").removeAttr("style");
        jQuery("#mask").hide();
    }

    function showFlash() {
        jQuery("#recorder-outter-panel").css({
            width: "260px",
            height: "180px"
        });
    }

    function switchPanel(id, audioName) {
        _currentSpeechResultId = id;
        _AudioName = audioName;
    }

    return {
        switchPanel: switchPanel,
        showSecuritySettings:showSecuritySettings
    }
}

//录音器--STTRecordPlay
var STTRecordPlay = new Object;

/**
* 自定义的录音结果处理函数
* @param {obj.id}         录音控件的ID
* @param {obj.content}    录音结果
* @param {obj.audioUrlId} 录音文件名（含后缀名）
*/
STTRecordPlay.RecordEnd = function (obj) {
    jQuery("#" + obj.id).val(jQuery("#" + obj.id).val() + obj.content + ",id=" + obj.audioUrlId);
}

/**
 * 自定义的录音完成处理函数
 * author  frsun  2014.4.12
 * @param key 
 */
STTRecordPlay.CompleteEnd = function(key){
	alert(key);
}

/**
* 自定义清除结果框内容函数
* @param {containId}         对应的录音控件的ID
*/
STTRecordPlay.SetSpeechResult = function (containId) {
    jQuery("#text_" + containId).val("");
}

/*
* 播放器的初始化,需在页面加载的load函数里面添加（页面的入口函数）
* @param {events}该参数为一个方法，该方法参数为获取的结果类
*
*/
STTRecordPlay.SetRecordEndEvent = function (events, SetSpeechResult, completeFun) {
    if (events != undefined) {
        STTRecordPlay.RecordEnd = events;
    }
    if(completeFun != undefined){
    	STTRecordPlay.CompleteEnd = completeFun;
    }    
    if (SetSpeechResult != undefined) {
        STTRecordPlay.SetSpeechResult = SetSpeechResult;
    }
}


//播放器--AudioPlayer
AudioPlayer = new Object;

/**
* 播放器的初始化,需在页面加载的load函数里面添加（页面的入口函数）
* @param {playProgresBack}该参数为一个方法，该方法的第一个参数为进度，可为空
* @param {playEndBack} 播放结束的回调方法，可为空
* @param {SetProgressbar} 设置进度条的方法，可为空
*/
AudioPlayer.init = function (playProgresBack, playEndBack, SetProgressbar) {
    if (playProgresBack != undefined) {
        AudioPlayer.playEnd = playEndBack;
    }
    if (playEndBack != undefined) {
        AudioPlayer.playProgres = playProgresBack;
    }
    if (SetProgressbar != undefined) {
        AudioPlayer.SetProgressbar = SetProgressbar;
    }
}

//播放结束，可使用初始化函数(init)重写该方法
AudioPlayer.playEnd = function () {
    //this.SetProgressbar(0, AudioPlayer.currentProgressbarborderID, AudioPlayer.currentProgressbarID);
    //this.SetProgressbar(1, this.currentProgressbarborderID, this.currentProgressbarID);

    setTimeout(function () {
        AudioPlayer.playProgress(0, 200, "");
    }, 200);
}

//播放过程中获取进度，可使用初始化函数(init)重写该方法
AudioPlayer.playProgress = function (position, audioLength, _currentSpeechResultId) {
    var progress = position / audioLength;
    var date = new Date(position);
    var minutes = date.getMinutes() > 9 ? date.getMinutes() : "0" + date.getMinutes();
    var seconds = date.getSeconds() > 9 ? date.getSeconds() : "0" + date.getSeconds();
    jQuery("#time_" + this.currentProgressbarID).html(minutes + ":" + seconds);
    var t = audioLength - position;
    if (t < 100) {
        progress = 1;
    }
    this.SetProgressbar(progress, this.currentProgressbarborderID, this.currentProgressbarID);
}

AudioPlayer.pause = function () {
    //alert("暂停播放");
}

AudioPlayer.stop = function () {
	_recorder.stopAudio();
    //alert("停止播放");
}

//开始播放
AudioPlayer.audioplay = function (recordID, currentProgressbarborderID, currentProgressbarID) {
    if (this.currentProgressbarborderID != "" && this.currentProgressbarID != "") {
        this.SetProgressbar(0, this.currentProgressbarborderID, this.currentProgressbarID);
        AudioPlayer.playProgress(0, 200, "");
    }
    _recorder.stopAudio();
    this.currentProgressbarborderID = currentProgressbarborderID;
    this.currentProgressbarID = currentProgressbarID;
    var url = SpeechRecorderSettings.RecordServer + recordID;
    _recorder.loadAudio(url, true)
    //_recorder.loadAudio("http://60.166.12.151:8080/Audio1367118866661.wav", true)
};

/*
* 设置进度条
* @param {percent} 当前播放进度
* @param {currentProgressbarborderID} 当前播放的音频对应的进度条边框ID
* @param {currentProgressbarID} 当前播放的音频对应的进度条ID
*/
AudioPlayer.SetProgressbar = function (percent, currentProgressbarborderID, currentProgressbarID) {
    try{
    	var width = jQuery("#initial_" + currentProgressbarID).css("width");
        width = parseFloat(width.replace("px", ""));
        var currentwidth = width * percent;
        jQuery("#" + currentProgressbarID).css({ width: currentwidth });
    }catch(e){
    	
    }
}

AudioPlayer.currentProgressbarborderID = "";
AudioPlayer.currentProgressbarID = "";
