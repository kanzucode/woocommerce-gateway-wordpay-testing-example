# WooCommerce Gateway WordPay  
Demo plugin to show how to use Codeception for plugin tests.

## Installation
1. Install and activate WooCommerce.
2. Place this in your WordPress plugin folder.
3. Run install:
`composer install`
4. Copy `.env.testing.sample` to `.env.testing`
5. Modify the configurations therein to point to your WordPress set-up.

To run acceptance tests, install chromedriver and selenium server. See instructions here: [Installation](https://codeception.com/docs/modules/WebDriver#Selenium)

## Run the tests
```
./vendor/bin/codecept run wpunit
./vendor/bin/codecept run functional
./vendor/bin/codecept run acceptance
```
