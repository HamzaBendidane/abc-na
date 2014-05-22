// Tools for sk
var tools;
define('tools', ['jquery'], function() {

    tools = {
        cssLoaded: [],
        loadCss : function(url) {
            if(this.cssLoaded.indexOf(url) < 0 ) {
                this.cssLoaded.push(url);
                var link = document.createElement("link");
                link.type = "text/css";
                link.rel = "stylesheet";
                link.href = url;
                document.getElementsByTagName("head")[0].appendChild(link);
            }
        }
    }
});