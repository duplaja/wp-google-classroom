/*************************
 *  JS For Card Flipper
 *************************/

jQuery(document).ready(function() {

    if (jQuery('#animatedCards').length) {
        jQuery("#animatedCards").animatedModal();
    }
});

jQuery(function(){
    flip = jQuery(".playing-card").flip({
      trigger: "click",
      front: '.card-front',
      back: '.card-back'
    });

    jQuery( ".playing-card" ).on( "click", function() {
        if ( jQuery( "#flip-audio" ).length ) {
            var audio = document.getElementById("flip-audio");
            audio.play();
        }
    });

});

 