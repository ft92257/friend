;(function($){

    $.setRegional = function(key, value){
        if (!$.regional) $.regional = {};
        $.regional[key] = value;
    };

    $.fn.extend({
        /**
         * @param {Object} op: {type:GET/POST, url:ajax请求地址, data:ajax请求参数列表, callback:回调函数 }
         */
        ajaxUrl: function(op){
            var $this = $(this);

            $this.trigger(MOB.eventType.pageClear);

            $.ajax({
                type: op.type || 'GET',
                url: op.url,
                data: op.data,
                cache: false,
                success: function(response){
                    var json = MOB.jsonEval(response);
                    if (json[MOB.keys.statusCode]==MOB.statusCode.error){
                        if (json[MOB.keys.message]) alertMsg.error(json[MOB.keys.message]);
                    } else {
                        /*if(op.d){
                         op.d.content(response);



                         $("#content\\:"+op.id).initUI();
                         setTimeout(function(){op.d.showModal();},100);
                         if ($.isFunction(op.callback)) op.callback(response);
                         }else{
                         $this.html(response).initUI();
                         if ($.isFunction(op.callback)) op.callback(response);
                         }*/
                        $this.html(response).initUI();
                        if ($.isFunction(op.callback)) op.callback(response);

                    }

                    if (json[MOB.keys.statusCode]==MOB.statusCode.timeout){
                        if ($.pdialog) $.pdialog.checkTimeout();
                        if (navTab) navTab.checkTimeout();

                        alertMsg.error(json[MOB.keys.message] || MOB.msg("sessionTimout"), {okCall:function(){
                            MOB.loadLogin();
                        }});
                    }

                },
                error: MOB.ajaxError,
                statusCode: {
                    503: function(xhr, ajaxOptions, thrownError) {
                        alert(MOB.msg("statusCode_503") || thrownError);
                    }
                }
            });
        },
        loadUrl: function(url,data,callback){
            $(this).ajaxUrl({url:url, data:data, callback:callback});
        },
        initUI: function(){
            return this.each(function(){
                //if($.isFunction(initUI)) initUI(this);
                MOB.initUI(this);
            });
        },
        /**
         * adjust component inner reference box height
         * @param {Object} refBox: reference box jQuery Obj
         */
        layoutH: function($refBox){
            return this.each(function(){
                var $this = $(this);
                if (! $refBox) $refBox = $this.parents("div.layoutBox:first");
                var iRefH = $refBox.height();
                var iLayoutH = parseInt($this.attr("layoutH"));
                var iH = iRefH - iLayoutH > 50 ? iRefH - iLayoutH : 50;

                if ($this.isTag("table")) {
                    $this.removeAttr("layoutH").wrap('<div layoutH="'+iLayoutH+'" style="overflow:auto;height:'+iH+'px"></div>');
                } else {
                    $this.height(iH).css("overflow","auto");
                }
            });
        },
        hoverClass: function(className, speed){
            var _className = className || "hover";
            return this.each(function(){
                var $this = $(this), mouseOutTimer;
                $this.hover(function(){
                    if (mouseOutTimer) clearTimeout(mouseOutTimer);
                    $this.addClass(_className);
                },function(){
                    mouseOutTimer = setTimeout(function(){$this.removeClass(_className);}, speed||10);
                });
            });
        },
        focusClass: function(className){
            var _className = className || "textInputFocus";
            return this.each(function(){
                $(this).focus(function(){
                    $(this).addClass(_className);
                }).blur(function(){
                    $(this).removeClass(_className);
                });
            });
        },
        inputAlert: function(){
            return this.each(function(){

                var $this = $(this);

                function getAltBox(){
                    return $this.parent().find("label.alt");
                }
                function altBoxCss(opacity){
                    var position = $this.position();
                    return {
                        width:$this.width(),
                        top:position.top+'px',
                        left:position.left +'px',
                        opacity:opacity || 1
                    };
                }
                if (getAltBox().size() < 1) {
                    if (!$this.attr("id")) $this.attr("id", $this.attr("name") + "_" +Math.round(Math.random()*10000));
                    var $label = $('<label class="alt" for="'+$this.attr("id")+'">'+$this.attr("alt")+'</label>').appendTo($this.parent());

                    $label.css(altBoxCss(1));
                    if ($this.val()) $label.hide();
                }

                $this.focus(function(){
                    getAltBox().css(altBoxCss(0.3));
                }).blur(function(){
                    if (!$(this).val()) getAltBox().show().css("opacity",1);
                }).keydown(function(){
                    getAltBox().hide();
                });
            });
        },
        isTag:function(tn) {
            if(!tn) return false;
            return $(this)[0].tagName.toLowerCase() == tn?true:false;
        },
        /**
         * 判断当前元素是否已经绑定某个事件
         * @param {Object} type
         */
        isBind:function(type) {
            var _events = $(this).data("events");
            return _events && type && _events[type];
        },
        /**
         * 输出firebug日志
         * @param {Object} msg
         */
        log:function(msg){
            return this.each(function(){
                if (console) console.log("%s: %o", msg, this);
            });
        }
    });

    /**
     * 扩展String方法
     */
    $.extend(String.prototype, {
        isPositiveInteger:function(){
            return (new RegExp(/^[1-9]\d*$/).test(this));
        },
        isInteger:function(){
            return (new RegExp(/^\d+$/).test(this));
        },
        isNumber: function(value, element) {
            return (new RegExp(/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/).test(this));
        },
        trim:function(){
            return this.replace(/(^\s*)|(\s*$)|\r|\n/g, "");
        },
        startsWith:function (pattern){
            return this.indexOf(pattern) === 0;
        },
        endsWith:function(pattern) {
            var d = this.length - pattern.length;
            return d >= 0 && this.lastIndexOf(pattern) === d;
        },
        replaceSuffix:function(index){
            return this.replace(/\[[0-9]+\]/,'['+index+']').replace('#index#',index);
        },
        trans:function(){
            return this.replace(/&lt;/g, '<').replace(/&gt;/g,'>').replace(/&quot;/g, '"');
        },
        encodeTXT: function(){
            return (this).replaceAll('&', '&amp;').replaceAll("<","&lt;").replaceAll(">", "&gt;").replaceAll(" ", "&nbsp;");
        },
        replaceAll:function(os, ns){
            return this.replace(new RegExp(os,"gm"),ns);
        },
        replaceTm:function($data){
            if (!$data) return this;
            return this.replace(RegExp("({[A-Za-z_]+[A-Za-z0-9_]*})","g"), function($1){
                return $data[$1.replace(/[{}]+/g, "")];
            });
        },
        replaceTmById:function(_box){
            var $parent = _box || $(document);
            return this.replace(RegExp("({[A-Za-z_]+[A-Za-z0-9_]*})","g"), function($1){
                var $input = $parent.find("#"+$1.replace(/[{}]+/g, ""));
                return $input.val() ? $input.val() : $1;
            });
        },
        isFinishedTm:function(){
            return !(new RegExp("{[A-Za-z_]+[A-Za-z0-9_]*}").test(this));
        },
        skipChar:function(ch) {
            if (!this || this.length===0) {return '';}
            if (this.charAt(0)===ch) {return this.substring(1).skipChar(ch);}
            return this;
        },
        isValidPwd:function() {
            return (new RegExp(/^([_]|[a-zA-Z0-9]){6,32}$/).test(this));
        },
        isValidMail:function(){
            return(new RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(this.trim()));
        },
        isSpaces:function() {
            for(var i=0; i<this.length; i+=1) {
                var ch = this.charAt(i);
                if (ch!=' '&& ch!="\n" && ch!="\t" && ch!="\r") {return false;}
            }
            return true;
        },
        isPhone:function() {
            return (new RegExp(/(^([0-9]{3,4}[-])?\d{3,8}(-\d{1,6})?$)|(^\([0-9]{3,4}\)\d{3,8}(\(\d{1,6}\))?$)|(^\d{3,8}$)/).test(this));
        },
        isUrl:function(){
            return (new RegExp(/^[a-zA-z]+:\/\/([a-zA-Z0-9\-\.]+)([-\w .\/?%&=:]*)$/).test(this));
        },
        isExternalUrl:function(){
            return this.isUrl() && this.indexOf("://"+document.domain) == -1;
        }
    });
})(jQuery);


(function(){
    var MONTH_NAMES=new Array('January','February','March','April','May','June','July','August','September','October','November','December','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    var DAY_NAMES=new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sun','Mon','Tue','Wed','Thu','Fri','Sat');
    function LZ(x) {return(x<0||x>9?"":"0")+x}

    /**
     * formatDate (date_object, format)
     * Returns a date in the output format specified.
     * The format string uses the same abbreviations as in parseDate()
     * @param {Object} date
     * @param {Object} format
     */
    function formatDate(date,format) {
        format=format+"";
        var result="";
        var i_format=0;
        var c="";
        var token="";
        var y=date.getYear()+"";
        var M=date.getMonth()+1;
        var d=date.getDate();
        var E=date.getDay();
        var H=date.getHours();
        var m=date.getMinutes();
        var s=date.getSeconds();
        var yyyy,yy,MMM,MM,dd,hh,h,mm,ss,ampm,HH,H,KK,K,kk,k;
        // Convert real date parts into formatted versions
        var value={};
        if (y.length < 4) {y=""+(y-0+1900);}
        value["y"]=""+y;
        value["yyyy"]=y;
        value["yy"]=y.substring(2,4);
        value["M"]=M;
        value["MM"]=LZ(M);
        value["MMM"]=MONTH_NAMES[M-1];
        value["NNN"]=MONTH_NAMES[M+11];
        value["d"]=d;
        value["dd"]=LZ(d);
        value["E"]=DAY_NAMES[E+7];
        value["EE"]=DAY_NAMES[E];
        value["H"]=H;
        value["HH"]=LZ(H);
        if (H==0){value["h"]=12;}
        else if (H>12){value["h"]=H-12;}
        else {value["h"]=H;}
        value["hh"]=LZ(value["h"]);
        if (H>11){value["K"]=H-12;} else {value["K"]=H;}
        value["k"]=H+1;
        value["KK"]=LZ(value["K"]);
        value["kk"]=LZ(value["k"]);
        if (H > 11) { value["a"]="PM"; }
        else { value["a"]="AM"; }
        value["m"]=m;
        value["mm"]=LZ(m);
        value["s"]=s;
        value["ss"]=LZ(s);
        while (i_format < format.length) {
            c=format.charAt(i_format);
            token="";
            while ((format.charAt(i_format)==c) && (i_format < format.length)) {
                token += format.charAt(i_format++);
            }
            if (value[token] != null) { result += value[token]; }
            else { result += token; }
        }
        return result;
    }

    function _isInteger(val) {
        return (new RegExp(/^\d+$/).test(val));
    }
    function _getInt(str,i,minlength,maxlength) {
        for (var x=maxlength; x>=minlength; x--) {
            var token=str.substring(i,i+x);
            if (token.length < minlength) { return null; }
            if (_isInteger(token)) { return token; }
        }
        return null;
    }

    /**
     * parseDate( date_string , format_string )
     *
     * This function takes a date string and a format string. It matches
     * If the date string matches the format string, it returns the date.
     * If it does not match, it returns 0.
     * @param {Object} val
     * @param {Object} format
     */
    function parseDate(val,format) {
        val=val+"";
        format=format+"";
        var i_val=0;
        var i_format=0;
        var c="";
        var token="";
        var token2="";
        var x,y;
        var now=new Date(1900,0,1);
        var year=now.getYear();
        var month=now.getMonth()+1;
        var date=1;
        var hh=now.getHours();
        var mm=now.getMinutes();
        var ss=now.getSeconds();
        var ampm="";

        while (i_format < format.length) {
            // Get next token from format string
            c=format.charAt(i_format);
            token="";
            while ((format.charAt(i_format)==c) && (i_format < format.length)) {
                token += format.charAt(i_format++);
            }
            // Extract contents of value based on format token
            if (token=="yyyy" || token=="yy" || token=="y") {
                if (token=="yyyy") { x=4;y=4; }
                if (token=="yy")   { x=2;y=2; }
                if (token=="y")    { x=2;y=4; }
                year=_getInt(val,i_val,x,y);
                if (year==null) { return 0; }
                i_val += year.length;
                if (year.length==2) {
                    if (year > 70) { year=1900+(year-0); }
                    else { year=2000+(year-0); }
                }
            } else if (token=="MMM"||token=="NNN"){
                month=0;
                for (var i=0; i<MONTH_NAMES.length; i++) {
                    var month_name=MONTH_NAMES[i];
                    if (val.substring(i_val,i_val+month_name.length).toLowerCase()==month_name.toLowerCase()) {
                        if (token=="MMM"||(token=="NNN"&&i>11)) {
                            month=i+1;
                            if (month>12) { month -= 12; }
                            i_val += month_name.length;
                            break;
                        }
                    }
                }
                if ((month < 1)||(month>12)){return 0;}
            } else if (token=="EE"||token=="E"){
                for (var i=0; i<DAY_NAMES.length; i++) {
                    var day_name=DAY_NAMES[i];
                    if (val.substring(i_val,i_val+day_name.length).toLowerCase()==day_name.toLowerCase()) {
                        i_val += day_name.length;
                        break;
                    }
                }
            } else if (token=="MM"||token=="M") {
                month=_getInt(val,i_val,token.length,2);
                if(month==null||(month<1)||(month>12)){return 0;}
                i_val+=month.length;
            } else if (token=="dd"||token=="d") {
                date=_getInt(val,i_val,token.length,2);
                if(date==null||(date<1)||(date>31)){return 0;}
                i_val+=date.length;
            } else if (token=="hh"||token=="h") {
                hh=_getInt(val,i_val,token.length,2);
                if(hh==null||(hh<1)||(hh>12)){return 0;}
                i_val+=hh.length;
            } else if (token=="HH"||token=="H") {
                hh=_getInt(val,i_val,token.length,2);
                if(hh==null||(hh<0)||(hh>23)){return 0;}
                i_val+=hh.length;}
            else if (token=="KK"||token=="K") {
                hh=_getInt(val,i_val,token.length,2);
                if(hh==null||(hh<0)||(hh>11)){return 0;}
                i_val+=hh.length;
            } else if (token=="kk"||token=="k") {
                hh=_getInt(val,i_val,token.length,2);
                if(hh==null||(hh<1)||(hh>24)){return 0;}
                i_val+=hh.length;hh--;
            } else if (token=="mm"||token=="m") {
                mm=_getInt(val,i_val,token.length,2);
                if(mm==null||(mm<0)||(mm>59)){return 0;}
                i_val+=mm.length;
            } else if (token=="ss"||token=="s") {
                ss=_getInt(val,i_val,token.length,2);
                if(ss==null||(ss<0)||(ss>59)){return 0;}
                i_val+=ss.length;
            } else if (token=="a") {
                if (val.substring(i_val,i_val+2).toLowerCase()=="am") {ampm="AM";}
                else if (val.substring(i_val,i_val+2).toLowerCase()=="pm") {ampm="PM";}
                else {return 0;}
                i_val+=2;
            } else {
                if (val.substring(i_val,i_val+token.length)!=token) {return 0;}
                else {i_val+=token.length;}
            }
        }
        // If there are any trailing characters left in the value, it doesn't match
        if (i_val != val.length) { return 0; }
        // Is date valid for month?
        if (month==2) {
            // Check for leap year
            if ( ( (year%4==0)&&(year%100 != 0) ) || (year%400==0) ) { // leap year
                if (date > 29){ return 0; }
            } else { if (date > 28) { return 0; } }
        }
        if ((month==4)||(month==6)||(month==9)||(month==11)) {
            if (date > 30) { return 0; }
        }
        // Correct hours value
        if (hh<12 && ampm=="PM") { hh=hh-0+12; }
        else if (hh>11 && ampm=="AM") { hh-=12; }
        return new Date(year,month-1,date,hh,mm,ss);
    }

    Date.prototype.formatDate = function(dateFmt) {
        return formatDate(this, dateFmt);
    };
    String.prototype.parseDate = function(dateFmt) {
        if (this.length < dateFmt.length) {
            dateFmt = dateFmt.slice(0,this.length);
        }
        return parseDate(this, dateFmt);
    };

    /**
     * replaceTmEval("{1+2}-{2-1}")
     */
    function replaceTmEval(data){
        return data.replace(RegExp("({[A-Za-z0-9_+-]*})","g"), function($1){
            return eval('(' + $1.replace(/[{}]+/g, "") + ')');
        });
    }
    /**
     * dateFmt:%y-%M-%d
     * %y-%M-{%d+1}
     * ex: new Date().formatDateTm('%y-%M-{%d-1}')
     * 	new Date().formatDateTm('2012-1')
     */
    Date.prototype.formatDateTm = function(dateFmt) {
        var y = this.getFullYear();
        var m = this.getMonth()+1;
        var d = this.getDate();

        var sDate = dateFmt.replaceAll("%y",y).replaceAll("%M",m).replaceAll("%d",d);
        sDate = replaceTmEval(sDate);

        var _y=1900, _m=0, _d=1;
        var aDate = sDate.split('-');

        if (aDate.length > 0) _y = aDate[0];
        if (aDate.length > 1) _m = aDate[1]-1;
        if (aDate.length > 2) _d = aDate[2];

        return new Date(_y,_m,_d).formatDate('yyyy-MM-dd');
    };

})();

//树状菜单
(function(){
    $.fn.mobTree = function(){
        var _this = $(this);
        _this.find("dt").each(function(){
            $(this).click(function(){
                var dd = $(this).next("dd");
                if(dd && dd.is(":hidden")){
                    dd.slideDown(300);
                    $(this).find("b").removeClass("caret-s");
                }else{
                    dd.slideUp(300);
                    $(this).find("b").addClass("caret-s");
                }
            });
        });
        _this.find('li').each(function(){
            $(this).click(function(){
                _this.find("li a").removeClass("cur");
                $(this).children("a").addClass("cur");
            });
        });
    }
})(jQuery);

//分页插件
(function(){
    $.fn.mobPaging =function(){
        var _this = $(this);
        var defaults = {
            targetType:'navTab',
            totalCount:0,
            numPerPage:20,
            pageNumShow:10,
            currentPage:1
        };
        var options = MOB.jsonEval(_this.data('opt'));
        var opt = $.extend({},defaults,options);
        opt.countPage = Math.ceil(opt.totalCount/opt.numPerPage);

        var datas = opt.data ? opt.data : {};


        _init();
        function _init(){
            if(opt.totalCount<1) return false;
            _createPaging();
            _bindEvent();
        }

        function _createPaging(){
            var pagingStr = '';
            pagingStr ='<span class="disable">共 '+opt.totalCount+' 个</span>';
            if(opt.currentPage>1){
                pagingStr +='<a href="javascript:;" class="paging-prev">上一页</a>';
            }


            //数字显示
            if(opt.pageNumShow>0){
                opt.pageNumStart = Math.floor(opt.pageNumShow / 2);
                if (opt.pageNumStart < 1) opt.pageNumStart = 1;
                opt.pageNumEnd = opt.pageNumStart + opt.pageNumShow - 1;
                if (opt.pageNumEnd > opt.countPage) opt.pageNumEnd = opt.countPage;

                if (opt.pageNumShow > opt.countPage) { //当显示的数字个数大于总页数
                    opt.pageNumStart = 1;
                    opt.pageNumEnd = opt.countPage;
                } else if (opt.pageNumEnd == opt.countPage) {
                    opt.pageNumStart = opt.countPage - (opt.pageNumShow - 1);
                }
                var numStr = '';
                for (var i = opt.pageNumStart; i <= opt.pageNumEnd; i++) {
                    numStr += i == opt.currentPage ? '<span class="cur">' + i + '</span>' : '<a href="javascript:;" class="paging-number">' + i + '</a>';
                }
                pagingStr += numStr;
            }


            if(opt.currentPage<opt.countPage){
                pagingStr +='<a href="javascript:;" class="paging-next">下一页</a>';
            }

            pagingStr +='<span class="disable">'+opt.currentPage+' / '+opt.countPage+'</span>';
            _this.append(pagingStr);
        }

        function _bindEvent(){
            _this.children("a").each(function(){
                var p=1;
                var cls = $(this).attr('class');
                switch(cls){
                    case 'paging-prev':
                        p = opt.currentPage-1;
                        break;
                    case 'paging-next':
                        p = opt.currentPage+1;
                        break;
                    case 'paging-number':
                        p =$(this).text();
                        break;

                }

                $(this).click(function(){
                    datas.p = p;
                    if(opt.targetType=="navTab"){
                        navTab.reload('', {data:datas});
                    }else if(opt.targetType == "dialog"){
                        $.pdialog.reload(opt.url,{data:datas});
                        /*$panel.ajaxUrl({
                         type:opt.type, url:opt.url, data:opt.data, callback:function(){
                         MOB.dialogBack($panel,d);
                         }
                         });*/
                    }

                });
            });
        }

    }
})(jQuery);

/*提示框*/
var alertMsg = {
    _options:{
        width:350,
        title:'提示',
        okValue: '确定',
        ok: function () {}

    },
    error:function(opt){
        opt.alertType='error';
        opt.title = opt.title || '错误';
        alertMsg._createDialog(opt);
    },
    warn:function(opt){
        opt.alertType='warn';
        alertMsg._createDialog(opt);
    },
    success:function(opt){
        opt.alertType='success';
        opt.title = opt.title || '成功';
        alertMsg._createDialog(opt);
    },
    confirm:function(opt){
        opt.alertType = 'confirm';
        opt.title=opt.title || "确认提示";
        opt.cancelValue = opt.cancelValue || '取消';
        opt.content = '<h2>'+ opt.content +'</h2>';
        opt.cancel = opt.cancel || function(){};
        opt.ok= opt.ok || function(){ return true; };
        var opts = $.extend(alertMsg._options, opt);




        alertMsg._createDialog(opts);

    },
    _createInnerHtml:function(opt){
        var html = '<div class="dialog-warp"><div class="dialog_body"><div class="dialog-m text-tips"><dl><dt>';
        switch(opt.alertType){
            case 'success':
                html +='<i class="system-ok"></i>';
                break;
            case 'error':
                html +='<i class="system-error"></i>';
                break;
            case 'warn':
                html +='<i class="system-warning"></i>';
                break;
            case 'confirm':
                html +='<i class="system-warning"></i>';
                break;
        }

        html +='</dt><dd>'+opt.content;
        html +='</dd></dl></div></div></div>';
        return html;
    },
    _createDialog:function(opt){
        var opt = $.extend(alertMsg._options, opt);
        var content = alertMsg._createInnerHtml(opt);
        opt.content = content;
        var d = dialog(opt);
        d.showModal();
    }
};

/*全选*/
(function($){
    $.fn.extend({

        checkboxCtrl: function(parent){
            return this.each(function(){
                var $trigger = $(this);
                var $checkbox = $(this).parent("span.z_checkbox");
                $checkbox.click(function(){
                    var group = $trigger.attr("group");
                    if ($trigger.is(":checked")) {
                        var type = $trigger.is(":checked") ? "all" : "none";
                        if (group) $.checkbox.select(group, type, parent);
                    } else {
                        if (group) $.checkbox.select(group, $trigger.attr("selectType") || "none", parent);
                    }

                });
            });
        }
    });

    $.checkbox = {
        selectAll: function(_name, _parent){
            this.select(_name, "all", _parent);
        },
        unSelectAll: function(_name, _parent){
            this.select(_name, "none", _parent);
        },
        selectInvert: function(_name, _parent){
            this.select(_name, "invert", _parent);
        },
        select: function(_name, _type, _parent){
            $parent = $(_parent || document);
            $checkboxLi = $parent.find(":checkbox[name='"+_name+"']");
            switch(_type){
                case "invert":
                    $checkboxLi.each(function(){
                        $checkbox = $(this);
                        $checkbox.attr('checked', !$checkbox.is(":checked"));
                    });
                    break;
                case "none":
                    $checkboxLi.attr('checked', false);
                    $checkboxLi.prev("i").removeClass("system-checkbox_cur");
                    break;
                default:
                    $checkboxLi.prev("i").addClass("system-checkbox_cur");
                    $checkboxLi.attr('checked', true);
                    break;
            }
        }
    };
})(jQuery);

/*(function($){
 $.fn.extend({

 checkboxCtrl: function(parent){
 return this.each(function(){
 var $trigger = $(this);
 $trigger.click(function(){
 var group = $trigger.attr("group");
 if ($trigger.is(":checkbox")) {
 var type = $trigger.is(":checked") ? "all" : "none";
 if (group) $.checkbox.select(group, type, parent);
 } else {
 if (group) $.checkbox.select(group, $trigger.attr("selectType") || "all", parent);
 }

 });
 });
 }
 });

 $.checkbox = {
 selectAll: function(_name, _parent){
 this.select(_name, "all", _parent);
 },
 unSelectAll: function(_name, _parent){
 this.select(_name, "none", _parent);
 },
 selectInvert: function(_name, _parent){
 this.select(_name, "invert", _parent);
 },
 select: function(_name, _type, _parent){
 $parent = $(_parent || document);
 $checkboxLi = $parent.find(":checkbox[name='"+_name+"']");
 switch(_type){
 case "invert":
 $checkboxLi.each(function(){
 $checkbox = $(this);
 $checkbox.attr('checked', !$checkbox.is(":checked"));
 });
 break;
 case "none":
 $checkboxLi.attr('checked', false);
 break;
 default:
 $checkboxLi.attr('checked', true);
 break;
 }
 }
 };
 })(jQuery);*/


/*单选*/
$.fn.extend({

    radioBox: function(parent){
        return this.each(function(){
            var $trigger = $(this);
            var checked = $trigger.is(":checked");
            $trigger.hide();
            $trigger.wrap('<cite class="z_radio"></cite>');
            if(checked){
                var mtRadio = $('<i class="system-radio_cur"></i>');
            }else{
                var mtRadio = $('<i class="system-radio"></i>');
            }

            $trigger.before(mtRadio);
            mtRadio.click(function(){
                var checked = $trigger.is(":checked");
                if(!checked){
                    // 获取name
                    var input = $(this).next("input");
                    var name=input.attr("name");
                    //取同名name的i
                    var obj = $("input[name="+name+"]");
                    obj.prev("i").removeClass("system-radio_cur").addClass("system-radio");
                    obj.attr("checked",false);
                    $(this).addClass("system-radio_cur");
                    input.attr("checked",true);
                }

            });
        });
    }
});

/*多选*/
$.fn.extend({

    checkBox: function(parent){
        return this.each(function(){
            var $trigger = $(this);
            var $parent = $trigger.parent();
            var checked = $trigger.is(":checked");
            $trigger.hide();
            var $wrap = $('<span class="z_checkbox"></span>');
            $parent.wrapInner($wrap);
            if(checked){
                var mtCheckBox = '<i class="system-checkbox system-checkbox_cur"></i>';
            }else{
                var mtCheckBox = '<i class="system-checkbox"></i>';
            }

            $trigger.before(mtCheckBox);
            //重新得到wrap
            var checkbox  =$trigger.parent(".z_checkbox");
            checkbox.click(function(){
                //var checked = $($wrap.children("input[type=checkbox]").eq(0)).is(":checked");
                var $trigger = $(this).find("input[type=checkbox]");
                var checked = $trigger.is(":checked");
                if(!checked){
                    $(this).find(".system-checkbox").addClass("system-checkbox_cur");
                    $trigger.attr("checked",true);
                }else{

                    $(this).find(".system-checkbox").removeClass("system-checkbox_cur");

                    $trigger.attr("checked",false);
                }

            });
        });
    }
});



/*;(function($){
    $.fn.mobTreeList = function(){
        var defautls = {
            expand:false,//第一个树菜单是否展开
            collapse:false,//是否全部展开
            icon:false,//在树前是否加上icon
            //checkbox:false,//在树前是否加上复选框
            //radiobox:false,//在树前是否加上单选框
            speed:100//打开速度
        };
        var opts = $.extend({}, defautls, MOB.jsonEval(this.data('opt')));
        return this.each(function(){
            var _this = $(this);
            /!*if(!opts.collapse){
                if(opts.expand){
                    _this.find("ul:gt(0)").hide();
                }else{
                    _this.find("ul").hide();                }
            }*!/


            genTree(_this);

        });
        function genTree(ele){
            var $tr = ele.find('tr:gt(0)');
            $tr.hide();
            var opt ;
            var pArr = [];
            var optArr = [];
            for(var i=0; i<$tr.length; i++){
                var _this = $tr.eq(i);
                opt={};
                opt = MOB.jsonEval(_this.data('opt'));
                if(typeof(opt) == "object"){
                    optArr.push(opt);
                    if(opt.parent>0){
                        pArr.push(opt.parent);//得到所有父节点
                    }else{
                        _this.show();
                    }



                }else{
                    optArr.push('');
                }
            }

            //加标签
            for(var i=0; i<$tr.length; i++){
                var $this = $tr.eq(i);
                if(optArr[i] !=''){
                    if(pArr.indexOf(optArr[i].id) !=-1){
                        var icon=$('<i class="system-classtree-on">+</i>');
                        icon.click=function(){
                            debug(1);
                        };
                        $this.find("td:eq(1)").prepend(icon);
                    }

                }

            }
        }
    }

})(jQuery);*/
