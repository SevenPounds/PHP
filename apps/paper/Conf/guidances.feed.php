<feed app='paper' type='guidances' info='教学应用'>
	<title comment="feed标题"> 
		<![CDATA[ {$actor}  ]]>
	</title>
	<body comment="feed详细内容/引用的内容">
		<![CDATA[ {$body|t|replaceUrl}]]>
	</body>
	<feedAttr comment="true" repost="true" like="false" favor="true" delete="true" />
</feed>