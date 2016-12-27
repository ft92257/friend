/**
 * NiuWa - Internet Financial Giants
 *
 * @author  liuwei <liuwei02@niuwap2p.com>
 */
;$(function(){

    //不能全为数字
    $.validator.addMethod("nonumber",function(value,element){
        return this.optional(element) || !/^\d+$/i.test(value);
    },lang.validator.username_nonumber);

    //验证是否为中文
    $.validator.addMethod("nonumber",function(value,element){
        return this.optional(element) || !/^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])+$/i.test(value);
    },lang.validator.username_nonumber);

});