<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (! function_exists('google_classroom_get_client')) {
    /*******************************************
     * 
     * Creates a client object using stored credentials. 
     * Renews access token if needed
     * 
     *******************************************/

    function google_classroom_get_client($token) {

        $decoded = gclassroom_wp_integration_decrypt_option(get_option('gclassroom_wp_integration_client_key'));

        if(!empty($decoded)) {

            $credentials = json_decode($decoded);

            $config = [
                'client_secret' => $credentials->web->client_secret,
                'client_id' => $credentials->web->client_id,
                'redirect_uri' => $credentials->web->redirect_uris[0],
                'project_id'=> $credentials->web->project_id
            ];

        } else { return 'Token not set up'; }     

        $client = new Google_Client($config);

        $client->addScope("https://www.googleapis.com/auth/classroom.courses");
        $client->addScope("https://www.googleapis.com/auth/classroom.rosters");
        $client->addScope("https://www.googleapis.com/auth/classroom.coursework.students");
        $client->addScope("https://www.googleapis.com/auth/classroom.announcements");
        $client->addScope('https://www.googleapis.com/auth/classroom.profile.emails');
        $client->setAccessType('offline');

        if(!empty($token)) {
            $client->setAccessToken($token);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $accessToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                update_option( 'classroom_auth_token', $accessToken);
                $client->setAccessToken($accessToken);
            } else {

                //Redirect to setup page here
            }
                    
        }
        return $client;
    }
}

if (!function_exists('google_classroom_get_classes')) {
    /****************************************************
     *  Returns (for use in a form) active classes assigned to the authenticated user
     * 
     * Params: $output = either select or checkbox 
     ****************************************************/

    function google_classroom_get_classes($output,$name_and_id = 'default') {
        
        if(!empty(get_option( 'classroom_auth_token'))) {
            // Get the API client and construct the service object.
            $newclient = google_classroom_get_client(get_option( 'classroom_auth_token'));

            $service = new Google_Service_Classroom($newclient);

            // Print the first 20 courses the user has access to.
            $optParams = array(
                'pageSize' => 50,
                'teacherId' => 'me',
                'courseStates' => 'ACTIVE'
            );
            $results = $service->courses->listCourses($optParams);

            if(empty($output)) { 
                $output = 'select';
            }

            $to_return = '';

            //If no courses found
            if (count($results->getCourses()) == 0) {
                return "No courses found.";
            }
            //If output type is select
            elseif ($output == 'select') {
          
                if ($name_and_id == 'default') { $name_and_id = 'singleclass'; }
                $to_return.="<select id='$name_and_id' name='$name_and_id'>";
               
                $course_array = array();
                foreach ($results->getCourses() as $course) {

                    $course_id = $course->getId();
                    $course_name = $course->getName();

                    $course_array["$course_id"] = $course_name;

                }

                uasort($course_array,'google_classroom_class_name_sort');

                foreach($course_array as $id=>$course) {               
               
                    $to_return .= "<option value='$id'>$course</option>";
                }
                $to_return.= '</select>';
                
                return $to_return;
            } 
            //If we want an array to populate Gravity form or the like
            elseif ($output == 'array') {

                $course_array = array();

                foreach ($results->getCourses() as $course) {

                    $course_id = $course->getId();
                    $course_name = $course->getName();

                    $course_array["$course_id"] = $course_name;
                }

                uasort($course_array,'google_classroom_class_name_sort');                

                return $course_array;
            }
            //If checkbox
            else {

                $course_array = array();
                foreach ($results->getCourses() as $course) {

                    $course_id = $course->getId();
                    $course_name = $course->getName();

                    $course_array["$course_id"] = $course_name;

                }

                uasort($course_array,'google_classroom_class_name_sort');

                foreach($course_array as $id=>$course) {
                    $to_return .= "<input type='checkbox' id='checkbox_for_{$id}' name='classes[]' value='$id'><label for='checkbox_for_{$id}' class='checkbox_label'>$course</label><br>";

                }
                return $to_return;
            }
        } else {
            return 'You must first set up this plugin.';
        }
    }
}

if (!function_exists('google_classroom_class_name_sort')) {
    function google_classroom_class_name_sort($a, $b) {
        preg_match('#\[(.*?)\]#', $a, $matcha);
        preg_match('#\[(.*?)\]#', $b, $matchb);
        
        if(isset($matcha[1]) && isset($matchb[1])) {
        
            return strcasecmp($matcha[1], $matchb[1]);
        } elseif (isset($matcha[1])) {
            return false;
        } elseif (isset($matchb[1])) {

            return true;
        }
        else {
            return false;
        }
    }
}

if (!function_exists('google_classroom_get_members')) {

    /*************************************************************
     *  Returns either a pre-built list of e-mails to copy / paste,
     *  or it can return an array of names for use in other things.
     *************************************************************/

    function google_classroom_get_members($class_id = '') {

        if(empty($class_id)) { return 'You must pass a class ID'; }

        $newclient = google_classroom_get_client(get_option( 'classroom_auth_token'));

        $service = new Google_Service_Classroom($newclient);

        $students = $service->courses_students->listCoursesStudents($class_id);

        $student_names = array();
        foreach($students as $student) {
            $student_names[] = ucwords(strtolower($student->profile->name->fullName));
        }

        usort($student_names, 'google_classroom_last_name_sort');
        return $student_names;
    }
}

if (!function_exists('google_classroom_last_name_sort')) {
    function google_classroom_last_name_sort($a, $b) {
        $atemp = explode(' ', $a);
        $btemp = explode(' ',$b);
        $aLast = end($atemp);
        $bLast = end($btemp);

        return strcasecmp($aLast, $bLast);
    }
}
if(!function_exists('google_classroom_invite_students')) {
    /*******************************************************
     * 
     *******************************************************/
    function google_classroom_invite_students($class_id = '',$students = array()) {


        if(empty($class_id)) { return 'You must pass a class ID'; }

        $to_return = '';
        $newclient = google_classroom_get_client(get_option( 'classroom_auth_token'));

        $service = new Google_Service_Classroom($newclient);

        foreach ($students as $email) {
            $googleInvitation = new Google_Service_Classroom_Invitation(
                array(
                    "role" => "STUDENT",
                    "userId" => "$email",
                    "courseId" => "$class_id"
                )
            );

            try{
                $invitationRes = $service->invitations->create($googleInvitation);

                $to_return .= $email.' inivited to classroom.<br>';

            }catch(Exception $exception){
                $errors = $exception->getErrors();

                $message = $errors[0]['message'];

                $to_return .= $email.'\'s invitation failed: '.$message.'<br>';
            }
        }

        return $to_return;
    }
}


if ( ! function_exists( 'google_classroom_send_assignment' ) ) {

    /**
	 * Sends an assignment to Google Classroom.
	 *
	 * @param $class_id (array: array of class IDs to send to)
     * @param $assignment_title (string: url of uploaded pdf)
     * @param $assignment_desc (string: description of assignment)
     * @param $duedate_raw (string: date in the format mm/dd/YYYY)
     * @param $link (string: link to single copy of file)
     **/

    function google_classroom_send_assignment($class_id,$assignment_title,$assignment_desc='',$duedate_raw,$duetime_raw = '11:59 PM',$link='') {

        $client = google_classroom_get_client(get_option( 'classroom_auth_token'));
        $service = new Google_Service_Classroom($client);
        $duedate = new Google_Service_Classroom_Date();
        $duetime = new Google_Service_Classroom_TimeOfDay();


        //Gets the timezone for the teacher
        $timezone = get_option('gclassroom_wp_integration_timezone');

        //Converts it to UTC
        $due_date_and_time = new DateTime($duedate_raw." $duetime_raw",new DateTimeZone("$timezone"));
        $due_date_and_time->setTimezone(new DateTimeZone('UTC'));
        
        //Get the date info
        $duedate_day = $due_date_and_time->format('d');
        $duedate_month = $due_date_and_time->format('m');
        $duedate_year = $due_date_and_time->format('Y');
        
        //Get the time info
        $utchour = $due_date_and_time->format('H');
        $utcmin = $due_date_and_time->format('i');

        //Update the Classroom Date and Time objects
        $duedate ->setDay("$duedate_day");
        $duedate ->setMonth("$duedate_month");
        $duedate ->setYear("$duedate_year");
        $duetime->setHours($utchour);
        $duetime->setMinutes("$utcmin");   

        //Create Coursework
        $coursework = new Google_Service_Classroom_CourseWork();
        $coursework->setTitle("$assignment_title");
        $coursework->setWorkType('ASSIGNMENT');  
        $coursework->setState('PUBLISHED');
        $coursework->setDescription("$assignment_desc");
        $coursework->setCourseId("$class_id");
        $coursework->setDueDate($duedate);
        $coursework->setDueTime($duetime);

        if (!empty($link)) {
            $coursework->setMaterials(array('link'=>array('url'=>"$link")));
        }
        $results = $service->courses_courseWork->create($class_id,$coursework);

        //return $results;
    }
}

if(!function_exists('google_classroom_extract_emails')) {
    /**
     * Pulls all e-mails from a passed string, returns as an array
     *  
     **/
    function google_classroom_extract_emails($str){
        // This regular expression extracts all emails from a string:
        $regexp = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';
        preg_match_all($regexp, $str, $m);
    
        return isset($m[0]) ? $m[0] : array();
    }
}