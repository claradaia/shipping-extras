# Shipping Extras

A WooCommerce extension that adds custom discount rates for shipping according to the purchase subtotal, as well as free shipping for users with a "VIP Customer" role.

Inspired by [Create Woo Extension](https://github.com/woocommerce/woocommerce/blob/trunk/packages/js/create-woo-extension/README.md).


## Getting Started

### Requirements

-   [NPM](https://www.npmjs.com/)
-   [Composer](https://getcomposer.org/download/)

### Installation and Build

```
$ npm install
$ npm run build
```

### Testing

#### Requirements

-   [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)

#### Loading sample data

Create sample users, products and shipping methods by running
```
$ wp-env start
$ wp-env run cli bash ./wp-content/plugins/shipping-extras/load_sample.sh
```

#### Shipping discounts

- Log in to the pre populated store as John Doe (username `jdoe`, password `jdoe`)
- Add items to your cart
- Go to checkout and check the shipping costs. Increase and decrease quantities to check discounts for each subtotal tier.

#### VIP Customer shipping

- Log in to the pre populated store as Victor Irving Parker (username `viparker`, password `viparker`)
- Add items to your cart
- Go to checkout and check the shipping costs
