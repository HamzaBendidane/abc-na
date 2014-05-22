// All we need for form
define('app-forms', ['jquery', 'bootstrap'], function() {
    
    //Popover
  $('.popover-pop').popover('hide');

    //Collapse
    $('#myCollapsible').collapse({
        toggle: false
    })

    //Tabs
    $('.myTabBeauty a').click(function(e) {
        e.preventDefault();
            $(this).tab('show');
    })

    //Dropdown
    $('.dropdown-toggle').dropdown();


    //Wizard
  //  $("#wizard").bwizard();

    //wysihtml5
    //$('#wysiwyg').wysihtml5();


// JS pour les formulaires
    $(function() {
        $('.check-all').on('click', function() {
            $(this).closest('.form-group').find('input:not([disabled])').prop('checked', true);
            return false;
        });
        $('.uncheck-all').on('click', function() {
            $(this).closest('.form-group').find('input:not([disabled])').prop('checked', false);
            return false;
        });
        $("#rapport").click(function() {
            if ( $("input[name='selectField\\[\\]']").is(':checked')){
                $('body').append('<div id="myModal" class="modal hide fade"></div>');
                $("#myModal").modal('show');
                $('.modal-backdrop').css({'background': 'url("/web/themes/default/img/loading-bars.gif") 50% 50% no-repeat rgba(0,0,0,0.8)'});
            }else{
                alert("Please select at least one chackbox");
                return false;
            }
        });
    });
    
    
            
});