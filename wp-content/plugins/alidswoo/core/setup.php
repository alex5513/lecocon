<?php

/**
 * Setup the plugin
 */
function adsw_install() {

	require( ADSW_PATH . 'core/sql.php' );

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	foreach(adsw_sql_list() as $key) {
		dbDelta($key);
	}

	adsw_upgrade_db();
	update_site_option( 'adsw-version', ADSW_VERSION  );
}

function adsw_upgrade_db() {

	global $wpdb;

	maybe_add_column($wpdb->prefix . 'adsw_ali_meta', 'skuOriginal', "ALTER TABLE `{$wpdb->prefix}adsw_ali_meta` ADD `skuOriginal` LONGTEXT DEFAULT NULL;");
}

/**
 * Uninstall plugin
 */
function adsw_uninstall(){}

/**
 * Check installed plugin
 */
function adsw_installed(){

	if ( !current_user_can('install_plugins') ) return;

	if ( get_site_option('adsw-version') < ADSW_VERSION )
		adsw_install( );
}
add_action( 'admin_menu', 'adsw_installed' );

/**
 * When activate plugin
 */
function adsw_activate(){

	adsw_installed();
    
	do_action( 'adsw_activate' );
}

/**
 * When deactivate plugin
 */
function adsw_deactivate(){

	do_action( 'adsw_deactivate' );
}