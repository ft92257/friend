var SUCCESS_STATUS = 20000;

/*
 * 自动选中导航条
 * 默认class current
 */
function selectNav(parent, curClass) {
	var links = parent.find('a');
	links.removeClass(curClass);

	links.each(function(){
		if (location.href.indexOf($(this).attr('data-match')) != -1) {
			links.filter('.' + curClass).removeClass(curClass);
			$(this).addClass(curClass);
		}
	});
}

/*
 * 自动验证字段 示例：
 * <input type="text" name="account" onblur="ajaxValidate(this)" />
 */
function ajaxValidate(tobj) {
	var obj = $(tobj);
	var url = URL_VALIDATE;
	var par = obj.parents('.control-group');
	var help = par.find('.help-block');
	if (obj.val() == '') {
		par.removeClass('success');
		par.removeClass('error');
		//help.html(help.attr('default'));
		showHelp(help, help.attr('default'));
		return false;
	}
	
	var data = {FIELD:obj.attr('name'),VALUE:obj.val()};
	
	$.post(url, data, function(json){
		if (json.status != 0) {
			obj.focus();
			//help.html(json.msg);
			showHelp(help, json.msg);
			par.removeClass('success');
			par.addClass('error');
		} else {
			//help.html(help.attr('data-default'));
			showHelp(help, help.attr('data-default'));
			par.removeClass('error');
			par.addClass('success');
		}
	}, 'json');
}

function showHelp(help, html) {
	if (html) {
		help.show();
		help.html(html);
	}
}

/*
 * 添加标签
 */
function pasteTag(tobj) {
	obj = $(tobj);
	var type = obj.attr('tagType');
	var selectedTags = obj.parent().prev('.selectedTags');
	var prev = obj.prev("input[type='text']");
	var content;
	if (tobj.tagName == 'SPAN') {
		content = obj.html();
	} else {
		content = prev.val();
	}
	
	$.post(GROUP + '/Tag/add', {type:type,content:content},
		function(text){
			if (text == '1') {
				selectedTags.append('<span onclick="deleteTag(this)">'+content+'</span>');
				prev.val('');
				var field = selectedTags.find("input[type='hidden']");
				field.val(field.val() + content + '|');
				if (tobj.tagName == 'SPAN') {
					obj.removeAttr('onClick');
					obj.unbind('click');
					obj.addClass('pasted');
				}
			} else {
				alert(text);
			}
	});
}

/*
 * 显示标签删除按钮
 */
function deleteTag(tobj) {
	obj = $(tobj);
	var cont = obj.html();
	var field = obj.siblings("input[type='hidden']");
	field.val(field.val().replace('|'+cont+'|', '|'));
	
	obj.parent().next('.hotTags').find('.pasted').each(function(){
		if (this.innerHTML == cont) {
			$(this).removeClass('pasted');
			$(this).bind('click', function(){pasteTag(this);});
		}
	});
	obj.remove();
}

/**
 * 获取下一级选项
 * @param url 数据获取地址，结果为 <option></option> 组合
 * @param tobj 父级元素
 * @param value 子级的默认值
 */
function getChildrenOptions(url, tobj, value) {
	var upid = tobj.value;
	var obj = $(tobj);

	$.post(url, {upid:upid}, function(text){
		obj.nextAll('select:gt(0)').val('');
		//obj.nextAll('select:gt(0)').hide();
		obj.nextAll('select:eq(0)').show();
		obj.nextAll('select:eq(0)').html(text);
		obj.nextAll('select:eq(0)').val(value);
	});
}



/*
 * checkbox 全部选中
 */
function checkAll(obj) {
	if (obj.checked) {
		$(obj).nextAll("input[type='checkbox']").each(function(){
			this.checked = "checked";
		});
	} else {
		$(obj).nextAll("input[type='checkbox']").attr("checked", false);
	}
}

function checkAllByName(name) {
	var status = 0;
	$("input[name='"+name+"[]']").each(function(){
		if (status == 1) {
			this.checked = false;
		} else if(status == 2) {
			this.checked = true;
		} else {
			if (this.checked) {
				this.checked = false;
				status = 1;
			} else {
				this.checked = true;
				status = 2;
			}
		}
	});
}

function multiOperate(action) {
	if (!confirm('确定要执行该批量操作吗？')) {
		return false;
	}

	var ids = new Array();
    $("input:checkbox[name='chk[]']:checked").each(function(){
    	ids.push($(this).val());
    })
	if(ids.length == 0){
		alert('请选择操作项');
		return;
	}
    var html = '<div id="multiOperateRet" style="display:none;width:400px;height:100px;text-align:center;font-size:16px;line-height:100px;">正在处理中......</div>';
    $('body').append(html);
    layerIfram($("#multiOperateRet"));
    multiPost(action, ids);
}

function multiPost(action, ids) {
	var id = ids.shift();
	
	$.post(URL_HOST+action,{id:id,isajax:1},function(json){
		if(json.status != SUCCESS_STATUS){
			alert('编号:'+ id + ' '+json.msg);
		} else {
			if (ids.length == 0) {
				//alert('操作成功！');
				window.location.href = window.location.href;
				return;
			} else {
				multiPost(action, ids);
			}
		}
	},'json');
}

/*
 * checkbox 不全部选中
 */
function checkNotAll(obj) {
	if (!obj.checked) {
		$(obj).prevAll("input[type='checkbox']").filter("[value='ALL']").attr("checked", false);
	}
}

/*
 * 自动更新列表字段
 */
function autoUpdate(obj, url) {
	if ($(obj).attr('oldval') == obj.value) {
		return false;
	}

	$.post(url, {field:$(obj).attr('field'),value:obj.value}, function(ret){
		if (ret.status != SUCCESS_STATUS) {
			alert(ret.msg);
		} else {
			$(obj).attr('oldval', obj.value);
		}
	}, 'json');
}

/*
 * 控制下一级是否显示
 */
function selectTarget(select, index, target) {
	select.change(function(){
		if ($(this).val() == index) {
			target.show();
		} else {
			target.hide();
		}
	});
	if (select.val() == index) {
		target.show();
	} else {
		target.hide();
	}
}
function checkedTarget(field, index, target) {
	var s = "[name='"+field+"']";

	$(s + ":eq("+index+")").click(function(){
		target.show();
	});
	$(s + ":lt("+index+"),"+ s + ":gt("+index+")").click(function(){
		target.hide();
	});
	if ($(s + ":eq("+index+")").attr('checked') == 'checked') {
		target.show();
	} else {
		target.hide();
	}
}
function checkedHideTarget(field, index, target) {
	var s = "[name='"+field+"']";

	$(s + ":eq("+index+")").click(function(){
		target.hide();
	});
	$(s + ":lt("+index+"),"+ s + ":gt("+index+")").click(function(){
		target.show();
	});
	if ($(s + ":eq("+index+")").attr('checked') == 'checked') {
		target.hide();
	} else {
		target.show();
	}
}

function addBefore(obj, pre) {
	var btn = $(obj);
	btn.before(pre + btn.prevAll('.BASE_PARAM').html());
	btn.prev('span').find('input').val('');
	btn.prev('div').find('img').attr('src', STATICS + 'images/404.jpg');
	//btn.prev('div').find('textarea').val('');
	
	btn.prev('span').find('input')[0].focus();
	//btn.prev('div').find('textarea').focus();
}

function addImageBefore(obj, pre) {
	var btn = $(obj);
	var i = btn.prevAll('.BASE_PARAM').attr('i');
	btn.prevAll('.BASE_PARAM').attr('i', parseInt(i)+1);
	var html = btn.prevAll('.BASE_PARAM').html();
	html = html.replace("ajaxFileUpload(this, 0)", "ajaxFileUpload(this, "+i+")");
	html = html.replace("AJAX_UPLOAD_0", "AJAX_UPLOAD_"+i);
	
	btn.before(pre + html);
	//btn.prev('span').find('input').val('');
	btn.prev('div').find('img').attr('src', STATICS + 'images/404.jpg');
	btn.prev('div').find('textarea').val('');
	
	//btn.prev('span').find('input')[0].focus();
	btn.prev('div').find('textarea').focus();
}

function ajaxFileUpload(obj, i)
{
	var par = $(obj).parents('.thumbnail');
	var old = par.find('img').attr('src');
	
	$(obj).ajaxStart(function(){
		par.find('img').attr('src', STATICS + 'images/loading.gif');
	})
	
	$.ajaxFileUpload
	(
		{
			url: URL_AJAX_UPLOAD,
			secureuri:false,
			fileElementId:obj,
			dataType: 'json',
			data:{"AJAX_UPLOAD_FIELD":obj.name,"i":i},
			success: function (data, status)
			{
				if(typeof(data.error) != 'undefined')
				{
					if(data.error != '')
					{
						alert(data.error);
						par.find('img').attr('src', old);
					}else
					{
						par.find('img').attr('src', data.url);
						par.find('input[type=hidden]').val(data.fid);
					}
				}
			},
			error: function (data, status, e)
			{
				alert(e);
			}
		}
	)
	
	return false;

}

function iFrameHeight(id) { 
	var ifm= document.getElementById(id); 
	var subWeb = document.frames ? document.frames[id].document : ifm.contentDocument; 
	if(ifm != null && subWeb != null) { 
		ifm.height = subWeb.body.scrollHeight; 
	} 
}

/*弹层调用*/
function layerIfram(obj){
	$.layer({
    type : 1,
	zIndex: 9999,
    shade : [0.62,'#000',true],
    area : ['auto', 'auto'],
    title : false,
    border : [0],
    page : {dom : obj}
	});

}

function layerClose(selector) {
	layer.close(layer.getIndex(selector));
}

function iframeFixHeight(field, wfix, hfix) {
    //框架id
    var par = parent.$('#IFRAME_' + field);
    var height = $('#' +　field).height() + hfix;
    par.height(height);
    par.width($('#' + field).width() + wfix);
	if (par.parents('.xubox_layer').length > 0) {
		par.parentsUntil('.xubox_layer').height(height);
		var top = ($(parent).height() - height) / 2;
		par.parents('.xubox_layer').css("top", top + "px");
	}
}

function init_float_div() {
	$('.float_div').parent().mouseover(function(){
        var flt = $(this).children('.float_div');
        var top = $(this).position().top + (flt.height() - $(this).height()) / 2;
        var left = $(this).position().left - (flt.width() - $(this).width()) / 2;
        flt.show();
        flt.css("top", top + "px");
        flt.css("left", left + "px");
    }).mouseout(function(){
    	$(this).children('.float_div').hide();
    });
}

function showChildren(obj, init) {
	var options = $('#' + obj.name + '_' + $(obj).val()).html();
	if (options) {
		$(obj).nextAll('select').show();
		$(obj).nextAll('select').html('');
		$(obj).next('select').html(options);
		if (init) {
			$(obj).next('select').val('');
		}
	} else {
		$(obj).nextAll('select').hide();
	}
}

function selectedTags(field, max) {
	var s = '|';var count = 0;var html = '';
	$('#TAGS_'+field).find('input').each(function(){
		if (this.checked) {
			s += this.value + '|';
			count++;
			html += '<span class="label label-info" style="margin-right:12px;">'+$(this).next().html()+'</span>';
		}
	});
	if (count > max) {
		alert('最多选择'+max+'个!');
		return;
	}
	
	if (html) {
		$("#TAGS_SHOW_" + field).show();
	}
	
	$("#TAGS_SHOW_" + field).html(html);
	$("[name="+field+"]").val(s);
	layer.close(layer.getIndex('#TAGS_'+field));
}

function initTable() {
    $(".tableList tr").each(function(){
        $(this).hover(function(){
            $(this).css({background:"#d7f7f4",transition:"all 0.3s ease"});
        },function(){
            $(this).css({background:"#fff",transition:"all 0s ease"});
        })
    })
}

/*
 * 瀑布流 
 * 使用方法如下 
 * 自定义getList方法即可
var p = 1;
var loading = false;
window.onscroll = function(){
	if (!loading && getCheck()) {
		loading = true;
		p++;
		getList();
	}
}*/
function getCheck(){
	var documentH = document.documentElement.clientHeight;
	var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
	var scrollHeight = document.documentElement.scrollHeight || document.body.scrollHeight;

	//console.log(scrollHeight - documentH - scrollTop);
	
	return (scrollHeight - documentH - scrollTop) < 150;
}

/**
 * 搜索地理位置
 */
function searchPoi(obj, name) {
	var ob = $(obj);
	if (ob.html() == '搜索') {
		$.post(URL_SEARCH_POI, {q:ob.prev().val()}, function(ret){
			if (ret.status == SUCCESS_STATUS) {
				var prev = '<select style="height:190px;width:300px;" multiple="multiple" onclick="_searchSelect(this)" name="'+name+'">';
				prev += ret.data;
				prev += '</select>';
				ob.html('取消');
				ob.prev().remove();
				ob.before(prev);
			} else {
				alert(ret.msg);
			}
			ob.prev().focus();
		}, 'json');
	} else {
		var prev = '<input type="text" class="input-large" name="'+name+'" />';
		ob.html('搜索');
		ob.prev().remove();
		ob.before(prev);
		ob.prev().focus();
	}
}

function _searchSelect(obj) {
	obj.multiple='';
	$(obj).css('height', '');
}

function checkboxAreaSelect(obj) {
	clk = $(obj).parent().find('input[type=checkbox]')[0];
	if (clk.checked) {
		clk.checked = false;
	} else {
		clk.checked = true;
	}
}

/**
 * 自定义排序
 */
function order(ord) {
	var tag = '?';
	var url = window.location.href;
	if (url.indexOf('&') > 0) {
		tag = '&';
	}
	var newurl = url.replace(/ord=\d/, ord);
	if (url.indexOf('ord=') > 0) {
		window.location.href = newurl;
	} else {
		window.location.href = url + tag + ord;
	}
}

function getRadioValue(objs) {
	var ret = '';
	objs.each(function(){
		if (this.checked) {
			ret = this.value
			return;
		}
	});

	return ret;
}

function formatJson(json, options) {
	var reg = null,
		formatted = '',
		pad = 0,
	//PADDING = '    '; // one can also use '\t' or a different number of spaces
		PADDING = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',//缩进
		BR = '<br>';//默认 \r\n

	// optional settings
	options = options || {};
	// remove newline where '{' or '[' follows ':'
	options.newlineAfterColonIfBeforeBraceOrBracket = (options.newlineAfterColonIfBeforeBraceOrBracket === true) ? true : false;
	// use a space after a colon
	options.spaceAfterColon = (options.spaceAfterColon === false) ? false : true;

	// begin formatting...
	if (typeof json !== 'string') {
		// make sure we start with the JSON as a string
		json = JSON.stringify(json);
	} else {
		// is already a string, so parse and re-stringify in order to remove extra whitespace
		json = JSON.parse(json);
		json = JSON.stringify(json);
	}

	// add newline before and after curly braces
	reg = /([\{\}])/g;
	json = json.replace(reg, '\r\n$1\r\n');

	// add newline before and after square brackets
	reg = /([\[\]])/g;
	json = json.replace(reg, '\r\n$1\r\n');

	// add newline after comma
	reg = /(\,)/g;
	json = json.replace(reg, '$1\r\n');

	// remove multiple newlines
	reg = /(\r\n\r\n)/g;
	json = json.replace(reg, '\r\n');

	// remove newlines before commas
	reg = /\r\n\,/g;
	json = json.replace(reg, ',');

	// optional formatting...
	if (!options.newlineAfterColonIfBeforeBraceOrBracket) {
		reg = /\:\r\n\{/g;
		json = json.replace(reg, ':{');
		reg = /\:\r\n\[/g;
		json = json.replace(reg, ':[');
	}
	if (options.spaceAfterColon) {
		reg = /\:/g;
		json = json.replace(reg, ': ');
	}

	$.each(json.split('\r\n'), function(index, node) {
		var i = 0,
			indent = 0,
			padding = '';

		if (node.match(/\{$/) || node.match(/\[$/)) {
			indent = 1;
		} else if (node.match(/\}/) || node.match(/\]/)) {
			if (pad !== 0) {
				pad -= 1;
			}
		} else {
			indent = 0;
		}

		for (i = 0; i < pad; i++) {
			padding += PADDING;
		}

		formatted += padding + node + BR;
		pad += indent;
	});

	return formatted;
}

function setRowspan(trs, index) {
	if (trs.length == 0) {
		return;
	}

	var firstTd = '';//第一个
	var repeatCount = 0;
	trs.each(function(){
		var curTd = $(this).find('td').eq(index);
		if (firstTd != '' && curTd.html() != firstTd.html()) {
			firstTd.attr('rowspan', repeatCount);

			repeatCount = 1;
			firstTd = curTd;
		} else {
			if (firstTd == '') {
				firstTd = curTd;
			} else {
				curTd.remove();
			}
			repeatCount++;
		}
	});
	firstTd.attr('rowspan', repeatCount);
}