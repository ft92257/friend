/**
/**
 *
 * ERP核心代码
 *
 */
//调试函数，开发完需删除
function debug(str){
    MOB.debug(str);
}
var MOB = {
    linkOpen:true,
    _set:{
        debug:true
    },
    _msg:{},
    debug:function(str){
        if(MOB.debug) console.log(str);
    },
    keyCode: {
        ENTER: 13, ESC: 27, END: 35, HOME: 36,
        SHIFT: 16, TAB: 9,
        LEFT: 37, RIGHT: 39, UP: 38, DOWN: 40,
        DELETE: 46, BACKSPACE:8
    },
    //服务器回调状态
    statusCode: {
        success: 200,
        timeout: 300,
        error: 400
    },
    keys: {statusCode:"statusCode", message:"message"},
    //回调页面处理方式
    callbackType:{
        close: "close",
        direct: "direct"
    },
    eventType: {
        pageClear:"pageClear",	// 用于重新ajaxLoad、关闭nabTab, 关闭dialog时，去除xheditor等需要特殊处理的资源
        resizeGrid:"resizeGrid"	// 用于窗口或dialog大小调整
    },
    ui:{
        sbar:true,
        hideMode:'display' //navTab组件切换的隐藏方式，支持的值有’display’，’offsets’负数偏移位置的值，默认值为’display’
    },
    dialog:{},//存储当前dialog
    msg:function(key, args){
        var _format = function(str,args) {
            args = args || [];
            var result = str || "";
            for (var i = 0; i < args.length; i++){
                result = result.replace(new RegExp("\\{" + i + "\\}", "g"), args[i]);
            }
            return result;
        }
        return _format(this._msg[key], args);
    },
    //ajax默认错误回调方法
    ajaxError:function(){
        alertMsg.error({content:'<h2>ajax Error</h2>'});
        return false;
    },
    //ajax默认回调方法
    ajaxDone:function(json){
        if(json.statusCode == MOB.statusCode.error) {
            //MOB.debug("json.statusCode:400");
            alertMsg.error({content:json.message});
            return false;
        } else if (json.statusCode == MOB.statusCode.timeout) {
            alertMsg.error({content:json.message});
            return false;
        } else if (json.statusCode == MOB.statusCode.success){
            //alertMsg.success({content:json.message});
            //MOB.debug("json.statusCode:200");
            return true;
        };
    },
    //json to string
    obj2str:function(o) {
        var r = [];
        if(typeof o =="string") return "\""+o.replace(/([\'\"\\])/g,"\\$1").replace(/(\n)/g,"\\n").replace(/(\r)/g,"\\r").replace(/(\t)/g,"\\t")+"\"";
        if(typeof o == "object"){
            if(!o.sort){
                for(var i in o)
                    r.push(i+":"+MOB.obj2str(o[i]));
                if(!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)){
                    r.push("toString:"+o.toString.toString());
                }
                r="{"+r.join()+"}"
            }else{
                for(var i =0;i<o.length;i++) {
                    r.push(MOB.obj2str(o[i]));
                }
                r="["+r.join()+"]"
            }
            return r;
        }
        return o.toString();
    },
    //string to json
    jsonEval:function(data) {
        try{
            if ($.type(data) == 'string')
                return eval('(' + data + ')');
            else return data;
        } catch (e){
            return {};
        }
    },
    progressBar:function(){
        var ajaxbg = $("#loadingBg,#loadingBar");
        $(document).ajaxStart(function(){
            ajaxbg.stop().show();
        }).ajaxStop(function(){
            ajaxbg.stop().hide();
        });
    },
    //初始化
    init:function(opt){
        var _doc = $(document);
        if (!_doc.isBind(MOB.eventType.pageClear)) {
            _doc.bind(MOB.eventType.pageClear, function(event){
                var box = event.target;
                //if ($.fn.xheditor) {
                //    $("textarea.editor", box).xheditor(false);
                //}
            });
        }
        MOB.progressBar();
        MOB.initEnv();
        navTab.init();

        //open location.href.hash

        $.History.openHash();

    },
    //初始化界面
    initEnv:function(){

        //validator初始化
        $.validator.setDefaults({
            debug: MOB._set.debug
        });
        $.extend($.validator.messages,validatorSet.mssages);

        MOB.initLayout();
        MOB.initUI();
        $(window).resize(function(){
            MOB.initLayout();
        });
        $(".sidebar-menu-toogle").toggle(
            function(){
                if($(".ERP-L").is(":animated")) return false;
                $(".sidebar span").stop().fadeOut(200);
                $(".ERP-L").stop().animate({width:60},300);
                $(".sidebar").stop().animate({width:60},300);

                $(".ERP-R").stop().animate({left:60},300);
            },
            function(){
                if($(".ERP-L").is(":animated")) return false;
                $(".ERP-L").stop().animate({width:200},300,function(){
                    $(".sidebar span").stop().fadeIn(200);
                });

                $(".sidebar").stop().animate({width:200},300);

                $(".ERP-R").stop().animate({left:200},300);
            }
        );

    },
    //初始化布局
    initLayout:function(){
        /*var W = $(window).width(),
            H = $(window).height();
        $(".navTab-panel").css({"width":W-200,"height":H-90,"overflow":"hidden"});
        $(".ERP-L").css({"height":H-90,"overflow":"hidden"});*/
        $(".sidebar-menu").height($(".ERP-L").height());
        /*if($(".ERP-R-M-L")){
            $(".sub-sidebar-menu").height($(".ERP-R-M-L").height());
        }*/
    },
    //初始化UI
    initUI:function(_box){
        var $p = $(_box || document);

        //上传组件
        if(WebUploader) $(".web-uploader", $p).each(function(){
            var _this = $(this);
            var opt = MOB.jsonEval(_this.data("opt"));
            var defaults ={
                auto:true,
                swf:'js/webuploader/Uploader.swf',
                pick:{id:_this[0], multiple:false},
                compress:false,
                thumb:false,
                cover:true

            };
            var options = $.extend(defaults, opt);
            var uploader = WebUploader.create(options);

            uploader.on( 'fileQueued', function( file ) {

                var $list = _this.siblings(".uploader-list");

                if(defaults.thumb) {
                    var $li = $(
                            '<div id="' + file.id + '" class="file-item thumbnail">' +
                            '<img>' +
                            '<div class="info">' + file.name + '</div>' +
                            '</div>'
                        ),
                        $img = $li.find('img');
                    // $list为容器jQuery实例
                    $list.append( $li );

                    // 创建缩略图
                    // 如果为非图片文件，可以不用调用此方法。
                    // thumbnailWidth x thumbnailHeight 为 100 x 100
                    uploader.makeThumb( file, function( error, src ) {
                        if ( error ) {
                            $img.replaceWith('<span>不能预览</span>');
                            return;
                        }

                        $img.attr( 'src', src );
                    }, 100, 100 );

                }else{
                    $list.html('<span style="display: inline-block;" id="'+ file.id +'">'+ file.name +'</span>');
                }

                // 文件上传过程中创建进度条实时显示。
                uploader.on( 'uploadProgress', function( file, percentage ) {
                    var $li = $( '#'+file.id ),
                        $percent = $li.find('.webuploader-progress i');

                    // 避免重复创建
                    if ( !$percent.length ) {
                        $percent = $('<cite class="webuploader-progress"><i></i></cite>')
                            .appendTo( $li )
                            .find('span');
                    }

                    $percent.css( 'width', percentage * 100 + '%' );

                });

                // 文件上传成功，给item添加成功class, 用样式标记上传成功。
                uploader.on( 'uploadSuccess', function( file ,responses) {
                    debug('#' + file.id);

                    if(defaults.thumb) {
                        $('#' + file.id).addClass('upload-state-done');
                    }
                    $('input[name='+defaults.backForm+']').val(responses.url);
                });

                // 文件上传失败，显示上传出错。
                uploader.on( 'uploadError', function( file ) {

                    var $li = $( '#'+file.id ),
                        $error = $li.find('div.error');

                    // 避免重复创建
                    if ( !$error.length ) {
                        $error = $('<div class="error"></div>').appendTo( $li );
                    }

                    $error.text('上传失败');
                });

                // 完成上传完了，成功或者失败，先删除进度条。
                uploader.on( 'uploadComplete', function( file ) {
                    $( '#'+file.id ).find('.webuploader-progress').remove();
                    //uploader.reset();
                });



            });
        });

        //表单初始化
        $("form.required-validate", $p).each(function(){
            var $form = $(this);
            $form.validate({
                onsubmit: false,
                focusInvalid: false,
                focusCleanup: true,
                errorElement: "span",
                ignore:".ignore",
                //errorLabelContainer: $("#signupForm span.error"),
                invalidHandler: function(form, validator) {
                    //alert('error');
                    //var errors = validator.numberOfInvalids();
                    //if (errors) {
                    //    var message = DWZ.msg("validateFormError",[errors]);
                    //    alertMsg.error(message);
                    //}
                }
            });

        });

        //实例化二级菜单缩进

        if($(".SUB-L", $p)) {
            MOB.twoTree();

        }
        //实例化无限级菜单
        if($(".tree", $p)){$(".tree", $p).tree();}
        if($(".ERP-R-M-L")){
            /*$(".ERP-R-M-toogle").click(function(){
                $(".ERP-R-M-toogle").toggleClass("system-toogle-stop");
                $(".ERP-R-M-toogle").toggleClass("system-toogle-open");
                $(".ERP-R-M").toggleClass("ERP-R-M-open");
            });*/
            $(".ERP-R-M-toogle").toggle(
                function(){
                    $(".ERP-R-M-L").animate({width:0},300,function(){
                        $(".ERP-R-M-R").animate({left:0},500,function(){
                            $(".ERP-R-M-toogle").removeClass("system-toogle-stop").addClass("system-toogle-open").animate({left:0},400);
                        });
                    });

                    $(".ERP-R-M-toogle").animate({left:-16},300,function(){

                    });

                },
                function(){


                    $(".ERP-R-M-toogle").animate({left:-16},300,function(){
                        $(".ERP-R-M-L").animate({width:200},400);
                        $(".ERP-R-M-R").animate({left:200},400,function(){
                            $(".ERP-R-M-toogle").fadeIn(400);
                        });
                        $(this).removeClass("system-toogle-open").addClass("system-toogle-stop").hide().css("left","184px");
                    });

                }
            );
        }

        //时间插件实例化
        //if($.fn.datepick) $(".date",$p).datepick({dateFormat: 'yyyy-mm-dd'});
        if ($.fn.datepicker){
            $('input.date', $p).each(function(){
                var $this = $(this);
                var opts = {};
                if ($this.attr("dateFmt")) opts.pattern = $this.attr("dateFmt");
                if ($this.attr("minDate")) opts.minDate = $this.attr("minDate");
                if ($this.attr("maxDate")) opts.maxDate = $this.attr("maxDate");
                if ($this.attr("mmStep")) opts.mmStep = $this.attr("mmStep");
                if ($this.attr("ssStep")) opts.ssStep = $this.attr("ssStep");
                $this.datepicker(opts);
            });
        }
        //分页实例化
        if($.fn.mobPaging) $(".pagingBar",$p).mobPaging();



        //实例化树状菜单
        if($("dl.main-tree")) $("dl.main-tree", $p).mobTree();

        //实例化画圆
        if($.fn.circle) {
            $(".canvas-circle",$p).each(function(){
                var _this = $(this);
                var canvasObj = $(".canvas-circle canvas");
                var canvas = canvasObj[0];

                if($.browser.msie && ($.browser.version=="7.0" || $.browser.version=="8.0")){

                    canvas=window.G_vmlCanvasManager.initElement(canvas);
                }

                var percent = $(".canvas-circle .canvas-value").html();
                if(percent=='100'){
                    percent=99.99;
                }
                if(percent=="0"){
                    percent=0.01;
                }
                var ids = $(".canvas-circle");
                var h = ids.height();
                ids.find(".canvas-content").css("line-height",h+"px");
                $(".canvas-circle").circle({
                    percent: percent,
                    fgcolor: "#ff6458",
                    bgcolor: "#ddd",
                    bordersize: 6
                });
            });
        }

        //实例化单选框
        //if($.fn.radioBox) $("input[type=radio]").radioBox();

        //实例化下拉框
        //if ($.fn.combox) $("select.select",$p).combox();

        //实例化复选框
        //if($.fn.checkBox) $("input[type=checkbox]", $p).checkBox();

        //实例化tabList滑动
        $(".tabList ul li", $p).each(function(){
            $(this).click(function(){
                var _this = $(this);
                var index= _this.index();
                $(".tabList ul li", $p).removeClass('cur');
                _this.addClass('cur');
                $(".tabCon .tabconm", $p).hide().eq(index).show();
            });
        });



        //初始化a标签 navTab

        $("a[target=navTab]", $p).each(function(){
            $(this).click(function(event){
                if(MOB.linkOpen){
                    var $this = $(this);
                    var data = $this.data("opt") || "";
                    var opt = MOB.jsonEval(data);
                    opt.tabid = opt.tabid || "noid";
                    opt.title = opt.title || $this.text();
                    opt.url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
                    opt.refresh = opt.refresh || false;
                    navTab.openTab(opt);
                    event.preventDefault();
                }else{
                    $(this).attr("target","_self");
                }
            });
        });


        //初始化a标签 dialog
        $("a[target=dialog]", $p).each(function(){
            $(this).click(function(event){
                var $this = $(this);
                var data = $this.data("opt") || "";
                var opt = MOB.jsonEval(data);
                opt.id = opt.tabid || "noid";
                opt.title = opt.title || $this.text();

                opt.url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));

                if (!opt.url.isFinishedTm()) {
                    alert('url error');
                    //alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
                    return false;
                }

                $.pdialog.open(opt);

                event.preventDefault();
                return false;
            });
        });

        $("a[target=ajaxTodo]", $p).each(function(){
            $(this).click(function(event){
                var $this = $(this);
                var data = $this.data("opt") || "";
                var opt = MOB.jsonEval(data);
                opt.href = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
                if(!opt.href.isFinishedTm()){
                    alert('url error');
                    return false;
                }
                if(opt.content){
                    opt.ok = function(){
                        ajaxTodo(opt.href, $this.attr("callback"));
                    };
                    alertMsg.confirm(opt);
                }else{
                    ajaxTodo(opt.href, $this.attr("callback"));
                }


                event.preventDefault();
                return false;
            });
        });

        //查找带回
        if ($.fn.lookup) $("a[lookupGroup]", $p).lookup();
        if ($.fn.multLookup) $("[multLookup]:button", $p).multLookup();
        if ($.fn.multLookup) $("[comboxLookup]:button", $p).comboxLookup();
        if ($.fn.suggest) $("input[suggestFields]", $p).suggest();
        if ($.fn.itemDetail) $("table.itemDetail", $p).itemDetail();
        if ($.fn.selectedTodo) $("a[target=selectedTodo]", $p).selectedTodo();
        if ($.fn.pagerForm) $("form[rel=pagerForm]", $p).pagerForm({parentBox:$p});


        //编辑器实例化
        $(".ueditor", $p).each(function(){
            var _this = $(this);
            var id = 'ueditor_'+ new Date().getTime();
            _this.attr("id",id);
            var ue = UE.getEditor(id,{
                autoHeight: false
            });
        });

        /*全选，反选*/
        $(":button.checkboxAll, :checkbox.checkboxAll", $p).checkboxCtrl($p);

        //是否全屏
        if($(".quit-screen-btn").length>0){
            navTab.fullScreen();
        }
        //调用扩展初始
        mobExtendInit($p);

    },
    /*dialogBack:function($panel){

        $panel.find("[layoutH]").layoutH();
        $panel.find(":button.close").click(function(){
            navTab.closeCurrentTab();
        });
    },
    dialogInit:function(options){
        var defaults = {
            fixed: true
        };
        var op = $.extend({},defaults,options);
        return dialog(op);
    },*/
    twoTree:function(){
        $(".sub-sidebar-toogle").click(function(){
            $(".sub-sidebar-toogle").toggleClass("system-toogle-stop");
            $(".sub-sidebar-toogle").toggleClass("system-toogle-open");
            $(".SUB-M").toggleClass("SUB-M-toogle");
        });
        /*var W = $(window).width(),
            H = $(window).height();
        $(".SUB-R").css({"height":H-90,"overflow":"hidden"});
        $(".SUB-L").css({"height":H-90,"overflow":"hidden"});
        $(".ERP-R-M-R").css({"height":H-90,"overflow-x":"hidden"});*/
    }


};


