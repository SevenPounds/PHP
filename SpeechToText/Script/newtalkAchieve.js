/// <reference path="../speechJS/LogUitl.js" />
/// <reference path="talk.js" />

var ShowTalk = {
    /*
    *展示控件内容
    *param：containId 容器的id，可以是DIV等
    */
    showContent: function (containId) {
        var htmlShow = [];
        htmlShow.push('<div class="voice_b"><div class="voice_mic">');
        htmlShow.push(String.format('<img id="speech_botton{0}" class="hand" src="SpeechToText/images/mic_normal.png" onmouseover="this.src=\'SpeechToText/images/mic_hover.png\'" ', containId));
        htmlShow.push(String.format('  onmouseout="this.src=\'SpeechToText/images/mic_normal.png\'" onclick="CilckEvents.talkClick(\'{0}\')" /></div>', containId));
        htmlShow.push(String.format('<div class="content_Img" id="content_Img{0}">初始化中...', containId));
        //htmlShow.push('<div class="voice_box"><div class="voice_box_v"><div class="voice_box_01"><div class="voice_box_02"></div></div></div>');
        //htmlShow.push('<div class="voice_box_r"><span class="bluecolor pt"><a href="#">说完了</a></span><span class="bluecolor"><a href="#">取消</a></span></div></div>');
        htmlShow.push('</div><div class="clear"></div></div>');
        htmlShow.push('<div>');
        //htmlShow.push('<div id="progress-bar-border' + containId + '" class="progress-bar-border"><div id="progress-bar' + containId + '" class="progress-bar"></div></div>');
        //htmlShow.push(String.format('<input class="audioPlay" id="audioPlay_' + containId + '" type="button" value="播放" onclick="CilckEvents.audioPlay(\'{0}\')"/></div>', containId));
        jQuery("#" + containId).html(htmlShow.join(''));
    }
}

var CilckEvents = {
    //talkType: 0,//0 初始化状态, 1为放音状态,2为录音状态，3为录音结束
    talkClick: function (containId, obj) {
        // modify by xlzhou2 2013-7-18 10:05:21 start
        if (_recordStateType === false) {
            _recordStateType = true;
            _speechDoc.showSecuritySettings();
            _flashAllowShowed = false;
            return;
        }
        
        //modify by frsun 2014.1.8 在点击按钮时太显示flash安全设置
        if(_flashAllowShowed===false){
        	_speechDoc.showSecuritySettings();
        	_flashAllowShowed = true;
        	return;
        }
       
        //modify End
        if (_microphoneState && _playerState == 0) {
        	STTRecordPlay.SetSpeechResult(containId);
            _audioNameList[containId] = [];
            _audioNameList[containId].audioName = "Audio" + new Date().getTime();
            _playerState = 2;
            divcontainId = "content_Img" + containId;
            _speechDoc.switchPanel(containId, _audioNameList[containId].audioName);
            this.startRecord(containId, divcontainId);
        }
    },
    //点击“说完了”事件
    talkEndClick: function (containId) {
        //if (_playerState == 2) {
        //    this.talkCancelClick(containId);
        //}
        jQuery("#wait_result").show();
        jQuery("#talk_finish").hide();
        _recorder.endRecognize();
    },
    //点击取消事件
    talkCancelClick: function (containId) {
        //if (_playerState == 2) { 
            //this.talkType = 0;
            jQuery("#content_Img" + containId).html('点击麦克风开始说话');
            _recorder.abortRecognize();
        //}
    },
    hideTalk: function (containId) {
        //this.talkType = 0;
        jQuery("#content_Img" + containId).html('点击麦克风开始说话');
        _recorder.abortRecognize();
    },
    startRecord: function (containId, divcontainId) {
        //开始录音
        if (recorderIsReady) {
            var recogParam = { vadSpeechTail: _intervalTime, sndId: _audioNameList[containId].audioName, uploadIatUrl: SpeechRecorderSettings.UploadServer};
            //this.talkType = 2;
            _recorder.beginRecognize(recogParam);
            setState(RecogState.Recording, containId);
            /*var htmlShow = [];
            htmlShow.push('<div class="voice_box"><div class="voice_box_v"><div class="voice_box_01"><div class="voice_box_02"></div>');
            htmlShow.push('</div></div><div class="voice_box_r"><span class="bluecolor"><a onclick="CilckEvents.talkEndClick(\'' + containId + '\')" id="talk_finish" style="text-decoration:none;cursor:pointer;font-size:12px;" >说完了</a>');
            htmlShow.push('<a id="wait_result" style="display:none;font-size:13px;text-decoration:none;" >请等待</a></span>');
            htmlShow.push('&nbsp;&nbsp;<span class="bluecolor"><a onclick="CilckEvents.talkCancelClick(\'' + containId + '\')" style="text-decoration:none;cursor:pointer;font-size:12px;">取消</a></span></div></div>');
            jQuery("#" + divcontainId).html(htmlShow.join(''));*/
        } else {
            //this.talkType = 0;
            LogUtil.warn("Recorder is not ready..");
            /*jQuery("#content_Img" + containId).html('Recorder is not ready..');*/
            return;
        }
    }
}
