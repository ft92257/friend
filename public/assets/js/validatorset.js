;var langValid = lang.validator;
var validatorSet = {
    mssages:{
        required: langValid.required,
        remote: langValid.remote,
        email: langValid.email,
        url: langValid.url,
        date: langValid.date,
        dateISO: langValid.dateISO,
        number: langValid.number,
        digits: langValid.digits,
        creditcard: langValid.creditcard,
        equalTo: langValid.equalTo,
        extension: langValid.extension,
        maxlength: $.validator.format(langValid.maxlength),
        minlength: $.validator.format(langValid.minlength),
        rangelength: $.validator.format(langValid.rangelength),
        range: $.validator.format(langValid.range),
        max: $.validator.format(langValid.max),
        min: $.validator.format(langValid.min)
    }

    /*注册表单*/
    //form1:{
    //
    //    //wrapper:"li",
    //    /*规则*/
    //    rules:{
    //        username:{
    //            required:true,
    //            rangelength:[6,20],
    //            nonumber:true,
    //            remote: {
    //                url: "username.html",
    //                type: "post",
    //                dataType: "json",
    //                data: {
    //                    username: function () {
    //                        return $("#username").val();
    //                    }
    //                }
    //            }
    //        },
    //        password1:{
    //            required:true,
    //            rangelength:[6,16]
    //        },
    //        password2:{
    //            required:true,
    //            rangelength:[6,16],
    //            equalTo: "#password"
    //
    //        },
    //        email:{
    //            required:true,
    //            email:true
    //        },
    //        realname:{
    //            required:true,
    //            rangelength:[2,8]
    //        },
    //        mobile:{
    //            required:true,
    //            number:true
    //        }
    //
    //    },
    //
    //    /*提示*/
    //    messages:{
    //        username:{
    //            required: langValid.username_required,
    //            rangelength: $.validator.format(langValid.username_rangelength),
    //            nonumber: langValid.username_nonumber,
    //            remote:langValid.username_exist
    //        }/*,
    //         password1:{
    //         required:true,
    //         minlength:6,
    //         maxlength:16
    //         },
    //         password2:{
    //         required:true,
    //         minlength:6,
    //         maxlength:16,
    //         equalTo: "#password1"
    //
    //         },
    //         email:{
    //         required:true,
    //         email:true
    //         },
    //         realname:{
    //         required:true,
    //         minlength:2,
    //         maxlength:8
    //         },
    //         mobile:{
    //         required:true,
    //         number:true
    //         }*/
    //    }
    //},
    //form2:{
    //    errorElement:"em",
    //    errorContainer: $("#messages"),
    //    errorLabelContainer: $("#messages"),
    //    wrapper: 'div',
    //    rules:{
    //        username:{
    //            required:true,
    //            rangelength:[6,20],
    //            nonumber:true
    //        },
    //        password1:{
    //            required:true,
    //            rangelength:[6,20]
    //        }
    //        ,
    //        password2:{
    //            required:true,
    //            rangelength:[6,20]
    //        }
    //    }
    //},
    //form3:{
    //
    //    errorElement:"div",
    //    errorClass:'user_text_error',
    //    //onfocusout:false,
    //
    //    //focusInvalid:false,
    //    /*errorPlacement: function(error, element) {
    //     //alert(error.html());
    //
    //
    //     $("#messages2").html($(error));
    //
    //
    //     },*/
    //
    //    showErrors:function(errorMap,errorList) {
    //        if(errorList.length==0){
    //            $("#messages2").html("");
    //        }
    //        $("#messages2").html(errorList[0].message);
    //
    //        /*this.defaultShowErrors();*/
    //
    //    },
    //    rules:{
    //        username:{
    //            required:true,
    //            rangelength:[6,20],
    //            nonumber:true
    //        },
    //        password1:{
    //            digits:true
    //
    //        }
    //        ,
    //        password2:{
    //            email:true
    //
    //        }
    //    }
    //}
}
;
