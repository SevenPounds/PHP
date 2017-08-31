/*
* 文件名: Settings.js
* 描述: SpeechRecorder 初始化配置文件
*/

SpeechRecorderSettings = {
    // 是否是调试状态
    IsDebug: false,
    
    // SpeechRecorder根目录
    Root: "videoretrieval/Scripts/",
    
    // 是否启用WebConsole日志
    EnableWebLog: true,
    
    // 云服务地址
    MspServer: "gz.voicecloud.cn:80",

    // 应用日志等级
    LogLvl: 0,
    
    // msc日志等级
    MscLogLvl:15,
    
    // 试音阀值
    AudioCheckThreshold:"15,35",

    // 插件列表,插件间用;分割，插件名和插件url用|分隔，形式为:( 插件名|插件url;插件名|插件名url )
    Plugins: "AudioChecker|Scripts/Plugins/AudioChecker.swf",
	
	//语音文件下载地址
	RecordServer: "http://szedu.changyan.cn/ercsvc-upload/upload/voice/",
	
	//语音上传地址
	UploadServer: "http://szedu.changyan.cn/ercsvc-upload/uploadVoice"
}