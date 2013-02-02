<?php
/*
Plugin Name: Surveys
Plugin URI: http://www.bin-co.com/tools/wordpress/plugins/surveys/
Description: The Surveys WordPress plugin lets you add surveys to your blog. You can let the vistors take surveys and see the result from the admin side.
Version: 1.01.8
Author: Binny V A
Author URI: http://www.binnyva.com/
*/

/**
 * Add a new menu under Manage, visible for all users with template viewing level.
 */
add_action( 'admin_menu', 'surveys_add_menu_links' );
function surveys_add_menu_links() {
	global $wp_version, $_registered_pages;
	$view_level= 2;
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	
	add_submenu_page($page, __('Manage Surveys', 'surveys'), __('Manage Surveys', 'surveys'), $view_level, 'surveys/survey.php');
	
	$code_pages = array('export.php','export_choose.php','individual_responses.php','question.php','question_form.php','responses.php','show_individual_response.php','survey_action.php','survey_form.php');
	foreach($code_pages as $code_page) {
		$hookname = get_plugin_page_hookname("surveys/$code_page", '' );
		$_registered_pages[$hookname] = true;
	}
}

add_action('init', 'surveys_init');
function surveys_init() {
	load_plugin_textdomain('surveys', false, dirname(plugin_basename( __FILE__ )).'/lang');
}

/**
 * This will scan all the content pages that wordpress outputs for our special code. If the code is found, it will replace the requested survey.
 */
add_shortcode( 'SURVEYS', 'surveys_shortcode' );
function surveys_shortcode( $attr ) {
	$survey_id = $attr[0];
	
	$contents = '';
	if(is_numeric($survey_id)) { // Basic validiation - more on the show_quiz.php file.
		ob_start();
		include(ABSPATH . 'wp-content/plugins/surveys/show_survey.php');
		$contents = ob_get_contents();
		ob_end_clean();
	}
	return $contents;
}

/// Add an option page for surveys.
add_action('admin_menu', 'surveys_option_page');
function surveys_option_page() {
	add_options_page(__('Surveys Settings', 'surveys'), __('Surveys Settings', 'surveys'), 8, basename(__FILE__), 'surveys_options');
}
function surveys_options() {
	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) die(__("Cheatin' uh?", 'surveys'));
	if (! user_can_access_admin_page()) wp_die( __('You do not have sufficient permissions to access this page.', 'surveys') );

	require(ABSPATH. '/wp-content/plugins/surveys/options.php');
}


add_action('activate_surveys/surveys.php','surveys_activate');
function surveys_activate() {
	global $wpdb;
	
	// Initial options.
	add_option('surveys_questions_per_page', 1);
	add_option('surveys_insert_csv_header', 1);
	
	$database_version = '5';
	$installed_db = get_option('surveys_db_version');
	
	if($database_version != $installed_db) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		// Create the table structure
		$sql = "CREATE TABLE {$wpdb->prefix}surveys_answer (
					ID int(11) unsigned NOT NULL auto_increment,
					question_ID int(11) unsigned NOT NULL,
					answer varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					sort_order int(3) NOT NULL,
					INDEX ( question_ID ),
					PRIMARY KEY  (ID)
					) ;
				CREATE TABLE {$wpdb->prefix}surveys_question (
					ID int(11) unsigned NOT NULL auto_increment,
					survey_ID int(11) unsigned NOT NULL,
					question mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					allow_user_answer int(1) NOT NULL default '0',
					allow_multiple_answers int(2) NOT NULL default '0',
					user_answer_format enum('entry','textarea','checkbox') NOT NULL default 'entry',
					PRIMARY KEY  (ID),
					KEY survey_id (survey_ID)
					) ;
				CREATE TABLE {$wpdb->prefix}surveys_result (
					ID int(11) unsigned NOT NULL auto_increment,
					survey_ID int(11) unsigned NOT NULL,
					name varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					email varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					added_on datetime NOT NULL,
					INDEX ( survey_ID ),
					PRIMARY KEY  (ID)
					) ;
				CREATE TABLE {$wpdb->prefix}surveys_result_answer (
					ID int(11) unsigned NOT NULL auto_increment,
					result_ID int(11) unsigned NOT NULL,
					answer_ID int(11) unsigned NOT NULL,
					question_ID INT( 11 ) UNSIGNED NOT NULL,
					user_answer TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					INDEX ( question_ID ),
					INDEX ( answer_ID ),
					INDEX ( result_ID ),
					PRIMARY KEY  (ID)
					) ;
				CREATE TABLE {$wpdb->prefix}surveys_survey (
					ID int(11) unsigned NOT NULL auto_increment,
					name varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					description mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					final_screen mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					status enum('1','0') NOT NULL default '0',
					added_on datetime NOT NULL,
					PRIMARY KEY  (ID)
					) ;";
		
		if($database_version == 2) {
			$wpdb->query("UPDATE {$wpdb->prefix}surveys_result_answer RA 
				SET question_ID=(SELECT question_ID FROM {$wpdb->prefix}surveys_answer WHERE ID=RA.answer_ID)");
		}
		dbDelta($sql);
		update_option( "surveys_db_version", $database_version );
	}
}
