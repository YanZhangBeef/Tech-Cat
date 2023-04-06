<?php

/**

 * The base configuration for WordPress

 *

 * The wp-config.php creation script uses this file during the installation.

 * You don't have to use the web site, you can copy this file to "wp-config.php"

 * and fill in the values.

 *

 * This file contains the following configurations:

 *

 * * Database settings

 * * Secret keys

 * * Database table prefix

 * * ABSPATH

 *

 * @link https://wordpress.org/support/article/editing-wp-config-php/

 *

 * @package WordPress

 */


// ** Database settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define( 'DB_NAME', 'bitnami_wordpress' );


/** Database username */

define( 'DB_USER', 'bn_wordpress' );


/** Database password */

define( 'DB_PASSWORD', '8f1793359e86141d603d8223c2036a8acf29e97cb2bfdcaa8ef6482ec510a9db' );


/** Database hostname */

define( 'DB_HOST', '127.0.0.1:3306' );


/** Database charset to use in creating database tables. */

define( 'DB_CHARSET', 'utf8' );


/** The database collate type. Don't change this if in doubt. */

define( 'DB_COLLATE', '' );


/**#@+

 * Authentication unique keys and salts.

 *

 * Change these to different unique phrases! You can generate these using

 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.

 *

 * You can change these at any point in time to invalidate all existing cookies.

 * This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define( 'AUTH_KEY',         'v5P)IO.FdK:wvS<QikrRs,t5XgB:Wwb3dc3VSW</? J@G`D!pE96}b0;o^V9f!1G' );

define( 'SECURE_AUTH_KEY',  'kCnFI9HO~n<_;9sGN/3)3X9]-.^^1Aj3DYd<C*8vY9zI|>,Cir[s[1pK3$@xE%S0' );

define( 'LOGGED_IN_KEY',    '91TQy$WEF_#E7=Xhq=HtYET(FCQLZylVWf=?V&uP0{5yp6y~.(4Vy&jPYCH0Bz+>' );

define( 'NONCE_KEY',        'eCoT@$?(1F<(u?9u<`HY(0.#]#6%.)s>ltu5x(&$j~M<.W[}E-OquFP+i4?XqM#F' );

define( 'AUTH_SALT',        'ImE[D^p4K>Iarq-RIJo.fjUReC^H(zXzENjiX:_zK%%D^ssji(G!*mzwE-Sn#Qh`' );

define( 'SECURE_AUTH_SALT', '8lLwcw7/.R{sRClSOH.&m>ysJi5ldDbm|R7k7gC#&<Jff:Ldt{-~e?e#(3e|vmW{' );

define( 'LOGGED_IN_SALT',   '0[xLu8PqDW3,/F_K()`+zMwADbhI?X#{cjCJ..mU.l`=C+ue>:T2.4o]hbLP;Lq}' );

define( 'NONCE_SALT',       'x5>Qj4l<9+${y/~;N<JY]`sFX>j]Q?CCninL?KWyVtvX%>n+j{9L5hY3e3wkz, 4' );


/**#@-*/


/**

 * WordPress database table prefix.

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


/* Add any custom values between this line and the "stop editing" line. */




define( 'FS_METHOD', 'direct' );
/**
 * The WP_SITEURL and WP_HOME options are configured to access from any hostname or IP address.
 * If you want to access only from an specific domain, you can modify them. For example:
 *  define('WP_HOME','http://example.com');
 *  define('WP_SITEURL','http://example.com');
 *
 */
if ( defined( 'WP_CLI' ) ) {
	$_SERVER['HTTP_HOST'] = '127.0.0.1';
}

define( 'WP_HOME', 'http://' . $_SERVER['HTTP_HOST'] . '/' );
define( 'WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST'] . '/' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */

if ( ! defined( 'ABSPATH' ) ) {

	define( 'ABSPATH', __DIR__ . '/' );

}


/** Sets up WordPress vars and included files. */

require_once ABSPATH . 'wp-settings.php';

/**
 * Disable pingback.ping xmlrpc method to prevent WordPress from participating in DDoS attacks
 * More info at: https://docs.bitnami.com/general/apps/wordpress/troubleshooting/xmlrpc-and-pingback/
 */
if ( !defined( 'WP_CLI' ) ) {
	// remove x-pingback HTTP header
	add_filter("wp_headers", function($headers) {
		unset($headers["X-Pingback"]);
		return $headers;
	});
	// disable pingbacks
	add_filter( "xmlrpc_methods", function( $methods ) {
		unset( $methods["pingback.ping"] );
		return $methods;
	});
}
