<?php
include('../../../wp-blog-header.php');
auth_redirect();
if($wp_version >= '2.6.5') check_admin_referer('surveys_create_edit_survey');
include('wpframe.php');

// I could have put this in the survey_form.php - but the redirect will not work.
if(isset($_REQUEST['submit'])) {
	if($_REQUEST['action'] == 'edit') { //Update goes here
		$wpdb->get_results("UPDATE {$wpdb->prefix}surveys_survey SET name='$_REQUEST[name]',description='$_REQUEST[description]',status='$_REQUEST[status]' WHERE '$_REQUEST[survey]'=ID");
		
		wp_redirect($wpframe_wordpress . '/wp-admin/edit.php?page=surveys/survey.php&message=updated');
	
	} else {
		$wpdb->get_results("INSERT INTO {$wpdb->prefix}surveys_survey(name,description,status,added_on) VALUES('$_REQUEST[name]','$_REQUEST[description]','$_REQUEST[status]',NOW())");
		$survey_id = $wpdb->insert_id;
		wp_redirect($wpframe_wordpress . '/wp-admin/edit.php?page=surveys/question.php&message=new_survey&survey='.$survey_id);
	}
}
exit;
