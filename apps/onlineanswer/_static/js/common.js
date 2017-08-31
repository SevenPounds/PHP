/**
 * 点赞方法
 * @param ansid
 * @author xypan
 */
function addAgree(ansid){
		if (agreelock==1){
			return;
		}
		agreelock = 1;
	   $.post(U('onlineanswer/Index/addAgree'), {ansid:ansid}, function(res){
	       res= eval("("+res+")");
		   if(res.status == "200"){
		       var num = $('#agree'+ansid).attr('rel');
	    	   num++;
	    	   $('#agree'+ansid).html("<img src='./apps/onlineanswer/_static//images/zan.gif' />已赞("+num+")");
			   if($('#digg'+ansid).length > 0 ){
				   $('#digg'+ansid).html('<img src="./apps/onlineanswer/_static//images/zan.gif" style="padding-bottom: 3px"><span style="color:#999">已赞('+num+')</span>');
			   }

		   }else {
		       ui.error('操作失败，如没有登录请先登录再操作');
	       }
	       agreelock = 0;
	   });
	}

//赞锁(防止重复点赞)
var agreelock = 0;