;(function($){
    $.fn.tree = function(){
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
            if(!opts.collapse){
                if(opts.expand){
                    _this.find("ul:gt(0)").hide();
                }else{
                    _this.find("ul").hide();                }
            }


            genTree(_this);

        });
        function genTree(ele){
            var zindex=0;
            $("li>a", ele).each(function(){
                zindex++;
                var $ul = $(this).siblings("ul");
                $(this).wrap('<div></div>');

                if(opts.icon){
                    if($ul.length>0){
                        if(zindex==1 && opts.expand){
                            $(this).prepend("<i class='system-folder-expand'></i>");
                        }else{
                            $(this).prepend("<i class='system-folder-collapse'></i>");
                        }
                    }
                }
                /*if(opts.checkbox){
                    $(this).prepend('<input type="checkbox" value="" name="">')
                }*/

                if($ul.length>0){
                    $(this).click(
                        function(){
                            if(!$ul.is(":animated")) {
                                if($ul.is(":hidden")){
                                    $ul.slideDown(opts.speed);
                                    $(this).find("i").addClass("system-folder-collapse").removeClass("system-folder-expand");
                                }else{
                                    $ul.slideUp(opts.speed);
                                    $(this).find("i").addClass("system-folder-expand").removeClass("system-folder-collapse");
                                }
                            }
                        }
                    );
                }
            });

        }
    }

})(jQuery);