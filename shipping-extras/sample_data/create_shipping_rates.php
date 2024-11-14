<?php
/**
 * Create shipping methods for testing
 * This is done by partially replicating the functions called by requests submitted through admin
 */

// Ensure WooCommerce is loaded
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zones = WC_Shipping_Zones::get_zones();
$global_zone = null;

foreach ( $zones as $zone ) {
    if ( empty( $zone['zone_locations'] ) ) {
        $global_zone = WC_Shipping_Zones::get_zone( $zone['zone_id'] );
        break;
    }
}

// create Standard shipping
$std_shipping_id = $global_zone->add_shipping_method('flat_rate');
$std_shipping = new WC_Shipping_Flat_Rate($std_shipping_id);

// must pretend there was a request
$_REQUEST = array(
    'instance_id' => $std_shipping_id
);

$post_data = array(
    'woocommerce_flat_rate_title' => 'Standard',
    'woocommerce_flat_rate_tax_status' => 'taxable',
    'woocommerce_flat_rate_cost' => '15.00',
);

$std_shipping->set_post_data($post_data);

if ( !$std_shipping->process_admin_options() ) {
    error_log('Error creating "Standard" shipping method. Try to add it manually through admin.');
}

// create Fast shipping
$fast_ship_id = $global_zone->add_shipping_method('flat_rate');
$fast_shipping = new WC_Shipping_Flat_Rate($fast_ship_id);

// must pretend there was a request
$_REQUEST = array(
    'instance_id' => $fast_ship_id
);

$post_data = array(
    'woocommerce_flat_rate_title' => 'Fast',
    'woocommerce_flat_rate_tax_status' => 'taxable',
    'woocommerce_flat_rate_cost' => '50.00',
);

$fast_shipping->set_post_data($post_data);

if ( !$fast_shipping->process_admin_options() ) {
    error_log('Error creating "Fast" shipping method. Try to add it manually through admin.');
}

?>