<?php
/**
 * Functions for the user profile
 * @since 1.8.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Add membership fields to user profiles
 * @since 1.8.0
 */
function wcmo_user_profile( $user ) {

	$expiring_roles = get_option( 'wcmo_expiring_roles', array() );

	if( ! empty( $expiring_roles ) ) {

		printf(
			'<h2>%s</h2>',
			__( 'Role Expiry Dates', 'wcmo' )
		); ?>

		<table class="form-table">
			<?php
			$date_format = get_option( 'date_format' );
			$roles = get_editable_roles();
			foreach( $expiring_roles as $role ) { ?>
				<tr>
					<th scope="row"><?php echo esc_html( $roles[$role]['name'] ); ?></th>
					<?php $expires = get_user_meta( $user->ID, $role . '_expires', true );
					if( $expires ) {
						$nice_expires = date( $date_format, $expires );
					} else {
						$nice_expires = '';
					} ?>
					<td>
						<input readonly type="text" name="<?php echo esc_attr( $role . '_expires' ); ?>" id="<?php echo esc_attr( $role . '_expires' ); ?>" value="<?php echo esc_attr( $nice_expires ); ?>" />
					</td>
				</tr>

				<?php $remind_on = get_user_meta( $user->ID, $role . '_remind_on', true );
				if( $remind_on ) { ?>
					<tr>
						<th scope="row"><?php echo esc_html( $roles[$role]['name'] ); ?>: <?php _e( 'Remind on', 'wcmo' ); ?></th>
						<?php $nice_remind_on = date( $date_format, $remind_on ); ?>
						<td>
							<input readonly type="text" name="<?php echo esc_attr( $role . '_remind_on' ); ?>" id="<?php echo esc_attr( $role . '_remind_on' ); ?>" value="<?php echo esc_attr( $nice_remind_on ); ?>" />
						</td>
					</tr>
				<?php }
			}

		} ?>

	</table>

	<?php
	printf(
		'<h2>%s</h2>',
		__( 'Extra Information', 'wcmo' )
	); ?>

	<table class="form-table">

		<?php $vat_number = get_user_meta( $user->ID, 'vat_number', true );
		if( $vat_number ) { ?>
			<tr>
				<th scope="row"><?php _e( 'VAT Number', 'wcmo' ) ?></th>
				<td><input type="text" name="<?php echo esc_attr( 'vat_number' ); ?>" id="<?php echo esc_attr( 'vat_number' ); ?>" value="<?php echo esc_attr( $vat_number ); ?>" /></td>
			</tr>
		<?php }

		// Look for custom fields
		$registration_fields = wcmo_get_registration_fields();
		$enabled_fields = wcmo_get_enabled_registration_fields();

		if( $registration_fields ) {

			foreach( $registration_fields as $field_id=>$new_field ) {

				if( isset( $enabled_fields['add_to_profile'][$field_id] ) ) {
					printf(
						'<tr><th scope="row">%s</th>',
						esc_attr( $new_field['label'] )
					);
					$value = get_user_meta( $user->ID, $field_id, true );

					if( isset( $new_field['type' ] ) && $new_field['type' ] == 'checkbox' ) {

						printf(
							'<td><input type="checkbox" name="%s" id="%s" %s value="1" /></td>',
							esc_attr( $field_id ),
							esc_attr( $field_id ),
							checked( 1, $value, false )
						);

					} else {

						printf(
							'<td><input type="text" name="%s" id="%s" value="%s" /></td>',
							esc_attr( $field_id ),
							esc_attr( $field_id ),
							esc_attr( $value )
						);

					}

					echo '</tr>';

				}

			}

		}

		do_action( 'wcmo_user_profile_after_registration_fields', $user ); ?>

	</table>

<?php }
add_action( 'show_user_profile', 'wcmo_user_profile' );
add_action( 'edit_user_profile', 'wcmo_user_profile' );

function wcmo_save_user_fields( $user_id ) {

  if( ! current_user_can('edit_user', $user_id ) ) {
		return false;
	}

	if( isset( $_POST['vat_number'] ) ) {
		update_user_meta( $user_id, 'vat_number', sanitize_text_field( $_POST['vat_number'] ) );
	}

	// Save custom profile fields
	$registration_fields = wcmo_get_registration_fields();
	if( $registration_fields ) {

		foreach( $registration_fields as $field_id=>$new_field ) {

			if( ! empty( $_POST[$field_id] ) ) {
				update_user_meta( $user_id, $field_id, sanitize_text_field( $_POST[$field_id] ) );
			} else {
				delete_user_meta( $user_id, $field_id );
			}

		}

	}

}
add_action( 'personal_options_update', 'wcmo_save_user_fields' );
add_action( 'edit_user_profile_update', 'wcmo_save_user_fields' );
