<?php
$this->db->where('message_thread.receiver', $this->session->userdata('user_id'));
$this->db->where('message.sender !=', $this->session->userdata('user_id'));
$this->db->where('message.read_status', 0);
$this->db->from('message_thread');
$this->db->join('message', 'message_thread.message_thread_code = message.message_thread_code');
$unreaded_message = $this->db->get()->num_rows();
?>



<div class="user-dashboard-sidebar box-shadow-5">
  <div class="user-box">
      <img src="<?php echo $this->user_model->get_user_image_url($this->session->userdata('user_id')); ?>" alt="" class="img-fluid">
      <div class="name">
          <div class="name mb-0"><?php echo $user_details['first_name'] . ' ' . $user_details['last_name']; ?></div>
          <small class="fw-400 text-muted text-13px"><?php echo $user_details['email']; ?></small>
      </div>
  </div>
  <div class="user-dashboard-menu">
      <ul>
          <li class="mb-3 <?php if ($page_name == 'my_courses') echo 'active'; ?>"><a href="<?php echo site_url('home/my_courses'); ?>"> <svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.9882 6.15793C17.9901 5.86439 17.9483 5.57224 17.8643 5.29131C17.7124 4.8039 17.4055 4.38121 16.9914 4.08926C16.5773 3.79731 16.0795 3.65255 15.5756 3.67759H13.5406V3.55806C13.5406 3.37876 13.5406 3.20543 13.5406 3.03808C13.5645 2.69585 13.5645 2.35232 13.5406 2.01009C13.5227 1.73682 13.4031 1.4806 13.2061 1.29314C13.009 1.10568 12.7491 1.00093 12.4788 1.00002C12.4083 0.991204 12.337 0.991204 12.2665 1.00002C11.7415 1.10162 11.2106 1.24507 10.6974 1.38253C10.3848 1.4662 10.0722 1.55585 9.75954 1.63355C9.68117 1.64296 9.60197 1.64296 9.5236 1.63355C9.41798 1.63437 9.31276 1.62029 9.21097 1.59172C8.93373 1.59769 8.66829 1.49609 8.39105 1.41241C7.90055 1.26331 7.40235 1.1416 6.89868 1.04783C6.78874 1.03924 6.6783 1.03924 6.56836 1.04783C5.63047 1.04783 5.42401 1.8009 5.38862 2.42846V3.67759H3.51284C3.17483 3.64731 2.83439 3.69259 2.51554 3.81022C2.1967 3.92786 1.90723 4.11498 1.66757 4.35838C1.42791 4.60177 1.24391 4.89551 1.12856 5.21885C1.01321 5.54219 0.969326 5.88724 1 6.22965V14.7106C1 16.3602 1.92609 17.3105 3.54823 17.3105H15.5579C15.8857 17.3291 16.2137 17.2768 16.52 17.1571C16.8263 17.0373 17.1039 16.8529 17.3343 16.616C17.5648 16.3791 17.7428 16.0952 17.8566 15.7831C17.9704 15.4711 18.0172 15.1381 17.9941 14.8062V14.3998C18.002 11.6505 18 8.90324 17.9882 6.15793ZM10.0368 5.88898C10.0368 4.9327 10.0368 3.97643 10.0368 3.02613C10.0491 2.93276 10.0848 2.84416 10.1406 2.76881C10.1963 2.69346 10.2703 2.63388 10.3553 2.5958C10.8213 2.44041 11.2991 2.30892 11.7828 2.17146L12.3727 2.00411L12.4375 2.24318C12.46 2.29818 12.472 2.35698 12.4729 2.4165V3.70747C12.4729 4.43663 12.4729 5.16579 12.4729 5.89495C12.4803 5.95306 12.4758 6.01209 12.4595 6.06833C12.4433 6.12456 12.4158 6.17678 12.3786 6.2217C12.3415 6.26661 12.2956 6.30325 12.2438 6.32931C12.192 6.35537 12.1355 6.37027 12.0777 6.37309C11.6353 6.46274 11.1929 6.56435 10.7328 6.66595L9.99549 6.82135L10.0368 5.88898ZM6.49757 2.9305V1.96825L7.17002 2.19536C7.695 2.34478 8.19049 2.48822 8.68008 2.64362C8.75402 2.67753 8.81764 2.7309 8.86432 2.79817C8.91101 2.86544 8.93905 2.94416 8.94553 3.02613C8.94553 4.01826 8.94553 5.00442 8.94553 6.01449V6.82135L8.29667 6.68388C7.79528 6.5763 7.29979 6.46872 6.8043 6.34918C6.72563 6.32716 6.65439 6.28379 6.59826 6.22376C6.54212 6.16372 6.50323 6.0893 6.48577 6.00851C6.48577 5.00442 6.48577 3.98838 6.49167 2.9305H6.49757ZM16.9264 14.9437C16.9317 15.1175 16.9011 15.2905 16.8366 15.4516C16.7721 15.6127 16.6751 15.7584 16.5519 15.8793C16.4286 16.0003 16.2818 16.0938 16.1208 16.154C15.9598 16.2142 15.7882 16.2396 15.6169 16.2287H3.58952C2.54546 16.2287 2.08536 15.7565 2.08536 14.7106V6.19977C2.06082 6.00548 2.08023 5.80808 2.14213 5.62251C2.20403 5.43695 2.30679 5.26809 2.44263 5.12873C2.57847 4.98936 2.74383 4.88314 2.92618 4.81812C3.10853 4.75309 3.3031 4.73096 3.49514 4.7534H5.41811V5.23154C5.41811 5.48256 5.41811 5.72761 5.41811 5.97265C5.41811 6.82135 5.81922 7.26362 6.72762 7.46086C7.48705 7.64227 8.23229 7.87999 8.95732 8.17209L9.13428 8.23783C9.26141 8.2933 9.39719 8.32567 9.53539 8.33346C9.63318 8.33454 9.72929 8.3076 9.81263 8.25576C10.5822 7.80832 11.4366 7.53128 12.3196 7.44293C12.6633 7.42892 12.9876 7.27782 13.2219 7.02257C13.4561 6.76731 13.5813 6.4286 13.5701 6.08023C13.5701 5.81726 13.5701 5.55428 13.5701 5.27935V4.77731H15.0094C15.2984 4.77731 15.5992 4.77731 15.8529 4.77731C16.1596 4.80475 16.4436 4.95265 16.6442 5.18939C16.8448 5.42615 16.9461 5.73298 16.9264 6.04437C16.9264 9.00883 16.9264 11.9753 16.9264 14.9437ZM12.1544 18.9302H6.93407C6.53886 18.9302 6.30292 19.1334 6.30292 19.4621C6.30431 19.5378 6.32093 19.6123 6.35175 19.6812C6.38258 19.7501 6.42696 19.8119 6.48218 19.8628C6.53741 19.9138 6.60231 19.9527 6.67291 19.9773C6.74351 20.0019 6.81833 20.0117 6.89278 20.006H12.0718C12.1854 20.0156 12.299 19.9884 12.3963 19.9283C12.5396 19.7609 12.6264 19.5515 12.644 19.3306C12.6322 19.0736 12.4434 18.9302 12.1544 18.9302Z" fill="white"/>
            <path d="M17.9882 6.15793C17.9901 5.86439 17.9483 5.57224 17.8643 5.29131C17.7124 4.8039 17.4055 4.38121 16.9914 4.08926C16.5773 3.79731 16.0795 3.65255 15.5756 3.67759H13.5406V3.55806C13.5406 3.37876 13.5406 3.20543 13.5406 3.03808C13.5645 2.69585 13.5645 2.35232 13.5406 2.01009C13.5227 1.73682 13.4031 1.4806 13.2061 1.29314C13.009 1.10568 12.7491 1.00093 12.4788 1.00002C12.4083 0.991204 12.337 0.991204 12.2665 1.00002C11.7415 1.10162 11.2106 1.24507 10.6974 1.38253C10.3848 1.4662 10.0722 1.55585 9.75954 1.63355C9.68117 1.64296 9.60197 1.64296 9.5236 1.63355C9.41798 1.63437 9.31276 1.62029 9.21097 1.59172C8.93373 1.59769 8.66829 1.49609 8.39105 1.41241C7.90055 1.26331 7.40235 1.1416 6.89868 1.04783C6.78874 1.03924 6.6783 1.03924 6.56836 1.04783C5.63047 1.04783 5.42401 1.8009 5.38862 2.42846V3.67759H3.51284C3.17483 3.64731 2.83439 3.69259 2.51554 3.81022C2.1967 3.92786 1.90723 4.11498 1.66757 4.35838C1.42791 4.60177 1.24391 4.89551 1.12856 5.21885C1.01321 5.54219 0.969326 5.88724 1 6.22965V14.7106C1 16.3602 1.92609 17.3105 3.54823 17.3105H15.5579C15.8857 17.3291 16.2137 17.2768 16.52 17.1571C16.8263 17.0373 17.1039 16.8529 17.3343 16.616C17.5648 16.3791 17.7428 16.0952 17.8566 15.7831C17.9704 15.4711 18.0172 15.1381 17.9941 14.8062V14.3998C18.002 11.6505 18 8.90324 17.9882 6.15793ZM10.0368 5.88898C10.0368 4.9327 10.0368 3.97643 10.0368 3.02613C10.0491 2.93276 10.0848 2.84416 10.1406 2.76881C10.1963 2.69346 10.2703 2.63388 10.3553 2.5958C10.8213 2.44041 11.2991 2.30892 11.7828 2.17146L12.3727 2.00411L12.4375 2.24318C12.46 2.29818 12.472 2.35698 12.4729 2.4165V3.70747C12.4729 4.43663 12.4729 5.16579 12.4729 5.89495C12.4803 5.95306 12.4758 6.01209 12.4595 6.06833C12.4433 6.12456 12.4158 6.17678 12.3786 6.2217C12.3415 6.26661 12.2956 6.30325 12.2438 6.32931C12.192 6.35537 12.1355 6.37027 12.0777 6.37309C11.6353 6.46274 11.1929 6.56435 10.7328 6.66595L9.99549 6.82135L10.0368 5.88898ZM6.49757 2.9305V1.96825L7.17002 2.19536C7.695 2.34478 8.19049 2.48822 8.68008 2.64362C8.75402 2.67753 8.81764 2.7309 8.86432 2.79817C8.91101 2.86544 8.93905 2.94416 8.94553 3.02613C8.94553 4.01826 8.94553 5.00442 8.94553 6.01449V6.82135L8.29667 6.68388C7.79528 6.5763 7.29979 6.46872 6.8043 6.34918C6.72563 6.32716 6.65439 6.28379 6.59826 6.22376C6.54212 6.16372 6.50323 6.0893 6.48577 6.00851C6.48577 5.00442 6.48577 3.98838 6.49167 2.9305H6.49757ZM16.9264 14.9437C16.9317 15.1175 16.9011 15.2905 16.8366 15.4516C16.7721 15.6127 16.6751 15.7584 16.5519 15.8793C16.4286 16.0003 16.2818 16.0938 16.1208 16.154C15.9598 16.2142 15.7882 16.2396 15.6169 16.2287H3.58952C2.54546 16.2287 2.08536 15.7565 2.08536 14.7106V6.19977C2.06082 6.00548 2.08023 5.80808 2.14213 5.62251C2.20403 5.43695 2.30679 5.26809 2.44263 5.12873C2.57847 4.98936 2.74383 4.88314 2.92618 4.81812C3.10853 4.75309 3.3031 4.73096 3.49514 4.7534H5.41811V5.23154C5.41811 5.48256 5.41811 5.72761 5.41811 5.97265C5.41811 6.82135 5.81922 7.26362 6.72762 7.46086C7.48705 7.64227 8.23229 7.87999 8.95732 8.17209L9.13428 8.23783C9.26141 8.2933 9.39719 8.32567 9.53539 8.33346C9.63318 8.33454 9.72929 8.3076 9.81263 8.25576C10.5822 7.80832 11.4366 7.53128 12.3196 7.44293C12.6633 7.42892 12.9876 7.27782 13.2219 7.02257C13.4561 6.76731 13.5813 6.4286 13.5701 6.08023C13.5701 5.81726 13.5701 5.55428 13.5701 5.27935V4.77731H15.0094C15.2984 4.77731 15.5992 4.77731 15.8529 4.77731C16.1596 4.80475 16.4436 4.95265 16.6442 5.18939C16.8448 5.42615 16.9461 5.73298 16.9264 6.04437C16.9264 9.00883 16.9264 11.9753 16.9264 14.9437ZM12.1544 18.9302H6.93407C6.53886 18.9302 6.30292 19.1334 6.30292 19.4621C6.30431 19.5378 6.32093 19.6123 6.35175 19.6812C6.38258 19.7501 6.42696 19.8119 6.48218 19.8628C6.53741 19.9138 6.60231 19.9527 6.67291 19.9773C6.74351 20.0019 6.81833 20.0117 6.89278 20.006H12.0718C12.1854 20.0156 12.299 19.9884 12.3963 19.9283C12.5396 19.7609 12.6264 19.5515 12.644 19.3306C12.6322 19.0736 12.4434 18.9302 12.1544 18.9302Z" fill="white"/>
            </svg>
            <span class="menu-text"><?php echo site_phrase('courses'); ?></span></a></li>
          <?php if (addon_status('ebook')) : ?>
            <li class="mb-3 <?php if ($page_name == 'my_ebooks' || $page_name == 'ebook_invoice') echo 'active'; ?>">
              <a href="<?php echo site_url('home/my_ebooks'); ?>"> <i class="fas fa-book"></i>
                <span class="menu-text"><?php echo site_phrase('ebooks'); ?></span></a>
            </li>
          <?php endif; ?>
          <?php if (addon_status('course_bundle')) : ?>
            <li class="mb-3 <?php if ($page_name == 'my_bundles' || $page_name == 'bundle_invoice') echo 'active'; ?>"><a href="<?php echo site_url('home/my_bundles'); ?>"> <i class="fas fa-cubes"></i> <span class="menu-text"><?php echo site_phrase('bundles'); ?></span></a></li>
          <?php endif; ?>

          <li class="mb-3 <?php if ($page_name == 'my_wishlist') echo 'active'; ?>"><a href="<?php echo site_url('home/my_wishlist'); ?>"> <i class="far fa-heart"></i> <span class="menu-text"><?php echo site_phrase('wishlists'); ?></span></a></li>

          <li class="mb-3 <?php if ($page_name == 'my_messages') echo 'active'; ?>">
            <a href="<?php echo site_url('home/my_messages'); ?>">
              <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#clip0_24_110)">
              <path d="M13.7403 -0.000265218H4.21357C3.65723 -0.0135193 3.10411 0.0928845 2.58757 0.312533C2.07102 0.532181 1.60174 0.860526 1.20801 1.27778C0.814277 1.69503 0.504248 2.19255 0.296606 2.74034C0.088965 3.28814 -0.0119882 3.87487 -0.000168665 4.46518V10.5721C-0.00424015 11.1552 0.101088 11.7333 0.309679 12.2728C0.518271 12.8123 0.825956 13.3024 1.21481 13.7145C1.60367 14.1265 2.06592 14.4524 2.57464 14.6731C3.08336 14.8937 3.62838 15.0048 4.17795 14.9997H13.7912C14.3473 15.0115 14.9 14.9039 15.416 14.6833C15.932 14.4627 16.4006 14.1337 16.7937 13.7161C17.1868 13.2985 17.4963 12.8009 17.7036 12.2532C17.9108 11.7055 18.0116 11.119 17.9998 10.5289V4.54077C18.0196 3.94062 17.9231 3.34256 17.7163 2.78356C17.5095 2.22457 17.1968 1.71655 16.7975 1.29091C16.3982 0.865268 15.9209 0.531069 15.3951 0.308981C14.8692 0.086894 14.306 -0.0183583 13.7403 -0.000265218ZM16.8955 10.5343C16.9038 10.9669 16.8296 11.3968 16.6772 11.7981C16.5249 12.1995 16.2976 12.564 16.009 12.8697C15.7204 13.1754 15.3765 13.416 14.998 13.5769C14.6195 13.7379 14.2141 13.816 13.8064 13.8064H4.20848C3.80033 13.8153 3.39472 13.7364 3.0161 13.5744C2.63749 13.4124 2.29372 13.1707 2.00553 12.864C1.71734 12.5572 1.4907 12.1916 1.33928 11.7894C1.18786 11.3871 1.11481 10.9565 1.12451 10.5235V4.45978C1.12041 4.02795 1.19771 3.59961 1.35188 3.19994C1.50605 2.80027 1.73397 2.43734 2.02225 2.13248C2.31052 1.82762 2.65334 1.58698 3.03053 1.42473C3.40772 1.26247 3.81168 1.18186 4.21866 1.18764H13.7963C14.2019 1.18317 14.6042 1.26536 14.9792 1.42931C15.3543 1.59326 15.6944 1.83562 15.9793 2.14194C16.2642 2.44827 16.4881 2.81231 16.6376 3.21237C16.7872 3.61242 16.8593 4.04032 16.8497 4.47058C16.8802 6.47922 16.8802 8.51485 16.8752 10.5343H16.8955Z" fill="#787D8D"/>
              <path d="M13.7403 -0.000265218H4.21357C3.65723 -0.0135193 3.10411 0.0928845 2.58757 0.312533C2.07102 0.532181 1.60174 0.860526 1.20801 1.27778C0.814277 1.69503 0.504248 2.19255 0.296606 2.74034C0.088965 3.28814 -0.0119882 3.87487 -0.000168665 4.46518V10.5721C-0.00424015 11.1552 0.101088 11.7333 0.309679 12.2728C0.518271 12.8123 0.825956 13.3024 1.21481 13.7145C1.60367 14.1265 2.06592 14.4524 2.57464 14.6731C3.08336 14.8937 3.62838 15.0048 4.17795 14.9997H13.7912C14.3473 15.0115 14.9 14.9039 15.416 14.6833C15.932 14.4627 16.4006 14.1337 16.7937 13.7161C17.1868 13.2985 17.4963 12.8009 17.7036 12.2532C17.9108 11.7055 18.0116 11.119 17.9998 10.5289V4.54077C18.0196 3.94062 17.9231 3.34256 17.7163 2.78356C17.5095 2.22457 17.1968 1.71655 16.7975 1.29091C16.3982 0.865268 15.9209 0.531069 15.3951 0.308981C14.8692 0.086894 14.306 -0.0183583 13.7403 -0.000265218ZM16.8955 10.5343C16.9038 10.9669 16.8296 11.3968 16.6772 11.7981C16.5249 12.1995 16.2976 12.564 16.009 12.8697C15.7204 13.1754 15.3765 13.416 14.998 13.5769C14.6195 13.7379 14.2141 13.816 13.8064 13.8064H4.20848C3.80033 13.8153 3.39472 13.7364 3.0161 13.5744C2.63749 13.4124 2.29372 13.1707 2.00553 12.864C1.71734 12.5572 1.4907 12.1916 1.33928 11.7894C1.18786 11.3871 1.11481 10.9565 1.12451 10.5235V4.45978C1.12041 4.02795 1.19771 3.59961 1.35188 3.19994C1.50605 2.80027 1.73397 2.43734 2.02225 2.13248C2.31052 1.82762 2.65334 1.58698 3.03053 1.42473C3.40772 1.26247 3.81168 1.18186 4.21866 1.18764H13.7963C14.2019 1.18317 14.6042 1.26536 14.9792 1.42931C15.3543 1.59326 15.6944 1.83562 15.9793 2.14194C16.2642 2.44827 16.4881 2.81231 16.6376 3.21237C16.7872 3.61242 16.8593 4.04032 16.8497 4.47058C16.8802 6.47922 16.8802 8.51485 16.8752 10.5343H16.8955Z" fill="#787D8D"/>
              <path d="M15.0229 5.8801L12.1527 8.21272C11.8931 8.4233 11.6438 8.63388 11.3639 8.83367C10.6789 9.33749 9.86655 9.61036 9.03308 9.6166C8.16319 9.61704 7.31604 9.32184 6.61577 8.77427C5.51654 7.88874 4.4173 6.97621 3.3486 6.07449L3.12468 5.9395C3.05703 5.89642 2.99891 5.83838 2.95428 5.76935C2.90965 5.70031 2.87956 5.62189 2.86604 5.53941C2.85252 5.45693 2.8559 5.37234 2.87594 5.29137C2.89598 5.2104 2.93222 5.13495 2.98219 5.07017C3.03001 4.99888 3.09344 4.941 3.16702 4.90149C3.2406 4.86199 3.32212 4.84205 3.40458 4.84339C3.55369 4.84675 3.69728 4.90388 3.8117 5.00537C4.87023 5.8639 5.92366 6.73323 6.97201 7.60256C7.54062 8.12062 8.25859 8.41771 9.00763 8.4449C9.73713 8.42221 10.4389 8.14301 11.0025 7.65116L12.2137 6.66844C12.8957 6.12848 13.5776 5.58853 14.2494 5.04857C14.4055 4.92773 14.5908 4.85657 14.7837 4.84339H14.8651C15.0501 4.93268 15.1945 5.0956 15.2672 5.29695C15.369 5.54533 15.1552 5.76671 15.0229 5.8801Z" fill="#787D8D"/>
              <path d="M15.0229 5.8801L12.1527 8.21272C11.8931 8.4233 11.6438 8.63388 11.3639 8.83367C10.6789 9.33749 9.86655 9.61036 9.03308 9.6166C8.16319 9.61704 7.31604 9.32184 6.61577 8.77427C5.51654 7.88874 4.4173 6.97621 3.3486 6.07449L3.12468 5.9395C3.05703 5.89642 2.99891 5.83838 2.95428 5.76935C2.90965 5.70031 2.87956 5.62189 2.86604 5.53941C2.85252 5.45693 2.8559 5.37234 2.87594 5.29137C2.89598 5.2104 2.93222 5.13495 2.98219 5.07017C3.03001 4.99888 3.09344 4.941 3.16702 4.90149C3.2406 4.86199 3.32212 4.84205 3.40458 4.84339C3.55369 4.84675 3.69728 4.90388 3.8117 5.00537C4.87023 5.8639 5.92366 6.73323 6.97201 7.60256C7.54062 8.12062 8.25859 8.41771 9.00763 8.4449C9.73713 8.42221 10.4389 8.14301 11.0025 7.65116L12.2137 6.66844C12.8957 6.12848 13.5776 5.58853 14.2494 5.04857C14.4055 4.92773 14.5908 4.85657 14.7837 4.84339H14.8651C15.0501 4.93268 15.1945 5.0956 15.2672 5.29695C15.369 5.54533 15.1552 5.76671 15.0229 5.8801Z" fill="#787D8D"/>
              </g>
              <defs>
              <clipPath id="clip0_24_110">
              <rect width="18" height="15" fill="white"/>
              </clipPath>
              </defs>
              </svg>
              <span class="menu-text"><?php echo site_phrase('messages'); ?></span>
              <?php if ($unreaded_message > 0) : ?>
                <span class="badge bg-warning float-end"><?php echo $unreaded_message; ?></span>
              <?php endif; ?>
            </a>
          </li>

          <!-- <li class="mb-3 <?php //if ($page_name == 'purchase_history' || $page_name == 'invoice') echo 'active'; ?>"><a href="<?php //echo site_url('home/purchase_history'); ?>"> <i class="fas fa-history"></i> <span class="menu-text"><?php //echo site_phrase('purchase_history'); ?></span></a></li> -->

          <?php //course_addon start
            if (addon_status('tutor_booking')) : ?>
              <li class="mb-3 <?php if( $page_name=='booked_schedule_student' ) echo 'active'; ?>"><a href="<?php echo site_url('my_bookings'); ?>"> <i class="far fa-user-history"></i> <span class="menu-text"><?php echo site_phrase('Booked Tutions '); ?></span></a></li>
          <?php endif; ?>

          <?php //course_addon start
          if (addon_status('affiliate_course')) :
            $CI    = &get_instance();
            $CI->load->model('addons/affiliate_course_model');
            $x = $CI->affiliate_course_model->is_affilator($this->session->userdata('user_id'));

            if ($x == 1) : ?>

              <li class="mb-3 <?php if ($page_name == 'affiliate_course_history') echo 'active'; ?>"><a href="<?php echo site_url('addons/affiliate_course/affiliate_course_history'); ?>"> <i class="fas fa-poll"></i> <span class="menu-text"><?php echo site_phrase('Affiliate History '); ?></span></a></li>

            <?php endif; ?>
          <?php endif;?>
          <li class="mb-3 <?php if( $page_name=='user_profile' ) echo 'active'; ?>"><a href="<?php echo site_url('home/profile/user_profile'); ?>"> <i class="fas fa-user-circle"></i> <span class="menu-text"><?php echo site_phrase('profile'); ?></span></a></li>
          <li class=" mb-3 <?php if( $page_name=='user_credentials' ) echo 'active'; ?>"><a href="<?php echo site_url('home/profile/user_credentials'); ?>"> <i class="fas fa-lock"></i> <span class="menu-text"><?php echo site_phrase('account'); ?></span></a></li>
          <li class=" mb-3 <?php if( $page_name=='update_user_photo' ) echo 'active'; ?>"><a href="<?php echo site_url('home/profile/user_photo'); ?>"> <i class="fas fa-camera-retro"></i> <span class="menu-text"><?php echo site_phrase('photo'); ?></span></a></li>
      </ul>
  </div>
</div>