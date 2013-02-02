<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

$survey_details = array();
if($action == 'edit') {
	$survey_details = $wpdb->get_row("SELECT name,description,status FROM {$wpdb->prefix}surveys_survey WHERE ID=$_REQUEST[survey]");
}
?>

<div class="wrap">
<h2><?php if($action=='new') e("New Survey");
else e('Edit Survey'); ?></h2>
<input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />

<?php wpframe_add_editor_js(); ?>

<form name="post" action="<?php echo $wpframe_plugin_folder?>/survey_action.php" method="post" id="post">
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<div class="postbox">
<h3 class="hndle"><span><?php e('Survey Name') ?></span></h3>
<div class="inside">
<input type='text' name='name' value='<?php echo $survey_details->name; ?>' />
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Description') ?></span></h3>
<div class="inside">
<textarea name='description' rows='5' cols='50' style='width:100%'><?php echo $survey_details->description?></textarea>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Status') ?></span></h3>
<div class="inside">
<label for="status"><?php e('Active') ?></label> <input type="checkbox" name="status" value="1" id="status" <?php if($survey_details->status or $action=='new') print " checked='checked'"; ?> />
</div></div>

</div>

<p class="submit">
<?php wp_nonce_field('surveys_create_edit_survey'); ?>
<input type="hidden" name="action" value="<?php echo $action; ?>" />
<input type="hidden" name="survey" value="<?php echo $_REQUEST['survey']; ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" tabindex="4" />
</p>

</div>
</form>

</div>
