<?php
/**
 * Wishlist shortcode
 *
 * @since             1.0.0
 * @package           TInvWishlist\Public
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Wishlist shortcode
 */
class TInvWL_Public_Wishlist_View {

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	private $_n;

	/**
	 * List per page
	 *
	 * @var integer
	 */
	private $lists_per_page;

	/**
	 * Current wishlist
	 *
	 * @var array
	 */
	private $curent_wishlist;
	/**
	 * This class
	 *
	 * @var \TInvWL_Public_Wishlist_View
	 */
	protected static $_instance = null;

	/**
	 * Get this class object
	 *
	 * @param string $plugin_name Plugin name.
	 * @return \TInvWL_Public_Wishlist_View
	 */
	public static function instance( $plugin_name = TINVWL_PREFIX ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $plugin_name );
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @param string $plugin_name Plugin name.
	 */
	function __construct( $plugin_name ) {
		$this->_n	 = $plugin_name;
		$this->define_hooks();
	}

	/**
	 * Defined shortcode and hooks
	 */
	function define_hooks() {
		add_action( 'wp', array( $this, 'wishlist_action' ), 0 );

		add_action( 'tinvwl_before_wishlist', array( $this, 'wishlist_header' ) );

		add_action( 'tinvwl_after_wishlist', array( 'TInvWL_Public_Wishlist_Social', 'init' ) );

		add_action( 'tinvwl_wishlist_item_action_add_to_cart', array( $this, 'product_allow_add_to_cart' ), 10, 3 );
		add_filter( 'tinvwl_wishlist_item_add_to_cart', array( $this, 'external_text' ), 10, 3 );
		add_action( 'tinvwl_after_wishlist_table', array( $this, 'get_per_page' ) );

		TInvWL_Public_Wishlist_Buttons::init( $this->_n );
	}

	/**
	 * Change Text for external product
	 *
	 * @param string $text Text for button add to cart.
	 * @param array  $wl_product Wishlist Product.
	 * @param object $product Product.
	 * @return string
	 */
	function external_text( $text, $wl_product, $product ) {
		if ( 'external' === ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->product_type : $product->get_type() ) ) {
			return $product->single_add_to_cart_text();
		}
		return $text;
	}

	/**
	 * Get current wishlist
	 *
	 * @return array
	 */
	function get_current_wishlist() {
		if ( empty( $this->curent_wishlist ) ) {
			$this->curent_wishlist = apply_filters( 'tinvwl_get_current_wishlist', tinv_wishlist_get() );
		}
		return $this->curent_wishlist;
	}

	/**
	 * Get current products from wishlist
	 *
	 * @param array   $wishlist Wishlist object.
	 * @param boolean $external Get woocommerce product info.
	 * @param integer $lists_per_page Count per page.
	 * @return array
	 */
	function get_current_products( $wishlist = null, $external = true, $lists_per_page = 10 ) {
		if ( empty( $wishlist ) ) {
			$wishlist = $this->get_current_wishlist();
		}
		$wlp = null;
		if ( 0 === $wishlist['ID'] ) {
			$wlp = TInvWL_Product_Local::instance();
		} else {
			$wlp = new TInvWL_Product( $wishlist );
		}
		if ( empty( $wlp ) ) {
			return array();
		}

		$paged	 = get_query_var( 'paged', 1 );
		$paged	 = 1 < $paged ? $paged : 1;

		$product_data	 = array(
			'count'		 => $lists_per_page,
			'offset'	 => $lists_per_page * ($paged - 1),
			'external'	 => $external,
			'order_by'	 => 'date',
			'order'		 => 'DESC',
		);
		$pages			 = ceil( count( $wlp->get_wishlist( array(
			'count'		 => 9999999,
			'external'	 => false,
		) ) ) / absint( $lists_per_page ) );

		if ( 1 < $paged ) {
			add_action( 'tinvwl_pagenation_wishlist', array( $this, 'page_prev' ) );
		}
		if ( $pages > $paged ) {
			add_action( 'tinvwl_pagenation_wishlist', array( $this, 'page_next' ) );
		}

		$product_data	 = apply_filters( 'tinvwl_before_get_current_product', $product_data );
		$products		 = $wlp->get_wishlist( $product_data );
		$products		 = apply_filters( 'tinvwl_after_get_current_product', $products );

		return $products;
	}

	/**
	 * Allow show button add to cart
	 *
	 * @param boolean $allow Settings flag.
	 * @param array   $wlproduct Wishlist Product.
	 * @param object  $product Product.
	 * @return boolean
	 */
	function product_allow_add_to_cart( $allow, $wlproduct, $product ) {
		if ( ! $allow ) {
			return false;
		}
		return ( $product->is_purchasable() || 'external' === ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->product_type : $product->get_type() ) ) && ( $product->is_in_stock() || $product->backorders_allowed() );
	}

	/**
	 * Basic validation actions
	 *
	 * @return boolean
	 */
	function wishlist_action() {
		if ( is_page( apply_filters( 'wpml_object_id', tinv_get_option( 'page', 'wishlist' ), 'page', true ) ) ) {
			$wishlist = $this->get_current_wishlist();
			if ( empty( $wishlist ) ) {
				return false;
			}

			$is_owner	 = is_user_logged_in() ? ( get_current_user_id() === $wishlist['author'] ) : $wishlist['is_owner'];
			$nonce		 = filter_input( INPUT_POST, 'wishlist_nonce' );
			if ( $nonce && wp_verify_nonce( $nonce, 'tinvwl_wishlist_owner' ) && $is_owner ) {
				do_action( 'tinvwl_before_action_owner', $wishlist );
				$this->wishlist_actions( $wishlist, true );
				do_action( 'tinvwl_after_action_owner', $wishlist );
			}
			if ( $nonce && wp_verify_nonce( $nonce, 'tinvwl_wishlist_user' ) && ! $is_owner ) {
				do_action( 'tinvwl_before_action_user', $wishlist );
				$this->wishlist_actions( $wishlist, false );
				do_action( 'tinvwl_after_action_user', $wishlist );
			}
		}
	}

	/**
	 * Basic actions
	 *
	 * @param array   $wishlist Wishlist object.
	 * @param boolean $owner Is Owner.
	 * @return boolean
	 */
	function wishlist_actions( $wishlist, $owner = false ) {
		$post = filter_input_array( INPUT_POST, array(
			'wishlist_pr'		 => array(
				'filter' => FILTER_VALIDATE_INT,
				'flags'	 => FILTER_FORCE_ARRAY,
			),
			'wishlist_qty'		 => array(
				'filter'	 => FILTER_VALIDATE_INT,
				'flags'		 => FILTER_FORCE_ARRAY,
				'options'	 => array( 'min_range' => 0, 'default' => 1 ),
			),
			'tinvwl-add-to-cart' => FILTER_VALIDATE_INT,
			'tinvwl-remove'		 => FILTER_VALIDATE_INT,
			'tinvwl-action'		 => FILTER_SANITIZE_STRING,
		) );

		if ( ! empty( $post['tinvwl-add-to-cart'] ) ) {
			$product	 = $post['tinvwl-add-to-cart'];
			$quantity	 = array_key_exists( $product, (array) $post['wishlist_qty'] ) ? $post['wishlist_qty'][ $product ] : 1;
			return $this->button_addtocart( $wishlist, $product, $quantity, $owner );
		}
		if ( ! empty( $post['tinvwl-remove'] ) ) {
			if ( ! $wishlist['is_owner'] ) {
				return false;
			}
			$product	 = $post['tinvwl-remove'];
			if ( 0 === $wishlist['ID'] ) {
				$wlp = TInvWL_Product_Local::instance();
			} else {
				$wlp = new TInvWL_Product( $wishlist );
			}
			if ( empty( $wlp ) ) {
				return false;
			}
			$product_data	 = $wlp->get_wishlist( array( 'ID' => $product ) );
			$product_data	 = array_shift( $product_data );
			if ( empty( $product_data ) ) {
				return false;
			}
			$title = sprintf( __( '&ldquo;%s&rdquo;', 'ti-woocommerce-wishlist' ), $product_data['data']->get_title() );
			if ( $wlp->remove( $product ) ) {
				wc_add_notice( sprintf( __( '%s has been removed from wishlist.', 'ti-woocommerce-wishlist' ), $title ) );
			} else {
				wc_add_notice( sprintf( __( '%s has not been removed from wishlist.', 'ti-woocommerce-wishlist' ), $title ), 'error' );
			}
			return true;
		}
		do_action( 'tinvwl_action_' . $post['tinvwl-action'], $wishlist, $post['wishlist_pr'], $post['wishlist_qty'], $owner ); // @codingStandardsIgnoreLine WordPress.NamingConventions.ValidHookName.UseUnderscores
	}

	/**
	 * Apply action add to cart
	 *
	 * @param array   $wishlist Wishlist object.
	 * @param integer $id Product id in wishlist.
	 * @param integer $quantity Product quantity.
	 * @param boolean $owner Is Owner.
	 * @return boolean
	 */
	function button_addtocart( $wishlist, $id, $quantity = 1, $owner = false ) {
		$id			 = absint( $id );
		$quantity	 = absint( $quantity );
		if ( empty( $id ) || empty( $quantity ) ) {
			return false;
		}

		$wlp = null;
		if ( 0 === $wishlist['ID'] ) {
			$wlp = TInvWL_Product_Local::instance();
		} else {
			$wlp = new TInvWL_Product( $wishlist );
		}
		if ( empty( $wlp ) ) {
			return false;
		}

		$product = $wlp->get_wishlist( array( 'ID' => $id ) );
		$product = array_shift( $product );
		if ( empty( $product ) || empty( $product['data'] ) ) {
			return false;
		}

		add_filter( 'clean_url', 'tinvwl_clean_url', 10, 2 );
		$redirect_url = $product['data']->add_to_cart_url();
		remove_filter( 'clean_url', 'tinvwl_clean_url', 10 );

		if ( apply_filters( 'tinvwl_product_add_to_cart_need_redirect', false, $product['data'], $redirect_url, $product ) ) {
			wp_redirect( apply_filters( 'tinvwl_product_add_to_cart_redirect_url', $redirect_url, $product['data'], $product ) ); // @codingStandardsIgnoreLine WordPress.VIP.RestrictedFunctions.wp_redirect
			die();
		} elseif ( apply_filters( 'tinvwl_allow_addtocart_in_wishlist', true, $wishlist, $owner ) ) {
			$add = TInvWL_Public_Cart::add( $wishlist, $id, $quantity );
			if ( $add ) {
				wc_add_to_cart_message( $add, true );
				return true;
			}
		}
		return false;
	}

	/**
	 * Output page
	 *
	 * @param array $atts Array parameter for shortcode.
	 * @return boolean
	 */
	function htmloutput( $atts ) {
		$wishlist = $this->get_current_wishlist();

		if ( empty( $wishlist ) ) {
			return $this->wishlist_null();
		}

		if ( 'private' === $wishlist['status'] && ! $wishlist['is_owner'] ) {
			return $this->wishlist_null();
		}
		if ( 'default' !== $wishlist['type'] && ! tinv_get_option( 'general', 'multi' ) ) {
			if ( $wishlist['is_owner'] ) {
				printf( '<p><a href="%s">%s</p><script type="text/javascript">window.location.href="%s"</script>', esc_attr( tinv_url_wishlist_default() ), esc_html__( 'Return to Wishlist', 'ti-woocommerce-wishlist' ), esc_attr( tinv_url_wishlist_default() ) );
				return false;
			} else {
				return $this->wishlist_null();
			}
		}

		$products = $this->get_current_products( $wishlist, true, absint( $atts['lists_per_page'] ) );

		$this->lists_per_page = $atts['lists_per_page'];

		foreach ( $products as $key => $product ) {
			if ( ! $product['data'] ) {
				unset( $products[ $key ] );
			}
		}

		if ( empty( $products ) ) {
			return $this->wishlist_empty( $products, $wishlist );
		}
		$data = array(
			'products'			 => $products,
			'wishlist'			 => $wishlist,
			'wishlist_table'	 => tinv_get_option( 'table' ),
			'wishlist_table_row' => tinv_get_option( 'product_table' ),
		);

		if ( $wishlist['is_owner'] ) {
			tinv_wishlist_template( 'ti-wishlist.php', $data );
		} else {
			tinv_wishlist_template( 'ti-wishlist-user.php', $data );
		}
	}

	/**
	 * Not Found Wishlist
	 *
	 * @param array $wishlist Wishlist object.
	 */
	function wishlist_null( $wishlist = array() ) {
		$data = array(
			'wishlist' => $wishlist,
		);
		tinv_wishlist_template( 'ti-wishlist-null.php', $data );
	}

	/**
	 * Empty Wishlist
	 *
	 * @param array $products Products wishlist.
	 * @param array $wishlist Wishlist object.
	 */
	function wishlist_empty( $products = array(), $wishlist = array() ) {
		$data = array(
			'products'		 => $products,
			'wishlist'		 => $wishlist,
			'wishlist_table' => tinv_get_option( 'table' ),
		);
		tinv_wishlist_template( 'ti-wishlist-empty.php', $data );
	}

	/**
	 * Header Wishlist
	 *
	 * @param array $wishlist Wishlist object.
	 */
	function wishlist_header( $wishlist ) {

		$data = array(
			'wishlist'	 => $wishlist,
		);
		tinv_wishlist_template( 'ti-wishlist-header.php', $data );
	}

	/**
	 * Prev page button
	 */
	function page_prev() {
		$paged	 = get_query_var( 'paged', 1 );
		$paged	 = 1 < $paged ? $paged - 1 : 0;
		$this->page( $paged, sprintf( '<i class="fa fa-chevron-left"></i>%s', __( 'Previous Page', 'ti-woocommerce-wishlist' ) ), array( 'class' => 'button tinv-prev' ) );
	}

	/**
	 * Next page button
	 */
	function page_next() {
		$paged	 = get_query_var( 'paged', 1 );
		$paged	 = 1 < $paged ? $paged + 1 : 2;
		$this->page( $paged, sprintf( '%s<i class="fa fa-chevron-right"></i>', __( 'Next Page', 'ti-woocommerce-wishlist' ) ), array( 'class' => 'button tinv-next' ) );
	}

	/**
	 * Page button
	 *
	 * @param integer $paged Index page.
	 * @param string  $text Text button.
	 * @param style   $style Style attribute.
	 */
	function page( $paged, $text, $style = array() ) {
		$paged		 = absint( $paged );
		$wishlist	 = tinv_wishlist_get();
		$link		 = tinv_url_wishlist( $wishlist['share_key'], $paged, true );
		if ( is_array( $style ) ) {
			$style = TInvWL_Form::__atrtostr( $style );
		}
		printf( '<a href="%s" %s>%s</a>', esc_url( $link ), $style, $text ); // WPCS: xss ok.
	}

	/**
	 * Shortcode basic function
	 *
	 * @param array $atts Array parameter from shortcode.
	 * @return string
	 */
	function shortcode( $atts = array() ) {
		$default = array(
			'lists_per_page' => 10,
		);
		$atts	 = shortcode_atts( $default, $atts );

		ob_start();
		$this->htmloutput( $atts );
		return ob_get_clean();
	}

	/**
	 * Get per page items for buttons
	 */
	function get_per_page() {
		if ( ! empty( $this->lists_per_page ) ) {
			echo TInvWL_Form::_text( array( // WPCS: xss ok.
				'type' => 'hidden',
				'name' => 'lists_per_page',
			), $this->lists_per_page);
		}
	}
}
