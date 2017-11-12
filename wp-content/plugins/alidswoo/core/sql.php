<?php
/**
 * Author: Vitaly Kukin
 * Date: 12.10.2016
 * Time: 9:49
 */

function adsw_sql_list(){

	global $wpdb;

	$charset_collate = !empty($wpdb->charset) ? "DEFAULT CHARACTER SET $wpdb->charset" : "DEFAULT CHARACTER SET utf8mb4";

	return array(

		"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}adsw_ali_meta (
            `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` BIGINT(20) unsigned NOT NULL,
            `product_id` VARCHAR(20) NOT NULL,
            `origPrice` DECIMAL(10,2) DEFAULT '0.00',
            `origPriceMax` DECIMAL(10,2) DEFAULT '0.00',
            `origSalePrice` DECIMAL(10,2) DEFAULT '0.00',
            `origSalePriceMax` DECIMAL(10,2) DEFAULT '0.00',
            `productUrl` VARCHAR(255) DEFAULT NULL,
            `feedbackUrl` VARCHAR(255) DEFAULT NULL,
            `storeUrl` VARCHAR(255) DEFAULT NULL,
            `storeName` VARCHAR(255) DEFAULT NULL,
            `storeRate` VARCHAR(255) DEFAULT NULL,
            `adminDescription` TEXT DEFAULT NULL,
            `skuOriginaAttr` LONGTEXT DEFAULT NULL,
            `skuOriginal` LONGTEXT DEFAULT NULL,
            `currencyCode` CHAR(4) DEFAULT 'USD',
            `needUpdate` TINYINT(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY (`post_id`),
            KEY (`product_id`)
	    ) ENGINE = InnoDB {$charset_collate};",

		"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}adsw_activities` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `post_id` BIGINT(20) unsigned NOT NULL,
            `product_data` TEXT DEFAULT NULL,
            `type` VARCHAR(20) DEFAULT NULL,
            `date` DATETIME DEFAULT '0000-00-00 00:00:00',
            `anonce` VARCHAR(255) DEFAULT NULL,
            `status` VARCHAR(20) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB {$charset_collate};",
		
	);
}