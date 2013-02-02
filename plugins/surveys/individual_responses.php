<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$survey_id = $_REQUEST['survey'];
$survey_details = $wpdb->get_row("SELECT ID, name FROM {$wpdb->prefix}surveys_survey WHERE ID=$survey_id");

if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'delete') {
	$wpdb->query("DELETE FROM {$wpdb->prefix}surveys_result_answer WHERE result_ID=$_REQUEST[result]");
	$wpdb->query("DELETE FROM {$wpdb->prefix}surveys_result WHERE ID=$_REQUEST[result]");
	
	print '<div id="message" class="updated fade"><p>'.t('Result Deleted.').'</p></div>';
}

?>

<div class="wrap">
<h2><?php print t("Survey '%s' Responses", stripslashes($survey_details->name)); ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;">#</div></th>
		<th scope="col"><?php e('Name') ?></th>
		<th scope="col"><?php e('Email') ?></th>
		<th scope="col"><?php e('Answered On') ?></th>
		<th scope="col" colspan="3"><?php e('Action') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
$offset = 0;
$page = 1;
$items_per_page = 10;

if(isset($_REQUEST['paged']) and $_REQUEST['paged']) {
	$page = intval($_REQUEST['paged']);
	$offset = ($page - 1) * $items_per_page;
}

// Retrieve the survey results
$results = $wpdb->get_results("SELECT ID, name,email, added_on FROM {$wpdb->prefix}surveys_result WHERE survey_ID=$survey_id 
									ORDER BY added_on DESC LIMIT $offset, $items_per_page");

if (count($results)) {
	$count = 0;
	$class = 'alternate';
	
	foreach($results as $survey) {
		$count++;
		$class = ('alternate' == $class) ? '' : 'alternate';
		print "<tr id='survey-{$survey->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $count ?></th>
		<td><?php echo stripslashes($survey->name) ?></td>
		<td><?php if($survey->email) echo "<a href='mailto:".stripslashes($survey->email)."'>".stripslashes($survey->email)."</a>"; ?></td>
		<td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($survey->added_on)) ?></td>
		<td><a href='edit.php?page=surveys/show_individual_response.php&amp;result=<?php echo $survey->ID?>&amp;survey=<?php echo $survey_id ?>&amp;action=show' class='show-result'><?php e('Show'); ?></a></td>
		<td><a href='edit.php?page=surveys/individual_responses.php&amp;action=delete&amp;result=<?php echo $survey->ID?>&amp;survey=<?php echo $survey_id ?>' class='delete' onclick="return confirm('<?php e(addslashes("You are about to delete this Result. Press 'OK' to delete and 'Cancel' to stop."))?>');"><?php e('Delete')?></a></td>
		</tr>
<?php
	}
} else {
?>
	<tr style='background-color: <?php echo $bgcolor; ?>;'>
		<td colspan="4"><?php e('No Responses found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<?php if(count($results)) { ?>
<div class="tablenav">
<?php
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}surveys_result WHERE survey_ID=$survey_id");
$total_pages = ceil($total_items / $items_per_page);
$page_links = paginate_links( array(
	'base' => add_query_arg( 'paged', '%#%' ),
	'format' => '',
	'total' => $total_pages,
	'current' => $page
));
if ( $page_links ) echo "<div class='tablenav-pages'>$page_links</div>";
?>
</div>
<?php } ?>

<ul>
<li><a href="edit.php?page=surveys/export_choose.php&amp;survey=<?php echo $_REQUEST['survey'] ?>"><?php e('Export to CSV') ?></a></li>
<li><a href='edit.php?page=surveys/responses.php&amp;survey=<?php echo $_REQUEST['survey'] ?>'><?php e('All Responses') ?></a></li>
<li><a href='edit.php?page=surveys/survey.php'><?php e('All Surveys') ?></a></li>
</ul>
</div>
