<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo (isset($title) ? $title : ucwords($page_title)).' | '.get_settings('system_name'); ?></title>
    
    <!-- Google Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Autres CSS inclusions -->
    <?php include 'includes_top.php'; ?>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
	
	<?php if ($page_name == 'course_page'):

		$title = $this->crud_model->get_course_by_id($course_id)->row_array()?>

		<title><?php echo $title['title'].' | '.get_settings('system_name'); ?></title>

	<?php else: ?>

		<title><?php echo ucwords($page_title).' | '.get_settings('system_name'); ?></title>

	<?php endif; ?>





	<meta charset="utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0, minimum-scale=0.86">

	<meta name="author" content="<?php echo get_settings('author') ?>" />



	<?php

	$seo_pages = array('course_page');

	if (in_array($page_name, $seo_pages)):

		$course_details = $this->crud_model->get_course_by_id($course_id)->row_array();?>

		<meta name="keywords" content="<?php echo $course_details['meta_keywords']; ?>"/>

		<meta name="description" content="<?php echo $course_details['meta_description']; ?>" />

	<?php elseif($page_name == 'blog_details'): ?>

		<meta name="keywords" content="<?php echo $blog_details['keywords']; ?>"/>

		<meta name="description" content="<?php echo ellipsis(strip_tags(htmlspecialchars_decode($blog_details['description'])), 140); ?>" />

	<?php elseif($page_name == 'blogs'): ?>

		<meta name="keywords" content="<?php echo get_settings('website_keywords'); ?>"/>

		<meta name="description" content="<?php echo get_frontend_settings('blog_page_subtitle'); ?>" />

	<?php else: ?>

		<meta name="keywords" content="<?php echo get_settings('website_keywords'); ?>"/>

		<meta name="description" content="<?php echo get_settings('website_description'); ?>" />

	<?php endif; ?>



	<!--Social sharing content-->

	<?php if($page_name == "course_page"): ?>

		<meta property="og:title" content="<?php echo $title['title']; ?>" />

		<meta property="og:image" content="<?php echo $this->crud_model->get_course_thumbnail_url($course_id); ?>">

	<?php elseif($page_name == 'blog_details'): ?>

		<meta property="og:title" content="<?php echo $blog_details['title']; ?>" />

		<?php $blog_banner = 'uploads/blog/banner/'.$blog_details['banner']; ?>

        <?php if(!file_exists($blog_banner) || !is_file($blog_banner)): ?>

            <?php $blog_banner = 'uploads/blog/banner/placeholder.png'; ?>

        <?php endif; ?>

		<meta property="og:image" content="<?php echo base_url($blog_banner); ?>">

	<?php elseif($page_name == 'blogs'): ?>

		<meta property="og:title" content="<?php echo get_frontend_settings('blog_page_title'); ?>" />

		<meta property="og:image" content="<?php echo site_url('uploads/blog/page-banner/'.get_frontend_settings('blog_page_banner')); ?>">

	<?php else: ?>

		<meta property="og:title" content="<?php echo $page_title; ?>" />

		<meta property="og:image" content="<?= base_url("uploads/system/".get_frontend_settings('banner_image')); ?>">

	<?php endif; ?>

	<meta property="og:url" content="<?php echo current_url(); ?>" />

	<meta property="og:type" content="Learning management system" />

	<!--Social sharing content-->



	<link name="favicon" type="image/x-icon" href="<?php echo base_url('uploads/system/'.get_frontend_settings('favicon')); ?>" rel="shortcut icon" />

	<?php include 'includes_top.php';?>



</head>

<body class="white-bg">
	
	<?php include("mega-menu.php"); ?>

	<?php

	if($this->session->userdata('app_url')):

		include "go_back_to_mobile_app.php";

	endif;



	if ($this->session->userdata('user_login')) {

		include 'logged_in_header.php';

	}else {

		include 'logged_out_header.php';

	}



	if(get_frontend_settings('cookie_status') == 'active'):

    	include 'eu-cookie.php';

  	endif;

  	

  	if($page_name === null){

  		include $path;

  	}else{

		include $page_name.'.php';

	}

	include 'footer.php';

	include 'includes_bottom.php';

	include 'modal.php';

	include 'common_scripts.php';

	?>

</body>

</html>

