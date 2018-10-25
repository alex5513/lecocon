<?php get_header(); ?>
<div class="main">
  <?php if ( is_category() ): ?>
    <h1>Catégorie : <?php single_cat_title(); ?></h1>
  <?php elseif ( is_tag() ): ?>
    <h1>Mot-clef : <?php single_cat_title(); ?></h1>
  <?php elseif ( is_year() ): ?>
    <h1>Année : <?php the_time('Y'); ?></h1>
  <?php elseif ( is_month() ): ?>
    <h1>Mois : <?php the_time('F Y'); ?></h1>
  <?php elseif ( is_day() ): ?>
    <h1>Jour : <?php the_time('j F Y'); ?></h1>
  <?php endif; ?>
  <?php get_template_part( 'loop' ); ?>
  <?php previous_posts_link(); ?>
  <?php next_posts_link(); ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
