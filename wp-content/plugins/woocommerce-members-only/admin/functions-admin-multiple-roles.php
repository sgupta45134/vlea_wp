<?php
/**
 * Functions for enabling multiple roles
 * @forked https://github.com/humanmade/multiple-roles
 * @since 1.7.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wcmo_remove_dropdown( $hook ) {

	if( wcmo_get_multiple_roles_approval() != 'yes' ) {
		return;
	}

	if ( 'user-edit.php' !== $hook && 'user-new.php' !== $hook) {
		return;
	}

	wp_enqueue_script( 'wcmo-multiple-roles', trailingslashit( WCMO_PLUGIN_URL ) . 'assets/js/wcmo-multi-roles-script.js', array( 'jquery' ), WCMO_PLUGIN_VERSION );

}
add_action( 'admin_enqueue_scripts', 'wcmo_remove_dropdown' );

/**
 * Output the checklist view. If the user is not allowed to edit roles,
 * nothing will appear.
 *
 * @param object $user The current user object.
 */
function wcmo_output_checklist( $user ) {

	if( wcmo_get_multiple_roles_approval() != 'yes' ) {
		return;
	}

	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}

	wp_nonce_field( 'update-md-multiple-roles', 'md_multiple_roles_nonce' );

	$roles = get_editable_roles();
	$user_roles = ( isset( $user->roles ) ) ? $user->roles : null;

	/**
	 * Output the roles checklist.
	 *
	 * @var $roles array All WordPress roles in name => label pairs.
	 * @var $user_roles array An array of role names belonging to the current user.
	 */
	$creating = isset( $_POST['createuser'] );
	$selected_roles = $creating && isset( $_POST['md_multiple_roles'] ) ? wp_unslash( $_POST['md_multiple_roles'] ) : '';
	?>
	<h3><?php _e( 'Permissions', 'multiple-roles' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><?php _e( 'Roles', 'multiple-roles' ); ?></th>
			<td>
				<?php foreach( $roles as $name => $label ) :
					$input_uniq_id = uniqid(); ?>
					<label for="md-multiple-roles-<?php echo esc_attr( $name ) . '-' . $input_uniq_id; ?>">
						<input
							id="md-multiple-roles-<?php echo esc_attr( $name ) . '-' . $input_uniq_id; ?>"
							type="checkbox"
							name="md_multiple_roles[]"
							value="<?php echo esc_attr( $name ); ?>"
	                        <?php if ( ! is_null( $user_roles ) ) : // Edit user page
	                            checked( in_array( $name, $user_roles ) );
							elseif ( ! empty( $selected_roles ) ) : // Add new user page
								checked( in_array( $name, $selected_roles ) );
	                        endif; ?>
						/>
						<?php echo esc_html( translate_user_role( $label['name'] ) ); ?>
					</label>
					<br />
				<?php endforeach; ?>
			</td>
		</tr>
	</table>

<?php
}
add_action( 'show_user_profile', 'wcmo_output_checklist' );
add_action( 'edit_user_profile', 'wcmo_output_checklist' );
add_action( 'user_new_form', 'wcmo_output_checklist' );

/**
 * Update the given user's roles as long as we've passed the nonce
 * and permissions checks.
 *
 * @param int $user_id The user ID whose roles might get updated.
 */
function wcmo_process_checklist( $user_id ) {

	if( wcmo_get_multiple_roles_approval() != 'yes' ) {
		return;
	}

	if ( isset( $_POST['md_multiple_roles_nonce'] ) && ! wp_verify_nonce( $_POST['md_multiple_roles_nonce'], 'update-md-multiple-roles' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}

	$new_roles = ( isset( $_POST['md_multiple_roles'] ) && is_array( $_POST['md_multiple_roles'] ) ) ? $_POST['md_multiple_roles'] : array();
	if ( empty( $new_roles ) ) {
		return;
	}

	wcmo_multi_update_roles( $user_id, $new_roles );

}
add_action( 'profile_update', 'wcmo_process_checklist' );


/**
 * Erase the user's existing roles and replace them with the new array.
 *
 * @param integer $user_id The WordPress user ID.
 * @param array $roles The new array of roles for the user.
 *
 * @return bool
 */
function wcmo_multi_update_roles( $user_id = 0, $roles = array() ) {

	if( wcmo_get_multiple_roles_approval() != 'yes' ) {
		return;
	}

	if ( empty( $roles ) ) {
		return false;
	}

	$roles = array_map( 'sanitize_key', ( array ) $roles );
	$roles = array_filter( ( array ) $roles, 'get_role' );

	$user = get_user_by( 'id', ( int ) $user_id );

	// remove all roles
	$user->set_role( '' );

	foreach( $roles as $role ) {
		$user->add_role( $role );
	}

	return true;
}
