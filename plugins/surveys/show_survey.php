<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

if(!is_single() and isset($GLOBALS['surveys_client_includes_loaded'])) { #If this is in the listing page - and a quiz is already shown, don't show another.
	printf(t("Please go to <a href='%s'>%s</a> to view the survey"), get_permalink(), get_the_title());
} else {

global $wpdb;

$question = $wpdb->get_results($wpdb->prepare("SELECT ID,question,allow_user_answer,allow_multiple_answers,user_answer_format FROM {$wpdb->prefix}surveys_question WHERE survey_ID=%d ORDER BY ID", $survey_id));

if(isset($_POST['action']) and $_POST['action']) { // Save the survey
	if($_POST['result_id']) { //Save the name and the email of the survey taker.
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}surveys_result SET name=%s, email=%s WHERE ID=%d", strip_tags($_POST['survey_taker_name']), strip_tags($_POST['email']), $_POST['result_id']));
		e("Thanks for taking the survey. Your details have been saved.");
		
	} else { //Save the survey details.
		//$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}surveys_result (survey_ID, added_on) VALUES(%d, DATE_ADD(NOW(), INTERVAL %f HOUR))", $survey_id, get_option('gmt_offset')));
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}surveys_result (survey_ID, added_on) VALUES(%d, NOW())", $survey_id));
		$result_id = $wpdb->insert_id;

		$question_count = 0;
		foreach($_POST['question_id'] as $question_id) {
			if(!$_POST['answer-' . $question_id]) { //User ignored the question.
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}surveys_result_answer (result_ID, answer_ID, question_ID) VALUES(%d, %d, %d)", 
					$result_id, 0, $question_id)); // Add an empty answer row.
			
			} else {
				foreach($_POST['answer-' . $question_id] as $answer_id) {
				$user_answer = '';
				
				if($answer_id == 'user-answer') { //Custom answer provided by the user.
					$answer_id = 0;
					$user_answer = $_POST['user-answer-' . $question_id]; //Get the user answer from the text input field.
				
				} elseif(!$answer_id) $answer_id = 0; //Question was ignored.
				
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}surveys_result_answer (result_ID, answer_ID, question_ID, user_answer) VALUES(%d, %d, %d, %s)", 
												$result_id, $answer_id, $question_id, strip_tags($user_answer)));
				
				if(!$question[$question_count]->allow_multiple_answers) break; // If this question don't allow multiple answers, break to the next question. This is basically a security measure. Users will have to edit the page HTML to make this necessary(very unlikely.).
				}
			}
			$question_count++;
		}
		
		$email = get_option('surveys_email');
		if($email) {
			$email_body = t("Hi,\nThere is a new result for the survey at %s...\n",get_the_title());
			
			//Code lifted from show_individual_response.php file
			$questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}surveys_question WHERE survey_ID=%d", $survey_id));

			foreach($questions as $q) {
				$email_body .= $q->question . "\n";
				$all_answers_for_question = $wpdb->get_results($wpdb->prepare("SELECT A.answer,RA.answer_ID,RA.user_answer 
						FROM {$wpdb->prefix}surveys_result_answer AS RA 
						LEFT JOIN {$wpdb->prefix}surveys_answer AS A 
						ON A.ID=RA.answer_ID WHERE RA.result_ID=%d AND RA.question_ID=%d", $result_id, $q->ID));
				
				$answers = array();
				foreach($all_answers_for_question as $one_answer) { // There is a chance that there is multiple answers for this question.
					if($one_answer->answer_ID) $answers[] = stripslashes($one_answer->answer);
					else $answers[] = stripslashes($one_answer->user_answer); //Custom User answer.
				}
				
				$email_body .= t("Answer: ");
				if($q->allow_user_answer and $q->user_answer_format == 'checkbox') {
					if($answers[0]) $email_body .= 'Yes';
					else $email_body .= 'No';
				} else {
					$email_body .= implode(', ', $answers);
				}
				$email_body .= "\n\n";
			}
			

			mail($email, t("Survey Result"), $email_body);

		}
		
		print t("Thanks for taking the survey. Your input is very valuable to us.<br />If you want, you can attach your name to your survey answers. If you want the result to be anonymous, just ignore this form.");
		?>
		<form action="" method="post" class="survey-form" style="text-align: left;">
			<label for="name"><?php e("Name") ?></label> <input type="text" name="survey_taker_name" id="name" value="" /><br />
			<label for="email"><?php e("Email") ?></label> <input type="text" name="email" id="email" value=""/><br />
			<input type="submit" name="action" id="action-button" value="<?php e("Submit Survey") ?>"  />
			<input type="hidden" name="result_id" value="<?php echo $result_id ?>" />
		</form>
	<?php
	}
} else { // Show The survey.

	if(!isset($GLOBALS['surveys_client_includes_loaded'])) {
?>
<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['wpframe_plugin_folder'] ?>/style.css" />
<script type="text/javascript" src="<?php echo $GLOBALS['wpframe_wordpress'] ?>/wp-includes/js/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['wpframe_plugin_folder'] ?>/script.js"></script>
<?php
		$GLOBALS['surveys_client_includes_loaded'] = true; // Make sure that this code is not loaded more than once.
	}

if($question) {
$questions_per_page = get_option('surveys_questions_per_page');
if(!is_numeric($questions_per_page)) $questions_per_page = 0;

?>

<div class="survey-area <?php if($questions_per_page != 1) echo 'multi-question'; ?>">
<form action="" method="post" class="survey-form" id="survey-<?php echo $survey_id?>">
<?php
$question_count = 1;

foreach ($question as $ques) {

	echo "<div class='survey-question' id='question-$question_count'>";
	echo "<b>{$ques->question}</b>\n";
	echo "<input type='hidden' name='question_id[]' value='{$ques->ID}' />\n";
	$all_answers = $wpdb->get_results("SELECT ID,answer FROM {$wpdb->prefix}surveys_answer WHERE question_id={$ques->ID} ORDER BY sort_order");
	
	$type = ($ques->allow_multiple_answers) ? 'checkbox' : 'radio'; //If this is a multi answer question, make it a checkbox. Else, it will be a radio.
	if(count($all_answers) == 0 and $ques->allow_user_answer) $type = 'hidden'; //If there are no admin specified answer, and it allows user answer, keep it as selected - user don't have to check anything.
	
	if(count($all_answers) or $ques->user_answer_format == 'textarea') echo "<br />";
	
  foreach ($all_answers as $ans) {
    echo "<input type='$type' name='answer-{$ques->ID}[]' id='answer-id-{$ans->ID}' class='answer' value='{$ans->ID}' />\n";
    echo "<label for='answer-id-{$ans->ID}'>" . stripslashes($ans->answer) . "</label><br />\n";
  }
	
	/* -- nk. A radio and a check-box appears at the end of every question.
     Not sure if this is for all types of answers or only radio, hence
     fixing only for radio currently"

  if($ques->allow_user_answer) {
   */
	if($ques->allow_user_answer  &&  $type != 'radio'  &&  $type != 'checkbox') {
		echo "<input type='$type' name='answer-{$ques->ID}[]' id='answer-id-{$ans->ID}' class='answer' value='user-answer' />\n";
		
		if($ques->user_answer_format == 'textarea')  {
			echo "<textarea name='user-answer-{$ques->ID}' rows='5' cols='30' class='user-answer'></textarea>";
    }
		elseif($ques->user_answer_format == 'checkbox')  {
       echo "<input type='checkbox' name='user-answer-{$ques->ID}' class='user-answer' value='1' />";
    }
		else
			echo "<input type='text' name='user-answer-{$ques->ID}' class='user-answer' value='' />";
	}

  echo "<br />\n";
	echo "</div>\n\n";
	$question_count++;
}

?><br />
<input type="button" id="survey-next-question" value="<?php e("Next") ?> &gt;"  /><br />

<input type="submit" name="action" id="survey-action-button" value="<?php e("Submit Survey") ?>"  />
<input type="hidden" name="survey_id" value="<?php echo $survey_id ?>" />
</form>

<script type="text/javascript">survey_questions_per_page = <?php echo $questions_per_page ?>;</script>
</div>

<?php }
}
}
?>
