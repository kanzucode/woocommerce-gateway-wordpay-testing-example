<?php
/**
 * Plugin Name: WooCommerce WordPay Gateway
 * Plugin URI: https://kanzucode.com
 * Description: Clones the "Cheque" gateway to create a fictional manual / offline payment method for WordPay - a payment gateway released by WordPress in an alternative universe.
 * Author: Kanzu Code
 * Author URI: http://kanzucode.com/
 * Version: 1.0.0
 * Text Domain: wc-gateway-wordpay
 * Domain Path: /i18n/languages/
 *
 *
 */

defined( 'ABSPATH' ) or exit;


/**
 * Add the gateway to WC Available Gateways
 *
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
function wc_offline_add_to_gateways( $wc_gateways ) {
	$wc_gateways[] = 'WC_Gateway_WordPay';
	return $wc_gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_offline_add_to_gateways' );


/**
 * Adds plugin page links
 *
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_offline_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=offline_gateway' ) . '">' . __( 'Configure', 'wc-gateway-wordpay' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_offline_gateway_plugin_links' );


/**
 * Offline Payment Gateway
 *
 * Provides an Offline Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		KanzuCode
 */
add_action( 'plugins_loaded', 'wc_offline_gateway_init', 11 );

function wc_offline_gateway_init() {
    // Make sure WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) {
    	return;
    }


    class WC_Gateway_WordPay extends WC_Payment_Gateway {

    	/**
    	 * Constructor for the gateway.
    	 */
    	public function __construct() {
    		$this->id                 = 'wordpay';
    		$this->icon               = apply_filters( 'woocommerce_cheque_icon', '' );
    		$this->has_fields         = false;
    		$this->method_title       = _x( 'WordPay payments', 'WordPay payment method', 'woocommerce' );
    		$this->method_description = __( 'Take payments in person via WordPay. This offline gateway can also be useful to test purchases.', 'woocommerce' );

    		// Load the settings.
    		$this->init_form_fields();
    		$this->init_settings();

    		// Define user set variables.
    		$this->title        = $this->get_option( 'title' );
    		$this->description  = $this->get_option( 'description' );
    		$this->instructions = $this->get_option( 'instructions' );

    		// Actions.
    		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    		add_action( 'woocommerce_thankyou_cheque', array( $this, 'thankyou_page' ) );

    		// Customer Emails.
    		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    	}

    	/**
    	 * Initialise Gateway Settings Form Fields.
    	 */
    	public function init_form_fields() {

    		$this->form_fields = array(
    			'enabled'      => array(
    				'title'   => __( 'Enable/Disable', 'woocommerce' ),
    				'type'    => 'checkbox',
    				'label'   => __( 'Enable WordPay payments', 'woocommerce' ),
    				'default' => 'no',
    			),
    			'title'        => array(
    				'title'       => __( 'Title', 'woocommerce' ),
    				'type'        => 'text',
    				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
    				'default'     => _x( 'WordPay payments', 'WordPay payment method', 'woocommerce' ),
    				'desc_tip'    => true,
    			),
    			'description'  => array(
    				'title'       => __( 'Description', 'woocommerce' ),
    				'type'        => 'textarea',
    				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
    				'default'     => __( 'Please send a WordPay payment to Store Name, Store Street, Store Town, Store State / County, Store Postcode.', 'woocommerce' ),
    				'desc_tip'    => true,
    			),
    			'instructions' => array(
    				'title'       => __( 'Instructions', 'woocommerce' ),
    				'type'        => 'textarea',
    				'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
    				'default'     => '',
    				'desc_tip'    => true,
    			),
    		);
    	}

    	/**
    	 * Output for the order received page.
    	 */
    	public function thankyou_page() {
    		if ( $this->instructions ) {
    			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
    		}
    	}

    	/**
    	 * Add content to the WC emails.
    	 *
    	 * @access public
    	 * @param WC_Order $order Order object.
    	 * @param bool     $sent_to_admin Sent to admin.
    	 * @param bool     $plain_text Email format: plain text or HTML.
    	 */
    	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
    		if ( $this->instructions && ! $sent_to_admin && 'cheque' === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
    			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
    		}
    	}

    	/**
    	 * Process the payment and return the result.
    	 *
    	 * @param int $order_id Order ID.
    	 * @return array
    	 */
    	public function process_payment( $order_id ) {

    		$order = wc_get_order( $order_id );

    		if ( $order->get_total() > 0 ) {
    			// Mark as on-hold (we're awaiting the cheque).
    			$order->update_status( apply_filters( 'woocommerce_cheque_process_payment_order_status', 'on-hold', $order ), _x( 'Awaiting WordPay payment', 'WordPay payment method', 'woocommerce' ) );
    		} else {
    			$order->payment_complete();
    		}

    		// Remove cart.
    		WC()->cart->empty_cart();

    		// Return thankyou redirect.
    		return array(
    			'result'   => 'success',
    			'redirect' => $this->get_return_url( $order ),
    		);
    	}
    }
}
