<?php
/**
 * The Template for frontend meeting createasdas
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/frontend/create-meeting.php.
 *
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $zoom;

if ( ! empty( $zoom ) ) {
	?>
    <div class="vczapi-pro-form-wrapper">
		<?php if ( ! empty( $zoom['current_page'] ) ) { ?>
            <a href="<?php echo esc_url( $zoom['current_page'] ); ?>" rel="nofollow"><?php _e( 'Back to List', 'vczapi-pro' ); ?></a>
		<?php } ?>
        <form id="vczapi-pro-frontend-meeting-create-form" class="vczapi-pro-frontend-meeting-create-form" action="" method="POST">
			<?php wp_nonce_field( 'vczapi-pro-create-meeting' ); ?>
			<?php
			if ( ! empty( $zoom['post'] ) ) {
				echo '<input type="hidden" value="' . $zoom['post']->ID . '" name="post_id">';
			}
			if ( ! empty( $zoom['current_page'] ) ) {
				?>
                <input type="hidden" value="<?php echo esc_url( $zoom['current_page'] ); ?>" name="redirect_to">
				<?php
			}
			?>
            <div class="vczapi-pro-row">
                <div class="vczapi-pro-col-md-12 vczapi-api-meeting-create-notifications"></div>
                <div class="vczapi-pro-form-fields">
                    <div class="vczapi-pro-col-md-12">
                        <label for="vczapi-pro-meeting-title"><?php _e( 'Title', 'vczapi-pro' ); ?> *</label>
                        <input type="text" class="vczapi-pro-form-control vczapi-pro-meeting-title" name="meeting_title" id="vczapi-pro-meeting-title" placeholder="<?php _e( 'Title of your meeeting.', 'vczapi-pro' ); ?>" value="<?php echo ! empty( $zoom['post'] ) && ! empty( $zoom['post']->post_title ) ? $zoom['post']->post_title : false; ?>" data-required="true">
                    </div>
                    <div class="vczapi-pro-col-md-12">
                        <label for="vczapi-pro-meeting-description"><?php _e( 'Description', 'vczapi-pro' ); ?></label>
						<?php wp_editor( ! empty( $zoom['post'] ) && ! empty( $zoom['post']->post_content ) ? $zoom['post']->post_content : false, 'meeting_description', array( 'textarea_rows' => 14 ) ); ?>
                    </div>
					<?php
					if ( ! empty( $zoom['post'] ) && ! empty( $zoom['meeting_fields'] ) && ! empty( $zoom['meeting_fields']['userId'] ) ) {
						echo '<input type="hidden" name="meeting_host" value="' . $zoom['meeting_fields']['userId'] . '">';
					} else if ( ! empty( $zoom['users'] ) ) {
						?>
                        <div class="vczapi-pro-col-md-12">
                            <label for="vczapi-pro-meeting-host"><?php _e( 'Meeting Host', 'vczapi-pro' ); ?> *</label>
                            <select class="vczapi-pro-form-control vczapi-pro-form-control-select2" name="meeting_host" data-required="true">
                                <option value=""><?php _e( 'Select a Host', 'vczapi-pro' ); ?></option>
								<?php foreach ( $zoom['users'] as $user ) { ?>
                                    <option <?php ! empty( $zoom['meeting_fields'] ) && ! empty( $zoom['meeting_fields']['userId'] ) ? selected( esc_attr( $zoom['meeting_fields']['userId'] ), $user->id ) : false; ?> value="<?php echo $user->id; ?>"><?php echo esc_html( $user->first_name ) . ' ( ' . esc_html( $user->email ) . ' )'; ?></option>
								<?php } ?>
                            </select>
                        </div>
						<?php
					} else {
						_e( 'Did not find any hosts here ? Please contact administrator.', 'vczapi-pro' );
					}
					?>
                    <div class="vczapi-pro-col-md-12">
                        <label for="vczapi-pro-meeting-host"><?php _e( 'Type', 'vczapi-pro' ); ?></label>
                        <select id="vczapi-pro-meeting-type" name="meeting_type" class="vczapi-pro-form-control">
                            <option value="1" <?php ! empty( $zoom['meeting_fields'] ) && ! empty( $zoom['meeting_fields']['meeting_type'] ) ? selected( esc_attr( absint( $zoom['meeting_fields']['meeting_type'] ) ), 1 ) : false; ?>><?php _e( 'Meeting', 'vczapi-pro' ); ?></option>
                            <option value="2" <?php ! empty( $zoom['meeting_fields'] ) && ! empty( $zoom['meeting_fields']['meeting_type'] ) ? selected( esc_attr( absint( $zoom['meeting_fields']['meeting_type'] ) ), 2 ) : false; ?>><?php _e( 'Webinar', 'vczapi-pro' ); ?></option>
                        </select>
                    </div>
                    <div class="vczapi-pro-col-md-12">
                        <label for="vczapi-pro-meeting-start-time"><?php _e( 'Start Date/Time', 'vczapi-pro' ); ?> *</label>
                        <input type="text" class="vczapi-pro-form-control vczapi-pro-meeting-start-time" name="meeting_start_time" id="vczapi-pro-meeting-start-time" value="<?php echo ! empty( $zoom['meeting_fields'] ) && ! empty( $zoom['meeting_fields']['start_date'] ) ? esc_attr( $zoom['meeting_fields']['start_date'] ) : false; ?>" data-required="true" data-existingdate="<?php echo ! empty( $zoom['meeting_fields'] ) && ! empty( $zoom['meeting_fields']['start_date'] ) ? esc_attr( $zoom['meeting_fields']['start_date'] ) : false; ?>">
                    </div>
                    <div class="vczapi-pro-col-md-12">
                        <label for="vczapi-pro-meeting-timezone"><?php _e( 'Timezone', 'vczapi-pro' ); ?> *</label>
						<?php
						$tzlists     = zvc_get_timezone_options();
						$wp_timezone = zvc_get_timezone_offset_wp();
						?>
                        <select id="vczapi-pro-meeting-timezone" name="meeting_timezone" class="vczapi-pro-form-control vczapi-pro-form-control-select2">
							<?php foreach ( $tzlists as $k => $tzlist ) {
								$option_tz_selected = false;
								if ( ! empty( $zoom['meeting_fields'] ) && ! empty( $zoom['meeting_fields']['timezone'] ) ) {
									$option_tz_selected = selected( $k, $zoom['meeting_fields']['timezone'], false );
								} else if ( ! empty( $wp_timezone ) && ! empty( $tzlists[ $wp_timezone ] ) && $tzlists[ $wp_timezone ] !== false ) {
									$option_tz_selected = selected( $k, $wp_timezone, false );
								}
								?>
                                <option value="<?php echo $k; ?>" <?php echo $option_tz_selected; ?>><?php echo esc_html( $tzlist ); ?></option>
							<?php } ?>
                        </select>
                    </div>
                    <div class="vczapi-pro-col-md-12">
                        <label for="vczapi-pro-meeting-password"><?php _e( 'Password', 'vczapi-pro' ); ?></label>
                        <input type="text" maxlength="10" data-maxlength="10" class="vczapi-pro-form-control vczapi-pro-meeting-password" name="meeting_password" id="vczapi-pro-meeting-password" value="<?php echo ! empty( $zoom['meeting_details'] ) && ! empty( $zoom['meeting_details']->password ) ? esc_attr( $zoom['meeting_details']->password ) : false; ?>">
                        <p class="description"><?php _e( 'Password to join the meeting. Password may only contain the following characters: [a-z A-Z 0-9]. Max of 10 characters.( Leave blank for auto generate )', 'vczapi-pro' ); ?></p>
                    </div>

					<?php do_action( 'vczapi_pro_frontend_create_meeting_extra_fields' ); ?>

                    <div class="vczapi-pro-col-md-12">
                        <input type="submit" class="vczapi-pro-btn" name="meeting_save" id="vczapi-pro-meeting-save-btn" value="<?php ! empty( $zoom['post'] ) ? _e( 'Update Meeting', 'vczapi-pro' ) : _e( 'Create Meeting', 'vczapi-pro' ); ?>">
                    </div>
                </div>
            </div>
        </form>
    </div>
	<?php
} else {
	echo "<p>" . __( 'This meeting does not exist anymore. Please create a new one.', 'vczapi-pro' ) . "</p>";
}

