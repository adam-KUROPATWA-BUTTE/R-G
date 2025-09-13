<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'randg2664393');


/** Database username */
define('DB_USER', 'randg2664393');


/** Database password */
define('DB_PASSWORD', 'assmjlpcmy');


/** Database hostname */
define('DB_HOST', '213.255.195.34');


/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );


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
define( 'AUTH_KEY',         'IiF=hA+2,/2Z9)v#I~R:RmV{[B84n4Wl))@7,)M*h;zSnX?Phgn:^#oa9l+|M7rS' );

define( 'SECURE_AUTH_KEY',  'CC8-x$*z^4t~/]Rpr Lf2ol7dNOCE.`$[6CvjWt44a{ngG#8Sl[vNC{mHz,.f]w_' );

define( 'LOGGED_IN_KEY',    '#j.),y;MP:oOtN0G`<:n~_]M)+}jmv:Qo2or-vyKCBwFm_y&lY$n|85[%jE-,kg.' );

define( 'NONCE_KEY',        'IZ<28Kpn.JU$n<J|xd87?c<J!9;a]ec,vW2-@4lYV(xtmo=IwhSUX`|IR)j38|48' );

define( 'AUTH_SALT',        '@06E<Z7gT>y| VLW_k([:xS,K8h@tF#<^ciWWeomt_Q33Q1oXcew>(U,T`#cKN0t' );

define( 'SECURE_AUTH_SALT', 'P=[gu6e{p#>q/gSr,A|R<6E+Yz(Y8l&7KkfgS20Y$jH):J]ONj0>yKa,O<(/2}+d' );

define( 'LOGGED_IN_SALT',   'C9] lDK5$m] 11QFkd!=L@V-LksEf=}68B<mt]~3#<H+D&,};v}FZ|W*qgIu`?P0' );

define( 'NONCE_SALT',       'px)VsHkF(p6S=04={#o3#ee6#zO1c$=Iw67fogfK*gpC]dQ;,+Gz8pwme[v5z$NS' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
