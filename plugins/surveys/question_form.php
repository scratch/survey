<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

$question = array();
$answer = array();
if($action == 'edit') {
	$answer		= $wpdb->get_results($wpdb->prepare("SELECT ID, answer FROM {$wpdb->prefix}surveys_answer WHERE question_ID=%d ORDER BY sort_order", $_REQUEST['question']));
	$question	= $wpdb->get_row($wpdb->prepare("SELECT question,allow_user_answer,allow_multiple_answers,user_answer_format FROM {$wpdb->prefix}surveys_question WHERE ID=%d", $_REQUEST['question']));
}

$anscount = 4;
if($action == 'edit' and $anscount < count($answer)) $anscount = count($answer) ;

?>

<div class="wrap">
<h2><?php if($action == 'new') e("New Question");
else e('Edit Question'); ?></h2>
<input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />

<?php
wpframe_add_editor_js();
?>
<script type="text/javascript">
var answer_count = <?php echo $anscount; ?>;

function newAnswer() {
	answer_count++;
	var para = document.createElement("p");
	var textarea = document.createElement("textarea");
	textarea.setAttribute("name", "answer[]");
	textarea.setAttribute("rows", "3");
	textarea.setAttribute("cols", "50");
	para.appendChild(textarea);
	
	document.getElementById("extra-answers").appendChild(para);
}
function init() {
	jQuery("#allow_user_answer").change(function() {
		if(jQuery("#allow_user_answer").is(":checked")) {
			jQuery("#user_answer_format_area").show('slow');
		} else {
			jQuery("#user_answer_format_area").hide('slow');
		}
	});
	jQuery("#post").submit(function(e) {
		// Make sure question is suplied
		var contents;
		if(window.tinyMCE && document.getElementById("content").style.display=="none") { // If visual mode is activated.
			contents = tinyMCE.get("content").getContent();
		} else {
			contents = document.getElementById("content").value;
		}
		
		if(!contents) {
			alert("Please enter the question");
			e.preventDefault();
			e.stopPropagation();
		}
		
		var answer_count = 0
		jQuery(".answer").each(function() {
			if(this.value) answer_count++;
		});
		if(document.getElementById("allow_user_answer").checked == false && answer_count < 2) {
			alert("Please enter atleast two answers");
			e.preventDefault();
			e.stopPropagation();
		}
	});
}
jQuery(document).ready(init);
</script>

<form name="post" action="edit.php?page=surveys/question.php" method="post" id="post">
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<div class="postbox">
<h3 class="hndle"><span><?php e('Question') ?></span></h3>
<div class="inside">
<?php the_editor($question->question); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Answers') ?></span></h3>
<div class="inside">

<?php
for($i=1; $i<=$anscount; $i++) { ?>
<p><textarea name="answer[]" class="answer" rows="3" cols="50"><?php if($action == 'edit') echo $answer[$i-1]->answer; ?></textarea></p>
<input type="hidden" name="answer_id[]" value="<?php echo $answer[$i-1]->ID ?>" />
<?php } ?>

<div id="extra-answers"></div>

<a href="javascript:newAnswer();"><?php e("Add New Answer"); ?></a><br /><br />

<label for="allow_user_answer"><?php e('Allow User Inputed Answers') ?></label>
<input type="checkbox" id="allow_user_answer" name="allow_user_answer" value="1" <?php if($question->allow_user_answer) echo 'checked=checked' ?> /><br />
<div id="user_answer_format_area" <?php if(!$question->allow_user_answer) echo 'style="display:none;"'; ?>>
<label for="user_answer_format"><?php e('User Answer Format') ?></label>
<select id="user_answer_format" name="user_answer_format">
<option value="entry" <?php if($question->user_answer_format == 'entry') echo 'selected="selected"'; ?>><?php e('Text Entry') ?></option>
<option value="textarea" <?php if($question->user_answer_format == 'textarea') echo 'selected="selected"'; ?>><?php e('Textarea') ?></option>
<option value="checkbox" <?php if($question->user_answer_format == 'checkbox') echo 'selected="selected"'; ?>><?php e('CheckBox') ?></option>
</select>
</div>


<label for="allow_multiple_answers"><?php e('Allow Multiple Answers') ?></label>
<input type="checkbox" id="allow_multiple_answers" name="allow_multiple_answers" value="1" <?php if($question->allow_multiple_answers) echo 'checked=checked' ?> />
</div>
</div></div>

<p class="submit">
<input type="hidden" name="survey" value="<?php echo $_REQUEST['survey']; ?>" />
<input type="hidden" name="question" value="<?php echo stripslashes($_REQUEST['question']); ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="action" value="<?php echo $action ?>" /> 
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" tabindex="4" />
</p>
<a href="edit.php?page=surveys/question.php&amp;survey=<?php echo $_REQUEST['survey'] ?>"><?php e("Go to Questions Page") ?></a>
</div>
</form>

</div>
