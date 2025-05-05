<?php
// includes/class-woo-gtin-generator.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WooCommerce GTIN Generator Class
 *
 * Handles GTIN field display, saving, and generation for WooCommerce products.
 *
 * @package Woo_Gtin
 * @since 1.0.0
 */


class Woo_Gtin_Generator {
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Add GTIN field for simple products
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_simple_product_gtin_field' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_simple_product_gtin_field' ] );

        // Add GTIN field for variable products
        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'add_variation_gtin_field' ], 10, 3 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_gtin_field' ], 10, 2 );

        // Auto-generate GTIN on product save
        add_action( 'save_post_product', [ $this, 'auto_generate_gtin_on_save' ], 20, 3 );
    }

    // Simple product GTIN field
    public function add_simple_product_gtin_field() {
        woocommerce_wp_text_input( [
            'id'          => '_gtin',
            'label'       => __( 'GTIN', 'woo-gtin-generator' ),
            'desc_tip'    => true,
            'description' => __( 'Enter the GTIN for this product.', 'woo-gtin-generator' ),
            'type'        => 'text',
            'custom_attributes' => [
                'maxlength' => 14,
                'pattern'   => '[0-9]{8,14}',
            ],
        ] );
    }

    public function save_simple_product_gtin_field( $post_id ) {
        if ( isset( $_POST['_gtin'] ) ) {
            update_post_meta( $post_id, '_gtin', sanitize_text_field( $_POST['_gtin'] ) );
        }
    }

    // Variation GTIN field
    public function add_variation_gtin_field( $loop, $variation_data, $variation ) {
        woocommerce_wp_text_input( [
            'id'          => 'gtin[' . $loop . ']',
            'label'       => __( 'GTIN', 'woo-gtin-generator' ),
            'value'       => get_post_meta( $variation->ID, '_gtin', true ),
            'wrapper_class' => 'form-row form-row-full',
            'custom_attributes' => [
                'maxlength' => 14,
                'pattern'   => '[0-9]{8,14}',
            ],
        ] );
    }

    public function save_variation_gtin_field( $variation_id, $i ) {
        if ( isset( $_POST['gtin'][ $i ] ) ) {
            update_post_meta( $variation_id, '_gtin', sanitize_text_field( $_POST['gtin'][ $i ] ) );
        }
    }

    // Auto-generate GTIN on product save if empty
    public function auto_generate_gtin_on_save( $post_id, $post, $update ) {
        if ( $post->post_type !== 'product' ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $gtin = get_post_meta( $post_id, '_gtin', true );
        if ( empty( $gtin ) ) {
            $base_code = str_pad( $post_id, 12, '0', STR_PAD_LEFT );
            $new_gtin = $this->generate_gtin13( $base_code );
            if ( $new_gtin ) {
                update_post_meta( $post_id, '_gtin', $new_gtin );
            }
        }

        // Auto-generate for variations
        $variations = wc_get_products( [
            'parent' => $post_id,
            'type'   => 'variation',
            'limit'  => -1,
        ] );

        foreach ( $variations as $variation ) {
            $var_gtin = get_post_meta( $variation->get_id(), '_gtin', true );
            if ( empty( $var_gtin ) ) {
                $base_code = str_pad( $variation->get_id(), 12, '0', STR_PAD_LEFT );
                $new_var_gtin = $this->generate_gtin13( $base_code );
                if ( $new_var_gtin ) {
                    update_post_meta( $variation->get_id(), '_gtin', $new_var_gtin );
                }
            }
        }
    }

    // Generate GTIN-13 with checksum
    public function generate_gtin13( string $base_code ) {
        if ( strlen( $base_code ) !== 12 || ! ctype_digit( $base_code ) ) {
            return false;
        }

        $sum = 0;
        for ( $i = 0; $i < 12; $i++ ) {
            $digit = intval( $base_code[ $i ] );
            $sum += ( $i % 2 === 0 ) ? $digit : $digit * 3;
        }

        $checksum = ( 10 - ( $sum % 10 ) ) % 10;

        return $base_code . $checksum;
    }
}
