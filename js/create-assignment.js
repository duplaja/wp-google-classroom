/***************************************
 *  JS Specific to Assignment Creation Form
 ***************************************/
jQuery(document).ready(function(){
    jQuery( "#due_date" ).datepicker({ 
        minDate: 0, 
        maxDate: "+12M",
        dateFormat: 'yy-mm-dd'
        
     });

    jQuery('#due_time').timepicker({
        'timeFormat': 'h:i A',
        'minTime': '5:00am',
        'maxTime': '11:59pm', 
    });
    
    jQuery("#assignmentform").validate({
        rules: {
'classes[]': {
    required: true
}
        },
        messages: { 
"classes[]": "You must select at least one class.",
"pdf_worksheet": "You must upload your assignment in PDF form.",
"assignment_title": "You must enter an assignment title."
        } 
    });
});

function showHideNumPages(value) {

    if (value == 'single') {
        jQuery('#hide_pages_per_version').hide();
    } else {
        jQuery('#hide_pages_per_version').show();
    }
}
    
function showHideClassroom(value) {

    if (value == 'yes') {
        jQuery('.classroom_display').show();
    } else {
        jQuery('.classroom_display').hide();
    }
}