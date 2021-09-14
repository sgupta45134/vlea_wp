=== Plugin Name ===
Name: CM Micropayment Platform
Contributors: CreativeMindsSolutions
Donate link: https://www.cminds.com/
Tags: micropayments, virtual currency, api, pay per view, pay per action, paypal gateway, reports, transactions
Requires at least: 5.4.0
Tested up to: 5.7
Stable tag: 2.0.5

The sole purpose of this plugin is to allow the developers/administrators to make in-site transactions without the neccesity of processing the external payments each time (quicker & easier).
After the installation of the plugin every registered user is given his own wallet. Then after the quick setup of the external Payment Gateway and the currency itself,
users can buy the virtual currency on your site. Next they use their wallet as if it was a prepaid credit card.

== Description ==

CM Micropayment Platform adds the in-site support for your own “virtual currency“. Assign users with wallets, make them earn points through actions or make them buy them via PayPal then
let tehm spend their points for downloading files, viewing restricted articles or any other action you want to be paid for!

== Changelog ==

= 2.0.5 =
* Added ability to swap users wallets
* Added Notification about wallet exchange in the [cm_user_wallet] shortcode
* Added Email notification about wallet exchange
* Added Wallet for fees
* Added Wallet for purchases

= 2.0.4 =
* Fixed package shortcode

= 2.0.3 =
* Updated package 1.9.1

= 2.0.2 =
* Added support to periodic % based grant points according to the number of points each wallet holds. 

= 2.0.1 =
* Fixed issue related to purchasing points with woocommerce gateways

= 2.0.0 =
* Added support to show the input field instead of radio selection for purchase points

= 1.9.9 =
* Implement nonce into pay tip function

= 1.9.8 =
* Fixed: user wallet pagination

= 1.9.7 =
Dev: New action "cm-micropayments-checkout-form" in checkout form

= 1.9.6 =
Fixed: send payment currency
Added: Option to select final order status of purchased Woocommerce product
Added: option 'Order status after payment with micropayment points' to set status of woocommerce order
Added: feature to manually set amount of purchased points
Added: action hook to labels tab and label for Payment Threshold
Added: Payment threshold

= 1.9.5 =
* Fixed: Plugin Report page view
* Added: Payment threshold

= 1.9.4 =
* Fixed: Table ('_defined_points_cost') column name `points` changed to `points_value` in select code
* Fixed: Plugin settings view

= 1.9.3 =
* Fixed: External wallets synchronization bug

= 1.9.2 =
* Fixed: DB query optimization

= 1.9.1 =
* Fixed: issues related to Stripe payout

= 1.9.0 =
* Added: option to limit wallets quantity for user
* Added two stripe missing labels and added currency for stripe payout

= 1.8.12 =
* Fixed: scripts enqueue order
* Fixed: Tablesorter jquery lib call

= 1.8.11 =
* Fixed: privacy access issues
* Improved: pagination row for transaction history view
* Added: missing label for Stripe payout and applied round decimals to stripe payout values
* Added: option to multiply granted points by quantity of woocommerce purchased product
* Added: option to show transaction history comment column
* Added: locale dependant numbers format in frontend
* Minor fixes

= 1.8.10 =
* Fixed: transaction fee processing
* Fixed: stripe payout value format
* Fixed: email notification shortcodes
* Added: percents feature to transaction fee
* Added: Transfer comment label as option
* Added: Stripe payout label as option
* Minor fixes

= 1.8.9 =
* Fixed: enqueue scripts issue
* Added: bulk external wallets update

= 1.8.8 =
* Added hook in emailtemplates.phtml and made changes in the hasUserEnoughPoints method
* Minor fixes

= 1.8.7 =
* Bugfix related to transfer recipient selection field
* Updated package 1.9.0

= 1.8.6 =
* Improved micropayment section on user profile page

= 1.8.5 =
* Improved transfer recipient selection field

= 1.8.4 =
* Added new shortcode cmmp_tip_button with parameters

= 1.8.3 =
* Added new currency "UGX - Ugandan Shilling" in settings
* Updated package 1.8.10

= 1.8.2 =
* Compatible with WP All Import/Export plugin

= 1.8.1 =
* Fixed correct version in readme file

= 1.8.0 =
* Fixed some typo mistakes
* Fixed decimal support
* Added Dokan integration
* Added percentage fee for admin
* Reorganized some options

= 1.7.2 =
* Added the support for managing the External Wallets (with External API plugin)

= 1.7.1 =
* Bugfix related to double entry in transaction section
* Updated order date format while save

= 1.7.0 =
* Added support to set optional add points to admin wallet on purchase

= 1.6.9 =
* Added support to add comment box in transfer_wallet_points shortcode
* Added support to add points to admin wallet

= 1.6.8 =
* Updated stripe library with latest version
* Improved stripe payout feature
* Added new parameter connectbutton into cm_micropayment_points_to_stripe shortcode
* Fixed some warnings

= 1.6.7 =
* Added stripe points ratio setting
* Bugfix related to show wallet data into user section in admin

= 1.6.6 =
* Bugfix related to filters
* Bugfix in transfer points between users feature

= 1.6.5 =
* Added new currency "MAD - Moroccan Dirham" in settings
* Added option to manually accept the pending transactions requests

= 1.6.4 =
* Bugfix related to checkout with anonymous user
* Added Wallet ID in transactions and wallets list
* Improvement in paypal payout feature 

= 1.6.3 =
* Added new currency "VND - Vietnamese Dong" in settings

= 1.6.2 =
* Added new addon info
* Added new labels
* Added an option to charge transaction fee on each transaction.

= 1.6.1 =
* Bugfix related to session transaction id
* Updated package 1.8.9

= 1.6.0 =
* Bugfix related to points header text in cm_user_wallet shortcode

= 1.5.9 =
* Removed deprecated notice
* Fixed some typo mistakes

= 1.5.8 =
* Bugfix in WooCommerce purchase grant feature
* Updated package 1.8.8

= 1.5.7 =
* Added new labels
* Updated package 1.8.7

= 1.5.6 =
* Bugfix related to charge a wallet

= 1.5.5 =
* Removed initSession

= 1.5.4 =
* Updated license package
* Fixed the [cm_micropayment_points_to_woo_discount] and [cm_micropayment_points_woo_discounts] shortcodes

= 1.5.3 =
* Updated license package
* Improved settings page UI with CSS

= 1.5.2 =
* Bugfix related to edd purchase session

= 1.5.1 =
* Add third decimal support to point cost column
* Fixed wallet name in transactions
* Removed deprecated functions

= 1.5.0 =
* Added Stripe payout method
* New shortcode [cm_wallet_id] was added
* Added show_wallet_id attribute to [cm_user_wallet] shortcode which allows to display user's wallet id
* Added pointlabel attribute to the [cm_user_balance] shortcode
* Updated "My wallet" page content
* Improved the [create_wallet_button]
* Fixed bug in displaying labels description
* Added point labels

= 1.4.23 =
* Fixed the rare bug in the multisite activation

= 1.4.22 =
* Feature: Added new filter allowing to get the wallet balance by user ID: eg. apply_filters('cmmt_get_wallet_points_by_id', $user_id);

= 1.4.21 =
* Feature: Added the user selection to the [transfer_wallet_points] shortcode when the user-wallet assignment is enabled

= 1.4.20 =
* Fixed PHP error with preparing SQL string.
* Updated licensing library.

= 1.4.19 =
* Added missing labels for EDD and WooCommerce gateways
* Updated the licensing package

= 1.4.18 =
* Fixed the labels in [transfer_wallet_points] shortcode
* Removed the Wallet From in the [transfer_wallet_points] shortcode
* Updated the licensing package

= 1.4.17 =
* Added the option to set the Currency Symbol

= 1.4.16 =
* Fixed the problem with the transaction links on the https pages

= 1.4.15 =
* Added the ability to remove the pending transactions from PayPal history

= 1.4.14 =
* Fixed the value calculation for [cm_user_balance_value] shortcode

= 1.4.13 =
* Added the option to replace the default currency with the points for WooCommerce
* Added the option to replace the default currency with the points for Easy Digital Downloads
* Minor bug fixes

= 1.4.12 =
* Added the option to remove all wallets
* Fixed the bug with searching the wallets by user login
* Added the support for WooCommerce Membership
* Fixed small bugs

= 1.4.11 =
* Small bug fixed

= 1.4.10 =
* Fixed bugs with Adding the points

= 1.4.9 =
* Added the option to disable anonymous payments
* Added the support for new AddOn
* Updated the Licensing Package
* Added the option for Periodic Cron operations

= 1.4.8 =
* Fixed the problem with the function redeclaration

= 1.4.7 =
* Fixed bugs with WooCommerce integration
* Made some UI improvements in shortcodes layouts

= 1.4.6 =
* Added the option to change the label for the WooCommerce checkout button

= 1.4.5 =
* Changed the conflicting JS variable name to fix errors

= 1.4.4 =
* Added the option to import the wallets and grant points
* Added the option to import the wallets and set amount of points
* Added the option to export the existing wallets
* Added the option to charge/grant all wallets automatically every X days

= 1.4.2 =
* Added the COP Colombian Peso currency
* Fixed the dates in Wallet Transactions to match server

= 1.4.1 =
* Fixed JS bug in Wallet dashboard page

= 1.4.0 =
* Added the option to disable the built-in PayPal
* Fixed the bugs on Notification tab in settings
* Moved the available filters from Installation Guide to Shortcodes tab
* Fixed small bugs

= 1.3.11 =
* Fixed the bug with the EDD Gateway renaming

= 1.3.10 =
* Fixed the problem with the EDD Payment Gateway

= 1.3.9 =
* Fixed many notices in Points Adding screens when no currency was selected
* Fixed some bugs in PayPal Payouts
* Improved the error reporting for PayPal Payouts

= 1.3.8 =
* Added the support for virtual currency in Easy Digital Downloads
* Added the support for virtual currency in WooCommerce
* Added the support for MultiSite
* Fixed the bug with the PayPal Payouts

= 1.3.7 =
* Added the option to export the wallets list to CSV
* Added the option to generate the WooCommerce discounts

= 1.3.5 =
* Added the Payment Gateway for WooCommerce
* Added the shortcode [transfer_wallet_points] allowing to transfer points between wallets
* Fixed the performance of "Generate Missing Wallets"

= 1.3.1 =
* Added basic support for WooCommerce (ability to buy points with WooCommerce gateways)
* Fixed ordering of the transactins on the backend
* Fixed the bug with the points not being added correctly on the PayPal EDD transactions
* Changed the number of items in Dashboard lists to 10 per page from 5
* Added the edit links next to the page selects in options

= 1.3.0 =
* Removed the check for the checkout shortcode when the EDD integration is active

= 1.2.9 =
* Fixed the rare error which appeared during purchase of the points

= 1.2.8 =
* Fixed bug with output_buffering resulting with [cm_user_wallet] displaying twice
* Fixed the small bugs in the PayPal Payout functionality

= 1.2.7 =
* Fixed bug with missing constant
* Updated the Licensing API
* Cleaned up the old links in the plugin's settings, about etc.
* Added the PayPal Payouts system
* Added the new options to setup the PayPal Payouts in Settings -> PayPal
* Added the new shortcode [cm_micropayment_points_to_paypal] allowing users to exchange their points for money

= 1.2.6 =
* Added the support for MultiSite Wordpress installation
* Improved performance

= 1.2.5 =
* Fixed the XSS vulnerability in Wordpress add_query_arg() and remove_query_arg() functions
* Fixed the problem with double wallets being created for the new users

= 1.2.4 =
* Added the option to override the Payment Gateways of Easy Digital Downloads
* Added the e-mail template for e-mails sent after the successfully EDD purchase
* Fixed some bugs and notices

= 1.2.3 =
* Added the new shortcode: [cm_user_balance_value] which shows the value of the points in the currency
* Added the option to grant points per Easy Digital Downloads purchase
* Added the option to convert the points to Easy Digital Downloads discounts
* Added the two new shortcodes: [cm_micropayment_points_to_discount] and [cm_micropayment_points_discounts] (see plugins "Help" for description)
* Added the new shortcode: [cm_micropayment_buy_more_link] which shows the link to the page where the points can be bought

= 1.2.2 =
* Fixed the issue: front-end methods were not available when requesting by AJAX.

= 1.2.1 =
* Fixed the bug with adding the points after Easy Digital Downloads purchase
* Added the option to manually fix the association between point packages and Easy Digital Downloads products (in products metabox)

= 1.2.0 =
* Improved the licensing API
* Fixed the bug with 'headers already sent' on the checkout

= 1.1.9 =
* Fixed issue with inconsistent labels.

= 1.1.8 =
* Fixed the bug regarding the notice about Easy Digital Downloads integration
* Added the option to manually check for the plugin updates

= 1.1.7 =
* Fixed the frequency the plugin checks for the update

= 1.1.6 =
* Fixed a small bug with the singular/plural point names

= 1.1.5 =
* Added the User login column in the "Manage Wallets" admin page
* Fixed some bugs
* Added new filters
* Added the new transaction type to better support the Easy Digital Downloads purchases

= 1.1.3 =
* Fixed the problem with manually changing the points on the Wallets admin page
* Added the option to setup the initial amount of points in the newly created Wallet

= 1.1.2 =
* Added some missing messages

= 1.1.1 =
* New filters added

= 1.1.0 =
* Added the Easy Digital Downloads integration
* Fixed some bugs
* Changed the General Settings interface

= 1.0.0 =
* First release of the plugin