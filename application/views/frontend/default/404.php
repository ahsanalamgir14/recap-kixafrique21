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
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>

<section class="category-header-area">

    <div class="container-lg">

        <div class="row text-center">

            <div class="col">

                <div class="page-not-found-banner">

                    <span class="page_not_found_heading text-dark">

                        404

                    </span>

                    <span class="page_not_found_message text-secondary">

                        <?php echo site_phrase('oh_snap'); ?> ! <?php echo site_phrase('this_is_not_the_web_page_you_are_looking_for') ?>

                    </span>

                    <a href="<?php echo site_url('home'); ?>" class="btn red" id=""><?php echo site_phrase('back_to_home'); ?></a>

                </div>

            </div>

        </div>

    </div>

</section>