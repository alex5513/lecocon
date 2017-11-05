<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/assets/stylesheets/style.css">
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>>
      <div class="pre-header">
          <?php echo __('Service client :', 'cocon_lang'); ?>
          <a href="#">client@lecocon.fr</a>
      </div>
      <header class="header">
          <div class="container">
              <div class="row">
                  <div class="col-sm-5">
                      <ul class="navigation">
                          <li>
                              <a href="#">Intérieur</a>
                          </li>
                          <li>
                              <a href="#">Autre catégorie</a>
                          </li>
                          <li>
                              <a href="#">Intérieur</a>
                          </li>
                      </ul>
                  </div>
                  <div class="col-sm-2">
                      <a href="#" class="logo">
                          <img src="<?php echo get_template_directory_uri() ?>/assets/images/logo.png" alt="">
                      </a>
                  </div>
                  <div class="col-sm-5 text-right">
                      <ul class="navigation">
                          <li>
                              <a href="#">Intérieur</a>
                          </li>
                          <li>
                              <a href="#">Panier <span class="number-cart">(0)</span></a>
                          </li>
                      </ul>
                  </div>
              </div>
          </div>
      </header>
