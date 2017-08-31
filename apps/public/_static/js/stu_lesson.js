//倒计时
function getTime(){
	$("#lives p").each(function(index,element) {
			var id=$(this).attr('id');
			id=id.substring(5,id.length);
			var t=$("#cuttime_"+id).val();
			var ot=$("#overtime_"+id).val();
			if(ot<=0){
				$(this).remove();
				$("#invite_"+id).remove();
			}else{
				$("#overtime_"+id).val(ot-1);
			}
			if(t<=0){
				var html=$("#warn_"+id).html();
				if($("#flag_"+id).val()=="0"){
					$("#warn_"+id).html(html+"正在上课中,");
					$("#warn2_"+id).remove();
					$("#flag_"+id).val("1");
				}
			}else{
			    var d=Math.floor(t/60/60/24);
			    var h=Math.floor(t/60/60%24);
			    var m=Math.floor(t/60%60);
			    var s=Math.floor(t%60);
				$("#day_"+id).html(d);
				$("#hour_"+id).html(h);
				$("#min_"+id).html(m);
				$("#cuttime_"+id).val(t-1);
			}
	}); 
}
setInterval(getTime,1000);

function startLesson(id){
	var t=$("#cuttime_"+id).val();
	if(t>300){
		alert('直播未开始，无法进入');
	}else{
		var url=$('#lessonUrl').val();
		window.open(url+"index.php?m=Home&c=Live&a=liveView&liveId="+id);
	}
}