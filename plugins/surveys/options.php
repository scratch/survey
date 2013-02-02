<?php
include('wpframe.php');

if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
	$options = array('questions_per_page', 'insert_csv_header', 'email');
	foreach($options as $opt) {
		if(isset($_POST[$opt])) update_option('surveys_' . $opt, $_POST[$opt]);
		else update_option('surveys_' . $opt, 0);
	}
	wpframe_message("Options updated");
}
?>
<div class="wrap">
<h2>Surveys Settings</h2>

<form action="" method="post">

<label for="questions"><?php e('Number of questions per page. If 0, all the questions will be shown in a single page') ?></label>
<input type="text" name="questions_per_page" value="<?php echo get_option('surveys_questions_per_page') ?>" id="questions_per_page" size="3" /><br />

<label for="surveys_email"><?php e('Send all survey results by email') ?></label>
<input type="text" name="email" value="<?php echo get_option('surveys_email') ?>" id="email" /><br />

<?php showOption('insert_csv_header', 'Insert the questions of the survey as the first line(header) in CSV export'); ?>

<p class="submit">
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save Options') ?>" style="font-weight: bold;" />
</p>

</form>

</div>

<?php
function showOption($option, $title) {
?>
<input type="checkbox" name="<?php echo $option?>" value="1" id="<?php echo $option?>" <?php if(get_option('surveys_'.$option)) print " checked='checked'"; ?> />
<label for="<?php echo $option?>"><?php e($title) ?></label><br />

<?php
}
