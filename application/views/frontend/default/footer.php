<footer class="footer-area d-print-none mt-5 pt-5">

  <div class="container-xl py-4">

    <div class="row mb-3">

      <div class="col-6 col-sm-6 col-xl-3 px-4">

        <h6 class="text-white fw-700 mb-4">

          <span class="header-underline"><?php echo site_phrase('top_categories'); ?></span>

        </h6>

        <ul class="list-unstyled text-small">

          <?php $top_10_categories = $this->crud_model->get_top_categories(6, 'sub_category_id'); ?>

          <?php foreach($top_10_categories as $top_10_category): ?>

            <?php $category_details = $this->crud_model->get_category_details_by_id($top_10_category['sub_category_id'])->row_array(); ?>

            <li class="mb-2 mt-1">

              <a class="link-secondary footer-hover-link" href="<?php echo site_url('home/courses?category='.$category_details['slug']); ?>">

                <?php echo $category_details['name']; ?>

                <!-- <span class="fw-700 text-end">(<?php //echo $top_10_category['course_number']; ?>)</span> -->

              </a>

            </li>

          <?php endforeach; ?>

        </ul>

      </div>

      <div class="col-6 col-sm-6 col-xl-2 px-4">

        <h6 class="text-white fw-700 mb-4">

          <span class="header-underline"><?php echo site_phrase('useful_links'); ?></span>

        </h6>

        <ul class="list-unstyled text-small">

          <li class="mb-2 mt-1"><a class="link-secondary footer-hover-link" href="<?php echo site_url('blog'); ?>"><?php echo site_phrase('blog'); ?></a></li>

          <li class="mb-2 mt-1"><a class="link-secondary footer-hover-link" href="<?php echo site_url('home/courses'); ?>"><?php echo site_phrase('all_courses'); ?></a></li>

          <li class="mb-2 mt-1"><a class="link-secondary footer-hover-link" href="<?php echo site_url('sign_up'); ?>"><?php echo site_phrase('sign_up'); ?></a></li>



          <?php $custom_page_menus = $this->crud_model->get_custom_pages('', 'footer'); ?>

          <?php foreach($custom_page_menus->result_array() as $custom_page_menu): ?>

            <li class="mb-2"><a class="link-secondary footer-hover-link" href="<?php echo site_url('page/'.$custom_page_menu['page_url']); ?>"><?php echo $custom_page_menu['button_title']; ?></a></li>

          <?php endforeach; ?>

        </ul>

      </div>

      <div class="col-6 col-sm-6 col-xl-2 px-4">

        <h6 class="text-white fw-700 mb-4">

          <span class="header-underline">Menu kix</span>

        </h6>

        <ul class="list-unstyled text-small">

          <li class="mb-2 mt-1"><a class="link-secondary footer-hover-link" href="../infos.php">kix Info</a></li>

          <li class="mb-2 mt-1"><a class="link-secondary footer-hover-link" href="../agora/">Agora</a></li>

          <li class="mb-2 mt-1"><a class="link-secondary footer-hover-link" href="../collect.php">Collect</a></li>

          <li class="mb-2 mt-1"><a class="link-secondary footer-hover-link" href="../carto/">Carto</a></li>

        </ul>

      </div>

      <div class="col-12 col-xl-5 order-md-first pe-4 pe-xl-5 ps-4 ps-xl-0">

        <div class="footer-summary">

          <h5 class="fw-600 mb-3"><?php echo get_settings('website_description'); ?></h5>



          <ul class="footer-social-link">

            <?php $facebook = get_frontend_settings('facebook'); ?>

            <?php $twitter = get_frontend_settings('twitter'); ?>

            <?php $linkedin = get_frontend_settings('linkedin'); ?>

            <?php if($facebook): ?>

              <li class="mb-1 mt-2">

                <a href="<?php echo $facebook; ?>"><i class="fab fa-facebook-f"></i></a>

              </li>

            <?php endif; ?>

            <?php if($twitter): ?>

              <li class="mb-1 mt-2">

                <a href="<?php echo $twitter; ?>"><i class="fab fa-twitter"></i></a>

              </li>

            <?php endif; ?>

            <?php if($linkedin): ?>

              <li class="mb-1 mt-2">

                <a href="<?php echo $linkedin; ?>"><i class="fab fa-linkedin"></i></a>

              </li>

            <?php endif; ?>

          </ul>

        </div>

      </div>

    </div>

  </div>

  <section class="border-top-sub-blue pt-3 pb-2">

    <div class="container-xl">

      <div class="row mt-3 py-1">

        <div class="col-md-4 ps-4 ps-md-1">

          <img src="<?php echo base_url('uploads/system/'.get_frontend_settings('light_logo')); ?>" width="160">

        </div>

        <div class="col-md-4 ps-4 text-13px pt-2 text-start text-md-center">

          &copy; 2024, <?php echo get_settings('system_name'); ?>, <?php echo site_phrase('all_rights_reserved'); ?>

          <br>

          <?php if(get_settings('footer_text')): ?>

            <a class="link-secondary footer-hover-link" href="<?php echo get_settings('footer_link'); ?>"><?php echo site_phrase(get_settings('footer_text')); ?></a>

          <?php endif; ?>

        </div>

        <div class="col-md-4 ps-4 text-start text-md-end mt-4 mt-md-0">

          <select class="language_selector" onchange="switch_language(this.value)">

            <?php

             $languages = $this->crud_model->get_all_languages();

             foreach ($languages as $language): ?>

                <?php if (trim($language) != ""): ?>

                    <option value="<?php echo strtolower($language); ?>" <?php if ($this->session->userdata('language') == $language): ?>selected<?php endif; ?>><?php echo ucwords($language);?></option>

                <?php endif; ?>

            <?php endforeach; ?>

          </select>

        </div>

      </div>

    </div>

  </section>

</footer>



<script type="text/javascript">

    function switch_language(language) {

        $.ajax({

            url: '<?php echo site_url('home/site_language'); ?>',

            type: 'post',

            data: {language : language},

            success: function(response) {

                setTimeout(function(){ location.reload(); }, 500);

            }

        });

    }

</script>







<!-- PAYMENT MODAL -->

<!-- Modal -->

<?php

$paypal_info = json_decode(get_settings('paypal'), true);

$stripe_info = json_decode(get_settings('stripe_keys'), true);

if ($paypal_info[0]['active'] == 0) {

  $paypal_status = 'disabled';

}else {

  $paypal_status = '';

}

if ($stripe_info[0]['active'] == 0) {

  $stripe_status = 'disabled';

}else {

  $stripe_status = '';

}

?>



<!-- Modal -->

<div class="modal fade multi-step" id="EditRatingModal" tabindex="-1" role="dialog" aria-hidden="true" reset-on-close="true">

  <div class="modal-dialog modal-lg" role="document">

    <div class="modal-content edit-rating-modal">

      <div class="modal-header">

        <h5 class="modal-title step-1" data-step="1"><?php echo site_phrase('step').' 1'; ?></h5>

        <h5 class="modal-title step-2" data-step="2"><?php echo site_phrase('step').' 2'; ?></h5>

        <h5 class="m-progress-stats modal-title">

          &nbsp;of&nbsp;<span class="m-progress-total"></span>

        </h5>



        <button type="button" class="close" data-bs-dismiss="modal">

          <span aria-hidden="true">&times;</span>

        </button>

      </div>

      <div class="m-progress-bar-wrapper">

        <div class="m-progress-bar">

        </div>

      </div>

      <div class="modal-body step step-1">

        <div class="container-fluid">

          <div class="row">

            <div class="col-md-6">

              <div class="modal-rating-box">

                <h4 class="rating-title"><?php echo site_phrase('how_would_you_rate_this_course_overall'); ?>?</h4>

                <fieldset class="your-rating">



                  <input type="radio" id="star5" name="rating" value="5" />

                  <label class = "full" for="star5"></label>



                  <!-- <input type="radio" id="star4half" name="rating" value="4 and a half" />

                  <label class="half" for="star4half"></label> -->



                  <input type="radio" id="star4" name="rating" value="4" />

                  <label class = "full" for="star4"></label>



                  <!-- <input type="radio" id="star3half" name="rating" value="3 and a half" />

                  <label class="half" for="star3half"></label> -->



                  <input type="radio" id="star3" name="rating" value="3" />

                  <label class = "full" for="star3"></label>



                  <!-- <input type="radio" id="star2half" name="rating" value="2 and a half" />

                  <label class="half" for="star2half"></label> -->



                  <input type="radio" id="star2" name="rating" value="2" />

                  <label class = "full" for="star2"></label>



                  <!-- <input type="radio" id="star1half" name="rating" value="1 and a half" />

                  <label class="half" for="star1half"></label> -->



                  <input type="radio" id="star1" name="rating" value="1" />

                  <label class = "full" for="star1"></label>



                  <!-- <input type="radio" id="starhalf" name="rating" value="half" />

                  <label class="half" for="starhalf"></label> -->



                </fieldset>

              </div>

            </div>

            <div class="col-md-6">

              <div class="modal-course-preview-box">

                <div class="card">

                  <img class="card-img-top img-fluid" id = "course_thumbnail_1" alt="">

                  <div class="card-body">

                    <h5 class="card-title" class = "course_title_for_rating" id = "course_title_1"></h5>

                    <p class="card-text" id = "instructor_details">



                    </p>

                  </div>

                </div>

              </div>

            </div>

          </div>

        </div>



      </div>

      <div class="modal-body step step-2">

        <div class="container-fluid">

          <div class="row">

            <div class="col-md-6">

              <div class="modal-rating-comment-box">

                <h4 class="rating-title"><?php echo site_phrase('write_a_public_review'); ?></h4>

                <textarea id = "review_of_a_course" name = "review_of_a_course" placeholder="<?php echo site_phrase('describe_your_experience_what_you_got_out_of_the_course_and_other_helpful_highlights').'. '.site_phrase('what_did_the_instructor_do_well_and_what_could_use_some_improvement') ?>?" maxlength="65000" class="form-control"></textarea>

              </div>

            </div>

            <div class="col-md-6">

              <div class="modal-course-preview-box">

                <div class="card">

                  <img class="card-img-top img-fluid" id = "course_thumbnail_2" alt="">

                  <div class="card-body">

                    <h5 class="card-title" class = "course_title_for_rating" id = "course_title_2"></h5>

                    <p class="card-text">

                      -

                      <?php

                      $admin_details = $this->user_model->get_admin_details()->row_array();

                      echo $admin_details['first_name'].' '.$admin_details['last_name'];

                      ?>

                    </p>

                  </div>

                </div>

              </div>

            </div>

          </div>

        </div>

      </div>

      <input type="hidden" name="course_id" id = "course_id_for_rating" value="">

      <div class="modal-footer">

        <button type="button" class="btn btn-primary next step step-1" data-step="1" onclick="sendEvent(2)"><?php echo site_phrase('next'); ?></button>

        <button type="button" class="btn btn-primary previous step step-2 mr-auto" data-step="2" onclick="sendEvent(1)"><?php echo site_phrase('previous'); ?></button>

        <button type="button" class="btn btn-primary publish step step-2" onclick="publishRating($('#course_id_for_rating').val())" id = ""><?php echo site_phrase('publish'); ?></button>

      </div>

    </div>

  </div>

</div><!-- Modal -->





