// version non plugin: a des dépendances au niveau de l'HTML => work in progress : mettre en plugins
// DatePickerRangeSet
var daterangepickerset = function() {


    var checkedranges = $("#datestringrange").val();
    $('#daterangepickerset').find('[data-range="' + checkedranges + '"]').addClass('btn-primary');

    var ranges = {
        'today': [moment(), moment()],
        'yesterday':    [moment().subtract('days', 1), moment().subtract('days', 1)],
        'thisweek':     [moment().startOf('week').add('days', 1), moment()],
        'lastweek':     [moment().subtract('days', 6).startOf('week').add('days', 1), moment().subtract('days', 6).endOf('week').add('days', 1)],
        'monthtodate':  [moment().startOf('month'), moment().endOf('month')],
        'lastmonth':    [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
        'last7days':    [moment().subtract('days', 6), moment()],
        'last30days':   [moment().subtract('days', 29), moment()],
        'alltime':      [moment().subtract('year', 2).startOf('month'), moment()]
    }

    var displayinputdaterange = function(start, end) {
        $container = $('#daterangepickerset');
        $container.find(".from-date").val(start.format('MM-DD-YYYY'));
        $container.find(".to-date").val(end.format('MM-DD-YYYY'));
    }

    var daterangset = $('#daterangeprimary').daterangepicker({
        startDate: moment().subtract('days', 2),
        endDate: moment(),
        minDate: '01/01/2012',
        maxDate: '12/31/2015',
        dateLimit: {days: 200},
        showDropdowns: true,
        showWeekNumbers: true,
        timePicker: false,
        timePickerIncrement: 1,
        timePicker12Hour: true,
        // ranges: {
        //'Today': [moment(), moment()],
        //'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
        //'Last 7 Days': [moment().subtract('days', 6), moment()],
        //'Last 30 Days': [moment().subtract('days', 29), moment()],
        //'This Month': [moment().startOf('month'), moment().endOf('month')],
        //'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
        //},
        opens: 'left',
        buttonClasses: ['btn btn-default'],
        applyClass: 'btn-small btn-primary',
        cancelClass: 'btn-small',
        format: 'MM/DD/YYYY',
        separator: ' to ',
        locale: {
            applyLabel: 'Submit',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom Range',
            daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            firstDay: 1
        }
    },
    function(start, end) {
        displayinputdaterange(start, end);
        $('.ranges li').siblings().removeClass('btn-primary').closest('[data-range=custom]').addClass('btn-primary');
        $('#datestringrange').val('custom');
    }
    );

    $('.openpicker').on('click', function() {
        $('#daterangeprimary').click();
    });


    $('.ranges li').click(function(e) {

        var $this = $(this),
                key = $this.data('range');

        $this.siblings().removeClass('btn-primary').end().addClass('btn-primary');

        $('#datestringrange').val(key);

        if (key !== undefined && key !== 'custom') {
            daterangset.data('daterangepicker').setStartDate(ranges[key][0]);
            daterangset.data('daterangepicker').setEndDate(ranges[key][1]);
            displayinputdaterange(ranges[key][0], ranges[key][1]);
        }
    });
};

// AMD
define('daterangepickerset', ['daterangepicker'], daterangepickerset);