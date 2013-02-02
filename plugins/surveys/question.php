<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

if(isset($_REQUEST['submit'])) {
	if($action == 'edit') { //update goes here
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}surveys_question SET question='%s',allow_user_answer=%d,allow_multiple_answers=%d,user_answer_format=%s, survey_ID=%d WHERE ID=%d", 
										stripslashes($_REQUEST['content']), $_REQUEST['allow_user_answer'], $_REQUEST['allow_multiple_answers'], $_REQUEST['user_answer_format'], 
										$_REQUEST['survey'], $_REQUEST['question']));
		
		$question_id = $_REQUEST['question'];
	
		$index = 0;
		foreach ( $_REQUEST['answer'] as $answer_text) {
			$answer_text = trim($_REQUEST['answer'][$index]);
			$answer_id = $_REQUEST['answer_id'][$index];
			if($answer_text !== '' and $answer_id) { //Update existing question.
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}surveys_answer SET answer='%s' WHERE ID=%d", stripslashes($answer_text), $answer_id));
				
			} elseif($answer_text == '' and $answer_id) { // Someone deleted an answer
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}surveys_answer WHERE ID=%d", $answer_id));
				
			} elseif($answer_text !== '') { // An answer without an Answers ID - that mean user clicked on the 'Add Answers' button.
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}surveys_answer(question_ID,answer,sort_order) VALUES(%d,'%s',%d)", $question_id,stripslashes($answer_text), $index+1));
			}
			$index++;
		}
		
		print '<div id="message" class="updated fade"><p>' . t('Question updated.') . '</p></div>';
		
	} else {
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}surveys_question(survey_ID,question,allow_user_answer, allow_multiple_answers, user_answer_format) VALUES(%d,'%s',%d, %d, %s)", 
										$_REQUEST['survey'], stripslashes($_REQUEST['content']), $_REQUEST['allow_user_answer'], $_REQUEST['allow_multiple_answers'], $_REQUEST['user_answer_format']));//Inserting the questions;
		print '<div id="message" class="updated fade"><p>'.t('Question added.') . '</p></div>';
		$question_id = $_REQUEST['question'] = $wpdb->insert_id;
	
		//Now, insert the answers.
		$counter = 1;
		foreach ( $_REQUEST['answer'] as $answer_text) {
			if($answer_text !== '') {
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}surveys_answer(question_ID,answer,sort_order) VALUES(%d,'%s','%d')", $question_id, stripslashes($answer_text), $counter));
				$counter++;
			}
		}
		
		$action='edit';
		
	}
	
}


if($_REQUEST['message'] == 'new_survey') {
	print '<div id="message" class="updated fade"><p>' . t('New survey added') . '.</p></div>';
}

if($_REQUEST['action']=='delete') {
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}surveys_result_answer WHERE question_ID='$_REQUEST[question]'");
	$wpdb->query("DELETE FROM {$wpdb->prefix}surveys_answer WHERE question_ID='$_REQUEST[question]'");
	$wpdb->query("DELETE FROM {$wpdb->prefix}surveys_question WHERE ID='$_REQUEST[question]'");
	
	print '<div id="message" class="updated fade"><p>'. t('Question deleted.') . '</p></div>';
}

$survey_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}surveys_survey WHERE ID=%d", $_REQUEST['survey']));
?>

<div class="wrap">
<h2><?php echo t("Manage Questions in ") . " '$survey_name'"; ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

<p><?php e('To add this survey to your blog, insert the code %s into any post.', '[SURVEYS '.$_REQUEST['survey'].']') ?></p>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;">#</div></th>
		<th scope="col"><?php e('Question') ?></th>
		<th scope="col"><?php e('Number Of Answers') ?></th>
		<th scope="col" colspan="3"><?php e('Action') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
// Retrieve the quetions
$all_question = $wpdb->get_results("SELECT Q.ID,Q.question,(SELECT COUNT(*) FROM {$wpdb->prefix}surveys_answer WHERE question_id=Q.ID) AS answer_count
										FROM {$wpdb->prefix}surveys_question AS Q
										WHERE Q.survey_id=$_REQUEST[survey]");

if (count($all_question)) {
	$class = 'alternate';
	$question_count = 0;
	foreach($all_question as $question) {
		$question_count++;
		$class = ('alternate' == $class) ? '' : 'alternate';
		print "<tr id='question-{$question->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $question_count ?></th>
		<td><?php echo $question->question ?></td>
		<td><?php echo $question->answer_count ?></td>
		<td><a href='edit.php?page=surveys/question_form.php&amp;question=<?php echo $question->ID?>&amp;action=edit&amp;survey=<?php echo $_REQUEST['survey']?>' class='edit'><?php e('Edit'); ?></a></td>
		<td><a href='edit.php?page=surveys/question.php&amp;action=delete&amp;question=<?php echo $question->ID?>&amp;survey=<?php echo $_REQUEST['survey']?>' class='delete' onclick="return confirm('<?php e(addslashes("You are about to delete this question. This will delete the answers to this question. Press 'OK' to delete and 'Cancel' to stop."))?>');"><?php e('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr>
		<td colspan="4"><?php e('No questions found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<ul>
<li><a href="edit.php?page=surveys/question_form.php&amp;action=new&amp;survey=<?php echo $_REQUEST['survey'] ?>"><?php e("Create New Question")?></a></li>
<li><a href="edit.php?page=surveys/responses.php&amp;survey=<?php echo $_REQUEST['survey'] ?>"><?php e("Show Responses")?></a></li>
</ul>
</div>
