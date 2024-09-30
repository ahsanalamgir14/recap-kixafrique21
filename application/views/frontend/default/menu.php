<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <style>
    /* Styles communs */
    .main-nav-wrap {
      position: relative;
    }

    .icon {
      margin-right: 10px;
    }

    /* Styles pour mobile */
    @media (max-width: 767.98px) {
      .menu-toggle {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 1000;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
      }

      .nav-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
        display: none;
      }

      .nav-menu {
        position: fixed;
        top: 0;
        left: -280px;
        width: 280px;
        height: 100%;
        background: #fff;
        z-index: 999;
        overflow-y: auto;
        transition: left 0.3s ease;
        display: flex;
        flex-direction: column;
      }

      .nav-menu.open {
        left: 0;
      }

      .nav-menu ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
      }

      .nav-menu li {
        border-bottom: 1px solid #eee;
      }

      .nav-menu a {
        display: flex;
        align-items: center;
        padding: 15px;
        color: #333;
        text-decoration: none;
      }

      .nav-menu .has-submenu > a:after {
        content: '\f105';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-left: auto;
      }

      .submenu {
        display: none;
        background: #f8f8f8;
      }

      .submenu a {
        padding-left: 30px;
      }

      .back-btn {
        background: #eee;
        text-align: center;
        cursor: pointer;
      }

      .mobile-auth-buttons {
        display: flex;
        justify-content: space-around;
        padding: 15px;
        border-top: 1px solid #eee;
      }

      .mobile-auth-buttons a {
        padding: 10px 20px;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
      }

      .login-btn {
        background-color: #007bff;
        font-size: 12px;
      }

      .register-btn {
        background-color: #28a745;
        font-size: 12px;
      }
    }

    /* Styles pour desktop */
    @media (min-width: 768px) {
      .menu-toggle, .nav-overlay, .back-btn, .mobile-auth-buttons {
        display: none;
      }

      .main-nav-wrap {
        display: flex;
        justify-content: center;
      }

      .nav-menu {
        position: static;
        width: auto;
        height: auto;
        background: transparent;
        overflow: visible;
        display: flex;
        justify-content: center;
        margin-left: 236px;
      }

      .nav-menu > ul {
        display: flex;
        list-style-type: none;
        padding: 0;
        margin: 0;
      }

      .nav-menu > ul > li {
        position: relative;
        margin: 0 10px;
      }

      .nav-menu a {
        display: flex;
        align-items: center;
        padding: 15px;
        color: #333;
        text-decoration: none;
      }

      .submenu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        min-width: 200px;
      }

      .nav-menu li:hover > .submenu {
        display: block;
      }

      .submenu .submenu {
        top: 0;
        left: 100%;
      }
    }
  </style>
</head>
<body>
  <button class="menu-toggle">
    <i class="fas fa-bars"></i>
  </button>

  <div class="nav-overlay"></div>

  <div class="main-nav-wrap">
    <nav class="nav-menu">
      <ul>
        <li><a href="<?php echo site_url('home/courses'); ?>"><span class="icon"><i class="fas fa-book"></i></span><?php echo site_phrase('Cours'); ?></a></li>
        <li class="has-submenu">
          <a href="#"><span class="icon"><i class="fas fa-th"></i></span><?php echo site_phrase('Categories'); ?></a>
          <ul class="submenu">
            <li class="back-btn"><a href="#"><i class="fas fa-arrow-left"></i>&nbsp; Retour</a></li>
            <?php
            $categories = $this->crud_model->get_categories()->result_array();
            foreach ($categories as $key => $category):?>
              <li class="has-submenu">
                <a href="<?php echo site_url('home/courses?category='.$category['slug']); ?>">
                  <span class="icon"><i class="<?php echo $category['font_awesome_class']; ?>"></i></span>
                  <?php echo $category['name']; ?>
                </a>
                <ul class="submenu">
                  <li class="back-btn"><a href="#"><i class="fas fa-arrow-left"></i>&nbsp; Retour</a></li>
                  <?php
                  $sub_categories = $this->crud_model->get_sub_categories($category['id']);
                  foreach ($sub_categories as $sub_category): ?>
                    <li><a href="<?php echo site_url('home/courses?category='.$sub_category['slug']); ?>"><?php echo $sub_category['name']; ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endforeach; ?>
            <li><a href="<?php echo site_url('home/courses'); ?>"><span class="icon"><i class="fas fa-list"></i></span><?php echo site_phrase('All courses'); ?></a></li>
          </ul>
        </li>
        <li><a href="<?php echo site_url('home/levels'); ?>"><span class="icon"><i class="fas fa-layer-group"></i></span><?php echo site_phrase('Niveaux'); ?></a></li>
      </ul>
      <div class="mobile-auth-buttons">
        <a href="<?php echo site_url('login'); ?>" class="login-btn">Se Connecter</a>
        <a href="<?php echo site_url('sign_up'); ?>" class="register-btn">S'inscrire</a>
      </div>
    </nav>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const menuToggle = document.querySelector('.menu-toggle');
      const navMenu = document.querySelector('.nav-menu');
      const navOverlay = document.querySelector('.nav-overlay');
      const hasSubmenuItems = document.querySelectorAll('.has-submenu > a');
      const backButtons = document.querySelectorAll('.back-btn');

      function toggleMenu() {
        navMenu.classList.toggle('open');
        navOverlay.style.display = navMenu.classList.contains('open') ? 'block' : 'none';
        menuToggle.style.display = navMenu.classList.contains('open') ? 'none' : 'block';
      }

      menuToggle.addEventListener('click', toggleMenu);

      navOverlay.addEventListener('click', toggleMenu);

      function toggleSubmenu(e) {
        if (window.innerWidth < 768) {
          e.preventDefault();
          const submenu = this.nextElementSibling;
          submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
        }
      }

      hasSubmenuItems.forEach(item => {
        item.addEventListener('click', toggleSubmenu);
      });

      backButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          this.closest('.submenu').style.display = 'none';
        });
      });

      window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
          navMenu.classList.remove('open');
          navOverlay.style.display = 'none';
          menuToggle.style.display = 'none';
          document.querySelectorAll('.submenu').forEach(submenu => {
            submenu.style.display = '';
          });
        } else {
          menuToggle.style.display = 'block';
        }
      });
    });
  </script>
</body>
</html>