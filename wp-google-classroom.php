<?php
/*
Plugin Name: WP Google Classroom
Plugin URI: https://mathwithmrdulaney.com/google-classroom-plugin
Description: WP Integration with Google Classroom
Version: 2.4
Author: Dan Dulaney
Author URI: https://dandulaney.com
GitHub Plugin URI: https://github.com/duplaja/wp-google-classroom
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2019 by Dan Dulaney <dan.dulaney07@gmail.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/duplaja/wp-google-classroom/',
	__FILE__,
	'wp-google-classroom'
);

$myUpdateChecker->setBranch('master');

use setasign\Fpdi\Fpdi;
require_once(dirname( __FILE__ ).'/vendor/autoload.php');
require_once(dirname( __FILE__ ).'/components/settings.php');
require_once(dirname( __FILE__ ).'/components/settings-generators.php');
require_once(dirname( __FILE__ ).'/components/classroom-helper-functions.php');
require_once(dirname( __FILE__ ).'/components/pdf-gen-functions.php');




function google_classroom_enqueue_scripts($hook) {

    if ( 'toplevel_page_google-classroom' == $hook || 'google-classroom_page_google-classroom-bulk-add' == $hook) {

        wp_enqueue_script('jquery-validate',plugins_url('js/jquery.validate.min.js', __FILE__),array('jquery'),'1.0');
        wp_enqueue_script( 'classroom-admin-js', plugins_url( 'js/admin.js', __FILE__ ),array('jquery','jquery-validate'), '1.0');
        wp_enqueue_style( 'admin-css', plugins_url( 'css/admin.css', __FILE__ ),array(), '1.2');

    }
    elseif( 'google-classroom_page_google-classroom-assignment'==$hook ) {

        wp_enqueue_script('jquery-validate',plugins_url('js/jquery.validate.min.js', __FILE__),array('jquery'),'1.0');
        wp_enqueue_script('jquery-timepicker-js',plugins_url('js/jquery.timepicker.min.js',__FILE__),array('jquery'),'1.0');
        wp_enqueue_script( 'classroom-assignment-js', plugins_url( 'js/create-assignment.js', __FILE__ ),array('jquery','jquery-ui-datepicker','jquery-timepicker-js','jquery-validate'), '1.0');

        wp_enqueue_style( 'admin-css', plugins_url( 'css/admin.css', __FILE__ ),array(), '1.2');
        wp_enqueue_style( 'jquery-timepicker-css', plugins_url( 'css/jquery.timepicker.min.css', __FILE__ ),array(), '1.0');
        wp_enqueue_style( 'jquery-ui-css', plugins_url( 'css/jquery-ui.css', __FILE__ ),array(), '1.1');
 
    } elseif ('google-classroom_page_google-classroom-class-spinner'==$hook) {
     
        wp_enqueue_style( 'admin-css', plugins_url( 'css/admin.css', __FILE__ ),array(), '1.2');
        wp_enqueue_style( 'animate-css', plugins_url( 'css/animate.min.css', __FILE__ ),array(), '1.0');

        wp_enqueue_script('winwheel',plugins_url('js/winwheel.min.js', __FILE__),array('tweenmax','jquery'),'1.0');
        wp_enqueue_script('tweenmax',plugins_url('js/TweenMax.min.js', __FILE__),array('jquery'),'1.0');
        wp_enqueue_script( 'animated-modal', plugins_url( 'js/animatedModal.min.js', __FILE__ ),array('jquery'), '1.0');
        wp_enqueue_script( 'create-spinner', plugins_url( 'js/create-spinner.js', __FILE__ ),array('jquery','winwheel','tweenmax','animated-modal'), '1.0');
    
    } elseif ('google-classroom_page_google-classroom-class-card'==$hook) {
        
        wp_enqueue_style( 'admin-css', plugins_url( 'css/admin.css', __FILE__ ),array(), '1.2');
        wp_enqueue_style( 'animate-css', plugins_url( 'css/animate.min.css', __FILE__ ),array(), '1.0');

        wp_enqueue_script( 'animated-modal', plugins_url( 'js/animatedModal.min.js', __FILE__ ),array('jquery'), '1.0');
        wp_enqueue_script( 'jquery-flip', plugins_url( 'js/flip.js', __FILE__ ),array('jquery'), '1.0');
        wp_enqueue_script( 'create-cards', plugins_url( 'js/create-cards.js', __FILE__ ),array('jquery','jquery-flip','animated-modal'), '1.0');
        
    }
    elseif ('google-classroom_page_google-classroom-sorting-sticks-calc'==$hook) { 

        wp_enqueue_style( 'admin-css', plugins_url( 'css/admin.css', __FILE__ ),array(), '1.2');
        wp_enqueue_script('sorting-sticks',plugins_url('js/sorting-sticks.js', __FILE__),array('jquery'),'1.0');

    } elseif ('google-classroom_page_google-classroom-class-sign' == $hook) {

        wp_enqueue_style( 'admin-css', plugins_url( 'css/admin.css', __FILE__ ),array(), '1.2');
        wp_enqueue_style( 'animate-css', plugins_url( 'css/animate.min.css', __FILE__ ),array(), '1.0');

        wp_enqueue_script( 'animated-modal', plugins_url( 'js/animatedModal.min.js', __FILE__ ),array('jquery'), '1.0');
        wp_enqueue_script('signout-js',plugins_url('js/signout.js', __FILE__),array('jquery','animated-modal'),'1.0');

    } else {
        return;
    }

}
add_action( 'admin_enqueue_scripts', 'google_classroom_enqueue_scripts' );



function google_classroom_dashboard_add_widgets() {
	wp_add_dashboard_widget( 'google_classroom_dashboard_widget_news', __( 'Google Teacher Tutorials', 'dw' ), 'google_classroom_dashboard_widget_news_handler' );
	wp_add_dashboard_widget( 'google_classroom_dashboard_widget_tips', __( 'Classroom Plugin Setup Tips', 'dw' ), 'google_classroom_dashboard_widget_tips_handler' );

}
add_action( 'wp_dashboard_setup', 'google_classroom_dashboard_add_widgets' );

function google_classroom_dashboard_widget_news_handler() {
    $to_return ='<ul>
        <li>
            <a target="_blank" href="https://mathwithmrdulaney.com/google-teacher-tips">Video Tips and Tricks</a>
        </li>
        <li>
            <a target="_blank" href="https://mathwithmrdulaney.com/google-classroom-plugin">Google Classroom Plugin Details</a>
        </li>
        <li>
            <a target="_blank" href="https://homeworkcaddy.com">Hosted Version of Classroom Plugin</a>        
        </li>
        </ul>';
    echo $to_return;
}

function google_classroom_dashboard_widget_tips_handler() {
    $to_return ='<ul>
        <li>
            <p>* This plugin only lists active classrooms. Archive any old unused ones to clear clutter.</p>
        </li>
        <li>
            <p>* The Hour / Period on printed sheets comes from anything in brackets in the classroom name. For example, if you have: <b>Algebra 1 [2nd Hr]</b> as the class name, then <b>2nd Hr</b> will show as the period on the sheets. Keep this short!</p>
        </li>
        <li>
            <p>* In line with the previous tip, the display order for classes goes by hour (within brackets) first, then newest created to oldest. 
            If you want your classes in order they happen here, give them pieces of names like [1st Hr], [2nd Hr], [3rd Hr] in the titles.</p>
        </li>
        </ul>';
    echo $to_return;
}
