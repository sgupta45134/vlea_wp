<?php

namespace com\cminds\payperposts\model;

use com\cminds\payperposts\helper\Storage;

class PaymentMethod extends Model {

	const META_PRICING_GROUP_INDEX = '';
	const PLUGIN_PREFIX = '';

	/**
	 *
	 * @var Post
	 */
	public $post;

	public static function isPluginActive() {
		// override it
		return false;
	}

	public static function getMetaPricingGroupIndexName() {
		return "";
	}

	public function setPricingGroupIndex( $groupIndexes ) {
		return $this->post->setPostMeta( static::META_PRICING_GROUP_INDEX, $groupIndexes );
	}

	public static function setPostPricingGroupIndex( $post_id, $groupIndexes ) {
		update_post_meta( $post_id, static::META_PRICING_GROUP_INDEX, $groupIndexes, '' );
	}

	public function getPricingGroupName( $group_index ) {
		$groups = static::getPricingGroups();
		if ( ! empty( $groups ) && isset( $groups[ $group_index ] ) && isset( $groups[ $group_index ]['name'] ) ) {
			return $groups[ $group_index ]['name'];
		}

		return "";
	}

	public static function getPostPricingGroupsIndexes( $post_id ) {
		$meta_field = static::getMetaPricingGroupIndexName();

		$post_groups = get_post_meta( $post_id, $meta_field, true );

		if ( ! empty( $post_groups ) ) {
			if ( ! is_array( $post_groups ) ) {
				$post_groups = [ $post_groups ]; // to compatibility with prev version
			}

			foreach ( $post_groups as $key => $group_index ) {
				if ( static::removeGroupFromProductIfNotExists( $post_id, $group_index ) ) {
					unset( $post_groups[ $key ] );
				}
			}
		} else {
			$post_groups = [];
		}

		return $post_groups;
	}

	public function getPricingGroupIndex() {
		$post_groups = get_post_meta( $this->post->getId(), static::getMetaPricingGroupIndexName(), true );

		if ( ! empty( $post_groups ) ) {
			if ( ! is_array( $post_groups ) ) {
				$post_groups = [ $post_groups ]; // to compatibility with prev version
			}

			foreach ( $post_groups as $key => $group_index ) {
				if ( static::removeGroupFromProductIfNotExists( $this->post->getId(), $group_index ) ) {
					unset( $post_groups[ $key ] );
				}
			}
		} else {
			$post_groups = [];
		}


		return $post_groups;
	}

	public static function addPricingGroupIndexToPost( $post_id, $group_index ) {
		$groups = static::getPostPricingGroupsIndexes( $post_id );


		if ( empty( $groups ) ) {
			$groups = [];
		}


		if ( ! in_array( $group_index, $groups ) ) {
			$groups[] = $group_index;
		}

		static::setPostPricingGroupIndex( $post_id, $groups );

		return $groups;
	}

	public static function removePricingGroupIndexFromPost( $post_id, $group_index ) {
		$groups = static::getPostPricingGroupsIndexes( $post_id );

		if ( empty( $groups ) ) {
			return [];
		}

		foreach ( $groups as $key => $exist_group_index ) {
			if ( $group_index == $exist_group_index ) {
				unset( $groups[ $key ] );
			}
		}

		$groups = array_values( $groups );

		static::setPostPricingGroupIndex( $post_id, $groups );

		return $groups;
	}

	public static function getSubscriptionPlansForGroup( $group_index ) {
		$pricing_groups = static::getPricingGroups();

		if ( ! empty( $pricing_groups ) && isset( $pricing_groups[ $group_index ] ) ) {
			if ($pricing_groups[ $group_index ]['prices']) {
				foreach ( $pricing_groups[ $group_index ]['prices'] as &$cost ) {
					$cost['period']  = $cost['number'] . ' ' . $cost['unit'];
					$cost['seconds'] = Subscription::period2seconds( $cost['period'] );
				}
			}

			$pricing_groups[ $group_index ]['group_index'] = $group_index;

			return $pricing_groups[ $group_index ];
		}

		return array();
	}

	public static function getPricingGroups() {
		return [];
	}

	public static function removeGroupFromProductIfNotExists( $post_id, $group_index ) {
		$pricing_groups = static::getPricingGroups();

		if ( ! empty( $pricing_groups ) ) {
			$groups = array_keys( $pricing_groups );
		} else {
			$groups = [];
		}

		if ( ! is_array( $groups ) && empty( $groups ) ) {
			return true;
		}

		if ( is_array( $groups ) ) {
			$remove = true;

			foreach ( $groups as $key => $exist_group_index ) {
				if ( $group_index == $exist_group_index ) {
					$remove = false;
					break;
				}
			}

			if ( $remove ) {
				static::setPostPricingGroupIndex( $post_id, $groups );
			}

			return $remove;
		}


		return true;
	}

	public function isGroupPaid( $group_index ) {

		if ( is_user_logged_in() ) {
			// user
			$subscription        = new Subscription();
			$subscription_groups = $subscription->getSubscriptionsForUserGroups();

			$pricing_groups = array_column( $subscription_groups, 'pricing_group' );

			if ( empty( $pricing_groups ) ) {
				return false;
			}

			foreach ( $subscription_groups as $subscription_group ) {
				if ( $subscription_group['pricing_group'] == $group_index ) {
					return $subscription_group;
				}
			}

		} else {
			// guest
			return $this->isGroupPaidForGuest( $group_index );
		}


		return false;
	}

	public function isGroupPaidForGuest( $group_index = 0, $plugin_prefix = '' ) {
		if ( self::isPluginActive() || ! $group_index ) {
			return false;
		}

		if ( empty( $plugin_prefix ) ) {
			$plugin_prefix = static::PLUGIN_PREFIX;
		}

		$end_date = Storage::get( $plugin_prefix . '_paid_group_' . $group_index, 0 );

		if ( $end_date ) {
			return [
				'group_index' => $group_index,
				'group_name'  => $this->getPricingGroupName( $group_index ),
				'end_date'    => $end_date,
			];
		}

		return false;
	}

	public function groupHasAnyContent( $group_index ) {
		global $wpdb;

		$meta_key = static::META_PRICING_GROUP_INDEX;

		if ( $meta_key == '' ) {
			return false;
		}


		$meta_value = ':"' . $group_index . '"';

		$query = "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta
				  WHERE meta_key='{$meta_key}' AND meta_value LIKE '%{$meta_value}%'";

		$posts = $wpdb->get_results( $query, ARRAY_A );

		if ( empty( $posts ) ) {
			return false;
		}

		foreach ( $posts as $post ) {
			$value = unserialize( $post['meta_value'] );
			if ( ( is_array( $value ) && in_array( $group_index, $value ) ) || ( is_numeric( $value ) && $group_index == $value ) ) {
				return true;
			}
		}

		return false;
	}

	public function getAllProtectedPosts() {

		global $wpdb;
		$meta_key = "cmppp_" . strtolower( static::PLUGIN_PREFIX ) . "_pricing_single";


		$query    = "
			SELECT pm.post_id FROM {$wpdb->prefix}postmeta as pm
			LEFT JOIN {$wpdb->prefix}posts as p ON p.ID=pm.post_id
			WHERE pm.meta_key='{$meta_key}' AND p.post_status='publish' 
		";
		$post_ids = $wpdb->get_col( $query );


		$args = array(
			'post__in' => $post_ids
		);

		$posts = get_posts( $args );


		return $posts;
	}

	
	public static function getPricingSingle( $post_id ) {
		return get_post_meta( $post_id, static::POST_META_PRICING_SINGLE_INDEX, true );
	}


	public static function getPricingGroupsList() {
		return array_map( function ( $group ) {
			return $group['name'];
		}, static::getPricingGroups() );
	}

}
