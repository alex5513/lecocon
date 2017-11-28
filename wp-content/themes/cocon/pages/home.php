<?php
    /* Template Name: Homepage */
    get_header();
?>

<?php
    if( have_rows('strates') ):
        while ( have_rows('strates') ) : the_row();
            echo clrz_get_template_part('strates', get_row_layout());
        endwhile;
    endif;
?>

<?php
    get_footer();
?>
