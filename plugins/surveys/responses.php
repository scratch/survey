<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$colors = array('bf1a1d', 'bfa024', '830088', '3e4cb9', '52803b', '805b6f', '827f71', '4bc0e0', 'c12a8f', '000000');
$color_index = 0;
function nextColor() {
	global $colors, $color_index;
	
	$color_index++;
	if($color_index > count($colors)) $color_index = 0;
	
	return "#" . $colors[$color_index - 1];
}

$survey_id = $_REQUEST['survey'];
$survey_details = $wpdb->get_row($wpdb->prepare("SELECT ID, name FROM {$wpdb->prefix}surveys_survey WHERE ID=%d", $survey_id));
?>

<div class="wrap">
<h2><?php e("Responses for '%s' Survey", $survey_details->name); ?></h2>
<p><a href="edit.php?page=surveys/individual_responses.php&amp;survey=<?php echo $survey_id ?>"><?php e('Show Individual Responses') ?></a></p>

<?php
$questions = $wpdb->get_results($wpdb->prepare("SELECT ID,question FROM {$wpdb->prefix}surveys_question WHERE survey_ID=%d", $survey_id));

foreach($questions as $question) {
print stripslashes($question->question);

//Show result.
$answers = $wpdb->get_results($wpdb->prepare("SELECT A.ID, A.answer, (SELECT COUNT(*) FROM {$wpdb->prefix}surveys_result_answer WHERE answer_ID=A.ID) AS votes 
	FROM {$wpdb->prefix}surveys_answer AS A WHERE A.question_ID=%d ORDER BY A.sort_order", $question->ID));

if(count($answers)) {
?>
<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><?php e('Answer') ?></th>
		<th scope="col" width="200"><?php e('Votes') ?></th>
		<th scope="col" width="150"><?php e('Vote Count/Percentage') ?></th>
	</tr>
	</thead>
	<tbody id="the-list">
<?php
//First find the total number of votes
$total = 0;
foreach($answers as $ans) $total += $ans->votes;
$class = 'alternate';

// Show each answer with the number of votes it recived.
foreach($answers as $ans) {
	$class = ('alternate' == $class) ? '' : 'alternate';
	print "<tr class='$class'><td>";
	if(isset($user_answer) and $ans->ID == $user_answer) print "<strong>".stripslashes($ans->answer)."</strong>"; //Users answer.
	else print stripslashes($ans->answer);
	print "</td>";
	
	if($ans->votes == 0) $percent = 0;
	else $percent = intval(($ans->votes / $total) * 100);
	$color = nextColor();
	print "<td class='pollin-result-bar-holder' style='width:200px;'><div class='pollin-result-bar' style='background-color:$color; width:$percent%;'>&nbsp;</div></td>";
	print "<td>{$ans->votes} " . t('Votes')."($percent%)</td>";
	print "</tr>";
}
?></tbody>
</table>
<strong><?php e('Total Votes') ?>: <?php echo $total?></strong><br /><hr />

<?php } else { ?>
<p><?php e('User inputed answers. Use the <a href="%s">Individual Responses</a> section to see the answers to this question.', 'edit.php?page=surveys/individual_responses.php&amp;survey='.$survey_id); ?></p>
<hr />
<?php }

} ?>

<ul>
<li><a href="edit.php?page=surveys/survey.php&amp;action=delete_all&amp;survey=<?php echo $survey_id ?>" class='delete' onclick="return confirm('<?php echo addslashes(t("This will delete ALL the responses to this survey. Are you sure? Press 'OK' to delete and 'Cancel' to stop."))?>');"><?php e('Delete All Responses') ?></a></li>
<li><a href="edit.php?page=surveys/survey.php"><?php e('Manage Surveys') ?></a></li>
</ul>
</div>