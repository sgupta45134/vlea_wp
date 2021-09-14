<?php

namespace com\cminds\payperposts\model;

class UserModel {

    public static function registerAndLoginUserWoo($email) {

        if(is_user_logged_in()){
            return false;
        }

	    $user = self::registerUser($email);

        $credentials = array(
            'user_login'    => $email,
            'user_password' => $user['password'],
            'remember'      => true,
        );

        $logged_in_user = wp_signon( $credentials, false );

        return $logged_in_user;
    }

	public static function registerUserEDD($email) {

		if(is_user_logged_in()){
			return false;
		}

		$user = self::registerUser($email);

		return $user['user']->data->ID;

	}

	public static function registerUser($email) {
		$new_user_password = md5(mt_rand());
		if(!username_exists($email)){
			$id = wp_insert_user(array(
				'user_login'   => $email,
				'user_pass'    => $new_user_password,
				'nickname'     => $email,
				'display_name' => $email,
				'role'         => 'subscriber'
			));

			return ['user' => get_user_by('ID', $id), 'password' => $new_user_password];
		}
	}


}
