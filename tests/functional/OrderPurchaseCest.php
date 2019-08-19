<?php
namespace WC_Gateway_WordPay\Tests\Functional;

use \FunctionalTester as FunctionalTester;

class OrderPurchaseCest
{

    public function test_order_created_successfully(FunctionalTester $I)
    {
        $I->amOnPage('/shop');
        $I->click('Add to cart');
        $I->click('View cart');
        $I->click('Proceed to checkout');
        $I->fillField('billing_first_name', 'Yosefu');
        $I->fillField('billing_last_name', 'Okot');
        $I->selectOption('form select[name=billing_country]', 'Uganda');
        $I->fillField('billing_address_1', 'Plot 1234');
        $I->fillField('billing_city', 'Kampala');
        $I->fillField('billing_state', 'Kampala');
        $I->fillField('billing_postcode', '25641');
        $I->fillField('billing_phone', '+2567123456');
        $I->fillField('billing_email', 'yosefu.omulangira.okot@test.test');
        $I->click('Place order');
        $I->see('Order received');
        $orderID = $I->grabFromCurrentUrl('~/order-received/(\d+)/~');
        //Confirm that the order is created
        $I->seePostInDatabase(
            [
                'ID'          => $orderID,
                'post_type'   => 'shop_order',
                'post_status' => 'wc-on-hold',
            ]
        );
        //Confirm that the payment was made using wordpay
        $I->seePostMetaInDatabase(
            [
                'post_id'    => $orderID,
                'meta_key'   => '_payment_method',
                'meta_value' => 'wordpay',
            ]
        );
    }
}
