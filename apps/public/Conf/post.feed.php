<feed app='public' type='post' info='原创微博'>
	<title comment="feed标题"> 
		<![CDATA[{$actor}]]>
	</title>
	<body comment="feed详细内容/引用的内容">
		<![CDATA[
		<php>if(empty($record_id)):</php>
		{$body|t|replaceUrl}
		<php>endif;</php>
		<php>if(!empty($record_id)):</php>
		    <!-- <div class="single_sound">
		        <div class="single_sound_btn" onclick="AudioPlayer.audioplay('{$record_id}', 'progress-bar-bordertalk-{$feedid}', 'progress-bartalk-{$feedid}')">
		            <img onmouseover="this.src='SpeechToText/images/sound12.jpg'" onmouseout="this.src='SpeechToText/images/sound11.jpg'" src="SpeechToText/images/sound11.jpg" />
		        </div>
		        <div id="progress-bar-bordertalk-{$feedid}" class="progress-bar-border">
		            <img id="initial_progress-bartalk-{$feedid}" class="initial-progress-bar" src="SpeechToText/images/sound_bg1.jpg" />
		            <span id="time_progress-bartalk-{$feedid}" style="color:#888;">00:00</span>
		            <img id="progress-bartalk-{$feedid}" class="record_progress-bar" src="SpeechToText/images/sound_bg2.jpg" />
		        </div>
		    </div> -->
			<div class="list-saycontent">
					<span class="inline-block">
					<a href="javascript:;" class="showsound" id="newplaybtn" name ='{$record_id}' onclick = "AudioPlayer.audioplaynew('{$record_id}', this)">
						<em class="jing"></em>
					</a>
					</span>
					<span class="inline-block"><a href="javascript:;" class="showwenzi"></a></span>
					<div class="wenzicontent">
						<i class="arrow-t"></i>
						<p>{$body|t|replaceUrl}</p>
					</div>
				</div>
		<php>endif;</php>
		]]>
	</body>
	<feedAttr comment="true" repost="true" like="false" favor="true" delete="true" />
</feed>