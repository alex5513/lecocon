<?php get_header(); ?>
<div class="site-main">
  <?php if ( have_posts() ): ?>
    <?php while ( have_posts() ): the_post(); ?>
      <div <?php post_class(); ?>>
        <h1 class="post-title"><?php the_title(); ?></h1>
        <div class="post-content">
          <?php the_content(); ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
