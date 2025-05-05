<?php
// includes/class-woo-gtin-generator.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Gtin_Generator {
    public function __construct() {
        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'add_gtin_field' ], 10, 3 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_gtin_field' ], 10, 2 );
    }

    public function add_gtin_field( $loop, $variation_data, $variation ) {
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
    }

    public function save_gtin_field( $variation_id, $i ) {
        if ( isset( $_POST['gtin'][ $i ] ) ) {
            update_post_meta( $variation_id, '_gtin', sanitize_text_field( $_POST['gtin'][ $i ] ) );
        }
    }
}
