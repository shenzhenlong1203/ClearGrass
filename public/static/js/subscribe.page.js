/**
 * Created by sunlan on 15/8/31.
 */

$(function(){
    var $subscribeBtn = $("#subscribe-btn"),$typeSpan = $(".type-span");

    getParameterByName("email")?$("#email").val(getParameterByName("email")):"";

    $subscribeBtn.bind("click",function(){
        if(!checkForm()) return;
        sendSubscribe();
    })

    function getParameterByName(name) {
        var match = RegExp('[?&]' + name + '=([^&]*)')
            .exec(window.location.search);
        return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
    }

    function sendSubscribe(){
        $subscribeBtn.html("Being updated..").attr("disable","disable");

        var _name = $("#name").val(),
            _email = $("#email").val(),
            _watchType = $("#watchType").val(),
            _watchSize = $("#watchSize").val(),
            _comment = $("#elseTxt").val();

        var params = {
            name:_name,
            email:_email,
            catalog:"amber",
            watchType:_watchType,
            watchSize:_watchSize,
            comment:_comment

        }

        var url = "/subscribe";
        $.ajax({
            url:url,
            type:"post",
            timeout:4000,
            data:params,
            dataType: "json",
            success:function(d){
                if( d.code == 0 ){
//                    successDialog("../static/images/success-icon.png","Thank you very much!");
                    $(".subscribe-input-area").hide();
                    $(".success-show").show();

                }else{
                    alert("Error");
                }
                $subscribeBtn.html("Keep Me Updated").attr("disable","false");
            },
            error:function() {
                alert("Error");
                $subscribeBtn.html("Keep Me Updated").attr("disable","false");
            }
        })


    }
//        successDialog("images/success-icon.png","Thank you very much!");
    $typeSpan.bind("click",function(){
        var _value = $(this).html();
        $(this).addClass("active").siblings().removeClass("active");
        $(this).parents(".cot").find("input").val(_value);
    })

    var inputArr = {
        "name":{name:"name", label:"name_label",empty:"Please enter your name",wrong:"Please enter your name",regx:".*"},
        "email":{name:"email", label:"email_label",empty:"Please enter your email ",wrong:"Please enter right email",regx:"\\w{1,}([\\-\\+\\.]\\w{1,}){0,}@\\w{1,}([\\-\\.]\\w{1,}){0,}\\.\\w{1,}([\\-\\.]\\w{1,}){0,}"},
        "watchType":{name:"watchType", label:"watch_type_label",regx:".*"},
        "watchSize":{name:"watchSize", label:"watch_size_label",regx:".*"},
    }

    function checkForm(){
        var status = true;
        var badElement = null;
        var badInput = null;
        for(item in inputArr) {
            var label = $("#"+inputArr[item].label)
            label.removeClass("error-label");
        }

        $(".subscribe-input-area input").each(function(){
            var _self = $(this),
                inputName = _self.attr("id");
            if(inputName in inputArr){
                var labelId = inputArr[inputName].label
                var label = $("#"+labelId)
                var value = $("#"+inputName).val();
                if(!value){
                    status = false;
                    label.addClass("error-label")
                }
                else{
                    var patt = new RegExp(inputArr[inputName].regx);
                    if(!patt.test(value)){
                        status = false;
                        label.addClass("error-label")
                    }
                }
                if (!status && badElement == null) {
                    badElement = label
                    badInput = _self
                }
            }

        })
        if (!status) {
            $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
            if ($body.offset().top < badElement.offset().top - 100) {
                $body.animate({
                    scrollTop: badElement.offset().top - 100
                }, 1000)
            }
            badInput.focus()
        }
        return status;
    }

    function successDialog(icon,txt){
        var _dialogHtml = '<div class="dialog-panel">'+
            '<div class="success-box">'+
            '<img src="'+icon+'" width="142" height="142" class="icon-suc"/>'+
            '<div class="txt">'+
            '<p>'+txt+'</p>'+
            '</div><span class="dialog-close"></span>'+
            '</div>'+
            '</div>';
        if($(".dialog-panel").length>0){
            $(".dialog-panel").remove();
        }

        $("body").append(_dialogHtml);

        $(".dialog-close").on("click",function(){
            window.location.reload();
        })
    }
})
