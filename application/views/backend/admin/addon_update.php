<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-webpack title_icon"></i> <?php echo get_phrase('addon_update'); ?>
                  <a href="<?php echo site_url('admin/addon'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><?php echo get_phrase('ge_to_addon_list'); ?></a>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row justify-content-center">
    <div class="col-xl-5">
        <?php if (!class_exists('ZipArchive')): ?>
            <div class="alert alert-danger" role="alert">
                <strong>N.B - </strong> <?php echo get_phrase('you_need_to_enable_the_zip_extension_on_your_server_to_update_addons'); ?>.
            </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?php echo get_phrase('upload_addon_file').' ('.get_phrase('zip_file').') ';?></h4>
                    <form action="<?php echo site_url('admin/addon/version_update'); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label><?php echo get_phrase('zip_file'); ?></label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="addon_zip" name="addon_zip" required onchange="changeTitleOfImageUploader(this)" required accept=".zip">
                                    <label class="custom-file-label" for="addon_zip"><?php echo get_phrase('upload_addon_file'); ?></label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary float-right"><?php echo get_phrase('update_addon'); ?></button>
                        <a href="<?php echo site_url('admin/addon'); ?>" class="btn btn-secondary float-left mdi mdi-arrow-left"><?php echo get_phrase('back'); ?></a>
                    </form>
                </div>
            </div> <!-- end card body-->
        </div>
    </div>
</div>
