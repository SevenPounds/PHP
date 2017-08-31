//加载创建的直播课
tea_created();
var switchMode='kaike';
function tea_created(){
	switchMode = 'kaike';
	$('#tea_created').addClass('active');
	$('#tea_signed').removeClass('active');
	$('#lessonTitle').html("开课记录");
	$.ajax({
		   type: "POST",
		   url: "index.php?app=public&mod=Lesson&act=teaCreated",
		   success: function(data){
			   $('#lives').html(data);
		   }
	});
	tea_getList();
}
function tea_getList(){
	$.ajax({
		type: "POST",
		url: "index.php?app=public&mod=Lesson&act=recordListCreate",
		data:{
			type:$("#type a.current").attr("code")
		},
		success: function(msg){
			$("#recordList").html(msg);
		}
	});
}

function tea_signed(){
	switchMode = 'tingke';
	$('#tea_signed').addClass('active');
	$('#tea_created').removeClass('active');
	$('#lessonTitle').html("听课记录");
	$.ajax({
		   type: "POST",
		   url: "index.php?app=public&mod=Lesson&act=teaSigned",
		   success: function(data){
			   $('#lives').html(data);
		   }
	});
	tea_getlistSign();
}
function tea_getlistSign(){
	$.ajax({
		type: "POST",
		url: "index.php?app=public&mod=Lesson&act=recordListSign",
		data:{
			type:$("#type a.current").attr("code")
		},
		success: function(msg){
			$("#recordList").html(msg);
		}
	});
}

$(function(){
	// 点击切换公开课标签的类型
	$("#type a").bind("click",function(){
		$("#type a").removeClass("current");
		$(this).addClass("current");
		if(switchMode == 'kaike'){
			tea_getList();
		}else if(switchMode == 'tingke'){
			tea_getlistSign();
		}
	});
});



var d ;
//弹出开课页面
function newRecordPop(){
	d = dialog({
		title :'我要开课',
	    url : 'index.php?app=public&mod=Lesson&act=newRecordPop',
	    width:685,
	    height:455,
	    modal : true,
	  //  fixed: true,
	    drag:true
	});
	d.show();
}

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
				if($('#tea_signed').hasClass("active")){
					if($("#flag_"+id).val()=="0"){
						$("#warn_"+id).html(html+"正在上课中,");
						$("#warn2_"+id).remove();
						$("#flag_"+id).val("1");
					}
				}else{
					if($("#invite_"+id).html()=="邀请听课"){
						$("#warn_"+id).html(html+"正在上课中,");
						$("#warn2_"+id).remove();
						$("#invite_"+id).html("点击进入");
					}
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

var s;
/**
 * 邀请好友列表
 * @param id
 */
function showFriend(id){
	if($('#invite_'+id).html()!="邀请听课"){
		var url=$('#lessonUrl').val();
		window.open(url+"index.php?m=Home&c=Live&a=liveView&liveId="+id);;
	}else{
		s = dialog({
			title :'邀请听课',
		    url : 'index.php?app=public&mod=Lesson&act=listAddUsers&liveId='+id,
		    width:500,
		    height:390,
		    modal : true,
		  //  fixed: true,
		    drag:true,
		});
		s.show();  
	}
}
var popSelect;
//单击选择权限按钮
function selectRadio(obj,type,select){
	if(type != ''){
		$("input[type=radio][value=0]").prop("checked",'checked');
		$('#pop_2').show();
		$('#maskDiv').show();
	}else{
		$("input[type=radio][value=1]").prop("checked",'checked');
		$('#ids').val('');
	}
} 
function startLesson(id){
	var t=$("#cuttime_"+id).val();
	if(t>300){
		alert('直播未开始，无法进入');
	}else{
		var url=$('#lessonUrl').val();
		window.open(url+"index.php?m=Home&c=Live&a=liveView&liveId="+id);
	}
}
function startLessonCreated(id){
	var t=$("#cuttime_"+id).val();
	if(t>1200){
		alert('直播未开始，无法进入');
	}else{
		var url=$('#lessonUrl').val();
		window.open(url+"index.php?m=Home&c=Live&a=liveView&liveId="+id);
	}
}