 function timeDifference(laterdate,earlierdate) {
    var difference = laterdate.getTime() - earlierdate.getTime();

    var daysDifference = Math.floor(difference/1000/60/60/24);
    difference -= daysDifference*1000*60*60*24

    var hoursDifference = Math.floor(difference/1000/60/60);
    difference -= hoursDifference*1000*60*60

    var minutesDifference = Math.floor(difference/1000/60);
    difference -= minutesDifference*1000*60

    var secondsDifference = Math.floor(difference/1000);


    var timeDiff;
    timeDiff = "";
    if(daysDifference>0)
    {
        if(daysDifference>1)
        {
            timeDiff+= daysDifference+" days ";
        }
        else{
            timeDiff+= daysDifference+" day ";
        }

    }
    if(hoursDifference>0)
    {
        if(hoursDifference>1)
        {
            timeDiff+= hoursDifference+" hours ";
        }
        else{
            timeDiff+= hoursDifference+" hour ";
        }
    }
    if(minutesDifference>0)
    {
        if(minutesDifference>1)
        {
            timeDiff+= minutesDifference+" minutes agos";
        }
        else{
            timeDiff+= minutesDifference+" minute ago";
        }
    }
    return timeDiff;
}

$(function(){
    $(".toTop").hide();

    $(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.toTop').fadeIn();
                $(".toTop").css("top","231px");
                $(".toTop").css("position","fixed");
                $(".toTop").css("border","none");
                $(".toTop").css("left","-0");
            } else {
                $('.toTop').fadeOut();
            }
        });

        $('.toTop').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });

        $("#formDevice label").bind('change',function(){
            $(this).closest('fieldset').find('label').removeClass('checked').end().end().addClass('checked');
        })

    });
});