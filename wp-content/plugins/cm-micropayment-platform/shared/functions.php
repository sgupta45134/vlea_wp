<?php

if ( !function_exists( 'cminds_prettyPoints' ) ) {

    function cminds_prettyPoints( $amount, $action ) {
		$dec_separator = '.';
		global $wp_locale;

		if (  isset( $wp_locale ) ) {
			$dec_separator = $wp_locale->number_format['decimal_point'];
		}
		$amount = explode($dec_separator, $amount)[0];
		$amount = str_replace(',','', $amount);
		if ( is_numeric($amount) && ( intval($amount) == floatval($amount) ) ){
			$amount = explode($dec_separator, $amount)[0]; // remove '.'and following zeroes
			if ( $action === 'add' ) {
				$amount .= '.00';
			}
		}
        return $amount;
    }

}
/**
 * CM Ad Changer
 *
 * @author CreativeMinds (http://ad-changer.cminds.com)
 * @copyright Copyright (c) 2013, CreativeMinds
 */
if ( !function_exists( 'parse_php_info' ) ) {

    if ( !defined( 'KB' ) ) {
        define( 'KB', 1024 );
    }
    if ( !defined( 'MB' ) ) {
        define( 'MB', 1048576 );
    }
    if ( !defined( 'GB' ) ) {
        define( 'GB', 1073741824 );
    }
    if ( !defined( 'TB' ) ) {
        define( 'TB', 1099511627776 );
    }
    if ( !defined( 'HOURINSECONDS' ) ) {
        define( 'HOURINSECONDS', 3600 );
    }

    function parse_php_info() {
        ob_start();
        phpinfo( INFO_MODULES );
        $s        = ob_get_contents();
        ob_end_clean();
        $s        = strip_tags( $s, '<h2><th><td>' );
        $s        = preg_replace( '/<th[^>]*>([^<]+)<\/th>/', "<info>\\1</info>", $s );
        $s        = preg_replace( '/<td[^>]*>([^<]+)<\/td>/', "<info>\\1</info>", $s );
        $vTmp     = preg_split( '/(<h2>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE );
        $vModules = array();
        for ( $i = 1; $i < count( $vTmp ); $i++ ) {
            if ( preg_match( '/<h2>([^<]+)<\/h2>/', $vTmp[ $i ], $vMat ) ) {
                $vName = trim( $vMat[ 1 ] );
                $vTmp2 = explode( "\n", $vTmp[ $i + 1 ] );
                foreach ( $vTmp2 AS $vOne ) {
                    $vPat  = '<info>([^<]+)<\/info>';
                    $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
                    $vPat2 = "/$vPat\s*$vPat/";
                    if ( preg_match( $vPat3, $vOne, $vMat ) ) { // 3cols
                        $vModules[ $vName ][ trim( $vMat[ 1 ] ) ] = array( trim( $vMat[ 2 ] ), trim( $vMat[ 3 ] ) );
                    } elseif ( preg_match( $vPat2, $vOne, $vMat ) ) { // 2cols
                        $vModules[ $vName ][ trim( $vMat[ 1 ] ) ] = trim( $vMat[ 2 ] );
                    }
                }
            }
        }
        return $vModules;
    }

}

if ( !function_exists( 'cminds_units2bytes' ) ) {

    function cminds_units2bytes( $str ) {
        $units      = array( 'B', 'K', 'M', 'G', 'T' );
        $unit       = preg_replace( '/[0-9]/', '', $str );
        $unitFactor = array_search( strtoupper( $unit ), $units );
        if ( $unitFactor !== false ) {
            return preg_replace( '/[a-z]/i', '', $str ) * pow( 2, 10 * $unitFactor );
        }
    }

}

if ( !function_exists( 'cminds_show_message' ) ) {

    /**
     * Generic function to show a message to the user using WP's
     * standard CSS classes to make use of the already-defined
     * message colour scheme.
     *
     * @param $message The message you want to tell the user.
     * @param $errormsg If true, the message is an error, so use
     * the red message style. If false, the message is a status
     * message, so use the yellow information message style.
     */
    function cminds_show_message( $message, $errormsg = false ) {
        if ( $errormsg ) {
            echo '<div id="message" class="error">';
        } else {
            echo '<div id="message" class="updated fade">';
        }

        echo "<p><strong>$message</strong></p></div>";
    }

}