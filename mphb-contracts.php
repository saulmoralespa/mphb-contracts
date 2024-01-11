<?php
/*
 * Plugin Name: Hotel Booking PDF Contracts
 * Description: Generate booking contract.
 * Version: 1.0.0
 * Author: Saul Morales Pacheco
 * Author URI: https://saulmoralespa.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: mphb-contracts
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!defined('HOTEL_BOOKING_PDF_CONTRACTS_VERSION')){
    define('HOTEL_BOOKING_PDF_CONTRACTS_VERSION', '1.0.0');
}

if(!defined('MPHB_CONTRACT_PLUGIN_DIR')){
    define( 'MPHB_CONTRACT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

add_action( 'plugins_loaded', 'hotel_booking_pdf_contracts_init');

function hotel_booking_pdf_contracts_init(){

    load_plugin_textdomain(
        'mphb-contracts',
        false,
        plugin_basename( plugin_dir_path( __FILE__ ) ) . '/languages'
    );

    if(!hotel_booking_pdf_contracts_requirements()) return;

    mphb_contracts()->contract();

}

function hotel_booking_pdf_contracts_notices( $notice ) {
    ?>
    <div class="error notice">
        <p><?php echo wp_kses_post(wpautop( $notice )); ?></p>
    </div>
    <?php
}

function hotel_booking_pdf_contracts_requirements(){

    if ( !class_exists( 'HotelBookingPlugin' ) && function_exists( 'MPHB' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    hotel_booking_pdf_contracts_notices( __( 'Hotel Booking PDF Contracts plugin requires activated Hotel Booking plugin.', 'mphb-contracts' ) );
                }
            );
        }
        return false;
    }

    return true;
}

function mphb_contracts(){
    static $plugin;
    if (!isset($plugin)){
        require_once('includes/class-mphb-contract-plugin.php');
        $plugin = new MPHB_Contract_Plugin(__FILE__, HOTEL_BOOKING_PDF_CONTRACTS_VERSION);
    }
    return $plugin;
}

function activate(){
    $capability = 'mphb_contracts_generate';
    $roles = [
            'administrator'
    ];

    if (
        class_exists('\MPHB\UsersAndRoles\Roles') &&
        defined('\MPHB\UsersAndRoles\Roles::MANAGER')
    ) {
        $roles[] = \MPHB\UsersAndRoles\Roles::MANAGER;
    }

    global $wp_roles;

    if (!class_exists('WP_Roles')) {
        return;
    }

    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }

    foreach ($roles as $role){
        $wp_roles->add_cap($role, $capability);
    }

}

register_activation_hook(__FILE__, 'activate');