<?php
namespace com\cminds\payperposts\model;

class SubscriptionReport extends Model {
	
	static function getCountByPostId($post_id) {
		global $wpdb;
		return $wpdb->get_var('SELECT COUNT(*) ' . self::getQuery($filter) . ' and p.ID='.$post_id);
	}

	static function getAlreadyOwn($post_id, $user_id) {
		global $wpdb;
		$count = $wpdb->get_var('SELECT COUNT(*) ' . self::getQuery($filter) . ' and p.ID='.$post_id.' and u.ID='.$user_id);
		if($count == '0') {
			return '0';
		} else {
			return '1';
		}
	}

	static function getCount($filter) {
		global $wpdb;
		return $wpdb->get_var('SELECT COUNT(*) ' . self::getQuery($filter));
	}
	
	static function getData($filter, $limit, $page) {
		global $wpdb;
		$offset = ($page-1)*$limit;
		$sql = 'SELECT
			s.umeta_id AS meta_id,
			p.ID AS post_id,
			p.post_author AS post_author,
			p.post_title,
			s.user_id AS user_id,
			u.display_name AS user_name,
			u.user_email AS user_email,
			start.meta_value AS start,
			end.meta_value AS end,
			duration.meta_value AS duration,
			amount.meta_value AS amount,
			plugin.meta_value AS plugin,
			refund.meta_value AS refund,
			pg.meta_value AS pricing_group_index,
			b.meta_value AS blog_id
			' .
			self::getQuery($filter) . "
			GROUP BY s.umeta_id, s.user_id, s.meta_value
			ORDER BY s.umeta_id DESC
			LIMIT $limit OFFSET $offset";
		
		//var_dump($sql);
		$wpdb->query('SET SQL_BIG_SELECTS = 1');
		$results = $wpdb->get_results($sql, ARRAY_A);
		
		$pricingGroupsMp = Micropayments::getPricingGroups();
		$pricingGroupsEDD = PostInstantPayment::getPricingGroups();
		foreach ($results as &$row) {
			if ($row['plugin'] == PostInstantPayment::PAYMENT_PLUGIN_NAME) {
				if (isset($pricingGroupsEDD[$row['pricing_group_index']])) {
					$row['pricing_group_name'] = $pricingGroupsEDD[$row['pricing_group_index']]['name'];
				} else {
					$row['pricing_group_name'] = $row['pricing_group_index'];
				}
			} else {
				if (isset($pricingGroupsMp[$row['pricing_group_index']])) {
					$row['pricing_group_name'] = $pricingGroupsMp[$row['pricing_group_index']]['name'];
				} else {
					$row['pricing_group_name'] = $row['pricing_group_index'];
				}
			}
			$row['refund'] = unserialize($row['refund']);
		}
		
		return $results;
		
	}
	
	static protected function getQuery($filter) {
		global $wpdb;
		
		$filterMap = array('user_id' => 's.user_id', 'post_id' => 'po.meta_value', 'pricing_group' => 'pg.meta_value',
				'plugin' => 'plugin.meta_value', 'blog_id' => 'b.meta_value');
		$filterQuery = '';
		if(is_array($filter)) {
			if(count($filter) > 0) {
				foreach ($filter as $key => $val) {
					if (!empty($val) AND isset($filterMap[$key])) {
						$filterQuery .= $wpdb->prepare(' AND '. $filterMap[$key] .' = %s', $val);
					}
				}
			}
		}
		if (!empty($filter['status'])) {
			if ('active' == $filter['status']) {
				$filterQuery .= ' AND start.meta_value < UNIX_TIMESTAMP() AND end.meta_value > UNIX_TIMESTAMP()';
			} else {
				$filterQuery .= ' AND (start.meta_value > UNIX_TIMESTAMP() OR end.meta_value < UNIX_TIMESTAMP())';
				if ($filter['status'] == 'refund') {
					$filterQuery .= ' AND refund.meta_value IS NOT NULL';
				}
			}
		}
		
		return $wpdb->prepare("
				FROM $wpdb->usermeta s
				JOIN $wpdb->usermeta po ON po.meta_key = CONCAT(%s, s.umeta_id)
				LEFT JOIN $wpdb->usermeta b ON b.meta_key = CONCAT(%s, s.umeta_id)
				JOIN $wpdb->usermeta start ON start.meta_key = CONCAT(%s, s.umeta_id)
				JOIN $wpdb->usermeta end ON end.meta_key = CONCAT(%s, s.umeta_id)
				JOIN $wpdb->usermeta duration ON duration.meta_key = CONCAT(%s, s.umeta_id)
				JOIN $wpdb->usermeta amount ON amount.meta_key = CONCAT(%s, s.umeta_id)
				LEFT JOIN $wpdb->usermeta plugin ON plugin.meta_key = CONCAT(%s, s.umeta_id)
				LEFT JOIN $wpdb->usermeta refund ON refund.meta_key = CONCAT(%s, s.umeta_id)
				JOIN $wpdb->usermeta pg ON pg.meta_key = CONCAT(%s, s.umeta_id)
				JOIN $wpdb->users u ON u.ID = s.user_id
				JOIN $wpdb->posts p ON p.ID = po.meta_value
				WHERE s.meta_key = %s",
			Subscription::META_MP_SUBSCRIPTION_POST_ID .'_',
			Subscription::META_MP_SUBSCRIPTION_BLOG_ID .'_',
			Subscription::META_MP_SUBSCRIPTION_START .'_',
			Subscription::META_MP_SUBSCRIPTION_END .'_',
			Subscription::META_MP_SUBSCRIPTION_DURATION .'_',
			Subscription::META_MP_SUBSCRIPTION_AMOUNT_PAID .'_',
			Subscription::META_MP_SUBSCRIPTION_PAYMENT_PLUGIN .'_',
			Subscription::META_MP_SUBSCRIPTION_REFUND_REASON .'_',
			Subscription::META_MP_SUBSCRIPTION_PRICING_GROUP .'_',
			Subscription::META_MP_SUBSCRIPTION
		) . $filterQuery;
		
	}
	
	static function getUserSubscriptions($userId) {
		return self::getData(array('user_id' => $userId), 999, 1);
	}
	
	static function getAuthorSubscriptions($userId) {
		$final_data = array();
		$author_filter = array();
		if(isset($_GET['status']) && $_GET['status'] != '') {
			$author_filter['status'] = $_GET['status'];
		}
		$data = self::getData($author_filter, 999, 1);
		if(count($data) > 0) {
			foreach($data as $adata) {
				if($adata['post_author'] == $userId) {
					$final_data[] = $adata;
				}
			}
		}
		return $final_data;
	}

}