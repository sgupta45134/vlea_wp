<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'VLE-WP' );

/** MySQL database username */
define( 'DB_USER', 'VLE-WP' );

/** MySQL database password */
define( 'DB_PASSWORD', '^w=!%sQ-t9:3Es!E' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '[3$(x>zc[>SP3j$W `]MkvFAtnZ+s?O86<]@)1kGt9wQrNYJO(V!Na#3sh{-@JCd' );
define( 'SECURE_AUTH_KEY',  '#!YxnQ@0b{&6rz2zz/.AGP2O7 wN|/`PomV*>cKRgHWFFzLh$|W:BqrE:8z D!V>' );
define( 'LOGGED_IN_KEY',    'q!./`:tx6{f9S{vgC22@QBv:E?JaFk@HcyUo}.@Y~b(2JI>=Ye^mw!!6@XNef;B)' );
define( 'NONCE_KEY',        '2v2kFwOj7hNivs.]tU0%k./6:3Ey*2!f5qLVC6VPX%v$w<v/ .$97E6r57rG~tXA' );
define( 'AUTH_SALT',        '1;9}rUmvL-HJUeS;|.C/x?BM_iw)1tr(u=#H%d/Z>nnEL$-QvVKZ/N|HG3-+V-C+' );
define( 'SECURE_AUTH_SALT', '#>jDbL={;4rgA 4M1;?{nR`d56X]H-/}B;.~^/6p@Swy8l>z9vT:<4|x*SU?nQFf' );
define( 'LOGGED_IN_SALT',   'd6BH G@qQ{!R)P(o,j/;a<o{v9z+u[WN>H`Y^ydQZ.B4ZI{G0Q};+]^zUXIHH#h,' );
define( 'NONCE_SALT',       '<3<:7UL{T9L;7;G@#P~,[!o*t-+@CM?.VYW>CeA^-UlX}j=PTGH}HTeo,rYQZ&lw' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
