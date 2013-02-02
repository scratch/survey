<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$result_id = $_REQUEST['result'];

$survey = $wpdb->get_row("SELECT S.ID, S.name, R.name AS rname, R.email, R.added_on FROM {$wpdb->prefix}surveys_survey AS S INNER JOIN {$wpdb->prefix}surveys_result AS R ON S.ID=R.survey_ID WHERE R.ID=$result_id");

print "<div class='wrap'><h2>" . t('Response to ') . stripslashes($survey->name) . "</h2>";
print "<h4>";
if($survey->rname) print t('By ') . stripslashes($survey->rname);
if($survey->email) print "(". stripslashes($survey->email) . ")";
print t(' On ');
print date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($survey->added_on)) . "</h4>";

$questions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}surveys_question WHERE survey_ID={$survey->ID}");

foreach($questions as $q) {
	print $q->question . '<br />';
	$all_answers_for_question = $wpdb->get_results($wpdb->prepare("SELECT A.answer,RA.answer_ID,RA.user_answer 
			FROM {$wpdb->prefix}surveys_result_answer AS RA 
			LEFT JOIN {$wpdb->prefix}surveys_answer AS A 
			ON A.ID=RA.answer_ID WHERE RA.result_ID=%d AND RA.question_ID=%d", $result_id, $q->ID));
	
	$answers = array();
	foreach($all_answers_for_question as $one_answer) { // There is a chance that there is multiple answers for this question.
		if($one_answer->answer_ID) $answers[] = stripslashes($one_answer->answer);
		else $answers[] = stripslashes($one_answer->user_answer); //Custom User answer.
	}
	
	print t("Answer: ") . "<strong>";
	if($q->allow_user_answer and $q->user_answer_format == 'checkbox') {
		if($answers[0]) print 'Yes';
		else print 'No';
	} else {
		print implode('</strong>, <strong>', $answers);
	}
	print "</strong>\n<br /><hr />";
}
?>

<ul>
<li><a href='edit.php?page=surveys/individual_responses.php&amp;survey=<?php echo $survey->ID?>'>Individual Responses</a></li>
<li><a href='edit.php?page=surveys/responses.php&amp;survey=<?php echo $survey->ID?>'>All Responses</a></li>
<li><a href='edit.php?page=surveys/survey.php'>All Surveys</a></li>
</ul>
</div>