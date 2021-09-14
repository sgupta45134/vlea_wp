<?php

namespace com\cminds\payperposts\model;

class Settings extends SettingsAbstract {

	const TYPE_MP_PRICE_GROUPS = 'mp_price_groups';
	const TYPE_LIST_KEY_VALUE = 'list_key_value';
	const TYPE_MP_PRICE_GROUPS_BULK = 'mp_price_groups_bulk';

	const OPTION_SUPPORTED_POST_TYPES = 'cmppp_supported_post_types';
	const OPTION_MICROPAYMENTS_AUTHORS = 'cmppp_mp_authors';
	const OPTION_MICROPAYMENTS_AUTHORS_DONATION = 'cmppp_mp_authors_donation';
	const OPTION_MICROPAYMENTS_GROUPS = 'cmppp_mp_groups';
	const OPTION_EDD_PRICING_GROUPS = 'cmppp_edd_pricing_groups';
	const OPTION_WOO_PRICING_GROUPS = 'cmppp_woo_pricing_groups';
	const OPTION_SUBSCRIPTION_MODE = 'cmppp_subscription_mode';
	const OPTION_HIDE_PAGE_CONTENT = 'cmppp_hide_page_content';
	const OPTION_HIDE_PAGE_CONTENT_ID = 'cmppp_hide_page_content_id';
	const OPTION_HIDE_PAGE_CONTENT_ADDITIONAL_BLOCKS = 'cmppp_hide_page_content_additional_blocks';
	const OPTION_NEW_SUB_CUSTOMER_NOTIF_ENABLE = 'cmppp_new_sub_customer_nofif_enable';
	const OPTION_NEW_SUB_CUSTOMER_NOTIF_SUBJECT = 'cmppp_new_sub_customer_nofif_subject';
	const OPTION_NEW_SUB_CUSTOMER_NOTIF_TEMPLATE = 'cmppp_new_sub_customer_nofif_template';
	const OPTION_PURCHASE_CONFIRMATION_NOTIF_ENABLE = 'cmppp_purchase_confirmation_nofif_enable';
	const OPTION_PURCHASE_CONFIRMATION_NOTIF_SUBJECT = 'cmppp_purchase_confirmation_nofif_subject';
	const OPTION_PURCHASE_CONFIRMATION_NOTIF_TEMPLATE = 'cmppp_purchase_confirmation_nofif_template';
	const OPTION_SUBSCRIPTION_EXPIRE_ENABLE = 'cmppp_subscription_expire_nofif_enable';
	const OPTION_SUBSCRIPTION_EXPIRE_SUBJECT = 'cmppp_subscription_expire_nofif_subject';
	const OPTION_SUBSCRIPTION_EXPIRE_TEMPLATE = 'cmppp_subscription_expire_nofif_template';
	const OPTION_SUBSCRIPTION_EXPIRE_DATE = 'cmppp_subscription_expire_date_template';
	const OPTION_NEW_SUB_ADMIN_NOTIF_ENABLE = 'cmppp_new_sub_admin_nofif_enable';
	const OPTION_NEW_SUB_ADMIN_NOTIF_EMAILS = 'cmppp_new_sub_admin_nofif_emails';
	const OPTION_NEW_SUB_ADMIN_NOTIF_SUBJECT = 'cmppp_new_sub_admin_nofif_subject';
	const OPTION_NEW_SUB_ADMIN_NOTIF_TEMPLATE = 'cmppp_new_sub_admin_nofif_template';
	const OPTION_HIDE_COMMENTS = 'cmppp_hide_comments';
	const OPTION_FADE_ENABLED = 'cmppp_fade_enabled';
	const OPTION_REFUND_ENABLED = 'cmppp_refund_enabled';
	const OPTION_REFUND_REASONS = 'cmppp_refund_reasons';
	const OPTION_REFUND_TIMEOUT_MINUTES = 'cmppp_refund_timeout';
	const OPTION_USE_POST_EXCERPT = 'cmppp_use_post_excerpt';
	const OPTION_USE_POST_PERCENT = 'cmppp_use_post_percent';
	const OPTION_SHOW_FULL_POST_IN_PREVIEW = 'cmppp_show_full_post_in_preview';
	const OPTION_SHOW_FULL_POST_FOR_SEARCH_ENGINES = 'cmppp_show_full_post_for_search_engines';
	const OPTION_RESTRICT_COPYING_CONTENT = 'cmppp_restrict_copying_content';
	const OPTION_ALLOWED_SEARCH_ENGINES = 'cmppp_allowed_search_engines';
	const OPTION_RELOAD_EXPIRED_SUBSCRIPTION = 'cmppp_reload_expired_subscription';
	const OPTION_SUBSCRIPTION_FORM_FOR_GUEST_USER = 'cmppp_subscription_form_for_guest_user';
	const OPTION_AUTO_REGISTER_AND_LOGIN_USER = 'cmppp_auto_register_and_login_user';
	const OPTION_LOGIN_FORM_ENABLE = 'cmppp_login_form_enable';
	const OPTION_SUBSCRIPTION_FORM_NOT_FOR_OWNER = 'cmppp_subscription_form_not_for_owner';

	const OPTION_SUBSCRIPTION_LIMIT_ENABLE = 'cmppp_subscription_limit_enable';
	const OPTION_SUBSCRIPTION_LIMIT_NUMBER = 'cmppp_subscription_limit_number';
	const OPTION_SUBSCRIPTION_LIMIT_NUMBER_MESSAGE = 'cmppp_subscription_limit_number_message';

	const OPTION_BULK_PAYMENT_METHOD = 'cmppp_bulk_payment_method';
	const OPTION_BULK_PAYMENT_BY_POST_OR_BULK = 'cmppp_bulk_payment_by_post_or_bulk';
	const OPTION_BULK_PAYMENT_SPECIFIC_CATEGORIES = 'cmppp_bulk_payment_specific_categories';
	const OPTION_ENABLE_CATEGORIES_PRICES = 'cmppp_enable_categories_prices';
	const OPTION_BULK_OPTIONS = 'cmppp_bulk_options';
	const OPTION_PERCENTAGE = 'cmppp_percentage';
	const PERCENTAGE_ON = 'percentage_on';
	const PERCENTAGE_OFF = 'percentage_off';
	const OPTION_PERCENT_OF_POINTS_TO_AUTHOR = 'cmppp_percent_of_points_to_author';

	const OPTION_POST_PRICES_COLUMN_SHOW = 'cmppp_post_prices_column_show';
	const OPTION_POST_PRICING_GROUP_COLUMN_SHOW = 'cmppp_post_pricing_group_column_show';

	const OPTION_SHOW_MESSAGE_YOU_HAVE_BOUGHT_THE_POST = 'cmppp_show_message_you_have_bought_the_post';
	// const OPTION_SHOW_MESSAGE_YOU_HAVE_PREPAID_ALL = 'cmppp_show_message_you_have_prepaid_all';

	const OPTION_AUTOREDIRECT_TO_PAID_POST = 'cmppp_autoredirect_to_paid_post';
	const OPTION_AUTOREDIRECT_TO_PAID_POST_SECONDS = 'cmppp_autoredirect_to_paid_post_seconds';

	const HIDE_CONTENT_OFF = 0;
	const HIDE_POST_THE_CONTENT = 'the_content';
	const HIDE_FULL_PAGE_CONTENT = 'full_page_content';
	const HIDE_SPECIFIED_BLOCK = 'specified_block';

	const SUBSCRIPTION_MODE_POST = 1;
	const SUBSCRIPTION_MODE_PRICING_GROUP = 2;
	const SUBSCRIPTION_MODE_PRICING_GROUP_OR_POST = 3;

	const OPTION_STYLES_PAYBOX_ENABLED = 'cmppp_styles_paybox_enabled';
	const OPTION_STYLES_PAYBOX_TEXT_COLOR = 'cmppp_styles_paybox_color';
	const OPTION_STYLES_PAYBOX_WIDTH = 'cmppp_styles_paybox_width';
	const OPTION_STYLES_PAYBOX_BACKGROUND_COLOR = 'cmppp_styles_paybox_background_color';
	// const OPTION_STYLES_PAYBOX_BORDER = 'cmppp_styles_paybox_border';
	const OPTION_STYLES_PAYBOX_BORDER_COLOR = 'cmppp_styles_paybox_border_color';

	const STYLES_PAYBOX_WIDTH_DEFAULT = 'default';
	const STYLES_PAYBOX_WIDTH_FULL = 'full';

	const OPTION_STYLES_CHECKOUT_BUTTON_ENABLED = 'cmppp_styles_checkout_button_enabled';
	const OPTION_STYLES_CHECKOUT_BUTTON_TEXT_COLOR = 'cmppp_styles_checkout_button_text_color';
	const OPTION_STYLES_CHECKOUT_BUTTON_BG_COLOR = 'cmppp_styles_checkout_button_bg_color';
	const OPTION_STYLES_CHECKOUT_BUTTON_BORDER_COLOR = 'cmppp_styles_checkout_button_border_color';
	const OPTION_STYLES_CHECKOUT_BUTTON_HOVER_BORDER_COLOR = 'cmppp_styles_checkout_button_hover_border_color';
	const OPTION_STYLES_CHECKOUT_BUTTON_HOVER_TEXT_COLOR = 'cmppp_styles_checkout_button_hover_text_color';
	const OPTION_STYLES_CHECKOUT_BUTTON_HOVER_BG_COLOR = 'cmppp_styles_checkout_button_hover_bg_color';
	const OPTION_STYLES_CHECKOUT_BUTTON_HIDE_BORDER = 'cmppp_styles_checkout_button_hide_border';

	public static $categories = array(
		'general'       => 'General',
		'pricing'       => 'Pricing',
		'refund'        => 'Refund',
		'notifications' => 'Notifications',
		'styles'        => 'Styles',
		'labels'        => 'Labels',
	);

	public static $subcategories = array(
		'general'       => array(
			'subscriptions'     => 'Subscriptions',
			'post_availability' => 'Post Availability',
			'post_types'        => 'Post types',
			'crawlers'          => 'Search engines',
			'copying_content'   => 'Restrict copying content',
			'messages'          => 'Messages',
		),
		'pricing'       => array(
			'micropayments' => 'CM Micropayments Points',
			'edd'           => 'EDD Payments',
			'woo'           => 'WooCommerce Payments',
			'post'          => 'Post',
			'bulk'          => 'Bulk',
			'percent'       => 'Author\'s share'
		),
		'refund'        => array(
			'refund' => 'Refund',
		),
		'notifications' => array(
			'csub' => 'New subscription (For Customer)',
			'purch' => 'Confirmation of purchase (For Customer)',
			'expire' => 'Subscription is about to expire (For Customer)',
			'sub'  => 'New subscription (For Admin)',
		),
		'styles' => array(
			'paybox' => 'Paybox',
			'checkout_button' => 'Checkout button',
		),
	);


	public static function getOptionsConfig() {

		return apply_filters( 'cmppp_options_config', array(

			// General
			Settings::OPTION_SUPPORTED_POST_TYPES => array(
				'type'        => Settings::TYPE_MULTICHECKBOX,
				'options'     => Settings::getPostTypesOptions(),
				'default'     => array( 'post', 'page' ),
				'category'    => 'general',
				'subcategory' => 'post_types',
				'title'       => 'Supported post types',
			),
			Settings::OPTION_SUBSCRIPTION_MODE    => array(
				'type'        => Settings::TYPE_RADIO,
				'options'     => array(
					Settings::SUBSCRIPTION_MODE_POST                  => 'Pay per each post',
					Settings::SUBSCRIPTION_MODE_PRICING_GROUP         => 'Pay per pricing group',
					Settings::SUBSCRIPTION_MODE_PRICING_GROUP_OR_POST => 'Pay per pricing group or single post',
				),
				'default'     => Settings::SUBSCRIPTION_MODE_PRICING_GROUP_OR_POST,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Subscription model',
				'desc'        => 'Choose the subscription model.<br />'
				                 . '"<strong>Pay per each post</strong>" - user have to activate subscription for each post.<br />'
				                 . '"<strong>Pay per pricing group</strong>" - user have to activate subscription once for pricing group and he will have access '
				                 . 'to all posts associated with this pricing group.<br />'
				                 . '"<strong>Pay per pricing group or single post</strong>" - user able to see both subscription options (price per single and per group access) on post/page.',
			),
			Settings::OPTION_HIDE_PAGE_CONTENT    => array(
				'type'        => Settings::TYPE_RADIO,
				'options'     => array(
					Settings::HIDE_CONTENT_OFF       => 'Show only part of the content (more options)',
					Settings::HIDE_POST_THE_CONTENT  => 'Hide content',
					Settings::HIDE_FULL_PAGE_CONTENT => 'Hide full page',
					Settings::HIDE_SPECIFIED_BLOCK   => 'Hide specific page elements (more options)',
				),
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'What should be hidden in the frontend before purchase?',
				'desc'        => 'The following content will be replaced by the payboxes.
				<div><b>"Show only part of the content (more options)"</b> -  Specify how much of the content is hidden. <br></div>
				<div><b>"Hide only content"</b> - Hides the complete content.<br></div>
				<div><b>"Hide full page"</b> - Hides the full page, including content, header, and footer.</div>
				<div><b>"Hide specific page elements (more options)"</b> - Specify which elements of the page are hidden.</div>',
			),

			Settings::OPTION_USE_POST_EXCERPT => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 1,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Use post excerpt',
				'desc'        => 'Displays the post excerpt, if available. If disabled, displays part of the content (set below).',
			),
			Settings::OPTION_USE_POST_PERCENT => array(
				'type'        => Settings::TYPE_INT,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Show first x% content',
				'desc'        => 'Shows only this percentage of the content. Only works if "use post excerpt" is disabled.',
			),
			Settings::OPTION_FADE_ENABLED     => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 1,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Apply fade out effect',
				'desc'        => 'Applies a fade out effect to gradually hide the content.'
			),

			Settings::OPTION_HIDE_PAGE_CONTENT_ID                => array(
				'type'                => Settings::TYPE_STRING,
				'default'             => '',
				'required'            => 1,
				'required_dependency' => [
					[ Settings::OPTION_HIDE_PAGE_CONTENT => Settings::HIDE_SPECIFIED_BLOCK ]
				],
				'category'            => 'general',
				'subcategory'         => 'subscriptions',
				'title'               => 'ID of Block to be replaced with payboxes',
				'desc'                => '<div>Set one element ID which will be replaced with payboxes. E.g. "main". Accepts only one ID that is not inside the post/page content</div>',
			),
			Settings::OPTION_HIDE_PAGE_CONTENT_ADDITIONAL_BLOCKS => array(
				'type'        => Settings::TYPE_STRING,
				'default'     => '',
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'ID of Blocks to be hidden',
				'desc'        => '<div>Set the element ID which will be hidden. To add multiple, separate them by commas. E.g "main, content, footer". The element cannot be inside the post/page content.</div>',
			),
			Settings::OPTION_HIDE_COMMENTS                       => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Hide comments before post purchase',
				'desc'        => 'If enabled, comments will be displayed only if user have active subscription for a specific post. If disabled, comments will be always visible.'
			),
			Settings::OPTION_SHOW_FULL_POST_IN_PREVIEW           => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 1,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Show full post in preview',
				'desc'        => 'If enabled, when showing the post preview (eg. for Draft posts) the post content won\'t be truncated - '
				                 . 'Wordpress will display full content which will be visible after user purchase a subscription.',
			),
			Settings::OPTION_SHOW_FULL_POST_FOR_SEARCH_ENGINES   => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'crawlers',
				'title'       => 'Show full post for search engine\'s crawlers',
				'desc'        => 'If enabled, the search engine\'s crawlers will see the entire post content instead of the payment box in order to index the full post\'s content.',
			),
			Settings::OPTION_ALLOWED_SEARCH_ENGINES              => array(
				'type'        => Settings::TYPE_TEXTAREA,
				'default'     => implode( "\n", array(
					"aolbuild",
					'baidu',
					'bingbot',
					'bingpreview',
					'msnbot',
					'duckduckgo',
					'adsbot-google',
					'googlebot',
					'mediapartners-google',
					'teoma',
					'slurp',
					'yandex'
				) ),
				'category'    => 'general',
				'subcategory' => 'crawlers',
				'title'       => 'Search engines User-Agent list',
				'desc'        => 'You can specify the User-Agent strings (case-insensitive regular expressions) separated with new lines to recognize '
				                 . 'the search engine\'s crawlers for the option above.',
			),
			Settings::OPTION_RESTRICT_COPYING_CONTENT            => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'copying_content',
				'title'       => 'Prevent users from copying content',
				'desc'        => 'If enabled, then user not able to copy content from post or page.',
			),
			Settings::OPTION_RELOAD_EXPIRED_SUBSCRIPTION         => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 1,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Reload browser when subscription expires',
				'desc'        => 'If enabled, script will check in the background if the subscription is still active and reload the browser when it expires '
				                 . 'or user has been logged-out to disallow further reading post.',
			),
			Settings::OPTION_SUBSCRIPTION_FORM_FOR_GUEST_USER    => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Allow subscription form for guest user',
				'desc'        => 'If enabled, guests will be able to purchase subscriptions of groups and categories even without being logged-in. This option shows the subscription form, including the price, to guests. Note that the guest access is based on a cookie and so guest users will lose access if this cookie is deleted.',
			),
			Settings::OPTION_AUTO_REGISTER_AND_LOGIN_USER    => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Register and login a new user for guest client',
				'desc'        => 'If enabled, guests will be registered and logged in automatically. Works only with "Allow subscription form for guest user" enabled.',
			),
			Settings::OPTION_LOGIN_FORM_ENABLE                   => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Enable login form for guest user',
				'desc'        => 'If enabled, then default wordpress login form will be displayed below paybox for guest users. If you have installed <a href="https://www.cminds.com/wordpress-plugins-library/registration-and-invitation-codes-plugin-for-wordpress" target="_blank">CM Registration Pro</a> plugin then <code>[cmreg-login-form]</code> shortcode replaced default wordpress login form.',
			),
			Settings::OPTION_SUBSCRIPTION_FORM_NOT_FOR_OWNER     => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 1,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Show full post for post author and administrator',
				'desc'        => 'If enabled, then subscription form will not be displayed for post author and administrator.',
			),
			Settings::OPTION_SUBSCRIPTION_LIMIT_ENABLE           => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'post_availability',
				'title'       => 'Allow to set limit the post availability for users',
				'desc'        => '',
			),
			Settings::OPTION_SUBSCRIPTION_LIMIT_NUMBER           => array(
				'type'        => Settings::TYPE_INT,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'post_availability',
				'title'       => 'Max user number limit',
				'desc'        => 'It works if above option is enabled<br>0 or less than 0 means no limit.',
			),
			Settings::OPTION_SUBSCRIPTION_LIMIT_NUMBER_MESSAGE   => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'post_availability',
				'title'       => 'Allow to set message with limit',
				'desc'        => 'If enabled, then message will show with max user number of limit.',
			),

			Settings::OPTION_SHOW_MESSAGE_YOU_HAVE_BOUGHT_THE_POST => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'messages',
				'title'       => 'Show message like "You\'ve bought the post"',
				'desc'        => 'If enabled, then message will show with before the post content. 
								<div><i>You can change the message in Labels tab.</i></div>',
			),

//			Settings::OPTION_SHOW_MESSAGE_YOU_HAVE_PREPAID_ALL => array(
//				'type'        => Settings::TYPE_BOOL,
//				'default'     => 0,
//				'category'    => 'general',
//				'subcategory' => 'messages',
//				'title'       => 'Show message like "You\'ve prepaid all our posts"',
//				'desc'        => 'If enabled, then message will show with before the post content.
//								<div><i>You can change the message in Labels tab.</i></div>',
//			),

			Settings::OPTION_AUTOREDIRECT_TO_PAID_POST         => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'general',
				'subcategory' => 'subscriptions',
				'title'       => 'Autoredirect to paid post after purchase',
				'desc'        => 'Redirects from the checkout page to the paid content. You can set the waiting.',
			),
			Settings::OPTION_AUTOREDIRECT_TO_PAID_POST_SECONDS => array(
				'type'                => Settings::TYPE_INT,
				'default'             => 5,
				'category'            => 'general',
				'subcategory'         => 'subscriptions',
				'title'               => 'Waiting time for redirection (in seconds)',
				'desc'                => 'Redirects the user after this amount of seconds (default is 5).',
				'required'            => 1,
				'required_dependency' => [
					[ Settings::OPTION_AUTOREDIRECT_TO_PAID_POST => 1 ]
				],
			),

			// Refund
			Settings::OPTION_REFUND_ENABLED                    => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'refund',
				'subcategory' => 'refund',
				'title'       => 'Enable refunds',
				'desc'        => 'Refunds are available only for posts purchased by CM Micropayments, the EDD payments currently are not supported.'
			),
			Settings::OPTION_REFUND_REASONS                    => array(
				'type'             => Settings::TYPE_LIST_KEY_VALUE,
				'default'          => array(
					array( 'key' => 'accident', 'value' => 'I accidentally clicked on the payment button' ),
					array( 'key' => 'expectations', 'value' => 'The content didn\'t meet my expectations' ),
					array( 'key' => 'price_too_high', 'value' => 'The price of the article was too high' ),
					array( 'key' => 'article_too_short', 'value' => 'The article was too short' ),
					array( 'key' => 'article_too_long', 'value' => 'The article was too long' ),
				),
				'category'         => 'refund',
				'subcategory'      => 'refund',
				'title'            => 'Refund reasons',
				'keyPlaceholder'   => 'Unique flag',
				'valuePlaceholder' => 'Text label',
			),
			Settings::OPTION_REFUND_TIMEOUT_MINUTES            => array(
				'type'        => Settings::TYPE_INT,
				'default'     => 10,
				'category'    => 'refund',
				'subcategory' => 'refund',
				'title'       => 'Time limit to allow refund [minutes]',
				'desc'        => 'Set the time limit in minutes to allow refund for users that activated a subscription. After this time user won\'t be able to refund.',
			),


			// Notifications
			Settings::OPTION_NEW_SUB_CUSTOMER_NOTIF_ENABLE     => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 1,
				'category'    => 'notifications',
				'subcategory' => 'csub',
				'title'       => 'Enable notifications',
			),
			Settings::OPTION_NEW_SUB_CUSTOMER_NOTIF_SUBJECT    => array(
				'type'        => Settings::TYPE_STRING,
				'category'    => 'notifications',
				'subcategory' => 'csub',
				'title'       => 'Email subject',
				'desc'        => 'You can use following shortcodes:<br />[blogname], [postname], [username], [userlogin], [startdate], [enddate], [duration], [amount]',
				'default'     => '[[blogname]] New subscription for [duration] ([amount])',
			),
			Settings::OPTION_NEW_SUB_CUSTOMER_NOTIF_TEMPLATE   => array(
				'type'        => Settings::TYPE_TEXTAREA,
				'category'    => 'notifications',
				'subcategory' => 'csub',
				'title'       => 'Email body template',
				'desc'        => 'You can use following shortcodes:<br />[blogname], [home], [postname], [permalink], [username], [userlogin], [startdate], [enddate],'
				                 . ' [duration], [amount], [reportlink]',
				'default'     => "Hi,\nnew subscription has appeared.\n\nWebsite: [blogname]\nWebsite URL: [home]\n"
				                 . "Post: [postname]\nPost link: [permalink]\nUser name: [username]\n"
				                 . "User login: [userlogin]\nStart date: [startdate]\nEnd date: [enddate]\nDuration: [duration]\nPoints charged: [amount]"
				                 . "\n\nSee the Subscription Report: [reportlink]",
			),

			Settings::OPTION_PURCHASE_CONFIRMATION_NOTIF_ENABLE     => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'notifications',
				'subcategory' => 'purch',
				'title'       => 'Enable notifications',
			),
			Settings::OPTION_PURCHASE_CONFIRMATION_NOTIF_SUBJECT    => array(
				'type'        => Settings::TYPE_STRING,
				'category'    => 'notifications',
				'subcategory' => 'purch',
				'title'       => 'Email subject',
				'desc'        => 'You can use following shortcodes:<br />[blogname], [postname], [username], [userlogin], [startdate], [enddate], [duration], [amount]',
				'default'     => '[[blogname]] Confirmation of purchase for [duration] ([amount])',
			),
			Settings::OPTION_PURCHASE_CONFIRMATION_NOTIF_TEMPLATE   => array(
				'type'        => Settings::TYPE_TEXTAREA,
				'category'    => 'notifications',
				'subcategory' => 'purch',
				'title'       => 'Email body template',
				'desc'        => 'You can use following shortcodes:<br />[blogname], [home], [postname], [permalink], [username], [userlogin], [startdate], [enddate],'
				                 . ' [duration], [amount], [reportlink]',
				'default'     => "Hi,\nyour purchase was confirmed.\n\nWebsite: [blogname]\nWebsite URL: [home]\n"
				                 . "Post: [postname]\nPost link: [permalink]\nUser name: [username]\n"
				                 . "User login: [userlogin]\nStart date: [startdate]\nEnd date: [enddate]\nDuration: [duration]\nPoints charged: [amount]"
				                 . "\n\nSee the Subscription Report: [reportlink]",
			),

			Settings::OPTION_SUBSCRIPTION_EXPIRE_ENABLE     => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'notifications',
				'subcategory' => 'expire',
				'title'       => 'Enable notifications',
			),
			Settings::OPTION_SUBSCRIPTION_EXPIRE_DATE            => array(
				'type'        => Settings::TYPE_INT,
				'default'     => 1,
				'category'    => 'notifications',
				'subcategory' => 'expire',
				'title'       => 'Subscription expires in (days)',
				'desc'        => 'Enter here the amount of days before the subscription expires to send the reminder.',
				'required'    => 1,
			),
			Settings::OPTION_SUBSCRIPTION_EXPIRE_SUBJECT    => array(
				'type'        => Settings::TYPE_STRING,
				'category'    => 'notifications',
				'subcategory' => 'expire',
				'title'       => 'Email subject',
				'desc'        => 'You can use following shortcodes:<br />[blogname], [postname], [username], [userlogin], [startdate], [enddate], [duration], [amount]',
				'default'     => '[[blogname]] Subscription is about to expire',
			),
			Settings::OPTION_SUBSCRIPTION_EXPIRE_TEMPLATE   => array(
				'type'        => Settings::TYPE_TEXTAREA,
				'category'    => 'notifications',
				'subcategory' => 'expire',
				'title'       => 'Email body template',
				'desc'        => 'You can use following shortcodes:<br />[blogname], [home], [postname], [permalink], [username], [userlogin], [startdate], [enddate],'
				                 . ' [duration], [amount], [reportlink]',
				'default'     => "Hi,\nyour subscription is about to expire.\n\nWebsite URL: [home]\n"
				                 . "Post: [postname]\nPost link: [permalink]\nUser name: [username]\n"
				                 . "User login: [userlogin]\nStart date: [startdate]\nEnd date: [enddate]\nDuration: [duration]\nPoints charged: [amount]"
				                 . "\n",
			),

			Settings::OPTION_NEW_SUB_ADMIN_NOTIF_ENABLE        => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'notifications',
				'subcategory' => 'sub',
				'title'       => 'Enable notifications',
			),
			Settings::OPTION_NEW_SUB_ADMIN_NOTIF_EMAILS        => array(
				'type'        => Settings::TYPE_CSV_LINE,
				'category'    => 'notifications',
				'subcategory' => 'sub',
				'title'       => 'Emails to notify',
				'desc'        => 'Enter comma separated email addresses to send the notification to.',
			),
			Settings::OPTION_NEW_SUB_ADMIN_NOTIF_SUBJECT       => array(
				'type'        => Settings::TYPE_STRING,
				'category'    => 'notifications',
				'subcategory' => 'sub',
				'title'       => 'Email subject',
				'desc'        => 'You can use following shortcodes:<br />[blogname], [postname], [username], [userlogin], [startdate], [enddate], [duration], [amount]',
				'default'     => '[[blogname]] New subscription for [duration] ([amount])',
			),
			Settings::OPTION_NEW_SUB_ADMIN_NOTIF_TEMPLATE      => array(
				'type'        => Settings::TYPE_TEXTAREA,
				'category'    => 'notifications',
				'subcategory' => 'sub',
				'title'       => 'Email body template',
				'desc'        => 'You can use following shortcodes:<br />[blogname], [home], [postname], [permalink], [username], [userlogin], [startdate], [enddate],'
				                 . ' [duration], [amount], [reportlink]',
				'default'     => "Hi,\nnew subscription has appeared.\n\nWebsite: [blogname]\nWebsite URL: [home]\n"
				                 . "Post: [postname]\nPost link: [permalink]\nUser name: [username]\n"
				                 . "User login: [userlogin]\nStart date: [startdate]\nEnd date: [enddate]\nDuration: [duration]\nPoints charged: [amount]"
				                 . "\n\nSee the Subscription Report: [reportlink]",
			),
			Settings::OPTION_POST_PRICING_GROUP_COLUMN_SHOW    => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'pricing',
				'subcategory' => 'post',
				'title'       => 'Show pricing group column in the posts table',
				'desc'        => 'If enabled the Pricing group column will be added to the posts table in the wp-admin.',
			),
			Settings::OPTION_POST_PRICES_COLUMN_SHOW           => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'pricing',
				'subcategory' => 'post',
				'title'       => 'Show prices column in the posts table',
				'desc'        => 'If enabled the Prices column will be added to the posts table in the wp-admin.',
			),

			Settings::OPTION_BULK_PAYMENT_METHOD              => array(
				'type'        => Settings::TYPE_RADIO,
				'options'     => array(
					'edd_payments' => 'EDD Payments',
					//'woocommerce_payments' => 'WooCommerce Payments',
					//'cm_micropayments_points' => 'CM Micropayments Points',
				),
				'default'     => 'edd_payments',
				'category'    => 'pricing',
				'subcategory' => 'bulk',
				'title'       => 'Payment model',
				'desc'        => '',
			),
			Settings::OPTION_BULK_PAYMENT_BY_POST_OR_BULK     => array(
				'type'        => Settings::TYPE_RADIO,
				'options'     => array(
					'pay_per_each_post' => 'Pay per each post',
					//'pay_per_pricing_group' => 'Pay per pricing group',
				),
				'default'     => 'pay_per_each_post',
				'category'    => 'pricing',
				'subcategory' => 'bulk',
				'title'       => 'Subscription model',
				'desc'        => '',
			),
			Settings::OPTION_BULK_PAYMENT_SPECIFIC_CATEGORIES => array(
				'type'        => Settings::TYPE_MULTICHECKBOX,
				'options'     => Settings::getPostCategoriesOptions(),
				'default'     => Settings::getPostCategoriesOptionsDefault(),
				'category'    => 'pricing',
				'subcategory' => 'bulk',
				'title'       => 'Specific post categories',
				'desc'        => 'If no category selected then individual price will apply on all supported post types',
			),
			Settings::OPTION_ENABLE_CATEGORIES_PRICES         => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 1,
				'category'    => 'pricing',
				'subcategory' => 'post',
				'title'       => 'Enable categories prices',
				'desc'        => 'If enabled users will be able to purchase posts by category price. Enables the category price paywall box on the front-end.',
			),
			Settings::OPTION_BULK_OPTIONS                     => array(
				'type'        => Settings::TYPE_MP_PRICE_GROUPS_BULK,
				'category'    => 'pricing',
				'subcategory' => 'bulk',
				'title'       => 'Pricing',
				'desc'        => '* Sets period one (1) if you choose <strong>lifetime</strong> subscription.',
			),
			Settings::OPTION_PERCENTAGE                  => array(
				'type'        => Settings::TYPE_RADIO,
				'options'     => array(
					Settings::PERCENTAGE_ON      => 'Yes',
					Settings::PERCENTAGE_OFF  => 'No',
				),
				'default'     => Settings::PERCENTAGE_OFF,
				'category'    => 'pricing',
				'subcategory' => 'percent',
				'title'       => 'Allow transferring points to author for his post/page',
				'desc'        => 'Enable this option if you want to transfer points to the post author\'s wallet for selling the access to his post/page.',
			),
			Settings::OPTION_PERCENT_OF_POINTS_TO_AUTHOR => array(
				'type'        => Settings::TYPE_INT,
				'default'     => 100,
				'category'    => 'pricing',
				'subcategory' => 'percent',
				'title'       => 'Author\'s share',
				'desc'        => 'Set a % that the author will receive for selling the access to his post/page. <br>
									For new users only <br> Max value 100',
			),


			// Styles section

			// paybox
			Settings::OPTION_STYLES_PAYBOX_ENABLED          => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'styles',
				'subcategory' => 'paybox',
				'title'       => 'Enable custom style for paybox',
				'desc'        => 'If enabled then paybox will be styled',
			),
			Settings::OPTION_STYLES_PAYBOX_WIDTH            => array(
				'type'        => Settings::TYPE_SELECT,
				'default'     => Settings::STYLES_PAYBOX_WIDTH_DEFAULT,
				'options'     => array(
					Settings::STYLES_PAYBOX_WIDTH_DEFAULT => 'Default',
					Settings::STYLES_PAYBOX_WIDTH_FULL    => 'Full width'
				),
				'category'    => 'styles',
				'subcategory' => 'paybox',
				'title'       => 'Width',
				'desc'        => 'Width of the paybox',
			),
			Settings::OPTION_STYLES_PAYBOX_TEXT_COLOR => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#000000',
				'category'    => 'styles',
				'subcategory' => 'paybox',
				'title'       => 'Text color',
				'desc'        => 'Text color of the paybox',
			),
			Settings::OPTION_STYLES_PAYBOX_BACKGROUND_COLOR => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#f0f0f0',
				'category'    => 'styles',
				'subcategory' => 'paybox',
				'title'       => 'Background color',
				'desc'        => 'Background color of the paybox',
			),
			Settings::OPTION_STYLES_PAYBOX_BORDER_COLOR     => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#e6e6e6',
				'category'    => 'styles',
				'subcategory' => 'paybox',
				'title'       => 'Border color',
				'desc'        => 'Border color of the paybox',
			),

			// checkbox buttion
			Settings::OPTION_STYLES_CHECKOUT_BUTTON_ENABLED => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'styles',
				'subcategory' => 'checkout_button',
				'title'       => 'Enable custom style for checkout button',
				'desc'        => 'If enabled then checkout buttons will be styled',
			),
			Settings::OPTION_STYLES_CHECKOUT_BUTTON_HIDE_BORDER        => array(
				'type'        => Settings::TYPE_BOOL,
				'default'     => 0,
				'category'    => 'styles',
				'subcategory' => 'checkout_button',
				'title'       => 'Hide border',
				'desc'        => 'If enabled button border will be hidden',
			),

			Settings::OPTION_STYLES_CHECKOUT_BUTTON_TEXT_COLOR       => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#000000',
				'category'    => 'styles',
				'subcategory' => 'checkout_button',
				'title'       => 'Text color',
				'desc'        => 'Text color of the button',
			),
			Settings::OPTION_STYLES_CHECKOUT_BUTTON_BG_COLOR       => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#ffffff',
				'category'    => 'styles',
				'subcategory' => 'checkout_button',
				'title'       => 'Background color',
				'desc'        => 'Background color of the button',
			),
			Settings::OPTION_STYLES_CHECKOUT_BUTTON_BORDER_COLOR       => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#212121',
				'category'    => 'styles',
				'subcategory' => 'checkout_button',
				'title'       => 'Border color',
				'desc'        => 'Border color of the button',
			),

			Settings::OPTION_STYLES_CHECKOUT_BUTTON_HOVER_TEXT_COLOR => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#ffffff',
				'category'    => 'styles',
				'subcategory' => 'checkout_button',
				'title'       => 'Text color on hover',
				'desc'        => 'Text color of the button on hover',
			),
			Settings::OPTION_STYLES_CHECKOUT_BUTTON_HOVER_BG_COLOR => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#000000',
				'category'    => 'styles',
				'subcategory' => 'checkout_button',
				'title'       => 'Background color on hover',
				'desc'        => 'Background color of the button on hover',
			),
			Settings::OPTION_STYLES_CHECKOUT_BUTTON_HOVER_BORDER_COLOR => array(
				'type'        => Settings::TYPE_COLOR,
				'default'     => '#212121',
				'category'    => 'styles',
				'subcategory' => 'checkout_button',
				'title'       => 'Border color on hover',
				'desc'        => 'Border color of the button on hover',
			),
		) );

	}


	public static function processPostRequest( $data ) {
		do_action( 'cmppp_before_save_settings' );
		parent::processPostRequest( $data );
		if ( empty( $data[ Settings::OPTION_MICROPAYMENTS_GROUPS ] ) ) {
			Settings::setOption( Settings::OPTION_MICROPAYMENTS_GROUPS, null );
		}
		if ( empty( $data[ Settings::OPTION_REFUND_REASONS ] ) ) {
			Settings::setOption( Settings::OPTION_REFUND_REASONS, null );
		}
		do_action( 'cmppp_after_save_settings' );
	}


	static function getPostTypesOptions() {
		$types = get_post_types( array(), 'objects' );
		foreach ( $types as $name => &$type ) {
			if ( isset( $type->labels->name ) ) {
				$type = $type->labels->name;
			} else {
				$type = $name;
			}
		}
		unset( $types['attachment'] );
		unset( $types['revision'] );
		unset( $types['nav_menu_item'] );
		unset( $types['custom_css'] );
		unset( $types['customize_changeset'] );
		unset( $types['oembed_cache'] );

		return $types;
	}

	static function getPostCategoriesOptions() {
		$args        = array(
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC'
		);
		$categories  = get_categories( $args );
		$fcategories = array();
		foreach ( $categories as $name => $cat ) {
			$fcategories[ $cat->term_id ] = $cat->name;
		}

		return $fcategories;
	}

	static function getPostCategoriesOptionsDefault() {
		$args        = array(
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC'
		);
		$categories  = get_categories( $args );
		$fcategories = array();
		foreach ( $categories as $name => $cat ) {
			$fcategories[] = $cat->term_id;
		}

		return $fcategories;
	}

}
