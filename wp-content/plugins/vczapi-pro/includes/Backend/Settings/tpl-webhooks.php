<?php
$home_uri = home_url();
?>
<div class="vczapi-pro-admin-content-wrap">
    <h3><?php _e( 'Documentation', 'vczapi-pro' ); ?></h3>
    <p><?php _e( 'Zoom utilizes webhooks as a medium to notify this plugin (consumer application) about events that occur in a Zoom account. Instead of making repeated calls to pull data frequently from the Zoom API, you can use webhooks to get information on events that happen in a Zoom account.', 'vczapi-pro' ); ?></p>
    <p style="color: red;font-size: 15px;"><strong>You can find detailed <a target="_blank" href="https://zoom.codemanas.com/webhooks">documentation</a> from this <a target="_blank" href="https://zoom.codemanas.com/webhooks">page</a>.</strong></p>

    <form action="" method="POST">
        <table class="form-table">
            <tbody>
            <tr>
                <th><label><?php _e( 'Verification Code', 'vczapi-pro' ); ?></label></th>
                <td>
                    <input type="text" class="regular-text" name="verification_code" value="<?php echo ! empty( $this->settings ) && ! empty( $this->settings['verification_code'] ) ? $this->settings['verification_code'] : '' ?>">
                    <input type="submit" class="button button-primary" name="save_verification_code" value="<?php _e( 'Save', 'vczapi-pro' ); ?>">
                    <p class="description"><?php printf( __( 'Get your verification code from your %s. Verification code needs to be exact.', 'vczapi-pro' ), '<a href="https://marketplace.zoom.us/develop/">Zoom Marketplace</a>' ); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
    <table class="vczapi-pro-admin-webhook-table">
        <tbody>
        <tr>
            <th><?php _e( 'Meetings Endpoint', 'vczapi-pro' ); ?></th>
            <td class="vczapi-pro-admin-webhook-endpoint-text">
                <span class="dashicons dashicons-admin-page"></span> <input class="vczapi-pro-admin-webhook-endpoint-text-box" type="text" readonly value='<?php echo $home_uri . '/wp-json/vczapi/v1/meeting'; ?>' onclick="this.select(); document.execCommand('copy'); alert('Copied to clipboard');"/>
            </td>
        </tr>
        <tr>
            <th><?php _e( 'Webinars Endpoint', 'vczapi-pro' ); ?></th>
            <td class="vczapi-pro-admin-webhook-endpoint-text">
                <span class="dashicons dashicons-admin-page"></span> <input class="vczapi-pro-admin-webhook-endpoint-text-box" type="text" readonly value='<?php echo $home_uri . '/wp-json/vczapi/v1/webinar'; ?>' onclick="this.select(); document.execCommand('copy'); alert('Copied to clipboard');"/>
            </td>
        </tr>
        </tbody>
    </table>
</div>