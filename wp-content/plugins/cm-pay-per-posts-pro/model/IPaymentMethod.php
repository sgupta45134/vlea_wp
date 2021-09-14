<?php

namespace com\cminds\payperposts\model;

interface IPaymentMethod {

//	function getPricingGroupIndex();

	function setPricingGroupIndex( $groupIndex );

	function getPricingGroupName($groupIndex);

	static function getSubscriptionPlansForGroup( $groupIndex );

	static function getPricingGroups();

	static function getInstance( Post $post );

	static function isAvailable();

	static function isConfigured();

	function initPayment( array $subscriptionPlan, $callbackUrl );

	function initSinglePayment( array $singlePlan, $callbackUrl );

}
