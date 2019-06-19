/**********************************************
 * Admin JS Scripts for Google Classroom Plugin
 * Settings page and bulk add students
 **********************************************/

jQuery(document).ready(function(){
    jQuery("#bulkaddform").validate();
});

function showDetailedInstructions() {
    jQuery('#detailedinstructions').toggle();
}