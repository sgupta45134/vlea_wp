<?php
$current_user_id = get_current_user_id();
$host_id         = get_user_meta( $current_user_id, 'user_zoom_hostid', true );
?>
<div class="vczapi-pro-sync-wrapper" style="margin-top:30px;">
    <div class="vczapi-notification vczapi-pro-notify">
        <p><?php _e( "Choose a method to sync your meeting.", "vczapi-pro" ); ?></p>
    </div>
    <table class="form-table" role="presentation">
        <tbody>
        <tr>
            <th scope="row"><label for="sync_type"><?php _e( "Sync Type", "vczapi-pro" ); ?></label></th>
            <td>
                <select id="vczapi-pro-sync-type" class="vczapi-pro-sync-type" name="sync_type">
                    <option value="1"><?php _e( "Meeting", "vczapi-pro" ); ?></option>
                    <option value="2"><?php _e( "Webinar", "vczapi-pro" ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sync_by"><?php _e( "Sync Method", "vczapi-pro" ); ?></label></th>
            <td>
                <span style="padding-bottom:5px; display: block;"><input name="sync_method" type="radio" id="vczapi-pro-sync-method" value="1" class="regular-text vczapi-pro-sync-method"> <?php _e( "Meeting/Webinar ID", "vczapi-pro" ); ?></span>
                <span style="display: block;"><input name="sync_method" type="radio" id="vczapi-pro-sync-method" value="2" class="regular-text vczapi-pro-sync-method"> <?php _e( "User", "vczapi-pro" ); ?></span>
            </td>
        </tr>
        <tr style="display: none;" class="vczapi-pro-show-sync-method-meetingid">
            <th scope="row"><label for="sync_by"><?php _e( "Choose User", "vczapi-pro" ); ?></label></th>
            <td>
				<?php
				if ( ! empty( $host_id ) ) {
					$user = get_userdata( $current_user_id );
					?>
                    <select class="zvc-hacking-select vczapi-pro-sync-meeting-id-user-id">
                        <option value="<?php echo $host_id; ?>"><?php echo $user->first_name . ' ( ' . $user->user_email . ' )'; ?></option>
                    </select>
				<?php } else if ( ! empty( $users ) ) { ?>
                    <select class="zvc-hacking-select vczapi-pro-sync-meeting-id-user-id">
                        <option value=""><?php _e( 'Select a User', 'vczapi-pro' ); ?></option>
						<?php foreach ( $users as $user ) { ?>
                            <option value="<?php echo $user->id; ?>"><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
						<?php } ?>
                    </select>
				<?php } else { ?>
                    <p><?php _e( "Cache data did not loa properly. Please refresh the page once again.", "vczapi-pro" ); ?></p>
				<?php } ?>

                <p class="description"><?php _e( "Choose which user the inserted meeting ID belongs to. This needs to be correct.", "vczapi-pro" ); ?></p>
            </td>
        </tr>
        <tr style="display: none;" class="vczapi-pro-show-sync-method-meetingid">
            <th scope="row"><label for="sync_by"><?php _e( "Enter a Meeting/Webinar ID", "vczapi-pro" ); ?></label></th>
            <td>
                <input name="meeting_id" type="number" id="vczapi-pro-sync-meeting-id" placeholder="99234288981" class="regular-text vczapi-pro-sync-meeting-id">
                <p class="description"><?php _e( "Insert meeting ID that belongs to above selected user. Usually, 11 characters.", "vczapi-pro" ); ?></p>
            </td>
        </tr>
        <tr style="display: none;" class="vczapi-pro-show-sync-method-user">
            <th scope="row"><label for="sync_by"><?php _e( "Sync by User", "vczapi-pro" ); ?></label></th>
            <td>
				<?php
				if ( ! empty( $host_id ) ) {
					$user = get_userdata( $current_user_id );
					?>
                    <select class="vczapi-pro-sync-by-user zvc-hacking-select">
                        <option value=""><?php _e( 'Select a User', 'vczapi-pro' ); ?></option>
                        <option value="<?php echo $host_id; ?>"><?php echo $user->first_name . ' ( ' . $user->user_email . ' )'; ?></option>
                    </select>
				<?php } else if ( ! empty( $users ) ) { ?>
                    <select class="vczapi-pro-sync-by-user zvc-hacking-select">
                        <option value=""><?php _e( 'Select a User', 'vczapi-pro' ); ?></option>
						<?php foreach ( $users as $user ) { ?>
                            <option value="<?php echo $user->id; ?>"><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
						<?php } ?>
                    </select>
				<?php } else { ?>
                    <p><?php _e( "Cache data did not loa properly. Please refresh the page once again.", "vczapi-pro" ); ?></p>
				<?php } ?>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="vczapi-pro-user-results vczapi-pro-show-sync-method-user"></div>
    <div class="vczapi-pro-show-sync-method-meetingid" style="display: none;">
        <p class="submit"><input type="submit" name="sync_via_meeting_id" id="vczapi-pro-sync-via-meeting-id" class="button button-primary vczapi-pro-sync-via-meeting-id" value="<?php _e( 'Import', 'vczapi-pro' ); ?>"></p>
    </div>
</div>