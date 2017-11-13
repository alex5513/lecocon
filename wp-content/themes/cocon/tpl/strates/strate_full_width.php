<?php
    $title = get_sub_field('text');
    $link = get_sub_field('link');
    $imageID = get_sub_field('image');
    $imageURL = wp_get_attachment_image_src($imageID, '1600x690');
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="push push-big">
                <div class="image-push" style="background-image: url('<?php echo $imageURL[0]; ?>');"></div>
                <?php if($title): ?>
                <a href="<?php echo $link; ?>" class="info-push">
                    <h2 class="title-push">
                        <?php echo $title; ?>
                    </h2>
                    <div class="link-before">
                        <div class="text">
                            En savoir plus
                        </div>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
