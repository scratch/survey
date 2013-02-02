<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

if($_REQUEST['message'] == 'updated') {
	print '<div id="message" class="updated fade"><p>' . t('Survey Updated') .'</p></div>';
}

if($_REQUEST['action'] == 'delete') {
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}surveys_survey WHERE ID='$_REQUEST[survey]'");
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}surveys_answer WHERE question_ID=(SELECT ID FROM {$wpdb->prefix}surveys_question WHERE survey_ID='$_REQUEST[survey]')");
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}surveys_question WHERE survey_ID='$_REQUEST[survey]'");
	// Delete Results
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}surveys_result_answer WHERE result_ID=(SELECT ID FROM {$wpdb->prefix}surveys_result WHERE survey_ID='$_REQUEST[survey]')");
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}surveys_result WHERE survey_ID='$_REQUEST[survey]'");
	
	print '<div id="message" class="updated fade"><p>'. t("Survey Deleted") . '</p></div>';

} elseif($_REQUEST['action'] == 'delete_all') {
	// Delete Results
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}surveys_result_answer WHERE result_ID=(SELECT ID FROM {$wpdb->prefix}surveys_result WHERE survey_ID='$_REQUEST[survey]')");
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}surveys_result WHERE survey_ID='$_REQUEST[survey]'");
	print '<div id="message" class="updated fade"><p>'. t("Survey Reset") . '</p></div>';
	
}
?>

<div class="wrap">
<h2><?php echo t("Manage Survey"); ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><?php e('ID') ?></div></th>
		<th scope="col"><?php e('Title') ?></th>
		<th scope="col"><?php e('Questions') ?></th>
		<th scope="col"><?php e('Responses') ?></th>
		<th scope="col"><?php e('Code') ?></th>
		<th scope="col"><?php e('Created on') ?></th>
		<th scope="col" colspan="3"><?php e('Action') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
// Retrieve the surveyes
$all_survey = $wpdb->get_results("SELECT S.ID,S.name,S.added_on,(SELECT COUNT(*) FROM {$wpdb->prefix}surveys_question WHERE survey_ID=S.ID) AS question_count,
									(SELECT COUNT(*) FROM {$wpdb->prefix}surveys_result WHERE survey_ID=S.ID) as response_count
									FROM `{$wpdb->prefix}surveys_survey` AS S ");

if (count($all_survey)) {
	$class = 'alternate';
	
	foreach($all_survey as $survey) {
		$class = ('alternate' == $class) ? '' : 'alternate';

		print "<tr id='survey-{$survey->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $survey->ID ?></th>
		<td><?php echo $survey->name ?></td>
		<td><?php echo $survey->question_count ?></td>
		<td><a href="edit.php?page=surveys/responses.php&amp;survey=<?php echo $survey->ID?>"><?php echo $survey->response_count ?> <?php e('Responses') ?></a></td>
		<td>[SURVEYS <?php echo $survey->ID ?>]</td>
		<td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($survey->added_on)) ?></td>
		<td><a href='edit.php?page=surveys/question.php&amp;survey=<?php echo $survey->ID?>' class='edit'><?php e('Manage Questions')?></a></td>
		<td><a href='edit.php?page=surveys/survey_form.php&amp;survey=<?php echo $survey->ID?>&amp;action=edit' class='edit'><?php e('Edit'); ?></a></td>
		<td><a href='edit.php?page=surveys/survey.php&amp;action=delete&amp;survey=<?php echo $survey->ID?>' class='delete' onclick="return confirm('<?php echo addslashes(t("You are about to delete this survey? This will delete all the questions and answers within this survey. Press 'OK' to delete and 'Cancel' to stop."))?>');"><?php echo t('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr>
		<td colspan="4"><?php e('No Surveys found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<a href="edit.php?page=surveys/survey_form.php&amp;action=new"><?php e("Create New Survey")?></a>
</div>
