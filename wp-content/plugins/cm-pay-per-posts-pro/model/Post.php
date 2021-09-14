<?php

namespace com\cminds\payperposts\model;

use com\cminds\payperposts\controller\SubscriptionsController;
use com\cminds\payperposts\lib\Email;

class Post extends Model {

	protected $post;

	function __construct( $post=null ) {
		$this->post = $post;
	}

	static function getInstance( $post ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}
		if ( $post and $post instanceof \WP_Post ) {
			return new static( $post );
		}

		return $post;
	}

	function isPaid() {
		return ( ( Micropayments::isConfigured() and $mp = new Micropayments( $this ) and $mp->isPaid() )
		         or ( PostInstantPayment::isAvailable() and $edd = new PostInstantPayment( $this ) and $edd->isPaid() )
		         or ( PostWooPayment::isConfigured() and PostWooPayment::getInstance( $this )->isPaid() )
		);
	}

	function getPostFragment() {


		if ( Settings::getOption( Settings::OPTION_USE_POST_EXCERPT ) ) {
			$excerpt = $this->getPostExcerpt();
			if ( ! empty( $excerpt ) ) {
				return $excerpt;
			}
		}

		$percent = Settings::getOption( Settings::OPTION_USE_POST_PERCENT );

		if ( ! Settings::getOption( Settings::OPTION_USE_POST_EXCERPT ) && $percent > 0 ) {
			$content = $this->post->post_content;
			$length  = strlen( $content ) * $percent / 100;
			$output  = wpautop( substr( $content, 0, $length ) );
		} else {
			$output = $this->getPostContentFragment();
		}


		return $output;
	}

	function getPostContentFragment() {

		$fragment = $this->getPostContentUntilMore();
		if ( ! empty( $fragment ) ) {
			return $fragment;
		}

		$fragment = $this->getPostContentFirstParagraph();
		if ( ! empty( $fragment ) ) {
			return $fragment;
		}

		return $this->getPostContentTrimWords();

	}

	function getPostContentUntilMore() {
		$pos = strpos( $this->post->post_content, '<!--more-->' );
		if ( $pos !== false ) {
			return wpautop( substr( $this->post->post_content, 0, $pos ) );
		}
	}

	function getPostContentFirstParagraph() {
		$content = wpautop( $this->post->post_content );
		$pos     = stripos( $content, '</p>' );
		if ( $pos !== false and $pos < 1000 ) {
			return substr( $content, 0, $pos + 4 );
		}
	}

	function getPostContentTrimWords() {
		return wpautop( wp_trim_words( $this->post->post_content ) );
	}

	function getPostExcerpt() {
		return $this->post->post_excerpt;
	}

	function getPostAuthor() {
		return $this->post->post_author;
	}

	function getPostContent() {
		return wpautop( $this->post->post_content );
	}

	function getId() {
		return $this->post->ID;
	}

	function getTitle() {
		return $this->post->post_title;
	}

	function getPermalink() {
		return get_permalink( $this->getId() );
	}

	function getPostType() {
		return $this->post->post_type;
	}

	function getPostStatus() {
		return $this->post->post_status;
	}

	function isPublish() {
		return ( 'publish' == $this->getPostStatus() );
	}

	function getPostMeta( $key, $single = true ) {
		return get_post_meta( $this->getId(), $key, $single );
	}

	function setPostMeta( $key, $value, $prev_value = '' ) {
		return update_post_meta( $this->getId(), $key, $value, $prev_value );
	}

	function isSubscriptionActive() {
		if ( $subscription = new Subscription( $this ) ) {
			return $subscription->isSubscriptionActive();
		} else {
			return false;
		}
	}

}
