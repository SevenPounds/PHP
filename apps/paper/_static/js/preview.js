(function (win) {
    win.Paper = win.Paper || {};

    // 文档加载完后初始化的事件
    $(document).ready(function () {
        // 初始化页面中的事件
        Paper.initPage();
    });

    /**
    * 页面初始化方法
    */
    Paper.initPage = function () {
        // 删除论文点击事件
        $(".delete-paper").click(Paper.deletePaper);

        // 隐私设置点击事件
        $(".privacy-set").click(Paper.privacySet);

        // 分享按钮点击事件
        $(".share-paper").toggle(Paper.showShare, Paper.hideShare);

        $("#body-bg").css("padding-top", "26px");

        if (uid != mid) {
            $(".right_box").css({ "height": "auto", "padding-left": "110px" });
        }

        // 保证预览时图片大小不超出预览框
        $("#paper_content img").each(function () {
            if ($(this).width() > 710) {
                $(this).css("width", "100%");
            }
        });
    };

    /**
    * 删除点击提示事件
    */
    Paper.deletePaper = function () {
        ui.confirmBox("删除提示", "确定删除该论文？", Paper.submitDelete);
        $(".hd").css({ 'background': '#73BFEE', 'color': 'white' });
        $(".btn-green-small").css('background', '#73BFEE');
    };

    /**
    * 提交删除论文
    */
    Paper.submitDelete = function () {
        //删除附件
        //Paper._deleteattach();

        var data = {};
        data.id = id;
        data.uid = uid;
        data.feed_id = $("#feed_id").val();
        data.type = type;
        $.ajax({
            url: 'index.php?app=paper&mod=Index&act=submitDeletePaper',
            type: 'post',
            data: data,
            success: function (result) {
                result = eval('(' + result + ')');
                if (result.statuscode == '200') {
                    ui.success(result.data);
                    location.href = "index.php?app=paper&mod=Index&act=index&type=" + type;
                } else {
                    ui.error(result.data);
                }
            },
            error: function (msg) {
                ui.error(msg);
            }
        });
    };

    /**
    * 隐私设置点击事件，弹出设置弹出层
    */
    Paper.privacySet = function () {
        ui.box.load("index.php?app=paper&mod=Index&act=privacysettings&uid=" + uid + "&id=" + id+"&type="+type, '隐私设置', function () { });
        $(".hd").css({ 'background': '#73BFEE', 'color': 'white' });
    };

    /**
    * 保存隐私设置
    */
    Paper.submitPrivacySet = function () {
        var privacy_code = $("#privacy_settings input[type='radio']:checked").val();
        var data = {};
        if (privacy_code == $("#privacy_set").val()) {
            ui.error("您未改变隐私设置！");
            return;
        }
        data.code = privacy_code;
        data.id = id;
        data.uid = uid;
        data.feed_id = $("#feed_id").val();
        data.type = type;
        $.ajax({
            url: 'index.php?app=paper&mod=Index&act=submitPrivacySet',
            type: 'post',
            data: data,
            success: function (result) {
                ui.box.close();
                //alert(result);
                result = eval('(' + result + ')');
                if (result.statuscode == '200') {
                    ui.success(result.data);
                } else {
                    ui.error(result.data);
                }
            },
            error: function (msg) {
                ui.box.close();
                ui.error(msg);
            }
        });
    };

    /**
    * 隐私设置弹出层的取消按钮响应事件
    */
    Paper.cancelPrivacySet = function () {
        ui.box.close();
    };

    /**
    * 点击显示分享区域
    */
    Paper.showShare = function () {
        var top = $(this).offset().top;
        var left = $(this).offset().left;
        var share = $(".jiathis_style_24x24");
        share.css({ 'position': 'absolute', 'top': (top + 25) + 'px', 'left': left + 'px' });
        share.show();
    };

    /**
    * 点击隐藏分享区域
    */
    Paper.hideShare = function () {
        var share = $(".jiathis_style_24x24");
        share.hide();
    };
})(window);