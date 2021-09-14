<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\App;
use com\cminds\payperposts\model\PaymentMethod;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\lib\InstantPayment;
use com\cminds\payperposts\model\Subscription;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\PostInstantPayment;
use com\cminds\payperposts\model\PostWooPayment;
use com\cminds\payperposts\model\Micropayments;
use com\cminds\payperposts\helper\TimeHelper;

class SettingsController extends Controller {

	const ACTION_CLEAR_CACHE = 'clear-cache';

	const PAGE_ABOUT_URL = 'https://plugins.cminds.com/product-catalog/?showfilter=No&cat=Plugin&nitems=2';
	const PAGE_USER_GUIDE_URL = 'https://plugins.cminds.com/cm-pay-per-post-plugin-for-wordpress/';

	protected static $actions = array(
		array( 'name' => 'admin_menu', 'priority' => 15 ),
		'admin_notices',
		'cmppp_display_supported_shortcodes',
	);

	protected static $ajax = array(
		'bulk_request_from_admin',
		'bulk_request_from_admin_for_categories',
		'tie_posts_to_group',
		'tie_posts_to_group__search_post',
		'tie_posts_to_group_page',
		'tie_posts_to_group_action',
		'tie_posts_to_group__add_post_to_group'
	);

	protected static $filters = array(
		array(
			'name'   => 'cmppp-settings-category',
			'args'   => 2,
			'method' => 'settingsLabels'
		)
	);

	public static function admin_menu() {
		add_submenu_page( App::MENU_SLUG, App::getPluginName() . ' Settings', 'Settings', 'manage_options', self::getMenuSlug(), array(
			get_called_class(),
			'render'
		) );
	}

	public static function getMenuSlug() {
		return App::MENU_SLUG;
	}

	public static function createPaymentGroupsForCategories( $data ) {
		if ( ! empty( $data ) && ! empty( $data['Categories'] ) ) {
			$categories   = $data['Categories'];
			$paymentModel = $data['PaymentModel'];
			$unit         = $data['Unit'];
			$price        = $data['Price'] ?? 0;
			$period       = $data['Period'];

			if ( ! empty( $categories ) ) {
				foreach ( $categories as $category_id ) {

					if ( $paymentModel == 'edd_payments' ) {
						$cmppp_edd_pricing_single_enabled = 1;

						$singleTimeSec = TimeHelper::period2seconds( $period . $unit );

						$product_title = get_cat_name( $category_id ) . ' (' . TimeHelper::seconds2period( $singleTimeSec ) . ')';

						if ( $cmppp_edd_pricing_single_enabled == '1' ) {

							global $wpdb;
							$product_id = $wpdb->get_var( "
								SELECT post_id as product_id FROM $wpdb->postmeta 
								WHERE meta_key='cmppp_category_id' && meta_value='$category_id'"
							);

							if ( $product_id ) {
								wp_update_post( array(
									'ID'         => $product_id,
									'post_title' => $product_title
								) );
								update_post_meta( $product_id, 'edd_price', $price );
								update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );

							} else {

								$product_id = wp_insert_post( array(
									'post_title'   => $product_title,
									'post_content' => '',
									'post_status'  => 'publish',
									'post_type'    => 'download',
								) );

								update_post_meta( $product_id, '_edd_bundled_products_conditions', '' );
								update_post_meta( $product_id, '_edd_bundled_products', '' );
								update_post_meta( $product_id, 'edd_download_files', '' );
								update_post_meta( $product_id, 'edd_variable_prices', '' );
								update_post_meta( $product_id, 'edd_price', $price );
								update_post_meta( $product_id, '_edd_download_sales', '0' );
								update_post_meta( $product_id, '_edd_download_earnings', '0.000000' );
								update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );
								update_post_meta( $product_id, 'cmppp_pricing_group', 0 );
								update_post_meta( $product_id, 'cmppp_category_id', $category_id );
								update_post_meta( $product_id, 'cmppp_period', $period );
								update_post_meta( $product_id, 'cmppp_unit', $unit );
							}
						}


					} else if ( $paymentModel == 'woocommerce_payments' ) {

					} else if ( $paymentModel == 'cm_micropayments_points' ) {

					}

				}
			}

		}
	}

	public static function bulk_request_from_admin_for_categories() {
		$categories        = $_POST['Categories'];
		$paymentModel      = $_POST['PaymentModel'];
		$subscriptionModel = $_POST['SubscriptionModel'];

		if ( $subscriptionModel == 'pay_per_each_post' ) {
			$period = $_POST['Period'];
			$Unit   = $_POST['Unit'];                // min, h, d, w, m, y, l
			$Price  = $_POST['Price'];

			$supported_post_types = Settings::getOption( Settings::OPTION_SUPPORTED_POST_TYPES );

			if ( count( $supported_post_types ) > 0 ) {
				foreach ( $supported_post_types as $pt ) {

					if ( ! empty( $categories ) ) {
						// create for categories

						self::createPaymentGroupsForCategories( $_POST );

					} else {

						// create for posts
						$ptargs = array(
							'post_type'   => $pt,
							'numberposts' => - 1,
							'post_status' => 'publish',
							'orderby'     => 'date',
							'order'       => 'DESC',
						);

						$all_posts = get_posts( $ptargs );
						if ( count( $all_posts ) > 0 ) {
							foreach ( $all_posts as $spost ) {
								if ( $paymentModel === 'edd_payments' ) {

									if ( $post = Post::getInstance( $spost->ID ) and $edd = new PostInstantPayment( $post ) ) {

										$pricingSingleIndex = $edd->getPricingSingleIndex();

										$cmppp_edd_pricing_single_enabled = 1;
										$cmppp_edd_pricing_single_number  = $period;
										if ( $cmppp_edd_pricing_single_number == '' ) {
											$cmppp_edd_pricing_single_number = 0;
										}
										$cmppp_edd_pricing_single_unit  = $Unit;
										$cmppp_edd_pricing_single_price = $Price;
										if ( $cmppp_edd_pricing_single_price == '' ) {
											$cmppp_edd_pricing_single_price = 0;
										}

										$eddsingleIndexArr          = array();
										$eddsingleIndexArr['allow'] = $cmppp_edd_pricing_single_enabled;

										$singleTimeSec = TimeHelper::period2seconds( $cmppp_edd_pricing_single_number . $cmppp_edd_pricing_single_unit );

										if ( $cmppp_edd_pricing_single_enabled ) {
											if ( isset( $pricingSingleIndex['product_id'] ) && $pricingSingleIndex['product_id'] != '' && $pricingSingleIndex['product_id'] > 0 ) {

												$product_id = $pricingSingleIndex['product_id'];
												wp_update_post( array(
													'ID'         => $product_id,
													'post_title' => $post->getTitle() . ' (' . TimeHelper::seconds2period( $singleTimeSec ) . ')'
												) );
												update_post_meta( $product_id, 'edd_price', $cmppp_edd_pricing_single_price );
												update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );

											} else {

												$product_id = wp_insert_post( array(
													'post_title'   => $post->getTitle() . ' (' . TimeHelper::seconds2period( $singleTimeSec ) . ')',
													'post_content' => '',
													'post_status'  => 'publish',
													'post_type'    => 'download',
												) );

												update_post_meta( $product_id, '_edd_bundled_products_conditions', '' );
												update_post_meta( $product_id, '_edd_bundled_products', '' );
												update_post_meta( $product_id, 'edd_download_files', '' );
												update_post_meta( $product_id, 'edd_variable_prices', '' );
												update_post_meta( $product_id, 'edd_price', $cmppp_edd_pricing_single_price );
												update_post_meta( $product_id, '_edd_download_sales', '0' );
												update_post_meta( $product_id, '_edd_download_earnings', '0.000000' );

												update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );
												update_post_meta( $product_id, 'cmppp_pricing_group', 0 );
											}
										} else {
											$product_id = 0;
										}

										$eddsingleIndexArr['number']     = $cmppp_edd_pricing_single_number;
										$eddsingleIndexArr['unit']       = $cmppp_edd_pricing_single_unit;
										$eddsingleIndexArr['price']      = $cmppp_edd_pricing_single_price;
										$eddsingleIndexArr['product_id'] = $product_id;
										$edd->setPricingSingleIndex( $eddsingleIndexArr );

									}
								} else if ( $paymentModel == 'woocommerce_payments' ) {

								} else if ( $paymentModel == 'cm_micropayments_points' ) {

								}

							}
						}
					}

				}
			} else {
				echo "First, you need to select supported post types under General tab.";
			}
		} else if ( $subscriptionModel == 'pay_per_pricing_group' ) {

		}
		echo "Bulk process successfully completed!";
		die;
	}

	public static function bulk_request_from_admin() {
		$categories        = $_POST['Categories'];
		$paymentModel      = $_POST['PaymentModel'];
		$subscriptionModel = $_POST['SubscriptionModel'];
		if ( $subscriptionModel == 'pay_per_each_post' ) {

			$period               = $_POST['Period'];
			$Unit                 = $_POST['Unit'];                // min, h, d, w, m, y, l
			$Price                = $_POST['Price'];
			$supported_post_types = Settings::getOption( Settings::OPTION_SUPPORTED_POST_TYPES );

			if ( count( $supported_post_types ) > 0 ) {
				foreach ( $supported_post_types as $pt ) {

					if ( $categories != '' ) {
						$ptargs = array(
							'post_type'   => $pt,
							'category'    => $categories,
							'numberposts' => - 1,
							'post_status' => 'publish',
							'orderby'     => 'date',
							'order'       => 'DESC',
						);
					} else {
						$ptargs = array(
							'post_type'   => $pt,
							'numberposts' => - 1,
							'post_status' => 'publish',
							'orderby'     => 'date',
							'order'       => 'DESC',
						);
					}
					$all_posts = get_posts( $ptargs );
					if ( count( $all_posts ) > 0 ) {
						foreach ( $all_posts as $spost ) {
							if ( $paymentModel == 'edd_payments' ) {

								if ( $post = Post::getInstance( $spost->ID ) and $edd = new PostInstantPayment( $post ) ) {

									$pricingSingleIndex = $edd->getPricingSingleIndex();

									$cmppp_edd_pricing_single_enabled = 1;
									$cmppp_edd_pricing_single_number  = $period;
									if ( $cmppp_edd_pricing_single_number == '' ) {
										$cmppp_edd_pricing_single_number = 0;
									}
									$cmppp_edd_pricing_single_unit  = $Unit;
									$cmppp_edd_pricing_single_price = $Price;
									if ( $cmppp_edd_pricing_single_price == '' ) {
										$cmppp_edd_pricing_single_price = 0;
									}

									$eddsingleIndexArr          = array();
									$eddsingleIndexArr['allow'] = $cmppp_edd_pricing_single_enabled;

									$singleTimeSec = TimeHelper::period2seconds( $cmppp_edd_pricing_single_number . $cmppp_edd_pricing_single_unit );

									if ( $cmppp_edd_pricing_single_enabled == '1' ) {
										if ( isset( $pricingSingleIndex['product_id'] ) && $pricingSingleIndex['product_id'] != '' && $pricingSingleIndex['product_id'] > 0 ) {

											$product_id = $pricingSingleIndex['product_id'];
											wp_update_post( array(
												'ID'         => $product_id,
												'post_title' => $post->getTitle() . ' (' . TimeHelper::seconds2period( $singleTimeSec ) . ')'
											) );
											update_post_meta( $product_id, 'edd_price', $cmppp_edd_pricing_single_price );
											update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );

										} else {

											$product_id = wp_insert_post( array(
												'post_title'   => $post->getTitle() . ' (' . TimeHelper::seconds2period( $singleTimeSec ) . ')',
												'post_content' => '',
												'post_status'  => 'publish',
												'post_type'    => 'download',
											) );

											update_post_meta( $product_id, '_edd_bundled_products_conditions', '' );
											update_post_meta( $product_id, '_edd_bundled_products', '' );
											update_post_meta( $product_id, 'edd_download_files', '' );
											update_post_meta( $product_id, 'edd_variable_prices', '' );
											update_post_meta( $product_id, 'edd_price', $cmppp_edd_pricing_single_price );
											update_post_meta( $product_id, '_edd_download_sales', '0' );
											update_post_meta( $product_id, '_edd_download_earnings', '0.000000' );

											update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );
											update_post_meta( $product_id, 'cmppp_pricing_group', 0 );
										}
									} else {
										$product_id = 0;
									}

									$eddsingleIndexArr['number']     = $cmppp_edd_pricing_single_number;
									$eddsingleIndexArr['unit']       = $cmppp_edd_pricing_single_unit;
									$eddsingleIndexArr['price']      = $cmppp_edd_pricing_single_price;
									$eddsingleIndexArr['product_id'] = $product_id;
									$edd->setPricingSingleIndex( $eddsingleIndexArr );

								}
							} else if ( $paymentModel == 'woocommerce_payments' ) {

							} else if ( $paymentModel == 'cm_micropayments_points' ) {

							}

						}
					}

				}
			} else {
				echo "First, you need to select supported post types under General tab.";
			}
		} else if ( $subscriptionModel == 'pay_per_pricing_group' ) {

		}
		echo "Bulk process successfully completed!";
		die;
	}

	public static function tie_posts_to_group() {
		global $wpdb;

		$response = "";
		if ( isset( $_GET['group_type'] ) && isset( $_GET['index'] ) ) {
			$group_type  = $_GET['group_type'];
			$group_index = $_GET['index'];

			$supported_post_types = Settings::getOption( Settings::OPTION_SUPPORTED_POST_TYPES );

			$post_types     = [];
			$all_post_types = [];

			// foreach type get instances
			if ( ! empty( $supported_post_types ) ) {
				$post_per_page = 30;

				$meta_key = '';
				if ( $group_type === 'EDD' ) {
					$meta_key       = PostInstantPayment::META_EDD_PRICING_GROUP_INDEX;
					$paymentInstant = PostInstantPayment::getInstance( new Post() );
				}
				if ( $group_type === 'Mircopayments' ) {
					$meta_key       = Micropayments::META_MP_PRICING_GROUP_INDEX;
					$paymentInstant = Micropayments::getInstance( new Post() );
				}
				if ( $group_type === 'WooCommerce' ) {
					$meta_key       = PostWooPayment::POST_META_PRICING_GROUP_INDEX;
					$paymentInstant = PostWooPayment::getInstance( new Post() );
				}

				$group_name = $paymentInstant->getPricingGroupName( $group_index );

				foreach ( $supported_post_types as $post_type ) {
					$query = "
						SELECT SQL_CALC_FOUND_ROWS p.*, pm.meta_value as groups 
						FROM {$wpdb->prefix}posts as p
						LEFT JOIN {$wpdb->prefix}postmeta as pm ON pm.post_id=p.ID
						WHERE pm.meta_key='{$meta_key}' AND pm.meta_value <> '' AND pm.meta_value IS NOT NULL 
						AND p.post_type='{$post_type}'
					";

					$res_posts = $wpdb->get_results( $query );
					foreach ( $res_posts as $key => $post ) {
						$groups = $paymentInstant::getPostPricingGroupsIndexes( $post->ID );

						if ( ! in_array( $group_index, $groups ) ) {
							unset( $res_posts[ $key ] );
						}
					}

					$total_count = count( $res_posts );
					$res_posts   = array_slice( $res_posts, 0, $post_per_page );

					$post_types[ $post_type ]['posts'] = $res_posts;

					$pagination_li = "";
					$total_pages   = ceil( $total_count / $post_per_page );

					if ( $total_pages > 1 ) {
						for ( $i = 1; $i <= $total_pages; $i ++ ) {
							$current       = ( $i == 1 ) ? 'current' : '';
							$pagination_li .= "<a class='{$current}' href='#'>{$i}</a>&nbsp;&nbsp;&nbsp;";
						}
					}

					$pagination = "<div class='pagination'>{$pagination_li}</div>";

					$post_types[ $post_type ]['pagination'] = $pagination;
					$all_post_types[ $post_type ]           = get_post_type_labels( get_post_type_object( $post_type ) );
				}

				$tabs_li_header = "";
				$tabs_inner     = "";
				$first          = true;

				foreach ( $all_post_types as $key => $post_type_obj ) {
					$current = ( $first ) ? ' current ' : '';

					$tabs_li_header .= "<li><a class='{$current}' href='#tab-{$key}'>{$post_type_obj->name}</a></li>";

					$posts      = $post_types[ $key ]['posts'];
					$pagination = $post_types[ $key ]['pagination'];

					$tr = '';
					foreach ( $posts as $post ) {
						$tr .= "
							<tr>
								<td class='check-column'><input type='checkbox' name='post_id[]' value='{$post->ID}'></td>
								<td>{$post->ID}</td>
								<td><a target='_blank' href='" . get_edit_post_link( $post->ID ) . "'>{$post->post_title}</a></td>
								<td>{$post->post_modified}</td>
							</tr>
						";
					}

					$tabs_inner .= "<div class='modal-group modal-group-{$key} {$current}'>
										<form>
											<br>
											<div class='search-wrapper' style='position: relative'>
												<input type='search' value='' style='width: 90%' placeholder='Enter the {$post_type_obj->singular_name} name' />
												<button type='button' class='button search_post' style='width: 9%'>" . __( "Search" ) . "</button>
												<div class='search-results' style='box-sizing: border-box;'></div>
											</div>
											<br>
											<input type='hidden' name='post_type' value='{$key}'>
											<input type='hidden' name='payment' value='{$group_type}'>
											<input type='hidden' name='group_index' value='{$group_index}'>
											<table class='wp-list-table widefat fixed striped table-view-list posts'>
												<thead>
												<tr>
													<th class='manage-column column-cb check-column'><input type='checkbox' value='all'></th>
													<th class='post_id'>ID</th>
													<th>Title</th>
													<th>Date Modified</th>
												</tr>
												</thead>
												<tbody>
													{$tr}
												</tbody>
											</table>
											<br>
											{$pagination}
											<br>
											
											Choose the action:
											<ul>
												<li><label><input type='radio' name='post-group-action' value='add_all'>Assign all {$post_type_obj->name} to group '{$group_name}'</label></li>
												<li><label><input type='radio' name='post-group-action' value='remove_checked'>Remove checked {$post_type_obj->name} from the group '{$group_name}'</label></li>
												<li><label><input type='radio' name='post-group-action' value='remove_all'>Remove all {$post_type_obj->name} from the group '{$group_name}'</label></li>
											</ul>
										
											<button type='button' class='button cmppp-tie-post-group-action-go'>Go</button>
										</form>
										
									</div>";

					$first = false;
				}

				$tabs = '
					<ul class="cmppp-modal-group-tabs">
						' . $tabs_li_header . '
					</ul>
					<div class="inner">
						' . $tabs_inner . '
					</div>
				';


				$response = $tabs;
			} else {
				echo "You should choose 'Supported post types' before tie posts to group.";
				wp_die();
			}
		}

		echo $response;
		wp_die();
	}

	public static function tie_posts_to_group_page() {
		global $wpdb;

		$post_per_page = 30;
		$offset        = ( $_GET['newPage'] - 1 ) * $post_per_page;

		$meta_key = '';
		if ( $_GET['payment'] === 'EDD' ) {
			$meta_key       = PostInstantPayment::META_EDD_PRICING_GROUP_INDEX;
			$paymentInstant = PostInstantPayment::getInstance( new Post() );
		}
		if ( $_GET['payment'] === 'Mircopayments' ) {
			$meta_key       = Micropayments::META_MP_PRICING_GROUP_INDEX;
			$paymentInstant = Micropayments::getInstance( new Post() );
		}
		if ( $_GET['payment'] === 'WooCommerce' ) {
			$meta_key       = PostWooPayment::POST_META_PRICING_GROUP_INDEX;
			$paymentInstant = PostWooPayment::getInstance( new Post() );
		}

		$query = "
			SELECT SQL_CALC_FOUND_ROWS p.*, pm.meta_value as groups 
			FROM {$wpdb->prefix}posts as p
			LEFT JOIN {$wpdb->prefix}postmeta as pm ON pm.post_id=p.ID
			WHERE pm.meta_key='{$meta_key}' AND pm.meta_value <> '' AND pm.meta_value IS NOT NULL 
			AND p.post_type='{$_GET['postType']}'
		";

		$posts = $wpdb->get_results( $query );
		foreach ( $posts as $key => $post ) {
			$groups = $paymentInstant::getPostPricingGroupsIndexes( $post->ID );

			if ( ! in_array( $_GET['group_index'], $groups ) ) {
				unset( $posts[ $key ] );
			}
		}

		$total_count = count( $posts );
		$posts       = array_slice( $posts, $offset, $post_per_page );

		$pagination_li = "";
		$total_pages   = ceil( $total_count / $post_per_page );

		if ( $total_pages > 1 ) {
			for ( $i = 1; $i <= $total_pages; $i ++ ) {
				$current       = ( $i == $_GET['newPage'] ) ? 'current' : '';
				$pagination_li .= "<a class='{$current}' href='#'>{$i}</a>&nbsp;&nbsp;&nbsp;";
			}
		}

		$pagination = "<div class='pagination'>{$pagination_li}</div>";


		$tr = '';
		foreach ( $posts as $post ) {
			$tr .= "
				<tr>
					<td class='check-column'><input type='checkbox' name='post_id[]' value='{$post->ID}'></td>
					<td>{$post->ID}</td>
					<td><a target='_blank' href='" . get_edit_post_link( $post->ID ) . "'>{$post->post_title}</a></td>
					<td>{$post->post_modified}</td>
				</tr>
			";
		}
		$html = "<div class='modal_paged_response'>
					<table class='tbody-wrapper'><tbody>{$tr}</tbody></table>
					<div class='pagination-wrapper'>{$pagination}</div>
				</div>";

		echo $html;
		wp_die();
	}

	public static function tie_posts_to_group__search_post() {
		$post_name   = $_GET['post_name'];
		$post_type   = $_GET['post_type'];
		$group_index = $_GET['group_index'];
		$payment     = $_GET['payment'];


		if ( $payment === 'EDD' ) {
			$paymentInstance = PostInstantPayment::getInstance( new Post() );
		}
		if ( $payment === 'Mircopayments' ) {
			$paymentInstance = Micropayments::getInstance( new Post() );
		}
		if ( $payment === 'WooCommerce' ) {
			$paymentInstance = PostWooPayment::getInstance( new Post() );
		}


		global $wpdb;

		$query = "
			SELECT p.*
			FROM {$wpdb->prefix}posts as p
			WHERE p.post_title LIKE '%{$post_name}%' 
			AND p.post_type='{$post_type}'
		";


		$posts = $wpdb->get_results( $query );

		foreach ( $posts as $key => $post ) {
			$groups = $paymentInstance::getPostPricingGroupsIndexes( $post->ID );
			if ( ! empty( $group_index ) && in_array( $group_index, $groups ) ) {
				unset( $posts[ $key ] );
			}
		}

		$html = "";

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$html .= "
					<div><a class='post_link' target='_blank' href='" . get_edit_post_link( $post->ID ) . "'>
					{$post->post_title}</a>&nbsp;&nbsp;
					<a href='#' class='add' data-post-id='{$post->ID}'>add</a></div>
				";
			}
		}

		if ( empty( $html ) ) {
			$html = "Nothing";
		}

		$html .= "<br><button class='button close-search-wrapper'>" . __( "Close" ) . "</button>";


		echo $html;
		wp_die();
	}

	public static function tie_posts_to_group_action() {
		$formData = [];
		parse_str( $_GET['formData'], $formData );

		if ( ! empty( $formData['group_index'] ) && $formData['payment'] ) {

			if ( $formData['payment'] === 'EDD' ) {
				$paymentInstance = PostInstantPayment::getInstance( new Post() );
			}
			if ( $formData['payment'] === 'Mircopayments' ) {
				$paymentInstance = Micropayments::getInstance( new Post() );
			}
			if ( $formData['payment'] === 'WooCommerce' ) {
				$paymentInstance = PostWooPayment::getInstance( new Post() );
			}

			switch ( $formData['post-group-action'] ) {
				case 'add_all':
					$post_ids = get_posts( array(
						'numberposts' => - 1,
						'post_type'   => $formData['post_type'],
						'fields'      => 'ids',
					) );
					if ( ! empty( $post_ids ) ) {
						foreach ( $post_ids as $post_id ) {
							$paymentInstance::addPricingGroupIndexToPost( $post_id, $formData['group_index'] );
						}
					}

					break;

				case 'remove_checked':
					if ( ! empty( $formData['post_id'] ) && isset( $formData['post_id'] ) ) {
						foreach ( $formData['post_id'] as $post_id ) {
							$paymentInstance::removePricingGroupIndexFromPost( $post_id, $formData['group_index'] );
						}
					}
					break;

				case 'remove_all':
					$post_ids = get_posts( array(
						'numberposts' => - 1,
						'post_type'   => $formData['post_type'],
						'fields'      => 'ids',
					) );

					foreach ( $post_ids as $post_id ) {
						$paymentInstance::removePricingGroupIndexFromPost( $post_id, $formData['group_index'] );
					}
					break;
			}
		}

		echo json_encode( [] );
		wp_die();
	}

	public static function tie_posts_to_group__add_post_to_group() {
		if ( $_POST['payment'] === 'EDD' ) {
			$paymentInstance = PostInstantPayment::getInstance( new Post() );
		}

		if ( $_POST['payment'] === 'Mircopayments' ) {
			$paymentInstance = Micropayments::getInstance( new Post() );
		}

		if ( $_POST['payment'] === 'WooCommerce' ) {
			$paymentInstance = PostWooPayment::getInstance( new Post() );
		}

		$groups = $paymentInstance::addPricingGroupIndexToPost( $_POST['post_id'], $_POST['group_index'] );

		echo json_encode( [] );
		wp_die();
	}


	public static function admin_notices() {
		if ( ! get_option( 'permalink_structure' ) ) {
			printf( '<div class="error"><p><strong>%s:</strong> to make the plugin works properly
				please enable the <a href="%s">Wordpress permalinks</a>.</p></div>', App::getPluginName(), admin_url( 'options-permalink.php' ) );
		}
	}

	public static function render() {
		wp_enqueue_style( 'cmppp-backend' );
		wp_enqueue_style( 'cmppp-settings' );
		wp_enqueue_script( 'cmppp-backend' );
		echo self::loadView( 'backend/template', array(
			'title'   => App::getPluginName() . ' Settings',
			'nav'     => self::getBackendNav(),
			'content' => self::loadBackendView( 'licensing-box' ) . self::loadBackendView( 'settings', array(
					'clearCacheUrl' => self::createBackendUrl( self::getMenuSlug(), array( 'action' => self::ACTION_CLEAR_CACHE ), self::ACTION_CLEAR_CACHE ),
				) ),
		) );
	}

	public static function settingsLabels( $result, $category ) {
		if ( $category == 'labels' ) {
			$result = self::loadBackendView( 'labels' );
		}

		return $result;
	}

	public static function processRequest() {

		$fileName = basename( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
		if ( is_admin() and $fileName == 'admin.php' and ! empty( $_GET['page'] ) and $_GET['page'] == self::getMenuSlug() ) {

			if ( ! empty( $_POST ) ) {

				// CSRF protection
				if ( ( empty( $_POST['nonce'] ) or ! wp_verify_nonce( $_POST['nonce'], self::getMenuSlug() ) ) ) {
					$response = array( 'status' => 'error', 'msg' => 'Invalid nonce.' );
				} else {
					Settings::processPostRequest( $_POST );
					Labels::processPostRequest();
					$response = array( 'status' => 'ok', 'msg' => 'Settings have been updated.' );
				}

				wp_redirect( self::createBackendUrl( self::getMenuSlug(), $response ) );
				exit;

			} else if ( ! empty( $_GET['action'] ) and ! empty( $_GET['nonce'] ) and wp_verify_nonce( $_GET['nonce'], $_GET['action'] ) )
				switch ( $_GET['action'] ) {
					case self::ACTION_CLEAR_CACHE:
						wp_redirect( self::createBackendUrl( self::getMenuSlug(), array(
							'status' => 'ok',
							'msg'    => 'Cache has been removed.'
						) ) );
						exit;
				}

		}
	}

	public static function getSectionExperts() {
		return self::loadBackendView( 'experts' );
	}

	public static function cmppp_display_supported_shortcodes() {
		echo self::loadBackendView( 'shortcodes' );
	}

}
