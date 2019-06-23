jQuery(document).ready(function() {

    if (jQuery('#animatedSignout').length) {
        jQuery("#animatedSignout").animatedModal({
            color: '#F5F5F5',
            beforeOpen: function() {
                jQuery('#wpadminbar').hide();
            }
        });
    }
});

function checkinout(checkboxElem) {

    var today = new Date();
    var date = today.getFullYear()+"-"+(today.getMonth()+1)+"-"+today.getDate();
    var sendDate = date;
    var ampm = formatAMPM(today);
    

    var id_num = checkboxElem.value;
    var locationbox = "destination"+id_num;
    var namebox = "name"+id_num;
    var timeoutbox = "time"+id_num;

    var destinationtemp = document.getElementById(locationbox).value;
    var stuname = document.getElementById(namebox).innerHTML;

    if(destinationtemp == 'Other') {

        var destination = prompt('Where is '+stuname+' going?','Other');

        if(destination == null) {
            destination = 'Other';
        }

    } else {
    
        var destination = destinationtemp;
    }

    if (checkboxElem.checked) {
        document.getElementById(timeoutbox).innerHTML = ""; 
        sendToCheckoutSpreadsheet(sendDate,stuname,ampm,'Returned');
    } else {

        document.getElementById(timeoutbox).innerHTML = ampm; 
        sendToCheckoutSpreadsheet(sendDate,stuname,ampm,destination);
    }
  }

  function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? "pm" : "am";
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour "0" should be "12"
    minutes = minutes < 10 ? "0"+minutes : minutes;
    var strTime = hours + ":" + minutes + " " + ampm;
    return strTime;
  }


  function forceCheckin(image) {

    var id_num = jQuery(image).data( "userid");

        var today = new Date();
        var date = today.getFullYear()+"-"+(today.getMonth()+1)+"-"+today.getDate();
        var sendDate = date;
        var ampm = formatAMPM(today);
      
        var namebox = "name"+id_num;
      
        var stuname = document.getElementById(namebox).innerHTML;

        sendToCheckoutSpreadsheet(sendDate,stuname,ampm,'Arrived in Classroom');

        jQuery('#forcecheckin'+id_num).css('background-color', 'green');

        setTimeout(function(){
            jQuery('#forcecheckin'+id_num).css('background-color', 'transparent');
          }, 2000);

  }

  function sendToCheckoutSpreadsheet(date,stuname,time,destination) {
      if(date != '' && stuname !='' && time != '' && destination != '') {

            var data = {
                'action': 'send_signout_update',
                'date': date,
                'stuname' : stuname,
                'time': time,
                'destination' : destination    
            };
            
            jQuery.post(ajaxurl, data, function(response) {
        


                if (response == 'failed') {
                    alert('Something went wrong. The signout was not logged, and should be logged manually.');
                } else {
                    console.log('Student signed out / in');
                }
                
            });
    
      }
  }