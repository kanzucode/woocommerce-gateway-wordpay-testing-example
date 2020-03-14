<?php
namespace WC_Gateway_WordPay\Tests\WPUnit;


class WCGatewayWordPayTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * @var \WpunitTester
     */
    protected $tester;

    public function setUp()
    {
        // Before...
        parent::setUp();

        // Add set up methods here.
    }

    public function tearDown()
    {
        // Add tear down methods here.

        // Then...
        parent::tearDown();
    }

    public function test_gateway_is_added_to_payment_gateways()
    {
        $gateways = ['Test_Gateway'];
        $new_gateways = wc_offline_add_to_gateways($gateways);
        $this->assertSame(['Test_Gateway','WC_Gateway_WordPay'],$new_gateways);
    }

    public function test_plugin_action_link_added(){
        $wp_plugin_links = ['deactivate' => '<a href="plugins.php?action=deactivate&amp;plugin=woocommerce-gateway-wordpay%2Fwoocommerce-gateway-wordpay.php&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=ad6e726ed5" aria-label="Deactivate WooCommerce WordPay Gateway">Deactivate</a>'];
        $plugin_links = [
            '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=offline_gateway' ) . '">' . __( 'Configure', 'wc-gateway-wordpay' ) . '</a>'
        ];
        $expected_plugin_links = wc_offline_gateway_plugin_links($wp_plugin_links);
        $this->assertArrayHasKey('configure', $expected_plugin_links );
    }
}
