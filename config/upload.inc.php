<?php
/**
 * @author yuliu2@iflytek.com
 * 云平台统一资源上传配置文件
 */
return array(
	/**
	 * 资源中心可上传资源，必须是可预览资源
	 * *.html,*.html已经不支持，安全性存在问题
	 */
	'previewable_exts' => array(
			'*.icr','*.icw','*.doc','*.docx','*.xls','*.xlsx','*.ppt','*.pptx','*.pps','*.ppsx','*.txt','*.rtf',
			'*.pdf','*.swf',
			'*.jpg','*.jpeg','*.bmp','*.png','*.gif',
			'*.mp4','*.3gp','*.asf','*.avi','*.rmvb','*.mpeg','*.wmv','*.rm','*.mpeg4','*.mov','*.flv','*.vob','*.mkv',
			'*.mp3','*.wma','*.wav','*.ogg','*.ape','*.mid','*.midi','*.rar','*.zip'
	),
	'video_exts'       => array(
			'*.mp4','*.3gp','*.asf','*.avi','*.rmvb','*.mpeg','*.wmv','*.rm','*.mpeg4','*.mov','*.flv','*.vob','*.mkv'
	)
);
?>