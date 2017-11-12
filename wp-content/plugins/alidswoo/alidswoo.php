<?php
/**
 *	Plugin Name: AliDropship Woo Plugin
 *	Plugin URI: https://alidropship.com/
 *	Description: AliDropship Woo is a WordPress plugin created for import AliExpress product to Woo Shop
 *	Version: 0.7.0.1
 *	Text Domain: adsw
 *	Domain Path: /lang
 *	Requires at least: WP 4.8.2
 *	Author: Vitaly Kukin & Yaroslav Nevskiy & Pavel Shishkin
 *	Author URI: http://yellowduck.me/
 *	License: SHAREWARE
 */

if ( !defined('ADSW_VERSION') ) define( 'ADSW_VERSION', '0.7.0.1' );
if ( !defined('ADSW_PATH') ) define( 'ADSW_PATH', plugin_dir_path( __FILE__ ) );
if ( !defined('ADSW_URL') ) define( 'ADSW_URL', str_replace( array('https:', 'http:'), '', plugins_url('alidswoo') ) );
if ( !defined('ADSW_CODE') ) define( 'ADSW_CODE', 'ion' );
if ( !defined('ADSW_ERROR') ) define( 'ADSW_ERROR', adsw_check_server() );

function adsw_check_server() {

	if( '5.6' > PHP_VERSION )
		return __( 'PHP Version is not suitable. You need version 5.6+', 'adsw' ) .
		'. <a href="https://alidropship.com/codex/hosting-server-setup-php-zend-guard/" target="_blank">Learn more</a>.';

	$extensions = get_loaded_extensions();

	$key = ADSW_CODE != 'ion' ? 'Zend Guard Loader' : 'ionCube Loader';

	if ( ! in_array($key, $extensions) ) {

		return sprintf( __( '%s Not found', 'adsw' ), $key ) .
		'. <a href="https://alidropship.com/codex/hosting-server-setup-php-zend-guard/" target="_blank">Learn more</a>.';
	}

	return false;
}

function adsw_admin_notice__error() {

	$check = adsw_check_server();

	if( $check ) {

		$class = 'notice notice-error';
		$message = __( 'Error!', 'adsw' ) . ' ' . $check;

		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
}
add_action( 'admin_notices', 'adsw_admin_notice__error' );

if ( ! ADSW_ERROR) {
    require( ADSW_PATH . 'core/core.php');
    require( ADSW_PATH . 'core/update.php');
}

if( is_admin() ) :

	require( ADSW_PATH . 'core/setup.php');

    register_activation_hook( __FILE__, 'adsw_lang_init' );
	register_activation_hook( __FILE__, 'adsw_install' );
	register_uninstall_hook( __FILE__, 'adsw_uninstall' );
	register_activation_hook( __FILE__, 'adsw_activate' );

endif;

if( ! ADSW_ERROR ) {
	require( ADSW_PATH . 'core/filters.php' );
	require( ADSW_PATH . 'core/init.php' );
    require( ADSW_PATH . 'core/cron.php' );

	if (is_admin()) :
		require(ADSW_PATH . 'core/controller.php');
	else :
        require( ADSW_PATH . 'core/hooks.php' );
	endif;
}