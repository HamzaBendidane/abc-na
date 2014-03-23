function init(param){
	
	$.ajaxSetup({
	    timeout: 15000
	});
	
	var capabilitie = utils.detect_device_capabilities();
       	
	$.extend($.mobile , {
		/* @todo : detecter capacité device : sur Ipod : supprimer les animations */
		defaultPageTransition : utils.get_page_transition(),
		ajaxEnabled :  true,
		loadingMessageTextVisible : true, 
		loadingMessage : param.loadingMessage,
		pageLoadErrorMessage : param.pageLoadErrorMessage,
		loadingMessageTheme : 'd',
		pageLoadErrorMessageTheme: 'd',
                allowSamePageTransition: true
	});
}

$(document).on('pageinit', function(e){
	$('.ui-content').css('min-height', $(window).height());
});

$(window).on('resize', function(e){
    //recalcule la taille du container
     $('#wrapper').height($(window).height());
});

/*utils*/
var utils = {
        detect_device_capabilities : function(){
                    if(/iPod/i.test(navigator.userAgent)){
                            return false;
                    }else {
                            return true;
                    }
                },
        // return slide, fade ou none             
        get_page_transition : function(){
           if(!this.detect_device_capabilities()){
               return 'none'; 
           }
           else if(/iPad/i.test(navigator.userAgent)){
               return 'fade';
           }
           else{
                return 'slide';
           }
        },
		
	createCookie : function(name,value,hours) {
		if (hours) {
			var date = new Date();
			date.setTime(date.getTime()+(hours*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	},
	
	readCookie : function (name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	},
	
	 eraseCookie : function(name) {
		this.createCookie(name,"",-1);
	},
	
	refreshPage : function() {
		  $.mobile.changePage(
		    window.location.href,
		    {
		      allowSamePageTransition : true,
		      transition              : 'none',
		      showLoadMsg             : true,
		      reloadPage              : true
		    }
		  );
		}
};
