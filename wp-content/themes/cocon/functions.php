<?php

function clrz_get_template_part($group_template, $file, $args = array()){
	global $wpdb,$post;
	$retour = false;
    if(!empty($args['svg']))
	   $filename = TEMPLATEPATH.'/tpl/'.$group_template.'/'.$file.'.svg';
    else
        $filename = TEMPLATEPATH.'/tpl/'.$group_template.'/'.$file.'.php';

	$cache_file = md5($filename);
	$cache_dir = ABSPATH.'/wp-content/clrz_cache/';

	$cache_valide = true;

	// Doit-on utiliser le cache ?
	if(!isset($args['expires']) || $args['expires'] == 0){
		$cache_valide = false;
	}

	// Le cache est-il valide ?
	if($cache_valide && (!file_exists($cache_dir.$cache_file))){
		$cache_valide = false;
	}

	// Le fichier de cache est-il expiré ?
	if($cache_valide && filemtime($cache_dir.$cache_file)+$args['expires'] < time()){
		$cache_valide = false;
	}

	// On recupere le fichier demandé
	ob_start();
	if($cache_valide){
		include $cache_dir.$cache_file;
	}
	else{
		if(file_exists($filename)){
			include $filename;
		}
	}
	$retour .= ob_get_contents();
	ob_end_clean();

	if(!$cache_valide && isset($args['expires']) && $args['expires'] > 0){
		$file_create = file_put_contents($cache_dir.$cache_file,$retour);
		// Si la création de cache a échoué
		if($file_create === FALSE){
		    if(!is_dir($cache_dir)){
		        mkdir($cache_dir);
		        @chmod($cache_dir,0777);
		    }
		}
	}

	return $retour;
}


// Image size
add_image_size( '1600x690', 1600, 690, true );
add_image_size( '410x520', 410, 520, true );

// Get price

function get_product_variant_price() {
    global $product;

	if( $product->is_on_sale() ): ?>
		<div class="price-box">
			<div class="remise price-info">
				<?php echo $product->get_regular_price(); ?>€
			</div>
			<div class="price price-info">
				<?php echo $product->get_sale_price(); ?>€
			</div>
		</div>
	<?php else: ?>
		<div class="price-box">
			<div class="price price-info">
				<?php echo $product->get_regular_price(); ?>€
			</div>
		</div>
	<?php endif;
}


function wpc_show_admin_bar() {
	return false;
}
add_filter('show_admin_bar' , 'wpc_show_admin_bar');

function get_woo_reviews()
{
$count = 0;
$html_r = "";
$title="";
$args = array(
'post_type' => 'product'
);

$comments_query = new WP_Comment_Query;
$comments = $comments_query->query( $args );

foreach($comments as $comment) :
$title = ''.get_the_title( $comment->comment_post_ID ).'';
$html_r = $html_r."<div class='author'>".$comment->comment_author."</div><div class='date'>".$comment->comment_date. "</div>";
$html_r = $html_r. "<div class='commentaire'>" .$comment->comment_content."</div>";
endforeach;
return $html_r;
}
