<feed app='blog' type='blog_post' info='日志'>
	<title comment="feed标题"> 
		<![CDATA[ {$actor}  ]]>
	</title>
	<body comment="feed详细内容/引用的内容">
		<![CDATA[ {$body|t|replaceUrl}
		]]>
	</body>
	<feedAttr comment="true" repost="true" like="false" favor="true" delete="true" />
</feed>