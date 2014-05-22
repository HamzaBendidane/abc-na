var $border_color = "#f9f9f9";
var $grid_color = "#eeeeee";
var $default_black = "#666666";
var $default_white = "#ffffff";
var $green = "#8ecf67";
var $blue = "#87ceeb";

$(function () { 
    function GenerateSeries(added){
        var data = [];
        var start = 100 + added;
        var end = 500 + added;
 
        for(i=1;i<=20;i++){        
            var d = Math.floor(Math.random() * (end - start + 1) + start);        
            data.push([i, d]);
            start++;
            end++;
        }
 
        return data;
    }
 
    var data1 = GenerateSeries(0);
    var data2 = GenerateSeries(10);    
 
    var markings = [
        { yaxis: { from: 0, to: 50 }, color: $border_color },
        { yaxis: { from: 100, to: 150 }, color: $border_color },
        { yaxis: { from: 200, to: 250 }, color: $border_color },
        { yaxis: { from: 300, to: 350 }, color: $border_color },
        { yaxis: { from: 400, to: 450 }, color: $border_color },
        { yaxis: { from: 500, to: 550 }, color: $border_color }
    ];
 
var options = {
            series: {
                lines: {
                    show: true,
                    lineWidth: 2
                },
                points: {
                    show: true,
                    radius: 4,
                    fill: true,
                    fillColor: "#ffffff",
                    lineWidth: 2
                },
                shadowSize: 0
            },
            grid: {
                markings: markings,
                hoverable: true,
                clickable: false,
                borderWidth: 1,
                tickColor: $border_color,
                borderColor: $grid_color,
                backgroundColor: { colors: [$default_white, $default_white] }
            },
            legend:{     
                show: true,
                position: 'nw',
                noColumns: 0,
            },
            tooltip: true,
            tooltipOpts: {
                content: '%x: %y'
            },

            xaxis: {ticks:24, tickDecimals: 0},
            yaxis: {ticks:6, tickDecimals: 0},

            colors: [$blue],

        };
 
    $.plot($("#yAxisGrid"),
        [
            {data:data1, label: "Likes"},
            {data:data2, label: "Shares"}
        ], options
    );
});