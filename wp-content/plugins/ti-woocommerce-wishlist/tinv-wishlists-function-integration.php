<?php
/**
 * Support functions for other plugins
 *
 * @since             1.5.0
 * @package           TInvWishlist
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! function_exists( 'tinvwl_rocket_reject_uri' ) ) {

	/**
	 * Disable cache for WP Rocket
	 *
	 * @param array $uri URI.
	 *
	 * @return array
	 */
	function tinvwl_rocket_reject_uri( $uri = array() ) {
		$ids       = array(
			tinv_get_option( 'page', 'wishlist' ),
		);
		$pages     = $ids;
		$languages = apply_filters( 'wpml_active_languages', array(), array(
			'skip_missing' => 0,
			'orderby'      => 'code',
		) );
		if ( ! empty( $languages ) ) {
			foreach ( $ids as $id ) {
				foreach ( $languages as $l ) {
					$pages[] = apply_filters( 'wpml_object_id', $id, 'page', true, $l['language_code'] );
				}
			}
			$pages = array_unique( $pages );
		}
		$pages = array_filter( $pages );
		if ( ! empty( $pages ) ) {
			foreach ( $pages as $page ) {
				$uri[] = str_replace( get_site_url(), '', get_permalink( $page ) );
			}
		}

		return $uri;
	}

	add_filter( 'rocket_cache_reject_uri', 'tinvwl_rocket_reject_uri' );
} // End if().

if ( ! function_exists( 'tinvwl_rocket_reject_cookies' ) ) {

	/**
	 * Disable cache for WP Rocket
	 *
	 * @param array $cookies Cookies.
	 *
	 * @return array
	 */
	function tinvwl_rocket_reject_cookies( $cookies = array() ) {
		$cookies[] = 'tinv_wishlist';

		return $cookies;
	}

	add_filter( 'rocket_cache_reject_cookies', 'tinvwl_rocket_reject_cookies' );
}

if ( ! function_exists( 'tinvwl_supercache_reject_uri' ) ) {

	/**
	 * Disable cache for WP Super Cache
	 *
	 * @global array $cache_rejected_uri
	 *
	 * @param string $buffer Intercepted the output of the page.
	 *
	 * @return string
	 */
	function tinvwl_supercache_reject_uri( $buffer ) {
		global $cache_rejected_uri;
		if ( ! is_null( $cache_rejected_uri ) && is_array( $cache_rejected_uri ) ) {
			$ids       = array(
				tinv_get_option( 'page', 'wishlist' ),
			);
			$pages     = $ids;
			$languages = apply_filters( 'wpml_active_languages', array(), array(
				'skip_missing' => 0,
				'orderby'      => 'code',
			) );
			if ( ! empty( $languages ) ) {
				foreach ( $ids as $id ) {
					foreach ( $languages as $l ) {
						$pages[] = apply_filters( 'wpml_object_id', $id, 'page', true, $l['language_code'] );
					}
				}
				$pages = array_unique( $pages );
			}
			$pages = array_filter( $pages );
			if ( ! empty( $pages ) ) {
				foreach ( $pages as $page ) {
					$cache_rejected_uri[] = str_replace( get_site_url(), '', get_permalink( $page ) );
				}
			}
		}

		return $buffer;
	}

	add_filter( 'wp_cache_ob_callback_filter', 'tinvwl_supercache_reject_uri' );
} // End if().

if ( ! function_exists( 'tinvwl_w3total_reject_uri' ) ) {

	/**
	 * Disable cache for W3 Total Cache
	 */
	function tinvwl_w3total_reject_uri() {
		if ( ! function_exists( 'w3tc_pgcache_flush' ) || ! function_exists( 'w3_instance' ) ) {
			return;
		}
		$ids       = array(
			tinv_get_option( 'page', 'wishlist' ),
		);
		$pages     = $ids;
		$languages = apply_filters( 'wpml_active_languages', array(), array(
			'skip_missing' => 0,
			'orderby'      => 'code',
		) );
		if ( ! empty( $languages ) ) {
			foreach ( $ids as $id ) {
				foreach ( $languages as $l ) {
					$pages[] = apply_filters( 'wpml_object_id', $id, 'page', true, $l['language_code'] );
				}
			}
			$pages = array_unique( $pages );
		}
		$pages = array_filter( $pages );
		if ( ! empty( $pages ) ) {
			foreach ( $pages as $i => $page ) {
				$pages[ $i ] = preg_replace( "/^\//", '', str_replace( get_site_url(), '', get_permalink( $page ) ) ); // @codingStandardsIgnoreLine Squiz.Strings.DoubleQuoteUsage.NotRequired
			}
		}
		$pages = array_unique( $pages );
		$pages = array_filter( $pages );

		if ( ! empty( $pages ) ) {
			$config   = w3_instance( 'W3_Config' );
			$sections = array( 'dbcache.reject.uri', 'pgcache.reject.uri' );
			foreach ( $sections as $section ) {
				$settings = array_map( 'trim', $config->get_array( $section ) );
				$changed  = false;
				foreach ( $pages as $page ) {
					if ( ! in_array( $page, $settings ) ) { // @codingStandardsIgnoreLine WordPress.PHP.StrictInArray.MissingTrueStrict
						$settings[] = $page;
						$changed    = true;
					}
				}
				if ( $changed ) {
					$config->set( $section, $settings );
					$config->save();
				}
			}
		}

		$settings = array_map( 'trim', $config->get_array( 'pgcache.reject.cookie' ) );
		if ( ! in_array( 'tinv_wishlist', $settings ) ) { // @codingStandardsIgnoreLine WordPress.PHP.StrictInArray.MissingTrueStrict
			$settings[] = 'tinv_wishlist';
			$config->set( 'pgcache.reject.cookie', $settings );
			$config->save();
		}
	}

	add_action( 'admin_init', 'tinvwl_w3total_reject_uri' );
} // End if().

if ( function_exists( 'tinvwl_comet_cache_reject' ) ) {

	/**
	 * Set define disabled for Comet Cache
	 *
	 * @param mixed $data Any content.
	 * @return mixed
	 */
	function tinvwl_comet_cache_reject( $data = '' ) {
		define( 'COMET_CACHE_ALLOWED', false );

		return $data;
	}

	add_filter( 'tinvwl_addtowishlist_return_ajax', 'tinvwl_comet_cache_reject' );
	add_action( 'tinvwl_before_action_owner', 'tinvwl_comet_cache_reject' );
	add_action( 'tinvwl_before_action_user', 'tinvwl_comet_cache_reject' );
	add_action( 'tinvwl_addproduct_tocart', 'tinvwl_comet_cache_reject' );
	add_action( 'tinv_wishlist_addtowishlist_button', 'tinvwl_comet_cache_reject' );
	add_action( 'tinv_wishlist_addtowishlist_dialogbox', 'tinvwl_comet_cache_reject' );
}

if ( ! function_exists( 'gf_productaddon_support' ) ) {

	/**
	 * Add supports WooCommerce - Gravity Forms Product Add-Ons
	 */
	function gf_productaddon_support() {
		if ( ! class_exists( 'woocommerce_gravityforms' ) ) {
			return false;
		}
		if ( ! function_exists( 'gf_productaddon_text_button' ) ) {

			/**
			 * Change text for button add to cart
			 *
			 * @param string $text_add_to_cart Text "Add to cart".
			 * @param array  $wl_product Wishlist product.
			 * @param object $product WooCommerce Product.
			 *
			 * @return string
			 */
			function gf_productaddon_text_button( $text_add_to_cart, $wl_product, $product ) {
				$gravity_form_data = get_post_meta( ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->id : ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() ) ), '_gravity_form_data', true );

				return ( $gravity_form_data ) ? __( 'Select options', 'woocommerce' ) : $text_add_to_cart;
			}

			add_filter( 'tinvwl_wishlist_item_add_to_cart', 'gf_productaddon_text_button', 10, 3 );
		}

		if ( ! function_exists( 'gf_productaddon_run_action_button' ) ) {

			/**
			 * Check for make redirect to url
			 *
			 * @param boolean $need Need redirect or not.
			 * @param object  $product WooCommerce Product.
			 *
			 * @return boolean
			 */
			function gf_productaddon_run_action_button( $need, $product ) {
				$gravity_form_data = get_post_meta( ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->id : ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() ) ), '_gravity_form_data', true );

				return ( $gravity_form_data ) ? true : $need;
			}

			add_filter( 'tinvwl_product_add_to_cart_need_redirect', 'gf_productaddon_run_action_button', 10, 2 );
		}

		if ( ! function_exists( 'gf_productaddon_action_button' ) ) {

			/**
			 * Redirect url
			 *
			 * @param string $url Redirect URL.
			 * @param object $product WooCommerce Product.
			 *
			 * @return string
			 */
			function gf_productaddon_action_button( $url, $product ) {
				$gravity_form_data = get_post_meta( ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->id : ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() ) ), '_gravity_form_data', true );

				return ( $gravity_form_data ) ? $product->get_permalink() : $url;
			}

			add_filter( 'tinvwl_product_add_to_cart_redirect_url', 'gf_productaddon_action_button', 10, 2 );
		}
	}

	add_action( 'init', 'gf_productaddon_support' );
} // End if().

if ( ! function_exists( 'tinvwl_wpml_product_get' ) ) {

	/**
	 * Change product data if product need translate
	 *
	 * @param array $product Wishlistl product.
	 *
	 * @return array
	 */
	function tinvwl_wpml_product_get( $product ) {
		if ( array_key_exists( 'data', $product ) ) {
			$_product_id   = $product_id = $product['product_id'];
			$_variation_id = $variation_id = $product['variation_id'];
			$_product_id   = apply_filters( 'wpml_object_id', $_product_id, 'product', true );
			if ( ! empty( $_variation_id ) ) {
				$_variation_id = apply_filters( 'wpml_object_id', $_variation_id, 'product', true );
			}
			if ( $_product_id !== $product_id || $_variation_id !== $variation_id ) {
				$product['data'] = wc_get_product( $variation_id ? $_variation_id : $_product_id );
			}
		}

		return $product;
	}

	add_filter( 'tinvwl_wishlist_product_get', 'tinvwl_wpml_product_get' );
}

if ( ! function_exists( 'tinvwl_wpml_addtowishlist_prepare' ) ) {

	/**
	 * Change product data if product need translate in WooCommerce Multilingual
	 *
	 * @param array $post_data Data for wishlist.
	 *
	 * @return array
	 */
	function tinvwl_wpml_addtowishlist_prepare( $post_data ) {
		if ( class_exists( 'woocommerce_wpml' ) ) {
			$woo_wpml = woocommerce_wpml::instance();
			if ( array_key_exists( 'product_id', $post_data ) && ! empty( $post_data['product_id'] ) ) {
				$post_data['product_id'] = $woo_wpml->products->get_original_product_id( $post_data['product_id'] );
			}
			if ( array_key_exists( 'product_id', $post_data ) && ! empty( $post_data['product_id'] ) && array_key_exists( 'product_variation', $post_data ) && ! empty( $post_data['product_variation'] ) ) {
				$original_product_language = $woo_wpml->products->get_original_product_language( $post_data['product_id'] );
				$post_data['product_variation'] = apply_filters( 'translate_object_id', $post_data['product_variation'], 'product_variation', true, $original_product_language );
			}
		}

		return $post_data;
	}

	add_filter( 'tinvwl_addtowishlist_prepare', 'tinvwl_wpml_addtowishlist_prepare' );
}

if ( ! function_exists( 'tinvwl_wpml_addtowishlist_out_prepare' ) ) {

	/**
	 * Change product data if product need translate in WooCommerce Multilingual
	 *
	 * @param array $attr Data for wishlist.
	 *
	 * @return array
	 */
	function tinvwl_wpml_addtowishlist_out_prepare( $attr ) {
		if ( class_exists( 'woocommerce_wpml' ) ) {
			$woo_wpml = woocommerce_wpml::instance();
			if ( array_key_exists( 'product_id', $attr ) && ! empty( $attr['product_id'] ) ) {
				$attr['product_id'] = $woo_wpml->products->get_original_product_id( $attr['product_id'] );
			}
			if ( array_key_exists( 'product_id', $attr ) && ! empty( $attr['product_id'] ) && array_key_exists( 'variation_id', $attr ) && ! empty( $attr['variation_id'] ) ) {
				$original_product_language = $woo_wpml->products->get_original_product_language( $attr['product_id'] );
				$attr['variation_id'] = apply_filters( 'translate_object_id', $attr['variation_id'], 'product_variation', true, $original_product_language );
			}
		}

		return $attr;
	}

	add_filter( 'tinvwl_addtowishlist_out_prepare_attr', 'tinvwl_wpml_addtowishlist_out_prepare' );
}

if ( ! function_exists( 'tinvwl_wpml_addtowishlist_out_prepare_product' ) ) {

	/**
	 * Change product if product need translate in WooCommerce Multilingual
	 *
	 * @param \WC_Product $product WooCommerce Product.
	 *
	 * @return \WC_Product
	 */
	function tinvwl_wpml_addtowishlist_out_prepare_product( $product ) {
		if ( class_exists( 'woocommerce_wpml' ) && is_object( $product ) ) {
			$woo_wpml		 = woocommerce_wpml::instance();
			$product_id		 = version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->id : ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
			$variation_id	 = version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->variation_id : ( $product->is_type( 'variation' ) ? $product->get_id() : 0 );

			if ( ! empty( $product_id ) ) {
				$product_id = $woo_wpml->products->get_original_product_id( $product_id );
			}
			if ( ! empty( $product_id ) && ! empty( $variation_id ) ) {
				$original_product_language = $woo_wpml->products->get_original_product_language( $product_id );
				$variation_id = apply_filters( 'translate_object_id', $variation_id, 'product_variation', true, $original_product_language );
			}
			if ( ! empty( $product_id ) ) {
				$product = wc_get_product( $variation_id ? $variation_id : $product_id );
			}
		}

		return $product;
	}

	add_filter( 'tinvwl_addtowishlist_out_prepare_product', 'tinvwl_wpml_addtowishlist_out_prepare_product' );
}

if ( ! function_exists( 'tinvwl_wpml_addtowishlist_prepare_form' ) ) {

	/**
	 * Change product form data if product need translate in WooCommerce Multilingual
	 *
	 * @param array $post_data Data for wishlist.
	 *
	 * @return array
	 */
	function tinvwl_wpml_addtowishlist_prepare_form( $post_data ) {
		if ( class_exists( 'woocommerce_wpml' ) && is_array( $post_data ) ) {
			$woo_wpml = woocommerce_wpml::instance();
			if ( array_key_exists( 'product_id', $post_data ) && ! empty( $post_data['product_id'] ) ) {
				$post_data['product_id'] = $woo_wpml->products->get_original_product_id( $post_data['product_id'] );
			}
			if ( array_key_exists( 'product_id', $post_data ) && ! empty( $post_data['product_id'] ) && array_key_exists( 'variation_id', $post_data ) && ! empty( $post_data['variation_id'] ) ) {
				$original_product_language = $woo_wpml->products->get_original_product_language( $post_data['product_id'] );
				$post_data['variation_id'] = apply_filters( 'translate_object_id', $post_data['variation_id'], 'product_variation', true, $original_product_language );
			}
		}

		return $post_data;
	}

	add_filter( 'tinvwl_addtowishlist_prepare_form', 'tinvwl_wpml_addtowishlist_prepare_form' );
}

if ( ! function_exists( 'tinvwl_wpml_filter_link' ) ) {

	/**
	 * Corect add wishlist key for WPML plugin.
	 *
	 * @param string $full_link Link for page.
	 * @param array  $l Language.
	 *
	 * @return string
	 */
	function tinvwl_wpml_filter_link( $full_link, $l ) {
		$share_key = get_query_var( 'tinvwlID', null );
		if ( ! empty( $share_key ) ) {
			if ( get_option( 'permalink_structure' ) ) {
				$suffix = '';
				if ( preg_match( '/([^\?]+)\?*?(.*)/i', $full_link, $_full_link ) ) {
					$full_link = $_full_link[1];
					$suffix    = $_full_link[2];
				}
				if ( ! preg_match( '/\/$/', $full_link ) ) {
					$full_link .= '/';
				}
				$full_link .= $share_key . '/' . $suffix;
			} else {
				$full_link .= add_query_arg( 'tinvwlID', $share_key, $full_link );
			}
		}

		return $full_link;
	}

	add_filter( 'WPML_filter_link', 'tinvwl_wpml_filter_link', 0, 2 );
}

if ( ! function_exists( 'tinvwl_gift_card_add' ) ) {

	/**
	 * Support WooCommerce - Gift Cards
	 * Redirect to page gift card, if requires that customers enter a name and email when purchasing a Gift Card.
	 *
	 * @param boolean     $redirect Default value to redirect.
	 * @param \WC_Product $product Product data.
	 *
	 * @return boolean
	 */
	function tinvwl_gift_card_add( $redirect, $product ) {
		if ( $redirect ) {
			return true;
		}
		$is_required_field_giftcard = get_option( 'woocommerce_enable_giftcard_info_requirements' );

		if ( 'yes' == $is_required_field_giftcard ) { // WPCS: loose comparison ok.
			$is_giftcard = get_post_meta( $product->get_id(), '_giftcard', true );
			if ( 'yes' == $is_giftcard ) { // WPCS: loose comparison ok.
				return true;
			}
		}

		return $redirect;
	}

	add_filter( 'tinvwl_product_add_to_cart_need_redirect', 'tinvwl_gift_card_add', 20, 2 );
}

if ( ! function_exists( 'tinvwl_gift_card_add_url' ) ) {

	/**
	 * Support WooCommerce - Gift Cards
	 * Redirect to page gift card, if requires that customers enter a name and email when purchasing a Gift Card.
	 *
	 * @param string      $redirect_url Default value to redirect.
	 * @param \WC_Product $product Product data.
	 *
	 * @return boolean
	 */
	function tinvwl_gift_card_add_url( $redirect_url, $product ) {
		$is_required_field_giftcard = get_option( 'woocommerce_enable_giftcard_info_requirements' );

		if ( 'yes' == $is_required_field_giftcard ) { // WPCS: loose comparison ok.
			$is_giftcard = get_post_meta( $product->get_id(), '_giftcard', true );
			if ( 'yes' == $is_giftcard ) { // WPCS: loose comparison ok.
				return $product->get_permalink();
			}
		}

		return $redirect_url;
	}

	add_filter( 'tinvwl_product_add_to_cart_redirect_url', 'tinvwl_gift_card_add_url', 20, 2 );
}

if ( ! function_exists( 'tinv_wishlist_meta_support_rpgiftcards' ) ) {

	/**
	 * Set descrition for meta WooCommerce - Gift Cards
	 *
	 * @param array $meta Meta array.
	 *
	 * @return array
	 */
	function tinv_wishlist_metasupport_rpgiftcards( $meta ) {
		foreach ( $meta as $key => $data ) {
			switch ( $data['key'] ) {
				case 'rpgc_note':
					$meta[ $key ]['key'] = __( 'Note', 'rpgiftcards' );
					break;
				case 'rpgc_to':
					$meta[ $key ]['key'] = ( get_option( 'woocommerce_giftcard_to' ) <> null ? get_option( 'woocommerce_giftcard_to' ) : __( 'To', 'rpgiftcards' ) ); // WPCS: loose comparison ok.
					break;
				case 'rpgc_to_email':
					$meta[ $key ]['key'] = ( get_option( 'woocommerce_giftcard_toEmail' ) <> null ? get_option( 'woocommerce_giftcard_toEmail' ) : __( 'To Email', 'rpgiftcards' ) ); // WPCS: loose comparison ok.
					break;
				case 'rpgc_address':
					$meta[ $key ]['key'] = ( get_option( 'woocommerce_giftcard_address' ) <> null ? get_option( 'woocommerce_giftcard_address' ) : __( 'Address', 'rpgiftcards' ) ); // WPCS: loose comparison ok.
					break;
				case 'rpgc_reload_card':
					$meta[ $key ]['key'] = __( 'Reload existing Gift Card', 'rpgiftcards' );
					break;
				case 'rpgc_description':
				case 'rpgc_reload_check':
					unset( $meta[ $key ] );
					break;
			}
		}

		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_post', 'tinv_wishlist_metasupport_rpgiftcards' );
} // End if().

if ( ! function_exists( 'tinv_wishlist_metaprepare_rpgiftcards' ) ) {

	/**
	 * Prepare save meta for WooCommerce - Gift Cards
	 *
	 * @param array $meta Meta array.
	 *
	 * @return array
	 */
	function tinv_wishlist_metaprepare_rpgiftcards( $meta ) {
		if ( array_key_exists( 'rpgc_reload_check', $meta ) ) {
			foreach ( array( 'rpgc_note', 'rpgc_to', 'rpgc_to_email', 'rpgc_address' ) as $value ) {
				if ( array_key_exists( $value, $meta ) ) {
					unset( $meta[ $value ] );
				}
			}
		}

		return $meta;
	}

	add_filter( 'tinvwl_product_prepare_meta', 'tinv_wishlist_metaprepare_rpgiftcards' );
}

if ( ! function_exists( 'tinv_wishlist_metasupport_woocommerce_bookings' ) ) {

	/**
	 * Set descrition for meta WooCommerce Bookings
	 *
	 * @param array   $meta Meta array.
	 * @param integer $product_id Priduct ID.
	 * @param integer $variation_id Variation Product ID.
	 *
	 * @return array
	 */
	function tinv_wishlist_metasupport_woocommerce_bookings( $meta, $product_id, $variation_id ) {
		if ( ! class_exists( 'WC_Booking_Form' ) || ! function_exists( 'is_wc_booking_product' ) ) {
			return $meta;
		}
		$product = wc_get_product( $variation_id ? $variation_id : $product_id );
		if ( is_wc_booking_product( $product ) ) {
			$booking_form = new WC_Booking_Form( $product );
			$post_data    = array();
			foreach ( $meta as $data ) {
				$post_data[ $data['key'] ] = $data['display'];
			}
			$booking_data = $booking_form->get_posted_data( $post_data );
			$meta         = array();
			foreach ( $booking_data as $key => $value ) {
				if ( ! preg_match( '/^_/', $key ) ) {
					$meta[ $key ] = array(
						'key'     => get_wc_booking_data_label( $key, $product ),
						'display' => $value,
					);
				}
			}
		}

		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_post', 'tinv_wishlist_metasupport_woocommerce_bookings', 10, 3 );
} // End if().

if ( ! function_exists( 'tinvwl_item_price_woocommerce_bookings' ) ) {

	/**
	 * Modify price for WooCommerce Bookings
	 *
	 * @param string      $price Returned price.
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 *
	 * @return string
	 */
	function tinvwl_item_price_woocommerce_bookings( $price, $wl_product, $product ) {
		if ( ! class_exists( 'WC_Booking_Form' ) || ! function_exists( 'is_wc_booking_product' ) ) {
			return $price;
		}
		if ( is_wc_booking_product( $product ) && array_key_exists( 'meta', $wl_product ) ) {
			$booking_form = new WC_Booking_Form( $product );
			$cost         = $booking_form->calculate_booking_cost( $wl_product['meta'] );
			if ( is_wp_error( $cost ) ) {
				return $price;
			}

			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

			if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
				if ( function_exists( 'wc_get_price_excluding_tax' ) ) {
					$display_price = wc_get_price_including_tax( $product, array( 'price' => $cost ) );
				} else {
					$display_price = $product->get_price_including_tax( 1, $cost );
				}
			} else {
				if ( function_exists( 'wc_get_price_excluding_tax' ) ) {
					$display_price = wc_get_price_excluding_tax( $product, array( 'price' => $cost ) );
				} else {
					$display_price = $product->get_price_excluding_tax( 1, $cost );
				}
			}

			if ( version_compare( WC_VERSION, '2.4.0', '>=' ) ) {
				$price_suffix = $product->get_price_suffix( $cost, 1 );
			} else {
				$price_suffix = $product->get_price_suffix();
			}
			$price = wc_price( $display_price ) . $price_suffix;
		}

		return $price;
	}

	add_filter( 'tinvwl_wishlist_item_price', 'tinvwl_item_price_woocommerce_bookings', 10, 3 );
} // End if().

if ( ! function_exists( 'tinvwl_item_status_woocommerce_bookings' ) ) {

	/**
	 * Modify availability for WooCommerce Bookings
	 *
	 * @param string      $status Status availability.
	 * @param string      $availability Default availability.
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 *
	 * @return type
	 */
	function tinvwl_item_status_woocommerce_bookings( $status, $availability, $wl_product, $product ) {
		if ( ! class_exists( 'WC_Booking_Form' ) || ! function_exists( 'is_wc_booking_product' ) ) {
			return $status;
		}
		if ( is_wc_booking_product( $product ) && array_key_exists( 'meta', $wl_product ) ) {
			$booking_form = new WC_Booking_Form( $product );
			$cost         = $booking_form->calculate_booking_cost( $wl_product['meta'] );
			if ( is_wp_error( $cost ) ) {
				return '<p class="stock out-of-stock"><span><i class="fa fa-times"></i></span><span>' . $cost->get_error_message() . '</span></p>';
			}
		}

		return $status;
	}

	add_filter( 'tinvwl_wishlist_item_status', 'tinvwl_item_status_woocommerce_bookings', 10, 4 );
}

if ( ! function_exists( 'tinv_wishlist_metasupport_wc_gf_addons' ) ) {

	/**
	 * Set descrition for meta WooCommerce - Gravity Forms Product Add-Ons
	 *
	 * @param array $meta Meta array.
	 *
	 * @return array
	 */
	function tinv_wishlist_metasupport_wc_gf_addons( $meta ) {
		if ( array_key_exists( 'wc_gforms_form_id', $meta ) && class_exists( 'RGFormsModel' ) ) {
			$form_meta = RGFormsModel::get_form_meta( $meta['wc_gforms_form_id']['display'] );
			if ( array_key_exists( 'fields', $form_meta ) ) {
				$_meta = array();
				foreach ( $form_meta['fields'] as $field ) {
					$field_name = $field->get_first_input_id( array( 'id' => 0 ) );
					if ( array_key_exists( $field_name, $meta ) ) {
						$meta[ $field_name ]['key'] = $field->label;
						$_meta[ $field_name ]       = $meta[ $field_name ];
					}
				}
				$meta = $_meta;
			}
		}

		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_post', 'tinv_wishlist_metasupport_wc_gf_addons' );
}

if ( ! function_exists( 'tinv_wishlist_metasupport_woocommerce_composite_products' ) ) {

	/**
	 * Set descrition for meta WooCommerce Composite Products
	 *
	 * @param array   $meta Meta array.
	 * @param integer $product_id Product ID.
	 *
	 * @return array
	 */
	function tinv_wishlist_metasupport_woocommerce_composite_products( $meta, $product_id ) {
		if ( array_key_exists( 'wccp_component_selection', $meta ) && is_array( $meta['wccp_component_selection'] ) ) {
			$meta = array();
		} // End if().

		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_post', 'tinv_wishlist_metasupport_woocommerce_composite_products', 10, 2 );
} // End if().

if ( ! function_exists( 'tinvwl_row_woocommerce_composite_products' ) ) {

	/**
	 * Add rows for sub product for WooCommerce Composite Products
	 *
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 */
	function tinvwl_row_woocommerce_composite_products( $wl_product, $product ) {
		if ( is_object( $product ) && $product->is_type( 'composite' ) && array_key_exists( 'wccp_component_selection', $wl_product['meta'] ) ) {
			$product_quantity = $product->is_sold_individually() ? 1 : $wl_product['quantity'];

			$components = $product->get_components();
			foreach ( $components as $component_id => $component ) {
				$composited_product_id       = ! empty( $wl_product['meta']['wccp_component_selection'][ $component_id ] ) ? absint( $wl_product['meta']['wccp_component_selection'][ $component_id ] ) : '';
				$composited_product_quantity = isset( $wl_product['meta']['wccp_component_quantity'][ $component_id ] ) ? absint( $wl_product['meta']['wccp_component_quantity'][ $component_id ] ) : $component->get_quantity( 'min' );

				$composited_variation_id = isset( $wl_product['meta']['wccp_variation_id'][ $component_id ] ) ? wc_clean( $wl_product['meta']['wccp_variation_id'][ $component_id ] ) : '';

				if ( $composited_product_id ) {

					$composited_product_wrapper = $component->get_option( $composited_variation_id ? $composited_variation_id : $composited_product_id );

					if ( ! $composited_product_wrapper ) {
						continue;
					}

					$composited_product = $composited_product_wrapper->get_product();

					if ( $composited_product->is_sold_individually() && $composited_product_quantity > 1 ) {
						$composited_product_quantity = 1;
					}

					$product_url   = $composited_product->get_permalink();
					$product_image = $composited_product->get_image();
					$product_title = $composited_product->get_title();
					$product_price = $composited_product->get_price_html();
					if ( $composited_product->is_visible() ) {
						$product_image = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), $product_image );
						$product_title = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), $product_title );
					}
					$product_title .= tinv_wishlist_get_item_data( $composited_product, $wl_product );

					$availability = (array) $composited_product->get_availability();
					if ( ! array_key_exists( 'availability', $availability ) ) {
						$availability['availability'] = '';
					}
					if ( ! array_key_exists( 'class', $availability ) ) {
						$availability['class'] = '';
					}
					$availability_html = empty( $availability['availability'] ) ? '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-check"></i></span><span class="tinvwl-txt">' . esc_html__( 'In stock', 'ti-woocommerce-wishlist' ) . '</span></p>' : '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-times"></i></span><span>' . esc_html( $availability['availability'] ) . '</span></p>';
					$row_string = '<tr>';
					$row_string .= '<td colspan="2"></td>&nbsp;<td class="product-thumbnail">%2$s</td><td class="product-name">%1$s:<br/>%3$s</td>';
					if ( tinv_get_option( 'product_table', 'colm_price' ) ) {
						$row_string .= '<td class="product-price">%3$s &times; %6$s</td>';
					}
					if ( tinv_get_option( 'product_table', 'colm_date' ) ) {
						$row_string .= '<td class="product-date">&nbsp;</td>';
					}
					if ( tinv_get_option( 'product_table', 'colm_stock' ) ) {
						$row_string .= '<td class="product-stock">%5$s</td>';
					}
					if ( tinv_get_option( 'product_table', 'add_to_cart' ) ) {
						$row_string .= '<td class="product-action">&nbsp;</td>';
					}
					$row_string .= '</tr>';

					echo sprintf( $row_string, $component->get_title(), $product_image, $product_title, $product_price, $availability_html, $composited_product_quantity * $product_quantity ); // WPCS: xss ok.
				} // End if().
			} // End foreach().
		} // End if().
	}

	add_action( 'tinvwl_wishlist_row_after', 'tinvwl_row_woocommerce_composite_products', 10, 2 );
} // End if().

if ( ! function_exists( 'tinvwl_item_price_woocommerce_composite_products' ) ) {

	/**
	 * Modify price for WooCommerce Composite Products
	 *
	 * @param string      $price Returned price.
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 *
	 * @return string
	 */
	function tinvwl_item_price_woocommerce_composite_products( $price, $wl_product, $product ) {
		if ( is_object( $product ) && $product->is_type( 'composite' ) && array_key_exists( 'wccp_component_selection', $wl_product['meta'] ) ) {
			$components = $product->get_components();
			$_price			 = $product->get_price();
			$regular_price	 = $product->get_regular_price();
			foreach ( $components as $component_id => $component ) {
				$composited_product_id       = ! empty( $wl_product['meta']['wccp_component_selection'][ $component_id ] ) ? absint( $wl_product['meta']['wccp_component_selection'][ $component_id ] ) : '';
				$composited_product_quantity = isset( $wl_product['meta']['wccp_component_quantity'][ $component_id ] ) ? absint( $wl_product['meta']['wccp_component_quantity'][ $component_id ] ) : $component->get_quantity( 'min' );

				$composited_variation_id = isset( $wl_product['meta']['wccp_variation_id'][ $component_id ] ) ? wc_clean( $wl_product['meta']['wccp_variation_id'][ $component_id ] ) : '';

				if ( $composited_product_id ) {
					$composited_product_wrapper = $component->get_option( $composited_variation_id ? $composited_variation_id : $composited_product_id );
					if ( $component->is_priced_individually() ) {
						$_price			 += $composited_product_wrapper->get_price() * $composited_product_quantity;
						$regular_price	 += $composited_product_wrapper->get_regular_price() * $composited_product_quantity;
					}
				}
			}
			if ( $_price == $regular_price ) {
				$price = wc_price( $_price ) . $product->get_price_suffix();
			} else {
				$price = wc_format_sale_price( $regular_price, $_price ) . $product->get_price_suffix();
			}
		}

		return $price;
	}

	add_filter( 'tinvwl_wishlist_item_price', 'tinvwl_item_price_woocommerce_composite_products', 10, 3 );
} // End if().

if ( ! function_exists( 'tinv_wishlist_metasupport_woocommerce_product_bundles' ) ) {

	/**
	 * Set descrition for meta WooCommerce Product Bundles
	 *
	 * @param array   $meta Meta array.
	 * @param integer $product_id Product ID.
	 *
	 * @return array
	 */
	function tinv_wishlist_metasupport_woocommerce_product_bundles( $meta, $product_id ) {
		$product = wc_get_product( $product_id );
		if ( is_object( $product ) && $product->is_type( 'bundle' ) ) {
			$meta = array();
		}

		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_post', 'tinv_wishlist_metasupport_woocommerce_product_bundles', 10, 2 );
} // End if().

if ( ! function_exists( 'tinvwl_row_woocommerce_product_bundles' ) ) {

	/**
	 * Add rows for sub product for WooCommerce Product Bundles
	 *
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 */
	function tinvwl_row_woocommerce_product_bundles( $wl_product, $product ) {
		if ( is_object( $product ) && $product->is_type( 'bundle' ) ) {
			$product_quantity = $product->is_sold_individually() ? 1 : $wl_product['quantity'];

			$product_id    = WC_PB_Core_Compatibility::get_id( $product );
			$bundled_items = $product->get_bundled_items();
			if ( ! empty( $bundled_items ) ) {
				foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {
					$bundled_product_id						 = $bundled_item->product_id;
					$bundled_item_variation_id_request_key	 = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_variation_id_' . $bundled_item_id;
					$bundled_variation_id					 = absint( isset( $wl_product['meta'][ $bundled_item_variation_id_request_key ] ) ? $wl_product['meta'][ $bundled_item_variation_id_request_key ] : 0 );
					if ( ! empty( $bundled_variation_id ) ) {
						$bundled_item->product = wc_get_product( $bundled_variation_id );
					}
					$bundled_product_type = $bundled_item->product->get_type();
					$is_optional          = $bundled_item->is_optional();

					$bundled_item_quantity_request_key = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_quantity_' . $bundled_item_id;
					$bundled_product_qty               = isset( $wl_product['meta'][ $bundled_item_quantity_request_key ] ) ? absint( $wl_product['meta'][ $bundled_item_quantity_request_key ] ) : $bundled_item->get_quantity();

					if ( $is_optional ) {

						/** Documented in method 'get_posted_bundle_configuration'. */
						$bundled_item_selected_request_key = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_selected_optional_' . $bundled_item_id;

						if ( ! array_key_exists( $bundled_item_selected_request_key, $wl_product['meta'] ) ) {
							$bundled_product_qty = 0;
						}
					}
					if ( 0 === $bundled_product_qty || 'visible' != $bundled_item->cart_visibility ) {
						continue;
					}

					$product_url   = $bundled_item->product->get_permalink();
					$product_image = $bundled_item->product->get_image();
					$product_title = $bundled_item->product->get_title();
					$product_price = $bundled_item->product->get_price_html();
					if ( $bundled_item->product->is_visible() ) {
						$product_image = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), $product_image );
						$product_title = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), $product_title );
					}
					$product_title .= tinv_wishlist_get_item_data( $bundled_item->product, $wl_product );

					$availability = (array) $bundled_item->product->get_availability();
					if ( ! array_key_exists( 'availability', $availability ) ) {
						$availability['availability'] = '';
					}
					if ( ! array_key_exists( 'class', $availability ) ) {
						$availability['class'] = '';
					}
					$availability_html = empty( $availability['availability'] ) ? '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-check"></i></span><span class="tinvwl-txt">' . esc_html__( 'In stock', 'ti-woocommerce-wishlist' ) . '</span></p>' : '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-times"></i></span><span>' . esc_html( $availability['availability'] ) . '</span></p>';
					$row_string        = '<tr>';
					$row_string        .= '<td colspan="2">&nbsp;</td><td class="product-thumbnail">%1$s</td><td class="product-name">%2$s</td>';
					if ( tinv_get_option( 'product_table', 'colm_price' ) ) {
						$row_string .= '<td class="product-price">%3$s &times; %5$s</td>';
					}
					if ( tinv_get_option( 'product_table', 'colm_date' ) ) {
						$row_string .= '<td class="product-date">&nbsp;</td>';
					}
					if ( tinv_get_option( 'product_table', 'colm_stock' ) ) {
						$row_string .= '<td class="product-stock">%4$s</td>';
					}

					if ( tinv_get_option( 'product_table', 'add_to_cart' ) ) {
						$row_string .= '<td class="product-action">&nbsp;</td>';
					}
					$row_string .= '</tr>';

					echo sprintf( $row_string, $product_image, $product_title, $product_price, $availability_html, $bundled_product_qty ); // WPCS: xss ok.
				} // End foreach().
			} // End if().
		} // End if().
	}

	add_action( 'tinvwl_wishlist_row_after', 'tinvwl_row_woocommerce_product_bundles', 10, 2 );
} // End if().

if ( ! function_exists( 'tinvwl_item_price_woocommerce_product_bundles' ) ) {

	/**
	 * Modify price for WooCommerce Product Bundles
	 *
	 * @param string      $price Returned price.
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 *
	 * @return string
	 */
	function tinvwl_item_price_woocommerce_product_bundles( $price, $wl_product, $product ) {
		if ( is_object( $product ) && $product->is_type( 'bundle' ) ) {

			$bundle_price  = $product->get_price();
			$product_id    = WC_PB_Core_Compatibility::get_id( $product );
			$bundled_items = $product->get_bundled_items();

			if ( ! empty( $bundled_items ) ) {

				$bundled_items_price = 0.0;

				foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {
					$is_optional = $bundled_item->is_optional();

					$bundled_item_quantity_request_key = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_quantity_' . $bundled_item_id;
					$bundled_product_qty               = isset( $wl_product['meta'][ $bundled_item_quantity_request_key ] ) ? absint( $wl_product['meta'][ $bundled_item_quantity_request_key ] ) : $bundled_item->get_quantity();

					if ( $is_optional ) {

						/** Documented in method 'get_posted_bundle_configuration'. */
						$bundled_item_selected_request_key = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_selected_optional_' . $bundled_item_id;

						if ( ! array_key_exists( $bundled_item_selected_request_key, $wl_product['meta'] ) ) {
							$bundled_product_qty = 0;
						}
					}
					if ( 0 === $bundled_product_qty ) {
						continue;
					}

					$bundled_item_price = $bundled_item->product->get_price() * $bundled_product_qty;

					$bundled_items_price += (double) $bundled_item_price;

				} // End foreach().
				$price = wc_price( (double) $bundle_price + $bundled_items_price );
				$price = apply_filters( 'woocommerce_get_price_html', $price, $product );
			} // End if().
		} // End if().

		return $price;
	}

	add_filter( 'tinvwl_wishlist_item_price', 'tinvwl_item_price_woocommerce_product_bundles', 10, 3 );
} // End if().

if ( ! function_exists( 'tinv_wishlist_metasupport_woocommerce_mix_and_match_products' ) ) {

	/**
	 * Set descrition for meta WooCommerce Mix and Match
	 *
	 * @param array   $meta Meta array.
	 * @param integer $product_id Product ID.
	 *
	 * @return array
	 */
	function tinv_wishlist_metasupport_woocommerce_mix_and_match_products( $meta, $product_id ) {
		if ( array_key_exists( 'mnm_quantity', $meta ) ) {
			$product = wc_get_product( $product_id );
			if ( is_object( $product ) && $product->is_type( 'mix-and-match' ) ) {
				$meta = array();
			}
		}

		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_post', 'tinv_wishlist_metasupport_woocommerce_mix_and_match_products', 10, 2 );
} // End if().

if ( ! function_exists( 'tinvwl_row_woocommerce_mix_and_match_products' ) ) {

	/**
	 * Add rows for sub product for WooCommerce Mix and Match
	 *
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 */
	function tinvwl_row_woocommerce_mix_and_match_products( $wl_product, $product ) {
		if ( is_object( $product ) && $product->is_type( 'mix-and-match' ) && array_key_exists( 'mnm_quantity', $wl_product['meta'] ) ) {
			$product_quantity = $product->is_sold_individually() ? 1 : $wl_product['quantity'];
			$mnm_items        = $product->get_children();
			if ( ! empty( $mnm_items ) ) {
				foreach ( $mnm_items as $id => $mnm_item ) {
					$item_quantity = 0;
					if ( array_key_exists( $id, $wl_product['meta']['mnm_quantity'] ) ) {
						$item_quantity = absint( $wl_product['meta']['mnm_quantity'][ $id ] );
					}
					if ( 0 >= $item_quantity ) {
						continue;
					}

					$product_url   = $mnm_item->get_permalink();
					$product_image = $mnm_item->get_image();
					$product_title = $mnm_item->get_title();
					$product_price = $mnm_item->get_price_html();
					if ( $mnm_item->is_visible() ) {
						$product_image = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), $product_image );
						$product_title = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), $product_title );
					}
					$product_title .= tinv_wishlist_get_item_data( $mnm_item, $wl_product );

					$availability = (array) $mnm_item->get_availability();
					if ( ! array_key_exists( 'availability', $availability ) ) {
						$availability['availability'] = '';
					}
					if ( ! array_key_exists( 'class', $availability ) ) {
						$availability['class'] = '';
					}
					$availability_html = empty( $availability['availability'] ) ? '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-check"></i></span><span class="tinvwl-txt">' . esc_html__( 'In stock', 'ti-woocommerce-wishlist' ) . '</span></p>' : '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-times"></i></span><span>' . esc_html( $availability['availability'] ) . '</span></p>';
					$row_string        = '<tr>';
					$row_string        .= '<td colspan="2">&nbsp;</td><td class="product-thumbnail">%1$s</td><td class="product-name">%2$s</td>';
					if ( tinv_get_option( 'product_table', 'colm_price' ) ) {
						$row_string .= '<td class="product-price">%3$s &times; %5$s</td>';
					}
					if ( tinv_get_option( 'product_table', 'colm_date' ) ) {
						$row_string .= '<td class="product-date">&nbsp;</td>';
					}
					if ( tinv_get_option( 'product_table', 'colm_stock' ) ) {
						$row_string .= '<td class="product-stock">%4$s</td>';
					}
					if ( tinv_get_option( 'product_table', 'add_to_cart' ) ) {
						$row_string .= '<td class="product-action">&nbsp;</td>';
					}
					$row_string .= '</tr>';

					echo sprintf( $row_string, $product_image, $product_title, $product_price, $availability_html, $item_quantity * $product_quantity ); // WPCS: xss ok.
				} // End foreach().
			} // End if().
		} // End if().
	}

	add_action( 'tinvwl_wishlist_row_after', 'tinvwl_row_woocommerce_mix_and_match_products', 10, 2 );
} // End if().

if ( ! function_exists( 'tinvwl_item_price_woocommerce_mix_and_match_products' ) ) {

	/**
	 * Modify price for WooCommerce Mix and Match
	 *
	 * @param string      $price Returned price.
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 *
	 * @return string
	 */
	function tinvwl_item_price_woocommerce_mix_and_match_products( $price, $wl_product, $product ) {
		if ( is_object( $product ) && $product->is_type( 'mix-and-match' ) && $product->is_priced_per_product() ) {
			$mnm_items = $product->get_children();
			if ( ! empty( $mnm_items ) ) {
				$_price = 0;
				foreach ( $mnm_items as $id => $mnm_item ) {
					$item_quantity = 0;
					if ( array_key_exists( $id, $wl_product['meta']['mnm_quantity'] ) ) {
						$item_quantity = absint( $wl_product['meta']['mnm_quantity'][ $id ] );
					}
					if ( 0 >= $item_quantity ) {
						continue;
					}
					$_price += wc_get_price_to_display( $mnm_item, array( 'qty' => $item_quantity ) );
				}
				if ( 0 < $_price ) {
					if ( $product->is_on_sale() ) {
						$price = wc_format_sale_price( $_price + wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), $_price + wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
					} else {
						$price = wc_price( $_price + wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
					}
					$price = apply_filters( 'woocommerce_get_price_html', $price, $product );
				}
			}
		}

		return $price;
	}

	add_filter( 'tinvwl_wishlist_item_price', 'tinvwl_item_price_woocommerce_mix_and_match_products', 10, 3 );
} // End if().

if ( ! function_exists( 'tinvwl_add_form_woocommerce_mix_and_match_products' ) ) {

	/**
	 * Remove empty meta for WooCommerce Mix and Match
	 *
	 * @param array $form Post form data.
	 * @return array
	 */
	function tinvwl_add_form_woocommerce_mix_and_match_products( $form = array() ) {
		if ( array_key_exists( 'mnm_quantity', $form ) ) {
			if ( is_array( $form['mnm_quantity'] ) && ! empty( $form['mnm_quantity'] ) ) {
				foreach ( $form['mnm_quantity'] as $key => $value ) {
					$value = absint( $value );
					if ( empty( $value ) ) {
						unset( $form['mnm_quantity'][ $key ] );
					}
				}
				if ( empty( $form['mnm_quantity'] ) ) {
					unset( $form['mnm_quantity'] );
				}
			}
		}

		return $form;
	}

	add_filter( 'tinvwl_addtowishlist_add_form', 'tinvwl_add_form_woocommerce_mix_and_match_products' );
} // End if().

if ( ! function_exists( 'tinv_wishlist_metasupport_yith_woocommerce_product_bundles' ) ) {

	/**
	 * Set descrition for meta WooCommerce Mix and Match
	 *
	 * @param array   $meta Meta array.
	 * @param integer $product_id Product ID.
	 *
	 * @return array
	 */
	function tinv_wishlist_metasupport_yith_woocommerce_product_bundles( $meta, $product_id ) {
		if ( array_key_exists( 'yith_bundle_quantity_1', $meta ) ) {
			$product = wc_get_product( $product_id );
			if ( is_object( $product ) && $product->is_type( 'yith_bundle' ) ) {
				$meta = array();
			}
		}

		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_post', 'tinv_wishlist_metasupport_yith_woocommerce_product_bundles', 10, 2 );
} // End if().

if ( ! function_exists( 'tinvwl_item_status_yith_woocommerce_product_bundles' ) ) {

	/**
	 * Modify status for YITH WooCommerce Product Bundles
	 *
	 * @param string      $availability_html Returned availability status.
	 * @param string      $availability Availability status.
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 *
	 * @return string
	 */
	function tinvwl_item_status_yith_woocommerce_product_bundles( $availability_html, $availability, $wl_product, $product ) {
		if ( empty( $availability ) && is_object( $product ) && $product->is_type( 'yith_bundle' ) ) {
			$response      = true;
			$bundled_items = $product->get_bundled_items();
			foreach ( $bundled_items as $key => $bundled_item ) {
				if ( method_exists( $bundled_item, 'is_optional' ) ) {
					if ( $bundled_item->is_optional() && ! array_key_exists( 'yith_bundle_optional_' . $key, $wl_product['meta'] ) ) {
						continue;
					}
				}
				if ( ! $bundled_item->get_product()->is_in_stock() ) {
					$response = false;
				}
			}

			if ( ! $response ) {
				$availability = array(
					'class'        => 'out-of-stock',
					'availability' => __( 'Out of stock', 'woocommerce' ),
				);
				$availability_html = '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-times"></i></span><span>' . esc_html( $availability['availability'] ) . '</span></p>';
			}
		}

		return $availability_html;
	}

	add_filter( 'tinvwl_wishlist_item_status', 'tinvwl_item_status_yith_woocommerce_product_bundles', 10, 4 );
} // End if().

if ( ! function_exists( 'tinvwl_row_yith_woocommerce_product_bundles' ) ) {

	/**
	 * Add rows for sub product for YITH WooCommerce Product Bundles
	 *
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 */
	function tinvwl_row_yith_woocommerce_product_bundles( $wl_product, $product ) {
		if ( is_object( $product ) && $product->is_type( 'yith_bundle' ) ) {
			$bundled_items    = $product->get_bundled_items();
			$product_quantity = $product->is_sold_individually() ? 1 : $wl_product['quantity'];
			if ( ! empty( $bundled_items ) ) {
				foreach ( $bundled_items as $key => $bundled_item ) {
					$item_quantity = $bundled_item->get_quantity();
					if ( array_key_exists( 'yith_bundle_quantity_' . $key, $wl_product['meta'] ) ) {
						$item_quantity = absint( $wl_product['meta'][ 'yith_bundle_quantity_' . $key ] );
					}
					if ( method_exists( $bundled_item, 'is_optional' ) ) {
						if ( $bundled_item->is_optional() && ! array_key_exists( 'yith_bundle_optional_' . $key, $wl_product['meta'] ) ) {
							$item_quantity = 0;
						}
					}
					if ( 0 >= $item_quantity ) {
						continue;
					}

					$product = $bundled_item->get_product();
					if ( ! is_object( $product ) ) {
						continue;
					}

					$product_url   = $product->get_permalink();
					$product_image = $product->get_image();
					$product_title = $product->get_title();
					$product_price = $product->get_price_html();
					if ( $product->is_visible() ) {
						$product_image = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), $product_image );
						$product_title = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), $product_title );
					}
					$product_title .= tinv_wishlist_get_item_data( $product, $wl_product );

					$availability = (array) $product->get_availability();
					if ( ! array_key_exists( 'availability', $availability ) ) {
						$availability['availability'] = '';
					}
					if ( ! array_key_exists( 'class', $availability ) ) {
						$availability['class'] = '';
					}
					$availability_html = empty( $availability['availability'] ) ? '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-check"></i></span><span class="tinvwl-txt">' . esc_html__( 'In stock', 'ti-woocommerce-wishlist' ) . '</span></p>' : '<p class="stock ' . esc_attr( $availability['class'] ) . '"><span><i class="fa fa-times"></i></span><span>' . esc_html( $availability['availability'] ) . '</span></p>';
					$row_string        = '<tr>';
					$row_string        .= '<td colspan="2">&nbsp;</td><td class="product-thumbnail">%1$s</td><td class="product-name">%2$s</td>';
					if ( tinv_get_option( 'product_table', 'colm_price' ) ) {
						$row_string .= '<td class="product-price">%3$s &times; %5$s</td>';
					}
					if ( tinv_get_option( 'product_table', 'colm_date' ) ) {
						$row_string .= '<td class="product-date">&nbsp;</td>';
					}
					if ( tinv_get_option( 'product_table', 'colm_stock' ) ) {
						$row_string .= '<td class="product-stock">%4$s</td>';
					}
					if ( tinv_get_option( 'product_table', 'add_to_cart' ) ) {
						$row_string .= '<td class="product-action">&nbsp;</td>';
					}
					$row_string .= '</tr>';

					echo sprintf( $row_string, $product_image, $product_title, $product_price, $availability_html, $item_quantity * $product_quantity ); // WPCS: xss ok.
				} // End foreach().
			} // End if().
		} // End if().
	}

	add_action( 'tinvwl_wishlist_row_after', 'tinvwl_row_yith_woocommerce_product_bundles', 10, 2 );
} // End if().

if ( ! function_exists( 'tinv_wishlist_metasupport_woocommerce_product_add_on' ) ) {

	/**
	 * Set descrition for meta WooCommerce Product Add-on
	 *
	 * @param array   $meta Meta array.
	 * @param integer $product_id Product ID.
	 *
	 * @return array
	 */
	function tinv_wishlist_metasupport_woocommerce_product_add_on( $meta, $product_id ) {
		$personalized_meta = absint( get_post_meta( $product_id, '_product_meta_id', true ) );
		if ( ! empty( $personalized_meta ) ) {
			$meta = array();
		}
		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_post', 'tinv_wishlist_metasupport_woocommerce_product_add_on', 10, 2 );
} // End if().

if ( ! function_exists( 'tinv_wishlist_item_meta_woocommerce_product_add_on' ) ) {

	/**
	 * Set descrition for meta WooCommerce Product Add-on
	 *
	 * @param array       $meta Meta array.
	 * @param array       $wl_product Wishlist Product.
	 * @param \WC_Product $product Woocommerce Product.
	 *
	 * @return array
	 */
	function tinv_wishlist_item_meta_woocommerce_product_add_on( $meta, $wl_product, $product ) {
		$personalized_meta = absint( get_post_meta( $wl_product['product_id'], '_product_meta_id', true ) );
		if ( ! empty( $personalized_meta ) && class_exists( 'NM_PersonalizedProduct' ) ) {
			$_meta             = $wl_product['meta'];
			$single_meta       = NM_PersonalizedProduct::get_instance()->get_product_meta( $personalized_meta );
			$product_meta      = json_decode( $single_meta->the_meta );
			$product_meta_data = array();
			$item_meta         = array();

			if ( $product_meta ) {
				foreach ( $product_meta as $__meta ) {
					if ( ! isset( $__meta->data_name ) ) {
						continue;
					}

					$element_name	 = strtolower( preg_replace( '![^a-z0-9]+!i', '_', $__meta->data_name ) );
					$element_value	 = '';
					$thefiles_key	 = 'thefile_' . $element_name;
					if ( ! isset( $_meta[ $element_name ] ) && ! isset( $_meta[ $thefiles_key ] ) ) {
						continue;
					}

					switch ( $__meta->type ) {
						case 'checkbox':
							if ( array_key_exists( $element_name, $_meta ) ) {
								$element_value = implode( ',', array_map( 'sanitize_text_field', wp_unslash( $_meta[ $element_name ] ) ) );
							}
							break;
						case 'select':
						case 'radio':
							$element_value	 = sanitize_text_field( $_meta[ $element_name ] );
							break;
						case 'file':
						case 'facebook':
							break;
						case 'image':
							$element_value	 = isset( $_meta[ $element_name ] ) ? sanitize_text_field( $_meta[ $element_name ] ) : '';
						case 'section':
							if ( ! is_array( $element_value ) ) {
								$post_element_name = '';
								if ( isset( $_meta[ $element_name ] ) && is_array( $_meta[ $element_name ] ) ) {
									$post_element_name	 = array_map( 'sanitize_text_field', wp_unslash( $_meta[ $element_name ] ) );
									$nele				 = array();
									foreach ( $post_element_name as $ele ) {
										$ele	 = stripslashes( nl2br( $ele ) );
										$nele[]	 = $ele;
									}
									$post_element_name = $nele;
								} else {
									$post_element_name = sanitize_text_field( $_meta[ $element_name ] );
								}
								if ( $post_element_name != '' ) { // @codingStandardsIgnoreLine WordPress.PHP.YodaConditions.NotYoda
									$element_value = $post_element_name;
								}
							}
							break;
						default:
							$element_value = sanitize_text_field( $_meta[ $element_name ] );
					} // End switch().
					if ( ! empty( $element_value ) ) {
						switch ( $__meta->type ) {
							case 'image':
								// If selected designs are more then one.
								if ( is_array( $element_value ) ) {

									$_v = '';
									foreach ( $element_value as $selected ) {
										$selecte_image_meta = json_decode( stripslashes( $selected ) );
										$_v .= $selecte_image_meta->title . ',';
									}
									$item_meta[] = array(
										'key'		 => $__meta->title,
										'display'	 => __( 'Photos imported - ', 'nm-personalizedproduct' ) . count( $element_value ),
									);
								} else {
									$selecte_image_meta	 = json_decode( stripslashes( $element_value ) );
									$item_meta[]		 = array(
										'key'		 => $__meta->title,
										'display'	 => $selecte_image_meta->title,
									);
								}
								break;
							default:
								if ( is_array( $element_value ) ) {
									list($filekey, $filename) = each( $element_value );
									if ( NM_PersonalizedProduct::get_instance()->is_image( $filename ) ) {
										$item_meta[] = array(
											'key'		 => $__meta->title,
											'display'	 => NM_PersonalizedProduct::get_instance()->make_filename_link( $element_value ),
										);
									} else {
										$item_meta[] = array(
											'key'		 => $__meta->title,
											'display'	 => implode( ',', $element_value ),
										);
									}
								} else {
									$item_meta[] = array(
										'key'		 => $__meta->title,
										'display'	 => stripslashes( $element_value ),
									);
								}
								break;
						} // End switch().
					} // End if().
				} // End foreach().
			} // End if().

			if ( 0 < count( $item_meta ) ) {
				ob_start();
				tinv_wishlist_template( 'ti-wishlist-item-data.php', array( 'item_data' => $item_meta ) );
				$meta .= '<br/>' . ob_get_clean();
			}
		} // End if().

		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_data', 'tinv_wishlist_item_meta_woocommerce_product_add_on', 10, 3 );
} // End if().

if ( ! function_exists( 'TII18n' ) ) {

	/**
	 * Return TI Yoasti 18n module class
	 *
	 * @return \TInvWL_Includes_API_Yoasti18n
	 */
	function TII18n() { // @codingStandardsIgnoreLine WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return TInvWL_Includes_API_Yoasti18n::instance();
	}
}

// Create a helper function for easy SDK access.
if ( ! function_exists( 'tinvwl_fs' ) ) {

	/**
	 * Freemius
	 *
	 * @return array
	 */
	function tinvwl_fs() {
		global $tinvwl_fs;

		if ( ! isset( $tinvwl_fs ) ) {
			// Include Freemius SDK.
			require_once dirname( __FILE__ ) . '/freemius/start.php';

			$tinvwl_fs = fs_dynamic_init( array(
				'id'                  => '839',
				'slug'                => 'ti-woocommerce-wishlist',
				'type'                => 'plugin',
				'public_key'          => 'pk_1944d351ab27040c8f65c72d1e7e7',
				'is_premium'          => false,
				'has_premium_version' => false,
				'has_addons'          => false,
				'has_paid_plans'      => false,
				'menu'                => array(
					'slug'       => 'tinvwl',
					'first-path' => 'admin.php?page=tinvwl' . ( get_option( TINVWL_PREFIX . '_wizard' ) ? '' : '-wizard' ),
					'account'    => false,
					'support'    => false,
				),
			) );
		}

		return $tinvwl_fs;
	}

	// Init Freemius.
	tinvwl_fs();

	if ( ! function_exists( 'tinvwl_fs_custom_connect_message_on_update' ) ) {
		function tinvwl_fs_custom_connect_message_on_update(
			$message, $user_first_name, $plugin_title, $user_login, $site_link,
			$freemius_link
		) {
			return sprintf(
				__fs( 'hey-x' ) . '<br>' .
				__( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'ti-woocommerce-wishlist' ), $user_first_name, '<b>' . $plugin_title . '</b>', '<b>' . $user_login . '</b>', $site_link, $freemius_link
			);
		}

		tinvwl_fs()->add_filter( 'connect_message_on_update', 'tinvwl_fs_custom_connect_message_on_update', 10, 6 );
	}

	if ( ! function_exists( 'tinvwl_fs_custom_connect_message' ) ) {
		function tinvwl_fs_custom_connect_message(
			$message, $user_first_name, $plugin_title, $user_login, $site_link,
			$freemius_link
		) {
			return sprintf(
				__fs( 'hey-x' ) . '<br>' .
				__( 'Allow %6$s to collect some usage data with %5$s to make the plugin even more awesome. If you skip this, that\'s okay! %2$s will still work just fine.', 'ti-woocommerce-wishlist' ), $user_first_name, '<b>' . __( 'WooCommerce Wishlist Plugin', 'ti-woocommerce-wishlist' ) . '</b>', '<b>' . $user_login . '</b>', $site_link, $freemius_link, '<b>' . __( 'TemplateInvaders', 'ti-woocommerce-wishlist' ) . '</b>'
			);
		}

		tinvwl_fs()->add_filter( 'connect_message', 'tinvwl_fs_custom_connect_message', 10, 6 );
	}

	tinvwl_fs()->add_action( 'after_uninstall', 'uninstall_tinv_wishlist' );
} // End if().
