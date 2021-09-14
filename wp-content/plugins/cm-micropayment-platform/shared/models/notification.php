<?php

class CMMicropaymentPlatformNotification
{

    public static function send($user_id, $slug, array $values)
    {
        require_once(ABSPATH . 'wp-includes/pluggable.php');

        $shortcodes = array('[senderWalletID]', '[withdrawedWalletID]', '[senderID]', '[withdrawerID]', '[amountPoints]', '[withdrawerName]', '[withdrawerEmail]', '[senderName]', '[senderEmail]', '[message]');
		$new_shortcodes = array( '[toWalletID]', '[fromWalletID]', '[toID]', '[fromID]', '[amountPoints]', '[fromName]', '[fromEmail]', '[toName]', '[toEmail]', '[message]' );

        if( !CMMicropaymentPlatform::get_option('cm_micropayment_send_notifications') ) return;

        $message = CMMicropaymentPlatform::get_option('cm_micropayment_' . $slug);
        $title = CMMicropaymentPlatform::get_option('cm_micropayment_' . $slug . '_title');
		
		if ( is_numeric($user_id) ) {
			$user = get_userdata($user_id);
			if ( !$user ) return;
			if ( !$user->user_email || $user->user_email == '' || trim($user->user_email) == '' ) return;
			$user_email = $user->user_email;
		} else {
			$user_email = $user_id;
		}

        if( trim($message) == '' ) return;

        $title = stripslashes($title);
        $message = stripslashes($message);
        $message = str_replace($shortcodes, $new_shortcodes, $message);

        foreach($new_shortcodes as $keyword)
        {
            $keySlug = str_replace(array('[', ']'), array('', ''), $keyword);
            if( isset($values[$keySlug]) )
            {
                $message = str_replace($keyword, $values[$keySlug], $message);
            }
        }

        wp_mail($user_email, $title, $message);
    }

}