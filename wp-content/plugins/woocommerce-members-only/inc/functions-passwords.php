<?php
/**
 * Functions for the password restriction method
 * @since 1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return array of passwords
 * @param $passwords	Optionally pass local category passwords
 * @since 1.0.0
 */
function wcmo_get_passwords( $passwords=false ) {
	if( ! $passwords ) {
		$passwords = get_option( 'wcmo_passwords', true );
	}
	$passwords = explode( "\n", str_replace( "\r", "", $passwords ) );
	return $passwords;
}

/**
 * Create the password form shortcode
 * @since 1.0.0
 */
function wcmo_password_form( $atts ) {
	$atts = extract(
		shortcode_atts(
			array(
				'label'								=> __( 'Enter your password', 'wcmo' ),
				'button'							=> __( 'Submit', 'wcmo' ),
				'validation_failure'	=> __( 'You have entered the incorrect password', 'wcmo' ),
				'validation_success'	=> __( 'You have entered the correct password', 'wcmo' )
			),
			$atts,
			'wcmo_password_form'
		)
	);
	ob_start(); ?>
	<form method="post" id="wcmo_password_form">
		<?php $show_form = true;
		if( isset( $_GET['wcmo'] ) && $_GET['wcmo'] == 'password_fail' ) {
			// Password failure message ?>
			<p>
				<?php echo esc_html( $validation_failure ); ?>
			</p>
		<?php } else if( isset( $_GET['wcmo'] ) && $_GET['wcmo'] == 'password_correct' ) { ?>
			<p>
				<?php echo esc_html( $validation_success );
				$show_form = false; ?>
			</p>
		<?php } ?>
		<?php if( $show_form ) {
			// Don't show the form if the password is correct ?>
			<p>
				<label for="wcmo_password_field"><?php echo esc_html( $label ); ?></label>
				<input type="text" name="wcmo_password_field" id="wcmo_password_field" autocomplete="off">
			</p>
			<?php wp_nonce_field( 'wcmo_password_nonce', 'wcmo_password_nonce' ); ?>
			<p>
				<button type="submit" name="wcmo_password_submit" id="wcmo_password_submit" value="<?php echo esc_html( $button ); ?>"><?php echo esc_html( $button ); ?></button>
			</p>
		<?php } ?>
	</form>
	<?php $form = ob_get_clean();
	return $form;
}
add_shortcode( 'wcmo_password_form', 'wcmo_password_form' );

/**
 * Validate the password form
 * @since 1.0.0
 */
function wcmo_validate_password_form() {

	// Check that page has the password form
	global $post;

	if( ! isset( $post->post_content ) || ! has_shortcode( $post->post_content, 'wcmo_password_form' ) ) {
		return;
	}
	if( ! isset( $_POST['wcmo_password_nonce'] ) || ! wp_verify_nonce( $_POST['wcmo_password_nonce'], 'wcmo_password_nonce' ) ) {
		return;
	}

	$referrer = isset( $_GET['wcmo_referrer'] ) ? $_GET['wcmo_referrer'] : false;

	// $passwords = wcmo_get_passwords();
	$url = get_permalink();
	$success = wcmo_set_access_status( $_POST['wcmo_password_field'] );

	if( ! $success ) {

		$args = array(
			'wcmo'	=> 'password_fail'
		);
		if( $referrer ) {
			$args['wcmo_referrer'] = $referrer;
		}

		// Incorrect or empty password
		$url = add_query_arg(
			$args,
			$url
		);
		wp_redirect( $url );
		die;

	} else {

		// Correct password so check where to go
		// wcmo_set_access_status( true, $_POST['wcmo_password_field'] );
		$url = wcmo_get_redirect_url( $_POST['wcmo_password_field'] );
		$redirect_page = get_option( 'wcmo_redirect_page' );

		if( $redirect_page == 'referrer' && isset( $_GET['wcmo_referrer'] ) ) {

			$url = get_site_url() . $_GET['wcmo_referrer'];

		} else {

			$url = add_query_arg(
				array(
					'wcmo'	=> 'password_correct'
				),
				$url
			);

		}

		wp_redirect( $url );
		die;

	}

}
add_action( 'template_redirect', 'wcmo_validate_password_form' );

/**
 * Set the user's access status
 * @param $password The password entered
 * @return Boolean
 * @since 1.0.0
 */
function wcmo_set_access_status( $password ) {

	$status = false;
	if( ! $password ) return false;

	// Category specific passwords are problematic - we need to define which categories the user has access to
	if( wcmo_get_restricted_content() == 'category' ) {

		// Check if restricted content is set to category
		$permitted_categories = array();
		$categories = get_terms( 'product_cat' );
		if( $categories ) {
			// Check if any categories have a local rule
			foreach( $categories as $cat ) {
				$override = get_term_meta( $cat->term_id, 'wcmo_override_global_restrictions', true );
				if( $override == 'yes' ) {
					// This category has a local restriction
					$local_rules = wcmo_get_category_local_rules_by_id( $cat->term_id );
					if( $local_rules ) {
						$local_passwords = wcmo_get_passwords( $local_rules['passwords'] );
						if( is_array( $local_passwords ) && in_array( $password, $local_passwords ) ) {
							// Check if we've entered a password for this category
							$permitted_categories[] = $cat->term_id;
							$status = true;
						}
					}
				}
			}
		}
		WC()->session->set( 'wcmo_access_cats', $permitted_categories );

	}

	// Check if the user has correctly entered the global password
	$passwords = wcmo_get_passwords();
	if( is_array( $passwords ) && in_array( $_POST['wcmo_password_field'], $passwords ) ) {
		$status = true;
	}
	WC()->session->set( 'wcmo_can_access_global', $status );

	// Check for product specific passwords
	$product_passwords = get_transient( 'wcmo_product_passwords', array() );

	$product_ids = array();
	if( $product_passwords ) {
		// Check through each product to see if we've entered a valid password
		foreach( $product_passwords as $product_id=>$passwords ) {
			$passwords = wcmo_get_passwords( $passwords );
			if( is_array( $passwords ) && in_array( $_POST['wcmo_password_field'], $passwords ) ) {
				// This is a product specific password, okay?
				$product_ids[] = $product_id;
			}
		}
	}

	if( ! empty( $product_ids ) ) {
		// We've found some product specific passwords
		WC()->session->set( 'wcmo_access_products', $product_ids );
		$status = true;
	}

	return $status;
}

/**
 * Manually start the WC session
 * @since 1.0.2
 */
function wcmo_start_wc_session() {

	if( is_admin() ) {
		// Only run this on the front end otherwise we'll get an error below
		return;
	}

	$restriction_method = wcmo_get_restriction_method();
	// We need to initiate the session to record whether the password has been accepted
	if( ( $restriction_method == 'password' || get_option( 'wcmo_uses_local_password' ) ) && ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
	}

}
// add_action( 'woocommerce_init', 'wcmo_start_wc_session' );
// Call later to avoid error
add_action( 'wp', 'wcmo_start_wc_session' );
