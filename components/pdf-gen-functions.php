<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use setasign\Fpdi\Fpdi;


if ( ! function_exists( 'google_classroom_generate_pdf' ) ) {
	/**
	 * Generates a PDF for single version.
	 *
	 * @param $input_file (string: path to uploaded pdf)
     * @param $input_file_url (string: url of uploaded pdf)
     * @param $student_names (array: of student names)
     * @param $name_location (string: where to print names)
     * @param $hour_string (hour: class period)
     * @param $output_name (string: name of filled PDF generated)
     * @param $show_qr (string: should QR Code be generated)
     * 
	 * @return
	 */
	function google_classroom_generate_pdf($chosen_name,$input_file,$input_file_url,$student_names,$name_location,$hour_string,$output_name,$show_qr = 'yes',$marked_rb_circle = '') {


        $pdf = new Fpdi('P','mm','Letter');
        $pdf->setMargins(0,0,0,0);


        if($show_qr == 'rocket') {
            // let's get an id for the background template
            $pdf->setSourceFile(dirname(__FILE__).'/rocket-letter.pdf'); 
            $backId = $pdf->importPage(1);
        }

        //$input_file = '/sites/dandulaney.dev/files/wp-content/plugins/wp-google-classroom/components/template.pdf';

        $num_pages = $pdf->setSourceFile($input_file); 


        $filled_pdfs_folder = wp_get_upload_dir()['basedir'].'/filled-pdfs';
        $generated_qrs_folder = wp_get_upload_dir()['basedir'].'/qr-codes';

        //$filled_pdfs_folder = dirname(__DIR__, 1).'/filled-pdfs';
        //$generated_qrs_folder = dirname(__DIR__, 1).'/qr-codes';

        if (!file_exists("$filled_pdfs_folder")) {
            mkdir("$filled_pdfs_folder", 0755, true);
        }
        if (!file_exists("$generated_qrs_folder")) {
            mkdir("$generated_qrs_folder", 0755, true);
        }

        $pdfurl = wp_get_upload_dir()['baseurl'].'/filled-pdfs/'.$output_name.'.pdf';
        $pngurl = '';
        if ($show_qr == 'yes') {
            $png = QRcode::png("$input_file_url", $generated_qrs_folder.'/'.$output_name.'.png');
            $pngurl = wp_get_upload_dir()['baseurl'].'/qr-codes/'.$output_name.'.png';
        }

        $name_print_locations = array();

        if(strpos($name_location, 'x') !== false) {
            $name_print_locations[] = explode('x',$name_location);
        }

        for($i = 0; $i < count($student_names); $i++) {

            $student_name = ucwords(strtolower($student_names[$i]));

            $tplIdx = $pdf->importPage(1);
            $specs = $pdf->getTemplateSize($tplIdx);
            $height = $specs['height'];
            $width = $specs['width'];
            $pdf->AddPage($height > $width ? 'P' : 'L');

            if($show_qr == 'rocket') {
                $pdf->useTemplate($backId, 0, 0);
                
                $pdf->useTemplate($tplIdx, 13, 16, 190, 228);
                

            } else {

                $pdf->useTemplate($tplIdx, 0, 0);

            }
            $pdf->SetFont('Arial'); 
            $pdf->SetTextColor(0,0,0); 

            foreach ($name_print_locations as $location) {
                $pdf->SetXY($location[0], $location[1]);
                
                if($show_qr == 'rocket') {

                    $pdf->SetXY(15, 18);
                    $pdf->Write(0, "# # {$chosen_name} - {$hour_string} - {$student_name} # #");

                    if(!empty($marked_rb_circle)) {

                        switch($marked_rb_circle) {    

                            case 'rocket':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 27, 246, 9, 9, 'png'); //Rocket
                                break;

                            case 'diamond':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 51, 246, 9, 9, 'png'); //Diamond
                                break;

                            case 'apple':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 74, 246, 9, 9, 'png'); //Apple
                                break;

                            case 'bell':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 97, 246, 9, 9, 'png'); //Bell
                                break;

                            case 'clover':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 121, 246, 9, 9, 'png'); //Clover
                                break;

                            case 'star':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 144, 246, 9, 9, 'png'); //Star
                                break;
                            
                            case 'horseshoe':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 168, 246, 9, 9, 'png'); //Horseshoe
                                break;
                        }
                    }
                } else {
                
                    $pdf->Write(0, "{$student_name}");
                
                }
            }
            if (count($name_print_locations) == 1) {
                $pdf->SetXY(159, 8); 
 
                if($location[0] ==130 && $location[1]==15) {
                    $pdf->SetXY(175, 8);
                }

                if($show_qr != 'rocket') {
                
                    $pdf->Write(0, "{$hour_string}");

                }
 
                
            }

            if($show_qr == 'yes') {
                if($location[0] ==130 && $location[1]==15) {
                    $pdf->Image("$pngurl",80,4,22,'PNG');
                }
                else {
                    $pdf->Image("$pngurl",183,4,22,'PNG');
                }
            }

            for($z=1;$z < $num_pages;$z++) {
                $currpage = $z+1;
                $tplIdx = $pdf->importPage($currpage);
                $specs = $pdf->getTemplateSize($tplIdx);
                
                $height = $specs['height'];
                $width = $specs['width'];
                $pdf->AddPage($height > $width ? 'P' : 'L');
               
                if($show_qr == 'rocket') {
                    $pdf->useTemplate($backId, 0, 0);
                
                    $pdf->useTemplate($tplIdx, 13, 16, 190, 228);

                    if(!empty($marked_rb_circle)) {

                        switch($marked_rb_circle) {    

                            case 'rocket':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 27, 246, 9, 9, 'png'); //Rocket
                                break;

                            case 'diamond':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 51, 246, 9, 9, 'png'); //Diamond
                                break;

                            case 'apple':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 74, 246, 9, 9, 'png'); //Apple
                                break;

                            case 'bell':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 97, 246, 9, 9, 'png'); //Bell
                                break;

                            case 'clover':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 121, 246, 9, 9, 'png'); //Clover
                                break;

                            case 'star':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 144, 246, 9, 9, 'png'); //Star
                                break;
                            
                            case 'horseshoe':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 168, 246, 9, 9, 'png'); //Horseshoe
                                break;
                        }
                    }


                } else {

                    $pdf->useTemplate($tplIdx, 0, 0);

                }


            }
            if ($num_pages > 1 && $num_pages % 2 == 1) {

                $pdf->AddPage();
                
            }

        }
        
        $pdf->Output($filled_pdfs_folder.'/'.$output_name.'.pdf', 'F');

        return array('pdf'=>$pdfurl,'png'=>$input_file_url);
    }
}

if ( ! function_exists( 'google_classroom_generate_multiple_pdf' ) ) {
	/**
	 * Generates PDF for something with multiple versions
     *
     * @param $input_file (string: path to uploaded pdf)
     * @param $input_file_url (string: url of uploaded pdf)
     * @param $student_names (array: of student names)
     * @param $name_location (string: where to print names)
     * @param $hour_string (string: class period)
     * @param $output_name (string: name of filled PDF generated)
     * @param $show_qr (string: should QR Code be generated)
     * @param $pages_in_each (int: number of pages in each before starts again)
     * 
	 * @return
	 */
	function google_classroom_generate_multiple_pdf($chosen_name,$input_file,$input_file_url,$student_names,$name_location,$hour_string,$output_name,$show_qr = 'yes', $marked_rb_circle = '', $pages_in_each=1) {

        $pdf = new Fpdi('P','mm','Letter');
        $pdf->setMargins(0,0,0,0);


        if($show_qr == 'rocket') {
            // let's get an id for the background template
            $pdf->setSourceFile(dirname(__FILE__).'/rocket-letter.pdf'); 
            $backId = $pdf->importPage(1);
        }


        $num_pages = $pdf->setSourceFile($input_file); 


        $filled_pdfs_folder = wp_get_upload_dir()['basedir'].'/filled-pdfs';
        $generated_qrs_folder = wp_get_upload_dir()['basedir'].'/qr-codes';

        if (!file_exists("$filled_pdfs_folder")) {
            mkdir("$filled_pdfs_folder", 0755, true);
        }
        if (!file_exists("$generated_qrs_folder")) {
            mkdir("$generated_qrs_folder", 0755, true);
        }

        $pdfurl = wp_get_upload_dir()['baseurl'].'/filled-pdfs/'.$output_name.'.pdf';
        $pngurl = '';
        if ($show_qr == 'yes') {
            $png = QRcode::png("$input_file_url", $generated_qrs_folder.'/'.$output_name.'.png');
            $pngurl = wp_get_upload_dir()['baseurl'].'/qr-codes/'.$output_name.'.png';
        }

        $name_print_locations = array();

        if(strpos($name_location, 'x') !== false) {
            $name_print_locations[] = explode('x',$name_location);
        }

        $page_num_input = 0;

        for($i = 0; $i < count($student_names); $i++) {

            $page_num_input++;
            if($page_num_input > $num_pages) {
                $page_num_input = 1;
            }

            $student_name = ucwords(strtolower($student_names[$i]));

            $tplIdx = $pdf->importPage($page_num_input);
            $specs = $pdf->getTemplateSize($tplIdx);
            $height = $specs['height'];
            $width = $specs['width'];
            $pdf->AddPage($height > $width ? 'P' : 'L');


            if($show_qr == 'rocket') {
                $pdf->useTemplate($backId, 0, 0);
                
                $pdf->useTemplate($tplIdx, 13, 16, 190, 228);
                

            } else {

                $pdf->useTemplate($tplIdx, 0, 0);

            }

            $pdf->SetFont('Arial'); 
            $pdf->SetTextColor(0,0,0); 

            foreach ($name_print_locations as $location) {
                
                $pdf->SetXY($location[0], $location[1]); 
                
                if($show_qr == 'rocket') {

                    $pdf->SetXY(15, 18);
                    $pdf->Write(0, "# # {$chosen_name} - {$hour_string} - {$student_name} # #");

                    if(!empty($marked_rb_circle)) {

                        switch($marked_rb_circle) {    

                            case 'rocket':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 27, 246, 9, 9, 'png'); //Rocket
                                break;

                            case 'diamond':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 51, 246, 9, 9, 'png'); //Diamond
                                break;

                            case 'apple':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 74, 246, 9, 9, 'png'); //Apple
                                break;

                            case 'bell':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 97, 246, 9, 9, 'png'); //Bell
                                break;

                            case 'clover':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 121, 246, 9, 9, 'png'); //Clover
                                break;

                            case 'star':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 144, 246, 9, 9, 'png'); //Star
                                break;
                            
                            case 'horseshoe':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 168, 246, 9, 9, 'png'); //Horseshoe
                                break;
                        }
                    }


                } else {
                
                    $pdf->Write(0, "{$student_name}");
                
                }
            }

            if (count($name_print_locations) == 1) {
                $pdf->SetXY(159, 8);

                if($location[0] ==130 && $location[1]==15) {
                    $pdf->SetXY(175, 8);
                }

                if($show_qr != 'rocket') {
                    $pdf->Write(0, "{$hour_string}");
                }
            }

            if($show_qr == 'yes') {
                if($location[0] ==130 && $location[1]==15) {
                    $pdf->Image("$pngurl",80,4,22,'PNG');
                }
                else {
                    $pdf->Image("$pngurl",183,4,22,'PNG');
                }
            }            

            if($pages_in_each == 1) {
                continue;
            }

            for($z=2;$z <= $pages_in_each;$z++) {

                $page_num_input++;
                if($page_num_input > $num_pages) {
                    $page_num_input = 1;
                }
                
                $tplIdx = $pdf->importPage($page_num_input);
                $specs = $pdf->getTemplateSize($tplIdx);
                
                $height = $specs['height'];
                $width = $specs['width'];
                $pdf->AddPage($height > $width ? 'P' : 'L');

                if($show_qr == 'rocket') {
                    $pdf->useTemplate($backId, 0, 0);
                    
                    $pdf->useTemplate($tplIdx, 13, 16, 190, 228);

                    if(!empty($marked_rb_circle)) {

                        switch($marked_rb_circle) {    

                            case 'rocket':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 27, 246, 9, 9, 'png'); //Rocket
                                break;

                            case 'diamond':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 51, 246, 9, 9, 'png'); //Diamond
                                break;

                            case 'apple':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 74, 246, 9, 9, 'png'); //Apple
                                break;

                            case 'bell':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 97, 246, 9, 9, 'png'); //Bell
                                break;

                            case 'clover':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 121, 246, 9, 9, 'png'); //Clover
                                break;

                            case 'star':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 144, 246, 9, 9, 'png'); //Star
                                break;
                            
                            case 'horseshoe':
                                $pdf->Image(dirname(__FILE__).'/black-circle.png', 168, 246, 9, 9, 'png'); //Horseshoe
                                break;
                        }
                    }

    
                } else {
    
                    $pdf->useTemplate($tplIdx, 0, 0);
    
                }
    
            }

            if ($pages_in_each > 1 && $pages_in_each % 2 == 1) {

                $pdf->AddPage();
                
            }

        }
        
        $pdf->Output($filled_pdfs_folder.'/'.$output_name.'.pdf', 'F');
        return array('pdf'=>$pdfurl,'png'=>$input_file_url);
    }
}