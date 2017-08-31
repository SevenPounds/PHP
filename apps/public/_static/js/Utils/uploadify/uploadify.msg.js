

var uploadify_onSelectError = function(file, errorCode, errorMsg) {
        switch (errorCode) {
            case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
                this.queueData.errorMsg = "每次最多上传 " + this.settings.queueSizeLimit + "个文件";
                break;
            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
            	this.queueData.errorMsg = "文件大小超过限制( " + this.settings.fileSizeLimit + " )";
                break;
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            	this.queueData.errorMsg = "文件大小为0";
                break;
            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            	this.queueData.errorMsg = "文件格式不正确，仅限 " + this.settings.fileTypeExts;
                break;
            default:
            	this.queueData.errorMsg = "错误代码：" + errorCode + "\n" + errorMsg;
        }
    };
 
var uploadify_onUploadError = function(file, errorCode, errorMsg, errorString) {
        // 手工取消不弹出提示
        if (errorCode == SWFUpload.UPLOAD_ERROR.FILE_CANCELLED
           || errorCode == SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED) {
            return;
        }
        switch (errorCode) {
            case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
            	this.queueData.errorMsg = "HTTP 错误\n" + errorMsg;
                break;
            case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
            	this.queueData.errorMsg = "上传文件丢失，请重新上传";
                break;
            case SWFUpload.UPLOAD_ERROR.IO_ERROR:
            	this.queueData.errorMsg = "IO错误";
                break;
            case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
            	this.queueData.errorMsg = "安全性错误\n" + errorMsg;
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
            	this.queueData.errorMsg = "每次最多上传 " + this.settings.uploadLimit + "个";
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
            	this.queueData.errorMsg = errorMsg;
                break;
            case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
            	this.queueData.errorMsg = "找不到指定文件，请重新操作";
                break;
            case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
            	this.queueData.errorMsg = "文件被取消";
            	break;
            case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
            	this.queueData.errorMsg = "参数错误";
                break;
            default:
            	this.queueData.errorMsg = "文件:" + file.name + "\n错误码:" + errorCode + "\n"
                        + errorMsg + "\n" + errorString;
        }
         
    }



//var uploadify_config = {
//    'uploader' : 'upload.php',
//    'swf' : '/js/uploadify/uploadify.swf',
//    'buttonImage' : '/images/uploadify-button.png',
//    'cancelImg' : '/images/uploadify-cancel.png',
//    'wmode' : 'transparent',
//    'removeTimeout' : 0,
//    'width' : 80,
//    'height' : 30,
//    'multi' : false,
//    'auto' : true,
//    'buttonText' : '上传',
//    'hideButton' : 'true',
//    'fileTypeExts' : '*.png;*.jpg;*.jpeg',
//    'fileSizeLimit' : '1MB',
//    'fileTypeDesc' : 'Image Files',
//    'formData' : {"action": "upload", "sid" : ""},
//    'overrideEvents' : [ 'onDialogClose', 'onUploadSuccess', 'onUploadError', 'onSelectError' ],
//    'onSelect' : uploadify_onSelect,
//    'onSelectError' : uploadify_onSelectError,
//    'onUploadError' : uploadify_onUploadError,
//    'onUploadSuccess' : uploadify_onUploadSuccess
//};
// 
//$("#id").uploadify(uploadify_config);