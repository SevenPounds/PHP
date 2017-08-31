(function (win) {
    var _title = '';
    var _content = '';
    var _attachlist;
    var _private = '';
    win.Paper = win.Paper || {};
    // 衙门加载完成后自动执行
    $(document).ready(function () {
        Paper.attachments = new Array();
        _title = $("#paper_title").val();
        _content = $('#content').val();
        _private = $("#paper_privacy").val();
        _attachlist = $("#attachlist").html();

        var hiddenval = $("#hiddenattachs").val();
        if (hiddenval != "null" && undefined != hiddenval && hiddenval != "") {
            var initattachments = eval("(" + hiddenval + ")");
            for (var i = 0; i < initattachments.length; i++) {
                Paper.attachments.push(new Array(initattachments[i]["attachid"], initattachments[i]["title"], initattachments[i]["attachtype"]))
            }
        }
        //判断附件数量
        if (Paper.attachments.length == 0) {
            $("#attachlist").css("display", "none");
        }

        // 取消编辑按钮点击事件
        $("#paper_cancel").click(Paper.cancelEdit);

        // 提交编辑按钮点击事件
        $("#paper_submit").click(Paper.submitEdit);

        $("#body-bg").css("padding-top", "26px");
    });

    /**
    * 取消论文编辑
    */
    Paper.cancelEdit = function () {
        location.href = "index.php?app=paper&mod=Index&act=index&type=" + type;
    };

    /**
    * 提交论文编辑
    */
    Paper.submitEdit = function () {
        var paper = Paper.getPaperInfo();
        var check = Paper.checkPaper(paper);
        if (check != '1') {
            ui.error(check);
        } else {
            $.ajax({
                url: 'index.php?app=paper&mod=Index&act=submitEditPaper',
                type: 'post',
                data: paper,
                success: function (result) {
                    //alert(result);
                    result = eval('(' + result + ')');
                    if (result.statuscode == '200') {
                        ui.success(result.data);
                        setTimeout("Paper.cancelEdit()", 2500);
                    } else {
                        ui.error(result.data);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    //alert(XMLHttpRequest.status);
                    ui.error(msg);
                }
            });
        }
    };

    /**
    * 获取论文信息
    */
    Paper.getPaperInfo = function () {
        var paper = {};

        paper.id = $("#hid_id").val();
        paper.uid = uid;
        paper.title = $("#paper_title").val();
        paper.type = type;
        E.sync();
        paper.content = $('#content').val();
        //添加附件 by zhaoliang 2013/11/5
        paper.attachments = Paper.attachments;
        //paper.friendid = $("#paper_recom").val();
        paper.privacyid = $("#paper_privacy").val();
        paper.feed_id = $("#feed_id").val();

        return paper;
    };

    /**
    * 检查论文信息是否完整
    * @param paper 论文信息对象
    */
    Paper.checkPaper = function (paper) {
        if (paper.title == _title && paper.privacyid == _private && paper.content == _content && _attachlist == $("#attachlist").html()) {
            ui.success("编辑成功！");
            //return "您未做任何修改！";
            return 1;
        }
        if (paper.title.length > 40) {
            return "标题不能超过40字！";
        }
        if (!paper.title || paper.title == "" || $.trim(paper.title) == "") {
            return "标题不能为空！";
        }
        if (!paper.content || paper.content == "" || $.trim(paper.content.replace(/&nbsp;/ig, " ")) == "") {
            return "内容不能为空！"
        }
        return 1;
    };
})(window);