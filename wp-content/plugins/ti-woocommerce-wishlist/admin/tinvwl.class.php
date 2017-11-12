<?php
/**
 * Admin pages class
 *
 * @since             1.0.0
 * @package           TInvWishlist\Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Admin pages class
 */
class TInvWL_Admin_TInvWL extends TInvWL_Admin_Base {

	/**
	 * Constructor
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $version Plugin version.
	 */
	function __construct( $plugin_name, $version ) {
		$this->_n	 = $plugin_name;
		$this->_v	 = $version;
	}

	/**
	 * Load functions.
	 * Create Wishlist and Product class.
	 * Load settings classes.
	 */
	function load_function() {
		TII18n();
		TInvWL_Notice::instance()->add_notice( 'rating', '<p>' . __( 'Woo-Ha! It has been a month since the first wishlist was created with <strong>WooCommerce WishList plugin</strong>!', 'ti-woocommerce-wishlist' ) . '</p><p>' . __( 'What do you think about our plugin?', 'ti-woocommerce-wishlist' ) . '</p><p>' . __( 'Share your love with us.', 'ti-woocommerce-wishlist' ) . '</p>' )->add_trigger( 'admin_init', 'tinvwl_rating_notice_trigger_30' );
		$this->load_settings();

		$this->define_hooks();
	}

	/**
	 * Load settings classes.
	 *
	 * @return boolean
	 */
	function load_settings() {
		$dir = TINVWL_PATH . 'admin/settings/';
		if ( ! file_exists( $dir ) || ! is_dir( $dir ) ) {
			return false;
		}
		$files = scandir( $dir );
		foreach ( $files as $value ) {
			if ( preg_match( '/\.class\.php$/i', $value ) ) {
				$file		 = preg_replace( '/\.class\.php$/i', '', $value );
				$class		 = 'TInvWL_Admin_Settings_' . ucfirst( $file );
				$settings	 = new $class( $this->_n, $this->_v );
			}
		}
		return true;
	}

	/**
	 * Define hooks
	 */
	function define_hooks() {
		add_action( 'admin_menu', array( $this, 'action_menu' ) );
		if ( 'skip' === filter_input( INPUT_GET, $this->_n . '-wizard' ) ) {
			update_option( $this->_n . '_wizard', true );
		}
		if ( ! get_option( $this->_n . '_wizard' ) ) {
			add_action( 'admin_notices', array( $this, 'wizard_run_admin_notice' ) );
		} elseif ( ! tinv_get_option( 'page', 'wishlist' ) ) {
			add_action( 'admin_notices', array( $this, 'empty_page_admin_notice' ) );
		}
		add_action( 'woocommerce_system_status_report', array( $this, 'system_report_templates' ) );

		add_action( 'switch_theme', array( $this, 'admin_notice_outdated_templates' ) );
		add_action( 'tinvwl_updated', array( $this, 'admin_notice_outdated_templates' ) );
		add_action( 'wp_ajax_' . $this->_n . '_checker_hook', array( $this, 'validation_template' ) );
		add_action( 'switch_theme', array( $this, 'clear_notice_validation_template' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_validate_template' ) );
		add_action( 'tinvwl_admin_promo_footer', array( $this, 'promo_footer' ) );
		add_action( 'tinvwl_remove_without_author_wishlist', array( $this, 'remove_empty_wishlists' ) );
		add_action( 'tinvwl_remove_without_author_wishlist', array( $this, 'remove_old_wishlists' ) );
		$this->scheduled_remove_wishlist();
	}

	/**
	 * Error notice if wizard didn't run.
	 */
	function wizard_run_admin_notice() {
		printf( '<div class="notice notice-error"><p>%1$s</p><p><a href="%2$s" class="button-primary">%3$s</a> <a href="%4$s" class="button-secondary">%5$s</a></p></div>',
			__( '<strong>Welcome to WooCommerce Wishlist Plugin<strong> – You‘re almost ready to start :)', 'ti-woocommerce-wishlist' ), // @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
			esc_url( admin_url( 'index.php?page=tinvwl-wizard' ) ),
			esc_html__( 'Run the Setup Wizard', 'ti-woocommerce-wishlist' ),
			esc_url( admin_url( 'index.php?page=' . $this->_n . '&' . $this->_n . '-wizard=skip' ) ),
			esc_html__( 'Skip Setup', 'ti-woocommerce-wishlist' )
		);
	}

	/**
	 * Error notice if wishlist page not set.
	 */
	function empty_page_admin_notice() {
		printf( '<div class="notice notice-error is-dismissible" style="position: relative;"><p>%1$s <a href="%2$s">%3$s</a>%4$s<a href="%5$s">%6$s</a></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss' ) . '</span></button></div>', // @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
			esc_html__( 'Link to Wishlists does not work!', 'ti-woocommerce-wishlist' ),
			esc_url( $this->admin_url( '' ) . '#general' ),
			esc_html__( 'Please apply the Wishlist page', 'ti-woocommerce-wishlist' ),
			esc_html__( ' or ', 'ti-woocommerce-wishlist' ),
			esc_url( admin_url( 'index.php?page=tinvwl-wizard' ) ),
			esc_html__( 'Run the Setup Wizard', 'ti-woocommerce-wishlist' )
		);
	}

	/**
	 * Creation mune and sub-menu
	 */
	function action_menu() {
		$page	 = add_menu_page( 'TI Wishlists', 'TI Wishlists', 'manage_options', $this->_n, null, TINVWL_URL . 'asset/img/icon_menu.png', 56 );
		add_action( "load-$page", array( $this, 'onload' ) );
		$menu	 = apply_filters( $this->_n . '_admin_menu', array() );
		foreach ( $menu as $item ) {
			if ( ! array_key_exists( 'page_title', $item ) ) {
				$item['page_title'] = $item['title'];
			}
			if ( ! array_key_exists( 'parent', $item ) ) {
				$item['parent'] = $this->_n;
			}
			if ( ! array_key_exists( 'capability', $item ) ) {
				$item['capability'] = 'manage_options';
			}
			$item['slug'] = implode( '-', array_filter( array( $this->_n, $item['slug'] ) ) );

			$page = add_submenu_page( $item['parent'], $item['page_title'], $item['title'], $item['capability'], $item['slug'], $item['method'] );
			add_action( "load-$page", array( $this, 'onload' ) );
		}
	}

	/**
	 * Load style and javascript
	 */
	function onload() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'admin_footer_text', array( $this, 'footer_admin' ) );
		add_filter( 'screen_options_show_screen', array( $this, 'screen_options_hide_screen' ), 10, 2 );

		add_filter( $this->_n . '_view_panelstatus', array( $this, 'status_panel' ), 9999 );
	}

	/**
	 * Load style
	 */
	function enqueue_styles() {
		wp_enqueue_style( 'gfonts', ( is_ssl() ? 'https' : 'http' ) . '://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800', '', null, 'all' );
		wp_enqueue_style( $this->_n, TINVWL_URL . 'asset/css/admin.css', array(), $this->_v, 'all' );
		wp_enqueue_style( $this->_n . '-font-awesome', TINVWL_URL . 'asset/css/font-awesome.min.css', array(), $this->_v, 'all' );
		wp_enqueue_style( $this->_n . '-form', TINVWL_URL . 'asset/css/admin-form.css', array(), $this->_v, 'all' );
	}

	/**
	 * Load javascript
	 */
	function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( $this->_n . '-bootstrap', TINVWL_URL . 'asset/js/bootstrap' . $suffix . '.js', array( 'jquery' ), $this->_v, 'all' );
		wp_register_script( $this->_n, TINVWL_URL . 'asset/js/admin' . $suffix . '.js', array( 'jquery', 'wp-color-picker' ), $this->_v, 'all' );
		wp_localize_script( $this->_n, 'tinvwl_comfirm', array(
			'text_comfirm_reset' => __( 'Are you sure you want to reset the settings?', 'ti-woocommerce-wishlist' ),
		) );
		wp_enqueue_script( $this->_n );
	}

	/**
	 * Add plugin footer copywriting
	 */
	function footer_admin() {
		do_action( 'tinvwl_admin_promo_footer' );
	}

	/**
	 * Promo in footer for wishlist
	 */
	function promo_footer() {
		echo 'Made with <i class="fa fa-heart"></i> by <a href="https://templateinvaders.com/?utm_source=' . TINVWL_UTM_SOURCE . '&utm_campaign=' . TINVWL_UTM_CAMPAIGN . '&utm_medium=' . TINVWL_UTM_MEDIUM . '&utm_content=made_by&partner=' . TINVWL_UTM_SOURCE . '">TemplateInvaders</a><br />If you like WooCommerce Wishlist Plugin please leave us a <a href="https://wordpress.org/support/plugin/ti-woocommerce-wishlist/reviews/#new-post"><span><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></span></a> rating.'; // WPCS: xss ok.
	}

	/**
	 * Create Upgrade button
	 *
	 * @param array $panel Panel Button.
	 *
	 * @return array
	 */
	function status_panel( $panel ) {
		array_unshift( $panel, sprintf( '<a class="tinvwl-btn red w-icon smaller-txt" href="%s"><i class="fa fa-star"></i><span class="tinvwl-txt">%s</span></a>', 'https://templateinvaders.com/product/ti-woocommerce-wishlist-wordpress-plugin/?utm_source=' . TINVWL_UTM_SOURCE . '&utm_campaign=' . TINVWL_UTM_CAMPAIGN . '&utm_medium=' . TINVWL_UTM_MEDIUM . '&utm_content=header_upgrade&partner=' . TINVWL_UTM_SOURCE, __( 'Upgrade to Pro', 'ti-woocommerce-wishlist' ) ) );

		return $panel;
	}

	/**
	 * Templates overriding status check.
	 *
	 * @param boolean $outdated Out date status.
	 * @return string
	 */
	function templates_status_check( $outdated = false ) {

		$found_files = array();

		$scanned_files = WC_Admin_Status::scan_template_files( TINVWL_PATH . '/templates/' );

		foreach ( $scanned_files as $file ) {
			if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
				$theme_file = get_stylesheet_directory() . '/' . $file;
			} elseif ( file_exists( get_stylesheet_directory() . '/woocommerce/' . $file ) ) {
				$theme_file = get_stylesheet_directory() . '/woocommerce/' . $file;
			} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
				$theme_file = get_template_directory() . '/' . $file;
			} elseif ( file_exists( get_template_directory() . '/woocommerce/' . $file ) ) {
				$theme_file = get_template_directory() . '/woocommerce/' . $file;
			} else {
				$theme_file = false;
			}

			if ( ! empty( $theme_file ) ) {
				$core_version	 = WC_Admin_Status::get_file_version( TINVWL_PATH . '/templates/' . $file );
				$theme_version	 = WC_Admin_Status::get_file_version( $theme_file );

				if ( $core_version && ( empty( $theme_version ) || version_compare( $theme_version, $core_version, '<' ) ) ) {
					if ( $outdated ) {
						return 'outdated';
					}
					$found_files[] = sprintf( __( '<code>%1$s</code> version <strong style="color:red">%2$s</strong> is out of date. The core version is <strong style="color:red">%3$s</strong>', 'ti-woocommerce-wishlist' ), str_replace( WP_CONTENT_DIR . '/themes/', '', $theme_file ), $theme_version ? $theme_version : '-', $core_version );
				} else {
					$found_files[] = str_replace( WP_CONTENT_DIR . '/themes/', '', $theme_file );
				}
			}
		}

		return $found_files;
	}

	/**
	 * Templates overriding status for WooCommerce Status report page.
	 */
	function system_report_templates() {

	    TInvWL_View::view( 'templates-status', array( 'found_files' => $this->templates_status_check() ) );
	}

	/**
	 * Outdated templates notice.
	 */
	function admin_notice_outdated_templates() {
		if ( 'outdated' === $this->templates_status_check( true ) ) {

			$theme = wp_get_theme();

			$html = sprintf( __( '<strong>Your theme (%1$s) contains outdated copies of some WooCommerce Wishlist Plugin template files.</strong><br> These files may need updating to ensure they are compatible with the current version of WooCommerce Wishlist Plugin.<br> You can see which files are affected from the <a href="%2$s">system status page</a>.<br> If in doubt, check with the author of the theme.', 'ti-woocommerce-wishlist' ), esc_html( $theme['Name'] ), esc_url( admin_url( 'admin.php?page=wc-status' ) ) );

			WC_Admin_Notices::add_custom_notice( 'outdated_templates', $html );
		} else {
			WC_Admin_Notices::remove_notice( 'outdated_templates' );
		}
	}

	/**
	 * Load javascript for validation templates
	 */
	function enqueue_scripts_validate_template() {
		$theme	 = wp_get_theme();
		$theme	 = $theme->get_template();
		if ( tinv_get_option( 'template_checker', 'theme' ) !== $theme ) {
			tinv_update_option( 'template_checker', '', array() );
			tinv_update_option( 'template_checker', 'theme', $theme );
			tinv_update_option( 'template_checker', 'checked', false );
			tinv_update_option( 'template_checker', 'time', 0 );
		}
		if ( tinv_get_option( 'template_checker', 'checked' ) && absint( tinv_get_option( 'template_checker', 'time' ) ) + HOUR_IN_SECONDS > time() ) {
			return;
		}
		$types	 = array_keys( wc_get_product_types() );
		foreach ( $types as $type => $type_name ) {
			if ( ! tinv_get_option( 'template_checker', 'missing_hook_' . $type ) ) {
				$data = filter_input_array( INPUT_GET, array(
					'wc-hide-notice'	 => FILTER_DEFAULT,
					'_wc_notice_nonce'	 => FILTER_DEFAULT,
				) );
				if ( 'missing_hook_' . $type === $data['wc-hide-notice'] && wp_verify_nonce( $data['_wc_notice_nonce'], 'woocommerce_hide_notices_nonce' ) ) {
					tinv_update_option( 'template_checker', 'missing_hook_' . $type, true );
				}
			}
		}
		if ( ! tinv_get_option( 'template_checker', 'hide_product_listing' ) ) {
			$data = filter_input_array( INPUT_GET, array(
				'wc-hide-notice'	 => FILTER_DEFAULT,
				'_wc_notice_nonce'	 => FILTER_DEFAULT,
			) );
			if ( 'missing_hook_listing' === $data['wc-hide-notice'] && wp_verify_nonce( $data['_wc_notice_nonce'], 'woocommerce_hide_notices_nonce' ) ) {
				tinv_update_option( 'template_checker', 'hide_product_listing', true );
			}
		}

		wp_enqueue_script( $this->_n . '-checker', TINVWL_URL . 'asset/js/admin.checker.min.js', array( 'jquery' ), $this->_v, 'all' );
	}

	/**
	 * Validation templates hook from request remote page
	 */
	function validation_template() {
		global $post, $product;

		if ( tinv_get_option( 'template_checker', 'checked' ) ) {
			return;
		}
		if ( absint( tinv_get_option( 'template_checker', 'time' ) ) + HOUR_IN_SECONDS > time() ) {
			return;
		}
		tinv_update_option( 'template_checker', 'time', time() );
		$tags	 = array(
			'woocommerce_single_product_summary'	 => 'tinvwl_single_product_summary',
			'woocommerce_before_add_to_cart_button'	 => 'tinvwl_before_add_to_cart_button',
			'woocommerce_after_add_to_cart_button'	 => 'tinvwl_after_add_to_cart_button',
		);
		$tch		 = TInvWL_CheckerHook::instance();
		$tch->add_action( $tags );
		$tch->add_action( array_keys( $tags ) );

		$types	 = wc_get_product_types();

		$check = true;
		foreach ( $types as $type => $type_name ) {
			if ( tinv_get_option( 'template_checker', 'missing_hook_' . $type ) ) {
				continue;
			}

			if ( function_exists( 'wc_get_products' ) ) {
				$products	 = wc_get_products( array(
					'status' => 'publish',
					'type'	 => $type,
					'limit'	 => 1,
				) );
			} else {
				$products = array_map( 'wc_get_product', get_posts( array(
					'post_type'		 => 'product',
					'post_status'	 => 'publish',
					'numberposts'	 => 1,
					'tax_query'		 => array(
						array(
							'taxonomy'	 => 'product_type',
							'field'		 => 'slug',
							'terms'		 => $type,
						),
					),
				) ) );
			}
			if ( ! empty( $products ) ) {
				$product = array_shift( $products );
				$post	 = get_post( $product->get_id() ); // @codingStandardsIgnoreLine  WordPress.Variables.GlobalVariables.OverrideProhibited
				$result	 = $tch->run( array(
					'template'		 => array( 'content-single-product.php', 'single-product/add-to-cart/' . $type . '.php' ),
					'template_args'	 => array(
						'available_variations'	 => array( 1, 2, 3, 4, 5 ),
						'attributes'			 => array(),
					),
					'url'			 => $product->get_permalink(),
				) );
				if ( ! empty( $result ) ) {
					$result	 = array_keys( $result );
					foreach ( $result as $key => $tag ) {
						if ( array_key_exists( $tag, $tags ) ) {
							$tags[ $tag ];
							if ( ! array_key_exists( $tag, $tags ) ) {
								unset( $result[ $key ] );
							}
						} else {
							unset( $result[ $key ] );
						}
					}
					if ( ! empty( $result ) ) {
						WC_Admin_Notices::add_custom_notice( 'missing_hook_' . $type, sprintf( _n( 'The "Add to Wishlist" button may work improperly in a product type "%1$s" because the hook "%2$s" is missing.<br />Please, ask your theme developers to check the theme templates or <a href="https://templateinvaders.com/help/" target="_blank">contact us</a> for assistance.', 'The "Add to Wishlist" button may work improperly in a product type "%1$s" because the hooks "%2$s" are missing.<br />Please, ask your theme developers to check the theme templates or <a href="https://templateinvaders.com/help/" target="_blank">contact us</a> for assistance.', count( $result ), 'ti-woocommerce-wishlist' ), $type_name, '<strong>' . join( '</strong>, <strong>', $result ) . '</strong>' ) );
						$check = false;
					} else {
						WC_Admin_Notices::remove_notice( 'missing_hook_' . $type );
					}
				} else {
					WC_Admin_Notices::remove_notice( 'missing_hook_' . $type );
				}
			}
		} // End foreach().

		tinv_update_option( 'template_checker', 'checked', $check );
		wp_die();
	}

	/**
	 * Clear notice validation template when theme switched
	 */
	function clear_notice_validation_template() {
		WC_Admin_Notices::remove_notice( 'missing_hook_listing' );
		$types	 = wc_get_product_types();
		foreach ( $types as $type => $type_name ) {
			WC_Admin_Notices::remove_notice( 'missing_hook_' . $type );
		}
		tinv_update_option( 'template_checker', '', array() );
	}

	/**
	 * Disable screen option on plugin pages
	 *
	 * @param boolean    $show_screen Show screen.
	 * @param \WP_Screen $_this Screen option page.
	 * @return boolean
	 */
	function screen_options_hide_screen( $show_screen, $_this ) {
		if ( $this->_n === $_this->parent_base || $this->_n === $_this->parent_file ) {
			return false;
		}
		return $show_screen;
	}

	/**
	 * Check if there is a hook in the cron
	 */
	function scheduled_remove_wishlist() {
		$timestamp = wp_next_scheduled( 'tinvwl_remove_without_author_wishlist' );
		if ( $timestamp ) {
			$time = strtotime( '00:00 today +1 HOURS' );
			wp_schedule_event( $time, 'daily', 'tinvwl_remove_without_author_wishlist' );
		}
	}

	/**
	 * Removing empty wishlist without a user older than 7 days
	 */
	public function remove_empty_wishlists() {
		$wl			 = new TInvWL_Wishlist();
		$wishlists	 = $wl->get( array(
			'author' => 0,
			'type'	 => 'default',
			'sql'	 => 'SELECT * FROM {table} {where} AND `date` < DATE_SUB( CURDATE(), INTERVAL 7 DAY)',
		) );
		foreach ( $wishlists as $wishlist ) {
			$wlp		 = new TInvWL_Product( $wishlist );
			$products	 = $wlp->get_wishlist( array(
				'count'		 => 1,
				'external'	 => true,
			) );
			if ( empty( $products ) ) {
				$wl->remove( $wishlist['ID'] );
			}
		}
	}

	/**
	 * Removing old wishlist without a user older than 34 days
	 */
	public function remove_old_wishlists() {
		$wl			 = new TInvWL_Wishlist();
		$wishlists	 = $wl->get( array(
			'author' => 0,
			'type'	 => 'default',
			'sql'	 => 'SELECT * FROM {table} {where} AND `date` < DATE_SUB( CURDATE(), INTERVAL 34 DAY)',
		) );
		foreach ( $wishlists as $wishlist ) {
			$wl->remove( $wishlist['ID'] );
		}
	}

}
