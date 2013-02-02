<?php
require('wpframe.php');
?>
<div class="wrap">
<h2><?php e("Export Data") ?></h2>

<form action="<?php echo $GLOBALS['wpframe_plugin_folder'] ?>/export.php" method="post">
<p><?php e("Select the fields you want to export...") ?></p>

<?php
showOption('survey_id', 'ID');
showOption('added_on', 'Date');
showOption('name', 'Name');
showOption('email', 'Email');
showOption('answers', 'Answers');
?>

<p class="submit">
<input type="hidden" id="survey" name="survey" value="<?php echo (int) $_REQUEST['survey'] ?>" />

<input type="submit" name="submit" value="<?php e('Export') ?>" style="font-weight: bold;" />
</p>

</form>

</div>

<?php
function showOption($option, $title) {
?>
<input type="checkbox" name="<?php echo $option ?>" value="1" id="<?php echo $option ?>" checked="checked" />
<label for="<?php echo $option?>"><?php echo($title) ?></label><br />

<?php
}