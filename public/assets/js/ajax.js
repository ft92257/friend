/**
 * 普通ajax表单提交
 * @param {Object} form
 * @param {Object} callback
 * @param {String} confirmMsg 提示确认信息
 */
;function validateCallback(form, callback, otherValid,confirmMsg) {
    var $form = $(form);

    //表单验证判断
    if (!$form.valid()) {
        return false;
    }
    if( typeof(otherValid)=="function"){
        if(!otherValid()){
            return false;
        }
    }
    var submitAjax = function(){
        $.ajax({
            type: form.method || 'POST',
            url:$form.attr("action"),
            data:$form.serializeArray(),
            dataType:"json",
            cache: false,
            success: callback || MOB.ajaxDone,
            error: MOB.ajaxError
        });
    };

    if(confirmMsg){
        var opt={};
        opt.content = confirmMsg;
        opt.ok = function(){
            submitAjax();
        };
        alertMsg.confirm(opt);
    }else{
        submitAjax();
    }

    return false;
}

/**
 * 普通表单提交回调函数
 * @param {Object} json
 */
function navTabAjaxDone(json){
    //return false;
    if(!MOB.ajaxDone(json)) return ;

    if (json.statusCode == MOB.statusCode.success) {
        if(json.navTabId){
            navTab.reloadFlag(json.navTabId);
        }else{
            //var $pagerForm = $("#pagerForm", navTab.getCurrentPanel());
            //var args = $pagerForm.size()>0 ? $pagerForm.serializeArray() : {}
            //navTabPageBreak(args, json.rel);
        }
        if ("closeCurrent" == json.callbackType) {
            setTimeout(function(){navTab.closeCurrentTab(json.navTabId);}, 100);
        } else if ("forward" == json.callbackType) {
            navTab.reload(json.forwardUrl);
        }else{
            //navTab.getCurrentPanel().find(":input[initValue]").each(function(){
            //    var initVal = $(this).attr("initValue");
            //    $(this).val(initVal);
            //});
        }

    }
}

function dialogAjaxDone(json){
    MOB.ajaxDone(json);
    if (json[MOB.keys.statusCode] == MOB.statusCode.success){
        if (json.navTabId){
            navTab.reload(json.forwardUrl, {navTabId: json.navTabId});
        } else {
            var $pagerForm = $("#pagerForm", navTab.getCurrentPanel());
        }
        if ("closeCurrent" == json.callbackType) {
            $.pdialog.closeCurrent();
        }
    }
}


/**
 * 处理navTab上的查询, 会重新载入当前navTab
 * @param {Object} form
 */
function navTabSearch(form, navTabId){
    var $form = $(form);
    //$form["p"].value = 1;
    navTab.reload($form.attr('action'), {data: $form.serializeArray(), navTabId:navTabId});
    return false;
}

/**
 * 处理dialog弹出层上的查询, 会重新载入当前dialog
 * @param {Object} form
 */
function dialogSearch(form){
    var $form = $(form);
    ////if (form[MOB.pageInfo.pageNum]) form[MOB.pageInfo.pageNum].value = 1;
    $.pdialog.reload($form.attr('action'), {data: $form.serializeArray()});
    return false;
}
function mobSearch(form, targetType){
    if (targetType == "dialog") dialogSearch(form);
    else navTabSearch(form);
    return false;
}

/**
 * 处理navTab ajaxTodo方式回调
 * @param url string
 * @param callback function
 */

function ajaxTodo(url, callback){
    var $callback = callback || navTabAjaxDone;
    if (! $.isFunction($callback)) $callback = eval('(' + callback + ')');
    $.ajax({
        type:'POST',
        url:url,
        dataType:"json",
        cache: false,
        success: $callback,
        error: MOB.ajaxError
    });
}

//测试第三方验证
function testValid(){
    return false;
}