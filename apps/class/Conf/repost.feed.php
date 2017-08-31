<feed app='class' type='repost' info='转发微博'>
	<title> 
		<![CDATA[{$actor}]]>
	</title>
	<body>
		<![CDATA[
		<eq name='body' value=''> 微博分享 </eq> 
		{$body|t|replaceUrl}
		<dl class="comment">
			<dt class="arrow bgcolor_arrow"><em class="arrline">◆</em><span class="downline">◆</span></dt>
			<php>if($sourceInfo['is_del'] == 0 && $sourceInfo['source_user_info'] != false):</php>
			<dd class="name">
				<a href='{$sourceInfo.source_user_info.space_url}' class='name' event-node='face_card' orgType='{$sourceInfo.source_user_info.type}' cid='{$sourceInfo.source_user_info.cid}'>{$sourceInfo.source_user_info.uname}</a>
			</dd>
			<dd>
				{* 转发原文 *}
				{$sourceInfo.source_content|t|replaceUrl}
				<php>if(!empty($sourceInfo['record_id'])):</php>
				    <div class="repost_sound">
				        <div class="single_sound_btn" onclick="AudioPlayer.audioplay('{$sourceInfo['record_id']}', 'progress-bar-bordertalk-{$feedid}', 'progress-bartalk-{$feedid}')">
				            <img onmouseover="this.src='SpeechToText/images/sound12.jpg'" onmouseout="this.src='SpeechToText/images/sound11.jpg'" src="SpeechToText/images/sound11.jpg" />
				        </div>
				        <div id="progress-bar-bordertalk-{$feedid}" class="repost-bar-border">
				            <img id="initial_progress-bartalk-{$feedid}" class="initial-progress-bar" src="SpeechToText/images/sound_bg1.jpg" />
				            <span id="time_progress-bartalk-{$feedid}" style="color:#888;">00:00</span>
				            <img id="progress-bartalk-{$feedid}" class="record_progress-bar" src="SpeechToText/images/sound_bg2.jpg" />
				        </div>
				    </div>
				<php>endif;</php>
				<php>if(!empty($sourceInfo['attach'])):</php>
				{* 附件微博 *}
				<eq name='sourceInfo.feedType' value='postfile'>
				<ul class="feed_file_list">
					<volist name='sourceInfo.attach' id='vo'>
					<li>
						<a href="{:U('widget/Upload/down',array('attach_id'=>$vo['attach_id']))}" class="current right" target="_blank"><i class="ico-down"></i></a>
						<i class="ico-{$vo.extension}-small"></i>
						<a href="{:U('widget/Upload/down',array('attach_id'=>$vo['attach_id']))}">{$vo.attach_name}</a>
						<span class="tips">({$vo.size|byte_format})</span>
					</li>
					</volist>			
				</ul>		
				</eq>
				{* 图片微博 *}
				<eq name='sourceInfo.feedType' value='postimage'>
				<div class="feed_img_lists" rel='small' >
					<ul class="small">
						<volist name='sourceInfo.attach' id='vo'>
						<li><a href="javascript:void(0)" event-node='img_small'><img class="imgicon" src='{$vo.attach_small}' title='点击放大' width="100" height="100"></a></li>
						</volist>
					</ul>
				</div>
				<div class="feed_img_lists" rel='big' style='display:none'>
					<ul class="feed_img_list big">
						<span class='tools'><a href="javascript:void(0);" event-node='img_big' class="ico-pack-up">收起</a></span>
						<volist name='sourceInfo.attach' id='vo'>
						<li title='{$vo.attach_url}'>
							<a href='{$vo.attach_url}' target="_blank" class="ico-show-big" title="查看大图" ></a>
							<a href="javascript:void(0)" event-node='img_big'><img class="imgsmall" src='{$vo.attach_middle}' title='点击缩小' /></a>
						</li>
						</volist>
					</ul>
				</div>
				</eq>
				{* 分享图片微博 *}
				<eq name='sourceInfo.feedType' value='share'>
				<div class="feed_img_lists" rel='small' >
					<ul class="small">
						<volist name='sourceInfo.attach' id='vo'>
						<li><a href="javascript:void(0)" event-node='img_small'><img class="imgicon" src='{$vo.attach_small}' title='点击放大' width="100" height="100"></a></li>
						</volist>
					</ul>
				</div>
				<div class="feed_img_lists" rel='big' style='display:none'>
					<ul class="feed_img_list big">
						<span class='tools'><a href="javascript:void(0);" event-node='img_big' class="ico-pack-up">收起</a></span>
						<volist name='sourceInfo.attach' id='vo'>
						<li title='{$vo.attach_url}'>
							<a href='{$vo.attach_url}' target="_blank" class="ico-show-big" title="查看大图" ></a>
							<a href="javascript:void(0)" event-node='img_big'><img class="imgsmall" src='{$vo.attach_middle}' title='点击缩小' /></a>
						</li>
						</volist>
					</ul>
				</div>
				</eq>
				<php>endif;</php>
				{* 视频微博 *}
				<eq name='sourceInfo.feedType' value='postvideo'>
				<div class="feed_img" id="video_mini_show_{$feedid}">
				  <a href="javascript:void(0);" onclick="switchVideo({$feedid},'open','{$sourceInfo.host}','{$sourceInfo.flashvar}')">
				    <img src="{$sourceInfo.flashimg}" style="width:150px;height:113px;overflow:hidden" />
				  </a>
				  <div class="video_play" ><a href="javascript:void(0);" onclick="switchVideo({$feedid},'open','{$sourceInfo.host}','{$sourceInfo.flashvar}')">
				      <img src="__THEME__/image/feedvideoplay.gif" ></a>
				  </div>
				</div>
				<div class="feed_quote" style="display:none;" id="video_show_{$feedid}"> 
				  <div class="q_tit">
				    <img class="q_tit_l" onclick="switchVideo({$feedid},'open','{$sourceInfo.host}','{$sourceInfo.flashvar}')" src="__THEME__/image/zw_img.gif" />
				  </div>
				  <div class="q_con"> 
				    <p style="margin:0;margin-bottom:5px" class="cGray2 f12">
				    <a href="javascript:void(0)" onclick="switchVideo({$feedid},'close')" class="ico-pack-up">收起</a>
				    &nbsp;&nbsp;|&nbsp;&nbsp;
				    <a href="{$sourceInfo.source}" target="_blank">
				      <i class="ico-show-all"></i>{$sourceInfo.title}</a>
				    </p>
				    <div id="video_content_{$feedid}"></div>
				  </div>
				  <div class="q_btm"><img class="q_btm_l" src="__THEME__/image/zw_img.gif" /></div>
				</div>
				</eq>
			</dd>
			<p class="info">
				<span class="right">
					<a href="{:U('public/Profile/feed',array('uid'=>$sourceInfo['uid'],'feed_id'=>$sourceInfo['feed_id']))}">原文转发<neq name="sourceInfo.repost_count" value="0">({$sourceInfo.repost_count})</neq></a><i class="vline">|</i>
					<a href="{:U('public/Profile/feed',array('uid'=>$sourceInfo['uid'],'feed_id'=>$sourceInfo['feed_id']))}">原文评论<neq name="sourceInfo.comment_count" value="0">({$sourceInfo.comment_count})</neq></a>
				</span>
				<span><a href="{:U('public/Profile/feed',array('uid'=>$sourceInfo['uid'],'feed_id'=>$sourceInfo['feed_id']))}" class="date" date="{$sourceInfo['publish_time']}">{$sourceInfo['publish_time']|friendlyDate}</a><span>{:getFromClient($sourceInfo['from'])}</span></span>
			</p>
			<php>else:</php>
			<dd class="name">内容已被删除</dd>
			<php>endif;</php>
		</dl>
		]]>
	</body>
	<feedAttr comment="true" repost="true" like="false" favor="true" delete="true" />
</feed>