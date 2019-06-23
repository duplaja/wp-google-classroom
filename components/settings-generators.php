<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!function_exists('google_classroom_show_bulk_add_form')) {

    function google_classroom_show_bulk_add_form() {


        $form_code ="
        <form id='bulkaddform' method='POST' action='{$_SERVER['REQUEST_URI']}' enctype='multipart/form-data'>
            <section>
                <div>
                    <label for='singleclass'>Choose Class: </label>";

                    $form_code.= google_classroom_get_classes('select');

            $form_code .= "</div></section>
            <section>
                <div>
                    <label for='email_string'>Text Containing Emails</label>
                    <textarea style='width:85%;height:200px'id='email_string' name='email_string' placeholder='Paste text / roster (with e-mails) here.' required minlength='5'></textarea>
                    <p>Don't worry about any extra text or formatting, it will be automatically removed.</p>
                </div>
            </section>
            <section>
                <input type='submit' value='Bulk Invite Students'>
            </section>
        </form>";

        return $form_code;
    }
}

if(!function_exists('google_classroom_show_assignment_form')) {

    function google_classroom_show_assignment_form() {

        $form_html = "
        <form id='assignmentform' method='POST' action='{$_SERVER['REQUEST_URI']}' enctype='multipart/form-data'>
            <section>
                <div style='float:left;margin-right:20px;'>
                    <label for='assignment_title'>Assignment Title *</label>
                    <input name='assignment_title' id='assignment_title' type='text' placeholder='Assignment Title' minlength='2' autocomplete='off' required>
                </div>
                <div style='float:left;margin-right:20px;'>
                    <label for='name_placement'>Name Location</label>
                    <select name='name_placement' id='name_placement'>
                        <option value='10x8' selected>Top Left Corner</option>
                        <option value='130x15'>Infinite Alg</option>
                        <option value='35x18'>WS / Note (Gina Wilson)</option>

                    </select>
                </div>
                <div style='float:left;'>
                    <label for='versioning'>Versioning</label>
                    <select id='versioning' name='versioning' onChange='showHideNumPages(this.options[this.selectedIndex].value)'>
                        <option value='single' selected='selected'>Single Sheet Version</option>
                        <option value='multiple'>Multiple Versions</option>
                    </select>
                </div>    
            </section>
            <br style='clear:both;' />
            <section>
                <div style='float:left;margin-right:20px;'>
                    <label for='send_to_classroom'>Send to Classroom?</label>
                    <select id='send_to_classroom' name='send_to_classroom' onChange='showHideClassroom(this.options[this.selectedIndex].value)'>
                        <option value='yes' selected='selected'>Yes</option>
                        <option value='no'>No</option>
                    </select>
                </div>
                <div style='float:left;margin-right:20px;'>
                    <label for='add_qr'>Add QR Code?</label>
                    <select id='add_qr' name='add_qr'>
                        <option value='yes' selected='selected'>Yes</option>
                        <option value='no'>No</option>
                    </select>
                </div>
                <div style='float:left;display:none' id='hide_pages_per_version'>
                    <label for='pages_per_version'># pages / version *</label>
                    <input id='pages_per_version' name='pages_per_version' type='number' min='1' value='1' required>
                </div>
            </section>
            <br style='clear:both;'/>
            <section>
                <div style='float:left;margin-right:20px;' class='one_half'>
                    <fieldset>
                        <legend> Select Class(es) *</legend>";

                    $form_html .= google_classroom_get_classes('checkbox');

                    $form_html .= "
                        <label for='classes[]' class='error'></label>
                        </fieldset>
                </div>
                <div style='float:left;'  class='one_half classroom_display'>
                    <label for='assignment_description'>Description</label>
                    <textarea id='assignment_description' name='assignment_description' placeholder='Assignment description here (optional)'></textarea>
                </div>
            </section>
            <br style='clear:both;' />
            <section>
                <div style='float:left;margin-right:20px;' class='classroom_display'>
                    <label for='due_date'>Due Date *</label>
                    <input type='text' id='due_date' name='due_date' readonly required>
                </div>
                <div style='float:left;margin-right:20px;' class='classroom_display'>
                <label for='due_time'>Due Time *</label>
                    <input name='due_time' id='due_time' type='text' value='11:59 PM' required/>
                </div>
                <div style='float:left;'>    
                    <label for='pdf_worksheet'>PDF Upload *</label>
                    <input type='file' id='pdf_worksheet' name='pdf_worksheet' accept='application/pdf' required>
                </div>
            </section>
            <br style='clear:both;' />
            <section>
                <div style='float:left;margin-right:20px;' class='one_half'>    
                    <input type='submit' value='Submit Assignment'>
                </div>
            </section>
        </form>
        ";

        return $form_html;
    }
}

if(!function_exists('google_classroom_show_spinner')) {

    function google_classroom_show_spinner() {


        if(isset($_POST['spinclassid'])) {

            $class_id =  $_POST['spinclassid'];

            $temp_students = google_classroom_get_members($class_id);

            $students = array();

            foreach($temp_students as $student) {

                $name_array = explode(' ',$student);
                
                $students[] = $name_array[0].' '.$name_array[1][0];
            }

            if(isset($_POST['teachers_choice'])) {

                $students[] = 'Teacher Picks';
            }

            if(isset($_POST['last_picks'])) {
                $students[] = 'Last Picks';
            }

            shuffle($students);

            $remove = '';
            if(isset($_POST['remove_students'])) {
                $remove = 'yes';
            } else {
                $remove ='no';
            }

            //Modal Stuff
            $output = '
            <!--Call your modal-->
            <h2>Your spinner is ready! Click Below to Open It</h2>
            <a id="launchSpinner" href="#animatedModal" class="button-link-special">Launch Spinner</a>';
            
            
            $output .= '
            <div id="animatedModal">
            <div class="modal-content">';
            
            $output .='
            <div style="float:left;margin-top:40px;margin-right:40px;margin-left:8%">
            <div id="storageElement" data-storeIt="';
            

            $output .= implode(',',$students);

            $output .= '" data-removestudents="'.$remove.'"></div>
                <div id="canvasContainer">
                    <canvas id="spinnerCanvas" height="550px" width="550px" 
                        data-responsiveMinWidth="180"
                        data-responsiveScaleHeight="true"   /* Optional Parameters */
                        data-responsiveMargin="50">
                        Canvas not supported, please user another browser.
                    </canvas>
                </div>
            ';
            $output .= '<div id="storageElementStatic" data-storeIt="';
            

            $output .= implode(',',$students);

            $output .= '"></div></div>';     

            $output .= "<div style='float:left;margin-top:40px'><br><br>
                    <h1 id='spinwinner' style='text-align:center;width:100%;color:white;background-color:navy;border-radius:15px;padding:20px 20px 20px 20px' name='spinwinner'>Student</h1>
                    <br><br><br><br>
                    <div class='button-link-special' onClick='startSpin()'>
                        Spin the Wheel
                    </div><br><br><br><br>";
                    $output.= "<a href='{$_SERVER['REQUEST_URI']}' class='button-link-special'>Close or Change Settings</a>";
        
                    if(isset($_POST["spinning_sound"])) {
                        $output .="<audio id='spinner-audio' src='".plugins_url( 'wheel-sound.mp3', __FILE__ )."' style='display:none'></audio>";
                    }
                $output .="</div>";
       
            $output .= '</div></div>'; //close modal
    
        } else {
            $classes = google_classroom_get_classes('select','spinclassid');

            $output = '<form id="spinnerform" method="POST" action="'.$_SERVER['REQUEST_URI'].'">
            <section>
                <div>'.
                "<label for='spinclassid'>Choose Your Class</label>".
                $classes.'
                </div>
            </section>
            <section>
                <div>
                <fieldset>
                    <legend> Extra Options</legend>
                        <input type="checkbox" name="spinning_sound" id="spinning_sound" checked><label for="spinning_sound" class="checkbox_label">Include Spinning Sound</label><br>
                        <input type="checkbox" name="remove_students" id="remove_students" checked><label for="remove_students" class="checkbox_label">Remove Names as They Are Picked</label><br>
                        <input type="checkbox" name="teachers_choice" id="teachers_choice"><label for="teachers_choice" class="checkbox_label">Include "Teacher\'s Choice" Option</label><br>
                        <input type="checkbox" name="last_picks" id="last_picks"><label for="last_picks" class="checkbox_label">Include "Last Person Picks" Option</label>
                </fieldset>
                </div>
            </section>
            <section>
                <input type="submit" value="Load Your Spinner">
            </section>
            </form>
            <h4>Some Quick Notes:</h4>
            <ul>
                <li><p>I usually treat an absent student as a "Teacher Picks" space</p></li>
                <li><p>If you are having it remove students, when it runs out of students,<br>
                the spinner is refilled again</p></li>
            </ul>';
        }   
  
        return $output;
    }

}


if(!function_exists('google_classroom_card_flip')) {

    function google_classroom_show_card_flip() {


        if(isset($_POST['flipclassid'])) {

            $class_id =  $_POST['flipclassid'];

            $temp_students = google_classroom_get_members($class_id);

            $students = array();

            foreach($temp_students as $student) {

                $name_array = explode(' ',$student);
                
                $students[] = $name_array[0].' '.$name_array[1][0];
            }

            if(isset($_POST['teachers_choice'])) {

                $students[] = 'Teacher Picks';
            }

            if(isset($_POST['last_picks'])) {
                $students[] = 'Last Picks';
            }

            shuffle($students);

            //Modal Stuff
            $output = '
            <!--Call your modal-->
            <h2>Your Cards are Ready! Click Below to Open Them</h2>
            <a id="animatedCards" href="#animatedCardsModal" class="button-link-special">Launch Cards</a>';
            
            
            $output .= '
            <div id="animatedCardsModal">
            <div class="modal-content" style="padding-top:25px;padding-left:15px">';
            $i=1;
            
            foreach($students as $student) {

                $student = str_replace(' ','<br><br>',$student);

                $output .= "<div id='card-{$i}' class='playing-card'>
                        <div class='card-front'>
                            <img src='".plugins_url( 'card-back.png', __FILE__ )."' class='playing-card-back'>
                        </div>
                        <div class='card-back'>
                            <br><br>$student
                        </div>
                       </div>";
                       $i++;
            }

            
            $output.= "<a href='{$_SERVER['REQUEST_URI']}' class='button-link-special' style='position: absolute;bottom:20px;right:10px;'>Close or Change Settings</a>";

            if(isset($_POST["flip_sound"])) {
                $output .="<audio id='flip-audio' src='".plugins_url( 'card-flip.wav', __FILE__ )."' style='display:none'></audio>";
            }

            $output .= '</div></div>'; //close modal  

        
        } else {
            $classes = google_classroom_get_classes('select','flipclassid');

            $output = '<form id="cardform" method="POST" action="'.$_SERVER['REQUEST_URI'].'">
            <section>
                <div>'.
                "<label for='flipclassid'>Choose Your Class</label>".
                $classes.'
                </div>
            </section>
            <section>
                <div>
                <fieldset>
                    <legend> Extra Options</legend>
                    <input type="checkbox" name="flip_sound" id="flip_sound" checked><label for="flip_sound" class="checkbox_label">Include Card Flipping Sound</label><br>
                        <input type="checkbox" name="teachers_choice" id="teachers_choice"><label for="teachers_choice" class="checkbox_label">Include "Teacher\'s Choice" Option</label><br>
                        <input type="checkbox" name="last_picks" id="last_picks"><label for="last_picks" class="checkbox_label">Include "Last Person Picks" Option</label>
                </fieldset>
                </div>
            </section>
            <section>
                <input type="submit" value="Prepare Your Cards">
            </section>
            </form>
            <h4>Some Quick Notes:</h4>
            <ul>
                <li><p>I usually treat an absent student as a "Teacher Picks" space</p></li>
            </ul>';
        }   
  
        return $output;
    }

}

if(!function_exists('google_classroom_show_signout_form')) {

    function google_classroom_show_signout_form() {

            $to_show = '';

            if(isset($_POST['class_for_checkout'])) {

                $class_id = $_POST['class_for_checkout'];

                $temp_students = google_classroom_get_members($class_id);

                $students = array();

                foreach($temp_students as $student) {

                    $name_array = explode(' ',$student);
                    
                    $students[] = $name_array[0].' '.$name_array[1][0];
                }
                
                if(!empty($students)) {

                    $student_table = '<!--Call your modal-->
                    <h2>Your Signout Sheet is Ready! Click Below to Open It</h2>
                    <a id="animatedSignout" href="#animatedSignoutModal" class="button-link-special">Launch Signout</a>';
            
            
                    $student_table .= '
            <div id="animatedSignoutModal">
            <div class="modal-content" style="padding-top:25px;padding-left:15px">';

                    $half = ceil(count($students)/2);
                    $one_third = ceil(count($students)/3);
                    $two_third = $one_third*2;

                    if(count($students) > 9 && count($students) < 19) {
                        $split_type = 'half';
                    } elseif (count($students) >=19) {
                        $split_type = 'third';
                    } else {
                        $split_type = 'none';
                    }

                    $student_table .= '
                    <table style="float:left;margin-right:40px" class="sign-in-and-out-table"><tr><th>Student</th><th>Force<br>Arrival</th><th>Location</th><th>In / Out</th></tr>';


                    foreach ($students as $key=>$student) {

                        if(($split_type == 'half' && $key == $half) || ($split_type=='third' && ($key == $one_third || $key==$two_third))) {
                            $student_table .='</table><table style="float:left;margin-right:40px" class="sign-in-and-out-table"><tr><th>Student</th><th>Force<br>Arrival</th><th>Location</th><th>In / Out</th></tr>';
                        }
                        $student_table .= "<tr><td style='padding-top:1px;padding-bottom:1px'><span id='name{$key}'>$student</span><br><span style='font-size:10px;color:red' id='time{$key}'></span></td>
                        <td id='forcecheckin{$key}' style='cursor:pointer'>
                        <img data-userid='{$key}' onclick='forceCheckin(this)' src='".plugins_url( 'icon-checkmark-circled.png', __FILE__ )."'>
                        </td>
                        <td>".google_classroom_out_locations($key)."</td>
                        <td class='onoffswitch'><div class='onoffswitch'>
                        <input type='checkbox' name='onoffswitch' class='onoffswitch-checkbox' value='{$key}' id='myonoffswitch{$key}' onchange='checkinout(this)'checked>
                        <label class='onoffswitch-label' for='myonoffswitch{$key}'>
                            <span class='onoffswitch-inner'></span>
                            <span class='onoffswitch-switch'></span>
                        </label></div>
                        </td>
                        </tr>";

                    }
                    $student_table .= '</table>';
                    
                    $student_table.= "
                    <div style='clear:both'>
                    <p style='float:left'>Use \"Force Arrival\" for students who come to the room without previously having been signed out (or if you accidentally close this page).</p>
                    <div style='clear:both'>
                    <a href='{$_SERVER['REQUEST_URI']}' class='button-link-special' style='float:left;'>Close / Choose Class</a>";

                    
                    $student_table.='</div></div>';

                    $to_show=$student_table.$to_show;
                } else {
                    $to_show .= '<h3>No Students Found In This Class: Choose Another</h3>';
                    $to_show .="<div id='checkout-form-container' style='float:left;margin-right:20px'>
                <form id='checkoutform' method='POST' action='{$_SERVER['REQUEST_URI']}' enctype='multipart/form-data'>
                    <section>
                        <div>
                            <label for='class_for_checkout'>Choose Class For Checkouts: </label>";

                            $to_show.= google_classroom_get_classes('select','class_for_checkout');

                    $to_show .= "</div></section>
                    <section>
                    <input type='submit' value='View Roster'>
                    </section>
                </form></div>";


                $to_show .= "<div style='margin-right:40px;margin-top:200px;float:left'><h4>Start New Signout Sheet</h4><p style='margin-bottom:10px'>The button below will allow you to switch to a new Sign Out Sheet. 
                This will NOT delete the old sheet from your Google Sheets, but will just create a new one and add new sign-outs to that.
                This lets you have distinct documents per month, quarter, year, whatever timeframe you would like.</p>
                <form id='change_sheet' method='POST' action='{$_SERVER['REQUEST_URI']}' enctype='multipart/form-data'>
                <input type='hidden' name='remove_stored' id='remove_stored' value='remove'>
                <input type='submit' value='Change Signout Sheet'>
                </form></div>";
                }


            } else {

                $to_show .="<div id='checkout-form-container' style='float:left;margin-right:20px'>
                <form id='checkoutform' method='POST' action='{$_SERVER['REQUEST_URI']}' enctype='multipart/form-data'>
                    <section>
                        <div>
                            <label for='class_for_checkout'>Choose Class For Checkouts: </label>";

                            $to_show.= google_classroom_get_classes('select','class_for_checkout');

                    $to_show .= "</div></section>
                    <section>
                    <input type='submit' value='View Roster'>
                    </section>
                </form></div>";


                $to_show .= "<div style='margin-right:40px;margin-top:200px;float:left'><h4>Start New Signout Sheet</h4><p style='margin-bottom:10px'>The button below will allow you to switch to a new Sign Out Sheet. 
                This will NOT delete the old sheet from your Google Sheets, but will just create a new one and add new sign-outs to that.
                This lets you have distinct documents per month, quarter, year, whatever timeframe you would like.</p>
                <form id='change_sheet' method='POST' action='{$_SERVER['REQUEST_URI']}' enctype='multipart/form-data'>
                <input type='hidden' name='remove_stored' id='remove_stored' value='remove'>
                <input type='submit' value='Change Signout Sheet'>
                </form></div>";

            }


            return $to_show;

    }

    function google_classroom_out_locations($id_num) {
        $locations = array('RR','Locker','Nurse','Office','Other');

        $temp_select = "<select id='destination{$id_num}'>";
        foreach($locations as $location) {
            if($location == 'RR') {
                $locationval = 'Restroom';
            } else {
                $locationval = $location;
            }
            $temp_select .= "<option value='$locationval'>$location</option>";
        }
        $temp_select .= '</select>';

        return $temp_select;
    }
}