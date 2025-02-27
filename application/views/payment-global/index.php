<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $page_title.' | '.get_settings('system_name'); ?></title>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="author" content="<?php echo get_settings('author') ?>" />

	<meta name="keywords" content="<?php echo get_settings('website_keywords'); ?>"/>
	<meta name="description" content="<?php echo get_settings('website_description'); ?>" />

	<link name="favicon" type="image/x-icon" href="<?php echo base_url('uploads/system/'.get_frontend_settings('favicon')); ?>" rel="shortcut icon" />
	<?php include 'includes_top.php';?>
</head>
<body>
	<?php
		if($this->session->userdata('app_url')):
			include "go_back_to_mobile_app.php";
		endif;
		
		echo '<div class="container-fluid">';
			include 'payment_gateway.php';
		echo '</div>';

		include 'includes_bottom.php';
	?>
</body>
</html>
