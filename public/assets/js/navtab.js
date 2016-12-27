/**
 * navTab插件
 */
var navTab = {
    componentBox: null, // tab component. contain tabBox, prevBut, nextBut, panelBox
    _tabBox: null,
    _prevBut: null,
    _nextBut: null,
    _panelBox: null,
    _moreBut:null,
    _moreBox:null,
    _currentIndex: 0,

    _op: {id:"navTab", stTabBox:".tags-list", stPanelBox:".navTab-panel", mainTabId:"main", close$:"i.system-close1", prevClass:"tabsLeft", nextClass:"tabsRight", stMore:".tags-more>a", stMoreLi:"ul.tabsMoreList"},

    init: function(options){
        if ($.History) $.History.init(".ERP-B");
        var $this = this;
        $.extend(this._op, options);
        this.componentBox = $("#"+this._op.id);
        this._tabBox = $(this._op.stTabBox);
        this._panelBox = this.componentBox.find(this._op.stPanelBox);
        this._prevBut = this.componentBox.find("."+this._op.prevClass);
        this._nextBut = this.componentBox.find("."+this._op.nextClass);
        this._moreBut = $(this._op.stMore);
        this._moreBox = $(this._op.stMoreLi);
        this._prevBut.click(function(event) {$this._scrollPrev()});
        this._nextBut.click(function(event) {$this._scrollNext()});
        this._moreBut.click(function(){
            $this._moreBox.show();
            return false;
        });
        $(document).click(function(){$this._moreBox.hide()});

        //this._contextmenu(this._tabBox);
        //this._contextmenu(this._getTabs());

        this._init();
        this._ctrlScrollBut();
    },
    _init: function(){
        var $this = this;
        this._getTabs().each(function(iTabIndex){
            $(this).unbind("click").click(function(event){

                $this._switchTab(iTabIndex);
            });
            $(this).find(navTab._op.close$).unbind("click").click(function(){
                $this._closeTab(iTabIndex);
            });
        });
        this._getMoreLi().each(function(iTabIndex){
            $(this).find(">i.system-close1").unbind("click").click(function(event){
                $this._closeTab(iTabIndex);
            });
            $(this).find(">a").unbind("click").click(function(event){
                $this._locationHash(iTabIndex);
                $this._switchTab(iTabIndex);

            });
        });

        this._switchTab(this._currentIndex);
    },
    _contextmenu:function($obj){ // navTab右键菜单
        var $this = this;
        $obj.contextMenu('navTabCM', {
            bindings:{
                reload:function(t,m){
                    $this._reload(t, true);
                },
                closeCurrent:function(t,m){
                    var tabId = t.attr("tabid");
                    if (tabId) $this.closeTab(tabId);
                    else $this.closeCurrentTab();
                },
                closeOther:function(t,m){
                    var index = $this._indexTabId(t.attr("tabid"));
                    $this._closeOtherTab(index > 0 ? index : $this._currentIndex);
                },
                closeAll:function(t,m){
                    $this.closeAllTab();
                }
            },
            ctrSub:function(t,m){
                var mReload = m.find("[rel='reload']");
                var mCur = m.find("[rel='closeCurrent']");
                var mOther = m.find("[rel='closeOther']");
                var mAll = m.find("[rel='closeAll']");
                var $tabLi = $this._getTabs();
                if ($tabLi.size() < 2) {
                    mCur.addClass("disabled");
                    mOther.addClass("disabled");
                    mAll.addClass("disabled");
                }
                if ($this._currentIndex == 0 || t.attr("tabid") == $this._op.mainTabId) {
                    mCur.addClass("disabled");
                    mReload.addClass("disabled");
                } else if ($tabLi.size() == 2) {
                    mOther.addClass("disabled");
                }

            }
        });
    },

    _getTabs: function(){
        return this._tabBox.find("> li");
    },
    _getPanels: function(){
        return this._panelBox.find("> div");
    },
    _getMoreLi: function(){
        return this._moreBox.find("> li");
    },
    _getTab: function(tabid){
        var index = this._indexTabId(tabid);
        if (index >= 0) return this._getTabs().eq(index);
    },
    getPanel: function(tabid){
        var index = this._indexTabId(tabid);
        if (index >= 0) return this._getPanels().eq(index);
    },
    _getTabsW: function(iStart, iEnd){
        return this._tabsW(this._getTabs().slice(iStart, iEnd));
    },
    _tabsW:function($tabs){
        var iW = 0;
        $tabs.each(function(){
            iW += $(this).outerWidth(true);
        });
        return iW;
    },
    _indexTabId: function(tabid){
        if (!tabid) return -1;
        var iOpenIndex = -1;
        this._getTabs().each(function(index){
            if ($(this).attr("tabid") == tabid){iOpenIndex = index; return;}
        });
        return iOpenIndex;
    },
    _getLeft: function(){
        return this._tabBox.position().left;
    },
    _getScrollBarW: function(){
        return this.componentBox.width()-55;
    },

    _visibleStart: function(){
        var iLeft = this._getLeft(), iW = 0;
        var $tabs = this._getTabs();
        for (var i=0; i<$tabs.size(); i++){
            if (iW + iLeft >= 0) return i;
            iW += $tabs.eq(i).outerWidth(true);
        }
        return 0;
    },
    _visibleEnd: function(){
        var iLeft = this._getLeft(), iW = 0;
        var $tabs = this._getTabs();
        for (var i=0; i<$tabs.size(); i++){
            iW += $tabs.eq(i).outerWidth(true);
            if (iW + iLeft > this._getScrollBarW()) return i;
        }
        return $tabs.size();
    },
    _scrollPrev: function(){
        var iStart = this._visibleStart();
        if (iStart > 0){
            this._scrollTab(-this._getTabsW(0, iStart-1));
        }
    },
    _scrollNext: function(){
        var iEnd = this._visibleEnd();
        if (iEnd < this._getTabs().size()){
            this._scrollTab(-this._getTabsW(0, iEnd+1) + this._getScrollBarW());
        }
    },
    _scrollTab: function(iLeft, isNext){
        var $this = this;
        this._tabBox.animate({ left: iLeft+'px' }, 200, function(){$this._ctrlScrollBut();});
    },
    _scrollCurrent: function(){ // auto scroll current tab
        var iW = this._tabsW(this._getTabs());
        if (iW <= this._getScrollBarW()){
            this._scrollTab(0);
        } else if (this._getLeft() < this._getScrollBarW() - iW){
            this._scrollTab(this._getScrollBarW()-iW);
        } else if (this._currentIndex < this._visibleStart()) {
            this._scrollTab(-this._getTabsW(0, this._currentIndex));
        } else if (this._currentIndex >= this._visibleEnd()) {
            this._scrollTab(this._getScrollBarW() - this._getTabs().eq(this._currentIndex).outerWidth(true) - this._getTabsW(0, this._currentIndex));
        }
    },
    _ctrlScrollBut: function(){
        var iW = this._tabsW(this._getTabs());
        if (this._getScrollBarW() > iW){
            this._prevBut.hide();
            this._nextBut.hide();
            this._tabBox.parent().removeClass("tabsPageHeaderMargin");
        } else {
            this._prevBut.show().removeClass("tabsLeftDisabled");
            this._nextBut.show().removeClass("tabsRightDisabled");
            this._tabBox.parent().addClass("tabsPageHeaderMargin");
            if (this._getLeft() >= 0){
                this._prevBut.addClass("tabsLeftDisabled");
            } else if (this._getLeft() <= this._getScrollBarW() - iW) {
                this._nextBut.addClass("tabsRightDisabled");
            }
        }
    },
    _loctionHash:function(iTabIndex){
        var $tab = this._getTabs().removeClass("selected").eq(iTabIndex).addClass("selected");
        var tabid = $tab.attr('tabid');
        if(tabid !='main') location.hash = $tab.attr('tabid') + '$' + $tab.attr('url');
    },
    _switchTab: function(iTabIndex){
        var $tab = this._getTabs().removeClass("selected").eq(iTabIndex).addClass("selected");

        if (MOB.ui.hideMode == 'offsets') {
            this._getPanels().css({position: 'absolute', top:'-100000px', left:'-100000px'}).eq(iTabIndex).css({position: '', top:'', left:''});
        } else {
            this._getPanels().hide().eq(iTabIndex).show();
        }

        this._getMoreLi().removeClass("selected").eq(iTabIndex).addClass("selected");

        //
        this._currentIndex = iTabIndex;

        this._scrollCurrent();
        this._reload($tab);
    },

    _closeTab: function(index, openTabid){

        this._getTabs().eq(index).remove();
        this._getPanels().eq(index).trigger(MOB.eventType.pageClear).remove();
        this._getMoreLi().eq(index).remove();
        if (this._currentIndex >= index) this._currentIndex--;

        if (openTabid) {
            var openIndex = this._indexTabId(openTabid);
            if (openIndex > 0) this._currentIndex = openIndex;
        }

        this._init();
        this._scrollCurrent();
        this._reload(this._getTabs().eq(this._currentIndex));
    },
    closeTab: function(tabid){
        var index = this._indexTabId(tabid);
        if (index > 0) { this._closeTab(index); }
    },
    closeCurrentTab: function(openTabid){ //openTabid 可以为空，默认关闭当前tab后，打开最后一个tab
        if (this._currentIndex > 0) {this._closeTab(this._currentIndex, openTabid);}
    },
    closeAllTab: function(){
        this._getTabs().filter(":gt(0)").remove();
        this._getPanels().filter(":gt(0)").trigger(MOB.eventType.pageClear).remove();
        this._getMoreLi().filter(":gt(0)").remove();
        this._currentIndex = 0;
        this._init();
        this._scrollCurrent();
    },
    _closeOtherTab: function(index){
        index = index || this._currentIndex;
        if (index > 0) {
            var str$ = ":eq("+index+")";
            this._getTabs().not(str$).filter(":gt(0)").remove();
            this._getPanels().not(str$).filter(":gt(0)").trigger(MOB.eventType.pageClear).remove();
            this._getMoreLi().not(str$).filter(":gt(0)").remove();
            this._currentIndex = 1;
            this._init();
            this._scrollCurrent();
        } else {
            this.closeAllTab();
        }
    },

    _loadUrlCallback: function($panel){
        $panel.find("[layoutH]").layoutH();
        $panel.find(":button.close").click(function(){
            navTab.closeCurrentTab();
        });
    },
    _reload: function($tab, flag){
        flag = flag || $tab.data("reloadFlag");
        var url = $tab.attr("url");
        if (flag && url) {
            $tab.data("reloadFlag", null);
            var $panel = this.getPanel($tab.attr("tabid"));

            if ($tab.hasClass("external")){
                navTab.openExternal(url, $panel);
            }else {
                //获取pagerForm参数
                var $pagerForm = $("#pagerForm", $panel);
                var args = $pagerForm.size()>0 ? $pagerForm.serializeArray() : {}
                $panel.loadUrl(url, args, function(){navTab._loadUrlCallback($panel);});
            }
        }
    },
    reloadFlag: function(tabid){
        var $tab = this._getTab(tabid);
        if ($tab){
            if (this._indexTabId(tabid) == this._currentIndex) this._reload($tab, true);
            else $tab.data("reloadFlag", 1);
        }
    },
    reload: function(url, options){
        var op = $.extend({data:{}, navTabId:"", callback:null}, options);
        var $tab = op.navTabId ? this._getTab(op.navTabId) : this._getTabs().eq(this._currentIndex);
        var $panel =  op.navTabId ? this.getPanel(op.navTabId) : this._getPanels().eq(this._currentIndex);

        if ($panel){
            if (!url) {
                url = $tab.attr("url");
            }
            if (url) {
                if ($tab.hasClass("external")) {
                    navTab.openExternal(url, $panel);
                } else {
                    if ($.isEmptyObject(op.data)) { //获取pagerForm参数
                        var $pagerForm = $("#pagerForm", $panel);
                        op.data = $pagerForm.size()>0 ? $pagerForm.serializeArray() : {}
                    }

                    $panel.ajaxUrl({
                        type:"POST", url:url, data:op.data, callback:function(response){
                            navTab._loadUrlCallback($panel);
                            if ($.isFunction(op.callback)) op.callback(response);
                        }
                    });
                }
            }
        }
    },
    getCurrentPanel: function() {
        return this._getPanels().eq(this._currentIndex);
    },
    checkTimeout:function(){
        var json = MOB.jsonEval(this.getCurrentPanel().html());
        if (json && json[MOB.keys.statusCode] == MOB.statusCode.timeout) this.closeCurrentTab();
    },
    openExternal:function(url, $panel){
        var ih = navTab._panelBox.height();

        var str = '<iframe src="{url}" style="width:100%;height:{height};" frameborder="no" border="0" marginwidth="0" marginheight="0"></iframe>';
        $panel.html(str.replaceAll("{url}", url).replaceAll("{height}", ih+"px"));
    },
    /**
     *
     * @param {Object} tabid
     * @param {Object} url
     * @param {Object} params: title, data, fresh
     */
    openTab: function(options){ //if found tabid replace tab, else create a new tab.
        var op = $.extend({title:"New Tab", type:"GET", data:{}, fresh:true, external:false}, options);
        var iOpenIndex = this._indexTabId(options.tabid);
        if (iOpenIndex >= 0){
            var $tab = this._getTabs().eq(iOpenIndex);
            var span$ = $tab.attr("tabid") == this._op.mainTabId ? "> span > span" : "> span";
            $tab.find(">a").attr("title", op.title).find(span$).html(op.title);
            var $panel = this._getPanels().eq(iOpenIndex);
            if(op.fresh || $tab.attr("url") != options.url) {
                $tab.attr("url", options.url);
                if (op.external || options.url.isExternalUrl()) {
                    $tab.addClass("external");
                    navTab.openExternal(options.url, $panel);
                } else {
                    $tab.removeClass("external");
                    $panel.ajaxUrl({
                        type:op.type, url:options.url, data:op.data, callback:function(){
                            navTab._loadUrlCallback($panel);
                        }
                    });
                }
            }
            this._currentIndex = iOpenIndex;
        } else {

            var tabFrag = '<li tabid="#tabid#"><a href="javascript:;" title="#title#" class="#tabid#">#title#</a><i class="system-close1 close"></i></li>';


            //var tabFrag = '<li tabid="#tabid#"><a href="javascript:" title="#title#" class="#tabid#"><span>#title#</span></a><a href="javascript:;" class="close">close</a></li>';
            this._tabBox.append(tabFrag.replaceAll("#tabid#", options.tabid).replaceAll("#title#", op.title));
            this._panelBox.append('<div class="page unitBox"></div>');
            this._moreBox.append('<li><a href="javascript:" title="#title#">#title#</a><i class="system-close2 close"></i> </li>'.replaceAll("#title#", op.title));

            var $tabs = this._getTabs();
            var $tab = $tabs.filter(":last");
            var $panel = this._getPanels().filter(":last");
            if (op.external || options.url.isExternalUrl()) {
                $tab.addClass("external");
                navTab.openExternal(options.url, $panel);
            } else {
                $tab.removeClass("external");
                $panel.ajaxUrl({
                    type:op.type, url:options.url, data:op.data, callback:function(){
                        navTab._loadUrlCallback($panel);
                    }
                });
            }
            if ($.History) {
                setTimeout(function(){
                    $.History.addHistory(options.tabid + '$' + options.url, function(tabid){
                        var i = navTab._indexTabId(options.tabid);
                        if (i >= 0) navTab._switchTab(i);
                    }, options.tabid);
                }, 10);
            }
            this._currentIndex = $tabs.size() - 1;
            //this._contextmenu($tabs.filter(":last").hoverClass("hover"));
        }

        this._init();
        this._scrollCurrent();

        this._getTabs().eq(this._currentIndex).attr("url", options.url);
    },

    fullScreen:function(){

        $(".ERP-R-M-R,.main").addClass("full-screen");
        $(".ERP-T,.ERP-L,.ERP-R-M-L,#dialogProxy,.ERP-R-M-toogle").hide();
        var quitBtn = $('<a href="javascript:;" class="quit-screen-btn" onclick="navTab.quitScreen();">退出全屏(ESC)</a>');
        $("body").append(quitBtn);

    },
    quitScreen:function(){
        $(".ERP-R-M-R,.main").removeClass("full-screen");
        $(".ERP-T,.ERP-L,.ERP-R-M-L,#dialogProxy,.ERP-R-M-toogle").show();
        $(".quit-screen-btn").remove();
    },
    esc:function(event){
        if(event.keyCode == MOB.keyCode.ESC){
            navTab.quitScreen();
        }
    }
};
$(document).on('keydown', function(event){navTab.esc(event);});