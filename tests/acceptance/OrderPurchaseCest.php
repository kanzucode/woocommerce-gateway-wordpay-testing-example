<?php
namespace WC_Gateway_WordPay\Tests\Acceptance;

use \AcceptanceTester as AcceptanceTester;

class OrderPurchaseCest
{

    public function test_order_created_successfully(AcceptanceTester $I)
    {
        $I->amOnUrl('http://localhost/shopping/shop/');
        $I->click('Add to cart');
        $I->wait(5);
        $I->click('.added_to_cart');
        $I->wait(5);
        $I->click('Proceed to checkout');
        $I->fillField('billing_first_name', 'Yosefu');
        $I->fillField('billing_last_name', 'Okot');
        $I->selectOption('form select[name=billing_country]', 'Uganda');
        $I->fillField('billing_address_1', 'Plot 1234');
        $I->fillField('billing_city', 'Kampala');
        // $I->fillField('billing_state', 'Kampala');
        $I->fillField('billing_phone', '+2567123456');
        $I->fillField('billing_email', 'yosefu.omulangira.okot@test.test');
        $I->wait(5);
        $I->click('#place_order');
        $I->waitForElement('.woocommerce-order',5);
        $orderID = $I->grabFromCurrentUrl('~/order-received/(\d+)/~');
        $I->see('Order received');

    }
}
