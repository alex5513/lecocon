<?php
/**
 * Basic function for plugin
 *
 * @since             1.0.0
 * @package           TInvWishlist
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( function_exists( 'spl_autoload_register' ) ) {

	/**
	 * Autoloader class. If no function spl_autoload_register, then all the files will be required
	 *
	 * @param string $_class Required class name.
	 *
	 * @return boolean
	 */
	function autoload_tinv_wishlist( $_class ) {
		$preffix = 'TInvWL';
		$ext	 = '.php';
		$class	 = explode( '_', $_class );
		$object	 = array_shift( $class );
		if ( $preffix !== $object ) {
			return false;
		}
		if ( empty( $class ) ) {
			$class = array( $preffix );
		}
		$basicclass	 = $class;
		array_unshift( $class, 'includes' );
		$classs		 = array(
			TINVWL_PATH . strtolower( implode( DIRECTORY_SEPARATOR, $basicclass ) ),
			TINVWL_PATH . strtolower( implode( DIRECTORY_SEPARATOR, $class ) ),
		);
		foreach ( $classs as $class ) {
			foreach ( array( '.class', '.helper' ) as $suffix ) {
				$filename = $class . $suffix . $ext;
				if ( file_exists( $filename ) ) {
					require_once $filename;
					return true;
				}
			}
		}
		return false;
	}

	spl_autoload_register( 'autoload_tinv_wishlist' );
} // End if().

if ( ! function_exists( 'tinv_array_merge' ) ) {

	/**
	 * Function to merge arrays with replacement options
	 *
	 * @param array $array1 Array.
	 * @param array $_ Array.
	 *
	 * @return array
	 */
	function tinv_array_merge( $array1, $_ = null ) {
		if ( ! is_array( $array1 ) ) {
			return $array1;
		}
		$args = func_get_args();
		array_shift( $args );
		foreach ( $args as $array2 ) {
			if ( is_array( $array2 ) ) {
				foreach ( $array2 as $key => $value ) {
					$array1[ $key ] = $value;
				}
			}
		}
		return $array1;
	}
}

if ( ! function_exists( 'tinv_get_option_defaults' ) ) {

	/**
	 * Extract default options from settings class
	 *
	 * @param string $category Name category settings.
	 *
	 * @return array
	 */
	function tinv_get_option_defaults( $category ) {
		$dir = TINVWL_PATH . 'admin/settings/';
		if ( ! file_exists( $dir ) || ! is_dir( $dir ) ) {
			return array();
		}
		$files = scandir( $dir );
		foreach ( $files as $key => $value ) {
			if ( preg_match( '/\.class\.php$/i', $value ) ) {
				$files[ $key ] = preg_replace( '/\.class\.php$/i', '', $value );
			} else {
				unset( $files[ $key ] );
			}
		}
		$defaults = array();
		foreach ( $files as $file ) {
			$class			 = 'TInvWL_Admin_Settings_' . ucfirst( $file );
			$class			 = new $class( '', '' );
			$class_methods	 = get_class_methods( $class );
			foreach ( $class_methods as $method ) {
				if ( preg_match( '/_data$/i', $method ) ) {
					$settings	 = $class->get_defaults( $class->$method() );
					$defaults	 = tinv_array_merge( $defaults, $settings );
				}
			}
		}
		if ( 'all' === $category ) {
			return $defaults;
		}
		if ( array_key_exists( $category, $defaults ) ) {
			return $defaults[ $category ];
		}
		return array();
	}
} // End if().

if ( ! function_exists( 'tinv_get_option' ) ) {

	/**
	 * Extract options from database or default array settings.
	 *
	 * @param string $category Name category settings.
	 * @param string $option Name paremetr. If is empty string, then function return array category settings.
	 *
	 * @return mixed
	 */
	function tinv_get_option( $category, $option = '' ) {
		$prefix	 = TINVWL_PREFIX . '-';
		$values	 = get_option( $prefix . $category, array() );
		if ( empty( $values ) ) {
			$values = tinv_get_option_defaults( $category );
		}
		if ( empty( $option ) ) {
			return $values;
		} else {
			if ( array_key_exists( $option, $values ) ) {
				return $values[ $option ];
			} else {
				$values = tinv_get_option_defaults( $category );
				if ( array_key_exists( $option, (array) $values ) ) {
					return $values[ $option ];
				}
			}
		}
		return null;
	}
}

if ( ! function_exists( 'tinv_get_option_admin' ) ) {

	/**
	 * Extract options from database or default array settings.
	 *
	 * @param string $category Name category settings.
	 * @param string $option Name paremetr. If is empty string, then function return array category settings.
	 *
	 * @return mixed
	 */
	function tinv_get_option_admin( $category, $option = '' ) {
		$prefix	 = TINVWL_PREFIX . '-';
		$values	 = get_option( $prefix . $category, array() );
		if ( empty( $values ) ) {
			$values = array();
		}
		if ( empty( $option ) ) {
			return $values;
		} elseif ( array_key_exists( $option, $values ) ) {
			return $values[ $option ];
		}
		return null;
	}
}

if ( ! function_exists( 'tinv_style' ) ) {

	/**
	 * Get style for custom style
	 *
	 * @param string $selector Selector style.
	 * @param string $element Attribute name.
	 *
	 * @return string
	 */
	function tinv_style( $selector = '', $element = '' ) {
		$key	 = md5( $selector . '||' . $element );
		$values	 = get_option( TINVWL_PREFIX . '-style_options', array() );
		if ( empty( $values ) ) {
			return '';
		}
		if ( array_key_exists( $key, $values ) ) {
			return $values[ $key ];
		}
		return '';
	}
}

if ( ! function_exists( 'tinv_update_option' ) ) {

	/**
	 * Update options in database.
	 *
	 * @param string $category Name category settings.
	 * @param string $option Name paremetr. If is empty string, then function update array category settings.
	 * @param mixed  $value Value option.
	 *
	 * @return boolean
	 */
	function tinv_update_option( $category, $option = '', $value = false ) {
		$prefix = TINVWL_PREFIX . '-';
		if ( empty( $option ) ) {
			if ( is_array( $value ) ) {
				update_option( $prefix . $category, $value );
				return true;
			}
		} else {
			$values = get_option( $prefix . $category, array() );

			$values[ $option ] = $value;
			update_option( $prefix . $category, $values );
			return true;
		}
		return false;
	}
}

if ( ! function_exists( 'tinv_wishlist_template' ) ) {

	/**
	 * The function overwrites the method output templates woocommerce
	 *
	 * @param string $template_name Name file template.
	 * @param array  $args Array variable in template.
	 * @param string $template_path Customization path.
	 */
	function tinv_wishlist_template( $template_name, $args = array(), $template_path = '' ) {
		if ( function_exists( 'wc_get_template' ) ) {
			wc_get_template( $template_name, $args, $template_path );
		} else {
			woocommerce_get_template( $template_name, $args, $template_path );
		}
	}
}

if ( ! function_exists( 'tinv_wishlist_locate_template' ) ) {

	/**
	 * Overwrites path for email and other template
	 *
	 * @param string $template_name Requered Template file.
	 * @param string $template_path Template path.
	 * @param string $default_path Template default path.
	 *
	 * @return string
	 */
	function tinv_wishlist_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = WC()->template_path();
		}

		if ( ! $default_path ) {
			$default_path = TINVWL_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template( array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		) );

		// Get default template.
		if ( ! $template && file_exists( $default_path . $template_name ) ) {
			$template = $default_path . $template_name;
		}

		// Return what we found.
		return apply_filters( 'tinvwl_locate_template', $template, $template_name, $template_path );
	}
} // End if().

if ( ! function_exists( 'tinv_wishlist_template_html' ) ) {

	/**
	 * The function overwrites the method return templates woocommerce
	 *
	 * @param string $template_name Name file template.
	 * @param array  $args Array variable in template.
	 * @param string $template_path Customization path.
	 *
	 * @return string
	 */
	function tinv_wishlist_template_html( $template_name, $args = array(), $template_path = '' ) {
		ob_start();
		tinv_wishlist_template( $template_name, $args, $template_path );
		return ob_get_clean();
	}
}

if ( ! function_exists( 'tinv_wishlist_get_item_data' ) ) {

	/**
	 * Extract meta attributes for product
	 *
	 * @param object  $product Object selected product.
	 * @param array   $wl_product Wishlist selected product.
	 * @param boolean $flat Return text or template.
	 *
	 * @return string
	 */
	function tinv_wishlist_get_item_data( $product, $wl_product = array(), $flat = false ) {
		$item_data      = array();
		$variation_id   = version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->variation_id : ( $product->is_type( 'variation' ) ? $product->get_id() : 0 );
		$variation_data = version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->variation_data : ( $product->is_type( 'variation' ) ? wc_get_product_variation_attributes( $product->get_id() ) : array() );
		if ( ! empty( $variation_id ) && is_array( $variation_data ) ) {
			foreach ( $variation_data as $name => $value ) {
				if ( '' === $value ) {
					// Could be any value that saved to a custom meta.
					if ( array_key_exists( 'meta', $wl_product ) && array_key_exists( $name, $wl_product['meta'] ) ) {
						$value = $wl_product['meta'][ $name ];
					} else {
						continue;
					}
				}

				$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

				// If this is a term slug, get the term's nice name.
				if ( taxonomy_exists( $taxonomy ) ) {
					$term = get_term_by( 'slug', $value, $taxonomy ); // @codingStandardsIgnoreLine WordPress.VIP.RestrictedFunctions.get_term_by
					if ( !is_wp_error( $term ) && $term && $term->name ) {
						$value = $term->name;
					}
					$label = wc_attribute_label( $taxonomy );

					// If this is a custom option slug, get the options name.
				} else {
					$value				 = apply_filters( 'woocommerce_variation_option_name', $value );
					$product_attributes	 = $product->get_attributes();

					if ( isset( $product_attributes[ str_replace( 'attribute_', '', $name ) ] ) ) {
						$label = wc_attribute_label( ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product_attributes[ str_replace( 'attribute_', '', $name ) ]['name'] : str_replace( 'attribute_', '', $name ) ) );
					} else {
						$label = $name;
					}
				}
				$item_data[] = array(
					'key'	 => $label,
					'value'	 => $value,
				);
			} // End foreach().
		} // End if().

		// Filter item data to allow 3rd parties to add more to the array.
		$item_data = apply_filters( 'tinv_wishlist_get_item_data', $item_data, $product );

		// Format item data ready to display.
		foreach ( $item_data as $key => $data ) {
			// Set hidden to true to not display meta on cart.
			if ( ! empty( $data['hidden'] ) ) {
				unset( $item_data[ $key ] );
				continue;
			}
			$item_data[ $key ]['key']		 = ! empty( $data['key'] ) ? $data['key'] : $data['name'];
			$item_data[ $key ]['display']	 = ! empty( $data['display'] ) ? $data['display'] : $data['value'];
		}

		// Output flat or in list format.
		if ( 0 < count( $item_data ) ) {
			ob_start();
			if ( $flat ) {
				foreach ( $item_data as $data ) {
					echo esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['display'] ) . '<br>';
				}
			} else {
				tinv_wishlist_template( 'ti-wishlist-item-data.php', array( 'item_data' => $item_data ) );
			}
			return ob_get_clean();
		}

		return '';
	}
} // End if().

if ( ! function_exists( 'tinv_wishlist_get' ) ) {

	/**
	 * Return Wishlist by id or share key
	 *
	 * @param mixed   $id Integer wishlist ID, or Share Key wishlist.
	 * @param boolean $toend Switches to the extract the default or guest wishlist.
	 *
	 * @return array
	 */
	function tinv_wishlist_get( $id = '', $toend = true ) {
		$wl			 = new TInvWL_Wishlist();
		$wishlist	 = null;
		if ( empty( $id ) ) {
			$id = get_query_var( 'tinvwlID', null );
		}

		if ( ! empty( $id ) ) {
			if ( is_integer( $id ) ) {
				$wishlist = $wl->get_by_id( $id );
			}
			if ( empty( $wishlist ) ) {
				$wishlist = $wl->get_by_share_key( $id );
			}

			if ( is_array( $wishlist ) ) {
				$wishlist['is_owner'] = false;
				if ( is_user_logged_in() ) {
					$wishlist['is_owner'] = get_current_user_id() == $wishlist['author']; // WPCS: loose comparison ok.
				} else {
					$wishlist['is_owner'] = $wl->get_sharekey() === $wishlist['share_key']; // WPCS: loose comparison ok.
				}
			}
		} elseif ( is_user_logged_in() && $toend ) {
			$wishlist = $wl->add_user_default();

			$wishlist['is_owner'] = true;
		} elseif ( $toend ) {
			$wishlist				 = $wl->add_sharekey_default();
			$wishlist['is_owner']	 = $wl->get_sharekey() === $wishlist['share_key'];
		}

		return $wishlist;
	}
} // End if().

if ( ! function_exists( 'tinv_url_wishlist_default' ) ) {

	/**
	 * Return the default wishlist url
	 *
	 * @return string
	 */
	function tinv_url_wishlist_default() {
		$page = apply_filters( 'wpml_object_id', tinv_get_option( 'page', 'wishlist' ), 'page', true ); // @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
		if ( empty( $page ) ) {
			return '';
		}
		$link = get_permalink( $page );
		return $link;
	}
}

if ( ! function_exists( 'tinv_url_wishlist_by_key' ) ) {

	/**
	 * Return the wishlist url by share key
	 *
	 * @param string  $share_key Share Key wishlist.
	 * @param integer $paged Page.
	 *
	 * @return string
	 */
	function tinv_url_wishlist_by_key( $share_key, $paged = 1 ) {
		$paged	 = absint( $paged );
		$paged	 = 1 < $paged ? $paged : 1;
		$link	 = tinv_url_wishlist_default();
		if ( empty( $link ) || empty( $share_key ) ) {
			return $link;
		}

		if ( get_option( 'permalink_structure' ) ) {
			$suffix = '';
			if ( preg_match( '/([^\?]+)\?*?(.*)/i', $link, $_link ) ) {
				$link	 = $_link[1];
				$suffix	 = $_link[2];
			}
			if ( ! preg_match( '/\/$/', $link ) ) {
				$link .= '/';
			}
			$link .= $share_key . '/' . $suffix;
		} else {
			$link = add_query_arg( 'tinvwlID', $share_key, $link );
		}

		if ( 1 < $paged ) {
			$link = add_query_arg( 'paged', $paged, $link );
		}
		return $link;
	}
} // End if().

if ( ! function_exists( 'tinv_url_wishlist' ) ) {

	/**
	 * Return the wishlist url by id or share key
	 *
	 * @param mixed   $id Integer wishlist ID, or Share Key wishlist.
	 * @param integer $paged Page.
	 * @param boolean $full Return full url or shroted url for logged in user.
	 *
	 * @return string
	 */
	function tinv_url_wishlist( $id = '', $paged = 1, $full = true ) {
		$share_key = $id;
		if ( ! ( is_string( $id ) && preg_match( '/^[A-Fa-f0-9]{6}$/', $id ) ) ) {
			$wishlist	 = tinv_wishlist_get( $id, false );
			$share_key	 = $wishlist['share_key'];
		}
		return tinv_url_wishlist_by_key( $share_key, $paged );
	}
}

if ( ! function_exists( 'tinv_wishlist_status' ) ) {

	/**
	 * Check status free or premium plugin and disable free
	 *
	 * @global string $status
	 * @global string $page
	 * @global string $s
	 *
	 * @param string $transient Plugin transient name.
	 *
	 * @return string
	 */
	function tinv_wishlist_status( $transient ) {
		if ( TINVWL_LOAD_FREE === $transient ) {
			TInvWL_PluginExtend::deactivate_self( TINVWL_LOAD_FREE );
			return 'plugins.php';
		}
		if ( TINVWL_LOAD_PREMIUM === $transient ) {
			if ( is_plugin_active( TINVWL_LOAD_FREE ) ) {
				TInvWL_PluginExtend::deactivate_self( TINVWL_LOAD_FREE );
				if ( ! function_exists( 'wp_create_nonce' ) ) {
					return 'plugins.php';
				}

				global $status, $page, $s;
				$redirect	 = 'plugins.php?';
				$redirect .= http_build_query( array(
					'action'		 => 'activate',
					'plugin'		 => $transient,
					'plugin_status'	 => $status,
					'paged'			 => $page,
					's'				 => $s,
				) );
				$redirect	 = esc_url_raw( add_query_arg( '_wpnonce', wp_create_nonce( 'activate-plugin_' . $transient ), $redirect ) );
				return $redirect;
			}
		}
		return false;
	}
} // End if().

if ( ! function_exists( 'tinvwl_body_classes' ) ) {

	/**
	 * Add custom class
	 *
	 * @param array $classes Current classes.
	 *
	 * @return array
	 */
	function tinvwl_body_classes( $classes ) {
		if ( tinv_get_option( 'style', 'customstyle' ) ) {
			$classes[] = 'tinvwl-theme-style';
		} else {
			$classes[] = 'tinvwl-custom-style';
		}

		return $classes;
	}

	add_filter( 'body_class', 'tinvwl_body_classes' );
}

if ( ! function_exists( 'tinvwl_shortcode_addtowishlist' ) ) {

	/**
	 * Shortcode Add To Wishlist
	 *
	 * @param array $atts Array parameter from shortcode.
	 *
	 * @return string
	 */
	function tinvwl_shortcode_addtowishlist( $atts = array() ) {
		$class = TInvWL_Public_AddToWishlist::instance();
		return $class->shortcode( $atts );
	}

	add_shortcode( 'ti_wishlists_addtowishlist', 'tinvwl_shortcode_addtowishlist' );
}

if ( ! function_exists( 'tinvwl_shortcode_view' ) ) {

	/**
	 * Shortcode view Wishlist
	 *
	 * @param array $atts Array parameter from shortcode.
	 *
	 * @return string
	 */
	function tinvwl_shortcode_view( $atts = array() ) {
		$class = TInvWL_Public_Wishlist_View::instance();
		return $class->shortcode( $atts );
	}

	add_shortcode( 'ti_wishlistsview', 'tinvwl_shortcode_view' );
}

if ( ! function_exists( 'tinvwl_shortcode_products_counter' ) ) {

	/**
	 * Shortcode view Wishlist
	 *
	 * @param array $atts Array parameter from shortcode.
	 *
	 * @return string
	 */
	function tinvwl_shortcode_products_counter( $atts = array() ) {
		$class = TInvWL_Public_TopWishlist::instance();
		return $class->shortcode( $atts );
	}

	add_shortcode( 'ti_wishlist_products_counter', 'tinvwl_shortcode_products_counter' );
}

if ( ! function_exists( 'tinvwl_view_addto_html' ) ) {

	/**
	 * Show button Add to Wishlsit
	 */
	function tinvwl_view_addto_html() {
		$class = TInvWL_Public_AddToWishlist::instance();
		$class->htmloutput();
	}
}

if ( ! function_exists( 'tinvwl_view_addto_htmlout' ) ) {

	/**
	 * Show button Add to Wishlsit, if product is not purchasable
	 */
	function tinvwl_view_addto_htmlout() {
		$class = TInvWL_Public_AddToWishlist::instance();
		$class->htmloutput_out();
	}
}

if ( ! function_exists( 'tinvwl_view_addto_htmlloop' ) ) {

	/**
	 * Show button Add to Wishlsit, in loop
	 */
	function tinvwl_view_addto_htmlloop() {
		$class = TInvWL_Public_AddToWishlist::instance();
		$class->htmloutput_loop();
	}
}

if ( ! function_exists( 'tinvwl_clean_url' ) ) {

	/**
	 * Clear esc_url to original
	 *
	 * @param string $good_protocol_url Cleared URL.
	 * @param string $original_url Original URL.
	 *
	 * @return string
	 */
	function tinvwl_clean_url( $good_protocol_url, $original_url ) {
		return $original_url;
	}
}

if ( ! function_exists( 'tinvwl_add_to_cart_need_redirect' ) ) {

	/**
	 * Check if the product is third-party, or has another link added to the cart then redirect to the product page.
	 *
	 * @param boolean     $redirect Default value to redirect.
	 * @param \WC_Product $product Product data.
	 * @param string      $redirect_url Current url for redirect.
	 *
	 * @return boolean
	 */
	function tinvwl_add_to_cart_need_redirect( $redirect, $product, $redirect_url ) {
		if ( $redirect ) {
			return true;
		}
		if ( 'external' === ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->product_type : $product->get_type() ) ) {
			return true;
		}

		$need_url_data	 = array_filter( array_merge( array(
			'variation_id'	 => ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->variation_id : ( $product->is_type( 'variation' ) ? $product->get_id() : 0 ) ),
			'add-to-cart'	 => ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->get_id() : ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() ) ),
		), array_map( 'urlencode', ( version_compare( WC_VERSION, '3.0.0', '<' ) ? ( is_array( $product->variation_data ) ? $product->variation_data : array() ) : array() ) ) ) );
		$need_url		 = apply_filters( 'woocommerce_product_add_to_cart_url', remove_query_arg( 'added-to-cart', add_query_arg( $need_url_data ) ), $product );
		$need_url_full	 = apply_filters( 'woocommerce_product_add_to_cart_url', remove_query_arg( 'added-to-cart', add_query_arg( $need_url_data, $product->get_permalink() ) ), $product );
		add_filter( 'clean_url', 'tinvwl_clean_url', 10, 2 );
		$_redirect_url	 = apply_filters( 'tinvwl_product_add_to_cart_redirect_url', $product->add_to_cart_url(), $product );
		remove_filter( 'clean_url', 'tinvwl_clean_url', 10 );
		if ( $_redirect_url !== $need_url && $_redirect_url !== $need_url_full ) {
			return true;
		}
		return $redirect;
	}

	add_filter( 'tinvwl_product_add_to_cart_need_redirect', 'tinvwl_add_to_cart_need_redirect', 10, 3 );
} // End if().

if ( ! function_exists( 'tinvwl_meta_validate_cart_add' ) ) {

	/**
	 * Checks the ability to add a product
	 *
	 * @param boolean     $redirect Default value to redirect.
	 * @param \WC_Product $product Product data.
	 * @param string      $redirect_url Current url for redirect.
	 * @param array       $wl_product Wishlist Product.
	 *
	 * @return boolean
	 */
	function tinvwl_meta_validate_cart_add( $redirect, $product, $redirect_url, $wl_product ) {
		if ( $redirect && array_key_exists( 'meta', $wl_product ) && ! empty( $wl_product['meta'] ) ) {

			TInvWL_Public_Cart::prepare_post( $wl_product );

			$wl_product			 = apply_filters( 'tinvwl_addproduct_tocart', $wl_product );
			$product_id			 = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $wl_product['product_id'] ) );
			$quantity			 = empty( $wl_quantity ) ? 1 : wc_stock_amount( $wl_quantity );
			$variation_id		 = $wl_product['variation_id'];
			$variations			 = ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->variation_data : ( $product->is_type( 'variation' ) ? wc_get_product_variation_attributes( $product->get_id() ) : array() ) );
			$passed_validation	 = $product->is_purchasable() && ( $product->is_in_stock() || $product->backorders_allowed() ) && 'external' !== ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->product_type : $product->get_type() );
			ob_start();
			wc_clear_notices();
			$passed_validation	 = apply_filters( 'woocommerce_add_to_cart_validation', $passed_validation, $product_id, $quantity, $variation_id, $variations );
			$wc_errors			 = wc_get_notices( 'error' );
			$wc_output			 = ob_get_clean();
			if ( $passed_validation && empty( $wc_errors ) && empty( $wc_output ) ) {
				$redirect = false;
			}

			TInvWL_Public_Cart::unprepare_post();
		}
		return $redirect;
	}

	add_filter( 'tinvwl_product_add_to_cart_need_redirect', 'tinvwl_meta_validate_cart_add', 90, 4 );
} // End if().

if ( ! function_exists( 'tinv_wishlist_print_meta' ) ) {

	/**
	 * Print meta data for wishlist form
	 *
	 * @param array   $meta Meta Array.
	 * @param boolean $flat Return text or template.
	 *
	 * @return string
	 */
	function tinv_wishlist_print_meta( $meta = array(), $flat = false ) {
		if ( ! is_array( $meta ) ) {
			$meta = array();
		}
		$product_id		 = $variation_id	 = 0;
		if ( array_key_exists( 'product_id', $meta ) ) {
			$product_id = $meta['product_id'];
		}
		if ( array_key_exists( 'variation_id', $meta ) ) {
			$variation_id = $meta['variation_id'];
		}
		foreach ( array( 'add-to-cart', 'product_id', 'variation_id', 'quantity' ) as $field ) {
			if ( array_key_exists( $field, $meta ) ) {
				unset( $meta[ $field ] );
			}
		}
		$meta = array_filter( $meta );
		if ( empty( $meta ) ) {
			return '';
		}
		$item_data = array();
		foreach ( $meta as $key => $value ) {
			if ( ! preg_match( '/^\_/', $key ) ) {
				$item_data[ $key ] = array(
					'key'		 => $key,
					'display'	 => $value,
				);
			}
		}
		$item_data = apply_filters( 'tinvwl_wishlist_item_meta_post', $item_data, $product_id, $variation_id );
		foreach ( $item_data as $key => $data ) {
			if ( is_object( $data['display'] ) || is_array( $data['display'] ) ) {
				$item_data[ $key ]['display'] = json_encode( $data['display'] );
			}
		}
		ob_start();
		if ( $flat ) {
			foreach ( $item_data as $data ) {
				echo esc_html( $data['key'] ) . ': ' . wp_kses_post( $data['display'] ) . '<br>';
			}
		} else {
			tinv_wishlist_template( 'ti-wishlist-item-data.php', array( 'item_data' => $item_data ) );
		}
		return apply_filters( 'tinvwl_wishlist_item_meta_wishlist', ob_get_clean() );
	}
} // End if().

if ( ! function_exists( 'tinv_wishlistmeta' ) ) {

	/**
	 * Show new meta data
	 *
	 * @param string      $meta Print meta.
	 * @param array       $wl_product Wishlist product.
	 * @param \WC_Product $product Woocommerce product.
	 *
	 * @return string
	 */
	function tinv_wishlistmeta( $meta = '', $wl_product, $product ) {
		if ( empty( $meta ) ) {
			if ( array_key_exists( 'meta', $wl_product ) ) {
				$meta = apply_filters( 'tinvwl_wishlist_item_meta_wishlist_output', tinv_wishlist_print_meta( $wl_product['meta'] ), $wl_product, $product );
			}
		}
		return $meta;
	}

	add_filter( 'tinvwl_wishlist_item_meta_data', 'tinv_wishlistmeta', 10, 3 );
}

if ( ! function_exists( 'tinvwl_add_to_cart_item_meta_post' ) ) {

	/**
	 * Save post data to cart item
	 *
	 * @param array  $cart_item_data Array with cart imet information.
	 * @param string $cart_item_key Cart item key.
	 *
	 * @return array
	 */
	function tinvwl_add_to_cart_item_meta_post( $cart_item_data, $cart_item_key ) {
		$postdata = $_POST; // @codingStandardsIgnoreLine WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		$postdata = apply_filters( 'tinvwl_product_prepare_meta', $postdata );
		if ( array_key_exists( 'variation_id', $postdata ) && ! empty( $postdata['variation_id'] ) ) {
			foreach ( $postdata as $key => $field ) {
				if ( preg_match( '/^attribute\_/', $key ) ) {
					unset( $postdata[ $key ] );
				}
			}
		}
		foreach ( array( 'add-to-cart', 'product_id', 'variation_id', 'quantity' ) as $field ) {
			if ( array_key_exists( $field, $postdata ) ) {
				unset( $postdata[ $field ] );
			}
		}
		$postdata = array_filter( $postdata );
		if ( empty( $postdata ) ) {
			return $cart_item_data;
		}
		ksort( $postdata );

		$cart_item_data['tinvwl_formdata'] = $postdata;
		return $cart_item_data;
	}

	add_action( 'woocommerce_add_cart_item', 'tinvwl_add_to_cart_item_meta_post', 10, 2 );
} // End if().

if ( ! function_exists( 'tinvwl_rating_notice_template' ) ) {

	/**
	 * Show admin notice.
	 *
	 * @param string $output String.
	 * @param string $key Unique notification key.
	 * @param string $message Text message.
	 * @return string
	 */
	function tinvwl_rating_notice_template( $output, $key, $message ) {

		TInvWL_View::view( 'notice-rating', array(
			'name'		 => 'rating',
			'message'	 => $message,
			'key'		 => $key,
		) );

		return '';
	}

	add_filter( 'tinv_notice_rating', 'tinvwl_rating_notice_template', 10, 3 );
}

if ( ! function_exists( 'tinvwl_rating_notice_hide' ) ) {

	/**
	 * Action for disable notice
	 */
	function tinvwl_rating_notice_hide() {
		$data = filter_input( INPUT_GET, 'ti-redirect' );
		if ( $data ) {
			wp_redirect( 'https://wordpress.org/support/plugin/ti-woocommerce-wishlist/reviews/#new-post' );
		}
	}

	add_action( 'tinv_notice_hide_rating', 'tinvwl_rating_notice_hide' );
}

if ( ! function_exists( 'tinvwl_rating_notice_trigger_30' ) ) {

	/**
	 * Trigger for reset notice
	 *
	 * @return boolean
	 */
	function tinvwl_rating_notice_trigger_30() {
		$tw			 = new TInvWL_Wishlist();
		$wishlist	 = $tw->get( array(
			'count'		 => 1,
			'order_by'	 => 'date',
		) );
		$wishlist	 = array_shift( $wishlist );
		if ( empty( $wishlist ) ) {
			return false;
		}
		$date	 = $wishlist['date'];
		$date	 = mysql2date( 'G', $date );
		$date	 = floor( ( time() - $date ) / DAY_IN_SECONDS );
		$step	 = floor( $date / 30 );
		if ( 0 >= $step ) {
			return false;
		}

		return $step;
	}
}

if ( ! function_exists( 'tinvwl_set_utm' ) ) {

	/**
	 * Set UTM sources.
	 */
	function tinvwl_set_utm() {

		// Set a source.
		$source = get_option( TINVWL_PREFIX . '_utm_source' );
		if ( ! $source ) {
			$source = defined( 'TINVWL_PARTNER' ) ? TINVWL_PARTNER : 'wordpress_org';
			update_option( TINVWL_PREFIX . '_utm_source', $source );
		}

		define( 'TINVWL_UTM_SOURCE', $source );

		// Set a medium.
		$medium = get_option( TINVWL_PREFIX . '_utm_medium' );
		if ( ! $medium ) {
			$medium = defined( 'TINVWL_PARTNER' ) ? 'integration' : 'organic';
			update_option( TINVWL_PREFIX . '_utm_medium', $medium );
		}

		define( 'TINVWL_UTM_MEDIUM', $medium );

		// Set a campaign.
		$campaign = get_option( TINVWL_PREFIX . '_utm_campaign' );
		if ( ! $campaign ) {
			$campaign = defined( 'TINVWL_PARTNER' ) ? ( defined( 'TINVWL_CAMPAIGN' ) ? TINVWL_CAMPAIGN : TINVWL_PARTNER ) : 'organic';
			update_option( TINVWL_PREFIX . '_utm_campaign', $campaign );
		}

		define( 'TINVWL_UTM_CAMPAIGN', $campaign );
	}
} // End if().
