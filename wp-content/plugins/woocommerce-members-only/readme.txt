=== WooCommerce Members Only ===
Contributors: Gareth Harris
Tags: memberships, private store, restricted content
Requires at least: 4.7
Tested up to: 5.7
Stable tag: 1.10.1
Create private stores and membership sites with WooCommerce

== Description ==

Create private stores and membership sites with WooCommerce

== Installation ==
1. Upload the `woocommerce-members-only` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==


== Screenshots ==

1.

== To Do ==

* Membership tab on Account page, displaying privileges etc?
* Members Only roles summary in admin, displaying privileges etc?
* Restrict access to pages
* drip content
* automatically email the password
* membership status tab in my account page
* prompt to create password form page on installation
* apply protection to all categories and post types
* conditional landing pages
* better password success/failure messages and redirect options
* restrict categories by user ID

== Changelog ==

= 1.10.1, 26 July 2021 =
* Added: wcmo_locally_restricted_categories transient
* Fixed: ensure transient expiry doesn't affect user ID restrictions

= 1.10.0, 23 June 2021 =
* Added: support for role assignment fields in variable products
* Added: GUI to add registration fields
* Added: upload fields to registrations
* Added: description in registration fields
* Added: option to add registration fields to admin notification emails
* Added: save custom registration fields in user profiles
* Added: option to set reminder date
* Added: reminder email for expiring memberships
* Fixed: ensure correct products returned in [products] shortcode

= 1.9.18, 5 April 2021 =
= Fixed: parse errors in functions-products.php

= 1.9.17, 25 March 2021 =
* Added: wcmo_ignore_hidden_roles_non_logged_in filter
* Added: wcmo_ignore_shipping_for_non_logged_in filter
* Added: wcmo_ignore_payments_for_non_logged_in filter
* Added: wcmo_ignore_restricted_payments_for_non_logged_in filter
* Fixed: posts with multiple categories not restricted correctly
* Fixed: hidden user roles not restricting individual products correctly
* Updated: Billing and Shipping State fields in registration form
* Updated: removed wcmo_admin_notices

= 1.9.16, 16 November 2020 =
* Added: integration with Product Table Ultimate
* Fixed: certain products still purchasable when method set to all
* Updated: allow users to access categories pages when wcmo_allow_view_products is set

= 1.9.15, 6 November 2020 =
* Fixed: prevent unrestricted products from redirecting to log-in page

= 1.9.14, 3 November 2020 =
* Added: wcmo_get_transient_expiration
* Added: wcmo_products_restricted_to_current_user_archive transients
* Added: wcmo_get_users_expiration_dates shortcode
* Fixed: parse error in functions-registration.php
* Fixed: correctly display custom user fields on profile page
* Fixed: widgets not hidden for restricted users
* Fixed: correctly update capabilities
* Fixed: user-role restriction on entire site not setting correctly
* Updated: licence key notifications

= 1.9.13, 6 October 2020 =
* Added: display custom registration fields in user profile
* Added: select field option for new registration fields

= 1.9.12, 22 September 2020 =
* Added: wcmo_user_needs_approval filter
* Added: further registration fields and priority option

= 1.9.11, 19 September 2020 =
* Added: extra registration fields

= 1.9.10, 18 September 2020 =
* Added: hide product price in widgets
* Fixed: remove add to cart button from restricted variable products
* Fixed: parse error in functions-registration.php
* Updated: performance improvement when entire site is restricted

= 1.9.9, 29 July 2020 =
* Added: referring page parameter to wcmo_replacement_add_to_cart_button

= 1.9.8, 23 July 2020 =
* Fixed: respect Access Product Pages setting

= 1.9.7, 14 July 2020 =
* Fixed: products restricted to user IDs displaying in archive pages

= 1.9.6, 17 June 2020 =
* Added: Access Product Pages and Redirect from Add to Cart Button settings
* Added: wcmo_after_assign_roles_after_purchase action
* Added: wcmo_after_update_users_role action
* Fixed: capabilities not displaying on Roles screen
* Updated: check for 'Pending' role in wcmo_email_additional_content_customer_new_account

= 1.9.5, 14 May 2020 =
* Added: wcmo_registration_form_role_label to change label for 'Role' field on registration form
* Added: automatically create 'pending' role if pending users barred from logging in
* Added: notice to pending users when they register

= 1.9.4, 23 April 2020 =
* Added: hide prices option
* Fixed: error message when user logs in without a role
* Fixed: empty registration fields not saving correctly
* Updated: prevent non-admins from editing role capabilities
* Updated: prevent the administrator role from being an option in the registration form
* Updated: translation files

= 1.9.3, 14 April 2020 =
* Added: wcmo_assign_roles_order_status option
* Fixed: payment and shipping methods not saving empty values
* Updated: prevent administrator role being set as new user role

= 1.9.2, 1 April 2020 =
* Update: check product is_object in wcmo_update_excluded_products_transient

= 1.9.1, 18 March 2020 =
* Fixed: changed is_numeric to is_object in wcmo_update_excluded_products_transient
* Fixed: shipping and payment settings not saving empty values
* Updated: retain wcmo_referrer param in URL when user enters incorrect password
* Updated: check WooCommerce is active

= 1.9.0, 3 March 2020 =
* Added: registration fields
* Added: allow user approval for specific user roles
* Added: wcmo_after_expired_user_remove_role
* Added: wcmo_new_registration_email_recipient filter
* Fixed: correctly set multiple roles for approved users
* Fixed: correctly remove 'Pending' role from approved users

= 1.8.5, 20 February 2020 =
* Fixed: redirecting to last product in query instead of product category

= 1.8.4, 14 February 2020 =
* Fixed: redirecting to last product in query instead of shop page

= 1.8.3, 12 February 2020 =
* Fixed: products restricted by user ID not displaying correctly in archives for logged out users

= 1.8.2, 10 February 2020 =
* Fixed: products restricted by user ID not displaying in archives

= 1.8.1, 27 January 2020 =
* Added: wcmo_role_expiration_date filter
* Added: Role based shipping methods

= 1.8.0, 25 January 2020 =
* Added: Expiration dates for membership roles
* Added: Role based payment methods
* Fixed: wcmo_update_excluded_products_transient queries incorrectly looking for product object

= 1.7.3, 24 January 2020 =
* Fixed: parse error in functions-admin-products.php when saving products
* Updated: removed fields parameter from wc_get_products in wcmo_update_product_passwords_transient

= 1.7.2, 20 January 2020 =
* Added: Italian translation
* Fixed: incorrect parameter in WP_Query

= 1.7.1, 9 January 2020 =
* Added: email user on approval or rejection
* Fixed: local passwords not working for non-logged-in users
* Fixed: issue with WooCommerce shortcodes displaying incorrect products

= 1.7.0, 12 December 2019 =
* Added: initial support for WooCommerce subscriptions
* Added: wcmo_remove_existing_roles filter to replace previous user roles
* Added: multiple roles support
* Added: option to prevent pending and rejected users from logging in
* Added: option to prevent automatic log in
* Added: set different default roles

= 1.6.3, 26 November 2019 =
* Added: approve / reject new user registrations

= 1.6.2, 20 November 2019 =
* Added: conditionally check menu items for user roles
* Added: redirect to referring page after logging in
* Fixed: excluded menu items respecting user role

= 1.6.1, 19 November 2019 =
* Fixed: parse error in products shortcode

= 1.6.0, 16 November 2019 =
* Added: restrict posts
* Added: hide products by user role
* Updated: set default method to no restriction
* Updated: assign user role on woocommerce_order_status_processing

= 1.5.0, 31 October 2019 =
* Added: restrict products by user roles
* Fixed: correctly remove restricted products from shortcode
* Fixed: correctly remove restricted products from WP_Query

= 1.4.2, 8 October 2019 =
* Updated: product tab ID for compatibility with Add-Ons Ultimate
* Updated: renamed wcmo_excluded_products transient to wcmo_products_restricted_by_user

= 1.4.1, 8 September 2019 =
* Fixed: prevent redirection from admin
* Fixed: correctly validate password

= 1.4.0, 16 August 2019 =
* Added: automatically assign roles on purchasing products

= 1.3.5, 13 August 2019 =
* Added: $password parameter to wcmo_redirect_url filter
* Fixed: set wcmo_excluded_products transient using WP_Query
* Updated: set autocomplete to off on password field

= 1.3.4, 28 June 2019 =
* Fixed: correctly redirect after logging in

= 1.3.3, 28 June 2019 =
* Fixed: user roles not correctly blocked from content

= 1.3.2, 21 June 2019 =
* Fixed: timeout error

= 1.3.1, 6 June 2019 =
* Fixed: correctly exclude authorised products from restrictions

= 1.3.0, 6 June 2019 =
* Added: checkbox to opt into hiding widgets
* Added: alternative label for add to cart button on restricted products
* Added: product restriction method column in products list
* Fixed: correctly check for restricted products on All Product Pages
* Fixed: correctly restrict locally protected products from archive pages
* Fixed: too many redirects when trying to access log-in page
* Updated: restructured settings page into sections

= 1.2.0, 1 April 2019 =
* Added: Members Only product panel
* Added: restrict product by user ID
* Fixed: incorrectly named password session variable

= 1.1.4, 28 March 2019 =
* Fixed: Updater error

= 1.1.3, 27 March 2019 =
* Fixed: JS error in admin

= 1.1.2, 26 March 2019 =
* Fixed: too many redirects issue

= 1.1.1, 26 March 2019 =
* Fixed: redirect not working correctly for some blocked content

= 1.1.0, 6 March 2019 =
* Added: individual category settings
* Updated: use term_id instead of slug for protected categories

= 1.0.2, 21 February 2019 =
* Added: hide restricted products in archives

= 1.0.1, 15 February 2019 =
* Added: pot file
* Added: licensing
* Updated: changed default restriction method to log-in-status

= 1.0.0, 11 December 2018 =
* Initial commit

== To Do ==
basic licence - private store functionality
pro licence - extended membership functionality
- woocommerce memberships
- woocommerce private store
password protect globally or by individual passwords
restrict access to woocommerce by user role
restrict access to woocommerce store, pages, products, categories
restrict access by log in status
hide or show pages in menus

set restriction levels - e.g. hidden, view only (no purchase)
redirect, show modal as content tease

restrict by membership plan
restrict by subscription level
redirect global setting or page by page
customizer styles
---
drip content
restrict by how long users have been members / how long since they registered
free shipping for members
discounts for members
add members tab to account page

== Upgrade Notice ==

Nothing here
