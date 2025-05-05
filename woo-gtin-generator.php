<?php

declare(strict_types=1);

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

if ( ! defined( 'GTIN_PLUGIN_URL' ) ) {
    define( 'GTIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// WooCommerce compatibility
if ( ! class_exists( 'WooCommerce' ) ) {
    add_action( 'admin_notices', function() {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'WooCommerce GTIN Generator requires WooCommerce to be installed and activated.', 'woo-gtin-generator' ) . '</p></div>';
    } );
    return;
}
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});
add_action( 'woocommerce_loaded', function() {
    load_plugin_textdomain( 'woo-gtin-generator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
} );
add_action( 'plugins_loaded', function() {
    require_once __DIR__ . '/includes/class-woo-gtin-generator.php';
    require_once __DIR__ . '/includes/class-woo-gtin-generator-admin.php';
    require_once __DIR__ . '/includes/class-woo-gtin-generator-frontend.php';

    new Woo_Gtin_Generator();
    new Woo_Gtin_Admin();
    new Woo_Gtin_Frontend();
    new Woo_Gtin_Bulk_Generator();
} );
add_action( 'woocommerce_product_after_variable_attributes', function( $loop, $variation_data, $variation ) {
    woocommerce_wp_text_input( [
        'id'                => 'gtin[' . $loop . ']',
        'label'             => __( 'GTIN', 'woo-gtin-generator' ),
        'value'             => get_post_meta( $variation->ID, '_gtin', true ),
        'wrapper_class'     => 'form-row form-row-full',
        'custom_attributes' => [
            'required'  => 'required',
            'maxlength' => 14,
            'pattern'   => '[0-9]{8,14}',
        ],
    ] );
}, 10, 3 );
add_action( 'woocommerce_save_product_variation', function( $variation_id, $i ) {
    if ( isset( $_POST['gtin'][ $i ] ) ) {
        update_post_meta( $variation_id, '_gtin', sanitize_text_field( $_POST['gtin'][ $i ] ) );
    }
}, 10, 2 );

