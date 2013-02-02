<?php
include('../../../wp-blog-header.php');
auth_redirect();
include('wpframe.php');

// Export data as a CSV File
$survey_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}surveys_survey WHERE ID=%d", $_REQUEST['survey']));
$all_results = $wpdb->get_results($wpdb->prepare("SELECT ID,added_on, name, email FROM {$wpdb->prefix}surveys_result WHERE survey_ID=%d ORDER BY added_on", $_REQUEST['survey']));
$questions = $wpdb->get_results($wpdb->prepare("SELECT ID, question FROM {$wpdb->prefix}surveys_question WHERE survey_ID=%d", $_REQUEST['survey']));

$survey_name = preg_replace('/\W/', '_', $survey_name);
$survey_name = preg_replace('/_{2,}/', '_', $survey_name);

// header("Content-type:text/octect-stream");
// header("Content-Disposition:attachment;filename=$survey_name.csv");
header("Content-type:text/plain");

// Show the question at the top
$show_questions_in_header = get_option('surveys_insert_csv_header');

if($show_questions_in_header) {
	$fields = array();
	if(!empty($_REQUEST['survey_id'])) $fields[] = "ID";
	if(!empty($_REQUEST['added_on'])) $fields[] = "Date";
	if(!empty($_REQUEST['name'])) $fields[] = "Name";
	if(!empty($_REQUEST['email'])) $fields[] = "Email";
	if(!empty($_REQUEST['answers'])) {
		foreach($questions as $q) 
			$fields[] = str_replace(array("\n", '"'), array('',"'"), stripslashes($q->question));
	}
	print '"' . implode('","', $fields) . "\"\n";
}

foreach($all_results as $result) {
	$answers = array();
	
	if(!empty($_REQUEST['survey_id'])) $answers[] = $result->ID;
	if(!empty($_REQUEST['added_on'])) $answers[] = $result->added_on;
	if(!empty($_REQUEST['name'])) $answers[] = $result->name;
	if(!empty($_REQUEST['email'])) $answers[] = $result->email;
	if(!empty($_REQUEST['answers'])) {
		foreach($questions as $q) {
			// Get all the answers for this Question in this result set.
			$all_answers_for_question = $wpdb->get_results($wpdb->prepare("SELECT A.answer,RA.answer_ID,RA.user_answer 
					FROM {$wpdb->prefix}surveys_result_answer AS RA 
					LEFT JOIN {$wpdb->prefix}surveys_answer AS A 
					ON A.ID=RA.answer_ID WHERE RA.result_ID=%d AND RA.question_ID=%d", $result->ID, $q->ID));
			
			//If this quesiton has multiple answers...
			$selected_answers = array();
			foreach($all_answers_for_question as $one_answer) {
				if($one_answer->answer_ID) $selected_answers[] = stripslashes($one_answer->answer);
				else $selected_answers[] = stripslashes($one_answer->user_answer); //Custom User answer.
			}
			$answers[] = implode('|', $selected_answers); //Custom answer.
		}
	}
	print '"' . implode('","', $answers) . "\"\n";
}
