<?php
    global $product;
    $UrlImg = get_the_post_thumbnail_url($post->ID, '410x520');
    $categories = get_the_terms( $post->ID, 'product_cat' );
    $class = $args;

?>
<div class="col-sm-4">
    <a href="<?php echo get_the_permalink(); ?>" class="product <?php echo $args; ?>">
        <div class="image-product">
            <img src="<?php echo $UrlImg; ?>" alt="<?php echo get_the_title(); ?>">
        </div>
        <div class="info-product">
            <h3 class="title-product">
                <?php echo get_the_title(); ?>
            </h3>
            <h4 class="category">
                <?php foreach ($categories as $category) { ?>
                    <span><?php echo $category->name; ?></span>
                <?php } ?>
            </h4>
            <?php echo get_product_variant_price(); ?>
        </div>
    </a>
</div>
