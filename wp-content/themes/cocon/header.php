<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/assets/stylesheets/style.css">
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>>

      <div class="container-search">
          <div class="label-search">
              Votre recherche ici
          </div>
          <?php echo do_shortcode( '[aws_search_form]' ); ?>

          <div class="close-popin"></div>
      </div>
      <div class="pre-header">
          <?php echo __('Service client :', 'cocon_lang'); ?>
          <a href="#">client@lecocon.fr</a>
      </div>
      <header class="header">
          <div class="container">
              <div class="row">
                  <div class="col-sm-5">
                      <?php
                      $cat_args = array(
                      'orderby'    => 'name',
                      'order'      => 'asc',
                      'hide_empty' => false,
                      );

                      $product_categories = get_terms( 'product_cat', $cat_args );
                      ?>
                      <?php if( !empty($product_categories) ): ?>
                      <ul class="navigation">
                          <?php foreach ($product_categories as $key => $category): ?>
                            <li><a href="<?php echo get_term_link($category) ?>"><?php echo $category->name ?></a></li>
                          <?php endforeach; ?>
                      </ul>
                      <?php endif; ?>
                  </div>
                  <div class="col-sm-2">
                      <a href="<?php echo get_home_url(); ?>" class="logo">
                          <img src="<?php echo get_template_directory_uri() ?>/assets/images/logo.png" alt="">
                      </a>
                  </div>
                  <div class="col-sm-5 text-right">
                      <ul class="navigation">
                          <li>
                              <a href="#" class="link-search">
                                  Search
                              </a>
                          </li>
                          <li>
                              <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('Mon compte',''); ?>"><?php _e('Mon compte',''); ?></a>
                          </li>
                          <li>
                               <a href="<?php echo wc_get_cart_url(); ?>">Panier <span class="number-cart">(<?php echo WC()->cart->get_cart_contents_count(); ?>)</span></a>
                          </li>
                      </ul>
                  </div>
              </div>
          </div>
      </header>
