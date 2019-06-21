/**************************
 *  All JS Functions for Students Spinner
 *
 * */

jQuery(document).ready(function() {
    if ( jQuery( "#storageElement" ).length ) {
        createWheel();
    }

    if (jQuery('#launchSpinner').length) {
        jQuery("#launchSpinner").animatedModal({
            beforeOpen: function() {
                jQuery('#wpadminbar').hide();
            }
        });
        
    }

});

function createWheel() {

    wheelSpinning = false;
    let wheelColors = ['eae56f','89f26e','7de6ef','e7706f'];
    names = jQuery("#storageElement").data("storeit").split(",");
    remove = jQuery("#storageElement").data("removestudents");
    
    if(names[0] == '') {
        var static_info = jQuery("#storageElementStatic").data("storeit");
        names = jQuery("#storageElementStatic").data("storeit").split(",");
        jQuery("#storageElement").data("storeit",static_info);
    }

    segments = [];

    var i;
    for (i = 0; i < names.length; i++) {
        color = i%wheelColors.length;

        segments.push({'fillStyle' : '#'+wheelColors[color], 'text' : names[i]});

    }
    // Create new wheel object specifying the parameters at creation time.
    theWheel = new Winwheel({
        'canvasId'     : 'spinnerCanvas',
        'numSegments'  : names.length,     // Specify number of segments.
        //'outerRadius'  : 230,   // Set outer radius so wheel fits inside the background.
        'responsive'    : true,
        'textFontSize' : 18,    // Set font size as desired.
        'segments'     : segments,  // Define segments including colour and text.
        'animation' :           // Specify the animation to use.
        {
            'type'     : 'spinToStop',
            'duration' : 7,     // Duration in seconds.
            'spins'    : 8,     // Number of complete spins.
            'callbackFinished' : alertWinner,
            'callbackAfter' : drawTriangle
        }
    });
};    

 
function drawTriangle()
{
    // Get the canvas context the wheel uses.
    let ctx = theWheel.ctx;

    ctx.strokeStyle = 'navy';     // Set line colour.
    ctx.fillStyle   = 'black';     // Set fill colour.
    ctx.lineWidth   = 2;
    ctx.beginPath();              // Begin path.
    ctx.moveTo(255, 0);           // Move to initial position.
    ctx.lineTo(295, 0);           // Draw lines to make the shape.
    ctx.lineTo(275, 35);
    ctx.lineTo(255, 0);
    ctx.stroke();                 // Complete the path by stroking (draw lines).
    ctx.fill();                   // Then fill.
}

function startSpin() {
    createWheel();
    jQuery('#spinwinner').html('&nbsp;');
    wheelSpinning = true;
    playSpinSound();
    theWheel.startAnimation();
}

function alertWinner(indicatedSegment) {
    drawTriangle();
    winner = indicatedSegment.text;
    jQuery('#spinwinner').html(winner);
    
    //Runs if we're removing options on each roll
    if(remove == 'yes') {

        var temp_names = names.filter(function(elem){
            return elem != indicatedSegment.text; 
        })
        
        var new_data = temp_names.join(',');

        jQuery("#storageElement").data("storeit",new_data);
    }
}

function playSpinSound(){
    if ( jQuery( "#spinner-audio" ).length ) {
        var audio = document.getElementById("spinner-audio");
        audio.play();
    }
}