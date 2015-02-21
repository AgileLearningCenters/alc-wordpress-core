=== WordPress Simple Paypal Shopping Cart ===
Contributors: Tips and Tricks HQ, Ruhul Amin, wptipsntricks, mbrsolution
Donate link: https://www.tipsandtricks-hq.com
Tags: cart, shopping cart, WordPress shopping cart, Paypal shopping cart, sell, selling, sell products, online shop, shop, e-commerce, wordpress ecommerce, wordpress store, store, PayPal cart widget, sell digital products, sell service, digital downloads, paypal, paypal cart, e-shop, compact cart, coupon, discount
Requires at least: 3.0
Tested up to: 4.1
Stable tag: 4.0.6
License: GPLv2 or later

Very easy to use Simple WordPress Paypal Shopping Cart Plugin. Great for selling products online in one click from your WordPress site.

== Description ==

WordPress Simple Paypal Shopping Cart allows you to add an 'Add to Cart' button for your product on any posts or pages. This simple shopping cart plugin lets you sell products and services directly from your own wordpress site and turns your WP blog into an ecommerce site.

It also allows you to add/display the shopping cart on any post or page or sidebar easily. The shopping cart shows the user what they currently have in the cart and allows them to change quantity or remove the items. 

http://www.youtube.com/watch?v=tEZWfTmZ2kk

You will be able to create products by using shortcodes dynamically.

The shopping cart output will be responsive if you are using it with a responsive theme.

It can be easily integrated with the NextGen Photo Gallery plugin to accommodate the selling of photographs from your gallery.

This plugin is a lightweight solution (with minimal number of lines of code and minimal options) so it doesn't slow down your site.

WP simple Paypal Cart Plugin, interfaces with the Paypal sandbox to allow for testing.

For video tutorial, screenshots, detailed documentation, support and updates, please visit:
[WP Simple Cart Details Page](https://www.tipsandtricks-hq.com/wordpress-simple-paypal-shopping-cart-plugin-768)
or
[WP Simple Cart Documentation](http://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart)

= Features =

* Easily create "add to cart" button with options if needed (price, shipping, options variations). The cart's shortcode can be displayed on posts or pages.
* Use a function to add dynamic "add to cart" button directly in your theme.
* Minimal number of configuration items to keep the plugin lightweight.
* Sell any kind of tangible products from your site.
* Ability to sell services from your your site.
* Sell any type of media file that you upload to your WordPress site. For example: you can sell ebooks (PDF), music files (MP3), audio files, videos, photos, images etc.
* Your customers will automatically get an email with the media file that they paid for.
* Show a nicely formatted product display box on the fly using a simple shortcode.
* You can use Paypal sandbox to do testing if needed (before you go live).
* Collect special instructions from your customers on the PayPal checkout page.
* The orders menu will show you all the orders that you have received from your site.
* Ability to configure an email that will get sent to your buyers after they purchase your product.
* Ability to configure a sale notification email that gets sent to the site admin when a customer purchase your item(s).
* Ability to configure discount coupons. Offer special discounts on your store/shop.
* You can create coupons and give to your customers. When they use coupons during the checkout they will receive a discount.
* Create discount coupons with an expiry date. The coupon code automatically expires after the date you set.
* Compatible with WordPress Multi-site Installation.
* Ability to specify SKU (item number) for each of your products in the shortcode.
* Ability to customize the add to cart button image and use a custom image for your purchase buttons.
* Track coupons with the order to see which customer used which coupon code.
* Ability to add a compact shopping cart to your site using a shortcode.
* Ability to show shopping cart with product image thumbnails.
* Ability to use a custom checkout page style.
* Ability to open checkout page in a new browser tab/window.
* Works nicely with responsive WordPress themes.
* Can be translated into any language.
* and more...

= Shopping Cart Setup Video Tutorials =

There is a series of video tutorials to show you how to setup the shopping cart on your site. 

Check the video tutorials [here](https://www.tipsandtricks-hq.com/wordpress-simple-paypal-shopping-cart-plugin-768).

= Language Translations =

The following language translations are already available:

* English
* German
* Spanish
* French
* Italian
* Japanese
* Polish
* Czech
* Hebrew
* Swedish
* Norwegian
* Danish

You can translate the plugin using [this documentation](http://www.tipsandtricks-hq.com/ecommerce/translating-the-wp-simple-shopping-cart-plugin-2627).

= Note =

There are a few exact duplicate copies of this plugin that other people made. We have a few users who are getting confused as to which one is the original simple shopping cart plugin. This is the original simple PayPal shopping cart and you can verify it with the following information:

* Check the stats tab of the plugin and you will be able to see a history of when this plugin was first added to WordPress.
* Check the number of downloads on the sidebar. The original plugin always gets more downloads than the copycats.
* Check the number of ratings. The original plugin should have more votes.
* Check the developer's site.

== Usage ==
1. To add an 'Add to Cart' button for a product, simply add the shortcode [wp_cart_button name="PRODUCT-NAME" price="PRODUCT-PRICE"] to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.

2. To add the 'Add to Cart' button on the sidebar or from other template files use the following function:
<?php echo print_wp_cart_button_for_product('PRODUCT-NAME', PRODUCT-PRICE); ?>
Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.

3. To add the shopping cart to a post or page (eg. checkout page) simply add the shortcode [show_wp_shopping_cart] to a post or page or use the sidebar widget to add the shopping cart to the sidebar. The shopping cart will only be visible in a post or page when a customer adds a product.

= Using Product Display Box =

Here is an exmaple shortcode that shows you how to use a product display box.

[wp_cart_display_product name="My Awesome Product" price="25.00" thumbnail="http://www.example.com/images/product-image.jpg" description="This is a short description of the product"]

Simply replace the values with your product specific data

= Using a compact shopping cart =

Add the following shortcode where you want to show the compact shopping cart:

[wp_compact_cart]

= Using Shipping =

1. To use shipping cost for your product, use the "shipping" parameter. Here is an example shortcode usage:
[wp_cart_button name="Test Product" price="19.95" shipping="4.99"]

or use the following php function from your wordpress template files
<?php echo print_wp_cart_button_for_product('product name',price,shipping cost); ?>

= Using Variation Control =

1. To use variation control use the variation parameter in the shortcode:
[wp_cart_button name="Test Product" price="25.95" var1="VARIATION-NAME|VARIATION1|VARIATION2|VARIATION3"]

example usage: [wp_cart_button name="Test Product" price="29.95" var1="Size|small|medium|large"]

2. To use multiple variation for a product (2nd or 3rd variation), use the following:

[wp_cart_button name="Test Product" price="29.95" var1="Size|small|medium|large" var2="Color|red|green|blue"]

[wp_cart_button name="Test Product" price="29.95" var1="Size|small|medium|large" var2="Color|red|green|blue" var3="Sleeve|short|full"]

== Installation ==

1. Unzip and Upload the folder 'wordpress-paypal-shopping-cart' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings and configure the options (for example: your email, Shopping Cart name, Return URL etc.)
4. Use the trigger text to add a product to a post or page where you want it to appear.

== Frequently Asked Questions ==
1. Can this plugin be used to accept paypal payment for a service or a product? Yes
2. Does this plugin have shopping cart? Yes.
3. Can the shopping cart be added to a checkout page? Yes.
4. Does this plugin has multiple currency support? Yes.
5. Is the 'Add to Cart' button customizable? Yes.
6. Does this plugin use a return URL to redirect customers to a specified page after Paypal has processed the payment? Yes.
7. How can I add a buy button on the sidebar widget of my site?
Check the documentation on [how to add buy buttons to the sidebar](http://www.tipsandtricks-hq.com/ecommerce/wordpress-shopping-cart-additional-resources-322#add_button_in_sidebar)
8. Can I use this plugin to sell digital downloads? 
Yes. See the [digital download usage documentation] (http://www.tipsandtricks-hq.com/ecommerce/wp-simple-cart-sell-digital-downloads-2468)
9. Can I configure discount coupon with this shopping cart plugin?
Yes. you can setup discount coupons from the "Coupon/Discount" interface of the plugin.
10. Can I configure product sale notification so I get notified when a sale is made? 
Yes. You can configure sale notification from the "Email Settings" interface of the plugin.
11. Can I modify the product box thumbnail image?
Yes.
12. Can I customize the format of the price display?
Yes.

== Screenshots ==
Visit the plugin site at https://www.tipsandtricks-hq.com/wordpress-simple-paypal-shopping-cart-plugin-768 for screenshots.

== Upgrade Notice ==

None

== Changelog ==

= 4.0.6 =
- Added an email tag to include the coupon code used in the notification email.
- Added an extra check to prevent a debug notice message from showing when the cart is reset.
- WordPress 4.1 compatibility.

= 4.0.5 = 
- Added two new filters to allow dynamic modification of the buyer and seller notification email body (just before the email is sent).
- Added a new filter so the orders menu viewing permission can be overridden by an addon.
- Added Danish Language translation to the plugin. The Danish translation file was submitted by Steve Jorgensen.
- Added a function to strip special characters from price parameter in the shortcode.

= 4.0.4 =
- Added some new email tags to show Transaction ID, Purchase Amount and Purchase Date (check your email settings field for details).
- Made some improvements to the PayPal IPN validation code.

= 4.0.3 =
- Fixed a few notices in the settings menu when run in debug mode.
- Fixed a warning notice on the front end when run in debug mode.

= 4.0.2 =
- Added a new option so you can store your custom language file for this plugin in a folder outside the plugin's directory.
- Added the following two new filters to allow customization of the add to cart button:
  wspsc_add_cart_button_form_attr
  wspsc_add_cart_submit_button_value
- Added Text Domain and Domain Path values to the plugin header.
- Added Norwegian language translation to the plugin. The Swedish translation file was submitted by Reidar F. Sivertsen.
- Added some security checks a) to make sure that the payment is deposited to the email specified in the settings  b) to block multiple payment notifications for the same transaction ID
- Buyer's contact phone number is now also saved with each order (given you have enabled it).
- Added the following new filter to allow customization of the product box shortcode:
  wspsc_product_box_thumbnail_code

= 4.0.1 =
- Added a new filter to format the price in the shopping cart. Example usage: 
  https://www.tipsandtricks-hq.com/ecommerce/customizing-price-amount-display-currency-formatting-3247
- WordPress 4.0 compatibility.

= 4.0.0 =
- Changed the permission on the orders menu so it is only available to admin users from the backend.
- Made some enhancement around the PHP session_start function call.
- Added an extra check to prevent direct access to the cart file.
- Added expiry date field in the discount coupon. You can now create discount coupons with an expiry.

= 3.9.9 =
- Added a new feature that allows you to show the product thumbnail image in the shopping cart. Use "show_thumbnail" parameter in the shopping cart shortcode for this.
- Added Swedish language translation to the plugin. The Swedish translation file was submitted by Felicia.
- Fixed a minor bug with the checkout page style feature.
- Added a new filter for the item name field in the shopping cart.
- Made some minor CSS improvements for the cart output.
- The {product_details} email shortcode will now show the full amount of the item (instead of the individual item amount).

= 3.9.8 =
- Added Hebrew Language translation to the plugin. The Hebrew translation file was submitted by Sagi Cooper.
- Added extra condition to address the "Invalid argument supplied" error that a few users were getting.

= 3.9.7 =
- Added a new feature to open the checkout page in a new tab/window when user clicks the checkout button.
- Updated the Cart Orders menu icon to use a slightly better looking dashicon.
- Added a new filter to allow modification of the custom field value. Filter name is wpspc_cart_custom_field_value
- Added a new action hook after the PayPal IPN is processed. This will allow you to do extra post payment processing task for your orders. Hook name wpspc_paypal_ipn_processed
- Made some improvements to some of the shopping cart icons (cart and delete item icons have been updated). 
- Cart output will work with a responsive theme.

= 3.9.6 =
- Added Czech Language translation to the plugin. The Czech translation file was submitted by Tomas Sykora.
- Added a new option/feature to specify a custom paypal checkout page style name. The plugin will use the custom checkout page style if you specify one.
- Each order now also shows the shipping amount in the order managment interface.

= 3.9.5 =
- Added a new feature that lets you (the site admin) configure a sale notification email for the admin. When your customer purchase a product, you get a notification email. Activate this feature from the "Email Settings" interface of the plugin.
- Added Polish language translation to the plugin. The Polish langage translation file was submitted by Gregor Konrad.
- Fixed a minor issue with custom button images that uses HTTPS URL.
- Added more CSS classes in the shopping cart so you can apply CSS tweaks easily.

= 3.9.4 =
- Fixed a minor bug in the new compact cart shortcode [wp_compact_cart]

= 3.9.3 =
- Added a new feature to show a compact shopping cart. You can show the compact shopping cart anywhere on your site (example: sidebar, header etc).
- Language translation strings updated. Translation instruction here - http://www.tipsandtricks-hq.com/ecommerce/translating-the-wp-simple-shopping-cart-plugin-2627
- Added a new function for getting the total cart item quantity (wpspc_get_total_cart_qty).
- Added a new function to get the sub total amount of the cart (wpspc_get_total_cart_sub_total).

= 3.9.2 =
- Added an option to specify a custom button image for the add to cart buttons. You can use the "button_image" parameter in the shortcode to customize the add to cart button image.
- Coupon code that is used in a transaciton will be saved with the order so you can see it in the back end.

= 3.9.1 =
- WP 3.8 compatibility

= 3.9.0 and 3.8.9 =
- WP Super Cache workaround - http://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart-and-wp-super-cache-workaround-334
- Added a new shortcode argument to specify a SKU number for your product.
- Fixed a few debug warnings/notices
- Added Italian language file

= 3.8.8 =
- Added a discount coupon feature to the shopping cart. You can now configure discount coupon via the Simple cart settings -> Coupon/Discount menu
- View link now shows the order details
- fixed a bug where the shipping price wasn't properly showing for more than $1000
- WordPress 3.7 compatibility

= 3.8.7 =
- Changed a few function names and made them unique to reduce the chance of a function name conflict with another plugin.
- Added a new option in the plugin so the purchased items of a transaction will be shown under orders menu
- Payment notification will only be processed when the status is completed.

= 3.8.6 =
- Updated the broken settings menu link
- Updated the NextGen gallery integration to return $arg1 rather than $arg2

= 3.8.5 =
- Added an email settings menu where the site admin can customize the buyer email that gets sent after a transaction
- Also, added the following dynamic email tags for the email body field:

{first_name} First name of the buyer
{last_name} Last name of the buyer
{product_details} The item details of the purchased product (this will include the download link for digital items).

= 3.8.4 =
- Fixing an issue that resulted from doing a commit when wordpress.org plugin repository was undergoing maintenance

= 3.8.3 =
- Improved the settings menu interface with the new shortcode usage instruction.

Full changelog for all versions can be found at the following URL:
http://www.tipsandtricks-hq.com/ecommerce/?p=319
