<div class="message">
	<?php
	$message = Codemanas\ZoomPro\Helpers::get_admin_notice();
	if ( ! empty( $message ) ) {
		echo $message;
	}
	?>
</div>
<form method="POST" action="">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" valign="top">
				<?php _e( 'Registration Email ?', 'vczapi-pro' ); ?>
            </th>
            <td>
                <input id="registration_email" name="registration_email" type="checkbox" class="regular-text" <?php ! empty( $this->settings ) && ! empty( $this->settings['registraion_email'] ) ? checked( $this->settings['registraion_email'], 'on' ) : false; ?> />
                <span class="description" for="registration_email"><?php _e( 'Checking this option will enable email after a user is registered into a meeting.', 'vczapi-pro' ); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" valign="top">
				<?php _e( 'Hide Add to Calender links ?', 'vczapi-pro' ); ?>
            </th>
            <td>
                <input id="vczapi-pro-hide-gcal-links" name="hide_ical_links" type="checkbox" class="regular-text" <?php ! empty( $this->settings ) && ! empty( $this->settings['hide_ical_links'] ) ? checked( $this->settings['hide_ical_links'], 'on' ) : false; ?> />
                <span class="description" for="hide_ical_links"><?php _e( 'Checking this option will hide add to calendar and add to Google calendar links from single pages and archive pages.', 'vczapi-pro' ); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" valign="top">
				<?php _e( 'Hide add to Calender links for not purchased meetings ?', 'vczapi-pro' ); ?>
            </th>
            <td>
                <input id="vczapi-pro-hide-gcal-links-for-not-purchased" name="hide_ical_links_woocommerce" type="checkbox" class="regular-text" <?php ! empty( $this->settings ) && ! empty( $this->settings['hide_ical_links_woocommerce'] ) ? checked( $this->settings['hide_ical_links_woocommerce'], 'on' ) : false; ?> />
                <span class="description" for="hide_ical_links_woocommerce"><?php _e( 'Checking this option will hide add to calendar and add to Google calendar links for products which are not yet purchased.', 'vczapi-pro' ); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" valign="top">
				<?php _e( 'Disable Reminder Emails', 'vczapi-pro' ); ?>
            </th>
            <td>
                <input id="vczapi-pro-reminder-emails" name="reminder_emails[]" type="checkbox" value="24" <?php echo ! empty( $this->settings ) && ! empty( $this->settings['reminder_emails_registrants'] ) && in_array( '24', $this->settings['reminder_emails_registrants'] ) ? 'checked="checked"' : ''; ?> > <?php _e( '24 hours before meeting', 'vczapi-pro' ); ?><br>
                <p class="description"><?php _e( 'Check this option to disable email notification for 24 hours before the meeting.', 'vczapi-pro' ); ?></p>
                <p><a href="<?php echo admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=pro-licensing&section=email-templates' ); ?>">Goto this page</a> <?php _e( 'and search for Email Reminders section to know more about the variables you can add to your email template.', 'vczapi-pro' ); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" valign="top">
				<?php _e( 'Inline Registration Form', 'vczapi-pro' ); ?>
            </th>
            <td>
                <input id="vczapi-pro-inline-registration-form" name="inline_registration_form" type="checkbox" class="regular-text" <?php ! empty( $this->settings ) && ! empty( $this->settings['inline_registration_form'] ) ? checked( $this->settings['inline_registration_form'], 'on' ) : false; ?> />
                <span class="description"><?php _e( 'Checking this option will enable registration form on the meeting page instead of seperate page.', 'vczapi-pro' ); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" valign="top">
				<?php _e( 'Create user on Registration ?', 'vczapi-pro' ); ?>
            </th>
            <td>
                <input id="create_user_on_registration" name="create_user_on_registration" type="checkbox" class="regular-text" <?php ! empty( $this->settings ) && ! empty( $this->settings['create_user_on_registration'] ) ? checked( $this->settings['create_user_on_registration'], 'on' ) : false; ?> />
                <span class="description"><?php _e( 'Checking this option will create a new user if not exists when registration form is submitted. Default role for the user will be', 'vczapi-pro' ); ?> <i><strong><?php _e( 'SUBSCRIBER', 'vczapi-pro' ); ?>.</strong></i> <?php printf( __( 'Goto "emails" tab for changing email details and follow this %s to change role of user.', 'vczapi-pro' ), '<a href="https://gist.github.com/techies23/f5e208f9c04dbc7f29ae6571d7642643" target="_blank">Link</a>' ); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" valign="top">
				<?php _e( 'Meeting/Webinar Registration Fields', 'vczapi-pro' ); ?>
            </th>
            <td>
                <table class="vczapi-data-table vczapi-registration-addtional-fields">
                    <thead>
                    <tr>
                        <th><?php _e( 'Field', 'vczapi-pro' ); ?></th>
                        <th><?php _e( 'Required', 'vczapi-pro' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[address][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['address']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Address', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[address][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['address']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['address']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[city][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['city']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'City', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[city][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['city']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['city']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[country][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['country']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Country', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[country][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['country']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['country']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[zip][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['zip']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Zip', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[zip][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['zip']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['zip']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[state][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['state']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'State', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[state][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['state']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['state']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[phone][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['phone']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Phone', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[phone][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['phone']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['phone']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[industry][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['industry']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Industry', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[industry][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['industry']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['industry']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[org][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['org']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Organization', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[org][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['org']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['org']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[job_title][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['job_title']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Job Title', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[job_title][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['job_title']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['job_title']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[purchasing_time_frame][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['purchasing_time_frame']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Purchasing Time Frame', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[purchasing_time_frame][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['purchasing_time_frame']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['purchasing_time_frame']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[role_in_purchase_process][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['role_in_purchase_process']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Role in Purchase process', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[role_in_purchase_process][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['role_in_purchase_process']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['role_in_purchase_process']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[no_of_employees][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['no_of_employees']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Number of Employees', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[no_of_employees][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['no_of_employees']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['no_of_employees']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="meeting_registration_fields[comments][enable]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['comments']['enable'] ) ? 'checked' : false; ?>> <?php _e( 'Comments', 'vczapi-pro' ); ?></td>
                        <td><input type="checkbox" name="meeting_registration_fields[comments][required]" <?php echo ! empty( $this->settings['meeting_registration_fields'] ['comments']['enable'] ) && ! empty( $this->settings['meeting_registration_fields'] ['comments']['required'] ) ? 'checked' : false; ?>></td>
                    </tr>
                    </tbody>
                </table>
                <p class="description"><?php esc_html_e( 'This is global field page. If you check options here - Zoom registration event page will show the fields based on these settings. You can override these settings by individually selecting fields when creating individual meetings. Select none to disable this. You will need to update your meetings after you change this setting.', 'vczapi-pro' ); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
    <p><input type="submit" class="button button-primary" name="save_registration_details" value="Save"></p>
</form>

