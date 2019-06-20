<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use setasign\Fpdi\Fpdi;


if ( ! function_exists( 'gclassroom_wp_integration_init' ) ) {
	/**
	 * Registers the pre_update_option filter for elements to encrypt.
	 *
	 * @param None
	 * @return None
	 */
    function gclassroom_wp_integration_init() {
	    add_filter( 'pre_update_option_gclassroom_wp_integration_client_key', 'gclassroom_wp_integration_update_option', 10, 2 );
    }
    add_action( 'init', 'gclassroom_wp_integration_init' );
}

if ( ! function_exists( 'gclassroom_wp_integration_update_option' ) ) {
	/**
	 * Converts option value to encrypted form, only if it has changed.
	 *
	 * @param $new_value (value sent from options page), $old_value (stored value for this option)
	 * @return $new_value (encrypted value of new option if changed, old encrypted value if not changed)
	 */
    function gclassroom_wp_integration_update_option( $new_value, $old_value ) {
        if ($new_value != $old_value && !empty($new_value)) {

            $credentials = json_decode($new_value);

            if(!empty($credentials->web->client_secret)) {

                $cypher = 'aes-256-cbc';
		        $key = md5(SECURE_AUTH_SALT);
                $new_value = openssl_encrypt("$new_value","$cypher","$key");
            } else {

                $new_value = 'Credentials Not Valid';
            }
            delete_option('classroom_auth_token');
	    } elseif ($new_value == '') {
            delete_option('classroom_auth_token');
        }
	    return $new_value;
    }
}

if ( ! function_exists( 'gclassroom_wp_integration_decrypt_option' ) ) {
	/**
	 * Decrypts options encrypted in the database
	 *
	 * @param $option (stored encrypted option value)
	 * @return $decrypted_value (decrypted value of option stored in DB)
	 */
    function gclassroom_wp_integration_decrypt_option($option) {
	    $cypher = 'aes-256-cbc';
	    $key = md5(SECURE_AUTH_SALT);
	    $decrypted_value = openssl_decrypt("$option","$cypher","$key");
	    return $decrypted_value;
    }
}

if ( ! function_exists( 'gclassroom_wp_integration_settings' ) ) {
	/**
	 * Registers plugin settings
	 *
	 * @param None
	 * @return None
	 */
    add_action( 'admin_init', 'gclassroom_wp_integration_settings' );
    function gclassroom_wp_integration_settings() {
	    register_setting( 'gclassroom-wp-integration-settings-group', 'gclassroom_wp_integration_client_key' );
	    register_setting( 'gclassroom-wp-integration-settings-group', 'gclassroom_wp_integration_timezone' );    
    }
}
if ( ! function_exists( 'gclassroom_wp_integration_menu' ) ) {
	/**
	 * Adds a link under the Settings submenu of the Admin dashboard for Classroom oAuth2 settings
	 *
	 * @param None
	 * @return None
	 */
    add_action('admin_menu', 'gclassroom_wp_integration_menu');
    function gclassroom_wp_integration_menu() {
        $icon_url = 'dashicons-welcome-learn-more';

        add_menu_page( 'Google Classroom', 'Google Classroom', 'manage_options', 'google-classroom', 'gclassroom_wp_integration_display_settings', $icon_url);
	    


        if(!empty(get_option( 'classroom_auth_token'))) {
            add_submenu_page('google-classroom', 'Create Assignement', 'Create Assignment', 'manage_options', 'google-classroom-assignment', 'gclassroom_wp_integration_display_assignment');
            add_submenu_page('google-classroom', 'Bulk Add Students', 'Bulk Add Students', 'manage_options', 'google-classroom-bulk-add', 'gclassroom_wp_integration_display_bulk_add');
            add_submenu_page('google-classroom', 'Spinner', 'Class Spinner', 'manage_options', 'google-classroom-class-spinner', 'gclassroom_wp_integration_display_class_spinner');       
            add_submenu_page('google-classroom', 'Sorting Sticks', 'Sorting Sticks Calc', 'manage_options', 'google-classroom-sorting-sticks-calc', 'gclassroom_wp_integration_display_sorting_sticks_calc');       

        }
        
    }
    
}

if ( ! function_exists( 'gclassroom_wp_integration_display_settings' ) ) {
	/**
	 * Function to display settings page for plugin
	 *
	 * @param None
	 * @return None
	 */
    function gclassroom_wp_integration_display_settings() {

        $encoded_access = get_option('gclassroom_wp_integration_client_key');

        if(!empty($encoded_access)) {

            $decoded = gclassroom_wp_integration_decrypt_option($encoded_access);

            $credentials = json_decode($decoded);

             $config = [
                'client_secret' => $credentials->web->client_secret,
                'client_id' => $credentials->web->client_id,
                'redirect_uri' => $credentials->web->redirect_uris[0],
                'project_id'=> $credentials->web->project_id
            ];

            $redirect_uri = $config['redirect_uri'];


            if(!empty($config['client_secret'])) {
                
                
                $client = new Google_Client($config);

                $client->addScope("https://www.googleapis.com/auth/classroom.courses");
                $client->addScope("https://www.googleapis.com/auth/classroom.rosters");
                $client->addScope("https://www.googleapis.com/auth/classroom.coursework.students");
                $client->addScope("https://www.googleapis.com/auth/classroom.announcements");
                $client->addScope('https://www.googleapis.com/auth/classroom.profile.emails');
                $client->setAccessType('offline');
                $client->setApprovalPrompt('force');
                $authUrl = $client->createAuthUrl();


                if (isset($_GET['code'])) {
                    //Get token for first time

                    $issue = false;
                    try{
                        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        
                    }catch(Exception $exception){
                        $errors = $exception->getErrors();
                        $issue = true;
                        delete_option('classroom_auth_token');

                    }
                
                    if(!$issue) {
                        //$client->setAccessToken($token);
                
                        update_option( 'classroom_auth_token', $token);
                        
                        echo "<script>
                        window.location.replace('$redirect_uri'); 
                        </script>";                    
                    }
                }    

            } else {

                delete_option('classroom_auth_token');
                echo '<h3 style="color:red">Invalid Credentials. Please copy again.</h3>';
            }
            

        } 
            

        echo '
        <h1>Google Classroom Management</h1>';
        
            echo "<button onClick='showDetailedInstructions()'>Show / Hide Setup Instructions</button>";

            //first part here displays a form to change the settings
            echo "<form method=\"post\" action=\"options.php\">";
            settings_fields( 'gclassroom-wp-integration-settings-group' );
            do_settings_sections( 'gclassroom-wp-integration-settings-group' );
            
            echo "
                
            <style>.seperator { border-bottom: 1px solid black; }</style>
                
            <div><h2>Classroom oAuth2 Settings</h2>";
  
            if (isset($authUrl)) {

                echo "<div class='request'>
                    <a class='login' href='$authUrl' style='color:green'>Click to Connect / Reconnect To Google Classroom</a>
                    <p>You should only need to click connect once, unless you change your JSON credentials below.</p>
                </div>";

            } 

            $full_callback = get_site_url().$_SERVER['REQUEST_URI'];

                echo "<div id='detailedinstructions' class='instructions' style='display:none'>
                    <h4>Detailed Setup Instructions</h4>
                    <ol>
                        <li><p>
                            First, copy this link: <input type='text' style='width:600px' value='{$full_callback}'>
                        </p></li>
                        <li><p>
                            Next, go to the Google Developers Console project creation page, by clicking <a href='https://console.developers.google.com/projectcreate' target='_blank'>here</a> (opens in new window). Be sure that you are on your school account.
                        </p></li>
                        <li><p>
                            Follow the steps from <a href='https://dulaney.fleeq.io/l/classroom-setup-plugin' target='_blank'>this video (click to open in new tab)</a>. Return here once you've downloaded your credentials file. 
                        </p></li>
                        <li><p>
                            Copy and paste the entire contents of your credentials file in the text box below.
                        </p></li>
                        <li><p>
                            Pick an appropriate timezone for your classroom</p>
                        </p></li>
                        <li><p>
                            Click 'Update My Google Settings'. This will cause the page to reload, and a new link to appear if everything was successful.
                        </p></li>
                        <li><p>
                            Click the link that says \"Click to Connect / Reconnect To Google Classroom\". Authorize the application with your school account.
                        </p></li>
                    </ol>
                    <p>That's it! You should now see additional tabs on this page, to add new assignments or to bulk-add students.</p>
                </div>";
            
            
            echo "<table class=\"form-table\"> 
            <tr valign=\"top\">
                <th scope=\"row\">JSON Credentials</th>
                <td><textarea name=\"gclassroom_wp_integration_client_key\" style='width:100%'>".esc_attr( get_option('gclassroom_wp_integration_client_key') )."</textarea></td>
                <td><p>Paste your JSON Credentials here.</p></td>
            </tr>
            <tr valign=\"top\">
                <th scope=\"row\">Class Timezone</th>
                <td>
                    <select name=\"gclassroom_wp_integration_timezone\">";            

                        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                        foreach($tzlist as $timezone) {
                            if(get_option('gclassroom_wp_integration_timezone') == $timezone) {

                                echo "<option value='$timezone' selected='selected'>$timezone</option>";

                            } else {
                                echo "<option value='$timezone'>$timezone</option>";
                            }
                        }
                    echo "</select>
                </td>
                <td><p>Choose the timezone your classes are in</p></td>
            </tr>"
            ;
            echo "</table>";

            submit_button('Update My Google Settings');
            echo "</form>";

        
    }
        
}


if(! function_exists('gclassroom_wp_integration_display_assignment')) {
    function gclassroom_wp_integration_display_assignment() {
 
        echo "<div>
                <h1>Generate New Assignments (PDF)</h1>";


                if(!isset($_POST['assignment_title'])) {
                    echo google_classroom_show_assignment_form();
                    echo "
                    <br><br>";
                }
                else {

                    //Builds output name from Assignment Title

                    $output_name = preg_replace("/[^A-Za-z0-9 ]/", '', $_POST['assignment_title']);
                    $output_name = str_replace(' ','_',strtolower($output_name));

                    if(isset($_FILES['pdf_worksheet'])) {
                        //Gets current time
                        $time = time();

                        //Gets folder and partial URL for the single uploaded files
                        $upload_path = wp_get_upload_dir()['basedir'].'/assignments'."/$time";
                        $upload_url_base = wp_get_upload_dir()['baseurl'].'/assignments'."/$time";

                        //Creates the folder to hold this particular round of uploaded assignments
                        if (!file_exists("$upload_path")) {
                            mkdir("$upload_path", 0755, true);
                        }

                        $target_file_path = $upload_path . "/$output_name"."-$time".".pdf";

                        if (move_uploaded_file($_FILES["pdf_worksheet"]["tmp_name"], $target_file_path)) {

                            $target_file_url = str_replace($upload_path,$upload_url_base,$target_file_path);

                        }
                    }


                    $qr_code_needed = $_POST['add_qr'];
                    $name_location = $_POST['name_placement'];
                    $duedate = $_POST['due_date'];
                    $duetime = $_POST['due_time'];
                    if(empty($duetime)) {
                        $duetime = '11:59 PM';
                    }
                    $version = $_POST['versioning'];
                    $pages_in_each = $_POST['pages_per_version'];
                    $class_names = google_classroom_get_classes('array'); //array with key as class ID, name as value
                    $classes = $_POST['classes']; //array

                    $returnedurls=array();
                                        
                    if($version == 'multiple') {
                        $pdf_temp = new Fpdi();
                        $num_pages = $pdf_temp->setSourceFile($target_file_path);
                
                        for($i = 1; $i <= $pages_in_each; $i++) {
                
                            $tplIdx = $pdf_temp->importPage($i);
                            $specs = $pdf_temp->getTemplateSize($tplIdx);
                            $height = $specs['height'];
                            $width = $specs['width'];
                            $pdf_temp->AddPage($height > $width ? 'P' : 'L');
                            $pdf_temp->useTemplate($tplIdx, 0, 0);
                        }
                        if($pages_in_each > 2 && $num_pages % 2 == 1) {
                
                            $pdf_temp->AddPage();
                
                        }
                
                        $modded_target_path = str_replace('.pdf','-mod.pdf',$target_file_path);
                        $modded_target_url = str_replace('.pdf','-mod.pdf',$target_file_url);
                
                        $pdf_temp->Output("$modded_target_path", 'F');
                        
                    }
                
                    foreach ($classes as $class_id) {
                
                        $class_name = $class_names[$class_id];

                        $output_name_temp = $output_name.'_'.$class_id.'_hour';

                        preg_match('#\[(.*?)\]#', $class_name, $match);
                        
                        if(!empty($match)) {
                            $hour_string = $match[1];
                        } else {
                            $hour_string = '';
                        }
                        //Get student names from Google Classroom API
                        $student_names =  google_classroom_get_members("$class_id");

                        //Extra Students for Testing and sharing
                        //array_unshift($student_names,'Student Two');
                        //array_unshift($student_names,'Student One');
                        
                        array_unshift($student_names,'Teacher Copy');

                        if($version == 'single') {
                            $returnedurls["$class_id"] = google_classroom_generate_pdf($target_file_path,$target_file_url,$student_names,$name_location,$hour_string,$output_name_temp,$qr_code_needed);
                        } elseif ($version == 'multiple') {
                            $returnedurls["$class_id"] = google_classroom_generate_multiple_pdf($target_file_path,$modded_target_url,$student_names,$name_location,$hour_string,$output_name_temp,$qr_code_needed,$pages_in_each);
                        }
                    }


                    $confirmation = '<h3>Clicking each file will open it, in a new tab</h3> <ul>';
    
                    foreach($returnedurls as $class_id => $urls) {

                        $confirmation.= "<li><a target='_blank' href='{$urls['pdf']}'>Modded PDF: {$class_names[$class_id]}</a></li>";


                        if ($_POST['send_to_classroom']== 'yes') {

                            $assignment_title = $_POST['assignment_title'];
                            $assignment_desc = $_POST['assignment_description'];
                            $link = $urls['png'];
                
                            $checkit = google_classroom_send_assignment($class_id,$assignment_title,$assignment_desc,$duedate,$duetime,$link);

                        }

                    }
                    $confirmation .= '</ul>';
                    
                    echo $confirmation;

                    echo "<br><p>Note: You can click the link below to add another assignment. ONLY do this once you are done with the links above. You can, if you choose, bookmark or print the above links, but there is no easy way to view them again otherwise.</p><a href='javascript:window.location.reload(true)'>Add Another Assignment</a>";

                }
            echo "</div>";
 
    }
}

if(!function_exists('gclassroom_wp_integration_display_bulk_add')) {
 
    function gclassroom_wp_integration_display_bulk_add() {

        echo "<div>
        <h1>Bulk Invite Students to a Class</h1>";
    
        if(!isset($_POST['email_string'])) {
            echo google_classroom_show_bulk_add_form();
            echo "
            <br><br>";
        }
        else {

            $emails_array = google_classroom_extract_emails($_POST['email_string']);

            if(!empty($emails_array)) {

                $class_id =$_POST['singleclass'];

                echo google_classroom_invite_students($class_id,$emails_array);

            } else {
                echo 'No e-mails found. Please try again.';
            }

            echo "<br><p>Click the link below to add more students.</p>
            <a href='javascript:window.location.reload(true)'>Add More Students</a>";

        }

        echo "
        </div>";
    }
    
}

if (!function_exists('gclassroom_wp_integration_display_class_spinner')) {
    function gclassroom_wp_integration_display_class_spinner() {

        echo "<h1>Student Spinner</h1>
        <div style='float:left;margin-right:20px;margin-left:20px;margin-top:10px'>";
        
        echo google_classroom_show_spinner();
        
        echo "</div>";

    }
}

if (!function_exists('gclassroom_wp_integration_display_sorting_sticks_calc')) {
    function gclassroom_wp_integration_display_sorting_sticks_calc() {
 
        echo "<div id='sorting-sticks-calc'>
        <h1>Sorting Sticks Calculator</h1>";
    
            echo '<label for="number_of_students">Number of Students: </label>
            <input type="number" name="number_of_students" id="number_of_students" onKeyUp="sorting_sticks(this.value)" onChange="sorting_sticks(this.value)">';

            echo "<br><br><table id='sorting_sticks_table'>
                <tr><th>Type of Sorting</th><th>Students / Group</th><th># of Groups</th><th>Evenly Divided?</th></tr>
                <tr>
                    <td>Numbers</td><td id='numbers_per'></td>
                    <td id='numbers_groups'>2</td><td id='numbers_extra'></td>
                </tr>
                <tr>
                    <td>Color of Shape</td><td id='color_shape_per'></td>
                    <td id='color_shape_groups'>3</td><td id='color_shape_extra'></td>
                </tr>
                <tr>
                    <td>Color of Bar (Top)</td><td id='color_bar_per'></td>
                    <td id='color_bar_groups'>4</td><td id='color_bar_extra'></td>
                </tr>
                <tr>
                    <td>Transportation</td><td id='trans_per'></td>
                    <td id='trans_groups'>5</td><td id='trans_extra'></td>
                </tr>
                <tr>
                    <td>Shape</td><td id='shape_per'></td>
                    <td id='shape_groups'>6</td><td id='shape_extra'></td>
                </tr>
                <tr>
                    <td>Sports</td><td id='sport_per'></td>
                    <td id='sport_groups'>7</td><td id='sport_extra'></td>
                </tr>
                <tr>
                    <td>Alphabet</td><td id='alpha_per'>2</td>
                    <td id='alpha_groups'></td><td id='alpha_extra'></td>
                </tr>
            </table>
            <h2>About Sorting Sticks</h2>
            <p>Sorting Sticks are a free tool from <a href='http://www.ilclassroomsinaction.org' target='_blank'>ilclassroomsinaction.org's Collaboration Kit</a>, for promoting collaborative learning. Students are given a stick as they enter the classroom, 
            and then only once the teacher is ready are they told which symbol to match. This lets teachers group students in a variety of ways, without them knowing in advance.
            This does require a little prep work on the part of the teacher, since you'll want to pre-select the sticks to pass out. This tool is intended to help with that process.</p>
            <p>The standard kit includes 40 sticks, and can be downloaded (and printed) for free at <a href='http://www.ilclassroomsinaction.org/uploads/1/2/0/9/120982154/sorting_sticks__1_.pdf' target='_blank'>this link.</a>
             For instructions on how to use the sticks, check out <a href='http://www.ilclassroomsinaction.org/uploads/1/2/0/9/120982154/final_sorting_guide__1_.pdf' target='_blank'>this guide.</a>";
 
        echo "</div>";       
    }
}