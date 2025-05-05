<?php

/*
 * Plugin Name:       WooCommerce GTIN Generator
 * Plugin URI:        https://github.com/buildouttech-design/woo-gtin-generator
 * Description:       Generate GTINs for WooCommerce products.
 * Version:           1.0.0
 * Requires at least: 5.3.0
 * Requires PHP:      8.2.0
 * Author:            Paul Anthony McGowan
 * Author URI:        https://buildouttechno.com
 * Text Domain:       woo-gtin-generator
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:       /languages
 * WC requires at least: 4.5
 * WC tested up to:     8.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action( 'plugins_loaded', function() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error">' . esc_html__( 'WooCommerce GTIN Generator requires WooCommerce to be installed and activated.', 'woo-gtin-generator' ) . '</div>';
        } );
        return;
    }

    // Declare WooCommerce feature compatibility
    add_action( 'before_woocommerce_init', function() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
        }
    });

    // Load textdomain for translations
    load_plugin_textdomain( 'woo-gtin-generator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

});

require_once __DIR__ . '/includes/class-woo-gtin-generator.php';
Woo_Gtin_Generator::instance();



