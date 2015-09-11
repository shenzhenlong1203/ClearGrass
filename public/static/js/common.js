/**
 * Created by sunlan on 15/8/31.
 */

$(function(){
    var $subscribeHome = $("#maile-subscribe"),$inputSub = $("#input-sub");
    $subscribeHome.on("click",function(){
        var _mailVal = $.trim($inputSub.val());
//        var patt = new RegExp(inputArr[inputName].regx);

        if(_mailVal){
            window.location.href = "subscribe?email="+_mailVal;
        }
        else{
            window.location.href = "subscribe";
        }
        ga("send", "event", "subscribe", _mailVal);
    })


    $(window).scroll(function(){
        var scrollTop = $("body").scrollTop() || $("html").scrollTop();
        if(scrollTop>=60){
            $("#header .nav").addClass("navFixed");
        }else{
            $("#header .nav").removeClass("navFixed");
        }
    })


    $("a.language").on("click", function () {
        var language = getCookie("language")
        var url = window.location.href
        if (url.indexOf("?") >= 0)
            url = url.substring(0, url.indexOf("?"));
        if (url.indexOf("#") > 0)
            url = url.substring(0, url.indexOf("#"));
        window.location.href = url + (language ==  "en" ? "?language=zh-CN" : "?language=en")
    })

    function getCookie(name)
    {
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");

        if(arr=document.cookie.match(reg))

            return unescape(arr[2]);
        else
            return null;
    }



})