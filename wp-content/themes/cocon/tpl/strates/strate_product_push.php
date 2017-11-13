<?php
    $productID_one = get_sub_field('product_one');
    $productID_two = get_sub_field('product_two');
    $imageID = get_sub_field('image');
    $imgUrl = wp_get_attachment_image_src($imageID, "410x520");
    $text = get_sub_field('text');
    $link = get_sub_field('link');
    $orderView = get_sub_field('view_order');
?>

<div class="container container-product-home">
    <div class="row">
        <?php if( $productID_one ):
        	$post = $productID_one;
        	setup_postdata( $post );?>
                <?php echo clrz_get_template_part('', 'product'); ?>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>

        <?php if($productID_two):
        	$post = $productID_two;
        	setup_postdata( $post );?>
                <?php echo clrz_get_template_part('', 'product', 'push-to-top'); ?>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
        <div class="col-sm-4">
            <div class="push push-product">
                <img src="<?php echo $imgUrl[0]; ?>" alt="<?php echo $text; ?>">

                <a href="<?php echo $link; ?>" class="info-push">
                    <h2 class="title-push">
                        <?php echo $text; ?>
                    </h2>
                    <div class="link-before">
                        <div class="text">
                            En savoir plus
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
