<aside class="site-side">
  <!-- Bouton RSS -->
  <a href="<?php bloginfo('rss2_url'); ?>">S'abonner au flux RSS</a>
  <!-- Formulaire de recherche -->
  <h4 class="section">Recherche</h4>
  <?php get_search_form(); ?>
  <!-- Archives -->
  <h4 class="section">Archives</h4>
  <ul class="list">
    <?php wp_get_archives('type=monthly'); ?>
  </ul>
  <!-- Pages -->
  <ul class="list">
    <?php wp_list_pages(); ?>
  </ul>
</aisde>