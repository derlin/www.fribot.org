<?php function NewsTickerAinkSettings() { global $options; $options = get_option('NewsTickerAink_option'); ?>
<div class="wrap">
<div class="icon32" id="icon-tools"><br/></div>
<h2><?php echo __('NewsTicker Aink'); ?></h2>

<form method="post" id="mainform" action="">
<table class="widefat fixed" style="margin:25px 0;">
	<thead>
		<tr>
			<th scope="col" width="200px">NewsTicker Aink Settings</th>
			<th scope="col">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="titledesc">NewsTicker Height:</td>
			<td class="forminp">
				<input name="NewsTickerAink_height" id="NewsTickerAink_height" style="width:100px;" value="<?php echo $options[NewsTickerAink_height]; ?>" type="text">
				<br /><small>ex: "250px" without quotes.</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Speed:</td>
			<td class="forminp">
				<input name="NewsTickerAink_speed" id="NewsTickerAink_speed" style="width:100px;" value="<?php echo $options[NewsTickerAink_speed]; ?>" type="text" class="required">
				<br /><small>ex: "1000" or "10000" without quotes.</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Content Text Align:</td>
			<td class="forminp">
				<select name="NewsTickerAink_content_align" id="NewsTickerAink_content_align" style="min-width:100px;">
					<?php if ($options[NewsTickerAink_content_align] == 'left'){ ?>
						<option value="left" selected="selected">Left</option>
						<option value="center">Center</option>
						<option value="right">Right</option>
						<option value="justify">Justify</option>
					<?php } else if ($options[NewsTickerAink_content_align] == 'center'){ ?>
						<option value="left">Left</option>
						<option value="center" selected="selected">Center</option>
						<option value="right">Right</option>
						<option value="justify">Justify</option>
					<?php } else if ($options[NewsTickerAink_content_align] == 'right'){ ?>
						<option value="left">Left</option>
						<option value="center">Center</option>
						<option value="right" selected="selected">Right</option>
						<option value="justify">Justify</option>
					<?php } else if ($options[NewsTickerAink_content_align] == 'justify'){ ?>
						<option value="left">Left</option>
						<option value="center">Center</option>
						<option value="right">Right</option>
						<option value="justify" selected="selected">Justify</option>
					<?php } else { ?>
						<option value="left" selected="selected">Left</option>
						<option value="center">Center</option>
						<option value="right">Right</option>
						<option value="justify">Justify</option>
					<?php } ?>
				</select>
				<br /><small>Text align for content of NewsTicker.</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Show Link:</td>
			<td class="forminp">
				<input name="NewsTickerAink_link" type="checkbox" <?php
				if($options[NewsTickerAink_link] == 'check') {
					echo 'checked="checked" value="check"';
				} else if($options[NewsTickerAink_link] != 'check') {
					echo 'value="check"';					
				} else {
					echo 'checked="checked" value="check"';
				}
				?> />
				<br /><small>Show NewsTicker Aink link.</small>
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="<?php get_option($options) ?>" />
<p class="submit bbot"><input name="save" type="submit" value="<?php esc_attr_e("Save Changes"); ?>" /></p>
</form>
</div>

	<div class="wrap"><hr /></div>

<div class="wrap">
<table class="widefat fixed" style="margin:25px 0;">
	<thead>
		<tr>
			<th scope="col" width="200px">Donate for NewsTicker Aink</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="forminp">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="PT2WCK3V8545C">
<p class="submit bbot"><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/en_US/i/scr/pixel.gif" width="1" height="1"></p>
</form>				
			</td>
		</tr>
	</tbody>
</table>
</div>

<?php } function CreateNewNewsTickerAink() {
global $NewsTicker, $wpdb;

// check to prevent php "notice: undefined index" msg
if(isset($_GET['action'])) $theswitch = $_GET['action']; else $theswitch ='';

switch($theswitch) { case 'addNewsTicker': ?>

<div class="wrap">
<div class="icon32" id="icon-themes"><br/></div>
<h2><?php _e('Create NewsTicker Aink','k0z3y') ?></h2>

<?php
// check and make sure the form was submitted
if(isset($_POST['submitted'])) {

	 $insert = "INSERT INTO " . $wpdb->prefix . "newsticker_aink" .
			   " (title, content, status, showfor, created) " .
			   "VALUES ('" .
					$wpdb->escape($_POST['title']) . "','" .
					$_POST['content'] . "','" .
					$wpdb->escape($_POST['status']) . "','" .
					$wpdb->escape($_POST['showfor']) . "','" .
					gmdate('Y-m-d H:i:s') .
				"')";
	$results = $wpdb->query($insert);

if ($results) : ?>

<?php global $NewsTickerAink_path; ?>
<p style="text-align:center;padding-top:50px;font-size:22px;">Creating your NewsTicker.....<br /><br /><img src="<?php echo $NewsTickerAink_path; ?>/images/loading.gif" alt="" /></p><meta http-equiv="refresh" content="0; URL=?page=NewsTickerAink_new">

<?php endif; } else { ?>

<form method="post" id="mainform" action="">
<table class="widefat fixed" style="margin:25px 0;">
	<thead>
		<tr>
			<th scope="col" width="200px">NewsTicker Aink</th>
			<th scope="col">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="titledesc">NewsTicker Title:</td>
			<td class="forminp">
				<input name="title" id="title" style="width:500px;" type="text">
				<br><small>Title for this NewsTicker.</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Content:</td>
			<td class="forminp">
				<textarea name="content" id="content" style="width:550px;height:150px;" class="required" minlength="5"></textarea>
				<br><small>HTML is allowed.</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Status:</td>
			<td class="forminp">
				<select name="status" id="status" style="min-width:100px;" class="required">
					<option value="Active">Active</option>
					<option value="Inactive">Inactive</option>
				</select>
				<br><small>If you do not want this NewsTicker live, select "Inactive".</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Show for:</td>
			<td class="forminp">
				<select name="showfor" id="showfor" style="min-width: 125px;" class="required">
					<option value="All User">All User</option>
					<option value="User Login">User Login</option>
					<option value="User Not Login">User Not Login</option>
				</select>
				<br><small>Show NewsTicker for selected user.</small>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<input class="btn button-primary" name="save" type="submit" value="<?php _e('Create New NewsTicker', 'k0z3y') ?>" />
		&nbsp;&nbsp;&nbsp;
	<input name="cancel" type="button" onClick="location.href='?page=NewsTickerAink_new'" value="<?php _e('Cancel','k0z3y')?>" />
	<input name="submitted" type="hidden" value="yes" />
</p>
</form>

<?php } ?>
</div>

<?php break; case 'editNewsTicker': ?>

<div class="wrap">
<div class="icon32" id="icon-themes"><br/></div>
<h2>Edit NewsTicker Aink</h2>

<?php if(isset($_POST['submitted']) && $_POST['submitted'] == 'yes') {

	$update = "UPDATE " . $wpdb->prefix . "newsticker_aink SET" .
			  " title = '" . $wpdb->escape($_POST['title']) . "'," .
			  " content = '" . $_POST['content'] . "'," .
			  " status = '" . $wpdb->escape($_POST['status']) . "'," .
			  " showfor = '" . $wpdb->escape($_POST['showfor']) . "'," .
			  " modified = '" . gmdate('Y-m-d H:i:s') . "'" .
			  " WHERE id ='" . $_GET['id'] ."'";
	$results = $wpdb->get_row($update);

?>
<?php global $NewsTickerAink_path; ?>
<p style="text-align:center;padding-top:50px;font-size:22px;">Saving your changes.....<br /><br /><img src="<?php echo $NewsTickerAink_path; ?>/images/loading.gif" alt="" /></p><meta http-equiv="refresh" content="0; URL=?page=NewsTickerAink_new">

<?php } else { global $wpdb;

	$AllUser = $wpdb->prepare("SELECT * "
			 . "FROM ". $wpdb->prefix . "newsticker_aink "
			 . "WHERE id='" . $_GET['id'] ."'");

?>

<form method="post" id="mainform" action="">
<table class="widefat fixed" style="margin:25px 0;">
	<thead>
		<tr>
			<th scope="col" width="200px">NewsTicker Aink</th>
			<th scope="col">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="titledesc">NewsTicker Title:</td>
			<td class="forminp">
				<input name="title" id="title" style="width:500px;" value="<?php foreach ($wpdb->get_results($AllUser) as $All) { ?><?php echo $All->title; ?><?php } ?>" type="text">
				<br><small>Title for this NewsTicker.</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Content:</td>
			<td class="forminp">
				<textarea name="content" id="content" style="width:550px;height:150px;" class="required" minlength="5"><?php foreach ($wpdb->get_results($AllUser) as $All) { ?><?php echo $All->content; ?><?php } ?></textarea>
				<br><small>HTML is allowed.</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Status:</td>
			<td class="forminp">
				<select name="status" id="status" style="min-width: 100px;" class="required">
					<?php foreach ($wpdb->get_results($AllUser) as $Status) { ?>
						<?php if ($Status->status == 'Active') { ?>
							<option value="Active" selected="selected">Active</option>
							<option value="Inactive">Inactive</option>
						<?php } else if ($Status->status == 'Inactive') { ?>
							<option value="Active">Active</option>
							<option value="Inactive" selected="selected">Inactive</option>
						<?php } ?>
					<?php } ?>
				</select>
				<br><small>If you do not want this NewsTicker live, select "Inactive".</small>
			</td>
		</tr><tr>
			<td class="titledesc">NewsTicker Show for:</td>
			<td class="forminp">
				<select name="showfor" id="showfor" style="min-width: 125px;" class="required">
					<?php foreach ($wpdb->get_results($AllUser) as $Show) { ?>
						<?php if ($Show->showfor == 'All User') { ?>
							<option value="All User" selected="selected">All User</option>
							<option value="User Login">User Login</option>
							<option value="User Not Login">User Not Login</option>
						<?php } else if ($Show->showfor == 'User Login') { ?>
							<option value="All User">All User</option>
							<option value="User Login" selected="selected">User Login</option>
							<option value="User Not Login">User Not Login</option>
						<?php } else if ($Show->showfor == 'User Not Login') { ?>
							<option value="All User">All User</option>
							<option value="User Login">User Login</option>
							<option value="User Not Login" selected="selected">User Not Login</option>
						<?php } ?>
					<?php } ?>
				</select>
				<br><small>Show NewsTicker for selected user.</small>
			</td>
		</tr>
	</tbody>
</table>

<p class="submit">
	<input class="btn button-primary" name="save" type="submit" value="<?php _e('Save changes','k0z3y') ?>" />
		&nbsp;&nbsp;&nbsp;
	<input name="cancel" type="button" onClick="location.href='?page=NewsTickerAink_new'" value="<?php _e('Cancel','k0z3y') ?>" />
	<input name="submitted" type="hidden" value="yes" />
</p>

</form>

<?php } ?>
</div>

<?php break; case 'delete':

	$delete = "DELETE FROM " . $wpdb->prefix . "newsticker_aink "
			. "WHERE id = '". $_GET['id'] ."'";
	$wpdb->query($delete);

?>
<?php global $NewsTickerAink_path; ?>
<p style="text-align:center;padding-top:50px;font-size:22px;">Deleting NewsTicker.....<br /><br /><img src="<?php echo $NewsTickerAink_path; ?>/images/loading.gif" alt="" /></p><meta http-equiv="refresh" content="0; URL=?page=NewsTickerAink_new">

<?php break; default: global $NewsTickerAink_path;

	$sql = "SELECT * "
		 . "FROM " . $wpdb->prefix . "newsticker_aink "
		 . "ORDER BY id desc";
	$results = $wpdb->get_results($sql);

?>

<style type="text/css">
a.edit {
	background:url('<?php echo $NewsTickerAink_path; ?>/images/edit-grey.png') no-repeat;
	display:block;
	width:16px;
	height:16px;
	float:left;
	margin-left:17.6px;
}
a.edit:hover {
	background:url('<?php echo $NewsTickerAink_path; ?>/images/edit.png') no-repeat;
}
a.delete {
	background:url('<?php echo $NewsTickerAink_path; ?>/images/cross-grey.png') no-repeat;
	display:block;
	width:16px;
	height:16px;
	float:left;
	margin-left:5.5px;
}
a.delete:hover {
	background:url('<?php echo $NewsTickerAink_path; ?>/images/cross.png') no-repeat;
}
</style>

<div class="wrap">
<div class="icon32" id="icon-themes"><br/></div>
<h2>NewsTicker Aink&nbsp;<a class="button add-new-h2" href="?page=NewsTickerAink_new&amp;action=addNewsTicker">Add New NewsTicker</a></h2>


<table id="tblspacer" class="widefat fixed" style="margin:25px 0;">
	<thead style="width:100%;">
		<tr style="width:100%;">
			<th scope="col" style="width:20px;">&nbsp;</th>
			<th scope="col"><?php _e('NewsTicker','k0z3y') ?></th>
			<th scope="col" style="width:120px;"><?php _e('Created','k0z3y') ?></th>
			<th scope="col" style="width:120px;"><?php _e('Modified','k0z3y') ?></th>
			<th scope="col" style="width:50px;"><?php _e('Status','k0z3y') ?></th>
			<th scope="col" style="width:100px;"><?php _e('Show for','k0z3y') ?></th>
			<th scope="col" style="text-align:center;width:70px;"><?php _e('Actions','k0z3y') ?></th>
		</tr>
	</thead>
	<?php if ($results) { $rowclass = ''; $i=1; ?>
	<tbody id="list">
	<?php foreach( $results as $result ) { $rowclass = 'even' == $rowclass ? 'alt' : 'even'; ?>
		<tr class="<?php echo $rowclass ?>">
			<td style="padding-left:10px;"><?php echo $i ?>.</td>
			<td><?php if(mb_strlen($result->content)>=250)echo mb_substr($result->content,0,250).'....';else echo $result->content ?></td>
			<td><?php echo mysql2date(get_option('date_format') .' '. get_option('time_format'), $result->created) ?></td>
			<td><?php echo mysql2date(get_option('date_format') .' '. get_option('time_format'), $result->modified) ?></td>
			<td><?php echo ucfirst($result->status) ?></td>
			<td><?php echo ucfirst($result->showfor) ?></td>
			<td style="text-align:center">
				<a href="?page=NewsTickerAink_new&amp;action=editNewsTicker&amp;id=<?php echo $result->id ?>" class="edit" title="Edit"><span></span></a>&nbsp;&nbsp;&nbsp;<a onclick="return confirmBeforeDelete();" href="?page=NewsTickerAink_new&amp;action=delete&amp;id=<?php echo $result->id ?>" class="delete" title="Delete"><span></span></a>
			</td>
		</tr>
	<?php $i++; } ?>
	</tbody>	
	<?php } else { ?>
		<tr>
			<td colspan="7">No NewsTicker found.</td>
		</tr>
	<?php } ?>

</table>
</div>

<?php } ?>

<script type="text/javascript">
	/* <![CDATA[ */
		function confirmBeforeDelete() { return confirm("Are you sure you want to delete this NewsTicker?"); }
	/* ]]> */
</script>

<?php } ?>